<?php /** 首页：新闻动态 */
$hm = []; if (!empty($settings['home_modules'])) { $hm = json_decode($settings['home_modules'], true) ?: []; }
$n = $hm['news'] ?? [];
$kicker   = $n['kicker']    ?? 'Insights';
$title    = $n['title']     ?? '业务拓展<em>实战</em>干货';
$desc     = $n['desc']      ?? '获客、转化、复购、裂变实操，持续输出可落地的打法。';
$moreText = $n['more_text'] ?? '全部动态';
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
        <div class="q-news">
            <?php if ($news): foreach ($news as $i => $a): ?>
            <a class="q-news__item q-reveal <?= $i % 3 === 1 ? 'd1' : ($i % 3 === 2 ? 'd2' : '') ?>" href="index.php?act=detail&type=article&id=<?= (int)$a['id'] ?>">
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
                    <h3 class="q-news__title"><?= e($a['title']) ?></h3>
                    <p class="q-news__desc"><?= e(cut($a['summary'], 60)) ?></p>
                    <span class="q-news__foot">
                        <span><?= (int)$a['views'] ?> 阅读</span>
                        <span><?= substr($a['created_at'], 0, 10) ?></span>
                    </span>
                </div>
            </a>
            <?php endforeach; else: ?>
            <a class="q-news__item q-reveal" href="index.php?act=list&cat=0">
                <div class="q-news__thumb q-news__thumb--ph">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/><path d="M18 14h-8M15 18h-5M10 6h8v4h-8V6z"/></svg>
                </div>
                <div class="q-news__body">
                    <h3 class="q-news__title">同城获客实操：3 天加满 500 精准客户的私域打法</h3>
                    <p class="q-news__desc">从定位客群、设计钩子到私域承接，拆解一套可复制的获客流程。</p>
                    <span class="q-news__foot"><span>1,286 阅读</span><span><?= date('Y-m-d') ?></span></span>
                </div>
            </a>
            <a class="q-news__item q-reveal d1" href="index.php?act=list&cat=0">
                <div class="q-news__thumb q-news__thumb--ph">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/><path d="M18 14h-8M15 18h-5M10 6h8v4h-8V6z"/></svg>
                </div>
                <div class="q-news__body">
                    <h3 class="q-news__title">转化翻倍：从首次接触到成交的四步话术拆解</h3>
                    <p class="q-news__desc">破冰、挖需、方案、逼单四段式结构，配合陪跑落地全流程。</p>
                    <span class="q-news__foot"><span>986 阅读</span><span><?= date('Y-m-d') ?></span></span>
                </div>
            </a>
            <a class="q-news__item q-reveal d2" href="index.php?act=list&cat=0">
                <div class="q-news__thumb q-news__thumb--ph">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/><path d="M18 14h-8M15 18h-5M10 6h8v4h-8V6z"/></svg>
                </div>
                <div class="q-news__body">
                    <h3 class="q-news__title">老客复购：一套模板让 30% 客户主动回来</h3>
                    <p class="q-news__desc">客户分层、触达节奏、复购活动一套可复制的运营流水线。</p>
                    <span class="q-news__foot"><span>752 阅读</span><span><?= date('Y-m-d') ?></span></span>
                </div>
            </a>
            <?php endif; ?>
        </div>
    </div>
</section>
