<?php /** 城市专属首页（盘企式全国分站：每城一个独立可收录页面，含 LocalBusiness 结构化数据 + 全国城市互链） */ ?>
<?= $jsonLdScript ?? '' ?>
<!-- ============ 面包屑横幅 ============ -->
<section class="q-band">
    <div class="q-grid-overlay"></div>
    <div class="q-container">
        <span class="q-kicker"><?= e($city['city']) ?> Site</span>
        <h1 class="q-band__title"><em><?= e($city['city'] . ($industry ?? '网站建设')) ?></em></h1>
        <p class="q-band__info"><?= e($desc ?? '') ?></p>
        <nav class="q-crumb">
            <a href="index.php">首页</a><span class="sep">/</span>
            <a href="index.php?act=city">全国分站</a><span class="sep">/</span>
            <span class="cur"><?= e($city['city']) ?></span>
        </nav>
    </div>
</section>

<!-- ============ 顶部品牌/意向词横幅（独立配置：city_banner_brand → site_name → 默认值） ============ -->
<?php
$brandBanner = trim((string)($settings['city_banner_brand'] ?? ''));
if ($brandBanner === '') { $brandBanner = trim((string)($settings['site_name'] ?? '')); }
if ($brandBanner === '') { $brandBanner = '得应盯'; }
$cityNotice   = trim((string)($settings['city_notice'] ?? ''));
?>
<?php if ($brandBanner !== ''): ?>
<div style="background:linear-gradient(115deg,#4f46e5,#7c83ff);color:#fff;text-align:center;padding:11px 16px;font-size:14px;font-weight:700;line-height:1.5;letter-spacing:.5px">
    🏷️ <?= e($brandBanner) ?> · 全国 <?= e($city['city'] ?? '') ?>本地服务 · <a href="tel:<?= e(!empty($S['phone']) ? $S['phone'] : '') ?>" style="color:#fff;text-decoration:underline"><?= e(!empty($S['phone']) ? $S['phone'] : '400-000-0000') ?></a>
</div>
<?php endif; ?>
<?php /* 公告降级为可选 hint，没人看默认隐藏 */ ?>
<?php if ($cityNotice !== ''): ?>
<div style="background:rgba(245,158,11,.08);color:#92400e;text-align:center;padding:6px 16px;font-size:12px;font-weight:500;line-height:1.5;border-top:1px solid rgba(245,158,11,.2)"><?= e($cityNotice) ?></div>
<?php endif; ?>

