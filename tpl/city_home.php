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

<!-- ============ 分站公告（后台「全国分站」可配置，清空则不显示） ============ -->
<?php $cityNotice = trim((string)($settings['city_notice'] ?? '')); ?>
<?php if ($cityNotice !== ''): ?>
<div style="background:linear-gradient(115deg,#fde68a,#fbbf24);color:#451a03;text-align:center;padding:9px 16px;font-size:13px;font-weight:600;line-height:1.6">📢 <?= e($cityNotice) ?></div>
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
            <div class="q-side__block">
                <h3 class="q-side__title"><i></i>全国服务城市</h3>
                <nav class="q-side__list q-side__city">
                    <?php if (!empty($cities)): foreach ($cities as $c): ?>
                    <a href="<?= e(city_url($c)) ?>"><?= e($c['city']) ?><small><?= e(!empty($c['title_suffix']) ? $c['title_suffix'] : '分站') ?></small></a>
                    <?php endforeach; endif; ?>
                </nav>
            </div>
        </aside>
        <div class="q-list-main">
            <!-- 本地服务要点 -->
            <div class="q-band__info" style="margin-bottom:18px;font-size:14px;line-height:1.9;color:var(--faint)">
                <?= e($city['city'] . ($industry ?? '网站建设')) ?>是<?= e($site) ?>面向<?= e($city['city']) ?>本地客户提供的专属服务：涵盖<?= e($industry ?? '网站建设') ?>、企业官网定制、商城小程序、SEO 排名优化等，全部本地化团队跟进，<?= e($city['city']) ?>本地企业足不出户即可获得一线城市的建站服务标准。
            </div>
            <?php if (!empty($cityArts)): ?>
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
            <?php else: ?>
            <div class="q-empty">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                <p><?= e($city['city']) ?>本地内容持续更新中，敬请期待</p>
            </div>
            <?php endif; ?>
            <!-- 城市间互链 -->
            <div style="margin-top:26px">
                <h3 class="q-side__title" style="margin-bottom:12px"><i></i>更多城市分站</h3>
                <div style="display:flex;flex-wrap:wrap;gap:8px">
                    <?php if (!empty($cities)): foreach ($cities as $c): ?>
                    <a href="<?= e(city_url($c)) ?>" style="display:inline-block;padding:7px 14px;font-size:12.5px;border:1px solid var(--border-tertiary);border-radius:20px;color:var(--faint);text-decoration:none;transition:all .15s"><?= e($c['city']) ?></a>
                    <?php endforeach; endif; ?>
                </div>
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
