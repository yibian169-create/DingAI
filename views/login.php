<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>登录 - 得应盯后台</title>
<link rel="stylesheet" href="static/css/admin.css">
<script>
(function(){try{var t=localStorage.getItem('dy_theme')||'light';document.documentElement.setAttribute('data-theme',t);}catch(e){}})();
</script>
</head>
<body>
<div class="login-wrap">
  <div class="login-box">
    <h1>得应盯 · 官网后台</h1>
    <p style="font-size:12.5px;color:var(--muted);margin:-10px 0 16px">管理员登录</p>
    <?php if (!empty($err)): ?><div class="err"><?= e($err) ?></div><?php endif; ?>
    <?php if (!empty($flash)): ?><div class="msg"><?= e($flash) ?></div><?php endif; ?>
    <form method="post" action="admin.php?m=login">
      <div class="field"><label>用户名</label><input type="text" name="username" required placeholder="admin"></div>
      <div class="field"><label>密码</label><input type="password" name="password" required placeholder="请输入密码"></div>
      <button class="btn btn-p" type="submit">登 录</button>
    <?= csrf_field() ?>
</form>
    <p style="font-size:12px;color:var(--muted);margin-top:14px;text-align:center">
      登录不进去？请确认密码大小写、是否全角输入或多了空格。
    </p>
  </div>
</div>
</body>
</html>
