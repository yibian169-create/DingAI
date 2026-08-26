<?php /** 首页：材质故事 交替分屏（家纺风）
 * 配置（首页 DIY → 材质故事）：kicker / title + items 数组
 *   每项 {img, heading, text, stat_num, stat_label, reverse(0/1)}
 */
$hm = []; if (!empty($settings['home_modules'])) { $hm = json_decode($settings['home_modules'], true) ?: []; }
$s = $hm['story'] ?? [];
$kicker = $s['kicker'] ?? 'Material Story';
$title  = $s['title']  ?? '一根纱的<em>漫长旅程</em>';
$items  = $s['items'] ?? [];
if (!is_array($items) || !$items) {
  $items = [
    ['img' => '', 'heading' => '从田间到织机', 'text' => '我们只选用长绒棉与欧洲亚麻，自然种植、低耗水加工。每一束纤维都经过精梳与质检，确保触感与耐久兼得。', 'stat_num' => '60+', 'stat_label' => '合作棉田 / 麻园', 'reverse' => 0],
    ['img' => '', 'heading' => '慢工艺，不妥协', 'text' => '从织造到印染、从缝制到整烫，关键工序全程数字化排产与人工复检，让每一件出厂产品都经得起触摸。', 'stat_num' => '48h', 'stat_label' => '快速打样', 'reverse' => 1],
  ];
}
?>
<section class="ht-story">
  <div class="ht-story__inner">
    <div class="ht-sec-head q-reveal">
      <span class="ht-kick"><?= $kicker ?></span>
      <h2 class="ht-sec-title"><?= $title ?></h2>
    </div>
    <?php foreach ($items as $it):
      $rev = !empty($it['reverse']) ? ' rev' : '';
      $img = $it['img'] ?? ''; $h = $it['heading'] ?? ''; $tx = $it['text'] ?? '';
      $n = $it['stat_num'] ?? ''; $l = $it['stat_label'] ?? '';
    ?>
    <div class="ht-split q-reveal<?= $rev ?>">
      <div class="ht-split__pic"><?php if ($img): ?><img src="<?= e($img) ?>" alt="<?= e($h) ?>"><?php endif; ?></div>
      <div class="ht-split__txt">
        <h3><?= e($h) ?></h3>
        <p><?= e($tx) ?></p>
        <?php if ($n): ?><div class="ht-stat-row"><div><b><?= e($n) ?></b><span><?= e($l) ?></span></div></div><?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</section>