<!-- ============ 本地服务 + 城市内容聚合 ============ -->
<section class="q-section q-section--tight">
    <div class="q-container q-list-layout">
        <aside class="q-sidebar">
            <div class="q-side__block q-side__contact">
                <h3 class="q-side__title"><i></i><?= e($city['city']) ?>本地服务</h3>
                <div class="num"><?= e(!empty($S['phone']) ? $S['phone'] : '400-000-0000') ?></div>
                <p><?= e($city['city']) ?>本地化一对一服务，专业顾问在线解答，<?= e($industry ?? '网站建设') ?>、网站制作、SEO 优化一站式搞定。</p>
                <a class="q-btn q-btn--grad q-btn--sm" href="tel:<?= e(!empty($S['phone']) ? $S['phone'] : '') ?>">立即咨询</a>
            </div>
            <?php /* 全国分站列表只在「导航 + 全国分站列表页」展示，此处移除侧栏全量列表，避免页面臃肿 */ ?>
        </aside>
        <div class="q-list-main">
            <!-- ============ 本地服务要点（重构 2025：优先展示分站自身 content；fallback 到模板文案） ============ -->
            <?php if (!empty($cityContent)): ?>
            <div class="city-content" style="margin-bottom:22px;font-size:14.5px;line-height:1.9;color:var(--faint)">
                <?php if (!empty($cityContentTitle)): ?>
                <h2 style="font-size:18px;font-weight:800;color:var(--text);margin-bottom:14px;line-height:1.5;background:linear-gradient(90deg,var(--primary),var(--primary-2));-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;display:inline-block"><?= e($cityContentTitle) ?></h2>
                <?php endif; ?>
                <style>.city-content p{margin:0 0 14px}.city-content h3{font-size:16px;font-weight:700;color:var(--text);margin:22px 0 12px;display:flex;align-items:center;gap:8px}.city-content h3::before{content:"";width:4px;height:16px;border-radius:3px;background:linear-gradient(180deg,var(--primary),var(--primary-2))}</style>
                <?= $cityContent /* 已是 ai_city_content 输出的 HTML（h3/p），可信 */ ?>
            </div>
            <?php else: ?>
            <div class="q-band__info" style="margin-bottom:18px;font-size:14px;line-height:1.9;color:var(--faint)">
                <?= e($city['city'] . ($industry ?? '网站建设')) ?>是<?= e($site) ?>面向<?= e($city['city']) ?>本地客户提供的专属服务：涵盖<?= e($industry ?? '网站建设') ?>、企业官网定制、商城小程序、SEO 排名优化等，全部本地化团队跟进，<?= e($city['city']) ?>本地企业足不出户即可获得一线城市的建站服务标准。
            </div>
            <?php endif; ?>
            <?php /* ===== 全国分站重构（2025）：本地资讯仅展示「本城市专属文章」（标题含城市名），
                     不展示全站全局文章 → 防止 300 城页面显示同一批文章被搜索引擎判重复 ===== */ ?>
            <?php if (!empty($cityArts) && !empty($cityContent)): ?>
            <div style="margin-bottom:20px">
                <h3 class="q-side__title" style="margin-bottom:12px"><i></i><?= e($city['city']) ?>本地资讯</h3>
                <div class="q-news q-news--2">
                    <?php foreach ($cityArts as $a): ?>
                    <a class="q-news__item q-reveal" href="index.php?act=detail&type=article&id=<?= (int)$a['id'] ?>">
                        <div class="q-news__thumb">
                            <?php if (!empty($a['cover'])): ?>
                            <img src="<?= e($a['cover']) ?>" alt="<?= e($a['title']) ?>" loading="lazy">
                            <?php else: ?>
                            <div class="q-news__thumb q-news__thumb--ph">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/><path d="M18 14h-8M15 18h-5M10 6h8v4h-8V6z"/></svg>
                            </div>
                            <?php endif; ?>
                            <span class="q-news__date"><?= substr($a['created_at'], 5, 5) ?></span>
                        </div>
                        <div class="q-news__body">
                            <h3 class="q-news__title"><?= e('[' . $city['city'] . ']' . $a['title']) ?></h3>
                            <p class="q-news__desc"><?= e(cut($a['summary'], 80)) ?></p>
                            <span class="q-news__foot"><span><?= (int)$a['views'] . ' 阅读' ?></span><span><?= substr($a['created_at'], 0, 10) ?></span></span>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            <?php /* 全国分站列表只在「导航 + 全国分站列表页」展示，此处把底部全量链接组替换为精简入口 */ ?>
            <div style="margin-top:26px;padding:18px 22px;background:var(--bg);border:1px dashed var(--border-tertiary);border-radius:10px;display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap">
                <div style="font-size:13px;color:var(--faint);line-height:1.7">
                    <strong style="color:var(--text)">全国其他 <?= (int)(count($cities ?? []) - 1) ?>+ 个城市分站</strong> · 同样 1v1 本地化服务<br>
                    <small>完整城市列表请到顶部「全国分站」导航查看</small>
                </div>
                <a class="q-btn q-btn--grad q-btn--sm" href="index.php?act=city">查看全国分站 →</a>
            </div>
        </div>
    </div>
</section>

<!-- ============ 底部 CTA ============ -->
<section class="q-cta">
    <div class="q-container q-cta__inner">
        <div>
            <h2><?= e($city['city'] . ($industry ?? '网站建设')) ?>，现在就开始</h2>
            <p><?= e($city['city']) ?>本地企业专属方案，免费咨询、量身定制。</p>
        </div>
        <div style="display:flex;gap:12px;flex-wrap:wrap">
            <a class="q-btn q-btn--grad" href="tel:<?= e(!empty($S['phone']) ? $S['phone'] : '') ?>">电话咨询</a>
            <a class="q-btn q-btn--ghost" href="index.php?act=form">在线留言</a>
        </div>
    </div>
</section>
