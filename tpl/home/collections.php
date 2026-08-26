<?php /** 首页：空间系列横向滑动（家纺风）
 * 配置（首页 DIY → 空间系列）：kicker / title + items 数组（每项为 {img,title,sub}）
 * items 通过「首页 DIY → 粘贴 home.json」整体写入；此处提供默认占位。
 */
$hm = []; if (!empty($settings['home_modules'])) { $hm = json_decode($settings['home_modules'], true) ?: []; }
$c = $hm['collections'] ?? [];
$kicker = $c['kicker'] ?? 'Explore by Space';
$title  = $c['title']  ?? '为每一种<em>生活空间</em>而生';
$items  = $c['items'] ?? [];
if (!is_array($items) || !$items) {
  $items = [
    ['img' => '', 'title' => '卧室', 'sub' => 'BEDROOM'],
    ['img' => '', 'title' => '浴室', 'sub' => 'BATH'],
    ['img' => '', 'title' => '客厅', 'sub' => 'LIVING'],
    ['img' => '', 'title' => '儿童房', 'sub' => 'KIDS'],
    ['img' => '', 'title' => '酒店布草', 'sub' => 'HOTEL'],
  ];
}
?>
<section class="ht-coll">
  <div class="ht-coll__inner">
    <div class="ht-sec-head q-reveal">
      <span class="ht-kick"><?= $kicker ?></span>
      <h2 class="ht-sec-title"><?= $title ?></h2>
    </div>
    <div class="ht-scroller">
      <?php foreach ($items as $it):
        $img = $it['img'] ?? ''; $t = $it['title'] ?? ''; $s = $it['sub'] ?? '';
      ?>
      <div class="ht-tile">
        <?php if ($img): ?><img src="<?= e($img) ?>" alt="<?= e($t) ?>"><?php endif; ?>
        <div class="ht-tile__lab"><?= e($t) ?><small><?= e($s) ?></small></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
