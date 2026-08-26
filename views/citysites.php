<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>全国分站 - 得应盯后台</title>
<link rel="stylesheet" href="static/css/admin.css">
<script>
(function(){try{var t=localStorage.getItem('dy_theme')||'light';document.documentElement.setAttribute('data-theme',t);}catch(e){}})();
</script>
</head>
<body>
<div class="layout">
  <?php require __DIR__ . '/sidebar.php'; ?>
  <div class="main">
    <div class="topbar"><h1>全国分站</h1><div class="right"><span><?= e($admin_name) ?></span><form method="post" action="admin.php?m=logout" style="display:inline"><?= csrf_field() ?><button type="submit" class="logout-link">退出</button></form></div></div>
    <?php if (!empty($flash)): ?><div class="msg"><?= e($flash) ?></div><?php endif; ?>

    <div class="split-wrap">
      <!-- 左侧：分站开关 / 导入 / 编辑 -->
      <div class="split-form">
        <div class="panel">
          <h2>分站开关</h2>
          <form method="post" action="admin.php?m=city_enable" style="display:flex;flex-direction:column;gap:14px">
            <label style="font-size:13.5px;color:var(--muted)">开启后自动拥有全国站点，前台 URL 后缀为城市拼音（如 ?city=baoding）</label>
            <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
              <select name="enable" style="width:auto;display:inline-block">
                <option value="1" <?= $enable === '1' ? 'selected' : '' ?>>开启</option>
                <option value="0" <?= $enable !== '1' ? 'selected' : '' ?>>关闭</option>
              </select>
              <button class="btn btn-p" type="submit">保存开关</button>
            </div>
            <span style="color:var(--muted);font-size:12.5px">首次开启若城市为空，将自动导入全国 <?= count(require __DIR__ . '/../lib/cities.php') ?> 个城市</span>
          <?= csrf_field() ?>
</form>
        </div>

        <div class="panel">
          <h2>分站公告（前台城市页顶部横幅）</h2>
          <p style="color:var(--muted);font-size:13px;margin-bottom:12px">显示在每个城市分站页顶部，用于运营通知（如"建议一次 50 城市分批生成"）。清空保存则不显示。</p>
          <form method="post" action="admin.php?m=city_notice">
            <input type="text" name="notice" value="<?= e(setting('city_notice', '')) ?>" placeholder="如：🚀 全国分站批量建设中，建议每次生成 50 个城市，分批进行更稳妥" style="margin-bottom:10px">
            <button class="btn btn-p" type="submit">保存公告</button>
          <?= csrf_field() ?>
</form>
        </div>

        <div class="panel">
          <h2>一键导入全国分站</h2>
          <p style="color:var(--muted);font-size:13px;margin-bottom:12px">内置全国 200+ 城市（含拼音后缀）。已存在的城市自动跳过。</p>
          <form method="post" action="admin.php?m=city_import">
            <button class="btn btn-p" type="submit" onclick="return confirm('导入全国城市分站？')">一键导入全国分站</button>
          <?= csrf_field() ?>
</form>
        </div>

        <div class="panel">
          <h2>一键生成全部分站 SEO</h2>
          <p style="color:var(--muted);font-size:13px;margin-bottom:12px">为尚未填写 SEO 的分站自动生成<strong>独立标题/关键词/描述</strong>（每城不同，百度收录必需）。已填写的分站保持不变。</p>
          <form method="post" action="admin.php?m=city_tdk_all" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
            <input type="text" name="industry" value="网站建设" style="flex:1;min-width:180px" placeholder="行业词，如：网站建设 / SEO优化">
            <button class="btn btn-p" type="submit">生成全部分站 SEO</button>
          <?= csrf_field() ?>
