<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>下载专区 - 得应盯后台</title>
<link rel="stylesheet" href="static/css/admin.css?v=<?= @filemtime(__DIR__ . '/../static/css/admin.css') ?: time() ?>">
<style>
  /* 兜底：防止图片空间弹窗在样式未加载或缓存异常时外露 */
  .dyimg-modal,.dyimg-toast{display:none !important}
</style>
<script>
(function(){try{var t=localStorage.getItem('dy_theme')||'light';document.documentElement.setAttribute('data-theme',t);}catch(e){}})();
</script>
</head>
<body>
<div class="layout">
  <?php require __DIR__ . '/sidebar.php'; ?>
  <div class="main">
    <div class="topbar"><h1>下载专区 <span class="pill">可分类 · 可下载源码</span></h1><div class="right"><span><?= e($admin_name) ?></span><form method="post" action="admin.php?m=logout" style="display:inline"><?= csrf_field() ?><button type="submit" class="logout-link">退出</button></form></div></div>
    <?php if (!empty($flash)): ?><div class="msg"><?= e($flash) ?></div><?php endif; ?>

    <!-- 分类管理：顶部横板 -->
    <div class="panel dl-cat-panel">
      <div class="dl-cat-head">
        <div>
          <h2 style="margin-bottom:4px">分类管理</h2>
          <p style="color:var(--muted);font-size:13px;margin:0">添加下载分类（如：源码模板 / 工具包 / 文档）。前端按分类筛选展示。</p>
        </div>
        <button type="button" class="btn btn-p" id="catAddBtn" onclick="toggleCatForm()">+ 新增分类</button>
      </div>

      <!-- 新增/编辑分类行 -->
      <form method="post" action="admin.php?m=download_cat" id="catForm" class="dl-cat-form" style="display:none">
        <input type="hidden" name="id" id="c_id" value="0">
        <div class="dl-cat-fields">
          <div class="dl-cat-field">
            <label>分类名称</label>
            <input type="text" name="name" id="c_name" placeholder="如：源码模板" required>
          </div>
          <div class="dl-cat-field sm">
            <label>排序</label>
            <input type="number" name="sort" id="c_sort" value="0">
          </div>
          <div class="dl-cat-field sm">
            <label>状态</label>
            <select name="status" id="c_status"><option value="1">启用</option><option value="0">停用</option></select>
          </div>
          <div class="dl-cat-actions">
            <button class="btn btn-p" type="submit">保存分类</button>
            <button class="btn btn-s" type="button" onclick="toggleCatForm(false)">取消</button>
          </div>
        </div>
      <?= csrf_field() ?>
</form>

      <!-- 分类标签横板 -->
      <div class="dl-cat-bar">
        <?php if (empty($cats)): ?>
          <span class="dl-cat-empty">暂无分类，点击右上角「新增分类」创建</span>
        <?php else: ?>
          <?php foreach ($cats as $c): ?>
            <div class="dl-cat-chip <?= (int)$c['status']===0 ? 'is-off' : '' ?>">
              <span class="dl-cat-name"><?= e($c['name']) ?></span>
              <span class="dl-cat-meta">排序 <?= (int)$c['sort'] ?> · <?= (int)$c['status']===1 ? '启用' : '停用' ?></span>
              <div class="dl-cat-ops">
                <button type="button" class="btn btn-s" onclick='editCat(<?= json_encode($c, JSON_UNESCAPED_UNICODE) ?>)'>编辑</button>
                <form method="post" action="admin.php?m=download_cat_del" style="display:inline" onsubmit="return confirm('确定删除该分类？其下的下载项将归为未分类。')">
                  <input type="hidden" name="id" value="<?= $c['id'] ?>">
                  <button class="btn btn-s btn-d" type="submit">删除</button>
                <?= csrf_field() ?>
