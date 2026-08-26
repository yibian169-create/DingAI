<?php /** 首页：产品 / 方案精选 */
$hm = []; if (!empty($settings['home_modules'])) { $hm = json_decode($settings['home_modules'], true) ?: []; }
$p = $hm['products'] ?? [];
$kicker   = $p['kicker']    ?? 'Solutions';
$title    = $p['title']     ?? '四大<em>业务拓展方案</em>';
$desc     = $p['desc']      ?? '覆盖获客、转化、复购、裂变的完整增长生产线。';
$moreText = $p['more_text'] ?? '定制方案';
?>
<section class="q-section">
    <div class="q-container">
        <div class="q-head-row q-reveal">
            <div>
                <span class="q-kicker"><?= e($kicker) ?></span>
                <h2 class="q-title"><?= $title ?></h2>
                <p class="q-desc"><?= e($desc) ?></p>
            </div>
            <a href="<?= e($contactUrl) ?>" class="q-more"><?= e($moreText) ?>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </a>
        </div>
        <div class="q-cards">
            <?php if ($pros): foreach ($pros as $i => $p): ?>
            <a class="q-card q-reveal <?= $i % 4 === 1 ? 'd1' : ($i % 4 === 2 ? 'd2' : ($i % 4 === 3 ? 'd3' : '')) ?>" href="index.php?act=detail&type=product&id=<?= (int)$p['id'] ?>">
                <div class="q-card__thumb">
                    <?php if (!empty($p['cover'])): ?>
                    <img src="<?= e($p['cover']) ?>" alt="<?= e($p['title']) ?>" loading="lazy">
                    <?php else: ?>
                    <div class="q-card__thumb q-card__thumb--ph">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="q-card__body">
                    <span class="q-card__cat">AI 应用</span>
                    <h3 class="q-card__title"><?= e($p['title']) ?></h3>
                    <p class="q-card__desc"><?= e(cut($p['summary'], 60)) ?></p>
                    <span class="q-card__meta"><span><?= substr($p['created_at'], 0, 10) ?></span><span>了解详情 →</span></span>
                </div>
            </a>
            <?php endforeach; else: ?>
            <a class="q-card q-reveal" href="index.php?act=city">
                <div class="q-card__thumb q-card__thumb--ph">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg>
                </div>
                <div class="q-card__body">
                    <span class="q-card__cat">拓展方案</span>
                    <h3 class="q-card__title">获客渠道搭建</h3>
                    <p class="q-card__desc">本地、同城、私域、短视频多通道获客方案，找到最适合你的精准客群。</p>
                    <span class="q-card__meta"><span><?= date('Y-m') ?></span><span>了解详情 →</span></span>
                </div>
            </a>
            <a class="q-card q-reveal d1" href="index.php?act=city">
                <div class="q-card__thumb q-card__thumb--ph">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg>
                </div>
                <div class="q-card__body">
                    <span class="q-card__cat">拓展方案</span>
                    <h3 class="q-card__title">转化漏斗设计</h3>
                    <p class="q-card__desc">从首次接触到成交每一步都设计好话术与节点，转化率自然提升。</p>
                    <span class="q-card__meta"><span><?= date('Y-m') ?></span><span>了解详情 →</span></span>
                </div>
            </a>
            <a class="q-card q-reveal d2" href="index.php?act=city">
                <div class="q-card__thumb q-card__thumb--ph">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg>
                </div>
                <div class="q-card__body">
                    <span class="q-card__cat">拓展方案</span>
                    <h3 class="q-card__title">私域复购运营</h3>
                    <p class="q-card__desc">客户分层、触达节奏、复购活动一套模板，把一次性客户变成长期资产。</p>
                    <span class="q-card__meta"><span><?= date('Y-m') ?></span><span>了解详情 →</span></span>
                </div>
            </a>
            <a class="q-card q-reveal d3" href="index.php?act=city">
                <div class="q-card__thumb q-card__thumb--ph">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg>
                </div>
                <div class="q-card__body">
                    <span class="q-card__cat">拓展方案</span>
                    <h3 class="q-card__title">口碑裂变设计</h3>
                    <p class="q-card__desc">转介绍、案例包装、好评体系帮你做出口碑，让客户主动带来客户。</p>
                    <span class="q-card__meta"><span><?= date('Y-m') ?></span><span>了解详情 →</span></span>
                </div>
            </a>
            <?php endif; ?>
        </div>
    </div>
</section>