</form>
        </div>

        <div class="panel">
          <h2>⚡ AI 智能生成 SEO <span style="color:var(--danger);font-size:11.5px;background:#fef2f2;padding:2px 8px;border-radius:8px;margin-left:6px;font-weight:500">更智能 · 防站群惩罚</span></h2>
          <p style="color:var(--muted);font-size:13px;margin-bottom:12px">用 AI 为每个城市独立写 SEO，<strong>意思一致但表达不同</strong>（避免统一模板被百度识别为站群）。已填过 SEO 的城市自动跳过（<strong>想强制覆盖</strong>：先到编辑表单清空该城的标题后缀，再来跑）。<br>每城约 3~5 秒，200 城约 15 分钟（DeepSeek 约 ¥0.1~0.2）。</p>
          <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-bottom:8px">
            <input type="text" id="ctIndustry" value="网站建设" placeholder="行业词" style="flex:1;min-width:150px">
            <input type="number" id="ctMax" value="50" min="1" max="300" style="width:90px" placeholder="上限">
            <button class="btn btn-p" id="ctBtn" onclick="cityTdkAiRun()">⚡ 开始 AI 智能生成</button>
          </div>
          <div class="note" id="ctStatus" style="display:none;margin-top:4px;font-size:12.5px;line-height:1.7;max-height:150px;overflow:auto"></div>
        </div>

        <div class="panel">
          <h2>全国分站内容批量生成（AI 逐城发文）</h2>
          <p style="color:var(--muted);font-size:13px;margin-bottom:12px">AI 为每个城市生成一篇「城市+行业」专属文章（标题/正文含城市名，自动发布到「自动发文计划」所选栏目）。<strong>每城约 20~60 秒</strong>，可随时中断，已生成的自动跳过。</p>
          <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-bottom:8px">
            <input type="text" id="cpIndustry" value="网站建设" style="flex:1;min-width:150px" placeholder="行业词">
            <input type="number" id="cpMax" value="50" min="1" max="300" style="width:90px" placeholder="上限">
            <button class="btn btn-p" id="cpBtn" onclick="cityPlanRun()">🚀 开始批量生成</button>
          </div>
          <div class="note" id="cpStatus" style="display:none;margin-top:4px;font-size:12.5px;line-height:1.7;max-height:150px;overflow:auto"></div>
        </div>

        <div class="panel">
          <h2>新增 / 编辑分站</h2>
          <form method="post" action="admin.php?m=city_save">
            <input type="hidden" name="id" id="f_id" value="0">
            <div class="fg">
              <div class="field"><label>城市名 *</label><input type="text" name="city" id="f_city" placeholder="如：保定"></div>
              <div class="field"><label>拼音后缀 *</label><input type="text" name="pinyin" id="f_py" placeholder="如：baoding"></div>
              <div class="field"><label>标题后缀</label><input type="text" name="title_suffix" id="f_ts" placeholder="如：- 保定分站"></div>
              <div class="field"><label>SEO 关键词</label><input type="text" name="keywords" id="f_kw"></div>
              <div class="field"><label>状态</label>
                <select name="status" id="f_status"><option value="1">启用</option><option value="0">停用</option></select>
              </div>
              <div class="field full"><label>SEO 描述</label><input type="text" name="description" id="f_desc"></div>
            </div>
            <div style="margin-top:14px"><button class="btn btn-p" type="submit">保存分站</button></div>
          <?= csrf_field() ?>
</form>
        </div>
      </div>

      <!-- 右侧：分站列表 -->
      <div class="split-list">
        <div class="panel">
          <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;margin-bottom:12px">
            <h2 style="margin:0">分站列表（<?= count($list) ?> 个城市）</h2>
            <div style="display:flex;gap:8px;flex-wrap:wrap">
              <form method="post" action="admin.php?m=city_clear_tdk" style="display:inline" onsubmit="return confirm('确定清空所有 <?= count($list) ?> 个分站的 SEO 字段？\n\n【清 SEO 按钮说明】\n✓ 清空：标题后缀 / 关键词 / 描述\n✓ 保留：城市本身（可重新跑模板或 AI 生成 SEO）\n✗ 不影响：分站开关、城市列表、栏目\n\n继续？')">
                <input type="hidden" name="confirm" value="1">
                <button type="submit" class="btn btn-s" style="background:#f59e0b;color:#fff;border:none">🧹 清 SEO 字段</button>
              <?= csrf_field() ?>
