<?php
/**
 * deyingding-php 前台入口
 * 路由: index.php?act=home|list|detail|city   （可选 ?city=城市名 触发分站）
 */

/* 未安装 → 跳转安装向导 */
if (!file_exists(__DIR__ . '/install.lock')) {
    header('Location: install/index.php');
    exit;
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/funcs.php';
ensure_schema(); // 旧库自动升级（users 表 + site_id 列）
track_visit();   // 记录访问日志（IP / 地域 / 设备 / 来源）
maybe_auto_post(); // 网页伪 Cron：到点自动 AI 写文并发布（无需宝塔计划任务）
ai_img_queue_pick_one(); // 网页伪 Cron：每次前台访问处理 1 篇配图队列任务（兜底，无宝塔计划任务也能跑）
geo_monitor_maybe_run(); // 网页伪 Cron：启用后每日自动对若干文章跑 GEO 探针（检测是否被 AI 引用）

$act  = $_GET['act'] ?? 'home';
$sid  = current_site_id();
$city = current_city(); // 分站开启且 ?city= 有效时返回分站配置
// 记住当前分站城市（30 天）：分站内打开文章详情等无 city 参数的页面也能保持城市上下文，标题自动带城市名
if ($city && !empty($city['pinyin'])) {
    setcookie('dy_city', (string)$city['pinyin'], time() + 86400 * 30, '/', '', false, true);
} elseif ($city && !empty($city['city'])) {
    setcookie('dy_city', (string)$city['city'], time() + 86400 * 30, '/', '', false, true);
}

/* 全站 SEO 兜底（分站可覆盖） */
$cityArr    = $city ?: ['keywords' => '', 'description' => '', 'title_suffix' => '', 'city' => ''];
$siteTitle  = setting('site_name', '得应盯网络科技');
$keywords   = !empty($cityArr['keywords']) ? $cityArr['keywords'] : setting('seo_keywords');
$descAll    = !empty($cityArr['description']) ? $cityArr['description'] : setting('seo_description');
$titleSuffix = $cityArr['title_suffix'] ?? '';
$suffix     = $city ? ' - ' . $city['city'] . $titleSuffix : '';

/* 全站通用数据（按当前站点隔离） */
$navAll   = nav_tree();                       // 顶部导航（树形，含子栏目）
$dlCats   = DB::all('SELECT * FROM categories WHERE site_id=? AND type=? AND status=1 ORDER BY sort ASC, id ASC', [$sid, 'download']); // 下载专区分类（导航下拉用）
$navFlat  = DB::all("SELECT * FROM categories WHERE status=1 AND site_id=? AND (type IS NULL OR type='' OR type!='download') ORDER BY sort ASC, id ASC", [$sid]); // 侧栏/全量（不含下载分类）
$newsFoot = DB::all('SELECT * FROM articles WHERE status=1 AND site_id=? ORDER BY id DESC LIMIT 3', [$sid]);    // 页脚最新动态
$aboutUrl    = 'index.php?act=list&cat=0';
$contactUrl  = 'index.php?act=city';

/* ---------- 首页 ---------- */
if ($act === 'home') {
    $title = $siteTitle . $suffix;
    $pros  = DB::all('SELECT * FROM products WHERE status=1 AND recommend=1 AND site_id=? ORDER BY id DESC LIMIT 4', [$sid]);
    $news  = DB::all('SELECT * FROM articles WHERE status=1 AND site_id=? ORDER BY id DESC LIMIT 3', [$sid]);
    /* 模板中心可视化编辑器：实时预览（仅本次渲染覆盖，不落库） */
    $homeSettings = settings_all();
    if (!empty($_GET['preview'])) {
        $pvAllowed = ['hero','scenario','stats','capabilities','about','products','workflow','news','cta','contact','ticker','board','collections','story','timeline','quote'];
        // 支持 GET / POST 两种 preview_lm/preview_md，POST 用于避免 URL 超长（含 base64 SVG 时）
        $pvLayoutRaw = $_REQUEST['preview_lm'] ?? '[]';
        $pvLayout = is_string($pvLayoutRaw) ? json_decode($pvLayoutRaw, true) : [];
        if (is_array($pvLayout)) {
            $clean = [];
            foreach ($pvLayout as $it) {
                $k = $it['key'] ?? '';
                if (in_array($k, $pvAllowed, true)) { $clean[] = ['key' => $k, 'show' => !empty($it['show']) ? 1 : 0]; }
            }
            if ($clean) { $homeSettings['home_layout'] = json_encode($clean, JSON_UNESCAPED_UNICODE); }
        }
        $pvModulesRaw = $_REQUEST['preview_md'] ?? '{}';
        $pvModules = is_string($pvModulesRaw) ? json_decode($pvModulesRaw, true) : [];
        if (is_array($pvModules)) {
            $mods = [];
            foreach ($pvModules as $k => $v) {
                if (in_array($k, $pvAllowed, true) && is_array($v)) {
                    $mods[$k] = $v; // 保留完整结构（含 images / items 等嵌套数组）
                }
            }
            if ($mods) { $homeSettings['home_modules'] = json_encode($mods, JSON_UNESCAPED_UNICODE); }
        }
    }
    $view = [
        'title' => $title, 'nav' => $navAll, 'settings' => $homeSettings, 'city' => $city,
        'pros' => $pros, 'news' => $news, 'site' => $siteTitle, 'cat' => null,
        'kw' => $keywords, 'desc' => $descAll,
        'aboutUrl' => $aboutUrl, 'contactUrl' => $contactUrl, 'newsFoot' => $newsFoot,
    ];
    if (!empty($_GET['ve'])) {
        // 画布编辑模式（后台「首页 DIY」iframe 专用）：悬停高亮 + 点击通知父窗口选中组件
        ob_start();
        render_tpl('home.php', $view);
        $html = ob_get_clean();
        $veJs = <<<'JS'
<script>
(function(){
  if(window.parent === window) return;

  /* ===== 进入编辑模式：标识 + 禁用所有动效 ===== */
  document.documentElement.classList.add('ve-editing');

  /* ===== 注入编辑模式样式（去掉 focus 蓝框/背景，保持原视觉） ===== */
  var CSS =
    /* 编辑态全局：关闭动效 */
    '.ve-editing *, .ve-editing *::before, .ve-editing *::after{animation-duration:0s !important;animation-delay:0s !important;animation-iteration-count:1 !important;transition-duration:0s !important;transition-delay:0s !important;scroll-behavior:auto !important}' +
    /* 滚动揭示直接显示，不被 IntersectionObserver 隐藏 */
    '.ve-editing .q-reveal,.ve-editing .q-reveal.d1,.ve-editing .q-reveal.d2,.ve-editing .q-reveal.d3{opacity:1!important;transform:none!important}' +
    /* 画布、像素人、悬浮气泡隐藏（避免动效干扰编辑） */
    '.ve-editing #net,.ve-editing .scanline,.ve-editing .glow,.ve-editing #pixelMarioScene{display:none!important}' +
    /* 区块：完全不显示 hover 提示、不响应点空白；只有文字/按钮可交互 */
    '.ve-slot{position:relative}' +
    '[data-ve-editable]{cursor:text}' +
    '[data-ve-editable]:hover{outline:1px dashed rgba(34,211,238,.55);outline-offset:2px}' +
    '[data-ve-editable]:focus{outline:none;background:transparent}' +
    '[data-ve-btn]{cursor:pointer;position:relative}' +
    '[data-ve-btn]:hover{outline:2px dashed #f59e0b;outline-offset:3px}' +
    /* 浮动按钮编辑器 */
    '.ve-pop{position:absolute;background:#0f172a;color:#e8eef8;border:1px solid #22d3ee;border-radius:12px;padding:14px;width:360px;max-width:calc(100vw - 16px);box-shadow:0 14px 44px rgba(0,0,0,.45);z-index:99999;font-family:inherit;font-size:13px;box-sizing:border-box}' +
    '.ve-pop h4{margin:0 0 10px;font-size:14px;color:#22d3ee;display:flex;justify-content:space-between;align-items:center}' +
    '.ve-pop label{display:block;font-size:12px;color:#93a0b8;margin:8px 0 4px}' +
    '.ve-pop input,.ve-pop select,.ve-pop textarea{width:100%;padding:7px 9px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.16);color:#e8eef8;border-radius:7px;font-size:13px;box-sizing:border-box;font-family:inherit}' +
    '.ve-pop select{margin-bottom:6px}' +
    '.ve-pop textarea{resize:vertical;min-height:60px}' +
    '.ve-pop .ve-pop__close{background:none;border:none;color:#93a0b8;font-size:20px;cursor:pointer;padding:0 4px;line-height:1}' +
    '.ve-pop .ve-pop__close:hover{color:#f87171}' +
    '.ve-pop__pick{margin-top:4px}' +
    '.ve-pop__tip{font-size:11px;color:#5e6b85;margin-top:6px}';
  var st=document.createElement('style'); st.textContent=CSS; document.head.appendChild(st);

  /* ===== 停掉 main.js 的动画启动器：禁用 IntersectionObserver、移除 canvas/mario ===== */
  // 拦截后续可能的 IntersectionObserver
  window.IntersectionObserver = function(){
    this.observe = function(){}; this.unobserve = function(){}; this.disconnect = function(){};
  };
  // 移除神经网络画布 + 隐藏 mario（main.js 会因找不到 #net / marioScene 跳过动画）
  var netCv = document.getElementById('net'); if(netCv && netCv.parentNode) netCv.parentNode.removeChild(netCv);
  var mario = document.getElementById('pixelMarioScene'); if(mario) mario.style.display='none';
  // 暂停所有还在运行的 requestAnimationFrame（main.js 可能已在跑 canvas 动画）
  try {
    var origRAF = window.requestAnimationFrame;
    var rafs = [];
    window.requestAnimationFrame = function(cb){ var id = setTimeout(function(){try{cb(performance.now());}catch(e){}},16); rafs.push(id); return id; };
    window.__veCancelAnims = function(){ rafs.forEach(function(id){clearTimeout(id);}); rafs = []; };
  } catch(e) {}
  // 取消已有 setInterval / setTimeout（mario 循环）
  var maxId = setTimeout(function(){}, 0);
  for(var i=1; i<maxId; i++){ clearInterval(i); }
  // 强制所有 .q-reveal 显示（覆盖 main.js 可能设置的 inline style）
  document.querySelectorAll('.q-reveal').forEach(function(el){el.style.opacity='1'; el.style.transform='none';});

  function esc(s){return String(s==null?'':s).replace(/[&<>"]/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c];});}
  function post(d){try{window.parent.postMessage(d,'*');}catch(e){}}

  /* ===== 链接类型识别 ===== */
  function parseLink(url){
    if(!url) return {type:'custom', id:''};
    var m;
    if((m = url.match(/index\.php\?act=detail&type=article&id=(\d+)/))) return {type:'article', id:m[1]};
    if((m = url.match(/index\.php\?act=detail&type=product&id=(\d+)/))) return {type:'product', id:m[1]};
    if((m = url.match(/index\.php\?act=form&id=(\d+)/))) return {type:'form', id:m[1]};
    if((m = url.match(/index\.php\?act=(?:download|detail&type=download)&id=(\d+)/))) return {type:'download', id:m[1]};
    if(/^(?:uploads\/|[a-z]+:\/\/)/i.test(url) || /\.(zip|rar|7z|pdf|docx?|xlsx?|pptx?|apk|exe)(?:[?#]|$)/i.test(url)) return {type:'download', id:url};
    return {type:'custom', id:url};
  }
  function linkToUrl(type, id){id=id||'';if(type==='article')return 'index.php?act=detail&type=article&id='+id;if(type==='product')return 'index.php?act=detail&type=product&id='+id;if(type==='form')return 'index.php?act=form&id='+id;return id;}

  /* ===== 字段映射 ===== */
  var LINKS = window.VE_LINKS || {articles:[], products:[], forms:[], downloads:[]};
  var FIELD_MAP = [
    {slot:'hero', sel:'.q-kicker', field:'kicker', type:'text'},
    {slot:'hero', sel:'.q-hero__title', field:'title', type:'html'},
    {slot:'hero', sel:'.q-hero__sub', field:'sub', type:'html'},
    {slot:'hero', sel:'.q-hero__cta .q-btn', field:'btn', type:'btn', textKey:'btn_text', urlKey:'btn_url'},
    {slot:'hero', sel:'.q-hero__tags', field:'tags', type:'tags'},
    {slot:'scenario', sel:'.q-kicker', field:'kicker', type:'text'},
    {slot:'scenario', sel:'.q-title', field:'title', type:'html'},
    {slot:'scenario', sel:'.q-desc', field:'desc', type:'text'},
    {slot:'scenario', sel:'.q-hero__cta .q-btn', field:'btn', type:'btn', textKey:'cta_text', urlKey:'cta_url'},
    {slot:'capabilities', sel:'.q-kicker', field:'kicker', type:'text'},
    {slot:'capabilities', sel:'.q-title', field:'title', type:'html'},
    {slot:'capabilities', sel:'.q-desc', field:'desc', type:'text'},
    {slot:'capabilities', sel:'.q-feat__cta .q-btn', field:'btn', type:'btn', textKey:'cta_text', urlKey:'cta_url'},
    {slot:'about', sel:'.q-kicker', field:'kicker', type:'text'},
    {slot:'about', sel:'.q-title', field:'title', type:'html'},
    {slot:'about', sel:'.q-desc', field:'desc', type:'text'},
    {slot:'about', sel:'.q-split a.q-btn', field:'btn', type:'btn', textKey:'cta_text', urlKey:'cta_url'},
    {slot:'products', sel:'.q-kicker', field:'kicker', type:'text'},
    {slot:'products', sel:'.q-title', field:'title', type:'html'},
    {slot:'products', sel:'.q-desc', field:'desc', type:'text'},
    {slot:'products', sel:'.q-more', field:'more', type:'btnText', textKey:'more_text'},
    {slot:'workflow', sel:'.q-kicker', field:'kicker', type:'text'},
    {slot:'workflow', sel:'.q-title', field:'title', type:'html'},
    {slot:'workflow', sel:'.q-desc', field:'desc', type:'text'},
    {slot:'news', sel:'.q-kicker', field:'kicker', type:'text'},
    {slot:'news', sel:'.q-title', field:'title', type:'html'},
    {slot:'news', sel:'.q-desc', field:'desc', type:'text'},
    {slot:'news', sel:'.q-more', field:'more', type:'btnText', textKey:'more_text'},
    {slot:'cta', sel:'.q-title', field:'title', type:'html'},
    {slot:'cta', sel:'.q-cta p', field:'sub', type:'html'},
    {slot:'cta', sel:'.q-cta__row .q-btn', field:'btn', type:'btn', textKey:'btn_text', urlKey:'btn_url'},
    {slot:'contact', sel:'.q-kicker', field:'kicker', type:'text'},
    {slot:'contact', sel:'.q-title', field:'title', type:'html'},
    {slot:'contact', sel:'.q-desc', field:'desc', type:'text'},
    {slot:'contact', sel:'.q-btn--grad', field:'cta_btn', type:'btn', textKey:'cta_text', urlKey:'cta_url'},
    {slot:'contact', sel:'.q-btn--ghost', field:'sub_btn', type:'btn', textKey:'sub_text', urlKey:'sub_url'},
  ];

  /* 点空白不做任何事 —— 只有点文字/按钮才交互 */

  /* ===== 文字/HTML/tags 元素：单击进入编辑，blur 保存（不破坏样式） ===== */
  FIELD_MAP.forEach(function(m){
    if(m.type!=='text' && m.type!=='html' && m.type!=='tags') return;
    var slot = document.querySelector('.ve-slot[data-ve="'+m.slot+'"]');
    if(!slot) return;
    var el = slot.querySelector(m.sel);
    if(!el) return;
    el.setAttribute('data-ve-editable','1');
    el.setAttribute('title','双击编辑');
    el.addEventListener('click', function(ev){ev.stopPropagation();});
    el.addEventListener('dblclick', function(ev){
      ev.stopPropagation(); ev.preventDefault();
      el.style.outline='none';
      el.style.background='transparent';
      el.contentEditable = (m.type==='html') ? 'true' : 'plaintext-only';
      el.focus();
      // 全选方便直接覆盖输入
      try { var r=document.createRange(); r.selectNodeContents(el); var s=window.getSelection(); s.removeAllRanges(); s.addRange(r); } catch(e){}
    });
    function save(){
      var val;
      if(m.type==='tags'){
        val = Array.from(el.querySelectorAll('span b')).map(function(b){return b.textContent.trim();}).filter(Boolean).join(',');
      } else if(m.type==='html'){
        val = el.innerHTML.trim();
      } else {
        val = el.textContent.trim();
      }
      post({ve:'update', key:m.slot, field:m.field, value:val});
      el.contentEditable = 'false';
      try { window.getSelection().removeAllRanges(); } catch(e){}
    }
    el.addEventListener('blur', save);
    el.addEventListener('keydown', function(ev){
      if(ev.key==='Enter' && !ev.shiftKey && m.type!=='html'){ev.preventDefault();el.blur();}
      if(ev.key==='Escape'){ev.preventDefault();el.blur();}
    });
  });

  /* ===== 按钮：单击弹浮动编辑器 ===== */
  var curPop = null;
  function closePop(){if(curPop){curPop.remove();curPop=null;}}

  function buildBtnPop(m, btn){
    var pop = document.createElement('div');
    pop.className = 've-pop';
    var textKey = m.textKey || null;
    var urlKey  = m.urlKey  || null;
    var textVal = btn.textContent.trim();
    var urlVal  = btn.getAttribute('href') || '';

    var html = '<h4>编辑按钮<button type="button" class="ve-pop__close" title="关闭">×</button></h4>'
      + '<label>按钮文字</label>'
      + '<input type="text" data-f="text" value="'+esc(textVal)+'">';
    if(urlKey){
      html += '<label>链接类型</label>'
        + '<select data-f="type">'
        + '<option value="custom">自定义链接</option>'
        + '<option value="article">文章（站内）</option>'
        + '<option value="product">产品（站内）</option>'
        + '<option value="form">表单提交</option>'
        + '<option value="download">下载文件</option>'
        + '</select>'
        + '<div data-f="pick"></div>'
        + '<p class="ve-pop__tip">💡 选「文章/产品/表单」直接选内容；选「下载」可填路径或从下载专区选；选「自定义」填网址</p>';
    }
    pop.innerHTML = html;
    pop.addEventListener('click', function(ev){ev.stopPropagation();});

    var typeSel = pop.querySelector('select[data-f="type"]');
    var pickBox = pop.querySelector('[data-f="pick"]');

    function refreshPick(){
      if(!typeSel || !pickBox) return;
      var pl = parseLink(urlVal);
      typeSel.value = pl.type;
      var h = '';
      if(pl.type==='article' || pl.type==='product'){
        var arr = pl.type==='article' ? LINKS.articles : LINKS.products;
        if(!arr.length){h='<p class="ve-pop__tip">暂无可选'+(pl.type==='article'?'文章':'产品')+'，请先在「文章管理/产品管理」添加</p>';}
        else {
          h = '<select data-f="id"><option value="">选择'+(pl.type==='article'?'文章':'产品')+'…</option>';
          arr.forEach(function(a){h += '<option value="'+a.id+'"'+(String(a.id)===String(pl.id)?' selected':'')+'>'+esc(a.title)+'</option>';});
          h += '</select>';
        }
      } else if(pl.type==='form'){
        if(!LINKS.forms.length){h='<p class="ve-pop__tip">暂无可选表单，请先在「自定义表单」添加</p>';}
        else {
          h = '<select data-f="id"><option value="">选择表单…</option>';
          LINKS.forms.forEach(function(a){h += '<option value="'+a.id+'"'+(String(a.id)===String(pl.id)?' selected':'')+'>'+esc(a.name)+'</option>';});
          h += '</select>';
        }
      } else if(pl.type==='download'){
        h = '<input type="text" data-f="id" value="'+esc(pl.id)+'" placeholder="下载路径，如 uploads/xx.zip">';
        if(LINKS.downloads.length){
          h += '<select data-f="file"><option value="">— 从下载专区选 —</option>';
          LINKS.downloads.forEach(function(a){h += '<option value="'+esc(a.file_url)+'">'+esc(a.title)+'</option>';});
          h += '</select>';
        }
      } else {
        h = '<input type="text" data-f="id" value="'+esc(pl.id)+'" placeholder="https://example.com">';
      }
      pickBox.innerHTML = h;
      bindPick();
    }

    function commitText(){
      var t = pop.querySelector('input[data-f="text"]').value;
      if(textKey) post({ve:'update', key:m.slot, field:textKey, value:t});
    }
    function commitUrl(){
      if(urlKey) post({ve:'update', key:m.slot, field:urlKey, value:urlVal});
    }
    function bindPick(){
      var idEl = pickBox.querySelector('[data-f="id"]');
      var fileEl = pickBox.querySelector('[data-f="file"]');
      var ev = idEl && idEl.tagName==='SELECT' ? 'change' : 'input';
      if(idEl) idEl.addEventListener(ev, function(){urlVal = linkToUrl(typeSel.value, idEl.value); commitUrl();});
      if(fileEl) fileEl.addEventListener('change', function(){if(fileEl.value){urlVal = fileEl.value;commitUrl();refreshPick();}});
    }

    var textInp = pop.querySelector('input[data-f="text"]');
    textInp.addEventListener('input', commitText);
    if(typeSel) typeSel.addEventListener('change', function(){urlVal = linkToUrl(typeSel.value, ''); refreshPick(); commitUrl();});
    pop.querySelector('.ve-pop__close').addEventListener('click', closePop);

    refreshPick();
    return pop;
  }

  FIELD_MAP.forEach(function(m){
    if(m.type!=='btn' && m.type!=='btnText') return;
    var slot = document.querySelector('.ve-slot[data-ve="'+m.slot+'"]');
    if(!slot) return;
    var btn = slot.querySelector(m.sel);
    if(!btn) return;
    btn.setAttribute('data-ve-btn', m.field);
    btn.addEventListener('click', function(ev){
      ev.preventDefault(); ev.stopPropagation();
      closePop();
      curPop = buildBtnPop(m, btn);
      document.body.appendChild(curPop);
      var r = btn.getBoundingClientRect();
      var popR = curPop.getBoundingClientRect();
      var top = window.scrollY + r.bottom + 8;
      var left = window.scrollX + r.left;
      if(left + popR.width > window.scrollX + window.innerWidth - 8) left = window.scrollX + window.innerWidth - popR.width - 8;
      if(top + popR.height > window.scrollY + window.innerHeight - 8) top = window.scrollY + r.top - popR.height - 8;
      curPop.style.top = top + 'px';
      curPop.style.left = left + 'px';
      setTimeout(function(){var i=curPop && curPop.querySelector('input[data-f="text"]'); if(i) i.focus();}, 50);
    });
  });

  document.addEventListener('click', function(ev){
    if(curPop && !curPop.contains(ev.target) && !ev.target.closest('[data-ve-btn]')) closePop();
  });
  document.addEventListener('keydown', function(ev){if(ev.key==='Escape') closePop();});

  /* 父窗口发来"滚动到指定组件"消息（点左侧组件 / 切 PC/手机 时触发） */
  window.addEventListener('message', function(ev){
    var d = ev.data;
    if(!d || d.ve !== 'scroll-to' || !d.key) return;
    var slot = document.querySelector('.ve-slot[data-ve="' + d.key + '"]');
    if(!slot) return;
    var rect = slot.getBoundingClientRect();
    var headerSpace = 80;
    var targetTop = window.scrollY + rect.top - headerSpace;
    if(targetTop < 0) targetTop = 0;
    window.scrollTo({top: targetTop, behavior:'smooth'});
  });
})();
</script>
JS;
        $html = str_replace('</body>', $veJs . "\n</body>", $html);
        echo $html;
        exit;
    }
    render_tpl('home.php', $view);
    exit;
}

/* ---------- 列表页 ---------- */
if ($act === 'list') {
    $catId = (int)($_GET['cat'] ?? 0);
    $cat   = DB::one('SELECT * FROM categories WHERE id=? AND site_id=?', [$catId, $sid]);
    $type  = $cat['type'] ?? 'article';
    $table = $type === 'product' ? 'products' : 'articles';
    $per   = 8;
    $pg    = paginate(
        "SELECT COUNT(*) AS n FROM $table WHERE status=1 AND cat_id=? AND site_id=?",
        "SELECT * FROM $table WHERE status=1 AND cat_id=? AND site_id=? ORDER BY id DESC",
        [$catId, $sid], $per
    );
    // 热门推荐（同栏目浏览量 Top5）
    $hot = DB::all("SELECT * FROM $table WHERE status=1 AND cat_id=? AND site_id=? ORDER BY views DESC, id DESC LIMIT 5", [$catId, $sid]);
    if (!$hot) {
        $hot = DB::all("SELECT * FROM $table WHERE status=1 AND site_id=? ORDER BY views DESC, id DESC LIMIT 5", [$sid]);
    }
    $title = city_label() . ($cat['name'] ?? '列表') . $suffix;
    render_tpl('list.php', [
        'title' => $title, 'nav' => $navAll, 'settings' => settings_all(), 'city' => $city,
        'cat' => $cat, 'list' => $pg['list'], 'pg' => $pg, 'type' => $type, 'site' => $siteTitle,
        'kw' => !empty($cat['seo_keywords']) ? $cat['seo_keywords'] : $keywords,
        'desc' => !empty($cat['seo_description']) ? $cat['seo_description'] : $descAll,
        'hot' => $hot, 'sideNav' => $navFlat, 'newsFoot' => $newsFoot,
        'aboutUrl' => $aboutUrl, 'contactUrl' => $contactUrl, 'cityLabel' => city_label(),
    ]);
    exit;
}

/* ---------- 详情页 ---------- */
if ($act === 'detail') {
    $type  = ($_GET['type'] ?? '') === 'product' ? 'product' : 'article';
    $table = $type === 'product' ? 'products' : 'articles';
    $id    = (int)($_GET['id'] ?? 0);
    $row   = DB::one("SELECT * FROM $table WHERE id=? AND site_id=?", [$id, $sid]);
    if (!$row || (int)$row['status'] !== 1) {
        http_response_code(404);
        render_tpl('404.php', [
            'title' => '内容不存在', 'nav' => $navAll, 'settings' => settings_all(), 'city' => $city,
            'site' => $siteTitle, 'cat' => null, 'kw' => $keywords, 'desc' => $descAll,
            'newsFoot' => $newsFoot,
        ]);
        exit;
    }
    DB::run("UPDATE $table SET views=views+1 WHERE id=?", [$id]);
    $row['views']++;
    $cat  = DB::one('SELECT * FROM categories WHERE id=? AND site_id=?', [$row['cat_id'], $sid]);
    $rel  = DB::all("SELECT * FROM $table WHERE status=1 AND cat_id=? AND site_id=? AND id<>? ORDER BY id DESC LIMIT 3", [$row['cat_id'], $sid, $id]);
    $tags = array_values(array_filter(array_map('trim', explode(',', (string)($row['tags'] ?? '')))));
    $title = city_label() . ($row['seo_title'] ?: $row['title']) . $suffix;

    /* GEO 结构化数据（JSON-LD）：文章→FAQPage，产品→Product，均含 Article/BreadcrumbList 基础信号 */
    $jsonLd = [];
    $pageUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? '') . $_SERVER['REQUEST_URI'];
    $orgName = setting('site_name', '得应盯网络科技');
    if ($type === 'product') {
        $ld = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $row['title'],
            'description' => $row['geo_summary'] ?: ($row['summary'] ?: strip_tags(mb_substr($row['content'], 0, 120))),
        ];
        if (!empty($row['cover'])) {
            $ld['image'] = $row['cover'];
        }
        if (!empty($row['price']) && $row['price'] !== '面议') {
            $ld['offers'] = ['@type' => 'Offer', 'priceCurrency' => 'CNY', 'price' => preg_replace('/[^\d.]/', '', $row['price']), 'availability' => 'https://schema.org/InStock'];
        }
        if (!empty($row['geo_faq'])) {
            $faq = @json_decode($row['geo_faq'], true);
            if (is_array($faq) && count($faq)) {
                $ld['subjectOf'] = [
                    '@type' => 'FAQPage',
                    'mainEntity' => array_map(function ($f) {
                        return ['@type' => 'Question', 'name' => $f['q'] ?? '', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a'] ?? '']];
                    }, $faq),
                ];
            }
        }
        $jsonLd[] = $ld;
    } else {
        $ld = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $row['title'],
            'articleBody' => strip_tags($row['content']),
            'datePublished' => $row['created_at'] ?? date('Y-m-d'),
            'publisher' => ['@type' => 'Organization', 'name' => $orgName],
        ];
        if (!empty($row['cover'])) {
            $ld['image'] = $row['cover'];
        }
        if (!empty($row['geo_faq'])) {
            $faq = @json_decode($row['geo_faq'], true);
            if (is_array($faq) && count($faq)) {
                $ld['@type'] = ['Article', 'FAQPage'];
                $ld['mainEntity'] = array_map(function ($f) {
                    return ['@type' => 'Question', 'name' => $f['q'] ?? '', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a'] ?? '']];
                }, $faq);
            }
        }
        $jsonLd[] = $ld;
    }
    $jsonLdScript = implode("\n", array_map(function ($o) {
        return '<script type="application/ld+json">' . json_encode($o, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>';
    }, $jsonLd));

    render_tpl('detail.php', [
        'title' => $title, 'nav' => $navAll, 'settings' => settings_all(), 'city' => $city,
        'row' => $row, 'cat' => $cat, 'type' => $type, 'site' => $siteTitle,
        'kw' => !empty($row['seo_keywords']) ? $row['seo_keywords'] : (!empty($cat['seo_keywords']) ? $cat['seo_keywords'] : $keywords),
        'desc' => !empty($row['seo_description']) ? $row['seo_description'] : (!empty($cat['seo_description']) ? $cat['seo_description'] : $descAll),
        'rel' => $rel, 'tags' => $tags, 'newsFoot' => $newsFoot,
        'aboutUrl' => $aboutUrl, 'contactUrl' => $contactUrl, 'cityLabel' => city_label(),
        'jsonLdScript' => $jsonLdScript,
    ]);
    exit;
}

/* ---------- 城市分站 ---------- */
if ($act === 'city') {
    $title = $siteTitle . ' - 城市分站' . ($city ? ' · ' . $city['city'] : '');
    render_tpl('city.php', [
        'title' => $title, 'nav' => $navAll, 'settings' => settings_all(), 'city' => $city,
        'cities' => DB::all('SELECT * FROM city_sites WHERE status=1 AND site_id=? ORDER BY sort ASC, id ASC', [$sid]),
        'site' => $siteTitle, 'cat' => null, 'kw' => $keywords, 'desc' => $descAll,
        'newsFoot' => $newsFoot, 'aboutUrl' => $aboutUrl, 'contactUrl' => $contactUrl,
    ]);
    exit;
}

/* ---------- 自定义表单（前台提交页） ---------- */
if ($act === 'form') {
    $fid = (int)($_GET['id'] ?? 0);
    /* site_id=0 视为「全局共享表单」，任何站点（含租户/分站视图）均可访问，避免前台 404 */
    $def = DB::one('SELECT * FROM form_defs WHERE id=? AND (site_id=? OR site_id=0) AND status=1', [$fid, $sid]);
    if (!$def) {
        http_response_code(404);
        render_tpl('404.php', [
            'title' => '表单不存在', 'nav' => $navAll, 'settings' => settings_all(), 'city' => $city,
            'site' => $siteTitle, 'cat' => null, 'kw' => $keywords, 'desc' => $descAll,
            'newsFoot' => $newsFoot,
        ]);
        exit;
    }
    $fields = form_fields($def);
    $okMsg  = '';
    $errMsg = '';
    if (($_POST['submit'] ?? '') === '1') {
        $data = [];
        $errs = [];
        foreach ($fields as $f) {
            $nameF = $f['name'];
            $val   = $_POST[$nameF] ?? '';
            if (is_array($val)) {
                $val = array_values(array_filter($val));
            }
            if (!empty($f['required'])) {
                $empty = is_array($val) ? !count($val) : trim((string)$val) === '';
                if ($empty) {
                    $errs[] = '请填写：' . $f['label'];
                }
            }
            $data[$nameF] = is_array($val) ? implode('、', $val) : trim((string)$val);
        }
        if (!$errs) {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
            $region = ip_to_region($ip);
            DB::insert('INSERT INTO form_data(site_id,form_id,data,ip,province,city) VALUES(?,?,?,?,?,?)', [$sid, $fid, json_encode($data, JSON_UNESCAPED_UNICODE), $ip, $region['province'], $region['city']]);
            $okMsg = '提交成功！我们会尽快与您联系。';
        } else {
            $errMsg = implode('；', $errs);
        }
    }
    $title = ($def['title'] ?: $def['name']) . $suffix;
    render_tpl('form.php', [
        'title' => $title, 'nav' => $navAll, 'settings' => settings_all(), 'city' => $city,
        'site' => $siteTitle, 'cat' => null, 'kw' => $keywords, 'desc' => $descAll,
        'newsFoot' => $newsFoot, 'def' => $def, 'fields' => $fields,
        'formHtml' => form_render_html($def), 'okMsg' => $okMsg, 'errMsg' => $errMsg,
    ]);
    exit;
}

/* ---------- 下载专区（可分类 · 可下载源码） ---------- */
if ($act === 'download') {
    $title = '下载专区' . $suffix;
    $cats  = DB::all('SELECT * FROM categories WHERE site_id=? AND type=? AND status=1 ORDER BY sort ASC, id ASC', [$sid, 'download']);
    $cid   = (int)($_GET['cat'] ?? 0);
    $where = 'd.site_id=? AND d.status=1';
    $params = [$sid];
    if ($cid > 0) {
        $where  .= ' AND d.cat_id=?';
        $params[] = $cid;
    }
    $items = DB::all("SELECT d.*, c.name AS cat_name FROM downloads d LEFT JOIN categories c ON c.id=d.cat_id WHERE $where ORDER BY d.sort ASC, d.id DESC", $params);
    render_tpl('download.php', [
        'title' => $title, 'nav' => $navAll, 'settings' => settings_all(), 'city' => $city,
        'cats' => $cats, 'curCat' => $cid, 'items' => $items, 'site' => $siteTitle,
        'desc' => setting('download_desc', ''), 'kw' => $keywords, 'descAll' => $descAll,
        'aboutUrl' => $aboutUrl, 'contactUrl' => $contactUrl, 'newsFoot' => $newsFoot,
    ]);
    exit;
}

/* ---------- 下载触发（计数 + 本地输出 / 外链跳转） ---------- */
if ($act === 'dl') {
    $id  = (int)($_GET['id'] ?? 0);
    $row = DB::one('SELECT * FROM downloads WHERE id=? AND site_id=? AND status=1', [$id, $sid]);
    if (!$row || empty($row['file_url'])) {
        http_response_code(404);
        echo '文件不存在或已下架';
        exit;
    }
    $url = $row['file_url'];
    DB::run('UPDATE downloads SET downloads=downloads+1 WHERE id=? AND site_id=?', [$id, $sid]);

    /* 兼容多种 file_url 写法：相对 uploads/、绝对 /uploads/、完整 http(s) URL */
    $isLocal = false;
    $rel     = '';
    $scheme  = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http');
    $baseHost = $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? '');
    $upPrefix = rtrim(UPLOAD_URL, '/') . '/';

    if (preg_match('#^https?://#i', $url)) {
        if (stripos($url, $baseHost . '/' . $upPrefix) === 0) {
            $isLocal = true;
            $rel = ltrim(substr($url, strlen($baseHost . '/' . $upPrefix)), '/');
        } elseif (stripos($url, $baseHost . '/uploads/') === 0) {
            $isLocal = true;
            $rel = ltrim(substr($url, strlen($baseHost . '/uploads/')), '/');
        }
    } elseif (preg_match('#^/uploads/#i', $url)) {
        $isLocal = true;
        $rel = ltrim(substr($url, strlen('/uploads/')), '/');
    } elseif (preg_match('#^' . preg_quote($upPrefix, '#') . '#i', $url)) {
        $isLocal = true;
        $rel = ltrim(substr($url, strlen($upPrefix)), '/');
    } elseif (preg_match('#^uploads/#i', $url)) {
        $isLocal = true;
        $rel = ltrim(substr($url, strlen('uploads/')), '/');
    }

    if ($isLocal && $rel !== '') {
        // 路径穿越防护：解析真实路径并限定必须位于 UPLOAD_DIR 之内，禁止 ../ 读取 config.php 等
        $base = realpath(UPLOAD_DIR);
        $file = realpath(UPLOAD_DIR . $rel);
        if ($file === false || $base === false || strncmp($file, $base . DIRECTORY_SEPARATOR, strlen($base . DIRECTORY_SEPARATOR)) !== 0) {
            http_response_code(403);
            exit('禁止访问');
        }
        if (is_file($file)) {
            $name = $row['file_name'] ?: basename($file);
            $ext  = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if ($ext !== '' && strpos(strtolower($name), '.' . $ext) === false) {
                $name .= '.' . $ext;
            }
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . rawurlencode($name) . '"');
            header('Content-Length: ' . filesize($file));
            header('Cache-Control: no-cache');
            readfile($file);
            exit;
        }
        // 本地记录存在但文件未找到时，回退到可访问的绝对 URL，避免 404
        $url = $baseHost . '/uploads/' . $rel;
    }

    // 外部链接或绝对 URL：直接跳转
    header('Location: ' . $url);
    exit;
}

header('Location: index.php');
exit;

/* ---------- 前台模板渲染 ---------- */
function render_tpl(string $tpl, array $data): void
{
    global $dlCats;
    if (!array_key_exists('dlCats', $data)) {
        $data['dlCats'] = $dlCats ?? [];
    }
    extract($data, EXTR_SKIP);
    require __DIR__ . '/tpl/layout.php';
}
