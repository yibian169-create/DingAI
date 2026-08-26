<?php /** 首页：场景方案 · 电商孵化（17年实战 · 150+ AI 工作流 · 像素人吃蘑菇变大） */
$hm = []; if (!empty($settings['home_modules'])) { $hm = json_decode($settings['home_modules'], true) ?: []; }
$s = $hm['scenario'] ?? [];
$kicker  = $s['kicker']    ?? '17年电商经历 · 150+ AI 工作流应用';
$title   = $s['title']     ?? '17年电商经历<br><em>为商家降本增效</em>';
$desc    = $s['desc']      ?? '批量生图低至0.1元/张，AI测图点击率优化；AI视频极致算力优化至几毛钱；行业专用AI工作流，覆盖AI自动短剧等150个工作流应用。17年电商实战经验，为商家真正降本增效。';
$ctaText = $s['cta_text']  ?? '我要学习使用';
$ctaUrl  = $s['cta_url']   ?? '#';
?>
<section class="q-section q-scenario q-scenario--mirror">
  <div class="glow glow--2" style="opacity:.35;bottom:-100px;left:-80px"></div>
  <div class="q-container q-scenario__grid q-reveal">
    <div class="q-scenario__copy">
      <span class="q-scenario__no">SCENARIO 01 · 电商孵化</span>
      <span class="q-kicker"><?= e($kicker) ?></span>
      <h2 class="q-title"><?= $title ?></h2>
      <p class="q-desc"><?= e($desc) ?></p>
      <div class="q-scenario__pills"><span class="q-pill">17年实战</span><span class="q-pill">批量生图</span><span class="q-pill">低至0.1元</span><span class="q-pill">AI测图</span><span class="q-pill">AI视频</span><span class="q-pill">AI短剧</span><span class="q-pill">150+工作流</span><span class="q-pill">降本增效</span></div>
      <div class="q-scenario__points">
        <div class="q-point"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M20 6L9 17l-5-5"/></svg> <b>0.1元</b>批量生图</div>
        <div class="q-point"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M20 6L9 17l-5-5"/></svg> <b>AI</b>测图点击率优化</div>
        <div class="q-point"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M20 6L9 17l-5-5"/></svg> AI视频<b>几毛钱算力</b></div>
        <div class="q-point"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M20 6L9 17l-5-5"/></svg> <b>150+</b>行业专用AI工作流</div>
      </div>
      <div class="q-hero__cta"><a class="q-btn q-btn--grad" href="<?= e($ctaUrl) ?>"><?= e($ctaText) ?></a></div>
    </div>
    <div class="q-reveal d2">
      <div class="pixel-mario-scene" id="pixelMarioScene">
        <div class="mario-orbit"></div>
        <div class="mario-ground"></div>
        <div class="mario-poof" id="marioPoof"></div>
        <div class="mario-hero" id="marioHero">
          <svg viewBox="0 0 224 256" aria-label="像素电商人">
            <g id="marioPixels">
              <rect x="70" y="20" width="84" height="14" fill="#e879f9"/>
              <rect x="56" y="34" width="112" height="14" fill="#e879f9"/>
              <rect x="56" y="48" width="112" height="14" fill="#e879f9"/>
              <rect x="70" y="62" width="84" height="14" fill="#22d3ee"/>
              <rect x="56" y="76" width="112" height="14" fill="#22d3ee"/>
              <rect x="56" y="90" width="112" height="14" fill="#22d3ee"/>
              <rect x="56" y="104" width="112" height="14" fill="#22d3ee"/>
              <rect x="84" y="90" width="14" height="14" fill="#050a16"/>
              <rect x="126" y="90" width="14" height="14" fill="#050a16"/>
              <rect x="42" y="118" width="140" height="14" fill="#818cf8"/>
              <rect x="42" y="132" width="140" height="14" fill="#818cf8"/>
              <rect x="42" y="146" width="140" height="14" fill="#818cf8"/>
              <rect x="42" y="160" width="140" height="14" fill="#818cf8"/>
              <rect x="56" y="174" width="112" height="14" fill="#818cf8"/>
              <rect x="56" y="188" width="112" height="14" fill="#818cf8"/>
              <rect x="105" y="132" width="14" height="14" fill="#22d3ee"/>
              <rect x="91" y="146" width="14" height="14" fill="#22d3ee"/>
              <rect x="119" y="146" width="14" height="14" fill="#22d3ee"/>
              <rect x="98" y="160" width="28" height="14" fill="#22d3ee"/>
              <rect x="28" y="132" width="14" height="56" fill="#22d3ee"/>
              <rect x="182" y="132" width="14" height="56" fill="#22d3ee"/>
              <rect x="70" y="202" width="28" height="14" fill="#1e293b"/>
              <rect x="126" y="202" width="28" height="14" fill="#1e293b"/>
              <rect x="70" y="216" width="28" height="14" fill="#1e293b"/>
              <rect x="126" y="216" width="28" height="14" fill="#1e293b"/>
            </g>
          </svg>
          <div class="mario-label" id="marioLabel">电商小白</div>
        </div>
        <div class="mario-mushroom" style="--x:50%;--y:13%" data-stage="1"><div class="mush-cap">17年经验</div><div class="mush-stem"></div></div>
        <div class="mario-mushroom" style="--x:86%;--y:36%" data-stage="2"><div class="mush-cap">批量生图</div><div class="mush-stem"></div></div>
        <div class="mario-mushroom" style="--x:72%;--y:78%" data-stage="3"><div class="mush-cap">AI测图</div><div class="mush-stem"></div></div>
        <div class="mario-mushroom" style="--x:28%;--y:78%" data-stage="4"><div class="mush-cap">AI视频</div><div class="mush-stem"></div></div>
        <div class="mario-mushroom" style="--x:14%;--y:36%" data-stage="5"><div class="mush-cap">150工作流</div><div class="mush-stem"></div></div>
      </div>
    </div>
  </div>
</section>