</form>
              <form method="post" action="admin.php?m=city_clear_all" style="display:inline" onsubmit="var n=<?= count($list) ?>;return confirm('⚠️ 高危操作：将彻底删除全部 ' + n + ' 个分站，不可恢复！\n\n【全删按钮说明】\n✗ 删除：所有城市分站（含 SEO 字段）\n✓ 保留：分站开关状态\n▶ 建议：删完立刻「一键导入全国分站」重建\n\n确认要删？')">
                <button type="submit" class="btn btn-s btn-d">🗑️ 全删分站</button>
              <?= csrf_field() ?>
</form>
            </div>
          </div>
          <table>
            <thead><tr><th>ID</th><th>城市</th><th>拼音后缀</th><th>标题后缀</th><th>状态</th><th>前台链接</th><th>操作</th></tr></thead>
            <tbody>
              <?php foreach ($list as $c): ?>
              <tr>
                <td><?= $c['id'] ?></td>
                <td><?= e($c['city']) ?></td>
                <td><code><?= e($c['pinyin']) ?></code></td>
                <td><?= e($c['title_suffix']) ?></td>
                <td><?= $c['status'] ? '<span class="tag tag-ok">启用</span>' : '<span class="tag tag-off">停用</span>' ?></td>
                <td><a href="<?= e(city_url($c)) ?>" target="_blank" style="color:#4f46e5">访问 →</a></td>
                <td>
                  <button class="btn btn-s" onclick='edit(<?= json_encode($c, JSON_UNESCAPED_UNICODE) ?>)'>编辑</button>
                  <form method="post" action="admin.php?m=city_del" style="display:inline" onsubmit="return confirm('删除分站？')">
                    <input type="hidden" name="id" value="<?= $c['id'] ?>">
                    <button class="btn btn-s btn-d" type="submit">删除</button>
                  <?= csrf_field() ?>
</form>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
var CSRF = <?= json_encode(csrf_token()) ?>;
var ALL_CITIES = <?= json_encode(array_values(array_filter(array_map(function ($c) {
    return ['id' => (int)$c['id'], 'city' => (string)$c['city'], 'status' => (int)$c['status']];
}, $list), function ($c) { return $c['status'] === 1; }))) ?>;
function edit(c) {
  document.getElementById('f_id').value = c.id;
  document.getElementById('f_city').value = c.city;
  document.getElementById('f_py').value = c.pinyin || '';
  document.getElementById('f_ts').value = c.title_suffix || '';
  document.getElementById('f_kw').value = c.keywords || '';
  document.getElementById('f_desc').value = c.description || '';
  document.getElementById('f_status').value = c.status;
}
/* ---------- 全国分站内容批量生成（AI 逐城发文，串行防超时） ---------- */
function cityPlanRun() {
  var industry = (document.getElementById('cpIndustry').value || '').trim() || '网站建设';
  var max = parseInt(document.getElementById('cpMax').value) || 50;
  var btn = document.getElementById('cpBtn');
  var st = document.getElementById('cpStatus');
  if (!btn || !st) { return; }
  if (!ALL_CITIES.length) { alert('请先「一键导入全国分站」'); return; }
  if (!confirm('将为最多 ' + max + ' 个城市各生成一篇「城市+' + industry + '」文章（每篇约 20~60 秒），继续？')) return;
  btn.disabled = true;
  st.style.display = 'block';
  var i = 0, done = 0, okN = 0, skipN = 0, failN = 0;
  function finish(msg) {
    st.textContent = msg;
    btn.disabled = false;
  }
  (function next() {
    if (i >= ALL_CITIES.length || done >= max) {
      finish('🎉 本批完成：成功 ' + okN + '，跳过 ' + skipN + '，失败 ' + failN + '。可再次点击继续生成剩余城市。');
      return;
    }
    var c = ALL_CITIES[i++];
    st.textContent = '⏳ [' + i + '/' + ALL_CITIES.length + '] 正在为《' + c.city + industry + '》生成…（每篇约 20~60 秒，请勿关闭页面）';
    var fd = new FormData();
    fd.append('city_id', c.id);
    fd.append('industry', industry);
    fd.append('csrf', CSRF);
    fetch('admin.php?m=city_plan_run', { method: 'POST', body: fd })
      .then(function (r) { return r.json(); })
      .then(function (r) {
        done++;
        if (r.ok) { okN++; st.textContent = '✅ [' + i + '/' + ALL_CITIES.length + '] ' + c.city + '：' + (r.msg || '完成'); }
        else if (r.dup) { skipN++; st.textContent = '⏭ [' + i + '/' + ALL_CITIES.length + '] ' + c.city + ' 已存在相关文章，跳过'; }
        else { failN++; st.textContent = '⚠ [' + i + '/' + ALL_CITIES.length + '] ' + c.city + ' 失败：' + (r.msg || '未知原因'); }
        next();
      })
      .catch(function (e) {
        done++; failN++;
        st.textContent = '⚠ [' + i + '/' + ALL_CITIES.length + '] ' + c.city + ' 请求失败：' + e.message + '（可再次点击从失败城市继续）';
        next();
      });
  })();
}

