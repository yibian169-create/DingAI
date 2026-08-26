<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>图片空间 - 得应盯后台</title>
<link rel="stylesheet" href="static/css/admin.css">
<script>
(function(){try{var t=localStorage.getItem('dy_theme')||'light';document.documentElement.setAttribute('data-theme',t);}catch(e){}})();
</script>
<style>
.folder-bar{display:flex;flex-wrap:wrap;gap:10px;align-items:center}
.folder{display:inline-flex;align-items:center;gap:8px;padding:8px 12px;border-radius:8px;border:1px solid var(--line);background:var(--card);font-size:13px;color:var(--text);cursor:pointer}
.folder:hover{border-color:var(--primary);color:var(--primary)}
.folder.active{background:#eef2ff;border-color:var(--primary);color:var(--primary)}
.folder b{margin-left:4px;font-size:12px;background:var(--toolbar-bg);padding:2px 7px;border-radius:999px}
.folder-wrap{position:relative;display:inline-flex;align-items:center}
.folder-del{width:22px;height:22px;border:none;border-radius:50%;background:transparent;color:var(--muted);cursor:pointer;margin-left:-6px}
.folder-del:hover{background:#fee2e2;color:var(--danger)}
.up-pick{display:inline-flex;align-items:center;gap:8px;padding:8px 14px;border:1px dashed var(--primary);border-radius:10px;color:var(--primary);font-size:13px;cursor:pointer;background:var(--card)}
.up-pick:hover{background:#eef2ff}
.up-wall{display:grid;grid-template-columns:repeat(4,1fr);gap:14px}
.up-card{background:var(--card);border:1px solid var(--line);border-radius:10px;overflow:hidden}
.up-card__thumb{aspect-ratio:1;background:var(--toolbar-bg);cursor:pointer}
.up-card__thumb img{width:100%;height:100%;object-fit:cover;display:block}
.up-card__name{padding:8px 10px;font-size:12.5px;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.up-card__meta{padding:0 10px 8px;font-size:11.5px;color:var(--muted);display:flex;gap:10px}
.up-card__ops{padding:0 10px 10px;display:flex;gap:6px}
.up-card__ops .btn{font-size:11px;padding:4px 8px}
.lightbox{display:none;position:fixed;inset:0;background:rgba(2,6,23,.7);z-index:9998;align-items:center;justify-content:center;padding:20px;flex-direction:column}
.lightbox img{max-width:92vw;max-height:82vh;border-radius:12px;box-shadow:0 20px 60px rgba(0,0,0,.35)}
.lightbox__bar{display:flex;align-items:center;gap:12px;margin-top:14px;background:var(--card);padding:10px 16px;border-radius:10px;font-size:13px}
</style>
</head>
<body>
<div class="layout">
  <?php require __DIR__ . '/sidebar.php'; ?>
  <div class="main">
    <div class="topbar"><h1>图片空间</h1><div class="right"><span><?= e($admin_name) ?></span><form method="post" action="admin.php?m=logout" style="display:inline"><?= csrf_field() ?><button type="submit" class="logout-link">退出</button></form></div></div>
    <?php if (!empty($flash)): ?><div class="msg"><?= e($flash) ?></div><?php endif; ?>

    <div class="split-wrap">
      <!-- 左侧：文件夹 + 上传 -->
      <div class="split-form">
        <div class="panel">
          <h2>文件夹</h2>
          <form method="post" action="admin.php?m=folder_save" style="display:flex;gap:8px;margin-bottom:14px">
            <input type="text" name="name" placeholder="新建文件夹名称" required style="padding:7px 12px;border:1px solid var(--line);border-radius:8px;font-size:13px">
            <button class="btn btn-p" type="submit" style="padding:7px 18px">新建</button>
          <?= csrf_field() ?>
</form>
          <div class="folder-bar">
            <a class="folder <?= $fid === 0 ? 'active' : '' ?>" href="admin.php?m=uploads">
              <span>🗂</span>
              <span>全部图片</span>
              <b><?= array_sum(array_column($folders, 'cnt')) ?></b>
            </a>
            <?php foreach ($folders as $fd): ?>
            <div class="folder-wrap">
              <a class="folder <?= $fid === (int)$fd['id'] ? 'active' : '' ?>" href="admin.php?m=uploads&fid=<?= $fd['id'] ?>">
                <span>📁</span>
                <span><?= e($fd['name']) ?></span>
                <b><?= $fd['cnt'] ?></b>
              </a>
              <form method="post" action="admin.php?m=folder_del" onsubmit="return confirm('删除文件夹「<?= e($fd['name']) ?>」？其中图片将移至全部图片')">
                <input type="hidden" name="id" value="<?= $fd['id'] ?>">
                <button class="folder-del" type="submit" title="删除文件夹">✕</button>
              <?= csrf_field() ?>
</form>
            </div>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="panel">
          <h2>上传图片</h2>
          <p style="color:var(--muted);font-size:13px;margin-bottom:12px">jpg / png / gif / webp，单张 ≤10MB，可多选</p>
          <form method="post" action="admin.php?m=upload_do" enctype="multipart/form-data">
            <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:12px">
              <label class="up-pick">
                <input type="file" name="file[]" accept="image/*" multiple hidden id="upInput">
                <span id="upPickLabel">📁 选择图片（可多选）</span>
              </label>
              <select name="folder_id" style="height:40px;border:1px solid var(--line);border-radius:10px;padding:0 12px;font-size:13.5px;background:var(--card)">
                <option value="0">存放到：全部图片</option>
                <?php foreach ($folders as $fd): ?>
                <option value="<?= $fd['id'] ?>" <?= $fid === (int)$fd['id'] ? 'selected' : '' ?>>存放到：<?= e($fd['name']) ?></option>
                <?php endforeach; ?>
              </select>
              <button class="btn btn-p" type="submit">上传</button>
            </div>
            <div id="upPreview" style="display:none;color:var(--muted);font-size:13px"></div>
          <?= csrf_field() ?>
</form>
        </div>
      </div>

      <!-- 右侧：图片库 -->
      <div class="split-list">
        <div class="panel">
          <h2>图片库（共 <?= $pg['total'] ?> 张）</h2>
          <?php if ($list): ?>
          <div class="up-wall">
            <?php foreach ($list as $u): ?>
            <div class="up-card">
              <div class="up-card__thumb">
                <img src="uploads/<?= e($u['path']) ?>" alt="<?= e($u['name']) ?>" loading="lazy" onclick='preview("uploads/<?= e($u['path']) ?>", "<?= e($u['name']) ?>")' title="点击放大预览">
              </div>
              <div class="up-card__name" title="<?= e($u['name']) ?>"><?= e(cut($u['name'], 14)) ?></div>
              <div class="up-card__meta">
                <span><?= round($u['size'] / 1024, 1) ?>KB</span>
                <span><?= substr($u['created_at'], 5, 11) ?></span>
              </div>
              <div class="up-card__ops">
                <button class="btn btn-s" type="button" onclick='copyUrl("uploads/<?= e($u['path']) ?>")'>复制 URL</button>
                <form method="post" action="admin.php?m=upload_del" style="display:inline" onsubmit="return confirm('删除图片？')">
                  <input type="hidden" name="id" value="<?= $u['id'] ?>">
                  <button class="btn btn-s btn-d" type="submit">删除</button>
                <?= csrf_field() ?>
</form>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
          <?php require __DIR__ . '/pager.php'; ?>
          <?php else: ?>
          <p style="color:var(--muted);padding:30px 0;text-align:center"><?= $fid ? '该文件夹暂无图片' : '暂无图片，点击左侧上传' ?></p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- 预览遮罩 -->
<div class="lightbox" id="lightbox" onclick="closePreview()">
  <img id="lbImg" src="" alt="预览">
  <div class="lightbox__bar">
    <span id="lbName"></span>
    <button class="btn btn-s" id="lbCopy" type="button">复制 URL</button>
  </div>
</div>

<script>
var upInput = document.getElementById('upInput');
var upPickLabel = document.getElementById('upPickLabel');
var upPreview = document.getElementById('upPreview');
var lb = document.getElementById('lightbox');
var lbImg = document.getElementById('lbImg');
var lbName = document.getElementById('lbName');
var lbCopy = document.getElementById('lbCopy');
var lbUrl = '';

upInput.addEventListener('change', function () {
  var n = upInput.files.length;
  upPickLabel.textContent = n ? '已选 ' + n + ' 张图片' : '📁 选择图片（可多选）';
  var names = [];
  for (var i = 0; i < Math.min(n, 5); i++) names.push(upInput.files[i].name);
  upPreview.style.display = n ? 'block' : 'none';
  upPreview.textContent = n ? '待上传：' + names.join('、') + (n > 5 ? ' 等 ' + n + ' 个文件' : '') : '';
});

function preview(url, name) {
  lbUrl = url;
  lbImg.src = url;
  lbName.textContent = name || '';
  lb.style.display = 'flex';
}
function closePreview() {
  lb.style.display = 'none';
  lbImg.src = '';
}
lbCopy.addEventListener('click', function () { copyUrl(lbUrl); });

function copyUrl(url) {
  var full = location.origin + '/' + location.pathname.split('/').slice(0, -1).join('/') + '/' + url;
  if (navigator.clipboard && window.isSecureContext) {
    navigator.clipboard.writeText(full).then(function () { toast('已复制: ' + full); });
  } else {
    prompt('复制此 URL 到内容里：', full);
  }
}

var toastEl = null;
function toast(msg) {
  if (!toastEl) {
    toastEl = document.createElement('div');
    toastEl.style.cssText = 'position:fixed;left:50%;bottom:40px;transform:translateX(-50%);background:#111827;color:var(--card);padding:10px 22px;border-radius:10px;font-size:13.5px;z-index:9999;box-shadow:0 10px 30px rgba(0,0,0,.35)';
    document.body.appendChild(toastEl);
  }
  toastEl.textContent = msg;
  toastEl.style.display = 'block';
  clearTimeout(toastEl._t);
  toastEl._t = setTimeout(function () { toastEl.style.display = 'none'; }, 2200);
}
</script>
</body>
</html>
