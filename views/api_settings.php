<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>API 配置 - 得应盯后台</title>
  <link rel="stylesheet" href="static/css/admin.css">
  <script>
(function(){try{var t=localStorage.getItem('dy_theme')||'light';document.documentElement.setAttribute('data-theme',t);}catch(e){}})();
</script>
  <style>
  .geo-badge{font-size:11px;font-weight:600;padding:1px 8px;border-radius:20px;margin-left:8px;vertical-align:middle}
  .geo-badge.ok{background:rgba(34,197,94,.14);color:#16a34a}
  .geo-badge.warn{background:rgba(245,158,11,.14);color:#d97706}
  .pw-wrap{display:flex;gap:8px;align-items:center}
  .pw-wrap input{flex:1}
  .pw-toggle{border:1px solid var(--line);background:var(--card-2);color:var(--muted);border-radius:8px;padding:0 12px;height:38px;cursor:pointer;font-size:13px;white-space:nowrap}
  .pw-toggle:hover{color:var(--primary)}
  .pw-mask{font-size:12px;color:var(--muted);margin-top:6px;font-family:ui-monospace,SFMono-Regular,Menlo,monospace}
  .model-pick{display:flex;gap:8px;align-items:center}
  .model-pick select{flex:1}
  .fetch-btn{border:1px solid var(--primary);background:transparent;color:var(--primary);border-radius:8px;padding:0 14px;height:38px;cursor:pointer;font-size:13px;white-space:nowrap}
  .fetch-btn:hover{background:var(--primary);color:#fff}
  .fetch-btn:disabled{opacity:.6;cursor:not-allowed}
  .fetch-msg{font-size:12px;margin-top:6px;color:var(--muted)}
  .fetch-msg.ok{color:#16a34a}
  .fetch-msg.err{color:#dc2626}
  </style>
</head>
<body>
<div class="layout">
  <?php require __DIR__ . '/sidebar.php'; ?>
  <div class="main">
    <div class="topbar">
      <h1>⚙ API 配置 <span class="pill">全站通用</span></h1>
      <div class="right"><span><?= e($admin_name) ?></span><form method="post" action="admin.php?m=logout" style="display:inline"><?= csrf_field() ?><button type="submit" class="logout-link">退出</button></form></div>
    </div>
    <?php if (!empty($flash)): ?><div class="msg"><?= e($flash) ?></div><?php endif; ?>

    <div class="panel" style="max-width:760px">
      <h2>全局 AI 接口（文章写作 / GEO 优化 / SEO 填写 / 产品描述 共用）</h2>
      <div class="note" style="margin-bottom:16px">
        只需在此填写一次 DeepSeek / 任意 OpenAI 协议接口的地址与 Key，<b>全站所有 AI 功能</b>（文章 AI 写作、🌐 GEO 优化、🤖 SEO 自动填写、产品 GEO 描述）统一调用，无需重复配置。
      </div>

      <form method="post" action="admin.php?m=api_save">
        <div class="api-config__body" style="display:block">
          <div class="field"><label>写作 / GEO / SEO API 地址</label><input type="url" id="ai_api_url" name="ai_api_url" value="<?= e($cfg['ai_api_url'] ?? 'https://api.deepseek.com/v1') ?>" placeholder="https://api.deepseek.com/v1"></div>
          <div class="field">
            <label>写作 / GEO / SEO API Key
              <?php if (!empty($cfg['ai_api_key'])): ?><span class="geo-badge ok">● 已配置</span><?php else: ?><span class="geo-badge warn">○ 未配置</span><?php endif; ?>
            </label>
            <div class="pw-wrap">
              <input type="password" id="ai_api_key" name="ai_api_key" value="" placeholder="sk-..." autocomplete="off">
              <button type="button" class="pw-toggle" onclick="togglePw('ai_api_key',this)">显示</button>
            </div>
            <?php if (!empty($cfg['ai_api_key'])): ?>
              <div class="pw-mask">当前保存：<?= e(mask_key($cfg['ai_api_key'])) ?></div>
            <?php endif; ?>
          </div>
          <div class="field"><label>写作模型</label>
            <div class="model-pick">
              <select id="ai_model" name="ai_model">
                <option value="" <?= empty($cfg['ai_model']) ? 'selected' : '' ?> disabled>请选择模型（拉取后可选）</option>
                <option value="deepseek-chat" <?= (isset($cfg['ai_model']) && $cfg['ai_model']==='deepseek-chat')?'selected':'' ?>>deepseek-chat</option>
                <option value="deepseek-reasoner" <?= (isset($cfg['ai_model']) && $cfg['ai_model']==='deepseek-reasoner')?'selected':'' ?>>deepseek-reasoner</option>
                <option value="gpt-4o" <?= (isset($cfg['ai_model']) && $cfg['ai_model']==='gpt-4o')?'selected':'' ?>>gpt-4o</option>
                <?php if (!empty($cfg['ai_model']) && !in_array($cfg['ai_model'], ['deepseek-chat','deepseek-reasoner','gpt-4o'])): ?><option value="<?= e($cfg['ai_model']) ?>" selected><?= e($cfg['ai_model']) ?>（当前）</option><?php endif; ?>
              </select>
              <button type="button" class="fetch-btn" onclick="fetchModels('ai', this)">🔄 拉取可用模型</button>
            </div>
            <div class="fetch-msg" id="ai_model_msg"></div>
            <div class="pw-mask">填好上方「API 地址 + 密钥」后，点右侧按钮自动从接口拉取全部可用模型</div>
          </div>
        </div>

        <div class="ai-divider"></div>
        <h3 style="font-size:14px;margin:6px 0 10px">生图 API（可选，用于 AI 写作自动插图）</h3>
        <div class="api-config__body" style="display:block">
          <div class="field"><label>生图 API 地址</label><input type="url" id="ai_img_url" name="ai_img_url" value="<?= e($cfg['ai_img_url'] ?? 'https://api.openai.com/v1') ?>" placeholder="https://api.openai.com/v1"></div>
          <div class="field">
            <label>生图 API Key
              <?php if (!empty($cfg['ai_img_key'])): ?><span class="geo-badge ok">● 已配置</span><?php else: ?><span class="geo-badge warn">○ 未配置</span><?php endif; ?>
            </label>
            <div class="pw-wrap">
              <input type="password" id="ai_img_key" name="ai_img_key" value="" placeholder="sk-..." autocomplete="off">
              <button type="button" class="pw-toggle" onclick="togglePw('ai_img_key',this)">显示</button>
            </div>
            <?php if (!empty($cfg['ai_img_key'])): ?>
              <div class="pw-mask">当前保存：<?= e(mask_key($cfg['ai_img_key'])) ?></div>
            <?php endif; ?>
          </div>
          <div class="field"><label>生图模型</label>
            <div class="model-pick">
              <select id="ai_img_model" name="ai_img_model">
                <option value="" <?= empty($cfg['ai_img_model']) ? 'selected' : '' ?> disabled>请选择模型（拉取后可选）</option>
                <option value="dall-e-3" <?= (isset($cfg['ai_img_model']) && $cfg['ai_img_model']==='dall-e-3')?'selected':'' ?>>dall-e-3</option>
                <option value="gpt-image-1" <?= (isset($cfg['ai_img_model']) && $cfg['ai_img_model']==='gpt-image-1')?'selected':'' ?>>gpt-image-1</option>
                <?php if (!empty($cfg['ai_img_model']) && !in_array($cfg['ai_img_model'], ['dall-e-3','gpt-image-1'])): ?><option value="<?= e($cfg['ai_img_model']) ?>" selected><?= e($cfg['ai_img_model']) ?>（当前）</option><?php endif; ?>
              </select>
              <button type="button" class="fetch-btn" onclick="fetchModels('img', this)">🔄 拉取可用模型</button>
            </div>
            <div class="fetch-msg" id="ai_img_model_msg"></div>
          </div>
        </div>

        <div class="api-config__foot" style="text-align:right;margin-top:14px">
          <button class="btn btn-p" type="submit">💾 保存 API 配置</button>
        </div>
      <?= csrf_field() ?>
</form>

      <div class="note">
        <b>说明：</b>本系统采用基础 GEO 策略——不联网抓取实时数据，而是把<b>已有</b>的文章/产品改写成 AI 引擎最易引用的形态（结论先行 + 要点化 + FAQ 结构化 + JSON-LD），一次结构化同时服务 SEO（富摘要）与 GEO（AI 引用）。
      </div>
    </div>
  </div>
</div>
<script>
function togglePw(id, btn){
  var el=document.getElementById(id);
  if(el.type==='password'){el.type='text';btn.textContent='隐藏';}
  else{el.type='password';btn.textContent='显示';}
}

function fillSelect(selId, models, saved, msgId){
  var sel = document.getElementById(selId);
  var msg = document.getElementById(msgId);
  if(!sel) return;
  var keep = (saved && models.indexOf(saved) === -1) ? saved : null;
  sel.innerHTML = '';
  models.forEach(function(m){
    var o = document.createElement('option');
    o.value = m; o.textContent = m;
    if(m === saved) o.selected = true;
    sel.appendChild(o);
  });
  if(keep){
    var o = document.createElement('option');
    o.value = keep; o.textContent = keep + '（当前）';
    o.selected = true; sel.appendChild(o);
  }
  if(msg){ msg.className = 'fetch-msg ok'; msg.textContent = '已拉取 ' + models.length + ' 个模型' + (keep ? '（当前已选不在列表中，已为你保留）' : ''); }
}

function fetchModels(kind, btn){
  var urlId = kind === 'img' ? 'ai_img_url' : 'ai_api_url';
  var keyId = kind === 'img' ? 'ai_img_key' : 'ai_api_key';
  var selId = kind === 'img' ? 'ai_img_model' : 'ai_model';
  var msgId = selId + '_msg';
  var urlEl = document.getElementById(urlId);
  var keyEl = document.getElementById(keyId);
  var msg = document.getElementById(msgId);
  var sel = document.getElementById(selId);
  var saved = sel ? sel.value : '';
  var url = urlEl ? urlEl.value.trim() : '';
  var key = keyEl ? keyEl.value.trim() : '';
  if(!url || !key){
    if(msg){ msg.className = 'fetch-msg err'; msg.textContent = '请先填写本区域的 API 地址与密钥'; }
    return;
  }
  if(btn){ btn.disabled = true; btn.textContent = '保存并拉取中…'; }
  if(msg){ msg.className = 'fetch-msg'; msg.textContent = '正在保存当前配置并拉取模型列表…'; }

  // 1) 先把当前输入框里的 url/key 静默保存到数据库
  var saveFd = new FormData();
  saveFd.append('ajax', '1');
  saveFd.append('ai_api_url', document.getElementById('ai_api_url') ? document.getElementById('ai_api_url').value.trim() : '');
  saveFd.append('ai_api_key', document.getElementById('ai_api_key') ? document.getElementById('ai_api_key').value.trim() : '');
  saveFd.append('ai_model', document.getElementById('ai_model') ? document.getElementById('ai_model').value : '');
  saveFd.append('ai_img_url', document.getElementById('ai_img_url') ? document.getElementById('ai_img_url').value.trim() : '');
  saveFd.append('ai_img_key', document.getElementById('ai_img_key') ? document.getElementById('ai_img_key').value.trim() : '');
  saveFd.append('ai_img_model', document.getElementById('ai_img_model') ? document.getElementById('ai_img_model').value : '');

  fetch('admin.php?m=api_save', {method:'POST', body: saveFd})
    .then(function(r){ return r.json(); })
    .then(function(s){
      if(!s.ok){ if(msg){ msg.className='fetch-msg err'; msg.textContent = '自动保存失败：' + (s.msg || '未知错误'); } if(btn){ btn.disabled=false; btn.textContent='🔄 拉取可用模型'; } return; }
      // 2) 保存成功后再拉模型
      var fd = new FormData();
      fd.append('url', url);
      fd.append('key', key);
      fetch('admin.php?m=api_fetch_models', {method:'POST', body: fd})
        .then(function(r){ return r.json(); })
        .then(function(j){
          if(btn){ btn.disabled = false; btn.textContent = '🔄 拉取可用模型'; }
          if(!j.ok){ if(msg){ msg.className='fetch-msg err'; msg.textContent = j.msg || '拉取失败'; } return; }
          fillSelect(selId, j.models, saved, msgId);
        })
        .catch(function(e){
          if(btn){ btn.disabled = false; btn.textContent = '🔄 拉取可用模型'; }
          if(msg){ msg.className='fetch-msg err'; msg.textContent = '请求出错：' + e; }
        });
    })
    .catch(function(e){
      if(btn){ btn.disabled = false; btn.textContent = '🔄 拉取可用模型'; }
      if(msg){ msg.className='fetch-msg err'; msg.textContent = '自动保存请求出错：' + e; }
    });
}
</script>
</body>
</html>
