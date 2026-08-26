<?php
/** 模板编辑独立页（包裹 tpl_edit_panels.php 表单面板） */
$data = $data ?? [];
$tab = $_GET['tab'] ?? 'global';
$from = '';
$settingsBaseUrl = 'admin.php?m=settings&tab=';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>模板编辑 - 得应盯后台</title>
<link rel="stylesheet" href="static/css/admin.css">
<script>
(function(){try{var t=localStorage.getItem('dy_theme')||'light';document.documentElement.setAttribute('data-theme',t);}catch(e){}})();
</script>
<style>
/* ===== 模板编辑专属样式 ===== */
.tpl-tab{display:flex;gap:0;border-bottom:2px solid var(--line);margin-bottom:24px}
.tpl-tab a{padding:12px 20px;font-size:14.5px;color:var(--muted);border-bottom:3px solid transparent;margin-bottom:-2px;text-decoration:none;font-weight:600}
.tpl-tab a.active{color:var(--primary);border-bottom-color:var(--primary)}
.tpl-tab a:hover{color:var(--primary)}
.tpl-panel{background:var(--card);border:1px solid var(--line);border-radius:14px;padding:24px 28px;box-shadow:0 2px 8px rgba(15,23,42,.04);margin-bottom:18px}
.tpl-panel h3{margin:0 0 4px;font-size:17px;font-weight:700;display:flex;align-items:center;gap:10px}
.tpl-panel h3::before{content:'';width:4px;height:18px;border-radius:2px;background:linear-gradient(135deg,var(--c1,#4f46e5),var(--c2,#818cf8))}
.tpl-panel .desc{color:var(--muted);font-size:13px;margin:0 0 18px}
.theme-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:12px;margin-top:6px}
.theme-card{position:relative;padding:16px 14px;border:2px solid var(--line);border-radius:12px;cursor:pointer;text-align:center;transition:all .2s;background:var(--card)}
.theme-card:hover{border-color:var(--c1,#4f46e5);transform:translateY(-2px);box-shadow:0 8px 20px rgba(79,70,229,.15)}
.theme-card input{position:absolute;opacity:0;pointer-events:none}
.theme-card .dot{display:inline-flex;gap:3px;margin-bottom:8px}
.theme-card .dot i{width:18px;height:18px;border-radius:50%}
.theme-card .name{font-size:13px;font-weight:600;color:var(--text);display:block}
.theme-card.checked{border-color:var(--primary);background:#eef2ff}
.theme-card.checked::after{content:'✓';position:absolute;top:6px;right:8px;color:var(--primary);font-weight:800;font-size:14px}
.theme-custom{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-top:14px}
.theme-custom label{font-size:12.5px;color:var(--muted);display:flex;align-items:center;gap:10px}
.theme-custom input[type=color]{width:48px;height:32px;border:1px solid var(--line);border-radius:8px;padding:2px;cursor:pointer}
.tpl-save-bar{position:sticky;bottom:0;background:linear-gradient(180deg,transparent,rgba(241,245,249,.95) 40%);padding:14px 0;text-align:right;z-index:5}
</style>
</head>
<body>
<div class="layout">
  <?php require __DIR__ . '/sidebar.php'; ?>
  <div class="main">
    <div class="topbar">
      <h1>模板编辑</h1>
      <div class="right"><span><?= e($admin_name) ?></span><form method="post" action="admin.php?m=logout" style="display:inline"><?= csrf_field() ?><button type="submit" class="logout-link">退出</button></form></div>
    </div>
    <?php if (!empty($flash)): ?><div class="msg"><?= e($flash) ?></div><?php endif; ?>

    <?php require __DIR__ . '/tpl_edit_panels.php'; ?>
  </div>
</div>
</body>
</html>
