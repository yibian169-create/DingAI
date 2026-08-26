<?php /** 首页：布料拼贴 Hero（家纺风 · 不对称分栏 + 悬浮图卡）
 * 配置（首页 DIY → 布料拼贴 Hero）：
 *   - kicker / title(支持 <br>) / sub / btn_text / btn_url / badge
 *   - trust：信任徽章，每行 "主|副"，用 | 分隔
 *   - images：拼贴图卡，取前 3 张（每行一个地址 / 逗号分隔 / JSON 数组）
 */
$hm = []; if (!empty($settings['home_modules'])) { $hm = json_decode($settings['home_modules'], true) ?: []; }
$b = $hm['board'] ?? [];
$kicker = $b['kicker'] ?? 'Home Textile Manufacturer · 家纺智造';
$title  = $b['title']  ?? '把自然的柔软，<br>织进<em>每一个家</em>';
$sub    = $b['sub']    ?? '从一根纱线到一床被褥。我们为家居品牌提供床品、毛巾、窗帘与软装布草的 OEM / ODM 一站式制造。';
$btnText= $b['btn_text'] ?? '获取报价方案';
$btnUrl = $b['btn_url'] ?? '#';
$badge  = $b['badge']  ?? '100% 天然材质';
$trustRaw = $b['trust'] ?? "OEKO-TEX|国际环保认证\nGOTS|有机纺织品标准\n25 年|家纺制造经验";
$trust = [];
foreach (explode("\n", $trustRaw) as $line) {
  $line = trim($line); if ($line === '') continue;
  $p = explode('|', $line, 2);
  $trust[] = ['b' => $p[0], 's' => $p[1] ?? ''];
}
$imgsRaw = $b['images'] ?? [];
if (is_string($imgsRaw)) { $imgsRaw = array_filter(array_map('trim', preg_split('/[\r\n,]+/', $imgsRaw))); }
$imgs = array_values(array_slice((array)$imgsRaw, 0, 3));
while (count($imgs) < 3) { $imgs[] = ''; }
?>
<section class="ht-board">
  <div class="ht-board__inner">
    <div class="ht-board__copy q-reveal">
      <span class="ht-kick"><?= $kicker ?></span>
      <h1 class="ht-board__title"><?= $title ?></h1>
      <div class="ht-rule"></div>
      <p class="ht-board__sub"><?= e($sub) ?></p>
      <div class="ht-board__cta">
        <a class="ht-btn ht-btn--p" href="<?= e($btnUrl) ?>"><?= e($btnText) ?></a>
      </div>
      <div class="ht-board__trust">
        <?php foreach ($trust as $t): ?><div><b><?= e($t['b']) ?></b><span><?= e($t['s']) ?></span></div><?php endforeach; ?>
      </div>
    </div>
    <div class="ht-board__stage q-reveal">
      <?php foreach ($imgs as $i => $img): ?>
      <div class="ht-card ht-card--<?= $i + 1 ?>"><?php if ($img): ?><img src="<?= e($img) ?>" alt=""><?php endif; ?></div>
      <?php endforeach; ?>
      <div class="ht-badge"><b><?= e($badge) ?></b></div>
    </div>
  </div>
</section>
