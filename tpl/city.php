<?php /** 城市分站列表（左右结构） */ ?>
<!-- ============ 面包屑横幅 ============ -->
<section class="q-band">
    <div class="q-grid-overlay"></div>
    <div class="q-container">
        <span class="q-kicker">City Sites</span>
        <h1 class="q-band__title"><em>城市分站</em></h1>
        <nav class="q-crumb">
            <a href="index.php">首页</a><span class="sep">/</span>
            <span class="cur">城市分站</span>
        </nav>
    </div>
</section>

<!-- ============ 左右结构：侧栏 + 城市网格 ============ -->
<section class="q-section q-section--tight">
    <div class="q-container q-list-layout">
        <aside class="q-sidebar">
            <div class="q-side__block">
                <h3 class="q-side__title"><i></i>分站导航</h3>
                <nav class="q-side__list q-side__city">
                    <?php if ($cities): foreach ($cities as $c): ?>
                    <a href="<?= e(city_url($c)) ?>"><?= e($c['city']) ?><small><?= e(!empty($c['title_suffix']) ? $c['title_suffix'] : '分站') ?></small></a>
                    <?php endforeach; endif; ?>
                </nav>
            </div>
            <div class="q-side__block q-side__contact">
                <h3 class="q-side__title"><i></i>免费咨询</h3>
                <div class="num"><?= e($phone) ?></div>
                <p>工作日 9:00-18:00，专业顾问一对一解答。</p>
                <a class="q-btn q-btn--grad q-btn--sm" href="tel:<?= e($phone) ?>">立即咨询</a>
            </div>
        </aside>
        <div class="q-list-main">
            <?php if ($cities): ?>
            <div class="q-stats__grid">
                <?php foreach ($cities as $c): ?>
                <a class="q-stat q-reveal" href="<?= e(city_url($c)) ?>" style="text-decoration:none;display:block">
                    <div class="q-stat__num" style="font-size:clamp(24px,2.6vw,34px)"><?= e($c['city']) ?></div>
                    <div class="q-stat__label"><?= e(!empty($c['title_suffix']) ? $c['title_suffix'] : '分站') ?><small style="display:block;color:var(--faint);font-size:12px;margin-top:4px">?city=<?= e($c['pinyin']) ?></small></div>
                </a>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="q-empty">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0z"/><circle cx="12" cy="10" r="3"/></svg>
                <p>分站建设中，敬请期待</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>
