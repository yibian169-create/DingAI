<?php /** 首页：电商孵化 · 一对一解答 */
$hm = []; if (!empty($settings['home_modules'])) { $hm = json_decode($settings['home_modules'], true) ?: []; }
$c = $hm['capabilities'] ?? [];
$kicker  = $c['kicker']   ?? 'ECOMMERCE INCUBATION';
$title   = $c['title']    ?? '电商孵化 · <em>一对一解答</em>';
$desc    = $c['desc']     ?? '不止是学习电商能力。更重要的是，你拥有了一个 <em>17 年电商经历</em>、可以随时【交谈】的朋友。从选品到内容，从流量到转化，都有真人导师陪你落地。';
$ctaText = $c['cta_text'] ?? '报名导师一对一孵化';
$ctaUrl  = $c['cta_url']  ?? '#';
?>
<section class="q-section">
  <div class="q-container">
    <div class="head-row q-reveal">
      <div>
        <span class="q-kicker"><?= e($kicker) ?></span>
        <h2 class="q-title"><?= $title ?></h2>
        <p class="q-desc"><?= $desc ?></p>
      </div>
    </div>
    <div class="q-feat__grid">
      <div class="q-feat q-reveal">
        <div class="q-feat__idx">01</div>
        <div class="q-feat__ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg></div>
        <h3>导师一对一诊断</h3>
        <p>导师先看店铺、看产品、看数据，找到你真正的卡点，而不是给你一套通用课程。</p>
      </div>
      <div class="q-feat q-reveal d1">
        <div class="q-feat__idx">02</div>
        <div class="q-feat__ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4H6z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg></div>
        <h3>选品与供应链</h3>
        <p>17 年电商实战沉淀的选品逻辑，帮你判断：什么能做、什么不能碰、利润在哪里。</p>
      </div>
      <div class="q-feat q-reveal d2">
        <div class="q-feat__idx">03</div>
        <div class="q-feat__ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg></div>
        <h3>AI 测图 · 点击率优化</h3>
        <p>用 AI 批量跑主图、测点击、找高转化方向，让每一分推广费都花得更值。</p>
      </div>
      <div class="q-feat q-reveal">
        <div class="q-feat__idx">04</div>
        <div class="q-feat__ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg></div>
        <h3>批量生图 / 视频</h3>
        <p>SKU 主图、详情页、短视频批量生成，低至 0.1 元/张，几毛钱算力做 AI 视频。</p>
      </div>
      <div class="q-feat q-reveal d1">
        <div class="q-feat__idx">05</div>
        <div class="q-feat__ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg></div>
        <h3>行业专用 AI 工作流</h3>
        <p>150+ 电商 AI 工作流覆盖自动短剧、客服话术、标题优化等场景，拿来就能跑。</p>
      </div>
      <div class="q-feat q-reveal d2">
        <div class="q-feat__idx">06</div>
        <div class="q-feat__ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg></div>
        <h3>长期陪跑 · 朋友式交流</h3>
        <p>不是上完课就结束。遇到问题随时问，导师像朋友一样帮你拆解，陪你把结果做出来。</p>
      </div>
    </div>
    <div class="q-feat__cta q-reveal">
      <a class="q-btn q-btn--grad" href="<?= e($ctaUrl) ?>"><?= e($ctaText) ?></a>
    </div>
  </div>
</section>
