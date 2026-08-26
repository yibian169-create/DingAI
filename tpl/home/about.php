<?php /** 首页：关于我们（AI 员工 · 多流程复杂数据任务） */
$hm = []; if (!empty($settings['home_modules'])) { $hm = json_decode($settings['home_modules'], true) ?: []; }
$a = $hm['about'] ?? [];
$kicker  = $a['kicker']   ?? 'AI DIGITAL WORKFORCE';
$title   = $a['title']    ?? 'AI 员工<br>处理 <em>多流程复杂数据任务</em>';
// 优先读可视化编辑器配置，未配置时回退到「主题设置」的关于文案
$desc    = $a['desc']     ?? ($settings['about_text'] ?? '像雇佣一名 7×24 小时在线的数据专员，得应盯 AI 员工能自动完成数据上传、解析、标准化、汇总分析、报告生成与问题闭环，把重复繁琐的数据工作交给 AI。');
$ctaText = $a['cta_text'] ?? '了解 AI 员工能力';
$ctaUrl  = $a['cta_url']  ?? '#';
?>
<section class="q-section">
  <div class="q-container q-split">
    <div class="q-reveal">
      <span class="q-kicker"><?= e($kicker) ?></span>
      <h2 class="q-title"><?= $title ?></h2>
      <p class="q-desc"><?= e($desc) ?></p>
      <ul class="q-split__list">
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg> 数据上传与智能解析：Excel / CSV 批量上传，自动识别文件与问题类型</li>
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg> 单位标准化管理：自动映射标准单位，支持人工干预与历史追溯</li>
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg> 数据汇总与分析：多维度汇总，自动计算问题率与分布趋势</li>
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg> 专业报告生成：基于模板自动生成 Excel 报告，支持自定义与批量导出</li>
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg> 问题闭环管理：识别未整改问题，生成整改报告并跟踪进度</li>
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg> 系统管理：文件、用户、权限、日志一站式管理</li>
      </ul>
      <a class="q-btn q-btn--grad" href="<?= e($ctaUrl) ?>"><?= e($ctaText) ?></a>
    </div>
    <div class="q-visual-panel q-reveal d2" id="aiFlowPanel">
      <div class="grid"></div><div class="halo"></div>
      <div class="q-visual-chip q-visual-chip--t"><b>AI</b><small>7×24h 数字员工</small></div>
      <div class="q-visual-chip q-visual-chip--b"><b>80%</b><small>数据处理效率提升</small></div>
      <div class="ai-flow" id="aiFlow">
        <div class="ai-flow__track"><div class="ai-flow__signal"></div><div class="ai-flow__pulse"></div></div>
        <div class="ai-flow__node" data-step="0"><div class="num">01</div><div class="body"><div class="ttl">工单数据自动采集与解析</div><div class="desc">自动登录系统，按条件采集关键数据，非侵入式，合规安全。</div></div></div>
        <div class="ai-flow__node" data-step="1"><div class="num">02</div><div class="body"><div class="ttl">超时预警智能研判</div><div class="desc">自动比对流转时长与标准时限，生成预警清单与异常环节。</div></div></div>
        <div class="ai-flow__node" data-step="2"><div class="num">03</div><div class="body"><div class="ttl">进度跟踪与闭环管理</div><div class="desc">定时复核完成情况，动态更新状态，构建完整管理闭环。</div></div></div>
        <div class="ai-flow__node" data-step="3"><div class="num">04</div><div class="body"><div class="ttl">数字员工管理监测看板</div><div class="desc">任务配置、参数设置、运行监控及日志管理，可视化操作。</div></div></div>
      </div>
    </div>
  </div>
</section>
