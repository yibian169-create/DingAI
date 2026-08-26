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
          <h2>一键导入全国分站</h2>
          <p style="color:var(--muted);font-size:13px;margin-bottom:12px">内置全国 200+ 城市（含拼音后缀）。已存在的城市自动跳过。</p>
          <form method="post" action="admin.php?m=city_import">
            <button class="btn btn-p" type="submit" onclick="return confirm('导入全国城市分站？')">一键导入全国分站</button>
          <?= csrf_field() ?>
</form>
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
          <h2>分站列表（<?= count($list) ?> 个城市）</h2>
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
function edit(c) {
  document.getElementById('f_id').value = c.id;
  document.getElementById('f_city').value = c.city;
  document.getElementById('f_py').value = c.pinyin || '';
  document.getElementById('f_ts').value = c.title_suffix || '';
  document.getElementById('f_kw').value = c.keywords || '';
  document.getElementById('f_desc').value = c.description || '';
  document.getElementById('f_status').value = c.status;
}
</script>
</body>
</html>
