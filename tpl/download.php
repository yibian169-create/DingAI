<?php /** 前台：下载专区 */
$S = $settings;
$cats = $cats ?? [];
$items = $items ?? [];
$curCat = $curCat ?? 0;
$desc = $desc ?? '';
?>
<section class="q-section q-download">
  <div class="q-container q-reveal">
    <div class="head-row">
      <div>
        <span class="q-kicker">DOWNLOAD ZONE</span>
        <h2 class="q-title">下载专区 · <em>源码 / 模板 / 工具包</em></h2>
        <div class="q-desc q-download__topdesc"><?= $desc ? $desc : '<p>精选开源源码、建站模板与实用工具，点击即可下载。</p><p>咱们源码 <b>开源 · 可商用</b>，欢迎学习与二次开发。</p>' ?></div>
      </div>
    </div>

    <?php if (!empty($cats)): ?>
    <div class="q-download__tabs">
      <a href="index.php?act=download" class="q-download__tab <?= $curCat == 0 ? 'is-active' : '' ?>">全部</a>
      <?php foreach ($cats as $c): ?>
        <a href="index.php?act=download&cat=<?= (int)$c['id'] ?>" class="q-download__tab <?= $curCat == (int)$c['id'] ? 'is-active' : '' ?>"><?= e($c['name']) ?></a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if (empty($items)): ?>
      <div class="q-download__empty">暂无下载资源，敬请期待。</div>
    <?php else: ?>
    <div class="q-download__grid">
      <?php foreach ($items as $d):
        $ext = strtolower($d['file_ext'] ?: pathinfo($d['file_url'] ?: '', PATHINFO_EXTENSION));
        $cover = $d['cover'] ?: '';
      ?>
      <article class="q-download__card">
        <div class="q-download__cover">
          <?php if ($cover): ?>
            <img src="<?= e($cover) ?>" alt="<?= e($d['title']) ?>">
          <?php else: ?>
            <div class="q-download__ph">📦</div>
          <?php endif; ?>
          <?php if ($ext): ?><span class="q-download__ext">.<?= e($ext) ?></span><?php endif; ?>
        </div>
        <div class="q-download__body">
          <div class="q-download__top">
            <h3 class="q-download__title"><?= e($d['title']) ?></h3>
            <?php if ($d['version']): ?><span class="q-download__ver"><?= e($d['version']) ?></span><?php endif; ?>
          </div>
          <?php if ($d['cat_name']): ?><div class="q-download__cat"><?= e($d['cat_name']) ?></div><?php endif; ?>
          <?php if ($d['summary']): ?><p class="q-download__summary"><?= e($d['summary']) ?></p><?php endif; ?>
          <?php if ($d['description']): ?><p class="q-download__desc"><?= mb_substr(strip_tags($d['description']), 0, 80) ?><?= mb_strlen(strip_tags($d['description'])) > 80 ? '…' : '' ?></p>
          <details class="q-download__detail">
            <summary>📖 下载说明 / 安装步骤</summary>
            <div class="q-download__fulldesc"><?= $d['description'] ?></div>
          </details>
          <?php endif; ?>
          <div class="q-download__foot">
            <div class="q-download__meta">
              <span class="q-download__count">⬇ <?= (int)$d['downloads'] ?> 次下载</span>
              <?php if ($d['file_size']): ?><span class="q-download__size"><?= e($d['file_size']) ?></span><?php endif; ?>
            </div>
            <a class="q-download__btn" href="index.php?act=dl&id=<?= (int)$d['id'] ?>">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
              <span>立即下载</span>
            </a>
          </div>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>
