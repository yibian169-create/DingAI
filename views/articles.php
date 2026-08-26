<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>内容管理 - 得应盯后台</title>
<link rel="stylesheet" href="static/css/admin.css">
<style>
/* 本页左侧编辑区加宽（AI 写作需要更多横向空间） */
.split-wrap{grid-template-columns:520px 1fr}
@media (max-width:1200px){.split-wrap{grid-template-columns:460px 1fr}}
@media (max-width:900px){.split-wrap{grid-template-columns:1fr}}
</style>
<script>
(function(){try{var t=localStorage.getItem('dy_theme')||'light';document.documentElement.setAttribute('data-theme',t);}catch(e){}})();
</script>
</head>
<body>
<div class="layout">
  <?php require __DIR__ . '/sidebar.php'; ?>
  <div class="main">
    <div class="topbar">
      <h1>内容管理（文章）<span class="pill">AI 写作 v2</span></h1>
      <div class="right"><span><?= e($admin_name) ?></span><form method="post" action="admin.php?m=logout" style="display:inline"><?= csrf_field() ?><button type="submit" class="logout-link">退出</button></form></div>
    </div>
    <?php if (!empty($flash)): ?><div class="msg"><?= e($flash) ?></div><?php endif; ?>

    <!-- 顶部 Tab -->
    <div class="ai-tabs">
      <div class="ai-tab active" onclick="switchTab('ai',this)">🤖 AI 自动写</div>
      <div class="ai-tab" onclick="switchTab('manual',this)">✍️ 手动发布</div>
      <div class="ai-tab" onclick="switchTab('plan',this)">⚙ 自动发文计划</div>
    </div>

    <!-- 主体：左编辑 + 右列表 -->
    <div class="split-wrap" id="splitWrap">
      <!-- 左侧：编辑区 -->
      <div class="split-form">
        <div class="panel">
          <h2 id="panelTitle">🤖 AI 自动写文章 <span class="badge ok">全自动 · 已过滤</span></h2>

          <!-- AI 自动写专属配置区 -->
          <div class="ai-box" id="aiBox">
            <span class="tag">⚡ DeepSeek 默认（写作）· ChatGPT / DALL·E 默认（生图）· 兼容所有 OpenAI 协议 · <a href="admin.php?m=api_settings" style="color:inherit;text-decoration:underline">API 配置在独立板块</a></span>

            <div class="ai-divider"></div>

            <div class="row3">
              <div class="field"><label>主题 / 关键词 *</label><input id="topic" placeholder="如：中小老板私域复购实战"></div>
              <div class="field"><label>字数</label>
                <select id="words"><option>800</option><option selected>1200</option><option>2000</option><option>3000</option></select>
              </div>
              <div class="field"><label>语气</label>
                <select id="tone"><option>亲切实战</option><option>专业严谨</option><option>轻松活泼</option><option>激励热血</option></select>
              </div>
            </div>
            <div class="field"><label>补充要求（可选）</label><textarea id="extra" placeholder="结尾加引导加微信话术；3 个小标题分段"></textarea></div>

            <label class="switch"><input type="checkbox" id="genImg" checked> ✨ 已配置生图 API 时，写完后根据文章大意自动插图 2 张（未配置则文章不带任何插图）</label>
            <label class="switch"><input type="checkbox" id="genCensor" checked> 自动过滤敏感词（违禁/政治/低俗/暴力，米字代替）</label>
            <label class="switch"><input type="checkbox" id="genPublish" checked> 生成后直接发布</label>
            <label class="switch"><input type="checkbox" id="genSeo" checked> 🔍 自动生成 SEO（标题/关键词/描述）</label>
            <label class="switch"><input type="checkbox" id="genGeo" checked> 🌐 自动生成 GEO（要点摘要 + FAQ）</label>

            <div class="note">未填写「生图 API 地址 + Key」→ 文章纯文字无插图；封面自动取正文第一张图（无图则列表页无封面）。</div>

            <div class="ai-actions">
              <button class="btn btn-p" id="genBtn" onclick="gen()">✨ 一键生成（文章+插图）</button>
            </div>
            <div class="gen-status" id="status"><span class="spinner"></span><span id="statusText">AI 正在创作中…</span></div>
          </div>
          <!-- /AI 自动写专属配置区 -->

          <!-- 统一发布/编辑表单 -->
          <form method="post" action="admin.php?m=article_save" id="articleForm">
            <input type="hidden" name="id" id="f_id" value="0">
            <div class="fg">
              <div class="field"><label>所属栏目 *</label>
                <select name="cat_id" id="f_cat">
                  <?php foreach ($cats as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= str_repeat('— ', (int)$c['pid'] !== 0 ? 1 : 0) ?><?= e($c['name']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="field"><label>标题 *</label><input type="text" name="title" id="f_title" required placeholder="AI 生成或手动填写"></div>

              <div class="field full"><label>封面图（缩微图 · 自动）</label>
                <div class="imgpick-row">
                  <input type="text" name="cover" id="f_cover" placeholder="留空时自动取正文第一张图作为封面；可手动上传或图片空间选择">
                  <button type="button" class="btn btn-s" data-imgpick data-target="f_cover" data-preview="f_cover_img">📁 图片空间</button>
                  <button type="button" class="btn btn-s" onclick="document.getElementById('coverFile').click()">⬆ 上传</button>
                  <input type="file" id="coverFile" accept="image/*" hidden onchange="uploadCover(this)">
                </div>
                <div class="cover-wrap">
                  <img id="f_cover_img" class="cover-preview" alt="封面预览" hidden>
                </div>
                <div class="note">AI 自动写 <b>优先取正文第一张插图</b>（已下载入库图片空间）；若未配置生图 API 则正文无图、封面留空；可随时手动上传覆盖。</div>
              </div>

              <div class="field"><label>标签（逗号分隔）</label><input type="text" name="tags" id="f_tags"></div>
              <div class="field"><label>推荐首页</label>
                <select name="recommend" id="f_rec"><option value="0">普通</option><option value="1">推荐</option></select>
              </div>
              <div class="field"><label>状态</label>
                <select name="status" id="f_status"><option value="1">发布</option><option value="0">下架</option></select>
              </div>
              <div class="field full"><label>摘要</label><input type="text" name="summary" id="f_summary"></div>
              <div class="field full"><label>正文（完整编辑器，插入图片默认图片空间）</label><textarea name="content" id="f_content"></textarea></div>
            </div>
            <details class="seo-box">
              <summary>SEO 设置（可选）</summary>
              <div class="fg" style="margin-top:10px">
                <div class="field"><label>SEO 标题</label><input type="text" name="seo_title" id="f_st"></div>
                <div class="field"><label>SEO 关键词</label><input type="text" name="seo_keywords" id="f_sk"></div>
                <div class="field full"><label>SEO 描述</label><input type="text" name="seo_description" id="f_sd"></div>
              </div>
              <div style="margin-top:10px;display:flex;gap:10px;align-items:center">
                <button type="button" class="btn btn-s" onclick="aiSeo('article')">🤖 SEO 自动填写</button>
                <span class="tip" style="font-size:12px;color:var(--muted)">基于标题与正文，一键生成 SEO 三要素</span>
              </div>
            </details>

            <details class="seo-box">
              <summary>🌐 GEO 优化（AI 改写 + FAQ 结构化）</summary>
              <div style="margin-top:10px;display:flex;gap:10px;align-items:center;margin-bottom:10px">
                <button type="button" class="btn btn-s" onclick="aiGeo('article')">🌐 一键 GEO 优化</button>
                <span class="tip" style="font-size:12px;color:var(--muted)">结论先行 + 要点化 + FAQ（前台自动输出 JSON-LD）</span>
              </div>
              <div class="fg">
                <div class="field full"><label>GEO 要点化摘要</label><textarea name="geo_summary" id="f_geo_summary" placeholder="AI 优化后自动生成：2-3 句结论先行的核心信息"></textarea></div>
                <div class="field full"><label>GEO FAQ（JSON）</label><textarea name="geo_faq" id="f_geo_faq" placeholder='AI 优化后自动生成：[{"q":"","a":""}]'></textarea></div>
              </div>
            </details>

            <div style="margin-top:14px;display:flex;gap:10px;align-items:center">
              <button class="btn btn-p" type="submit">💾 保存文章</button>
              <span class="badge ok" id="censorBadge">敏感词检测：通过</span>
            </div>
          <?= csrf_field() ?>
</form>
        </div>
      </div>

      <!-- 右侧：文章列表 -->
      <div class="split-list">
        <div class="panel">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
            <h2 style="font-size:15px;margin:0">文章列表</h2>
            <span style="font-size:12.5px;color:var(--muted)">共 <?= $pg['total'] ?? 0 ?> 篇</span>
          </div>
          <table>
            <thead><tr><th style="width:60px">ID</th><th>标题</th><th style="width:110px">栏目</th><th style="width:90px">推荐</th><th style="width:90px">状态</th><th style="width:80px">浏览</th><th style="width:110px">时间</th><th style="width:200px">操作</th></tr></thead>
            <tbody>
              <?php foreach ($list as $a): ?>
              <tr>
                <td><?= $a['id'] ?></td>
                <td><a href="index.php?act=detail&type=article&id=<?= $a['id'] ?>" target="_blank" style="color:#4f46e5"><?= e($a['title']) ?></a></td>
                <td><?= cat_name($a['cat_id'], $cats) ?></td>
                <td>
                  <form method="post" action="admin.php?m=article_toggle" style="display:inline">
                    <input type="hidden" name="id" value="<?= $a['id'] ?>">
                    <input type="hidden" name="field" value="recommend">
                    <button type="submit" class="btn-tag <?= $a['recommend'] ? 'on' : 'off' ?>" title="点击切换"><?= $a['recommend'] ? '已推荐' : '普通' ?></button>
                  <?= csrf_field() ?>
</form>
                </td>
                <td>
                  <form method="post" action="admin.php?m=article_toggle" style="display:inline">
                    <input type="hidden" name="id" value="<?= $a['id'] ?>">
                    <input type="hidden" name="field" value="status">
                    <button type="submit" class="btn-tag <?= $a['status'] ? 'on' : 'off' ?>" title="点击切换"><?= $a['status'] ? '已发布' : '已下架' ?></button>
                  <?= csrf_field() ?>
</form>
                </td>
                <td><?= $a['views'] ?></td>
                <td><?= substr($a['created_at'], 0, 10) ?></td>
                <td>
                  <button class="btn btn-s" onclick='editArticle(<?= json_encode($a, JSON_UNESCAPED_UNICODE) ?>)'>编辑</button>
                  <form method="post" action="admin.php?m=article_del" style="display:inline" onsubmit="return confirm('确定删除？')">
                    <input type="hidden" name="id" value="<?= $a['id'] ?>">
                    <button class="btn btn-s btn-d" type="submit">删除</button>
                  <?= csrf_field() ?>
</form>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <?php require __DIR__ . '/pager.php'; ?>
        </div>
      </div>
    </div>

    <!-- 自动发文计划面板 -->
    <div class="auto-wrap" id="autoWrap" style="display:none">
      <div class="auto-overview">
        <div class="auto-stat"><b><?= count(array_filter(array_map('trim', explode("\n", $cfg['ai_plan_kw'] ?? '')))) ?></b><small>关键词总数</small></div>
        <div class="auto-stat"><b><?= DB::one('SELECT COUNT(*) AS n FROM ai_post_log WHERE site_id=? AND created_at>=? AND created_at<?', [$sid, date('Y-m-d 00:00:00'), date('Y-m-d 23:59:59')])['n'] ?? 0 ?></b><small>今日已发</small></div>
        <div class="auto-stat"><b><?= trim((string)($cfg['ai_plan_times'] ?? '09:00,14:00,20:00')) ?></b><small>计划时间点</small></div>
      </div>

      <div class="auto-card">
        <h3>📋 计划设置</h3>
        <form method="post" action="admin.php?m=ai_plan" class="fg">
          <label class="switch" style="margin:4px 0"><input type="checkbox" name="on" <?= ($cfg['ai_plan_on'] ?? '0') === '1' ? 'checked' : '' ?>> ⏰ 启用自动发文</label>
          <div class="field"><label>每日篇数</label><input type="number" name="perday" value="<?= e($cfg['ai_plan_perday'] ?? '3') ?>"></div>
          <div class="field"><label>发布时间点（逗号分隔，24h）</label><input name="times" value="<?= e($cfg['ai_plan_times'] ?? '09:00,14:00,20:00') ?>" placeholder="09:00,14:00,20:00"></div>
          <div class="field"><label>默认栏目</label>
            <select name="cat"><?php foreach ($cats as $c): ?><option value="<?= $c['id'] ?>" <?= (int)($cfg['ai_plan_cat'] ?? 0) === $c['id'] ? 'selected' : '' ?>><?= str_repeat('— ', (int)$c['pid'] !== 0 ? 1 : 0) ?><?= e($c['name']) ?></option><?php endforeach; ?></select>
          </div>
          <div class="field full"><label>关键词池（每行一个，AI 按顺序取用，循环不重复）</label><textarea name="kw" style="min-height:120px" placeholder="节日营销活动&#10;客户转介绍设计&#10;短视频获客"><?= e($cfg['ai_plan_kw'] ?? '') ?></textarea></div>
          <div class="field full"><label>补充要求（每篇通用）</label><textarea name="extra" placeholder="统一语气、结尾话术等"><?= e($cfg['ai_plan_extra'] ?? '') ?></textarea></div>
          <label class="switch" style="margin:4px 0"><input type="checkbox" name="img" <?= ($cfg['ai_plan_img'] ?? '1') === '1' ? 'checked' : '' ?>> ✨ 已配置生图 API 时每篇插图 2 张（未配置则纯文字无图，封面取正文第一张图）</label>
          <label class="switch" style="margin:4px 0"><input type="checkbox" name="censor" checked disabled> 🔒 自动过滤敏感词（米字代替）</label>
          <label class="switch" style="margin:4px 0"><input type="checkbox" name="publish" <?= ($cfg['ai_plan_publish'] ?? '1') === '1' ? 'checked' : '' ?>> 🚀 生成后直接发布（取消则存草稿）</label>
          <label class="switch" style="margin:4px 0"><input type="checkbox" name="seo" <?= ($cfg['ai_plan_seo'] ?? '1') === '1' ? 'checked' : '' ?>> 🔍 自动生成 SEO（标题/关键词/描述）</label>
          <label class="switch" style="margin:4px 0"><input type="checkbox" name="geo" <?= ($cfg['ai_plan_geo'] ?? '1') === '1' ? 'checked' : '' ?>> 🌐 自动生成 GEO（要点摘要 + FAQ）</label>
          <div class="field full" style="display:flex;gap:10px;align-items:center;margin-top:12px">
            <button class="btn btn-p" type="submit">保存计划</button>
            <a class="btn btn-s" href="admin.php?m=ai_post_now" onclick="return confirm('立即用随机关键词生成并发布一篇？')">▶ 立即跑一篇（测试）</a>
          </div>
        <?= csrf_field() ?>
</form>
      </div>

      <div class="auto-card">
        <h3>🏷 关键词使用状态</h3>
        <div>
          <?php
          $used = array_column(DB::all('SELECT keyword FROM ai_post_log WHERE site_id=? AND created_at>=? AND created_at<?', [$sid, date('Y-m-d 00:00:00'), date('Y-m-d 23:59:59')]) ?: [], 'keyword');
          foreach (array_filter(array_map('trim', explode("\n", $cfg['ai_plan_kw'] ?? ''))) as $kw):
          ?>
            <span class="kw-tag <?= in_array($kw, $used, true) ? 'done' : '' ?>"><?= e($kw) ?> <?= in_array($kw, $used, true) ? '✓' : '' ?></span>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="auto-card">
        <h3>📜 执行日志</h3>
        <div class="auto-log">
          <?php foreach (DB::all('SELECT keyword,model,has_image,created_at FROM ai_post_log WHERE site_id=? ORDER BY id DESC LIMIT 20', [$sid]) as $log): ?>
            <div>[<?= substr($log['created_at'], 5, 11) ?>] <b>自动发布</b>：<?= e($log['keyword']) ?>（<?= $log['model'] ?> · 插图 <?= $log['has_image'] ? '2 张 ✓' : '0' ?>）</div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="note">
        <b>触发机制（无需宝塔配置）：</b><br>
        ① <b>网页伪 Cron</b>：访客或你打开前台任意页面时，系统自动检查是否到发布时间点且今天篇数未达标 → 抓取下一个未用关键词 → DeepSeek 写文 → 若已配置生图 API 则根据文意用 ChatGPT 插 2 张图、封面自动取正文第一张图；未配置则纯文字无图 → 自动发布。<br>
        ② <b>防重复</b>：每篇发布后记录关键词到 <code>ai_post_log</code>，当日循环不刷屏。
      </div>
    </div>

  </div>
</div>
<script src="static/js/editor.js?v=<?= @filemtime(__DIR__ . '/../static/js/editor.js') ?: time() ?>"></script>
<script src="static/js/imgpick.js?v=<?= @filemtime(__DIR__ . '/../static/js/imgpick.js') ?: time() ?>"></script>
<script>
QEditor.init('f_content', { height: 380 });
ImgPick.init();
document.getElementById('f_cover').addEventListener('input', function () {
  var pv = document.getElementById('f_cover_img');
  if (this.value) { pv.src = this.value; pv.hidden = false; } else { pv.hidden = true; }
});

function editArticle(a) {
  switchTab('manual', document.querySelectorAll('.ai-tab')[1]);
  document.getElementById('f_id').value = a.id;
  document.getElementById('f_cat').value = a.cat_id;
  document.getElementById('f_title').value = a.title;
  document.getElementById('f_cover').value = a.cover || '';
  document.getElementById('f_tags').value = a.tags || '';
  document.getElementById('f_rec').value = a.recommend;
  document.getElementById('f_status').value = a.status;
  document.getElementById('f_summary').value = a.summary || '';
  QEditor.set('f_content', a.content || '');
  document.getElementById('f_st').value = a.seo_title || '';
  document.getElementById('f_sk').value = a.seo_keywords || '';
  document.getElementById('f_sd').value = a.seo_description || '';
  document.getElementById('f_geo_summary').value = a.geo_summary || '';
  document.getElementById('f_geo_faq').value = a.geo_faq || '';
  var pv = document.getElementById('f_cover_img');
  if (a.cover) { pv.src = a.cover; pv.hidden = false; }
}

/* ---------- Tab 切换 ---------- */
function switchTab(mode, el){
  document.querySelectorAll('.ai-tab').forEach(t=>t.classList.remove('active'));
  if(el) el.classList.add('active');
  var aiBox=document.getElementById('aiBox'), title=document.getElementById('panelTitle'),
      splitWrap=document.getElementById('splitWrap'), autoWrap=document.getElementById('autoWrap');
  if(mode==='plan'){
    aiBox.style.display='none';
    splitWrap.style.display='none';
    autoWrap.style.display='block';
    title.innerHTML='⚙ 自动发文计划 <span class="badge ok">网页配置 · 自动运行</span>';
    return;
  }
  autoWrap.style.display='none';
  splitWrap.style.display='grid';
  if(mode==='manual'){ aiBox.style.display='none'; title.innerHTML='✍️ 手动发布文章'; }
  else { aiBox.style.display='block'; title.innerHTML='🤖 AI 自动写文章 <span class="badge ok">全自动 · 已过滤</span>'; }
}

/* ---------- AI 生成 ---------- */
function gen(){
  var btn=document.getElementById('genBtn'),
      status=document.getElementById('status'),
      statusText=document.getElementById('statusText'),
      topic=document.getElementById('topic').value.trim();
  if(!topic){ alert('请输入主题/关键词'); return; }
  var genImg=document.getElementById('genImg').checked,
      genCensor=document.getElementById('genCensor').checked,
      genPublish=document.getElementById('genPublish').checked,
      genSeo=document.getElementById('genSeo').checked,
      genGeo=document.getElementById('genGeo').checked,
      words=parseInt(document.getElementById('words').value)||1200,
      tone=document.getElementById('tone').value,
      extra=document.getElementById('extra').value.trim();
  btn.disabled=true; status.classList.add('show'); statusText.textContent='① AI 正在写文章…';
  var fd=new FormData();
  fd.append('topic',topic); fd.append('words',words); fd.append('tone',tone); fd.append('extra',extra); fd.append('with_img',genImg?'1':'0'); fd.append('seo',genSeo?'1':'0'); fd.append('geo',genGeo?'1':'0');
  fetch('admin.php?m=ai_generate',{method:'POST',body:fd}).then(r=>r.json()).then(r=>{
    if(!r.ok){ statusText.textContent='⚠ '+r.msg; btn.disabled=false; return; }
    var content=r.content;
    document.getElementById('f_title').value=r.title;
    document.getElementById('f_tags').value=topic;
    document.getElementById('f_status').value=genPublish?'1':'0';
    QEditor.set('f_content', content);
    var m=content.match(/<img[^>]+src=["']([^"']+)["']/);
    if(m){ document.getElementById('f_cover').value=m[1]; }
    // AI 自动生成 SEO / GEO 并回填表单
    if(r.seo_title) document.getElementById('f_st').value=r.seo_title;
    if(r.seo_keywords) document.getElementById('f_sk').value=r.seo_keywords;
    if(r.seo_description) document.getElementById('f_sd').value=r.seo_description;
    if(r.geo_summary) document.getElementById('f_geo_summary').value=r.geo_summary;
    if(r.geo_faq) document.getElementById('f_geo_faq').value=r.geo_faq;
    // 自动展开 SEO / GEO 面板，让用户看到已生成的内容
    var seoBox=document.querySelector('details.seo-box');
    if(seoBox && genSeo){ seoBox.open=true; }
    var geoBoxes=document.querySelectorAll('details.seo-box');
    if(geoBoxes.length>1 && genGeo){ geoBoxes[1].open=true; }
    var badge=document.getElementById('censorBadge');
    badge.textContent='敏感词检测：'+r.hit+' 处已替换'; badge.className=r.hit>0?'badge warn':'badge ok';
    var seoTip=genSeo?' SEO':'', geoTip=genGeo?' GEO':'';
    if(r.img_count>0){ statusText.textContent='✅ 文章+'+r.img_count+'张插图已完成（'+seoTip+geoTip+' 已自动生成）'; }
    else if(r.img_err){ statusText.textContent='✅ 文章已完成（'+seoTip+geoTip+' 已自动生成），⚠ 插图失败：'+r.img_err; }
    else { statusText.textContent='✅ 文章已完成（'+seoTip+geoTip+' 已自动生成；未配置生图 API，纯文字无插图）'; }
    // 勾选"生成后直接发布"且为新建文章时，自动提交保存并发布
    if(genPublish){
      var fid=document.getElementById('f_id').value;
      if(fid==='0'||fid===''){
        document.getElementById('f_status').value='1';
        document.getElementById('f_content').value=document.getElementById('f_content').value||'';
        statusText.textContent='✅ 已生成，正在自动发布…';
        document.getElementById('articleForm').submit();
        return;
      }
    }
    btn.disabled=false;
  }).catch(e=>{ statusText.textContent='⚠ 请求失败：'+e.message; btn.disabled=false; });
}

/* ---------- AI SEO / GEO 共用接口 ---------- */
function aiSeo(type){
  var title=document.getElementById('f_title').value.trim();
  var content=document.getElementById('f_content').value||'';
  if(!title){alert('请先填写标题');return;}
  if(!confirm('根据标题与正文自动生成 SEO 三要素？'))return;
  var fd=new FormData();fd.append('title',title);fd.append('content',content);
  fetch('admin.php?m=ai_seo',{method:'POST',body:fd}).then(r=>r.json()).then(r=>{
    if(!r.ok){alert('⚠ '+r.msg);return;}
    if(r.seo_title)document.getElementById('f_st').value=r.seo_title;
    if(r.seo_keywords)document.getElementById('f_sk').value=r.seo_keywords;
    if(r.seo_description)document.getElementById('f_sd').value=r.seo_description;
    alert('✅ SEO 三要素已自动填写（请记得保存文章）');
  }).catch(e=>alert('⚠ 请求失败：'+e.message));
}
function aiGeo(type){
  var title=document.getElementById('f_title').value.trim();
  var content=document.getElementById('f_content').value||'';
  if(!title||!content){alert('请先填写标题与正文');return;}
  if(!confirm('AI 将把本文改写成结论先行+要点化+FAQ，并生成 JSON-LD 字段？'))return;
  var fd=new FormData();fd.append('title',title);fd.append('content',content);fd.append('type',type);
  fetch('admin.php?m=ai_geo',{method:'POST',body:fd}).then(r=>r.json()).then(r=>{
    if(!r.ok){alert('⚠ '+r.msg);return;}
    document.getElementById('f_geo_summary').value=r.summary||'';
    document.getElementById('f_geo_faq').value=JSON.stringify(r.faq||[],null,0);
    alert('✅ GEO 优化完成：已生成要点化摘要与 '+((r.faq||[]).length)+' 条 FAQ（请记得保存文章，前台将自动输出 JSON-LD）');
  }).catch(e=>alert('⚠ 请求失败：'+e.message));
}

function uploadCover(input){
  var fd=new FormData(); fd.append('file',input.files[0]);
  fetch('admin.php?m=upload_json',{method:'POST',body:fd}).then(r=>r.json()).then(r=>{
    if(r.ok){ document.getElementById('f_cover').value=r.url; var img=document.getElementById('f_cover_img'); img.src=r.url; img.hidden=false; }
    else { alert(r.msg||'上传失败'); }
  });
}
</script>
</body>
</html>
