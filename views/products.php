<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>产品管理 - 得应盯后台</title>
<link rel="stylesheet" href="static/css/admin.css">
<script>
(function(){try{var t=localStorage.getItem('dy_theme')||'light';document.documentElement.setAttribute('data-theme',t);}catch(e){}})();
</script>
</head>
<body>
<div class="layout">
  <?php require __DIR__ . '/sidebar.php'; ?>
  <div class="main">
    <div class="topbar"><h1>产品管理</h1><div class="right"><span><?= e($admin_name) ?></span><form method="post" action="admin.php?m=logout" style="display:inline"><?= csrf_field() ?><button type="submit" class="logout-link">退出</button></form></div></div>
    <?php if (!empty($flash)): ?><div class="msg"><?= e($flash) ?></div><?php endif; ?>

    <div class="split-wrap">
      <!-- 左侧：发布 / 编辑产品 -->
      <div class="split-form">
        <div class="panel">
          <h2>发布 / 编辑产品</h2>
          <form method="post" action="admin.php?m=product_save">
            <input type="hidden" name="id" id="f_id" value="0">
            <div class="fg">
              <div class="field"><label>所属栏目 *</label>
                <select name="cat_id" id="f_cat">
                  <?php foreach ($cats as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= str_repeat('— ', (int)$c['pid'] !== 0 ? 1 : 0) ?><?= e($c['name']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="field"><label>产品名称 *</label><input type="text" name="title" id="f_title" required></div>
              <div class="field"><label>封面图 URL</label>
                <div class="imgpick-row">
                  <input type="text" name="cover" id="f_cover" placeholder="可填写 URL，或点右侧从图片空间选择/本地上传">
                  <button type="button" class="btn btn-s" data-imgpick data-target="f_cover" data-preview="f_cover_img">📁 图片空间</button>
                  <button type="button" class="btn btn-s" onclick="document.getElementById('coverFile').click()">⬆ 上传</button>
                  <input type="file" id="coverFile" accept="image/*" hidden onchange="uploadCover(this)">
                </div>
                <div class="cover-wrap">
                  <img id="f_cover_img" class="cover-preview" alt="封面预览" hidden>
                </div>
              </div>
              <div class="field"><label>价格</label><input type="text" name="price" id="f_price" placeholder="面议 / ¥xxx"></div>
              <div class="field"><label>推荐首页</label>
                <select name="recommend" id="f_rec"><option value="0">普通</option><option value="1">推荐</option></select>
              </div>
              <div class="field"><label>状态</label>
                <select name="status" id="f_status"><option value="1">上架</option><option value="0">下架</option></select>
              </div>
              <div class="field full"><label>摘要</label><input type="text" name="summary" id="f_summary"></div>
              <div class="field full"><label>详情</label><textarea name="content" id="f_content"></textarea></div>
            </div>
            <details class="seo-box">
              <summary>SEO 设置（可选）</summary>
              <div class="fg" style="margin-top:10px">
                <div class="field"><label>SEO 标题</label><input type="text" name="seo_title" id="f_st"></div>
                <div class="field"><label>SEO 关键词</label><input type="text" name="seo_keywords" id="f_sk"></div>
                <div class="field full"><label>SEO 描述</label><input type="text" name="seo_description" id="f_sd"></div>
              </div>
              <div style="margin-top:10px;display:flex;gap:10px;align-items:center">
                <button type="button" class="btn btn-s" onclick="aiSeo('product')">🤖 SEO 自动填写</button>
                <span class="tip" style="font-size:12px;color:var(--muted)">基于名称与详情，一键生成 SEO 三要素</span>
              </div>
            </details>

            <details class="seo-box">
              <summary>🌐 GEO 优化（AI 产品描述 + FAQ 结构化）</summary>
              <div style="margin-top:10px;display:flex;gap:10px;align-items:center;margin-bottom:10px">
                <button type="button" class="btn btn-s" onclick="aiGeo('product')">🌐 一键 GEO 优化</button>
                <span class="tip" style="font-size:12px;color:var(--muted)">生成要点化描述 + FAQ（前台输出 Product/FAQ JSON-LD）</span>
              </div>
              <div class="fg">
                <div class="field full"><label>GEO 要点化描述</label><textarea name="geo_summary" id="f_geo_summary" placeholder="AI 优化后自动生成"></textarea></div>
                <div class="field full"><label>GEO FAQ（JSON）</label><textarea name="geo_faq" id="f_geo_faq" placeholder='[{"q":"","a":""}]'></textarea></div>
              </div>
            </details>
            <div style="margin-top:14px"><button class="btn btn-p" type="submit">保存产品</button></div>
          <?= csrf_field() ?>
</form>
        </div>
      </div>

      <!-- 右侧：产品列表 -->
      <div class="split-list">
        <div class="panel">
          <h2>产品列表</h2>
          <table>
            <thead><tr><th style="width:60px">ID</th><th>名称</th><th style="width:110px">栏目</th><th style="width:90px">价格</th><th style="width:90px">推荐</th><th style="width:90px">状态</th><th style="width:110px">时间</th><th style="width:200px">操作</th></tr></thead>
            <tbody>
              <?php foreach ($list as $p): ?>
              <tr>
                <td><?= $p['id'] ?></td>
                <td><a href="index.php?act=detail&type=product&id=<?= $p['id'] ?>" target="_blank" style="color:#4f46e5"><?= e($p['title']) ?></a></td>
                <td><?= cat_name($p['cat_id'], $cats) ?></td>
                <td><?= e($p['price']) ?></td>
                <td>
                  <form method="post" action="admin.php?m=product_toggle" style="display:inline">
                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                    <input type="hidden" name="field" value="recommend">
                    <button type="submit" class="btn-tag <?= $p['recommend'] ? 'on' : 'off' ?>" title="点击切换"><?= $p['recommend'] ? '已推荐' : '普通' ?></button>
                  <?= csrf_field() ?>
</form>
                </td>
                <td>
                  <form method="post" action="admin.php?m=product_toggle" style="display:inline">
                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                    <input type="hidden" name="field" value="status">
                    <button type="submit" class="btn-tag <?= $p['status'] ? 'on' : 'off' ?>" title="点击切换"><?= $p['status'] ? '已上架' : '已下架' ?></button>
                  <?= csrf_field() ?>
</form>
                </td>
                <td><?= substr($p['created_at'], 0, 10) ?></td>
                <td>
                  <button class="btn btn-s" onclick='editProduct(<?= json_encode($p, JSON_UNESCAPED_UNICODE) ?>)'>编辑</button>
                  <form method="post" action="admin.php?m=product_del" style="display:inline" onsubmit="return confirm('确定删除？')">
                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                    <button class="btn btn-s btn-d" type="submit">删除</button>
                  <?= csrf_field() ?>
</form>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <?php require __DIR__ . '/pager.php'; ?>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="static/js/editor.js?v=<?= @filemtime(__DIR__ . '/../static/js/editor.js') ?: time() ?>"></script>
<script src="static/js/imgpick.js?v=<?= @filemtime(__DIR__ . '/../static/js/imgpick.js') ?: time() ?>"></script>
<script>
QEditor.init('f_content', { height: 380 });
ImgPick.init();

function uploadCover(input){
  var fd=new FormData(); fd.append('file',input.files[0]);
  fetch('admin.php?m=upload_json',{method:'POST',body:fd}).then(r=>r.json()).then(r=>{
    if(r.ok){ document.getElementById('f_cover').value=r.url; var img=document.getElementById('f_cover_img'); img.src=r.url; img.hidden=false; }
    else { alert(r.msg||'上传失败'); }
  });
}

function showCoverPreview() {
  var url = document.getElementById('f_cover').value, pv = document.getElementById('f_cover_img');
  if (url) { pv.src = url; pv.hidden = false; } else { pv.hidden = true; }
}

document.getElementById('f_cover').addEventListener('input', showCoverPreview);

function editProduct(p) {
  document.getElementById('f_id').value = p.id;
  document.getElementById('f_cat').value = p.cat_id;
  document.getElementById('f_title').value = p.title;
  document.getElementById('f_cover').value = p.cover || '';
  showCoverPreview();
  document.getElementById('f_price').value = p.price || '';
  document.getElementById('f_rec').value = p.recommend;
  document.getElementById('f_status').value = p.status;
  document.getElementById('f_summary').value = p.summary || '';
  QEditor.set('f_content', p.content || '');
  document.getElementById('f_st').value = p.seo_title || '';
  document.getElementById('f_sk').value = p.seo_keywords || '';
  document.getElementById('f_sd').value = p.seo_description || '';
  document.getElementById('f_geo_summary').value = p.geo_summary || '';
  document.getElementById('f_geo_faq').value = p.geo_faq || '';
}

/* ---------- AI SEO / GEO 共用接口 ---------- */
function aiSeo(type){
  var title=document.getElementById('f_title').value.trim();
  var content=document.getElementById('f_content').value||'';
  if(!title){alert('请先填写产品名称');return;}
  if(!confirm('根据名称与详情自动生成 SEO 三要素？'))return;
  var fd=new FormData();fd.append('title',title);fd.append('content',content);
  fetch('admin.php?m=ai_seo',{method:'POST',body:fd}).then(r=>r.json()).then(r=>{
    if(!r.ok){alert('⚠ '+r.msg);return;}
    if(r.seo_title)document.getElementById('f_st').value=r.seo_title;
    if(r.seo_keywords)document.getElementById('f_sk').value=r.seo_keywords;
    if(r.seo_description)document.getElementById('f_sd').value=r.seo_description;
    alert('✅ SEO 三要素已自动填写（请记得保存产品）');
  }).catch(e=>alert('⚠ 请求失败：'+e.message));
}
function aiGeo(type){
  var title=document.getElementById('f_title').value.trim();
  var content=document.getElementById('f_content').value||'';
  if(!title||!content){alert('请先填写产品名称与详情');return;}
  if(!confirm('AI 将生成要点化产品描述 + FAQ？'))return;
  var fd=new FormData();fd.append('title',title);fd.append('content',content);fd.append('type',type);
  fetch('admin.php?m=ai_geo',{method:'POST',body:fd}).then(r=>r.json()).then(r=>{
    if(!r.ok){alert('⚠ '+r.msg);return;}
    document.getElementById('f_geo_summary').value=r.summary||'';
    document.getElementById('f_geo_faq').value=JSON.stringify(r.faq||[],null,0);
    alert('✅ GEO 优化完成：已生成产品描述与 '+((r.faq||[]).length)+' 条 FAQ（请记得保存产品）');
  }).catch(e=>alert('⚠ 请求失败：'+e.message));
}
</script>
</body>
</html>
