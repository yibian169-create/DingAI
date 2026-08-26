<?php /** 内容/产品详情（左右结构） */ ?>
<?= $jsonLdScript ?? '' ?>
<!-- ============ 面包屑横幅 ============ -->
<section class="q-band">
    <div class="q-grid-overlay"></div>
    <div class="q-container">
        <span class="q-kicker"><?= e($type === 'product' ? 'Products' : 'News') ?></span>
        <h1 class="q-band__title"><em><?= e(($cityLabel ?? '') . $row['title']) ?></em></h1>
        <nav class="q-crumb">
            <a href="index.php">首页</a><span class="sep">/</span>
            <a href="index.php?act=list&cat=<?= (int)$row['cat_id'] ?>"><?= e($cat['name'] ?? '列表') ?></a><span class="sep">/</span>
            <span class="cur"><?= e(($cityLabel ?? '') . $row['title']) ?></span>
        </nav>
    </div>
</section>

<!-- ============ 左右结构：主内容 + 侧栏 ============ -->
<section class="q-section q-section--tight">
    <div class="q-container q-detail-layout">
        <main class="q-detail-main">
            <article class="q-article q-reveal">
                <h1 class="q-article__title"><?= e(($cityLabel ?? '') . $row['title']) ?></h1>
                <div class="q-article__meta">
                    <span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                        <?= substr($row['created_at'], 0, 10) ?>
                    </span>
                    <span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        <?= (int)$row['views'] ?> 次浏览
                    </span>
                    <?php if ($type === 'product'): ?>
                    <span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                        价格：<?= e(!empty($row['price']) ? '¥' . $row['price'] : '面议') ?>
                    </span>
                    <?php endif; ?>
                </div>
                <div class="q-content">
                    <?php if (!empty($row['cover'])): ?><img src="<?= e($row['cover']) ?>" alt="<?= e($row['title']) ?>" style="margin-bottom:26px;border-radius:16px"><?php endif; ?>
                    <?= $row['content'] /* 富文本，后台可控 */ ?>
                </div>
                <?php if (!empty($tags)): ?>
                <div class="q-article__tags">
                    <?php foreach ($tags as $tag): ?>
                    <a href="index.php?act=list&cat=<?= (int)$row['cat_id'] ?>"># <?= e($tag) ?></a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </article>
            <?php if (!empty($rel)): ?>
            <div class="q-related q-reveal">
                <h3><i></i>相关阅读</h3>
                <div class="q-news q-news--2">
                    <?php foreach ($rel as $r): ?>
                    <a class="q-news__item" href="index.php?act=detail&type=<?= $type ?>&id=<?= (int)$r['id'] ?>">
                        <div class="q-news__thumb">
                            <?php if (!empty($r['cover'])): ?>
                            <img src="<?= e($r['cover']) ?>" alt="<?= e($r['title']) ?>" loading="lazy">
                            <?php else: ?>
                            <div class="q-news__thumb q-news__thumb--ph">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/><path d="M18 14h-8M15 18h-5M10 6h8v4h-8V6z"/></svg>
                            </div>
                            <?php endif; ?>
                            <span class="q-news__date"><?= substr($r['created_at'], 5, 5) ?></span>
                        </div>
                        <div class="q-news__body">
                            <h3 class="q-news__title"><?= e(($cityLabel ?? '') . $r['title']) ?></h3>
                            <span class="q-news__foot"><span><?= (int)$r['views'] ?> 阅读</span><span><?= substr($r['created_at'], 0, 10) ?></span></span>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </main>
        <?php
        $sideNav  = $navFlat ?? [];
        $hot      = $rel; // 详情页侧栏热门用「相关阅读」
        $curCatId = (int)($row['cat_id'] ?? 0);
        require __DIR__ . '/side_col.php';
        ?>
    </div>
</section>
