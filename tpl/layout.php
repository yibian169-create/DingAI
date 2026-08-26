<?php
/**
 * 前台公共布局（dd8888 深色科技风）
 * 可用变量：title/kw/desc/settings/nav/city/site/cat/newsFoot/aboutUrl/contactUrl
 */
$S = $settings;
$curCatId = isset($cat['id']) ? (int)$cat['id'] : 0;
$curPid   = isset($cat['pid']) ? (int)$cat['pid'] : -1;
$phone    = !empty($S['phone']) ? $S['phone'] : '18732237111';
$email    = !empty($S['email']) ? $S['email'] : 'hello@example.com';
$address  = !empty($S['address']) ? $S['address'] : '保定市朝阳南大街519号得应盯';
$contactPhone  = !empty($S['contact_phone']) ? $S['contact_phone'] : $phone;
$contactPhone2 = !empty($S['contact_phone2']) ? $S['contact_phone2'] : '';
$contactWxQr   = !empty($S['contact_wx_qr']) ? $S['contact_wx_qr'] : '';
$contactMpQr   = !empty($S['contact_mp_qr']) ? $S['contact_mp_qr'] : '';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($title) ?></title>
<meta name="keywords" content="<?= e($kw ?? '') ?>">
<meta name="description" content="<?= e($desc ?? '') ?>">
<link rel="stylesheet" href="static/css/style.css">
<?php $tc = get_theme_colors(setting('theme', 'aurora')); ?>
<style>:root{--main-color:<?= e($tc[0]) ?>;--aux-color:<?= e($tc[1]) ?>;--accent-color:<?= e($tc[2]) ?>}</style>
<?php
/* 模板中心：优先加载内置模板，其次兼容历史站点模板 */
$tplActive = setting('tpl_active', '');
$safeName = preg_replace('/[^a-zA-Z0-9_-]/', '', $tplActive);
$tplCss = '';
$tplJs  = '';
$tplPath = '';
if ($safeName !== '') {
    $builtinDir = 'tpls/builtin/' . $safeName;
    $siteDir    = 'tpls/site_' . current_site_id() . '/' . $safeName;
    if (is_file(__DIR__ . '/../' . $builtinDir . '/style.css')) {
        $tplCss = $builtinDir . '/style.css';
        $tplPath = $builtinDir;
        if (is_file(__DIR__ . '/../' . $builtinDir . '/main.js')) { $tplJs = $builtinDir . '/main.js'; }
    } elseif (is_file(__DIR__ . '/../' . $siteDir . '/style.css')) {
        $tplCss = $siteDir . '/style.css';
        $tplPath = $siteDir;
        if (is_file(__DIR__ . '/../' . $siteDir . '/main.js')) { $tplJs = $siteDir . '/main.js'; }
    }
}
$tplVer = $tplCss ? filemtime(__DIR__ . '/../' . $tplCss) : time();
?>
<?php if ($tplCss): ?>
<link rel="stylesheet" href="<?= e($tplCss) ?>?v=<?= $tplVer ?>">
<?php endif; ?>
</head>
<body<?= $safeName ? ' class="tpl-' . e($safeName) . '"' : '' ?>>

