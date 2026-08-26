<?php /** 首页：服务流程（四步，官网开始自己干活） */
$hm = []; if (!empty($settings['home_modules'])) { $hm = json_decode($settings['home_modules'], true) ?: []; }
$w = $hm['workflow'] ?? [];
$kicker = $w['kicker'] ?? 'HOW IT WORKS';
$title  = $w['title']  ?? '四步，官网开始 <em>自己干活</em>';
?>
<section class="q-section">
  <div class="q-container">
    <div class="head-row q-reveal">
      <div>
        <span class="q-kicker"><?= e($kicker) ?></span>
        <h2 class="q-title"><?= $title ?></h2>
      </div>
    </div>
    <div class="q-flow">
      <div class="q-flow__step q-reveal">
        <div class="q-flow__num">01</div>
        <h3>AI 搭站</h3>
        <p>描述业务，自动生成官网框架与栏目。</p>
      </div>
      <div class="q-flow__step q-reveal d1">
        <div class="q-flow__num">02</div>
        <h3>内容生成</h3>
        <p>大模型定时撰写并发布行业文章与产品页。</p>
      </div>
      <div class="q-flow__step q-reveal d2">
        <div class="q-flow__num">03</div>
        <h3>GEO 优化</h3>
        <p>整理为 AI 引擎偏好的权威问答资产。</p>
      </div>
      <div class="q-flow__step q-reveal d3">
        <div class="q-flow__num">04</div>
        <h3>自动获客</h3>
        <p>被 AI 引用 + 留资表单，线索自动回流。</p>
      </div>
    </div>
  </div>
</section>
