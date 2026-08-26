<?php /** 首页：竖排时间线 · 四道工序（家纺风）
 * 配置（首页 DIY → 制造流程）：kicker / title + items 数组
 *   每项 {num, heading, text}
 */
$hm = []; if (!empty($settings['home_modules'])) { $hm = json_decode($settings['home_modules'], true) ?: []; }
$t = $hm['timeline'] ?? [];
$kicker = $t['kicker'] ?? 'How We Craft';
$title  = $t['title']  ?? '四道工序，<em>慢工出细活</em>';
$items  = $t['items'] ?? [];
if (!is_array($items) || !$items) {
  $items = [
    ['num' => '01', 'heading' => '选料与纺纱', 'text' => '甄选天然纤维，精梳纺纱，确保纱线均匀度与亲肤触感。'],
    ['num' => '02', 'heading' => '织造与印染', 'text' => '宽幅织机量产，环保活性染色，色牢度稳定达 4 级以上。'],
    ['num' => '03', 'heading' => '裁剪缝制', 'text' => '数字化裁剪与缝制产线，支持床品、毛巾、窗帘多品类共线。'],
    ['num' => '04', 'heading' => '质检与交付', 'text' => 'AQL 全检 + 出口包装，60 国稳定交期，全球直发。'],
  ];
}
?>
<section class="ht-timeline">
  <div class="ht-timeline__inner">
    <div class="ht-sec-head q-reveal">
      <span class="ht-kick"><?= $kicker ?></span>
      <h2 class="ht-sec-title"><?= $title ?></h2>
    </div>
    <div class="ht-tl q-reveal">
      <?php foreach ($items as $it): ?>
      <div class="ht-tl__it">
        <h4><?= e($it['num'] ?? '') ?> · <?= e($it['heading'] ?? '') ?></h4>
        <p><?= e($it['text'] ?? '') ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
