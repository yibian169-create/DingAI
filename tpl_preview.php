<?php
/**
 * 模板可视化预览页（需后台登录）
 * 用法：tpl_preview.php?tpl=catering
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/funcs.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_admin();

// 当前仅保留「家纺家居·暖调」模板预览；其余模板已从模板中心下架
$allowed = ['_demo_template_home_textile2' => '家纺家居·暖调'];
$tpl = trim($_GET['tpl'] ?? '');
if (!array_key_exists($tpl, $allowed)) {
    $tpl = '';
}
$isBuiltin = $tpl !== '';

$site          = $isBuiltin ? $allowed[$tpl] : '得应盯';
$phone         = '18732237111';
$email         = 'hello@example.com';
$address       = '保定市朝阳南大街519号';
$contactUrl    = '#';
$aboutUrl      = '#';
$newsFoot      = [];
$cat           = null;
$title         = ($isBuiltin ? $allowed[$tpl] : '系统默认') . ' · 模板预览';
$kw            = '';
$desc          = '';
$pros          = [];
$news          = [];

$S = [
    'site_name' => $site,
    'phone' => $phone,
    'email' => $email,
    'address' => $address,
    'footer_text' => $isBuiltin ? ($allowed[$tpl] . ' · 行业模板预览') : '帮中小老板把业务推出去，让客户主动找上门。',
    'hero_title' => $isBuiltin ? $allowed[$tpl] : '业务拓展实战帮手',
    'hero_sub' => $isBuiltin ? ('欢迎来到 ' . $allowed[$tpl] . ' 主题预览，这是为行业场景化定制的全新视觉风格。') : '专注帮中小老板做业务拓展：获客、转化、口碑，让生意自己跑起来。',
    'about_text' => '这是模板预览页面，实际内容以后台发布的数据为准。你可以在后台「模板中心」一键启用该模板，全站立即换肤。',
    'stat1' => '500', 'stat1_label' => '服务老板',
    'stat2' => '30',  'stat2_label' => '拓展打法',
    'stat3' => '92',  'stat3_label' => '满意度',
    'stat4' => '24',  'stat4_label' => '小时在线',
];
// tpl/home.php 使用 $settings 变量读取布局与模块配置
$settings = $S;

$cssHref = $isBuiltin ? 'tpls/' . $tpl . '/style.css' : '';
$bodyClass = $isBuiltin ? 'tpl-' . preg_replace('/[^a-zA-Z0-9_-]/', '', $tpl) : '';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($title) ?></title>
<meta name="keywords" content="<?= e($kw) ?>">
<meta name="description" content="<?= e($desc) ?>">
<link rel="stylesheet" href="static/css/style.css">
<?php if ($cssHref): ?>
<link rel="stylesheet" href="<?= e($cssHref) ?>?v=<?= time() ?>">
<?php endif; ?>
<style>
.prev-bar{position:fixed;top:0;left:0;right:0;z-index:9999;display:flex;align-items:center;gap:12px;padding:10px 18px;background:rgba(17,24,39,.92);color:#fff;font-size:13px;backdrop-filter:blur(8px)}
.prev-bar b{font-weight:700}
.prev-bar .pill{padding:3px 10px;border-radius:999px;background:rgba(255,255,255,.15)}
.prev-bar a{margin-left:auto;color:#fff;text-decoration:none;padding:6px 14px;border-radius:8px;background:rgba(255,255,255,.18)}
.prev-bar a:hover{background:rgba(255,255,255,.3)}
body{padding-top:46px}
</style>
</head>
<body<?= $bodyClass ? ' class="' . e($bodyClass) . '"' : '' ?>>

<div class="prev-bar">
  <b>模板预览</b>
  <span class="pill"><?= e($isBuiltin ? $allowed[$tpl] : '系统默认') ?></span>
  <span>仅作可视化演示，实际内容以你的站点数据为准</span>
  <a href="javascript:window.close()">关闭</a>
</div>

<!-- 最小化公共头部（仅用于预览，与真实前台 layout 风格一致） -->
<header class="q-header" style="position:relative">
  <div class="q-container q-header__inner">
    <a href="#" class="q-logo"><span class="q-logo__mark">D</span><span class="q-logo__text"><?= e($site) ?></span></a>
    <nav class="q-nav">
      <a class="q-nav__link is-active" href="#">首页</a>
      <a class="q-nav__link" href="#">产品/服务</a>
      <a class="q-nav__link" href="#">关于我们</a>
      <a class="q-nav__link" href="#">联系我们</a>
    </nav>
    <div class="q-header__actions">
      <a class="q-header__user q-header__user--cta" href="#">立即开始</a>
    </div>
  </div>
</header>

<?php require __DIR__ . '/tpl/home.php'; ?>

<!-- 最小化公共底部 -->
<footer class="q-footer" style="position:relative">
  <div class="q-container q-footer__grid">
    <div class="q-footer__brand">
      <a href="#" class="q-logo"><span class="q-logo__mark">D</span><span class="q-logo__text"><?= e($site) ?></span></a>
      <p class="q-footer__desc"><?= e($S['footer_text']) ?></p>
    </div>
    <div class="q-footer__col"><h4>快速导航</h4><a href="#">首页</a><a href="#">产品/服务</a><a href="#">关于我们</a><a href="#">联系我们</a></div>
    <div class="q-footer__col"><h4>联系方式</h4><ul class="q-footer__contact"><li><?= e($phone) ?></li><li><?= e($email) ?></li><li><?= e($address) ?></li></ul></div>
  </div>
  <div class="q-footer__bar"><div class="q-container"><span>© <?= date('Y') ?> <?= e($site) ?> 版权所有</span><span>Powered by deyingding-php</span></div></div>
</footer>

</body>
</html>
