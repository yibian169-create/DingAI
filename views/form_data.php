<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>表单数据 - <?= e($def['name']) ?> - 得应盯后台</title>
<link rel="stylesheet" href="static/css/admin.css">
<script>
(function(){try{var t=localStorage.getItem('dy_theme')||'light';document.documentElement.setAttribute('data-theme',t);}catch(e){}})();
</script>
</head>
<body>
<div class="layout">
  <?php require __DIR__ . '/sidebar.php'; ?>
  <div class="main">
    <div class="topbar">
      <h1>表单数据：<?= e($def['name']) ?></h1>
      <div class="right">
        <a class="tb-link" href="index.php?act=form&id=<?= $def['id'] ?>" target="_blank">前台预览</a>
        <a class="tb-btn" style="text-decoration:none" href="admin.php?m=form_data_export&fid=<?= $def['id'] ?>">导出 CSV</a>
        <span><?= e($admin_name) ?></span>
        <form method="post" action="admin.php?m=logout" style="display:inline"><?= csrf_field() ?><button type="submit" class="logout-link">退出</button></form>
      </div>
    </div>
    <?php if (!empty($flash)): ?><div class="msg"><?= e($flash) ?></div><?php endif; ?>

    <div class="panel">
      <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:14px">
        <h2 style="margin:0">提交记录（共 <?= $pg['total'] ?> 条）</h2>
        <div style="display:flex;gap:8px;align-items:center">
          <span style="font-size:13px;color:var(--muted)">嵌入代码：</span>
          <code style="font-size:12px;background:var(--toolbar-bg);padding:6px 10px;border-radius:6px;cursor:pointer" title="点击复制" onclick='copyEmbed(this)'>iframe 嵌入</code>
        </div>
      </div>
      <table style="min-width:900px">
        <thead><tr><th style="width:56px">ID</th>
          <?php foreach ($fields as $f): ?><th><?= e($f['label']) ?><?= !empty($f['required']) ? ' *' : '' ?></th><?php endforeach; ?>
          <th style="width:130px">提交时间</th><th style="width:130px">IP</th><th style="width:90px">操作</th></tr></thead>
        <tbody>
          <?php if ($rows): foreach ($rows as $r): ?>
          <tr>
            <td><?= $r['id'] ?></td>
            <?php foreach ($fields as $f): ?>
            <td>
              <?php $v = $r['data'][$f['name']] ?? ''; ?>
              <?php if (is_array($v)): ?><?= e(implode('、', $v)) ?><?php else: ?><?= e((string)$v) ?><?php endif; ?>
            </td>
            <?php endforeach; ?>
            <td><?= substr($r['created_at'], 0, 16) ?></td>
            <td><?= e($r['ip']) ?></td>
            <td>
              <form method="post" action="admin.php?m=form_data_del" onsubmit="return confirm('删除该条记录？')">
                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                <input type="hidden" name="fid" value="<?= $def['id'] ?>">
                <button class="btn btn-s btn-d" type="submit">删除</button>
              <?= csrf_field() ?>
</form>
            </td>
          </tr>
          <?php endforeach; else: ?>
          <tr><td colspan="<?= count($fields) + 4 ?>" style="text-align:center;color:var(--muted);padding:40px">暂无提交数据</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
      <?php require __DIR__ . '/pager.php'; ?>
    </div>
  </div>
</div>
<script>
function copyEmbed(el) {
  var url = location.origin + '/' + location.pathname.split('/').slice(0, -1).join('/') + '/index.php?act=form&id=<?= $def['id'] ?>';
  var code = '<iframe src="' + url + '" width="100%" height="620" frameborder="0" style="border:1px solid var(--line);border-radius:12px"></iframe>';
  if (navigator.clipboard && window.isSecureContext) {
    navigator.clipboard.writeText(code).then(function () { el.textContent = '已复制 ✓'; setTimeout(function () { el.textContent = 'iframe 嵌入'; }, 1600); });
  } else {
    prompt('复制以下代码嵌入到任意页面：', code);
  }
}
</script>
</body>
</html>
