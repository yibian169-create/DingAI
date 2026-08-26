<?php /** 首页：工艺引语（家纺风）
 * 配置（首页 DIY → 工艺引语）：text / by
 */
$hm = []; if (!empty($settings['home_modules'])) { $hm = json_decode($settings['home_modules'], true) ?: []; }
$q = $hm['quote'] ?? [];
$text = $q['text'] ?? '“好的家纺不该被看见，它只是让每个夜晚，都更柔软一点。”';
$by   = $q['by']   ?? '— 棉叙制造 · 工艺哲学';
?>
<section class="ht-quote"><div class="ht-quote__inner q-reveal">
  <blockquote><?= $text ?></blockquote>
  <div class="ht-quote__by"><?= e($by) ?></div>
</div></section>
