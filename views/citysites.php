<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>全国分站 - 得应盯后台</title>
<link rel="stylesheet" href="static/css/admin.css">
<style>
/* ====== 全国分站重构专属样式（暗色科技风 × 状态徽章 × 省份分组） ====== */
.cs-stats{display:grid;grid-template-columns:repeat(6,1fr);gap:14px;margin-bottom:18px}
.cs-stat{padding:14px 16px;background:var(--card);border:1px solid var(--line);border-radius:var(--radius);position:relative;overflow:hidden;box-shadow:var(--shadow)}
.cs-stat .label{font-size:12px;color:var(--muted);margin-bottom:6px;display:flex;align-items:center;gap:6px}
.cs-stat .num{font-size:24px;font-weight:800;background:linear-gradient(90deg,var(--primary),var(--primary-2));-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent}
.cs-stat .num.ok{background:linear-gradient(90deg,#10b981,#22d3ee);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent}
.cs-stat .num.warn{background:linear-gradient(90deg,#f59e0b,#fbbf24);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent}
.cs-stat .num.bad{background:linear-gradient(90deg,#ef4444,#f472b6);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent}
.cs-stat .num.muted{color:var(--muted)}
.province-group{margin-bottom:14px;border:1px solid var(--line);border-radius:var(--radius);background:var(--card);overflow:hidden}
.province-head{display:flex;align-items:center;justify-content:space-between;padding:11px 16px;background:var(--bg);cursor:pointer;font-weight:700;font-size:13.5px}
.province-head .left{display:flex;align-items:center;gap:10px}
.province-head .toggle{font-size:11px;color:var(--muted);transition:transform .2s}
.province-head .toggle.open{transform:rotate(90deg)}
.province-stats{font-size:11.5px;color:var(--muted);font-weight:500}
.province-stats .ok{color:#10b981}.province-stats .warn{color:#f59e0b}.province-stats .bad{color:#ef4444}.province-stats .empty{color:var(--muted)}
.province-body{border-top:1px solid var(--line)}
.badge{display:inline-flex;align-items:center;gap:4px;font-size:11px;padding:3px 9px;border-radius:999px;font-weight:600;white-space:nowrap}
.badge.ok{background:rgba(16,185,129,.12);color:#10b981}
.badge.warn{background:rgba(245,158,11,.14);color:#f59e0b}
.badge.bad{background:rgba(239,68,68,.12);color:#ef4444}
.badge.run{background:rgba(34,211,238,.14);color:#22d3ee;animation:pulse 1.4s infinite}
.badge.empty{background:var(--bg);color:var(--muted)}
.badge.seo{background:rgba(79,70,229,.12);color:var(--primary)}
@keyframes pulse{0%{opacity:1}50%{opacity:.5}100%{opacity:1}}
.cs-table{width:100%;border-collapse:collapse}
.cs-table th,.cs-table td{padding:11px 14px;text-align:left;border-bottom:1px solid var(--line);font-size:13px;vertical-align:middle}
.cs-table th{font-size:11.5px;color:var(--muted);background:var(--bg);font-weight:600;text-transform:uppercase;letter-spacing:.5px}
.cs-table tbody tr:hover{background:rgba(79,70,229,.04)}
.cs-table .ct{font-weight:700;font-size:14px;color:var(--text)}
.cs-table .py{font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:11.5px;color:var(--muted);background:var(--bg);padding:2px 7px;border-radius:5px}
.cs-table .tsuf{max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:12.5px;color:var(--faint)}
.cs-table .err{font-size:11.5px;color:#ef4444;max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.cs-actions{display:flex;gap:6px;flex-wrap:wrap}
.cs-actions .btn{padding:5px 11px;font-size:12px;border-radius:6px}
.cs-progress{height:6px;background:var(--bg);border-radius:3px;overflow:hidden;margin-top:6px}
.cs-progress>i{display:block;height:100%;background:linear-gradient(90deg,#10b981,#22d3ee);transition:width .4s}
.cs-progress.warn>i{background:linear-gradient(90deg,#f59e0b,#fbbf24)}
.cs-modal-bg{position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:99;display:none;align-items:flex-start;justify-content:center;padding-top:5vh;overflow-y:auto}
.cs-modal-bg.open{display:flex}
.cs-modal{background:var(--card);border-radius:12px;width:min(880px,92vw);max-height:88vh;display:flex;flex-direction:column;box-shadow:0 30px 80px rgba(0,0,0,.25);overflow:hidden}
.cs-modal-head{display:flex;align-items:center;justify-content:space-between;padding:14px 18px;border-bottom:1px solid var(--line)}
.cs-modal-head .t{font-size:15px;font-weight:700;display:flex;align-items:center;gap:8px}
.cs-modal-head .x{cursor:pointer;font-size:22px;color:var(--muted);background:none;border:none;line-height:1}
.cs-modal-body{padding:18px;overflow-y:auto;flex:1}
.cs-modal-foot{padding:13px 18px;border-top:1px solid var(--line);display:flex;justify-content:flex-end;gap:10px}
.cs-field{margin-bottom:13px}
.cs-field label{display:block;font-size:12.5px;color:var(--muted);margin-bottom:5px;font-weight:600}
.cs-field .req{color:#ef4444;margin-left:2px}
.cs-field input[type=text],.cs-field textarea{width:100%;border:1px solid var(--line);border-radius:6px;padding:9px 11px;font-size:13px;background:var(--bg);color:var(--text);font-family:inherit}
.cs-field textarea{min-height:280px;font-family:ui-monospace,SFMono-Regular,Menlo,monospace;line-height:1.7}
@media (max-width:960px){.cs-stats{grid-template-columns:repeat(3,1fr)}}
@media (max-width:640px){.cs-stats{grid-template-columns:repeat(2,1fr)}.cs-table th:nth-child(4),.cs-table td:nth-child(4),.cs-table th:nth-child(5),.cs-table td:nth-child(5){display:none}}
</style>
<script>
(function(){try{var t=localStorage.getItem('dy_theme')||'light';document.documentElement.setAttribute('data-theme',t);}catch(e){}})();
</script>
</head>
<body>
<div class="layout">
  <?php require __DIR__ . '/sidebar.php'; ?>
  <div class="main">
    <div class="topbar"><h1>🗺️ 全国分站</h1>
      <div class="right">
        <span style="font-size:12.5px;color:var(--muted);margin-right:8px">重构版 · 2025 · 内容写分站表 · 省份分组 · 精确去重</span>
        <span><?= e($admin_name) ?></span>
        <form method="post" action="admin.php?m=logout" style="display:inline"><?= csrf_field() ?><button type="submit" class="logout-link">退出</button></form>
      </div>
    </div>
    <?php if (!empty($flash)): ?><div class="msg"><?= e($flash) ?></div><?php endif; ?>

<?php
// 0) 老库兼容：强制确保 city_sites 新增字段已 ALTER（即便 ensure_schema 漏跑也能兜底）
//    —— 这是 views 层的二次保险，确保数据齐
$_schemaFixes = [
    'province'        => 'ALTER TABLE city_sites ADD COLUMN province VARCHAR(20) NOT NULL DEFAULT "" COMMENT "所属省份" AFTER pinyin',
    'content'         => 'ALTER TABLE city_sites ADD COLUMN content MEDIUMTEXT COMMENT "分站专属正文" AFTER province',
    'content_title'   => 'ALTER TABLE city_sites ADD COLUMN content_title VARCHAR(200) NOT NULL DEFAULT "" COMMENT "分站内容标题" AFTER content',
    'content_at'      => 'ALTER TABLE city_sites ADD COLUMN content_at DATETIME DEFAULT NULL COMMENT "内容生成时间" AFTER content_title',
    'content_status'  => 'ALTER TABLE city_sites ADD COLUMN content_status TINYINT NOT NULL DEFAULT 0 COMMENT "内容状态 0未/1已/2中/3失败" AFTER content_at',
    'content_err'     => 'ALTER TABLE city_sites ADD COLUMN content_err VARCHAR(255) NOT NULL DEFAULT "" COMMENT "生成失败原因" AFTER content_status',
    'article_id'      => 'ALTER TABLE city_sites ADD COLUMN article_id INT UNSIGNED NOT NULL DEFAULT 0 COMMENT "关联文章ID" AFTER content_err',
];
if (!isset($_CITYSITES_SCHEMA_FIXED)) {
    foreach ($_schemaFixes as $_col => $_sql) {
        try {
            DB::one("SELECT `$_col` FROM city_sites LIMIT 1");
        } catch (Throwable $_e) {
            try { DB::run($_sql); } catch (Throwable $_e2) { /* 列已存在 或权限不足，忽略 */ }
        }
    }
    $_CITYSITES_SCHEMA_FIXED = true;
}
unset($_schemaFixes, $_col, $_sql, $_e, $_e2);

// 1) 计算统计：按城市 status/content_status/tkd 三维度聚合
$statsAll = ['total' => 0, 'on' => 0, 'content_ok' => 0, 'content_empty' => 0, 'content_bad' => 0, 'content_run' => 0, 'seo_ok' => 0, 'seo_empty' => 0];
$grouped = []; // province => [cities]
$overall = ['total' => 0, 'content_ok' => 0, 'content_empty' => 0, 'content_bad' => 0, 'content_run' => 0, 'seo_ok' => 0, 'seo_empty' => 0];

// 全国分站重构（2025）：老库兼容 —— 即使 ensure_schema 漏补某新字段，视图也能跑
//（不让 "Undefined array key province" Warning 渲染到 HTML 表格里）
$_reqCols = ['province','content','content_title','content_at','content_status','content_err','article_id'];
foreach ($list as &$_c) { foreach ($_reqCols as $_col) { if (!array_key_exists($_col, $_c)) { $_c[$_col] = ''; } } }
unset($_c, $_reqCols, $_col);

foreach ($list as $c) {
    $prov = trim((string)($c['province'] ?? ''));
    if ($prov === '') { $prov = '未分组'; }
    $grouped[$prov][] = $c;
    $statsAll['total']++;
    if ((int)($c['status'] ?? 0) === 1) { $statsAll['on']++; }
    $cs = (int)($c['content_status'] ?? 0);
    if ($cs === 1 && trim((string)($c['content'] ?? '')) !== '') { $statsAll['content_ok']++; $overall['content_ok']++; }
    elseif ($cs === 3) { $statsAll['content_bad']++; $overall['content_bad']++; }
    elseif ($cs === 2) { $statsAll['content_run']++; $overall['content_run']++; }
    else { $statsAll['content_empty']++; $overall['content_empty']++; }
    // SEO 是否完整：title_suffix + keywords 都非空
    if (trim((string)($c['title_suffix'] ?? '')) !== '' && trim((string)($c['keywords'] ?? '')) !== '') {
        $statsAll['seo_ok']++;
    } else {
        $statsAll['seo_empty']++;
    }
}
// 按省份中文排序（北京/上海/天津/重庆 直辖市置顶，其他按拼音）
ksort($grouped);
// 直辖市置顶
$top = ['北京','上海','天津','重庆'];
$rest = [];
foreach ($grouped as $k=>$_) { if (!in_array($k, $top, true)) { $rest[$k] = $grouped[$k]; unset($grouped[$k]); } }
foreach (array_reverse($top) as $p) { if (isset($grouped[$p])) { $tmp = $grouped[$p]; unset($grouped[$p]); $grouped = [$p => $tmp] + $grouped; } }
$grouped = $grouped + $rest;
?>
    <!-- ===== 顶部统计卡 ===== -->
    <div class="cs-stats">
      <div class="cs-stat"><div class="label">🏙️ 分站总数</div><div class="num"><?= number_format($statsAll['total']) ?></div><div class="label" style="font-size:11px">启用 <?= $statsAll['on'] ?> · 停用 <?= $statsAll['total'] - $statsAll['on'] ?></div></div>
      <div class="cs-stat"><div class="label">✅ 已生成分站内容</div><div class="num ok"><?= number_format($statsAll['content_ok']) ?></div><div class="cs-progress" title="完成率 <?= $statsAll['total'] > 0 ? round($statsAll['content_ok'] / $statsAll['total'] * 100) : 0 ?>%"><i style="width:<?= $statsAll['total'] > 0 ? round($statsAll['content_ok'] / $statsAll['total'] * 100) : 0 ?>%"></i></div></div>
      <div class="cs-stat"><div class="label">⏳ 待生成</div><div class="num warn"><?= number_format($statsAll['content_empty']) ?></div><div class="label" style="font-size:11px">点上方「批量生成」跑一批</div></div>
      <div class="cs-stat"><div class="label">⚠ 生成失败</div><div class="num bad"><?= number_format($statsAll['content_bad']) ?></div><div class="label" style="font-size:11px">点行内「重新生成」</div></div>
      <div class="cs-stat"><div class="label">🔍 已填 SEO</div><div class="num"><?= number_format($statsAll['seo_ok']) ?></div><div class="label" style="font-size:11px">关键词+描述</div></div>
      <div class="cs-stat"><div class="label">📊 完成率</div><div class="num <?= $statsAll['content_ok'] === $statsAll['total'] && $statsAll['total'] > 0 ? 'ok' : 'muted' ?>"><?= $statsAll['total'] > 0 ? round($statsAll['content_ok'] / $statsAll['total'] * 100) : 0 ?>%</div><div class="label" style="font-size:11px">内容 / 总数</div></div>
    </div>

    <div class="split-wrap">
      <!-- ===== 左侧：分站开关 / 公告 / 导入 / 批量生成 ===== -->
      <div class="split-form">
        <div class="panel">
          <h2>① 分站开关</h2>
          <form method="post" action="admin.php?m=city_enable" style="display:flex;flex-direction:column;gap:14px">
            <label style="font-size:13.5px;color:var(--muted)">开启后自动获得全国站点，前台 URL 后缀为城市拼音（如 <code>?city=baoding</code> 或 <code>/baoding/</code>）</label>
            <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
              <select name="enable" style="width:auto;display:inline-block">
                <option value="1" <?= $enable === '1' ? 'selected' : '' ?>>开启</option>
                <option value="0" <?= $enable !== '1' ? 'selected' : '' ?>>关闭</option>
              </select>
              <button class="btn btn-p" type="submit">保存开关</button>
            </div>
            <span style="color:var(--muted);font-size:12.5px">首次开启若城市为空，将自动导入全国 <?= count(require __DIR__ . '/../lib/cities.php') ?> 个城市（含 34 个省级行政区）</span>
            <?= csrf_field() ?>
          </form>
        </div>

        <div class="panel" style="border-color:var(--primary)">
          <h2>② 分站顶部品牌横幅</h2>
          <p style="color:var(--muted);font-size:13px;margin-bottom:12px">
            <strong>显示在每个城市分站页顶部</strong>，是强化品牌曝光的核心位置。
            <br>📍 <strong>品牌名自动取自</strong>：<a href="admin.php?m=tpl_edit" style="color:var(--primary);text-decoration:underline"><strong>模板中心 → 基础设置 → 站点名称</strong></a>，无需在此处重复填写。
          </p>
          <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;padding:14px 16px;background:linear-gradient(115deg,#fde68a,#fbbf24);border-radius:8px;margin-bottom:12px">
            <span style="font-size:13px;color:#451a03;font-weight:600">当前品牌名：</span>
            <strong style="font-size:16px;color:#451a03;background:rgba(255,255,255,.5);padding:4px 12px;border-radius:6px"><?= e(setting('site_name', '得应盯')) ?></strong>
            <a class="btn btn-s" href="admin.php?m=tpl_edit" style="background:rgba(69,26,3,.18);color:#451a03;border:none">⚙ 修改 →</a>
          </div>
          <details style="margin-top:8px;font-size:12.5px;color:var(--muted)">
            <summary style="cursor:pointer;color:var(--primary);font-weight:600">▶ 还想加一行「运营通知」？（可选，公告没人看的话就别设）</summary>
            <form method="post" action="admin.php?m=city_notice" style="margin-top:10px">
              <input type="text" name="notice" value="<?= e(setting('city_notice', '')) ?>" placeholder="如：全国分站批量建设中（默认隐藏，留空即可）" style="margin-bottom:10px">
              <button class="btn btn-p" type="submit">保存公告</button>
              <?= csrf_field() ?>
            </form>
          </details>
        </div>

        <div class="panel">
          <h2>③ 一键导入全国分站</h2>
          <p style="color:vaAR(--muted);font-size:13px;margin-bottom:12px">内置全国 34 省级行政区 × 城市（含拼音后缀）。<strong>已存在的城市自动跳过</strong>，可重复点击（幂等）。</p>
          <form method="post" action="admin.php?m=city_import">
            <button class="btn btn-p" type="submit" onclick="return confirm('导入全国 301 个城市分站？（已存在的城市会跳过）')">🗺️ 一键导入全国分站（含省份）</button>
            <?= csrf_field() ?>
          </form>
        </div>

        <!-- ===== 批量生成内容（核心） ===== -->
        <div class="panel" style="border-color:var(--primary)">
          <h2>⚡ ④ 一键 AI 批量生成分站内容
            <span style="color:var(--danger);font-size:11.5px;background:#fef2f2;padding:2px 8px;border-radius:8px;margin-left:6px;font-weight:500">写回分站表 · 精确去重</span>
          </h2>
          <p style="color:var(--muted);font-size:13px;margin-bottom:12px">
            AI 为每个城市生成一篇「<strong>城市 + 行业</strong>」专属内容（标题/正文/小标题/可操作建议），<strong>直接写入分站表</strong>、不再写全局文章。
            <br>
            ✅ 精确去重：已生成（<code>content_status=1</code>）的城市自动跳过，避免"已写过的又重新写"。
            <br>
            🛡️ 反 AI 思考泄露：自动过滤 <code>reasoning_content</code> 等思考字段，正文纯净。
            <br>
            ⏱ 每城约 15~30 秒（受 AI 网速影响）。<?= $statsAll['content_empty'] > 0 ? '<strong>当前待生成 ' . $statsAll['content_empty'] . ' 城</strong>。' : '<span style="color:var(--ok)">全部城市已生成内容 🎉</span>' ?>
          </p>
          <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-bottom:8px">
            <input type="text" id="cpIndustry" value="<?= e(setting('seo_keywords', '')) ? 'AI 服务' : 'AI 服务' ?>" placeholder="行业词（如 AI 员工培训）" style="flex:1;min-width:160px">
            <input type="number" id="cpMax" value="50" min="1" max="301" style="width:90px" placeholder="上限">
            <label style="display:flex;align-items:center;gap:5px;font-size:12.5px;color:var(--muted);cursor:pointer"><input type="checkbox" id="cpForce"> 强制重生成（清掉已有内容）</label>
            <button class="btn btn-p" id="cpBtn" onclick="cityContentRun()">⚡ 开始 AI 批量生成内容（<?= $statsAll['content_empty'] ?> 待生成）</button>
          </div>
          <div class="note" id="cpStatus" style="display:none;margin-top:4px;font-size:12.5px;line-height:1.7;max-height:180px;overflow:auto;background:var(--bg);border-radius:6px;padding:10px 12px"></div>
        </div>

        <!-- ===== 一键 AI 重新调整分站 SEO（独立于内容） ===== -->
        <div class="panel">
          <h2>🔍 ⑤ 一键 AI 重新调整分站 SEO <span style="color:var(--ok);font-size:11.5px;background:rgba(16,185,129,.12);padding:2px 8px;border-radius:8px;margin-left:6px;font-weight:500">防站群惩罚</span></h2>
          <p style="color:var(--muted);font-size:13px;margin-bottom:12px">用 AI（DeepSeek 等写作 API）为每个城市<strong>独立写差异化 SEO</strong>（title_suffix/keywords/description）。<br>
          已填过 SEO 的城市自动跳过。想强制重生成：列表右上角点「🧹 清 SEO」。<br>
          每城约 3~5 秒。</p>
          <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-bottom:8px">
            <input type="text" id="ctIndustry" value="AI 服务" placeholder="行业词" style="flex:1;min-width:150px">
            <input type="number" id="ctMax" value="50" min="1" max="301" style="width:90px" placeholder="上限">
            <button class="btn btn-p" id="ctBtn" onclick="cityTdkAiRun()">🚀 开始 AI 智能生成 SEO</button>
          </div>
          <div class="note" id="ctStatus" style="display:none;margin-top:4px;font-size:12.5px;line-height:1.7;max-height:180px;overflow:auto;background:var(--bg);border-radius:6px;padding:10px 12px"></div>
        </div>

        <!-- ===== 新增/编辑分站 ===== -->
        <div class="panel">
          <h2>⑥ 新增 / 编辑分站</h2>
          <form method="post" action="admin.php?m=city_save">
            <input type="hidden" name="id" id="f_id" value="0">
            <div class="fg">
              <div class="field"><label>城市名 <span class="req">*</span></label><input type="text" name="city" id="f_city" placeholder="如：保定"></div>
              <div class="field"><label>拼音后缀 <span class="req">*</span></label><input type="text" name="pinyin" id="f_py" placeholder="如：baoding"></div>
              <div class="field"><label>所属省份</label><input type="text" name="province" id="f_prov" placeholder="如：河北（留空自动识别内置数据）"></div>
              <div class="field"><label>标题后缀</label><input type="text" name="title_suffix" id="f_ts" placeholder="如：- 保定分站"></div>
              <div class="field"><label>SEO 关键词</label><input type="text" name="keywords" id="f_kw"></div>
              <div class="field"><label>状态</label><select name="status" id="f_status"><option value="1">启用</option><option value="0">停用</option></select></div>
              <div class="field full"><label>SEO 描述</label><input type="text" name="description" id="f_desc"></div>
            </div>
            <div style="margin-top:14px"><button class="btn btn-p" type="submit">保存分站</button></div>
            <?= csrf_field() ?>
          </form>
        </div>
      </div>

      <!-- ===== 右侧：省份分组的分站列表 + 操作 ===== -->
      <div class="split-list">
        <div class="panel">
          <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;margin-bottom:14px">
            <h2 style="margin:0">🗺️ 分站列表（按省份分组 · <?= $statsAll['total'] ?> 城）</h2>
            <div style="display:flex;gap:8px;flex-wrap:wrap">
              <button class="btn btn-s" type="button" onclick="csExpandAll(true)">▼ 全部展开</button>
              <button class="btn btn-s" type="button" onclick="csExpandAll(false)">▶ 全部折叠</button>
              <form method="post" action="admin.php?m=city_clear_tdk" style="display:inline" onsubmit="return confirm('确定清空所有分站的 SEO 字段？\n\n✓ 清空：标题后缀 / 关键词 / 描述\n✓ 保留：城市本身与分站内容（可重新跑模板或 AI 生成 SEO）\n✗ 不影响：分站开关、城市列表、栏目\n\n继续？')">
                <input type="hidden" name="confirm" value="1">
                <button type="submit" class="btn btn-s" style="background:#f59e0b;color:#fff;border:none">🧹 清 SEO</button>
                <?= csrf_field() ?>
              </form>
              <form method="post" action="admin.php?m=city_clear_content" style="display:inline" onsubmit="return confirm('⚠️ 清空所有分站的「内容」字段？\n\n✓ 清空：content_title / content / content_at / content_err\n✓ 保留：SEO 字段、城市本身\n▶ 后续：可重新跑 AI 内容生成\n\n继续？')">
                <button type="submit" class="btn btn-s" style="background:#22d3ee;color:#fff;border:none">🧽 清内容</button>
                <?= csrf_field() ?>
              </form>
              <form method="post" action="admin.php?m=city_clear_all" style="display:inline" onsubmit="var n=<?= $statsAll['total'] ?>;return confirm('⚠️ 高危操作：将彻底删除全部 ' + n + ' 个分站！\n\n【全删按钮说明】\n✗ 删除：所有城市分站（含 SEO + 内容字段）\n✓ 保留：分站开关状态\n▶ 建议：删完立刻「一键导入全国分站」重建\n\n确认要删？')">
                <button type="submit" class="btn btn-s btn-d">🗑️ 全删分站</button>
                <?= csrf_field() ?>
              </form>
            </div>
          </div>
<?php if ($statsAll['total'] === 0): ?>
          <div class="q-empty" style="padding:40px 0"><p style="text-align:center;color:var(--muted)">暂无分站。请先开启「分站开关」或「一键导入全国分站」</p></div>
<?php else: ?>
          <div class="cs-list">
<?php foreach ($grouped as $prov => $cities): ?>
  <?php
    $pOk = $pEmpty = $pBad = $pRun = 0;
    foreach ($cities as $c) {
        $cs = (int)($c['content_status'] ?? 0);
        if ($cs === 1 && trim((string)($c['content'] ?? '')) !== '') { $pOk++; }
        elseif ($cs === 3) { $pBad++; }
        elseif ($cs === 2) { $pRun++; }
        else { $pEmpty++; }
    }
  ?>
            <div class="province-group" data-province="<?= e($prov) ?>">
              <div class="province-head" onclick="csToggle(this)">
                <div class="left">
                  <span class="toggle">▶</span>
                  <span>📍 <?= e($prov) ?></span>
                  <span class="province-stats">
                    <span class="ok">✓ <?= $pOk ?></span>
                    <?php if ($pEmpty > 0): ?><span class="empty">空 <?= $pEmpty ?></span><?php endif; ?>
                    <?php if ($pRun > 0): ?><span class="empty" style="color:#22d3ee">⏳ <?= $pRun ?></span><?php endif; ?>
                    <?php if ($pBad > 0): ?><span class="bad">⚠ <?= $pBad ?></span><?php endif; ?>
                    共 <?= count($cities) ?> 城
                  </span>
                </div>
                <span style="font-size:11.5px;color:var(--muted)">点击展开</span>
              </div>
              <div class="province-body" style="display:none">
                <table class="cs-table">
                  <thead><tr>
                    <th>城市</th><th>拼音</th><th>分站内容</th><th>标题后缀</th><th>SEO</th><th>生成时间</th><th>操作</th>
                  </tr></thead>
                  <tbody>
                  <?php foreach ($cities as $c): ?>
                    <tr data-cid="<?= (int)($c['id'] ?? 0) ?>" data-city="<?= e($c['city'] ?? '') ?>" data-pinyin="<?= e($c['pinyin'] ?? '') ?>" data-province="<?= e($c['province'] ?? '') ?>" data-status="<?= (int)($c['status'] ?? 0) ?>">
                      <td><span class="ct"><?= e($c['city'] ?? '') ?></span>
                        <?php if ((int)($c['status'] ?? 0) === 0): ?><span class="badge empty" style="margin-left:6px">停用</span><?php endif; ?>
                      </td>
                      <td><code class="py"><?= e($c['pinyin'] ?? '') ?></code></td>
                      <td>
<?php $cs = (int)($c['content_status'] ?? 0); ?>
<?php if ($cs === 1 && trim((string)($c['content'] ?? '')) !== ''): ?>
                        <span class="badge ok">✓ 已生成</span>
                        <?php if (trim((string)($c['content_title'] ?? '')) !== ''): ?>
                        <div style="font-size:11.5px;color:var(--muted);margin-top:4px;max-width:240px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="<?= e($c['content_title'] ?? '') ?>"><?= e(mb_substr((string)($c['content_title'] ?? ''), 0, 26)) ?></div>
                        <?php endif; ?>
<?php elseif ($cs === 2): ?>
                        <span class="badge run">⏳ 生成中</span>
<?php elseif ($cs === 3): ?>
                        <span class="badge bad">⚠ 失败</span>
                        <?php if (!empty($c['content_err'])): ?>
                        <div class="err" title="<?= e($c['content_err'] ?? '') ?>"><?= e(mb_substr((string)($c['content_err'] ?? ''), 0, 32)) ?></div>
                        <?php endif; ?>
<?php else: ?>
                        <span class="badge empty">○ 待生成</span>
<?php endif; ?>
                      </td>
                      <td><span class="tsuf" title="<?= e($c['title_suffix'] ?? '') ?>"><?= e(trim((string)($c['title_suffix'] ?? '')) !== '' ? (string)($c['title_suffix'] ?? '') : '—') ?></span></td>
                      <td>
<?php $seoOK = trim((string)($c['title_suffix'] ?? '')) !== '' && trim((string)($c['keywords'] ?? '')) !== ''; ?>
                        <?php if ($seoOK): ?><span class="badge seo">✓ 已填</span><?php else: ?><span class="badge empty">空</span><?php endif; ?>
                      </td>
                      <td style="font-size:11.5px;color:var(--muted)">
                        <?= !empty($c['content_at']) ? e(substr((string)$c['content_at'], 0, 16)) : '—' ?>
                      </td>
                      <td>
                        <div class="cs-actions">
                          <a class="btn btn-s" href="<?= e(city_url($c)) ?>" target="_blank">访问</a>
                          <button class="btn btn-s" onclick="csEdit(<?= (int)($c['id'] ?? 0) ?>, '<?= e($c['city'] ?? '') ?>')">编辑</button>
<?php if ($cs === 1 && trim((string)($c['content'] ?? '')) !== ''): ?>
                          <button class="btn btn-s" style="background:rgba(245,158,11,.15);color:#f59e0b;border:none" onclick="csRegenOne(<?= (int)($c['id'] ?? 0) ?>, '<?= e($c['city'] ?? '') ?>', 1)">🔄 重新生成</button>
<?php else: ?>
                          <button class="btn btn-s" style="background:rgba(16,185,129,.15);color:#10b981;border:none" onclick="csRegenOne(<?= (int)($c['id'] ?? 0) ?>, '<?= e($c['city'] ?? '') ?>', 0)">⚡ 立即生成</button>
<?php endif; ?>
                          <form method="post" action="admin.php?m=city_del" style="display:inline" onsubmit="return confirm('删除「<?= e($c['city'] ?? '') ?>」分站？')">
                            <input type="hidden" name="id" value="<?= (int)($c['id'] ?? 0) ?>">
                            <button class="btn btn-s btn-d" type="submit">删除</button>
                            <?= csrf_field() ?>
                          </form>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
<?php endforeach; ?>
          </div>
<?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ===== 编辑分站内容弹窗 ===== -->
<div class="cs-modal-bg" id="csModalBg" onclick="if(event.target===this) csClose()">
  <div class="cs-modal">
    <div class="cs-modal-head">
      <div class="t">✏️ 编辑分站 —— <span id="csModalCity"></span></div>
      <button class="x" onclick="csClose()" type="button">×</button>
    </div>
    <div class="cs-modal-body">
      <div class="cs-field"><label>分站内容标题</label><input type="text" id="csModalTitle" placeholder="如：北京AI服务本地实战指南"></div>
      <div class="cs-field">
        <label>分站内容正文（HTML，分段用 <code>&lt;p&gt;</code>，小标题用 <code>&lt;h3&gt;</code>）</label>
        <textarea id="csModalContent" placeholder="留空保存 = 清空此分站内容"></textarea>
      </div>
      <div style="font-size:11.5px;color:var(--muted);line-height:1.7;background:var(--bg);padding:10px 12px;border-radius:6px">
        💡 <strong>说明</strong>：保存后将立即更新前台 <code>index.php?city=&lt;拼音&gt;</code> 城市页面的"本地服务要点"区。<br>
        清空保存 = 撤销该分站内容（前台会回退到模板自动文案）；不想丢旧内容请点取消。
      </div>
    </div>
    <div class="cs-modal-foot">
      <button class="btn btn-s" type="button" onclick="csClose()">取消</button>
      <button class="btn btn-s btn-d" type="button" id="csModalClearBtn" onclick="csModalClear()">🧽 清空内容</button>
      <button class="btn btn-p" type="button" onclick="csModalSave()">💾 保存</button>
    </div>
  </div>
</div>

<script>
var CSRF = <?= json_encode(csrf_token()) ?>;
var ALL_CITIES = <?= json_encode(array_values(array_map(function ($c) {
    return [
        'id' => (int)$c['id'],
        'city' => (string)$c['city'],
        'pinyin' => (string)($c['pinyin'] ?? ''),
        'province' => (string)($c['province'] ?? ''),
        'status' => (int)$c['status'],
        'content_status' => (int)($c['content_status'] ?? 0),
        'content_title' => (string)($c['content_title'] ?? ''),
        'content' => (string)($c['content'] ?? ''),
        'title_suffix' => (string)($c['title_suffix'] ?? ''),
        'keywords' => (string)($c['keywords'] ?? ''),
        'description' => (string)($c['description'] ?? ''),
        'tdk_try_at' => $c['tdk_try_at'] ?? null,
        'content_at' => $c['content_at'] ?? null,
        'content_err' => (string)($c['content_err'] ?? ''),
    ];
}, $list))) ?>;

/* ===== 省份折叠 ===== */
function csToggle(head) {
  var body = head.nextElementSibling;
  var tg = head.querySelector('.toggle');
  if (body.style.display === 'none' || body.style.display === '') {
    body.style.display = 'block';
    if (tg) tg.classList.add('open');
  } else {
    body.style.display = 'none';
    if (tg) tg.classList.remove('open');
  }
}
function csExpandAll(open) {
  document.querySelectorAll('.province-group').forEach(function (g) {
    var body = g.querySelector('.province-body');
    var tg = g.querySelector('.toggle');
    if (open) { body.style.display = 'block'; if (tg) tg.classList.add('open'); }
    else { body.style.display = 'none'; if (tg) tg.classList.remove('open'); }
  });
}

/* ===== 行内「立即生成」「重新生成」单城 ===== */
function csRegenOne(cityId, city, force) {
  var industry = document.getElementById('cpIndustry').value || 'AI 服务';
  if (!confirm('为「' + city + '」' + (force ? '强制重生成' : '生成') + '分站内容？\n行业词：' + industry + '\n（每城约 15~30 秒）')) return;
  var status = document.getElementById('cpStatus');
  status.style.display = 'block';
  status.textContent = '⏳ 正在为「' + city + '」' + (force ? '强制重生成' : '生成') + '分站内容…';
  var fd = new FormData();
  fd.append('city_id', cityId);
  fd.append('industry', industry);
  fd.append('force', force ? '1' : '0');
  fd.append('csrf', CSRF);
  fetch('admin.php?m=city_content_run', { method: 'POST', body: fd })
    .then(function (r) { return r.json(); })
    .then(function (r) {
      if (r.ok) { status.textContent = '✅ ' + city + '：' + (r.title || r.msg); setTimeout(function(){location.reload();}, 800); }
      else if (r.dup) { status.textContent = '⏭ ' + city + ' 已有内容（content_status=1），跳过'; }
      else { status.textContent = '⚠ ' + city + ' 失败：' + (r.msg || '未知'); }
    })
    .catch(function (e) { status.textContent = '⚠ ' + city + ' 请求失败：' + e.message; });
}

/* ===== 批量 AI 生成分站内容（content_status 精确去重） ===== */
function cityContentRun() {
  var industry = (document.getElementById('cpIndustry').value || '').trim() || 'AI 服务';
  var max = parseInt(document.getElementById('cpMax').value) || 50;
  var force = document.getElementById('cpForce').checked ? 1 : 0;
  var btn = document.getElementById('cpBtn');
  var st = document.getElementById('cpStatus');
  if (!btn || !st) { return; }
  // 只跑「未生成」+「失败」（强制模式下也跑已生成）
  var list = ALL_CITIES.filter(function (c) {
    if (c.status !== 1) return false;
    if (force) return true;
    return c.content_status === 0 || c.content_status === 3;
  });
  if (!list.length) { alert('当前可生成的城市为空（已全部生成或全部停用）。\n\n如需重生成：勾选「强制重生成」即可覆盖已生成的。'); return; }
  list = list.slice(0, max);
  if (!confirm('AI 将为 ' + list.length + ' 个城市每城生成分站专属内容（每城约 15~30 秒，共约 ' + Math.ceil(list.length * 22 / 60) + ' 分钟）。\n\n行业词：' + industry + (force ? '\n⚠️ 强制模式：已生成的也会被覆盖' : '\n✓ 已生成的自动跳过') + '\n\n继续？')) { return; }
  btn.disabled = true;
  st.style.display = 'block';
  var i = 0, done = 0, okN = 0, skipN = 0, failN = 0;
  var failList = [];
  function finish(m) {
    var extra = failList.length ? '\n\n失败明细：\n' + failList.join('\n') : '';
    st.textContent = m + extra;
    btn.disabled = false;
    setTimeout(function(){ if (okN > 0 || failN > 0) location.reload(); }, 1200);
  }
  (function next() {
    if (i >= list.length) {
      finish('🎉 本批完成：AI 新增 ' + okN + '，跳过 ' + skipN + '（已有内容），失败 ' + failN + '。页面即将刷新。');
      return;
    }
    var c = list[i++];
    st.textContent = '⏳ [' + i + '/' + list.length + '] 正在为《' + c.city + '》生成内容…（每城约 15~30 秒）';
    var fd = new FormData();
    fd.append('city_id', c.id);
    fd.append('industry', industry);
    fd.append('force', force ? '1' : '0');
    fd.append('csrf', CSRF);
    fetch('admin.php?m=city_content_run', { method: 'POST', body: fd })
      .then(function (r) { return r.json(); })
      .then(function (r) {
        done++;
        if (r.ok) { okN++; st.textContent = '✅ [' + i + '/' + list.length + '] ' + c.city + '：' + (r.title || r.msg); }
        else if (r.dup) { skipN++; st.textContent = '⏭ [' + i + '/' + list.length + '] ' + c.city + ' 已有内容，自动跳过'; }
        else { failN++; st.textContent = '⚠ [' + i + '/' + list.length + '] ' + c.city + ' 失败：' + (r.msg || '未知'); if (failList.length < 12) failList.push(c.city + '：' + (r.msg || '未知')); }
        next();
      })
      .catch(function (e) {
        done++; failN++;
        st.textContent = '⚠ [' + i + '/' + list.length + '] ' + c.city + ' 请求失败：' + e.message;
        if (failList.length < 12) failList.push(c.city + '：请求失败 ' + e.message);
        next();
      });
  })();
}

/* ===== 批量 AI SEO（保留旧功能，独立于内容） ===== */
function cityTdkAiRun(){
  var industry = (document.getElementById('ctIndustry').value || '').trim() || 'AI 服务';
  var max = parseInt(document.getElementById('ctMax').value) || 50;
  var btn = document.getElementById('ctBtn');
  var st = document.getElementById('ctStatus');
  if (!btn || !st) { return; }
  var list = ALL_CITIES.filter(function (c) { return c.status === 1 && !c.tdk_try_at; });
  if (!list.length) { alert('所有城市都已尝试过生成 SEO。\n\n如需重跑：列表右上角点「🧹 清 SEO」。'); return; }
  list = list.slice(0, max);
  if (!confirm('AI 将为 ' + list.length + ' 个城市每城独立生成 SEO（每城约 3~5 秒，共约 ' + Math.ceil(list.length * 4 / 60) + ' 分钟）。\n已填过 SEO 的城市自动跳过。\n继续？')) { return; }
  btn.disabled = true;
  st.style.display = 'block';
  var i = 0, okN = 0, skipN = 0, failN = 0;
  var failList = [];
  function finish(m) {
    var extra = failList.length ? '\n\n失败明细：\n' + failList.join('\n') : '';
    st.textContent = m + extra;
    btn.disabled = false;
    setTimeout(function(){ if (okN > 0 || failN > 0) location.reload(); }, 1200);
  }
  (function next(){
    if (i >= list.length) {
      finish('🎉 本批完成：AI 新增 ' + okN + '，跳过 ' + skipN + '（已有 SEO），失败 ' + failN + '。页面即将刷新。');
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
        if (r.ok) { okN++; st.textContent = '✅ [' + i + '/' + list.length + '] ' + c.city + '：' + (r.title_suffix || r.msg); }
        else if (r.dup) { skipN++; st.textContent = '⏭ [' + i + '/' + list.length + '] ' + c.city + ' 已有 SEO，跳过'; }
        else { failN++; st.textContent = '⚠ [' + i + '/' + list.length + '] ' + c.city + ' 失败：' + (r.msg || '未知'); if (failList.length < 12) failList.push(c.city + '：' + (r.msg || '未知')); }
        next();
      })
      .catch(function (e) { failN++; st.textContent = '⚠ [' + i + '/' + list.length + '] ' + c.city + ' 请求失败：' + e.message; if (failList.length < 12) failList.push(c.city + '：请求失败 ' + e.message); next(); });
  })();
}

/* ===== 编辑弹窗 ===== */
var csEditId = 0;
function csEdit(cityId, city) {
  csEditId = cityId;
  var c = ALL_CITIES.find(function (x) { return x.id === cityId; });
  if (!c) { alert('城市不存在'); return; }
  document.getElementById('csModalCity').textContent = c.city + '（' + (c.province || '未分组') + ' · ' + (c.pinyin || '—') + '）';
  document.getElementById('csModalTitle').value = c.content_title || '';
  document.getElementById('csModalContent').value = c.content || '';
  // 同步到 left 表单（基础信息）
  document.getElementById('f_id').value = c.id;
  document.getElementById('f_city').value = c.city;
  document.getElementById('f_py').value = c.pinyin;
  document.getElementById('f_prov').value = c.province;
  document.getElementById('f_ts').value = c.title_suffix;
  document.getElementById('f_kw').value = c.keywords;
  document.getElementById('f_desc').value = c.description;
  document.getElementById('f_status').value = c.status;
  document.getElementById('csModalBg').classList.add('open');
}
function csClose() { document.getElementById('csModalBg').classList.remove('open'); csEditId = 0; }
function csModalSave() {
  if (csEditId <= 0) { return; }
  var title = document.getElementById('csModalTitle').value;
  var content = document.getElementById('csModalContent').value;
  var fd = new FormData();
  fd.append('city_id', csEditId);
  fd.append('content_title', title);
  fd.append('content', content);
  fd.append('csrf', CSRF);
  fetch('admin.php?m=city_content_save', { method: 'POST', body: fd })
    .then(function (r) { return r.json(); })
    .then(function (r) {
      if (r.ok) { alert(r.msg || '已保存'); csClose(); location.reload(); }
      else { alert('保存失败：' + (r.msg || '')); }
    })
    .catch(function (e) { alert('请求失败：' + e.message); });
}
function csModalClear() {
  if (csEditId <= 0) return;
  if (!confirm('确定清空该分站的「内容」字段吗？\n\n✓ 清空：content_title / content / content_at\n✓ 保留：城市本身 + SEO 字段\n▶ 后续：可重新跑 AI 内容生成\n\n继续？')) return;
  document.getElementById('csModalTitle').value = '';
  document.getElementById('csModalContent').value = '';
  csModalSave();
}
</script>
</body>
</html>
