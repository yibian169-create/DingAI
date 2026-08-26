<?php /** 栏目列表内容（左右结构） */ ?>
<!-- ============ 栏目横幅 ============ -->
<section class="q-band">
    <div class="q-grid-overlay"></div>
    <div class="q-container">
        <span class="q-kicker"><?= e($type === 'product' ? 'Products' : 'News') ?></span>
        <h1 class="q-band__title"><em><?= e(($cityLabel ?? '') . ($cat['name'] ?? '全部内容')) ?></em></h1>
        <p class="q-band__info"><?= e(!empty($cat['seo_description']) ? $cat['seo_description'] : '精选内容与实战干货，持续更新中。') ?></p>
        <nav class="q-crumb">
            <a href="index.php">首页</a><span class="sep">/</span>
            <span class="cur"><?= e(($cityLabel ?? '') . ($cat['name'] ?? '列表')) ?></span>
        </nav>
    </div>
</section>

<!-- ============ 左右结构：侧栏 + 列表主体 ============ -->
<section class="q-section q-section--tight">
    <div class="q-container q-list-layout">
        <?php
        $sideNav  = $sideNav ?? ($navFlat ?? []);
        $curCatId = isset($cat['id']) ? (int)$cat['id'] : 0;
        require __DIR__ . '/side_col.php';
        ?>
        <div class="q-list-main">
            <?php if ($list): ?>
            <div class="q-news q-news--2">
                <?php foreach ($list as $row): ?>
                <a class="q-news__item q-reveal" href="index.php?act=detail&type=<?= $type ?>&id=<?= (int)$row['id'] ?>">
                    <div class="q-news__thumb">
                        <?php if (!empty($row['cover'])): ?>
                        <img src="<?= e($row['cover']) ?>" alt="<?= e($row['title']) ?>" loading="lazy">
                        <?php else: ?>
                        <div class="q-news__thumb q-news__thumb--ph">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/><path d="M18 14h-8M15 18h-5M10 6h8v4h-8V6z"/></svg>
                        </div>
                        <?php endif; ?>
                        <span class="q-news__date"><?= substr($row['created_at'], 5, 5) ?></span>
                    </div>
                    <div class="q-news__body">
                        <h3 class="q-news__title"><?= e(($cityLabel ?? '') . $row['title']) ?></h3>
                        <p class="q-news__desc"><?= e(cut($row['summary'], 80)) ?></p>
                        <span class="q-news__foot">
                            <span><?= $type === 'product' ? e(!empty($row['price']) ? '¥' . $row['price'] : '面议') : ((int)$row['views'] . ' 阅读') ?></span>
                            <span><?= substr($row['created_at'], 0, 10) ?></span>
                        </span>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
            <?php if ($pg['pages'] > 1): ?>
            <div class="q-pagination">
                <?php for ($i = 1; $i <= $pg['pages']; $i++): ?>
                    <?php if ($i === $pg['page']): ?>
                    <span class="current"><?= $i ?></span>
                    <?php else: ?>
                    <a href="index.php?act=list&cat=<?= (int)($cat['id'] ?? 0) ?>&page=<?= $i ?>"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
            <?php else: ?>
            <div class="q-empty">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                <p>暂无内容，敬请期待</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>
