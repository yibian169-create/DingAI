<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>栏目管理 - 得应盯后台</title>
<link rel="stylesheet" href="static/css/admin.css">
<script>
(function(){try{var t=localStorage.getItem('dy_theme')||'light';document.documentElement.setAttribute('data-theme',t);}catch(e){}})();
</script>
</head>
<body>
<div class="layout">
  <?php require __DIR__ . '/sidebar.php'; ?>
  <div class="main">
    <div class="topbar"><h1>栏目管理</h1><div class="right"><span><?= e($admin_name) ?></span><form method="post" action="admin.php?m=logout" style="display:inline"><?= csrf_field() ?><button type="submit" class="logout-link">退出</button></form></div></div>
    <?php if (!empty($flash)): ?><div class="msg"><?= e($flash) ?></div><?php endif; ?>

    <div class="split-wrap">
      <!-- 左侧：新增 / 编辑栏目 -->
      <div class="split-form">
        <div class="panel">
          <h2>新增 / 编辑栏目</h2>
          <p style="color:var(--muted);font-size:13px;margin:-8px 0 14px">支持子栏目；父栏目下可添加文章/产品。</p>
          <form method="post" action="admin.php?m=category_save">
            <input type="hidden" name="id" id="f_id" value="0">
            <div class="fg">
              <div class="field"><label>栏目名称 *</label><input type="text" name="name" id="f_name" required></div>
              <div class="field"><label>上级栏目</label>
                <select name="pid" id="f_pid"><option value="0">顶级栏目</option>
                  <?php foreach ($cats as $c): if ((int)$c['pid'] === 0): ?>
                    <option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option>
                  <?php endif; endforeach; ?>
                </select>
              </div>
              <div class="field"><label>类型</label>
                <select name="type" id="f_type">
                  <option value="article">文章</option>
                  <option value="product">产品</option>
                  <option value="single">单页</option>
                  <?php /* 下载分类请在「下载专区」模块创建，不在此处选，避免整条栏目被全站过滤而"消失" */ ?>
                </select>
              </div>
              <div class="field"><label>排序</label><input type="number" name="sort" id="f_sort" value="0"></div>
              <div class="field"><label>状态</label>
                <select name="status" id="f_status"><option value="1">启用</option><option value="0">停用</option></select>
              </div>
            </div>
            <details class="seo-box">
              <summary>SEO 设置（可选）</summary>
              <div class="fg" style="margin-top:10px">
                <div class="field"><label>SEO 标题</label><input type="text" name="seo_title" id="f_st"></div>
                <div class="field"><label>SEO 关键词</label><input type="text" name="seo_keywords" id="f_sk"></div>
                <div class="field full"><label>SEO 描述</label><input type="text" name="seo_description" id="f_sd"></div>
              </div>
            </details>
            <div style="margin-top:14px"><button class="btn btn-p" type="submit">保存栏目</button></div>
          <?= csrf_field() ?>
</form>
        </div>
      </div>

      <!-- 右侧：栏目列表 -->
      <div class="split-list">
        <div class="panel">
          <h2>栏目列表</h2>
          <table>
            <thead><tr><th>ID</th><th>名称</th><th>层级</th><th>类型</th><th>排序</th><th>状态</th><th>操作</th></tr></thead>
            <tbody>
              <?php foreach ($cats as $c): ?>
              <tr>
                <td><?= $c['id'] ?></td>
                <td><?= (int)$c['pid'] === 0 ? '📁 ' : '└ ' ?><?= e($c['name']) ?></td>
                <td><?= (int)$c['pid'] === 0 ? '父栏目' : '子栏目' ?></td>
                <td><?= e($c['type']) ?></td>
                <td><?= $c['sort'] ?></td>
                <td><?= $c['status'] ? '<span class="tag tag-ok">启用</span>' : '<span class="tag tag-off">停用</span>' ?></td>
                <td>
                  <button class="btn btn-s" onclick="edit(<?= $c['id'] ?>,<?= $c['pid'] ?>,'<?= e($c['name']) ?>','<?= $c['type'] ?>',<?= $c['status'] ?>,'<?= e($c['seo_title']) ?>','<?= e($c['seo_keywords']) ?>','<?= e($c['seo_description']) ?>')">编辑</button>
                  <form method="post" action="admin.php?m=category_del" style="display:inline" onsubmit="return confirm('删除栏目？（不会删除其内容）')">
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
function edit(id, pid, name, type, status, st, sk, sd) {
  document.getElementById('f_id').value = id;
  document.getElementById('f_pid').value = pid;
  document.getElementById('f_name').value = name;
  document.getElementById('f_type').value = type;
  document.getElementById('f_status').value = status;
  document.getElementById('f_st').value = st;
  document.getElementById('f_sk').value = sk;
  document.getElementById('f_sd').value = sd;
}
</script>
</body>
</html>
