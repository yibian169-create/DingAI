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
      <div class="field">
        <label>安全验证</label>
        <div class="cap-slider" id="capSlider">
          <div class="cap-track" id="capTrack"></div>
          <span class="cap-text" id="capText">按住滑块，拖到最右侧完成验证</span>
          <div class="cap-thumb" id="capThumb">»</div>
        </div>
        <input type="hidden" name="captcha" id="capPayload" value="">
        <input type="hidden" name="captcha_token" id="capToken" value="">
      </div>
      <button class="btn btn-p" type="submit" id="loginBtn">登 录</button>
    <?= csrf_field() ?>
</form>
    <p style="font-size:12px;color:var(--muted);margin-top:14px;text-align:center">
      登录不进去？请确认密码大小写、是否全角输入或多了空格。
    </p>
  </div>
</div>
<style>
.cap-slider{position:relative;height:42px;border:1px solid var(--border-tertiary);border-radius:8px;background:var(--bg-2,#f3f4f6);overflow:hidden;user-select:none;touch-action:none;cursor:grab}
.cap-track{position:absolute;left:0;top:0;bottom:0;width:0;background:#4ade80;opacity:.35;transition:width .08s linear}
.cap-text{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:12.5px;color:var(--muted);z-index:1;pointer-events:none}
.cap-thumb{position:absolute;left:2px;top:2px;width:42px;height:38px;border-radius:6px;background:var(--color-background-primary,#fff);border:1px solid var(--border-secondary);display:flex;align-items:center;justify-content:center;font-size:16px;color:var(--muted);z-index:2}
.cap-slider.ok .cap-thumb{background:#22c55e;color:#fff;border-color:#22c55e}
.cap-slider.ok .cap-text{color:#166534;font-weight:500}
</style>
<script>
(function(){
  var slider=document.getElementById('capSlider'), thumb=document.getElementById('capThumb'),
      track=document.getElementById('capTrack'), text=document.getElementById('capText'),
      payload=document.getElementById('capPayload'), tokenEl=document.getElementById('capToken'),
      ok=false, startX=null, startT=0, pts=0, moved=0;
  fetch('admin.php?m=captcha_new').then(function(r){return r.json();}).then(function(r){
    if(r && r.token) tokenEl.value=r.token;
  }).catch(function(){});
  thumb.addEventListener('pointerdown',function(e){
    if(ok) return;
    startX=e.clientX; startT=Date.now(); pts=0; moved=0;
    try{thumb.setPointerCapture(e.pointerId);}catch(err){}
  });
  thumb.addEventListener('pointermove',function(e){
    if(startX===null) return;
    var max=slider.clientWidth-thumb.offsetWidth-4;
    moved=Math.max(0,Math.min(max,e.clientX-startX));
    thumb.style.left=(2+moved)+'px';
    track.style.width=(moved/max*100)+'%';
    pts++;
  });
  thumb.addEventListener('pointerup',function(){
    if(startX===null) return;
    var max=slider.clientWidth-thumb.offsetWidth-4;
    var ratio=moved/max, dur=Date.now()-startT;
    if(ratio>=0.93 && dur>=250 && dur<=20000 && pts>=3){
      ok=true;
      payload.value=JSON.stringify({time:dur,ratio:ratio,points:pts});
      slider.classList.add('ok');
      text.textContent='✓ 验证通过';
      thumb.style.left=(2+max)+'px';
      track.style.width='100%';
    }else{
      thumb.style.left='2px'; track.style.width='0';
      text.textContent='请按住滑块拖到最右侧';
    }
    startX=null;
  });
  slider.addEventListener('pointerup',function(){startX=null;});
})();
</script>
</body>
</html>
