<?php /** 首页：底部 CTA（可配置） */
$hm = [];
if (!empty($settings['home_modules'])) { $hm = json_decode($settings['home_modules'], true) ?: []; }
$c = $hm['cta'] ?? [];
$title   = $c['title']    ?? '降本增效，每个老板都想要';
$sub     = $c['sub']      ?? '无需特别懂技术。下载软件包，直接部署宝塔，<strong>100% 代码开源免费商用</strong>。';
$btnText = $c['btn_text'] ?? '开源下载';
$btnUrl  = $c['btn_url']  ?? '#';
?>
<section class="q-section">
  <div class="q-container">
    <div class="q-cta q-reveal">
      <h2><?= e($title) ?></h2>
      <p><?= $sub ?></p>
      <div class="q-cta__row">
        <a class="q-btn q-btn--grad" href="<?= e($btnUrl) ?>"><?= e($btnText) ?></a>
      </div>
    </div>
  </div>
</section>
