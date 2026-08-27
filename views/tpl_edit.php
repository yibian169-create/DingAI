<?php
/** 主题设置独立页（包裹 tpl_edit_panels.php 单页一览式面板） */
// 独立页入口：强制从数据库读取最新配置，确保保存后回显不落空
$data = !empty($data) && is_array($data) ? $data : settings_all();
$tab = '';
$from = '';
$settingsBaseUrl = 'admin.php?m=settings';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>主题设置 - 得应盯后台</title>
<link rel="stylesheet" href="static/css/admin.css">
<script>
(function(){try{var t=localStorage.getItem('dy_theme')||'light';document.documentElement.setAttribute('data-theme',t);}catch(e){}})();
</script>
<style>
/* 主题设置独立页微调 */
.main{padding:24px 28px 80px}
.topbar{margin:-24px -28px 20px;padding:16px 28px}
</style>
</head>
<body>
<div class="layout">
  <?php require __DIR__ . '/sidebar.php'; ?>
  <div class="main">
    <div class="topbar">
      <h1>主题设置 <span style="font-size:13px;color:var(--muted);font-weight:500">全局配置 · 主站实时跟随</span></h1>
      <div class="right"><span><?= e($admin_name) ?></span><form method="post" action="admin.php?m=logout" style="display:inline"><?= csrf_field() ?><button type="submit" class="logout-link">退出</button></form></div>
    </div>
    <?php if (!empty($flash)): ?><div class="msg"><?= e($flash) ?></div><?php endif; ?>

    <?php require __DIR__ . '/tpl_edit_panels.php'; ?>
  </div>
</div>
</body>
</html>