</form>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

    <div class="split-wrap dl-split">
      <!-- 左侧：编辑下载项 -->
      <div class="split-form">
        <div class="panel">
          <h2 id="panelTitle">新增下载项</h2>
          <form method="post" action="admin.php?m=download_save" id="dlForm">
            <input type="hidden" name="id" id="f_id" value="0">
            <div class="fg">
              <div class="field full"><label>标题 *</label><input type="text" name="title" id="f_title" required placeholder="如：deyingding-php 官网源码 v1.2"></div>
              <div class="field"><label>所属分类</label>
                <select name="cat_id" id="f_cat">
                  <option value="0">未分类</option>
                  <?php foreach ($cats as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="field"><label>版本</label><input type="text" name="version" id="f_version" placeholder="如：v1.2 / 2026.08"></div>

              <div class="field full"><label>封面图（可选）</label>
                <div class="imgpick-row">
                  <input type="text" id="f_cover" name="cover" placeholder="可留空；支持上传或图片空间选择">
                  <button type="button" class="btn btn-s" onclick="dyImgPicker('f_cover')">图片空间</button>
                  <button type="button" class="btn btn-s" onclick="document.getElementById('coverFile').click()">上传</button>
                  <input type="file" id="coverFile" accept="image/*" hidden onchange="dyImgUpload(this,'f_cover')">
                </div>
                <img id="f_cover_img" class="cover-preview" alt="封面预览" hidden style="max-width:120px;margin-top:8px;border-radius:8px">
              </div>

              <div class="field full"><label>源码文件 *</label>
                <div class="imgpick-row">
                  <input type="text" id="f_file_url" name="file_url" placeholder="上传后自动填入；也可手动填写外部链接">
                  <button type="button" class="btn btn-s" onclick="document.getElementById('dlFile').click()">上传文件</button>
                  <input type="file" id="dlFile" hidden onchange="uploadDlFile(this)">
                </div>
                <div class="dl-file-info" id="dlFileInfo" style="display:flex;gap:10px;flex-wrap:wrap;margin-top:8px;font-size:12px;color:var(--muted)">
                  <input type="hidden" name="file_name" id="f_file_name">
                  <input type="hidden" name="file_ext" id="f_file_ext">
                  <input type="hidden" name="file_size" id="f_file_size">
                </div>
                <div class="note">支持 zip / rar / 7z / tar.gz / pdf / doc(x) / apk 等，最大 200MB。前台点击「下载」即触发下载。</div>
              </div>

              <div class="field full"><label>摘要</label><input type="text" name="summary" id="f_summary" placeholder="一句话简介，前台卡片展示"></div>
              <div class="field full"><label>下载说明（详细描述 · 富文本，可插代码块）</label><textarea name="description" id="f_description" placeholder="适用环境、安装步骤、注意事项；点工具栏「< >」插入代码块，方便用户查阅"></textarea></div>
              <div class="field"><label>排序</label><input type="number" name="sort" id="f_sort" value="0"></div>
              <div class="field"><label>状态</label>
                <select name="status" id="f_status"><option value="1">发布</option><option value="0">下架</option></select>
              </div>
            </div>
            <div style="margin-top:14px;display:flex;gap:10px">
              <button class="btn btn-p" type="submit">保存下载项</button>
              <button class="btn btn-s" type="button" onclick="resetForm()">重置</button>
            </div>
          <?= csrf_field() ?>
</form>
        </div>
      </div>

      <!-- 右侧：下载项列表 -->
      <div class="split-list">
        <div class="panel">
          <h2 style="font-size:15px;margin:0 0 12px">下载项列表</h2>
          <div class="dl-filterbar">
            <span class="dl-filterbar__label">分类：</span>
            <a href="admin.php?m=downloads" class="dl-filterbar__tab <?= $fid==0?'is-active':'' ?>">全部</a>
            <?php foreach ($cats as $c): ?>
              <a href="admin.php?m=downloads&cat=<?= $c['id'] ?>" class="dl-filterbar__tab <?= (int)$fid===(int)$c['id']?'is-active':'' ?>"><?= e($c['name']) ?></a>
            <?php endforeach; ?>
          </div>
          <?php if (empty($list)): ?>
            <div style="padding:30px;text-align:center;color:var(--muted)">暂无下载项。先在左侧添加下载项。</div>
          <?php else: ?>
          <table>
            <thead><tr><th style="width:50px">ID</th><th>标题</th><th style="width:110px">分类</th><th style="width:80px">版本</th><th style="width:70px">下载</th><th style="width:70px">状态</th><th style="width:140px">操作</th></tr></thead>
            <tbody>
              <?php foreach ($list as $d): ?>
              <tr>
                <td><?= $d['id'] ?></td>
                <td>
                  <a href="index.php?act=download" target="_blank" style="color:#4f46e5"><?= e($d['title']) ?></a>
                  <?php if ($d['file_ext']): ?><span style="font-size:11px;color:var(--muted);margin-left:6px">.<?= e($d['file_ext']) ?></span><?php endif; ?>
                </td>
                <td><?= e(cat_name($d['cat_id'], $cats)) ?></td>
                <td><?= e($d['version'] ?: '-') ?></td>
                <td><?= (int)$d['downloads'] ?></td>
                <td><?= $d['status'] ? '<span class="tag tag-ok">发布</span>' : '<span class="tag tag-off">下架</span>' ?></td>
                <td>
                  <button class="btn btn-s" onclick='editDl(<?= json_encode($d, JSON_UNESCAPED_UNICODE) ?>)'>编辑</button>
                  <form method="post" action="admin.php?m=download_del" style="display:inline" onsubmit="return confirm('确定删除该下载项？')">
                    <input type="hidden" name="id" value="<?= $d['id'] ?>">
                    <button class="btn btn-s btn-d" type="submit">删除</button>
                  <?= csrf_field() ?>
</form>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <?php require __DIR__ . '/pager.php'; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- 封面 / 图片空间 复用上传器 -->
<div class="dyimg-modal" id="dyImgPickerModal" style="display:none" onclick="if(event.target===this)dyImgPickerClose()">
  <div class="dyimg-modal__overlay"></div>
  <div class="dyimg-modal__box">
    <div class="dyimg-modal__head"><h3>从图片空间选择</h3><button type="button" class="btn btn-s" onclick="dyImgPickerClose()">关闭</button></div>
    <div class="dyimg-modal__body" id="dyImgPickerBody"><div class="dyimg-empty">加载中...</div></div>
  </div>
</div>
<div class="dyimg-toast" id="dyImgToast" style="display:none"></div>

<script src="static/js/editor.js?v=<?= @filemtime(__DIR__ . '/../static/js/editor.js') ?: time() ?>"></script>
<script>
/* ===== 下载说明富文本编辑器（与文章一致，支持代码块 / 图片） ===== */
try {
  QEditor.init('f_description', { height: 320 });
} catch (e) { console.error('QEditor init failed', e); }

/* ===== 分类管理横板 ===== */
function toggleCatForm(show){
  var f=document.getElementById('catForm'), b=document.getElementById('catAddBtn');
  if(show===false){
    f.style.display='none'; b.style.display='';
    resetCatForm();
  } else {
    f.style.display='block'; b.style.display='none';
    document.getElementById('c_name').focus();
  }
}
function resetCatForm(){
  document.getElementById('c_id').value=0;
  document.getElementById('c_name').value='';
  document.getElementById('c_sort').value=0;
  document.getElementById('c_status').value=1;
}
function editCat(c){
  document.getElementById('c_id').value=c.id||0;
  document.getElementById('c_name').value=c.name||'';
  document.getElementById('c_sort').value=c.sort||0;
  document.getElementById('c_status').value=(c.status===undefined||c.status===null)?1:c.status;
  toggleCatForm(true);
}

/* ===== 复用 tpl_edit_panels 的上传 / 图片空间逻辑 ===== */
(function(){
  var currentTarget=null, toastEl=document.getElementById('dyImgToast');
  function toast(m){ if(!toastEl)return; toastEl.textContent=m; toastEl.style.display='block'; clearTimeout(toastEl._t); toastEl._t=setTimeout(function(){toastEl.style.display='none';},2200); }
  window.dyImgSet=function(id,url){ var i=document.getElementById(id); if(!i)return; i.value=url; var pv=document.getElementById(id+'_img'); if(pv){pv.src=url;pv.hidden=false;} };
  window.dyImgUpload=function(input,target){ if(!input.files||!input.files[0])return; var label=input.closest('label')||input.parentElement; var txt=label?label.querySelector('.dyimg-btn-txt'):null; if(txt)txt.textContent='上传中...'; input.disabled=true; var fd=new FormData(); fd.append('file',input.files[0]); fetch('admin.php?m=upload_json',{method:'POST',body:fd}).then(function(r){return r.json();}).then(function(j){ if(j.ok){ dyImgSet(target,j.url); toast('上传成功'); } else { alert(j.msg||'上传失败'); } }).catch(function(e){alert('上传出错：'+e.message);}).finally(function(){ if(txt)txt.textContent='上传'; input.disabled=false; input.value=''; }); };
  window.dyImgPicker=function(target){ currentTarget=target; var modal=document.getElementById('dyImgPickerModal'), body=document.getElementById('dyImgPickerBody'); if(!modal||!body)return; modal.style.display='flex'; body.innerHTML='<div class="dyimg-empty"><span class="spinner"></span> 加载图片空间...</div>'; fetch('admin.php?m=uploads_picker').then(function(r){return r.json();}).then(function(j){ if(!j.ok||!j.list||!j.list.length){ body.innerHTML='<div class="dyimg-empty">暂无图片，请先去「图片空间」上传</div>'; return; } body.innerHTML=''; j.list.forEach(function(it){ var el=document.createElement('div'); el.className='dyimg-item'; el.title=it.name||''; el.innerHTML='<img src="'+(it.url||'')+'" alt="'+(it.name||'')+'"><div class="dyimg-item__name">'+(it.name||'')+'</div>'; el.onclick=function(){ dyImgSet(target,it.url); dyImgPickerClose(); toast('已选择图片'); }; body.appendChild(el); }); }).catch(function(e){ body.innerHTML='<div class="dyimg-empty" style="color:var(--danger)">加载失败：'+e.message+'</div>'; }); };
  window.dyImgPickerClose=function(){ var m=document.getElementById('dyImgPickerModal'); if(m)m.style.display='none'; currentTarget=null; };
})();

/* ===== 源码文件上传 ===== */
function uploadDlFile(input){
  if(!input.files||!input.files[0])return;
  var btn=input.previousElementSibling, old=btn.textContent; btn.textContent='上传中...'; btn.disabled=true;
  var fd=new FormData(); fd.append('file',input.files[0]);
  fetch('admin.php?m=download_file_upload',{method:'POST',body:fd}).then(function(r){return r.json();}).then(function(j){
    if(j.ok){
      document.getElementById('f_file_url').value=j.url;
      document.getElementById('f_file_name').value=j.name||'';
      document.getElementById('f_file_ext').value=j.ext||'';
      document.getElementById('f_file_size').value=j.size||'';
      var info=document.getElementById('dlFileInfo');
      info.innerHTML='<span class="dyimg-item__name" style="background:var(--card);border:1px solid var(--line);padding:4px 10px;border-radius:8px">📦 '+eHtml(j.name||'')+' · '+(j.ext||'')+' · '+(j.size||'')+'</span>'
        +'<input type="hidden" name="file_name" id="f_file_name" value="'+eAttr(j.name||'')+'">'
        +'<input type="hidden" name="file_ext" id="f_file_ext" value="'+eAttr(j.ext||'')+'">'
        +'<input type="hidden" name="file_size" id="f_file_size" value="'+eAttr(j.size||'')+'">';
      toastMsg('文件已上传');
    } else { alert(j.msg||'上传失败'); }
  }).catch(function(e){alert('上传出错：'+e.message);}).finally(function(){ btn.textContent=old; btn.disabled=false; input.value=''; });
}
function eAttr(s){ return String(s).replace(/"/g,'&quot;'); }
function eHtml(s){ return String(s).replace(/[&<>]/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;'}[c];}); }
function toastMsg(m){ var t=document.getElementById('dyImgToast'); if(!t)return; t.textContent=m; t.style.display='block'; clearTimeout(t._t); t._t=setTimeout(function(){t.style.display='none';},2200); }

/* ===== 编辑回填 ===== */
function editDl(d){
  document.getElementById('panelTitle').textContent='编辑下载项 #'+d.id;
  document.getElementById('f_id').value=d.id;
  document.getElementById('f_title').value=d.title||'';
  document.getElementById('f_cat').value=d.cat_id||0;
  document.getElementById('f_version').value=d.version||'';
  document.getElementById('f_cover').value=d.cover||'';
  var pv=document.getElementById('f_cover_img'); if(d.cover){pv.src=d.cover;pv.hidden=false;}else{pv.hidden=true;}
  document.getElementById('f_file_url').value=d.file_url||'';
  document.getElementById('f_file_name').value=d.file_name||'';
  document.getElementById('f_file_ext').value=d.file_ext||'';
  document.getElementById('f_file_size').value=d.file_size||'';
  document.getElementById('f_summary').value=d.summary||'';
  QEditor.set('f_description', d.description||'');
  document.getElementById('f_sort').value=d.sort||0;
  document.getElementById('f_status').value=d.status;
  var info=document.getElementById('dlFileInfo');
  if(d.file_name){
    info.innerHTML='<span class="dyimg-item__name" style="background:var(--card);border:1px solid var(--line);padding:4px 10px;border-radius:8px">📦 '+eHtml(d.file_name)+' · '+(d.file_ext||'')+' · '+(d.file_size||'')+'</span>'
      +'<input type="hidden" name="file_name" id="f_file_name" value="'+eAttr(d.file_name)+'">'
      +'<input type="hidden" name="file_ext" id="f_file_ext" value="'+eAttr(d.file_ext||'')+'">'
      +'<input type="hidden" name="file_size" id="f_file_size" value="'+eAttr(d.file_size||'')+'">';
  } else { info.innerHTML='<input type="hidden" name="file_name" id="f_file_name"><input type="hidden" name="file_ext" id="f_file_ext"><input type="hidden" name="file_size" id="f_file_size">'; }
  document.getElementById('f_title').focus();
}
function resetForm(){
  document.getElementById('panelTitle').textContent='新增下载项';
  document.getElementById('dlForm').reset();
  document.getElementById('f_id').value=0;
  document.getElementById('f_cover_img').hidden=true;
  document.getElementById('dlFileInfo').innerHTML='<input type="hidden" name="file_name" id="f_file_name"><input type="hidden" name="file_ext" id="f_file_ext"><input type="hidden" name="file_size" id="f_file_size">';
  QEditor.set('f_description', '');
}
</script>
</body>
</html>