<!-- ============ 顶部导航 ============ -->
<header class="q-header" id="qHeader">
    <div class="q-container q-header__inner">
        <a href="index.php" class="q-logo">
            <span class="q-logo__mark">Ding</span>
            <span class="q-logo__text"><?= e($site) ?><small class="q-logo__sub">DEYINGDING·AI</small></span>
        </a>
        <nav class="q-nav" id="qNav">
            <a class="q-nav__link <?= ($_GET['act'] ?? 'home') === 'home' ? 'is-active' : '' ?>" href="index.php">首页</a>
            <?php foreach ($nav as $c): ?>
            <?php $isActive = $curCatId === (int)$c['id'] || $curPid === (int)$c['id']; ?>
            <?php if (!empty($c['children'])): ?>
            <div class="q-nav__item q-drop">
                <a class="q-nav__link <?= $isActive ? 'is-active' : '' ?>" href="index.php?act=list&cat=<?= (int)$c['id'] ?>"><?= e($c['name']) ?>
                    <svg class="q-nav__caret" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                </a>
                <div class="q-drop__menu">
                    <?php foreach ($c['children'] as $sub): ?>
                    <a class="q-drop__link <?= $curCatId === (int)$sub['id'] ? 'is-active' : '' ?>" href="index.php?act=list&cat=<?= (int)$sub['id'] ?>"><?= e($sub['name']) ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php else: ?>
            <a class="q-nav__link <?= $isActive ? 'is-active' : '' ?>" href="index.php?act=list&cat=<?= (int)$c['id'] ?>"><?= e($c['name']) ?></a>
            <?php endif; ?>
            <?php endforeach; ?>
            <?php /* 分站开启 → 显示城市分站入口 */ ?>
            <?php if (($S['city_enable'] ?? '0') === '1'): ?>
            <a class="q-nav__link <?= ($_GET['act'] ?? '') === 'city' ? 'is-active' : '' ?>" href="index.php?act=city">全国分站</a>
            <?php endif; ?>
            <?php /* 表单开启导航显示 → 显示表单入口 */ ?>
            <?php foreach (DB::all('SELECT * FROM form_defs WHERE status=1 AND show_nav=1 AND (site_id=? OR site_id=0) ORDER BY id ASC', [current_site_id()]) as $nf): ?>
            <a class="q-nav__link <?= (($_GET['act'] ?? '') === 'form' && (int)($_GET['id'] ?? 0) === (int)$nf['id']) ? 'is-active' : '' ?>" href="index.php?act=form&id=<?= (int)$nf['id'] ?>"><?= e($nf['title'] ?: $nf['name']) ?></a>
            <?php endforeach; ?>
            <?php /* 下载专区入口：始终显示（即便暂无下载数据也可进入管理/展示页） */ ?>
            <?php $dlActive = ($_GET['act'] ?? '') === 'download'; ?>
            <?php if (!empty($dlCats)): ?>
            <div class="q-nav__item q-drop">
                <a class="q-nav__link <?= $dlActive ? 'is-active' : '' ?>" href="index.php?act=download">下载专区
                    <svg class="q-nav__caret" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                </a>
                <div class="q-drop__menu">
                    <a class="q-drop__link <?= $dlActive && empty($_GET['cat']) ? 'is-active' : '' ?>" href="index.php?act=download">全部下载</a>
                    <?php foreach ($dlCats as $dc): ?>
                    <a class="q-drop__link <?= $dlActive && (int)($_GET['cat'] ?? 0) === (int)$dc['id'] ? 'is-active' : '' ?>" href="index.php?act=download&cat=<?= (int)$dc['id'] ?>"><?= e($dc['name']) ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php else: ?>
            <a class="q-nav__link <?= $dlActive ? 'is-active' : '' ?>" href="index.php?act=download">下载专区</a>
            <?php endif; ?>
        </nav>
        <div class="q-header__actions">
            <a class="q-header__phone" href="tel:<?= e($phone) ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                <?= e($phone) ?>
            </a>
            <button class="q-burger" id="qBurger" aria-label="菜单"><span></span><span></span><span></span></button>
        </div>
    </div>
</header>
<div class="q-nav__backdrop" id="qNavBackdrop"></div>

<?php if (!empty($city)): ?>
<div style="background:linear-gradient(115deg,#818cf8,#e879f9);color:#07101f;text-align:center;padding:7px;font-size:13px;font-weight:600">
  <?= e($city['city']) ?>分站已生效<?= $city['title_suffix'] ? '（' . e($city['title_suffix']) . '）' : '' ?>
</div>
<?php endif; ?>

<?php require __DIR__ . '/' . $tpl; ?>

