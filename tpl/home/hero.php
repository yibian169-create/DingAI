<?php /** 首页：首屏 Hero（支持多图轮播 · 可配置）
 * 配置（首页 DIY → 首屏 Hero）：
 *   - kicker / title / sub / btn_text / btn_url / tags
 *   - images：轮播图，每行一个图片地址（可留空 = 用渐变背景 + 原 AI 对话卡）
 *   - slider_interval：自动轮播间隔秒数（默认 5）
 */
$hm = [];
if (!empty($settings['home_modules'])) { $hm = json_decode($settings['home_modules'], true) ?: []; }
$h = $hm['hero'] ?? [];
$kicker = $h['kicker'] ?? '代码全部开源 · AI 加持';
$title  = $h['title']  ?? '让万千工厂和品牌商<br>拥有 <em>好用的官方网站</em>';
$sub    = $h['sub']    ?? '全自动文章 SEO × GEO，降本增效。《得应盯建站系统》通过 GEO 优化，尽量让大语言模型主动引用你的内容。';
$btnText= $h['btn_text'] ?? '开源下载';
$btnUrl = $h['btn_url'] ?? '#';
$tags   = $h['tags'] ?? ['AIGC 自动成文','GEO 生成式优化','RAG 知识库','大模型 获客'];
$sliderInterval = max(2, (int)($h['slider_interval'] ?? 5));

/* 轮播图解析：支持 每行一个URL / 逗号分隔 / JSON 数组 */
$images = [];
$imgsRaw = $h['images'] ?? '';
if (is_array($imgsRaw)) {
    $images = array_values(array_filter(array_map('trim', $imgsRaw)));
} elseif (is_string($imgsRaw) && trim($imgsRaw) !== '') {
    $dec = json_decode($imgsRaw, true);
    if (is_array($dec)) { $images = array_values(array_filter(array_map('trim', $dec))); }
    else { $images = array_values(array_filter(array_map('trim', preg_split('/[\r\n,]+/', $imgsRaw)))); }
}
$hasSlider = count($images) > 0;
?>
<section class="q-hero"<?= $hasSlider ? ' data-slider="1" data-interval="' . $sliderInterval . '"' : '' ?>>
  <?php if ($hasSlider): ?>
  <!-- 轮播图（多张背景） -->
  <div class="q-hero__slider" id="heroSlider">
    <?php foreach ($images as $i => $img): ?>
    <div class="q-hero__slide<?= $i === 0 ? ' active' : '' ?>" style="background-image:url('<?= e($img) ?>')"></div>
    <?php endforeach; ?>
  </div>
  <div class="q-hero__mask"></div>
  <?php if (count($images) > 1): ?>
  <div class="q-hero__dots" id="heroDots">
    <?php foreach ($images as $i => $img): ?><button type="button" data-i="<?= $i ?>" class="<?= $i === 0 ? 'on' : '' ?>" aria-label="第<?= $i + 1 ?>张"></button><?php endforeach; ?>
  </div>
  <?php endif; ?>
  <?php else: ?>
  <canvas id="net"></canvas>
  <div class="q-hero__bg"></div>
  <div class="q-grid-overlay"></div>
  <div class="glow glow--1"></div><div class="glow glow--2"></div><div class="glow glow--3"></div>
  <div class="scanline"></div>
  <?php endif; ?>
  <div class="q-container q-hero__inner">
    <div class="q-hero__copy q-reveal">
      <span class="q-kicker"><?= $kicker ?></span>
      <h1 class="q-hero__title"><?= $title ?></h1>
      <p class="q-hero__sub"><?= $sub ?></p>
      <div class="q-hero__cta">
        <a class="q-btn q-btn--grad" href="<?= e($btnUrl) ?>"><?= e($btnText) ?></a>
      </div>
      <div class="q-hero__tags">
        <?php foreach ($tags as $t): ?><span><b><?= e($t) ?></b></span><?php endforeach; ?>
      </div>
    </div>
    <?php if (!$hasSlider): ?>
    <div class="q-stage q-reveal d2">
      <div class="q-card-float">
        <div class="aicard__head">
          <span class="dots"><i></i><i></i><i></i></span>
          <span><span class="live"></span> 得应盯 AI 助手</span>
        </div>
        <div class="aicard__body" id="chat">
          <div class="chat-row user"><div class="ava u">我</div><div class="bubble">请帮我对这篇文章做 GEO 优化，让 AI 搜索主动引用。</div></div>
          <div class="chat-row user" id="articleRow"><div class="ava u">我</div><div class="bubble"><div class="article-card"><div class="article-card__head"><span>📄 文章</span><small>工业传感器选型指南</small></div><div class="article-card__excerpt">选型需综合考虑量程、精度、输出信号、环境耐受性等关键参数。热电偶、热电阻、压力传感器各有适用场景……</div><div class="article-card__scan"></div></div></div></div>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </div>
</section>
