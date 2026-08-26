<?php /** 首页：数据统计（可配置 4 组） */
$hm = [];
if (!empty($settings['home_modules'])) { $hm = json_decode($settings['home_modules'], true) ?: []; }
$s = $hm['stats'] ?? [];
$defs = [
  1 => ['num' => '100', 'suffix' => '%', 'label' => '企业官网AI版 · 代码开源免费商用'],
  2 => ['num' => '81',  'suffix' => '%', 'label' => '增加 AI 引擎引用概率|AI 时代 降本增效'],
  3 => ['num' => '150', 'suffix' => '+', 'label' => '电商 + AI 一对一孵化|可用 150+ AI 应用'],
  4 => ['num' => '80',  'suffix' => '%', 'label' => '企业 AI 数字员工|效率提升 80%'],
];
$items = [];
foreach ($defs as $i => $d) {
    // 优先读可视化编辑器的模块配置，未配置时回退到「主题设置」的 statN / statN_label
    $items[] = [
        'num'    => $s['stat' . $i . '_num']    ?? ($settings['stat' . $i]          ?? $d['num']),
        'suffix' => $s['stat' . $i . '_suffix'] ?? $d['suffix'],
        'label'  => $s['stat' . $i . '_label']  ?? ($settings['stat' . $i . '_label'] ?? $d['label']),
    ];
}
?>
<section class="q-section q-stats">
  <div class="q-container">
    <div class="q-stats__grid">
      <?php foreach ($items as $idx => $it): ?>
      <div class="q-stat q-reveal<?= $idx ? ' d' . $idx : '' ?>">
        <div class="q-stat__num"><em data-count="<?= e($it['num']) ?>">0</em><?= e($it['suffix']) ?></div>
        <div class="q-stat__label"><?= str_replace('|', '<br>', e($it['label'])) ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
