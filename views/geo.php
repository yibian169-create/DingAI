<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>GEO 中心 - 得应盯后台</title>
<link rel="stylesheet" href="static/css/admin.css">
<script>
(function(){try{var t=localStorage.getItem('dy_theme')||'light';document.documentElement.setAttribute('data-theme',t);}catch(e){}})();
</script>
<style>
/* ===== Tabs ===== */
.tabs{display:flex;gap:8px;margin:18px 0 16px;flex-wrap:wrap}
.tab-btn{cursor:pointer;border:1px solid var(--line);background:var(--card);color:var(--muted);border-radius:10px;padding:10px 18px;font-size:14px;font-weight:500;transition:.15s}
.tab-btn:hover{border-color:var(--primary);color:var(--primary)}
.tab-btn.active{background:var(--primary);color:#fff;border-color:var(--primary);box-shadow:0 4px 12px rgba(99,102,241,.25)}
.tab{display:none}
.tab.active{display:block}

/* ===== Top audit banner ===== */
.geo-audit-banner{background:var(--card);border:1px solid var(--line);border-radius:18px;padding:22px 24px;display:flex;gap:26px;align-items:stretch;box-shadow:0 2px 10px rgba(0,0,0,.04)}
.geo-audit-score{display:flex;flex-direction:column;justify-content:center;align-items:center;min-width:150px;text-align:center;border-right:1px solid var(--line);padding-right:26px}
.score-circle{width:96px;height:96px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,var(--primary),var(--primary-2));color:#fff;font-size:36px;font-weight:800;box-shadow:0 6px 18px rgba(99,102,241,.28)}
.score-grade{font-size:14px;color:var(--muted);margin-top:10px;font-weight:500}
.audit-banner-items{flex:1;display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px;align-content:center}
.audit-item-h{display:flex;gap:12px;padding:12px;background:var(--card-2);border:1px solid var(--line);border-radius:12px;align-items:flex-start}
.audit-item-h .dot{width:10px;height:10px;border-radius:50%;margin-top:5px;flex:0 0 auto}
.audit-item-h .dot.ok{background:var(--ok)}.audit-item-h .dot.warn{background:var(--warn)}.audit-item-h .dot.bad{background:var(--danger)}
.audit-item-h .body{flex:1;min-width:0}
.audit-item-h .name{font-size:13.5px;font-weight:600;color:var(--text);margin-bottom:3px}
.audit-item-h .tip{font-size:12px;color:var(--muted);line-height:1.55}
.audit-item-h .act{margin-top:8px}
.audit-item-h .act a,.audit-item-h .act button{font-size:11.5px;padding:5px 11px;border-radius:6px;border:1px solid var(--primary);background:transparent;color:var(--primary);text-decoration:none;cursor:pointer}
@media(max-width:860px){
  .geo-audit-banner{flex-direction:column;padding:18px}
  .geo-audit-score{border-right:0;border-bottom:1px solid var(--line);padding:0 0 18px 0}
  .audit-banner-items{grid-template-columns:1fr}
}

/* ===== Layout ===== */
.geo-workflow{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin:18px 0}
@media(max-width:900px){.geo-workflow{grid-template-columns:1fr 1fr}}
@media(max-width:520px){.geo-workflow{grid-template-columns:1fr}}
.geo-step{background:var(--card);border:1px solid var(--line);border-radius:14px;padding:18px 16px;position:relative;overflow:hidden}
.geo-step::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,var(--primary),var(--primary-2));opacity:.7}
.geo-step .num{width:30px;height:30px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:13px;margin-bottom:12px;background:var(--primary);color:#fff}
.geo-step h4{font-size:14px;margin:0 0 7px}
.geo-step p{font-size:12px;color:var(--muted);margin:0;line-height:1.6}

.advert-box{background:linear-gradient(135deg,rgba(245,158,11,.08),rgba(249,115,22,.08));border:1px dashed var(--line);border-radius:14px;padding:18px;margin-bottom:18px}
.advert-box textarea{width:100%;min-height:84px;resize:vertical;border:1px solid var(--line);border-radius:10px;padding:11px;background:var(--card);color:var(--text);font-family:inherit;box-sizing:border-box}

.panel{background:var(--card);border:1px solid var(--line);border-radius:14px;padding:20px;margin-bottom:18px}
.panel h2{font-size:16px;margin:0 0 12px;display:flex;align-items:center;gap:8px}
.panel h3{font-size:14px;margin:0 0 10px}
.panel .muted{color:var(--muted);font-size:12px;font-weight:400}
.sub-note{font-size:12.5px;color:var(--muted);line-height:1.7;margin:6px 0 14px}

.btn{cursor:pointer;border:1px solid var(--primary);background:var(--primary);color:#fff;border-radius:8px;padding:9px 16px;font-size:13px;font-weight:500;transition:.15s}
.btn:hover{filter:brightness(1.05)}
.btn.ghost{background:transparent;color:var(--primary)}
.btn.ghost:hover{background:rgba(99,102,241,.06)}
.btn.s{font-size:12px;padding:6px 12px}
.btn.d{border-color:var(--danger);color:var(--danger);background:transparent}
.btn.d:hover{background:rgba(226,75,74,.06)}
.btn:disabled{opacity:.55;cursor:not-allowed}

.batch-box{background:var(--card-2);border:1px dashed var(--line);border-radius:14px;padding:18px;margin-bottom:18px}
.batch-box textarea{width:100%;min-height:90px;resize:vertical;border:1px solid var(--line);border-radius:10px;padding:11px;background:var(--card);color:var(--text);font-family:inherit;box-sizing:border-box}
.batch-meta{display:flex;gap:10px;align-items:center;margin-top:10px;flex-wrap:wrap}
.gen-status{padding:10px 12px;border-radius:8px;background:var(--card-2);margin-bottom:12px;display:none}
.gen-status.warn{background:rgba(245,158,11,.12)}
.gen-status.ok{background:rgba(16,185,129,.12)}

.empty-guide{text-align:center;padding:34px 20px;color:var(--muted)}
.empty-guide b{color:var(--text);display:block;margin-bottom:8px}
.empty-guide .samples{display:flex;gap:8px;justify-content:center;flex-wrap:wrap;margin-top:12px}
.empty-guide .samples span{background:var(--card-2);border:1px solid var(--line);border-radius:20px;padding:4px 12px;font-size:12px;cursor:pointer}
.empty-guide .samples span:hover{border-color:var(--primary);color:var(--primary)}

.geo-entries table{width:100%;border-collapse:collapse;font-size:13px}
.geo-entries th{text-align:left;color:var(--muted);font-weight:600;padding:10px 8px;border-bottom:2px solid var(--line);font-size:12px}
.geo-entries td{padding:12px 8px;border-bottom:1px solid var(--line);vertical-align:top}
.geo-entries .q{font-weight:600;color:var(--text)}
.geo-entries .a{font-size:12.5px;color:var(--muted);max-width:420px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.kw-tag{display:inline-block;background:var(--card-2);border:1px solid var(--line);border-radius:6px;padding:2px 8px;font-size:11.5px;margin:2px;color:var(--muted)}

.field{margin-bottom:12px}
.field label{display:block;font-size:12.5px;color:var(--muted);margin-bottom:5px}
.field input,.field textarea,.field select{width:100%;box-sizing:border-box;border:1px solid var(--line);border-radius:8px;padding:9px 11px;background:var(--card);color:var(--text);font-family:inherit}
.field textarea{min-height:70px;resize:vertical}
.switch{display:inline-flex;align-items:center;gap:7px;font-size:13px;cursor:pointer}
.acts{display:flex;gap:10px;flex-wrap:wrap;margin:14px 0}

/* ===== Monitor ===== */
.gm-head{display:flex;justify-content:space-between;align-items:center;margin:4px 0 14px}
.gm-head h2{margin:0 0 4px;font-size:18px}
.gm-note{background:linear-gradient(135deg,rgba(14,165,233,.08),rgba(99,102,241,.08));border:1px solid var(--line);border-radius:14px;padding:14px 18px;margin:0 0 18px;font-size:12.5px;color:var(--muted);line-height:1.7}
.gm-cards{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:18px}
@media(max-width:900px){.gm-cards{grid-template-columns:1fr 1fr}}
@media(max-width:520px){.gm-cards{grid-template-columns:1fr}}
.gm-card{background:var(--card);border:1px solid var(--line);border-radius:14px;padding:16px}
.gm-card .k{font-size:12.5px;color:var(--muted)}
.gm-card .v{font-size:30px;font-weight:800;margin-top:6px;color:var(--text)}
.gm-card .v small{font-size:13px;font-weight:500;color:var(--muted)}

.gm-2col{display:grid;grid-template-columns:1fr 1fr;gap:18px;align-items:start;margin-bottom:18px}
@media(max-width:980px){.gm-2col{grid-template-columns:1fr}}
.gm-panel{background:var(--card);border:1px solid var(--line);border-radius:14px;padding:18px}
.gm-panel h3{margin:0 0 10px;font-size:14px}

.trend{display:flex;align-items:flex-end;gap:8px;height:130px;padding-top:6px}
.trend .bar{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:flex-end;height:100%}
.trend .bar .col{width:100%;background:linear-gradient(180deg,var(--primary),var(--primary-2));border-radius:6px 6px 0 0;min-height:2px}
.trend .bar .d{font-size:10px;color:var(--muted);margin-top:5px}

.gm-pf{width:100%;border-collapse:collapse;font-size:12.5px}
.gm-pf td{padding:8px 6px;border-bottom:1px solid var(--line)}
.gm-pf td:last-child{text-align:right}
.gm-table{width:100%;border-collapse:collapse;font-size:12.5px}
.gm-table th{text-align:left;color:var(--muted);font-weight:600;padding:9px 6px;border-bottom:2px solid var(--line)}
.gm-table td{padding:10px 6px;border-bottom:1px solid var(--line);vertical-align:top}
.tag-cited{display:inline-block;background:rgba(16,185,129,.14);color:var(--ok);border:1px solid rgba(16,185,129,.3);border-radius:10px;padding:1px 9px;font-size:11px}
.tag-nocite{display:inline-block;background:rgba(245,158,11,.14);color:var(--warn);border:1px solid rgba(245,158,11,.3);border-radius:10px;padding:1px 9px;font-size:11px}
.tag-neg{display:inline-block;background:rgba(226,75,74,.14);color:var(--danger);border:1px solid rgba(226,75,74,.3);border-radius:10px;padding:1px 9px;font-size:11px}
.snip{color:var(--muted);max-width:340px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}

.log{max-height:200px;overflow:auto;background:var(--card-2);border:1px solid var(--line);border-radius:10px;padding:12px;font-size:12px;line-height:1.7;margin-top:10px;display:none}
.log b.ok{color:var(--ok)}.log b.no{color:var(--warn)}.log b.neg{color:var(--danger)}

/* ===== 操作说明 ===== */
.geo-guide{padding:0;overflow:hidden}
.guide-head{display:flex;justify-content:space-between;align-items:center;padding:18px 20px;cursor:pointer;background:linear-gradient(135deg,rgba(99,102,241,.06),rgba(14,165,233,.06))}
.geo-guide.collapsed .guide-head{background:var(--card)}
.guide-head h2{margin:0}
.guide-toggle{font-size:14px;color:var(--primary);transition:.2s}
.geo-guide.collapsed .guide-toggle{transform:rotate(-90deg)}
.guide-body{padding:18px 20px;display:block}
.geo-guide.collapsed .guide-body{display:none}
.guide-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:14px}
@media(max-width:900px){.guide-grid{grid-template-columns:1fr 1fr}}
@media(max-width:560px){.guide-grid{grid-template-columns:1fr}}
.guide-item{display:flex;gap:12px;background:var(--card-2);border:1px solid var(--line);border-radius:12px;padding:14px}
.guide-item .gnum{width:26px;height:26px;flex:0 0 auto;border-radius:50%;background:var(--primary);color:#fff;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700}
.guide-item .gtxt{flex:1;min-width:0}
.guide-item b{font-size:13px;color:var(--text)}
.guide-item p{font-size:12px;color:var(--muted);margin:6px 0 0;line-height:1.6}
.guide-tip{margin:14px 0 0;padding:10px 14px;background:rgba(245,158,11,.08);border:1px dashed rgba(245,158,11,.35);border-radius:10px;font-size:12.5px;color:var(--muted)}

/* ===== 客户问题词折叠面板 ===== */
.kw-head{display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:8px}
.kw-head h2{margin:0}
.kw-body{display:block;transition:.2s}
.kw-body.collapsed{display:none}
.kw-pager{display:flex;align-items:center;gap:6px;flex-wrap:wrap;margin:12px 0 0;padding-top:10px;border-top:1px solid var(--line)}
.kw-pager .pg-btn{cursor:pointer;border:1px solid var(--line);background:var(--card);color:var(--text);border-radius:6px;padding:5px 11px;font-size:12px;transition:.15s}
.kw-pager .pg-btn:hover{border-color:var(--primary);color:var(--primary)}
.kw-pager .pg-btn.active{background:var(--primary);color:#fff;border-color:var(--primary)}
.kw-pager .pg-info{font-size:12px;color:var(--muted);margin-left:auto}

/* ===== 专业度评分 ===== */
.eeat-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin:10px 0}
@media(max-width:760px){.eeat-grid{grid-template-columns:1fr 1fr}}
.eeat-dim{background:var(--card-2);border:1px solid var(--line);border-radius:10px;padding:14px 10px;text-align:center}
.eeat-dim .n{font-size:28px;font-weight:800}
.eeat-dim .l{font-size:12px;color:var(--muted);margin-top:4px}
.eeat-total{font-size:13px;color:var(--muted);margin:6px 0 10px}
.eeat-tips{font-size:12.5px;color:var(--muted);line-height:1.7;margin:0;padding-left:18px}
.code-box{width:100%;min-height:160px;font-family:var(--font-mono,monospace);font-size:12px;border:1px solid var(--line);border-radius:10px;padding:12px;background:var(--card-2);color:var(--text);box-sizing:border-box;white-space:pre;overflow:auto}
.out-box{margin-top:10px;display:none}
</style>
</head>
<body>
<div class="layout">
  <?php require __DIR__ . '/sidebar.php'; ?>
  <div class="main">
    <div class="topbar">
      <h1>🌐 GEO 中心 <span class="pill">让 DeepSeek、豆包、Kimi 在回答客户问题时，优先提到你的网站</span></h1>
      <div class="right"><span><?= e($admin_name) ?></span><form method="post" action="admin.php?m=logout" style="display:inline"><?= csrf_field() ?><button type="submit" class="logout-link">退出</button></form></div>
    </div>
    <?php if (!empty($flash)): ?><div class="msg"><?= e($flash) ?></div><?php endif; ?>

    <!-- ===== GEO 体检：顶部横版 Banner ===== -->
    <div class="geo-audit-banner">
      <div class="geo-audit-score">
        <div class="score-circle"><?= $audit['score'] ?></div>
        <div class="score-grade">综合 GEO 健康度<br><?= $audit['grade'] ?></div>
      </div>
      <div class="audit-banner-items">
        <?php
        $auditActions = [
          '文章问答小卡片' => ['href' => 'admin.php?m=articles', 'label' => '去补充文章 GEO'],
          '产品问答小卡片' => ['href' => 'admin.php?m=products', 'label' => '去补充产品 GEO'],
          '标题关键词描述' => ['href' => 'admin.php?m=articles', 'label' => '去自动填写 SEO'],
          '实体一致性'     => ['js' => "alert('建议：把工厂名/品牌名/核心产品词在全局保持统一，可在「系统设置」中设定站点名称，并在每篇文章中保持一致的实体称谓。')", 'label' => '查看统一建议'],
          '问答覆盖'       => ['js' => "switchTab('content');setTimeout(function(){document.getElementById('batchTopics').focus();},100)", 'label' => '批量生成词条'],
        ];
        foreach ($audit['items'] as $it):
          $act = $auditActions[$it['name']] ?? null;
        ?>
          <div class="audit-item-h">
            <span class="dot <?= $it['status'] ?>"></span>
            <div class="body">
              <div class="name"><?= e($it['name']) ?></div>
              <div class="tip"><?= e($it['tip']) ?></div>
              <?php if ($act && $it['status'] !== 'ok'): ?>
                <div class="act">
                  <?php if (!empty($act['href'])): ?><a href="<?= $act['href'] ?>"><?= $act['label'] ?></a>
                  <?php else: ?><button type="button" onclick="<?= e($act['js']) ?>"><?= $act['label'] ?></button><?php endif; ?>
                </div>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="tabs">
      <div class="tab-btn <?= $tab === 'content' ? 'active' : '' ?>" onclick="switchTab('content')">📝 GEO 内容</div>
      <div class="tab-btn <?= $tab === 'monitor' ? 'active' : '' ?>" onclick="switchTab('monitor')">📡 GEO 监控</div>
    </div>

    <!-- ============ GEO 内容 ============ -->
    <div class="tab <?= $tab === 'content' ? 'active' : '' ?>" id="tab-content">

      <!-- 操作说明：告诉工厂老板每个板块怎么用 -->
      <div class="panel geo-guide">
        <div class="guide-head" onclick="toggleGuide(this)">
          <h2>📖 GEO 操作说明书 <span class="muted">（第一次用请先看这里）</span></h2>
          <span class="guide-toggle">▼</span>
        </div>
        <div class="guide-body">
          <div class="guide-grid">
            <div class="guide-item">
              <div class="gnum">1</div>
              <div class="gtxt">
                <b>顶部体检分数</b>
                <p>先看清网站离「被 AI 推荐」还差几步。绿色=达标，黄色=建议补，红色=必须改。点检查项下面的按钮直接跳转去补。</p>
              </div>
            </div>
            <div class="guide-item">
              <div class="gnum">2</div>
              <div class="gtxt">
                <b>商家介绍</b>
                <p>写一段 50-150 字的工厂自我介绍：你是谁、做什么、服务哪些客户、联系方式。AI 回答时会文末展示，别让 AI 瞎编。</p>
              </div>
            </div>
            <div class="guide-item">
              <div class="gnum">3</div>
              <div class="gtxt">
                <b>客户会搜什么词</b>
                <p>输入一个产品词（如：超声波清洗机），AI 自动列出客户真正会问的问题。勾选后点「投喂自动发文计划」，系统就会按这些词自动写文章。</p>
              </div>
            </div>
            <div class="guide-item">
              <div class="gnum">4</div>
              <div class="gtxt">
                <b>品牌资料库</b>
                <p>把产品参数、工厂资质、真实案例、联系方式一条条存进来。AI 写答案时会自动参考，避免胡说八道。</p>
              </div>
            </div>
            <div class="guide-item">
              <div class="gnum">5</div>
              <div class="gtxt">
                <b>内容优化工具</b>
                <p>选一篇已发布的文章，一键检测：写得专不专业、能不能被 AI 看懂、有没有跟别人重复、能不能直接发到知乎/小红书/公众号。</p>
              </div>
            </div>
            <div class="guide-item">
              <div class="gnum">6</div>
              <div class="gtxt">
                <b>AI 问答库</b>
                <p>批量输入客户问题主题，AI 结合品牌资料库生成「问题+答案」。生成后点「同步文章」直接变成网站文章，前台自动带 FAQ 卡片。</p>
              </div>
            </div>
          </div>
          <p class="guide-tip">💡 建议按 1→2→4→3→6→5 的顺序操作，先把工厂资料填准，再去生成内容，最后做检测和分发。</p>
        </div>
      </div>

      <div class="geo-workflow">
        <div class="geo-step"><div class="num">1</div><h4>全站诊断</h4><p>先体检：看看你的网站离"被 AI 推荐"还差哪几步。</p></div>
        <div class="geo-step"><div class="num">2</div><h4>生成问答</h4><p>把客户常问的问题交给 AI，结合工厂真实资料生成标准答案。</p></div>
        <div class="geo-step"><div class="num">3</div><h4>做 FAQ 卡片</h4><p>给每篇文章加上"问答小卡片"，让 AI 一眼就能抓取拿去用。</p></div>
        <div class="geo-step"><div class="num">4</div><h4>分发与监控</h4><p>同步到文章，再分发知乎/小红书；到监控页看 AI 有没有引用你。</p></div>
      </div>

      <!-- 商家介绍 -->
      <form method="post" action="admin.php?m=geo_advert_save" class="advert-box">
        <h4>🏭 商家广告 / 网站主体介绍 <span class="muted">（以文末独立「关于我们」区块展示，不混入答案）</span></h4>
        <textarea id="geoAdvert" name="advert" placeholder="例如：我们是广东某某食品机械厂，主营超声波清洗机…"><?= e($geo_advert ?? '') ?></textarea>
        <div class="batch-meta" style="margin-top:10px">
          <button class="btn s" type="submit">💾 保存商家介绍</button>
          <span class="hint" style="font-size:12px;color:var(--muted)">建议 50-150 字，客观说明你是谁、做什么、服务范围、联系方式。请勿使用绝对化用语以免被 AI 判定软文。</span>
        </div>
        <?= csrf_field() ?>
      </form>

      <!-- 关键词蒸馏 + 知识库：双栏 -->
      <div class="gm-2col">
        <div class="panel kw-panel">
          <div class="kw-head">
            <div>
              <h2 style="margin:0">🔍 客户会搜什么词</h2>
              <span class="muted" id="kwCount">共 <?= count($keywords) ?> 个词</span>
            </div>
            <button type="button" class="btn ghost s" id="kwFoldBtn" onclick="toggleKwFold()">展开列表 ▼</button>
          </div>
          <p class="sub-note">输入一个产品词，AI 帮你列出客户真正会问的问题，比如"哪家好""多少钱""怎么选"。这些词可以直接加到自动发文计划里。</p>
          <div class="kw-input-row">
            <div class="kw-input-wrap">
              <span class="kw-input-icon">🔍</span>
              <input id="kwTopic" class="kw-input" placeholder="输入主题，如：超声波清洗机">
            </div>
            <button class="btn btn-p kw-btn-primary" onclick="distill()">
              <span class="kw-btn-icon">✨</span>生成客户问题词
            </button>
          </div>
          <div class="kw-presets">
            <span class="kw-presets-label">试试：</span>
            <button type="button" class="kw-chip" onclick="document.getElementById('kwTopic').value='网站建设'">网站建设</button>
            <button type="button" class="kw-chip" onclick="document.getElementById('kwTopic').value='小程序开发'">小程序开发</button>
            <button type="button" class="kw-chip" onclick="document.getElementById('kwTopic').value='SEO优化'">SEO优化</button>
            <button type="button" class="kw-chip" onclick="document.getElementById('kwTopic').value='AI生图'">AI生图</button>
            <button type="button" class="kw-chip" onclick="document.getElementById('kwTopic').value='小程序商城'">小程序商城</button>
          </div>
          <div class="gen-status" id="kwStatus"><span id="kwStatusText"></span></div>
          <?php if (!empty($keywords)): ?>
            <form method="post" action="admin.php?m=geo_kw_feed" id="kwForm" onsubmit="return checkKwFeed()">
              <div class="kw-body collapsed" id="kwBody">
                <table class="geo-entries" style="margin-top:6px">
                  <thead><tr><th style="width:30px"><input type="checkbox" id="kwAll" title="全选/取消全选" onclick="toggleKwAll()"></th><th>客户意图</th><th>关键词</th><th style="width:90px">状态</th><th style="width:70px">操作</th></tr></thead>
                  <tbody id="kwTbody"></tbody>
                </table>
                <div class="kw-pager" id="kwPager"></div>
                <div class="batch-meta" style="margin-top:10px">
                  <button class="btn s" type="submit">➡ 投喂自动发文计划</button>
                  <span class="hint" style="font-size:12px;color:var(--muted)">已投喂过的词也可以再次勾选加入计划</span>
                  <?= csrf_field() ?>
                </div>
              </div>
            </form>
            <script>window._kwData=<?= json_encode(array_map(function($k){return ['id'=>(int)$k['id'],'intent'=>$k['intent'],'kw'=>$k['kw'],'used'=>(int)$k['used'],'delUrl'=>'admin.php?m=geo_kw_del&id='.(int)$k['id']];},$keywords), JSON_UNESCAPED_UNICODE) ?>;</script>
          <?php else: ?>
            <p class="sub-note" style="margin-top:8px">暂无关键词，输入主题点「生成客户问题词」开始。</p>
          <?php endif; ?>
        </div>

        <div class="panel">
          <h2>📚 品牌资料库</h2>
          <p class="sub-note">把工厂的真实资料（产品参数、案例、资质、联系方式）存进来，AI 写答案时会自动参考，避免胡说八道。</p>
          <form method="post" action="admin.php?m=geo_kb_add" class="field">
            <input name="title" placeholder="条目标题，如：公司资质与主营产品" style="margin-bottom:10px">
            <textarea name="content" placeholder="条目内容（产品参数、案例、资质、联系方式等真实信息）"></textarea>
            <div class="batch-meta" style="margin-top:10px">
              <button class="btn s" type="submit">➕ 添加知识条目</button>
              <?= csrf_field() ?>
            </div>
          </form>
          <?php if (!empty($kb)): ?>
            <ul style="list-style:none;margin:12px 0 0;padding:0">
              <?php foreach ($kb as $k): ?>
                <li style="display:flex;justify-content:space-between;gap:10px;padding:9px 0;border-top:1px solid var(--line);font-size:13px">
                  <span style="min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= e(mb_substr($k['title'],0,32)) ?> <span style="color:var(--muted);font-size:11px">· <?= e(mb_substr(strip_tags($k['content']),0,30)) ?></span></span>
                  <a href="admin.php?m=geo_kb_del&id=<?= $k['id'] ?>" onclick="return confirm('删除该知识条目？')" style="color:var(--danger);text-decoration:none;font-size:12px">删除</a>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>
      </div>

      <!-- 内容优化工具 -->
      <div class="panel">
        <h2>🛠 内容优化工具</h2>
        <p class="sub-note">选一篇已发布的文章，一键检测：写得到底专不专业、AI 能不能看懂、有没有跟别人重复、能不能直接发到知乎/小红书/公众号。</p>
        <div class="batch-meta">
          <select id="optArt" style="flex:1;max-width:420px;padding:9px 11px;border:1px solid var(--line);border-radius:8px;background:var(--card);color:var(--text)">
            <option value="0">选择文章…</option>
            <?php foreach ($articles as $a): ?><option value="<?= $a['id'] ?>"><?= e(mb_substr($a['title'],0,30)) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="acts">
          <button class="btn s" onclick="runEeat()">专业度评分</button>
          <button class="btn s ghost" onclick="runSchema()">预览 FAQ 卡片</button>
          <button class="btn s ghost" onclick="runUniq()">内容去重检测</button>
          <button class="btn s ghost" onclick="runDist()">生成分发文案</button>
        </div>
        <div class="gen-status" id="optStatus"><span id="optStatusText"></span></div>
        <div id="eeatBox" class="out-box">
          <div class="eeat-grid" id="eeatGrid"></div>
          <div class="eeat-total" id="eeatTotal"></div>
          <ul class="eeat-tips" id="eeatTips"></ul>
        </div>
        <div id="schemaBox" class="out-box">
          <div class="gen-status ok" style="margin-bottom:8px">FAQ 卡片代码（给 AI 看的"问题-答案"格式，可直接复制到文章里）</div>
          <textarea class="code-box" id="schemaCode" readonly></textarea>
        </div>
        <div id="distBox" class="out-box">
          <div class="gen-status ok" style="margin-bottom:8px">多平台 Markdown（知乎 / 小红书 / 公众号通用，复制后粘贴发布）</div>
          <textarea class="code-box" id="distCode" readonly></textarea>
        </div>
      </div>

      <!-- 批量生成 + 词条库 -->
      <div class="panel">
        <h2>📚 AI 问答库（给 AI 准备的 FAQ）</h2>
        <p class="sub-note">每一条 = 客户常问的问题 + 工厂标准答案 + 相关关键词。生成后一键变成网站文章，前台自动带上"问答小卡片"，方便 AI 抓取。</p>
        <div class="batch-box">
          <h4>✨ 批量生成 AI 问答 <span class="muted">（每行一个主题，最多 30 个，自动参考品牌资料库）</span></h4>
          <textarea id="batchTopics" placeholder="例如：&#10;食品机械工厂招商加盟政策&#10;超声波清洗机哪家好"></textarea>
          <div class="batch-meta">
            <select id="batchCat">
              <option value="0">默认栏目</option>
              <?php foreach ($cats as $c): ?><option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option><?php endforeach; ?>
            </select>
            <button class="btn" onclick="genBatch()">🚀 批量生成</button>
            <button class="btn s" type="button" onclick="fillSamples()">填入示例主题</button>
            <span id="batchCount" style="font-size:12px;color:var(--muted)">已输入 0 个主题</span>
          </div>
        </div>
        <div class="gen-status" id="geoStatus"><span id="geoStatusText"></span></div>
        <div class="geo-entries">
          <table>
            <thead><tr><th style="width:40px">ID</th><th>主题 / 问题</th><th>回答摘要</th><th style="width:130px">关键词</th><th style="width:170px">操作</th></tr></thead>
            <tbody>
              <?php foreach ($entries as $e): ?>
              <tr>
                <td><?= $e['id'] ?></td>
                <td>
                  <div class="q"><?= e($e['topic']) ?><?php if (!empty($e['advert'])): ?><span class="kw-tag" style="margin-left:6px">含商家介绍</span><?php endif; ?></div>
                  <div style="font-size:12px;color:var(--muted)"><?= e($e['question']) ?></div>
                </td>
                <td class="a" title="<?= e($e['answer']) ?>"><?= e($e['answer']) ?></td>
                <td><?php foreach (array_filter(array_map('trim', explode(',', $e['keywords']))) as $k): ?><span class="kw-tag"><?= e($k) ?></span><?php endforeach; ?></td>
                <td>
                  <form method="post" action="admin.php?m=geo_sync_article" style="display:inline" onsubmit="return confirm('同步为一篇新文章？')">
                    <input type="hidden" name="id" value="<?= $e['id'] ?>"><button class="btn s" type="submit">同步文章</button><?= csrf_field() ?>
                  </form>
                  <form method="post" action="admin.php?m=geo_del" style="display:inline" onsubmit="return confirm('删除该词条？')">
                    <input type="hidden" name="id" value="<?= $e['id'] ?>"><button class="btn s d" type="submit">删除</button><?= csrf_field() ?>
                  </form>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php if (empty($entries)): ?><tr><td colspan="5" class="empty-guide"><b>暂无 GEO 词条</b>在上方批量输入主题，AI 会自动生成问答资产。<div class="samples"><span onclick="fillSamples()">👆 点我填入示例主题</span></div></td></tr><?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ============ GEO 监控 ============ -->
    <div class="tab <?= $tab === 'monitor' ? 'active' : '' ?>" id="tab-monitor">
      <div class="gm-head">
        <div>
          <h2>📡 AI 有没有提到你的网站</h2>
          <div style="font-size:12.5px;color:var(--muted)">看 DeepSeek、豆包、Kimi 这些 AI 在回答客户问题时，有没有把你的网站推荐出来</div>
        </div>
      </div>
      <div class="gm-note">
        <b>检测原理</b>：用你自己后台配置的 AI 接口，向 DeepSeek/豆包/Kimi 等提问，看它们的回答里有没有出现你的网站域名。全程走你自己的 API，不爬任何平台登录页，稳定合规。
      </div>

      <div class="gm-cards">
        <div class="gm-card"><div class="k">今日 AI 来源访问</div><div class="v"><?= (int)$stats['ai_today'] ?> <small>次</small></div></div>
        <div class="gm-card"><div class="k">累计 AI 来源访问</div><div class="v"><?= (int)$stats['ai_total'] ?> <small>次</small></div></div>
        <div class="gm-card"><div class="k">AI 引用率</div><div class="v"><?= (int)$stats['rate'] ?><small>%</small></div></div>
        <div class="gm-card"><div class="k">竞品命中 / 负面预警</div><div class="v"><?= (int)$stats['comp_cited'] ?> <small>/ <?= (int)$stats['neg_hit'] ?></small></div></div>
      </div>

      <div class="gm-2col">
        <div class="gm-panel">
          <h3>被动回流：近 7 天 AI 来源访问</h3>
          <div class="trend">
            <?php foreach ($stats['ai_trend'] as $t): $h = max(2, round($t['n'] / max(1, ...array_column($stats['ai_trend'], 'n')) * 100)); ?>
              <div class="bar"><div class="col" style="height:<?= $h ?>px" title="<?= $t['date'] ?>：<?= $t['n'] ?> 次"></div><div class="d"><?= substr($t['date'],5) ?></div></div>
            <?php endforeach; ?>
          </div>
          <div style="font-size:12px;color:var(--muted);margin-top:10px">
            当前识别域名：<b><?= e($stats['domain'] ?: '未配置（请在下方填写站点域名）') ?></b><br>
            识别来源：DeepSeek / 豆包 / 元宝 / Kimi / 智谱 / 文心 / 通义 / Perplexity / ChatGPT 及 GPTBot、DeepSeekBot、Bytespider 等 AI 爬虫 UA。
          </div>
        </div>
        <div class="gm-panel">
          <h3>主动检测：各平台有没有推荐你</h3>
          <?php if (empty($stats['by_platform'])): ?>
            <div style="font-size:12.5px;color:var(--muted)">尚未运行检测。在下方点「检测全部已发布文章」开始。</div>
          <?php else: ?>
            <table class="gm-pf">
              <tr><td>平台</td><td>检测次数</td><td>提到你</td><td>引用率</td></tr>
              <?php foreach ($stats['by_platform'] as $p): $n=(int)$p['n']; $c=(int)$p['cited']; $r=$n>0?round($c/$n*100):0; ?>
                <tr><td><?= e($p['platform']) ?></td><td><?= $n ?></td><td><?= $c ?></td><td><?= $r ?>%</td></tr>
              <?php endforeach; ?>
            </table>
          <?php endif; ?>
          <?php if (!empty($stats['top_cited'])): ?>
            <h3 style="margin-top:16px">被引用最多的文章</h3>
            <ul style="list-style:none;margin:0;padding:0">
              <?php foreach ($stats['top_cited'] as $a): ?>
                <li style="display:flex;justify-content:space-between;gap:10px;padding:8px 0;border-bottom:1px solid var(--line);font-size:12.5px"><span style="color:var(--primary)"><?= e(mb_substr($a['title'],0,26)) ?></span><span><?= (int)$a['cited'] ?> 次引用</span></li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>
      </div>

      <!-- 竞品对比 + 负面监控 -->
      <div class="gm-2col">
        <div class="gm-panel">
          <h3>🆚 竞品对比监控</h3>
          <p class="sub-note">输入竞品域名和同一个客户问题，看 AI 更推荐谁，就能知道自家内容差在哪。</p>
          <div class="field"><label>竞品域名</label><input id="compDomain" placeholder=" competitor.com"></div>
          <div class="field"><label>对比问题</label><input id="compQ" placeholder="超声波清洗机哪家好？"></div>
          <div class="acts"><button class="btn s" onclick="runCompetitor()">运行竞品对比</button></div>
        </div>
        <div class="gm-panel">
          <h3>⚠ 负面监控</h3>
          <p class="sub-note">输入客户可能问的负面问题，看 AI 回答里有没有出现"投诉""差评""维权"这类词，有就提醒你。</p>
          <div class="field"><label>负面监控问题</label><input id="negQ" placeholder="XX 品牌靠谱吗？有没有投诉？"></div>
          <div class="acts"><button class="btn s" onclick="runNegative()">运行负面检测</button></div>
        </div>
      </div>

      <div class="gm-panel" style="margin-bottom:18px">
        <h3>设置（站点域名 / 自动检测 / 竞品 / 负面词）</h3>
        <form method="post" action="admin.php?m=geo_competitor_settings">
          <?= csrf_field() ?>
          <div class="gm-2col" style="margin:0">
            <div class="field" style="margin-bottom:12px">
              <label>站点域名 / 首页地址（用于判定 AI 是否引用，留空则自动取当前访问域名）</label>
              <input type="url" name="site_url" value="<?= e($cfg['site_url'] ?? '') ?>" placeholder="https://example.com">
            </div>
            <div style="display:flex;flex-direction:column;gap:12px;justify-content:center">
              <label class="switch"><input type="checkbox" name="geo_monitor_on" <?= ($cfg['geo_monitor_on'] ?? '0') === '1' ? 'checked' : '' ?>> 启用每日自动检测（随访客访问触发，无需计划任务）</label>
              <div class="field" style="margin:0">
                <label>每日自动检测上限（篇）</label>
                <input type="number" name="geo_monitor_perday" min="1" max="50" value="<?= (int)($cfg['geo_monitor_perday'] ?? 5) ?>" style="max-width:140px">
              </div>
            </div>
          </div>
          <div class="field" style="margin-top:10px">
            <label>竞品域名（每行一个或逗号分隔，供一键批量对比）</label>
            <textarea name="geo_competitors" placeholder="competitor-a.com, competitor-b.com"><?= e($cfg['geo_competitors'] ?? '') ?></textarea>
          </div>
          <div class="field">
            <label>负面监控问题（每行一个或逗号分隔）</label>
            <textarea name="geo_negative" placeholder="XX 品牌投诉, XX 靠谱吗"><?= e($cfg['geo_negative'] ?? '') ?></textarea>
          </div>
          <button class="btn" type="submit">保存设置</button>
        </form>
      </div>

      <div class="gm-panel">
        <h3>立即检测</h3>
        <div class="acts">
          <button class="btn" id="btnAll" onclick="runAll()">检测全部已发布文章（带 GEO）</button>
          <select id="artSel" class="btn ghost" style="padding:9px 8px">
            <option value="0">选择单篇文章立即检测…</option>
            <?php foreach ($articles as $a): ?><option value="<?= (int)$a['id'] ?>"><?= e(mb_substr($a['title'],0,30)) ?></option><?php endforeach; ?>
          </select>
          <button class="btn ghost" onclick="runOne()">检测所选文章</button>
        </div>
        <div class="log" id="log"></div>
      </div>

      <div class="gm-panel" style="margin:18px 0 30px">
        <h3>检测明细（最近 30 条，含竞品/负面）</h3>
        <?php if (empty($stats['recent'])): ?>
          <div style="font-size:12.5px;color:var(--muted)">暂无记录。</div>
        <?php else: ?>
          <table class="gm-table">
            <thead><tr><th>文章/对象</th><th>平台</th><th>检测类型</th><th>检测问题</th><th>结果</th><th>时间</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($stats['recent'] as $r): ?>
              <tr>
                <td><?= e(mb_substr($r['title'] ?? ($r['target'] ?: '(本站)'),0,22)) ?></td>
                <td><?= e($r['platform']) ?></td>
                <td style="font-size:12px;color:var(--muted)"><?= e($r['kind']) ?></td>
                <td style="max-width:200px"><?= e(mb_substr($r['question'],0,36)) ?></td>
                <td>
                  <?php if ($r['kind'] === 'negative'): ?>
                    <?= $r['cited'] ? '<span class="tag-neg">负面命中</span>' : '<span class="tag-nocite">无负面</span>' ?>
                  <?php elseif ($r['kind'] === 'competitor'): ?>
                    <?= $r['cited'] ? '<span class="tag-neg">竞品被引</span>' : '<span class="tag-nocite">竞品未引</span>' ?>
                  <?php else: ?>
                    <?= $r['cited'] ? '<span class="tag-cited">已引用</span>' : '<span class="tag-nocite">未引用</span>' ?>
                  <?php endif; ?>
                </td>
                <td style="color:var(--muted)"><?= substr($r['checked_at'],0,16) ?></td>
                <td>
                  <form method="post" action="admin.php?m=geo_monitor_del" style="display:inline" onsubmit="return confirm('删除该记录？')">
                    <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                    <button class="btn ghost" style="padding:3px 9px;font-size:11px" type="submit">删</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<script>
function toggleGuide(el){el.closest('.geo-guide').classList.toggle('collapsed');}
function switchTab(t){
  document.getElementById('tab-content').classList.toggle('active', t==='content');
  document.getElementById('tab-monitor').classList.toggle('active', t==='monitor');
  document.querySelectorAll('.tab-btn').forEach(function(b){b.classList.remove('active');});
  event.currentTarget.classList.add('active');
  var u=new URL(location.href); u.searchParams.set('tab',t); history.replaceState(null,'',u);
}
function setStatus(el,text,isOk){var e=document.getElementById(el);e.style.display='block';e.className='gen-status '+(isOk?'ok':(isOk===false?'warn':''));document.getElementById(el+'Text').innerHTML=text;}
function fillSamples(){document.getElementById('batchTopics').value='食品机械工厂招商加盟政策\n超声波清洗机哪家好\n工业烤箱怎么选\n食品加工厂需要哪些设备\n';updateCount();document.getElementById('batchTopics').focus();}
function updateCount(){var t=document.getElementById('batchTopics').value;var n=t.split(/[\r\n,，]+/).filter(function(x){return x.trim()!=='';}).length;document.getElementById('batchCount').textContent='已输入 '+n+' 个主题'+(n>30?'（超过30将只处理前30个）':'');}
document.getElementById('batchTopics').addEventListener('input',updateCount);
function genBatch(){
  var ta=document.getElementById('batchTopics');var topics=ta.value.trim();
  if(!topics){alert('请输入至少一个主题');return;}
  var advert=document.getElementById('geoAdvert'); if(advert&&!advert.value.trim()){if(!confirm('商家广告/网站主体介绍为空，生成的答案将不带业务引导。建议先填写上方介绍，是否继续？'))return;}
  var btn=document.querySelector('.batch-box .btn'); btn.disabled=true;
  setStatus('geoStatus','<span class="spinner"></span> AI 正在批量生成词条，请稍候…',null);
  var fd=new FormData();fd.append('topics',topics);fd.append('cat',document.getElementById('batchCat').value);
  if(advert)fd.append('advert',advert.value.trim());
  fetch('admin.php?m=geo_generate_batch',{method:'POST',body:fd}).then(r=>r.json()).then(function(r){
    btn.disabled=false;
    if(!r.ok){setStatus('geoStatus','⚠ '+r.msg,false);return;}
    var log='';if(r.log&&r.log.length){log='<br><small style="opacity:.85">明细：</small><ul style="margin:6px 0 0;padding-left:18px;font-size:12px">';r.log.forEach(function(it){log+='<li>'+(it.status==='ok'?'✅':'❌')+' '+eHtml(it.topic)+(it.status==='ok'?' → '+eHtml(it.question):'：'+(it.msg||'失败'))+'</li>';});log+='</ul>';}
    setStatus('geoStatus','✅ 批量生成完成：成功 '+r.success+' / 失败 '+r.failed+' / 总计 '+r.total+log,true);
    setTimeout(function(){location.reload();},r.failed===0?1200:2500);
  }).catch(function(err){btn.disabled=false;setStatus('geoStatus','⚠ 请求失败：'+err.message,false);});
}
function distill(){
  var topic=document.getElementById('kwTopic').value.trim();
  if(!topic){alert('请输入主题');return;}
  var btn=event.currentTarget;btn.disabled=true;
  setStatus('kwStatus','<span class="spinner"></span> AI 正在整理客户问题词…',null);
  var fd=new FormData();fd.append('topic',topic);
  fetch('admin.php?m=geo_kw_distill',{method:'POST',body:fd}).then(r=>r.json()).then(function(r){
    btn.disabled=false;
    if(!r.ok){setStatus('kwStatus','⚠ '+r.msg,false);return;}
    setStatus('kwStatus','✅ 已整理 '+r.saved+' 个客户问题词，刷新后可在下方勾选投喂',true);
    setTimeout(function(){location.reload();},900);
  }).catch(function(err){btn.disabled=false;setStatus('kwStatus','⚠ 请求失败：'+err.message,false);});
}
var kwPage=1,kwPerPage=10,kwFoldInit=false;
function toggleKwFold(){
  var body=document.getElementById('kwBody');var btn=document.getElementById('kwFoldBtn');
  if(body.classList.contains('collapsed')){
    body.classList.remove('collapsed');btn.textContent='收起列表 ▲';
    if(!kwFoldInit && window._kwData){kwFoldInit=true;renderKwPage(1);}
  }else{
    body.classList.add('collapsed');btn.textContent='展开列表 ▼';
  }
}
function renderKwPage(p){
  if(!window._kwData)return;
  var total=window._kwData.length,pages=Math.max(1,Math.ceil(total/kwPerPage));
  if(p<1)p=1;if(p>pages)p=pages;kwPage=p;
  var start=(p-1)*kwPerPage,end=Math.min(start+kwPerPage,total),tb=document.getElementById('kwTbody');
  tb.innerHTML='';
  for(var i=start;i<end;i++){
    var k=window._kwData[i];
    var tr=document.createElement('tr');
    tr.innerHTML='<td><input type="checkbox" class="kw-chk" name="ids[]" value="'+k.id+'"></td>'+
      '<td>'+eHtml(k.intent)+'</td>'+
      '<td><span class="kw-tag">'+eHtml(k.kw)+'</span></td>'+
      '<td style="font-size:12px;color:var(--muted)">'+(k.used?'✅ 已投喂':'可投喂')+'</td>'+
      '<td><a href="'+k.delUrl+'" onclick="return confirm(\'删除该关键词？\')" style="font-size:12px;color:var(--danger);text-decoration:none">删除</a></td>';
    tb.appendChild(tr);
  }
  document.getElementById('kwAll').checked=false;
  renderKwPager(pages);
}
function renderKwPager(pages){
  var el=document.getElementById('kwPager');el.innerHTML='';
  if(pages<=1){return;}
  var prev=document.createElement('span');prev.className='pg-btn';prev.textContent='上一页';prev.onclick=function(){changeKwPage(kwPage-1);};
  el.appendChild(prev);
  for(var i=1;i<=pages;i++){
    var b=document.createElement('span');b.className='pg-btn'+(i===kwPage?' active':'');b.textContent=i;b.onclick=(function(n){return function(){changeKwPage(n);};})(i);
    el.appendChild(b);
  }
  var next=document.createElement('span');next.className='pg-btn';next.textContent='下一页';next.onclick=function(){changeKwPage(kwPage+1);};
  el.appendChild(next);
  var info=document.createElement('span');info.className='pg-info';info.textContent='第 '+kwPage+' / '+pages+' 页';
  el.appendChild(info);
}
function changeKwPage(n){renderKwPage(n);}
function toggleKwAll(){
  var all=document.getElementById('kwAll').checked;
  document.querySelectorAll('#kwForm .kw-chk').forEach(function(cb){cb.checked=all;});
}
function checkKwFeed(){
  var checked=document.querySelectorAll('#kwForm .kw-chk:checked').length;
  if(checked===0){alert('请先勾选至少一个关键词');return false;}
  return true;
}
function optAjax(m,extra,cb){
  var id=document.getElementById('optArt').value;
  if(!id){alert('请先选择一篇文章');return;}
  var fd=new FormData();fd.append('article_id',id);if(extra)extra(fd);
  document.getElementById('optStatus').style.display='block';document.getElementById('optStatus').className='gen-status';document.getElementById('optStatusText').textContent='处理中…';
  fetch('admin.php?m='+m,{method:'POST',body:fd}).then(r=>r.json()).then(function(r){
    if(!r.ok){document.getElementById('optStatus').className='gen-status warn';document.getElementById('optStatusText').textContent='⚠ '+r.msg;return;}
    cb(r);
  }).catch(function(e){document.getElementById('optStatus').className='gen-status warn';document.getElementById('optStatusText').textContent='⚠ 请求失败';});
}
function runEeat(){
  optAjax('geo_eeat',null,function(r){
    document.getElementById('optStatus').className='gen-status ok';document.getElementById('optStatusText').textContent='专业度总分：'+r.total;
    document.getElementById('eeatBox').style.display='block';
    var labels={experience:'经验性',expertise:'专业性',authority:'权威性',trust:'可信度'};
    var g=document.getElementById('eeatGrid');g.innerHTML='';
    for(var k in r.dims){var d=document.createElement('div');d.className='eeat-dim';var col=r.dims[k]>=75?'var(--ok)':(r.dims[k]>=50?'var(--warn)':'var(--danger)');d.innerHTML='<div class="n" style="color:'+col+'">'+r.dims[k]+'</div><div class="l">'+labels[k]+'</div>';g.appendChild(d);}
    document.getElementById('eeatTotal').textContent='总分 '+r.total+' / 100 · '+(r.total>=75?'优秀，易被 AI 引用':(r.total>=50?'中等，建议优化':'偏弱，需补充数据与案例'));
    var tips=document.getElementById('eeatTips');tips.innerHTML='';
    (r.tips||[]).forEach(function(t){var li=document.createElement('li');li.textContent=t;tips.appendChild(li);});
  });
}
function runSchema(){
  optAjax('geo_schema',null,function(r){
    document.getElementById('optStatus').className='gen-status ok';document.getElementById('optStatusText').textContent='已生成 FAQ 卡片';
    document.getElementById('schemaBox').style.display='block';
    document.getElementById('schemaCode').value=r.json;
  });
}
function runUniq(){
  optAjax('geo_uniq',null,function(r){
    document.getElementById('optStatus').className='gen-status ok';
    document.getElementById('optStatusText').textContent='最高相似度 '+r.max_sim+'%'+(r.dup_title?'（与《'+r.dup_title+'》相似）':'');
  });
}
function runDist(){
  optAjax('geo_distribute',null,function(r){
    document.getElementById('optStatus').className='gen-status ok';document.getElementById('optStatusText').textContent='已生成分发文案';
    document.getElementById('distBox').style.display='block';
    document.getElementById('distCode').value=r.md;
  });
}
function eHtml(s){return (s||'').replace(/[&<>"]/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c];});}
/* 监控 */
function log(html){var el=document.getElementById('log');el.style.display='block';el.innerHTML+=html+'<br>';el.scrollTop=el.scrollHeight;}
function runOne(){
  var id=document.getElementById('artSel').value;if(!id){alert('请先选择一篇文章');return;}
  log('检测文章 #'+id+' …');
  var fd=new FormData();fd.append('article_id',id);fd.append('platform','deepseek');
  fetch('admin.php?m=geo_check',{method:'POST',body:fd}).then(function(r){return r.json();}).then(function(d){
    if(d.ok){log('· #'+id+' '+d.title+'：<b class="'+(d.cited?'ok':'no')+'">'+(d.cited?'已引用':'未引用')+'</b> '+(d.domain||''));}else{log('· #'+id+' 失败：'+(d.msg||''));}
  }).catch(function(e){log('· #'+id+' 请求异常');});
}
function runAll(){
  var btn=document.getElementById('btnAll');btn.disabled=true;log('开始批量检测…');
  step();
  function step(){
    var fd=new FormData();fd.append('article_id','0');fd.append('limit','5');
    fetch('admin.php?m=geo_check',{method:'POST',body:fd}).then(function(r){return r.json();}).then(function(d){
      if(!d.ok){log('批量失败：'+(d.msg||''));btn.disabled=false;return;}
      (d.results||[]).forEach(function(x){if(x.ok){log('· #'+x.article_id+' '+x.title+'：<b class="'+(x.cited?'ok':'no')+'">'+(x.cited?'已引用':'未引用')+'</b>');}else{log('· #'+(x.article_id||'?')+' 失败：'+(x.msg||''));}});
      log('本轮处理 '+d.processed+' 篇，剩余 '+d.remaining+' 篇');
      if(d.remaining>0){setTimeout(step,400);}else{log('✅ 全部完成');btn.disabled=false;}
    }).catch(function(e){log('批量请求异常');btn.disabled=false;});
  }
}
function runCompetitor(){
  var d=document.getElementById('compDomain').value.trim();var q=document.getElementById('compQ').value.trim();
  if(!d||!q){alert('请输入竞品域名与对比问题');return;}
  log('竞品对比：'+d+' / '+q);
  var fd=new FormData();fd.append('domain',d);fd.append('question',q);fd.append('platform','deepseek');
  fetch('admin.php?m=geo_monitor_competitor',{method:'POST',body:fd}).then(r=>r.json()).then(function(r){log('· '+(r.ok?'<b class="'+(r.comp_cited?'neg':'no')+'">'+(r.comp_cited?'竞品被引 / 本站'+(r.self_cited?'也被引':'未引'):'竞品未引 / 本站'+(r.self_cited?'被引':'未引'))+'</b>':'⚠ '+(r.msg||'')));}).catch(function(e){log('· 请求异常');});
}
function runNegative(){
  var q=document.getElementById('negQ').value.trim();if(!q){alert('请输入负面监控问题');return;}
  log('负面检测：'+q);
  var fd=new FormData();fd.append('question',q);fd.append('platform','deepseek');
  fetch('admin.php?m=geo_monitor_negative',{method:'POST',body:fd}).then(r=>r.json()).then(function(r){log('· '+(r.ok?'<b class="'+(r.negative?'neg':'no')+'">'+(r.negative?'⚠ 检测到负面措辞':'未检出明显负面')+'</b>':'⚠ '+(r.msg||'')));}).catch(function(e){log('· 请求异常');});
}
updateCount();
if(window._kwData && window._kwData.length){kwFoldInit=true;renderKwPage(1);}
</script>
</body>
</html>
