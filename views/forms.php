<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>自定义表单 - 得应盯后台</title>
<link rel="stylesheet" href="static/css/admin.css">
<script>
(function(){try{var t=localStorage.getItem('dy_theme')||'light';document.documentElement.setAttribute('data-theme',t);}catch(e){}})();
</script>
<style>
.fld-head{display:flex;align-items:center;justify-content:space-between;margin:20px 0 10px}
.fld-head h3{font-size:15px;margin:0}
.fld-list{display:flex;flex-direction:column;gap:12px}
.fld-row{background:var(--card);border:1px solid var(--line);border-radius:12px;overflow:hidden}
/* ====== 全国分站重构（2025）表单字段配置 UI 三层视觉 + 独立成行 ======
   每行只放一个字段元素，宽松不紧凑；重要性 = 大小 + 颜色 + 独占行
   ① 字段标签（label，给访客看的）→ 红框粗体独占首行（满宽）
   ② 类型 + 必填 + 删除 → 共享辅助行（小灰底、右对齐）
   ③ 字段名（name，系统用的英文） → 蓝色中等独立行（满宽）
   ④ 占位提示 / 选项 → 弱化灰底独立行（满宽）
   ============================================================== */
.fld-row__head{padding:14px 14px 8px;background:var(--toolbar-bg);border-bottom:1px dashed var(--line);display:flex;flex-direction:column;gap:10px}
.fld-row__num{width:24px;height:24px;display:grid;place-items:center;background:#eef2ff;color:var(--primary);border-radius:50%;font-size:12px;font-weight:700;flex-shrink:0}
/* ① 字段标签 input —— 红框粗体独占首行满宽（最高级视觉优先级） */
.fld-row__head input.fld-label{
  width:100%;border:2px solid #ef4444;background:#fef2f2;padding:12px 16px;font-size:16px;font-weight:700;
  border-radius:8px;color:var(--text);transition:all .15s ease
}
.fld-row__head input.fld-label:focus{
  outline:none;border-color:#dc2626;background:#fee2e2;box-shadow:0 0 0 3px rgba(239,68,68,.18)
}
.fld-row__head input.fld-label::placeholder{color:#fca5a5;font-weight:500}
/* label hint 徽章（红色，"显示给访客"） */
.fld-row__head .fld-label-wrap{display:flex;align-items:center;gap:8px}
.fld-row__head .fld-label-hint{display:inline-flex;align-items:center;gap:4px;font-size:12px;color:#dc2626;font-weight:600;background:#fee2e2;padding:4px 10px;border-radius:6px;flex-shrink:0}
/* ② 类型/必填/删除 → 共享辅助行（紧凑右对齐） */
.fld-row__head .fld-row__opts{display:flex;align-items:center;gap:8px;justify-content:flex-end;flex-wrap:wrap}
.fld-row__head select.fld-type{
  min-width:120px;border:1px solid var(--line);border-radius:6px;padding:6px 10px;
  font-size:12.5px;color:var(--muted);background:var(--bg);cursor:pointer
}
.fld-row__head label.fld-req{font-size:12px;color:var(--muted);display:flex;align-items:center;gap:4px;opacity:.85;margin:0}
.fld-row__head button.fld-del{
  width:26px;height:26px;border:none;border-radius:6px;background:#fee2e2;color:var(--danger);cursor:pointer;font-size:14px
}
/* ③ 字段名（name）→ 独立整行，蓝色中等 */
.fld-row__body{padding:4px 14px 14px;display:flex;flex-direction:column;gap:10px;background:var(--card)}
.fld-row__body .fld-name-hint{display:inline-block;font-size:11px;color:#6366f1;font-weight:600;background:#eef2ff;padding:3px 9px;border-radius:5px;align-self:flex-start}
.fld-row__body input.fld-name{
  width:100%;border:1.5px solid #818cf8;background:#eef2ff;padding:11px 14px;font-size:13.5px;font-weight:500;
  border-radius:7px;color:var(--text);font-family:ui-monospace,SFMono-Regular,Menlo,monospace
}
.fld-row__body input.fld-name:focus{outline:none;border-color:#6366f1;background:#e0e7ff}
/* ④ 占位提示 + 选项 → 独立行，弱化灰底 */
.fld-row__body input.fld-ph,
.fld-row__body textarea.fld-options{
  width:100%;border:1px dashed var(--line);background:var(--bg);padding:8px 12px;font-size:12.5px;color:var(--muted);
  border-radius:6px
}
</style>
</head>
<body>
<div class="layout">
  <?php require __DIR__ . '/sidebar.php'; ?>
  <div class="main">
    <div class="topbar"><h1>自定义表单</h1><div class="right"><span><?= e($admin_name) ?></span><form method="post" action="admin.php?m=logout" style="display:inline"><?= csrf_field() ?><button type="submit" class="logout-link">退出</button></form></div></div>
    <?php if (!empty($flash)): ?><div class="msg"><?= e($flash) ?></div><?php endif; ?>

    <div class="split-wrap">
      <!-- 左侧：创建 / 编辑表单 -->
      <div class="split-form">
        <div class="panel">
          <h2>创建 / 编辑表单</h2>
          <form method="post" action="admin.php?m=form_save" onsubmit="return buildFieldsJson()">
            <input type="hidden" name="id" id="f_id" value="0">
            <input type="hidden" name="fields_json" id="f_fields_json" value="">
            <div class="fg">
              <div class="field"><label>表单名称 *（后台识别用）</label><input type="text" name="name" id="f_name" required placeholder="如：预约业务诊断表"></div>
              <div class="field"><label>前台标题</label><input type="text" name="title" id="f_title" placeholder="如：预约一对一业务诊断（留空显示表单名）"></div>
              <div class="field"><label>提交按钮文字</label><input type="text" name="submit_text" id="f_submit" value="提交"></div>
              <div class="field"><label>状态</label>
                <select name="status" id="f_status"><option value="1">启用</option><option value="0">停用</option></select>
              </div>
              <div class="field"><label>前端导航显示</label>
                <select name="show_nav" id="f_show_nav"><option value="0">不显示</option><option value="1">显示</option></select>
              </div>
              <div class="field full"><label>表单说明</label><input type="text" name="remark" id="f_remark" placeholder="显示在表单上方的一句话说明（可留空）"></div>
            </div>

            <div class="fld-head">
              <h3>字段列表</h3>
              <button type="button" class="btn btn-s" onclick="addField()">＋ 添加字段</button>
            </div>
            <div id="fldList" class="fld-list"></div>
            <div style="margin-top:16px"><button class="btn btn-p" type="submit">保存表单</button></div>
          <?= csrf_field() ?>
</form>
        </div>
      </div>

      <!-- 右侧：表单列表 -->
      <div class="split-list">
        <div class="panel">
          <h2>表单列表（<?= count($list) ?> 个）</h2>
          <table>
            <thead><tr><th style="width:60px">ID</th><th>表单名称</th><th>字段数</th><th>提交数</th><th>状态</th><th>导航显示</th><th>前台地址</th><th style="width:280px">操作</th></tr></thead>
            <tbody>
              <?php foreach ($list as $f): ?>
              <tr>
                <td><?= $f['id'] ?></td>
                <td><?= e($f['name']) ?><br><small style="color:var(--muted)"><?= e($f['title']) ?></small></td>
                <td><?= $f['field_n'] ?></td>
                <td><a href="admin.php?m=form_data&fid=<?= $f['id'] ?>" style="color:#4f46e5"><?= $f['cnt'] ?> 条</a></td>
                <td><?= $f['status'] ? '<span class="tag tag-ok">启用</span>' : '<span class="tag tag-off">停用</span>' ?></td>
                <td><?= !empty($f['show_nav']) ? '<span class="tag tag-ok">显示</span>' : '<span class="tag tag-off">隐藏</span>' ?></td>
                <td><code style="font-size:12px">index.php?act=form&id=<?= $f['id'] ?></code></td>
                <td>
                  <button class="btn btn-s" onclick='editForm(<?= json_encode($f, JSON_UNESCAPED_UNICODE) ?>)'>编辑</button>
                  <a class="btn btn-s" href="admin.php?m=form_data&fid=<?= $f['id'] ?>">数据</a>
                  <a class="btn btn-s" href="admin.php?m=form_data_export&fid=<?= $f['id'] ?>">导出CSV</a>
                  <form method="post" action="admin.php?m=form_del" style="display:inline" onsubmit="return confirm('删除表单及其全部提交数据？')">
                    <input type="hidden" name="id" value="<?= $f['id'] ?>">
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
var TYPES = [
  ['text', '单行文本'], ['textarea', '多行文本'], ['tel', '手机号'], ['email', '邮箱'],
  ['number', '数字'], ['date', '日期'], ['select', '下拉选择'], ['radio', '单选'], ['checkbox', '多选']
];
var fieldSeq = 0;

function addField(f) {
  f = f || {};
  fieldSeq++;
  var type = f.type || 'text';
  var id = 'fld_' + fieldSeq;
  var box = document.createElement('div');
  box.className = 'fld-row';
  box.id = id;
  var typeOpts = TYPES.map(function (t) { return '<option value="' + t[0] + '"' + (type === t[0] ? ' selected' : '') + '>' + t[1] + '</option>'; }).join('');
  box.innerHTML =
    '<div class="fld-row__head">' +
      // === ① 字段标签：独占首行（最关键输入，满宽红框）===
      '<div class="fld-label-wrap">' +
        '<span class="fld-row__num">' + fieldSeq + '</span>' +
        '<span class="fld-label-hint">🏷 显示给访客</span>' +
        '<input class="fld-label" placeholder="字段名称（必填）：姓名 / 手机号 / 公司名 …" value="' + esc(f.label || '') + '">' +
      '</div>' +
      // === ② 辅助控件：独占一行（紧凑右对齐）===
      '<div class="fld-row__opts">' +
        '<select class="fld-type" onchange="fldTypeChange(this)">' + typeOpts + '</select>' +
        '<label class="fld-req"><input type="checkbox" class="fld-required"' + (f.required ? ' checked' : '') + '>必填</label>' +
        '<button type="button" class="fld-del" onclick="this.closest(\'.fld-row\').remove()">✕</button>' +
      '</div>' +
    '</div>' +
    '<div class="fld-row__body">' +
      // === ③ 字段名：独立行（系统用，可留空自动生成）===
      '<span class="fld-name-hint">💻 系统字段名（英文，可留空自动生成拼音+随机）</span>' +
      '<input class="fld-name" placeholder="如：name / phone / company（留空自动用拼音+随机）" value="' + esc(f.name || '') + '">' +
      // === ④ 占位提示：弱化行 ===
      '<input class="fld-ph" placeholder="占位提示（不重要，可留空，如：\"请输入您的姓名\"）" value="' + esc(f.placeholder || '') + '" style="display:' + (type === 'select' || type === 'radio' || type === 'checkbox' || type === 'date' ? 'none' : '') + '">' +
      // === ⑤ 选项：仅 select/radio/checkbox 显示 ===
      '<textarea class="fld-options" placeholder="选项，每行一个（仅下拉/单选/多选需要）" style="display:' + (type === 'select' || type === 'radio' || type === 'checkbox' ? '' : 'none') + '" rows="3">' + esc((f.options || []).join('\n')) + '</textarea>' +
    '</div>';
  document.getElementById('fldList').appendChild(box);
}

function fldTypeChange(sel) {
  var row = sel.closest('.fld-row');
  var t = sel.value;
  var isOpt = t === 'select' || t === 'radio' || t === 'checkbox';
  var isDate = t === 'date';
  row.querySelector('.fld-ph').style.display = (isOpt || isDate) ? 'none' : '';
  row.querySelector('.fld-options').style.display = isOpt ? '' : 'none';
}

function buildFieldsJson() {
  var rows = document.querySelectorAll('.fld-row');
  if (!rows.length) { alert('请至少添加一个字段'); return false; }
  var fields = [];
  rows.forEach(function (r) {
    var label = r.querySelector('.fld-label').value.trim();
    if (!label) return;
    var f = {
      label: label,
      name: r.querySelector('.fld-name').value.trim(),
      type: r.querySelector('.fld-type').value,
      required: r.querySelector('.fld-required').checked ? 1 : 0,
      placeholder: r.querySelector('.fld-ph').value.trim(),
      options: r.querySelector('.fld-options').value.split('\n').map(function (s) { return s.trim(); }).filter(Boolean)
    };
    fields.push(f);
  });
  if (!fields.length) { alert('字段名称不能为空'); return false; }
  document.getElementById('f_fields_json').value = JSON.stringify(fields);
  return true;
}

function editForm(f) {
  document.getElementById('f_id').value = f.id;
  document.getElementById('f_name').value = f.name;
  document.getElementById('f_title').value = f.title || '';
  document.getElementById('f_submit').value = f.submit_text || '提交';
  document.getElementById('f_remark').value = f.remark || '';
  document.getElementById('f_status').value = f.status;
  document.getElementById('f_show_nav').value = f.show_nav || 0;
  document.getElementById('fldList').innerHTML = '';
  try {
    var fields = JSON.parse(f.fields || '[]');
    fields.forEach(function (x) { addField(x); });
  } catch (e) { addField(); }
}

function esc(s) {
  return String(s || '').replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}
addField();
</script>
</body>
</html>