/* ---------- AI 智能生成分站 SEO（每城不同措辞但意思一致） ---------- */
function cityTdkAiRun(){
  var industry = (document.getElementById('ctIndustry').value || '').trim() || '网站建设';
  var max = parseInt(document.getElementById('ctMax').value) || 50;
  var btn = document.getElementById('ctBtn');
  var st = document.getElementById('ctStatus');
  if (!btn || !st) { return; }
  if (!ALL_CITIES.length) { alert('请先「一键导入全国分站」'); return; }
  var list = ALL_CITIES.slice(0, max);
  if (!confirm('AI 将为 ' + list.length + ' 个城市每城独立生成 SEO（每城约 3~5 秒，共约 ' + Math.ceil(list.length * 4 / 60) + ' 分钟）。\n已填过 SEO 的城市自动跳过。\n继续？')) { return; }
  btn.disabled = true;
  st.style.display = 'block';
  var i = 0, okN = 0, skipN = 0, failN = 0;
  function finish(m) { st.textContent = m; btn.disabled = false; }
  (function next(){
    if (i >= list.length) {
      finish('🎉 本批完成：AI 新增 ' + okN + '，跳过 ' + skipN + '（已有 SEO），失败 ' + failN + '。可再次点击继续生成剩余城市。');
      return;
    }
    var c = list[i++];
    st.textContent = '⏳ [' + i + '/' + list.length + '] 正在为《' + c.city + '》AI 生成 SEO…（约 3~5 秒）';
    var fd = new FormData();
    fd.append('city_id', c.id);
    fd.append('industry', industry);
    fd.append('csrf', CSRF);
    fetch('admin.php?m=ai_city_tdk_one', { method: 'POST', body: fd })
      .then(function (r) { return r.json(); })
      .then(function (r) {
        if (r.ok) { okN++; st.textContent = '✅ [' + i + '/' + list.length + '] ' + c.city + '：' + (r.title_suffix || r.msg || '完成'); }
        else if (r.dup) { skipN++; st.textContent = '⏭ [' + i + '/' + list.length + '] ' + c.city + ' 已有 SEO，跳过'; }
        else { failN++; st.textContent = '⚠ [' + i + '/' + list.length + '] ' + c.city + ' 失败：' + (r.msg || '未知原因'); }
        next();
      })
      .catch(function (e) { failN++; st.textContent = '⚠ [' + i + '/' + list.length + '] ' + c.city + ' 请求失败：' + e.message; next(); });
  })();
}
</script>
</body>
</html>