<!-- ============ 页脚 ============ -->
<footer class="q-footer">
    <div class="q-container q-footer__grid">
        <div class="q-footer__brand">
            <a href="index.php" class="q-logo">
                <span class="q-logo__mark">Ding</span>
                <span class="q-logo__text"><?= e($site) ?></span>
            </a>
            <p class="q-footer__desc"><?= e(!empty($S['footer_text']) ? $S['footer_text'] : '帮中小老板把业务推出去，让客户主动找上门。') ?></p>
            <div class="q-footer__social">
                <a href="#" aria-label="微信"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg></a>
                <a href="#" aria-label="抖音"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12a4 4 0 1 0 4 4V4a5 5 0 0 0 5 5"/></svg></a>
                <a href="mailto:<?= e($email) ?>" aria-label="邮箱"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 6L2 7"/></svg></a>
                <a href="tel:<?= e($phone) ?>" aria-label="电话"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg></a>
            </div>
        </div>
        <div class="q-footer__col">
            <h4>快速导航</h4>
            <?php foreach ($nav as $c): ?>
            <a href="index.php?act=list&cat=<?= (int)$c['id'] ?>"><?= e($c['name']) ?></a>
            <?php endforeach; ?>
        </div>
        <div class="q-footer__col">
            <h4>联系方式</h4>
            <ul class="q-footer__contact">
                <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg><?= e($phone) ?></li>
                <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 6L2 7"/></svg><?= e($email) ?></li>
                <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0z"/><circle cx="12" cy="10" r="3"/></svg><?= e($address) ?></li>
            </ul>
        </div>
        <div class="q-footer__news">
            <h4>最新动态</h4>
            <?php if (!empty($newsFoot)): foreach ($newsFoot as $a): ?>
            <a href="index.php?act=detail&type=article&id=<?= (int)$a['id'] ?>"><?= e($a['title']) ?></a>
            <?php endforeach; else: ?>
            <a href="index.php?act=list&cat=0">暂无动态，敬请期待</a>
            <?php endif; ?>
        </div>
    </div>
    <div class="q-footer__bar">
        <div class="q-container" style="display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap">
            <span>© <?= date('Y') ?> <?= e($site) ?> 版权所有</span>
            <span>Powered by <?= e($site) ?> · deyingding-php<?php if (!empty($S['techsupport_text']) && !empty($S['techsupport_url'])): ?> · 技术支持：<a href="<?= e($S['techsupport_url']) ?>" target="_blank" rel="noopener"><?= e($S['techsupport_text']) ?></a><?php endif; ?></span>
        </div>
    </div>
</footer>

<button class="q-top" id="qTop" aria-label="返回顶部">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m18 15-6-6-6 6"/></svg>
</button>

<!-- 右侧悬浮联系侧边栏 -->
<div class="side-contact" id="sideContact">
  <div class="side-contact__tab" role="button" aria-label="打开联系我们">
    <span class="side-contact__tab-icon">💬</span>
    <span class="side-contact__tab-text">联系我们</span>
  </div>
  <div class="side-contact__panel">
    <div class="side-contact__head">
      <span>联系我们</span>
      <button type="button" class="side-contact__close" aria-label="关闭">×</button>
    </div>
    <div class="side-contact__body">
      <div class="side-contact__section">
        <?php if ($contactPhone): ?>
        <div class="side-contact__row">
          <i>📞</i>
          <div><div class="side-contact__label">服务电话</div><a class="side-contact__value" href="tel:<?= e($contactPhone) ?>"><?= e($contactPhone) ?></a></div>
        </div>
        <?php endif; ?>
        <?php if ($contactPhone2): ?>
        <div class="side-contact__row">
          <i class="wechat">💬</i>
          <div><div class="side-contact__label">导师电话</div><a class="side-contact__value" href="tel:<?= e($contactPhone2) ?>"><?= e($contactPhone2) ?></a></div>
        </div>
        <?php endif; ?>
      </div>
      <?php if ($contactWxQr || $contactMpQr): ?>
      <div class="side-contact__section">
        <div class="side-contact__qr-title">扫码咨询</div>
        <?php if ($contactWxQr): ?>
        <div class="side-contact__qr"><img src="<?= e($contactWxQr) ?>" alt="负责人微信二维码"></div>
        <div style="text-align:center;font-size:13px;color:var(--muted);margin-top:6px">负责人微信</div>
        <?php endif; ?>
        <?php if ($contactMpQr): ?>
        <div class="side-contact__qr" style="margin-top:12px"><img src="<?= e($contactMpQr) ?>" alt="微信公众号二维码"></div>
        <div style="text-align:center;font-size:13px;color:var(--muted);margin-top:6px">微信公众号</div>
        <?php endif; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<div class="side-contact__backdrop"></div>

<script src="static/js/main.js"></script>
<?php if ($tplJs): $jsVer = filemtime(__DIR__ . '/../' . $tplJs); ?><script src="<?= e($tplJs) ?>?v=<?= $jsVer ?>"></script><?php endif; ?>
</body>
</html>
