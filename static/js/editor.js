/* ============================================================
   deyingding 后台专业富文本编辑器（零依赖 / 多行工具栏 / SVG 图标）
   用法：QEditor.init('f_content', { height: 420 });
   表单提交前自动同步内容到隐藏 textarea。
   ============================================================ */
(function (global) {
  "use strict";

  /* ---- SVG 图标库（细线风格） ---- */
  var ICON = {
    bold:    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M7 5h6a3.5 3.5 0 0 1 0 7H7zM7 12h7a3.5 3.5 0 0 1 0 7H7zM7 5v14"/></svg>',
    italic:  '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="4" x2="10" y2="4"/><line x1="14" y1="20" x2="5" y2="20"/><line x1="15" y1="4" x2="9" y2="20"/></svg>',
    under:   '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 4v6a6 6 0 0 0 12 0V4"/><line x1="4" y1="20" x2="20" y2="20"/></svg>',
    strike:  '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4H9a3 3 0 0 0-2.83 4M14 12a4 4 0 0 1 0 8H6M4 12h16"/></svg>',
    color:   '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><text x="12" y="15" text-anchor="middle" font-size="13" font-weight="800" fill="currentColor" stroke="none" font-family="-apple-system,sans-serif">A</text><line x1="5" y1="20" x2="19" y2="20"/></svg>',
    bg:      '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="6" width="18" height="12" rx="1" fill="rgba(129,140,248,.25)"/><text x="12" y="15" text-anchor="middle" font-size="12" font-weight="800" fill="currentColor" stroke="none" font-family="-apple-system,sans-serif">A</text></svg>',
    alignL:  '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="15" y2="12"/><line x1="3" y1="18" x2="18" y2="18"/></svg>',
    alignC:  '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="6" y1="12" x2="18" y2="12"/><line x1="4" y1="18" x2="20" y2="18"/></svg>',
    alignR:  '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="9" y1="12" x2="21" y2="12"/><line x1="6" y1="18" x2="21" y2="18"/></svg>',
    alignJ:  '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>',
    ulist:   '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="9" y1="6" x2="20" y2="6"/><line x1="9" y1="12" x2="20" y2="12"/><line x1="9" y1="18" x2="20" y2="18"/><circle cx="4.5" cy="6" r="1.2" fill="currentColor"/><circle cx="4.5" cy="12" r="1.2" fill="currentColor"/><circle cx="4.5" cy="18" r="1.2" fill="currentColor"/></svg>',
    olist:   '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="9" y1="6" x2="20" y2="6"/><line x1="9" y1="12" x2="20" y2="12"/><line x1="9" y1="18" x2="20" y2="18"/><path d="M4 4h1v4M4 12h1v3M4 19h2v-2a1 1 0 0 0-1-1" fill="none"/></svg>',
    indentL: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 8 7 12 3 16"/><line x1="10" y1="4" x2="21" y2="4"/><line x1="10" y1="12" x2="21" y2="12"/><line x1="10" y1="20" x2="21" y2="20"/></svg>',
    indentR: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="21 8 17 12 21 16"/><line x1="3" y1="4" x2="14" y2="4"/><line x1="3" y1="12" x2="14" y2="12"/><line x1="3" y1="20" x2="14" y2="20"/></svg>',
    quote:   '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21c3 0 7-1 7-8V5H3v8h4c0 4-2 6-4 6zM14 21c3 0 7-1 7-8V5h-7v8h4c0 4-2 6-4 6z"/></svg>',
    code:    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>',
    hr:      '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="3" y1="12" x2="21" y2="12"/></svg>',
    link:    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.07 0l3.54-3.54a5 5 0 0 0-7.07-7.07L11.9 4.93"/><path d="M14 11a5 5 0 0 0-7.07 0L3.4 14.54a5 5 0 1 0 7.07 7.07l1.65-1.65"/></svg>',
    image:   '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>',
    video:   '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2"/></svg>',
    table:   '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="3" y1="15" x2="21" y2="15"/><line x1="9" y1="3" x2="9" y2="21"/><line x1="15" y1="3" x2="15" y2="21"/></svg>',
    emoji:   '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg>',
    clear:   '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>',
    undo:    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7v6h6"/><path d="M21 17a9 9 0 0 0-15-6.7L3 13"/></svg>',
    redo:    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 7v6h-6"/><path d="M3 17a9 9 0 0 1 15-6.7L21 13"/></svg>',
    source:  '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>',
    full:    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="4 9 4 4 9 4"/><polyline points="15 4 20 4 20 9"/><polyline points="20 15 20 20 15 20"/><polyline points="9 20 4 20 4 15"/></svg>',
  };

  var EMOJIS = ['😀','😁','😂','🤣','😊','😍','😘','😎','🤩','🥳','🤔','😏','😅','🤣','😭','😡','🤬','🤯','🥺','😴','🤮','🤧','🤒','🤕','👍','👎','👏','🙌','🤝','🙏','✌️','🤞','💪','🔥','⭐','✨','🎉','🎊','❤️','🧡','💛','💚','💙','💜','🖤','🤍','⚡','💯','🏆','🚀','🍎','🍕','🍔','🌹','🌻'];

  /* ---- 工具栏配置（多行） ---- */
  var ROW1 = [
    { type: 'select', name: 'formatBlock', width: 110,
      options: [
        { v: 'p',         t: '正文' },
        { v: 'h1',        t: '标题 1' },
        { v: 'h2',        t: '标题 2' },
        { v: 'h3',        t: '标题 3' },
        { v: 'h4',        t: '标题 4' },
        { v: 'blockquote',t: '引用' },
        { v: 'pre',       t: '代码块' },
      ]
    },
    { type: 'select', name: 'fontName', width: 120,
      options: [
        { v: '',           t: '默认字体' },
        { v: 'PingFang SC', t: '苹方' },
        { v: 'Microsoft YaHei', t: '微软雅黑' },
        { v: 'SimSun',     t: '宋体' },
        { v: 'SimHei',     t: '黑体' },
        { v: 'KaiTi',      t: '楷体' },
        { v: 'Arial',      t: 'Arial' },
        { v: 'Georgia',    t: 'Georgia' },
        { v: 'Consolas',   t: 'Consolas' },
      ]
    },
    { type: 'color', name: 'foreColor',   icon: 'color', title: '字体颜色', label: '前景' },
    { type: 'color', name: 'hiliteColor', icon: 'bg',    title: '背景颜色', label: '背景', extraClass: 'qe-bg' },
    { sep: 1 },
    { type: 'btn',    cmd: 'bold',       icon: 'bold' },
    { type: 'btn',    cmd: 'italic',     icon: 'italic' },
    { type: 'btn',    cmd: 'underline',  icon: 'under' },
    { type: 'btn',    cmd: 'strikeThrough', icon: 'strike' },
    { sep: 1 },
    { type: 'btn',    cmd: 'justifyLeft',   icon: 'alignL' },
    { type: 'btn',    cmd: 'justifyCenter', icon: 'alignC' },
    { type: 'btn',    cmd: 'justifyRight',  icon: 'alignR' },
    { type: 'btn',    cmd: 'justifyFull',   icon: 'alignJ' },
    { sep: 1 },
    { type: 'btn',    cmd: 'insertUnorderedList', icon: 'ulist' },
    { type: 'btn',    cmd: 'insertOrderedList',   icon: 'olist' },
    { type: 'btn',    cmd: 'outdent', icon: 'indentL' },
    { type: 'btn',    cmd: 'indent',  icon: 'indentR' },
    { sep: 1 },
    { type: 'btn',    cmd: 'removeFormat', icon: 'clear', title: '清除格式' },
  ];

  var ROW2 = [
    { type: 'btn', fn: 'link',      icon: 'link',  title: '插入链接' },
    { type: 'btn', fn: 'unlink',    icon: 'link',  title: '取消链接', cmd: 'unlink' },
    { type: 'btn', fn: 'image',     icon: 'image', title: '插入图片（从图片空间复制 URL）' },
    { type: 'btn', fn: 'video',     icon: 'video', title: '插入视频（iframe/embed）' },
    { type: 'btn', fn: 'table',     icon: 'table', title: '插入表格' },
    { type: 'btn', fn: 'hr',        icon: 'hr',    title: '水平线' },
    { type: 'btn', fn: 'code',      icon: 'code',  title: '行内代码块' },
    { type: 'btn', fn: 'quote',     icon: 'quote', title: '引用块', cmd: 'formatBlock', val: 'blockquote' },
    { sep: 1 },
    { type: 'btn', fn: 'emoji',     icon: 'emoji', title: '表情' },
    { sep: 1 },
    { type: 'btn', cmd: 'undo',      icon: 'undo' },
    { type: 'btn', cmd: 'redo',      icon: 'redo' },
    { sep: 1 },
    { type: 'btn', fn: 'source',    icon: 'source', title: '源码模式' },
    { type: 'btn', fn: 'fullscreen',icon: 'full',   title: '全屏编辑' },
    { type: 'label', cls: 'qe-spacer' },
    { type: 'label', cls: 'qe-count', label: '字数：', id: 'qeCount' },
  ];

  function buildRow(row) {
    var html = '';
    row.forEach(function (b) {
      if (b.sep) { html += '<span class="qe-sep"></span>'; return; }
      if (b.type === 'label') {
        html += '<span class="' + b.cls + '" id="' + (b.id || '') + '">' + (b.label || '') + '<b>0</b></span>';
        return;
      }
      if (b.type === 'select') {
        var opts = b.options.map(function (o) { return '<option value="' + o.v + '">' + o.t + '</option>'; }).join('');
        html += '<select class="qe-sel" data-cmd="' + b.name + '" style="width:' + b.width + 'px">' + opts + '</select>';
        return;
      }
      if (b.type === 'color') {
        html += '<label class="qe-btn qe-color" title="' + (b.title || b.name) + '">' +
                (b.icon ? ICON[b.icon] : '') +
                (b.label ? '<span class="qe-btn__lbl">' + b.label + '</span>' : '') +
                '<input type="color" data-cmd="' + b.name + '" value="' + (b.default || '#222222') + '"></label>';
        return;
      }
      // 按钮
      var attrs = 'data-cmd="' + (b.cmd || '') + '"' +
                  (b.fn ? ' data-fn="' + b.fn + '"' : '') +
                  (b.val ? ' data-val="' + b.val + '"' : '') +
                  ' title="' + (b.title || '') + '"';
      html += '<button type="button" class="qe-btn" ' + attrs + '>' + ICON[b.icon] + '</button>';
    });
    return html;
  }

  function init(textareaId, opts) {
    var ta = document.getElementById(textareaId);
    if (!ta) return;
    opts = opts || {};

    document.execCommand('styleWithCSS', false, true);

    var wrap = document.createElement('div');
    wrap.className = 'qe-wrap';
    ta.parentNode.insertBefore(wrap, ta);
    ta.style.display = 'none';
    ta.classList.add('qe-source');
    wrap.appendChild(ta);

    wrap.innerHTML += '<div class="qe-toolbar qe-row1">' + buildRow(ROW1) + '</div>' +
                      '<div class="qe-toolbar qe-row2">' + buildRow(ROW2) + '</div>' +
                      '<div class="qe-editor" contenteditable="true" spellcheck="false" style="min-height:' +
                      (opts.height || 420) + 'px">' + (ta.value || '') + '</div>' +
                      '<div class="qe-emoji-pop" hidden></div>';

    var ed = wrap.querySelector('.qe-editor');

    /* 同步：表单提交时 */
    function sync() { ta.value = ed.innerHTML; updateCount(); }

    /* 字号统计 */
    function updateCount() {
      var t = (ed.innerText || '').replace(/\s+/g, '').length;
      var c = wrap.querySelector('#qeCount b');
      if (c) c.textContent = t;
    }
    updateCount();

    /* 通用：exec + 同步 */
    function run(cmd, val) {
      ed.focus();
      document.execCommand(cmd, false, val || null);
      sync();
    }

    /* 按钮点击（包含 cmd / fn） */
    wrap.addEventListener('click', function (e) {
      var btn = e.target.closest('.qe-btn');
      if (!btn || btn.querySelector('input[type=color]')) {
        // 颜色 input 是子元素，原生 change 触发
        if (e.target.matches('input[type=color]')) {
          run(e.target.getAttribute('data-cmd'), e.target.value);
          e.stopPropagation();
        }
        return;
      }
      e.preventDefault();
      ed.focus();
      var fn = btn.getAttribute('data-fn');
      var cmd = btn.getAttribute('data-cmd');
      var val = btn.getAttribute('data-val');
      if (fn === 'link')      return doLink();
      if (fn === 'unlink')    return run('unlink');
      if (fn === 'image')     return doImage();
      if (fn === 'video')     return doVideo();
      if (fn === 'table')     return doTable();
      if (fn === 'hr')        return run('insertHorizontalRule');
      if (fn === 'code')      return doCode();
      if (fn === 'quote')     return run('formatBlock', val || 'blockquote');
      if (fn === 'emoji')     return doEmoji();
      if (fn === 'source')    return toggleSource();
      if (fn === 'fullscreen')return toggleFs();
      if (cmd) run(cmd, val);
    });

    /* select 变化 */
    wrap.addEventListener('change', function (e) {
      if (e.target.matches('.qe-sel')) {
        run(e.target.getAttribute('data-cmd'), e.target.value);
        e.target.selectedIndex = 0; // 还原
      }
    });

    /* 编辑时实时更新字数 */
    ed.addEventListener('input', updateCount);

    /* ---- 各类插入 ---- */
    function doLink() {
      var url = prompt('链接地址：', 'https://');
      if (!url) return;
      document.execCommand('createLink', false, url);
      sync();
    }
    function doImage() {
      // 弹窗：图片空间选图 / URL 输入 双模式
      var pop = wrap.querySelector('.qe-imgpop');
      if (!pop) {
        pop = document.createElement('div');
        pop.className = 'qe-imgpop';
        pop.innerHTML =
          '<div class="qe-imgpop__box">' +
            '<div class="qe-imgpop__bar"><span>插入图片</span><button type="button" class="qe-imgpop__close" title="关闭">✕</button></div>' +
            '<div class="qe-imgpop__tabs">' +
              '<button type="button" class="qe-imgpop__tab is-active" data-tab="lib">📁 图片空间</button>' +
              '<button type="button" class="qe-imgpop__tab" data-tab="url">🔗 URL 链接</button>' +
            '</div>' +
            '<div class="qe-imgpop__body">' +
              '<div class="qe-imgpop__lib">' +
                '<div class="qe-imgpop__grid"></div>' +
                '<div class="qe-imgpop__pages"></div>' +
                '<p class="qe-imgpop__empty" hidden>图片空间暂无图片，可切换到「URL 链接」粘贴地址</p>' +
              '</div>' +
              '<div class="qe-imgpop__url" hidden>' +
                '<input type="text" placeholder="粘贴图片 URL，如 https://…" />' +
                '<p class="qe-imgpop__tip">也可先在「图片空间」上传图片，再回来选择</p>' +
              '</div>' +
            '</div>' +
            '<div class="qe-imgpop__foot">' +
              '<span class="qe-imgpop__sel">未选择图片</span>' +
              '<button type="button" class="qe-imgpop__insert">插入图片</button>' +
            '</div>' +
          '</div>';
        wrap.appendChild(pop);

        // 关闭
        pop.querySelector('.qe-imgpop__close').addEventListener('click', function () { pop.classList.remove('show'); });
        pop.addEventListener('click', function (e) { if (e.target === pop) pop.classList.remove('show'); });

        // Tab 切换
        pop.querySelectorAll('.qe-imgpop__tab').forEach(function (t) {
          t.addEventListener('click', function () {
            pop.querySelectorAll('.qe-imgpop__tab').forEach(function (x) { x.classList.remove('is-active'); });
            t.classList.add('is-active');
            var mode = t.getAttribute('data-tab');
            pop.querySelector('.qe-imgpop__lib').hidden = mode !== 'lib';
            pop.querySelector('.qe-imgpop__url').hidden = mode !== 'url';
            resetSel();
            if (mode === 'lib') loadLib(1);
          });
        });

        // 插入
        pop.querySelector('.qe-imgpop__insert').addEventListener('click', function () {
          var lib = !pop.querySelector('.qe-imgpop__lib').hidden;
          var sel = pop.querySelector('.qe-imgpop__grid .is-selected');
          var url = '';
          if (lib && sel) url = sel.getAttribute('data-url');
          if (!lib) url = pop.querySelector('.qe-imgpop__url input').value.trim();
          if (!url) { alert('请选择或输入图片地址'); return; }
          run('insertImage', url);
          pop.classList.remove('show');
          resetSel();
        });
      }
      pop.classList.add('show');
      loadLib(1);
    }

    function resetSel() {
      wrap.querySelectorAll('.qe-imgpop__grid .is-selected').forEach(function (x) { x.classList.remove('is-selected'); });
      wrap.querySelector('.qe-imgpop__sel').textContent = '未选择图片';
    }

    function loadLib(page) {
      var pop = wrap.querySelector('.qe-imgpop');
      var grid = pop.querySelector('.qe-imgpop__grid');
      var pagesBox = pop.querySelector('.qe-imgpop__pages');
      var empty = pop.querySelector('.qe-imgpop__empty');
      grid.innerHTML = '<p class="qe-imgpop__loading">加载中…</p>';
      fetch('admin.php?m=pic_lib&page=' + page)
        .then(function (r) { return r.json(); })
        .then(function (d) {
          grid.innerHTML = '';
          if (!d.list || !d.list.length) {
            empty.hidden = false;
            pagesBox.innerHTML = '';
            return;
          }
          empty.hidden = true;
          d.list.forEach(function (img) {
            var it = document.createElement('button');
            it.type = 'button';
            it.className = 'qe-imgpop__item';
            it.setAttribute('data-url', img.url);
            it.innerHTML = '<img src="' + img.url + '" alt="" loading="lazy">';
            var _nm = document.createElement('span');
            _nm.textContent = img.name;
            it.appendChild(_nm);
            it.addEventListener('click', function () {
              resetSel();
              it.classList.add('is-selected');
              wrap.querySelector('.qe-imgpop__sel').textContent = '已选择：' + img.name;
            });
            grid.appendChild(it);
          });
          // 分页
          pagesBox.innerHTML = '';
          for (var i = 1; i <= d.pages; i++) {
            var p = document.createElement('button');
            p.type = 'button';
            p.className = 'qe-imgpop__pg' + (i === d.page ? ' is-active' : '');
            p.textContent = i;
            p.addEventListener('click', function (i) { return function () { loadLib(i); }; }(i));
            pagesBox.appendChild(p);
          }
        })
        .catch(function () {
          grid.innerHTML = '<p class="qe-imgpop__empty">加载失败，请刷新后重试</p>';
        });
    }
    function doVideo() {
      var code = prompt('粘贴视频嵌入代码（iframe / embed / video src）：',
        '<iframe src="https://player.bilibili.com/player.html?bvid=xxx" scrolling="no" frameborder="0" width="640" height="360" allowfullscreen></iframe>');
      if (!code) return;
      run('insertHTML', code);
    }
    function doTable() {
      var r = parseInt(prompt('表格行数：', '3'), 10) || 3;
      var c = parseInt(prompt('表格列数：', '3'), 10) || 3;
      var h = '<table><tbody>';
      for (var i = 0; i < r; i++) {
        h += '<tr>';
        for (var j = 0; j < c; j++) h += '<td>&nbsp;</td>';
        h += '</tr>';
      }
      h += '</tbody></table><p><br></p>';
      run('insertHTML', h);
    }
    function doCode() {
      var code = prompt('粘贴代码内容：', '');
      if (code === null) return;
      run('insertHTML', '<pre><code>' + escapeHtml(code) + '</code></pre>');
    }
    function doEmoji() {
      var pop = wrap.querySelector('.qe-emoji-pop');
      pop.innerHTML = '';
      var grid = document.createElement('div');
      grid.className = 'qe-emoji-grid';
      EMOJIS.forEach(function (em) {
        var b = document.createElement('button');
        b.type = 'button';
        b.textContent = em;
        b.addEventListener('mousedown', function (e) { e.preventDefault(); });
        b.addEventListener('click', function () {
          run('insertText', em);
          pop.hidden = true;
        });
        grid.appendChild(b);
      });
      pop.appendChild(grid);
      pop.hidden = false;
      setTimeout(function () {
        document.addEventListener('click', function close(ev) {
          if (!pop.contains(ev.target)) { pop.hidden = true; document.removeEventListener('click', close); }
        });
      }, 0);
    }

    function toggleSource() {
      var showing = ta.style.display !== 'none';
      if (showing) {
        ta.value = ed.innerHTML;
        ta.style.display = '';
        ed.style.display = 'none';
        wrap.classList.add('qe-source-mode');
      } else {
        ed.innerHTML = ta.value;
        ed.style.display = '';
        ta.style.display = 'none';
        wrap.classList.remove('qe-source-mode');
      }
    }
    function toggleFs() {
      wrap.classList.toggle('qe-fullscreen');
      document.body.style.overflow = wrap.classList.contains('qe-fullscreen') ? 'hidden' : '';
    }

    /* 表单提交前同步 */
    var form = ta.form;
    if (form) form.addEventListener('submit', sync);

    function escapeHtml(s) {
      return s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }
  }

  global.QEditor = {
    init: init,
    set: function (id, html) {
      var ta = document.getElementById(id);
      if (!ta) return;
      ta.value = html || '';
      var wrap = ta.closest('.qe-wrap');
      if (wrap) {
        var ed = wrap.querySelector('.qe-editor');
        if (ed) ed.innerHTML = html || '';
      }
    },
    focus: function (id) {
      var ta = document.getElementById(id);
      if (!ta) return;
      var wrap = ta.closest('.qe-wrap');
      if (wrap) {
        var ed = wrap.querySelector('.qe-editor');
        if (ed) ed.focus();
      }
    },
  };
})(window);