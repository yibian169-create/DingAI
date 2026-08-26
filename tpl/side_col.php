<?php
/**
 * 通用左侧栏片段（左右结构右侧栏复用）
 * 可用变量：$sideNav(array 栏目), $hot(array 热门), $phone, $curCatId, $type, $cityLabel
 * 调用方式：require __DIR__ . '/side_col.php';
 */
$sideNav  = $sideNav ?? ($navFlat ?? []);
$hot      = $hot ?? [];
$curCatId = $curCatId ?? (isset($cat['id']) ? (int)$cat['id'] : 0);
$type     = $type ?? 'article';
$phone    = !empty($phone) ? $phone : (isset($S) && !empty($S['phone']) ? $S['phone'] : '18732237111');
?>
<aside class="q-sidebar">
    <?php if ($sideNav): ?>
    <div class="q-side__block">
        <h3 class="q-side__title"><i></i>栏目导航</h3>
        <nav class="q-side__list">
            <?php foreach ($sideNav as $v): ?>
            <?php $sidActive = (int)($v['id'] ?? 0) === $curCatId; ?>
            <a class="<?= $sidActive ? 'is-active' : '' ?>" href="index.php?act=list&cat=<?= (int)($v['id'] ?? 0) ?>"><?= e($v['name'] ?? '') ?></a>
            <?php endforeach; ?>
        </nav>
    </div>
    <?php endif; ?>
    <?php if ($hot): ?>
    <div class="q-side__block">
        <h3 class="q-side__title"><i></i>热门推荐</h3>
        <div class="q-side__hot">
            <?php foreach ($hot as $i => $h): ?>
            <a href="index.php?act=detail&type=<?= e($type) ?>&id=<?= (int)$h['id'] ?>">
                <span class="rank"><?= $i + 1 ?></span>
                <span><?= e(($cityLabel ?? '') . ($h['title'] ?? '')) ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
    <div class="q-side__block q-side__contact">
        <h3 class="q-side__title"><i></i>免费咨询</h3>
        <div class="num"><?= e($phone) ?></div>
        <p>工作日 9:00-18:00，专业顾问一对一解答。</p>
        <a class="q-btn q-btn--grad q-btn--sm" href="tel:<?= e($phone) ?>">立即咨询</a>
    </div>
</aside>
