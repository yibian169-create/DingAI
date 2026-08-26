/* ============================================================
   imgpick.js — 图片空间选择器（产品/文章封面图一键插入）
   复用后台 pic_lib 接口（admin.php?m=pic_lib），无需重复上传
   用法：给按钮加 data-imgpick data-target="输入框id" data-preview="预览img id"
   ============================================================ */
(function () {
  'use strict';

  var styleInjected = false;
  function injectStyle() {
    if (styleInjected) return;
    styleInjected = true;
    var css =
      '.imgpick-row{display:flex;gap:10px;align-items:stretch}' +
      '.imgpick-row input{flex:1;min-width:0}' +
      '.imgpick-preview{max-width:160px;max-height:120px;border-radius:10px;border:1px solid #e2e8f0;margin-top:10px;object-fit:cover;display:block}' +
      '.ip-modal{position:fixed;inset:0;z-index:99999;display:none;align-items:center;justify-content:center;background:rgba(15,23,42,.5)}' +
      '.ip-modal.show{display:flex}' +
      '.ip-box{width:min(880px,94%);max-height:86vh;background:#fff;border-radius:16px;overflow:hidden;display:flex;flex-direction:column;box-shadow:0 30px 80px rgba(2,6,18,.4)}' +
      '.ip-bar{display:flex;align-items:center;gap:12px;padding:16px 20px;border-bottom:1px solid #eef1f6}' +
      '.ip-bar b{font-size:16px;color:#0f172a}' +
      '.ip-bar .ip-close{margin-left:auto;border:none;background:#f1f5f9;width:34px;height:34px;border-radius:9px;cursor:pointer;color:#475569;font-size:16px}' +
      '.ip-bar .ip-close:hover{background:#e2e8f0}' +
      '.ip-body{padding:18px 20px;overflow-y:auto}' +
      '.ip-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:12px}' +
      '.ip-item{position:relative;border:1px solid #eef1f6;border-radius:12px;overflow:hidden;background:#f8fafc;cursor:pointer;padding:6px;transition:all .18s}' +
      '.ip-item:hover{border-color:#4f46e5;transform:translateY(-2px)}' +
      '.ip-item.is-sel{border-color:#4f46e5;box-shadow:0 0 0 3px rgba(79,70,229,.2)}' +
      '.ip-item img{width:100%;height:90px;object-fit:cover;border-radius:8px;display:block}' +
      '.ip-item span{display:block;font-size:11px;color:#64748b;margin-top:5px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}' +
      '.ip-pages{display:flex;gap:6px;flex-wrap:wrap;justify-content:center;padding:14px 0 2px}' +
      '.ip-pages button{min-width:32px;height:32px;border:1px solid #e2e8f0;background:#fff;border-radius:8px;cursor:pointer;color:#475569}' +
      '.ip-pages button.is-active{background:#4f46e5;border-color:#4f46e5;color:#fff}' +
      '.ip-empty,.ip-loading{padding:40px;text-align:center;color:#94a3b8;font-size:14px}';
    var s = document.createElement('style');
    s.textContent = css;
    document.head.appendChild(s);
  }

  var modal, curTarget = '', curPreview = '';

  function buildModal() {
    if (modal) return;
    injectStyle();
    modal = document.createElement('div');
    modal.className = 'ip-modal';
    modal.innerHTML =
      '<div class="ip-box">' +
        '<div class="ip-bar"><b>从图片空间选择</b><button type="button" class="ip-close" title="关闭">✕</button></div>' +
        '<div class="ip-body"><div class="ip-grid"></div><div class="ip-pages"></div>' +
        '<p class="ip-empty" hidden>图片空间暂无图片，请先到「图片空间」上传</p></div>' +
      '</div>';
    document.body.appendChild(modal);
    modal.querySelector('.ip-close').addEventListener('click', close);
    modal.addEventListener('click', function (e) { if (e.target === modal) close(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });
  }

  function loadLib(page) {
    var grid = modal.querySelector('.ip-grid');
    var pagesBox = modal.querySelector('.ip-pages');
    var empty = modal.querySelector('.ip-empty');
    grid.innerHTML = '<p class="ip-loading">加载中…</p>';
    fetch('admin.php?m=pic_lib&page=' + page)
      .then(function (r) { return r.json(); })
      .then(function (d) {
        grid.innerHTML = '';
        if (!d.list || !d.list.length) { empty.hidden = false; pagesBox.innerHTML = ''; return; }
        empty.hidden = true;
        d.list.forEach(function (img) {
          var it = document.createElement('button');
          it.type = 'button';
          it.className = 'ip-item';
          it.setAttribute('data-url', img.url);
          it.innerHTML = '<img src="' + img.url + '" alt="" loading="lazy">';
          var _nm = document.createElement('span');
          _nm.textContent = img.name;
          it.appendChild(_nm);
          it.addEventListener('click', function () {
            grid.querySelectorAll('.ip-item').forEach(function (x) { x.classList.remove('is-sel'); });
            it.classList.add('is-sel');
            var inp = document.getElementById(curTarget);
            if (inp) inp.value = img.url;
            var pv = curPreview ? document.getElementById(curPreview) : null;
            if (pv) { pv.src = img.url; pv.hidden = false; }
            setTimeout(close, 220);
          });
          grid.appendChild(it);
        });
        pagesBox.innerHTML = '';
        for (var i = 1; i <= (d.pages || 1); i++) {
          (function (p) {
            var b = document.createElement('button');
            b.type = 'button';
            b.textContent = p;
            if (p === d.page) b.className = 'is-active';
            b.addEventListener('click', function () { loadLib(p); });
            pagesBox.appendChild(b);
          })(i);
        }
      })
      .catch(function () {
        grid.innerHTML = '<p class="ip-empty">加载失败，请刷新后重试</p>';
      });
  }

  function open(target, preview) {
    buildModal();
    curTarget = target;
    curPreview = preview || '';
    modal.classList.add('show');
    loadLib(1);
  }
  function close() { if (modal) modal.classList.remove('show'); }

  window.ImgPick = {
    init: function () {
      document.querySelectorAll('[data-imgpick]').forEach(function (btn) {
        if (btn.__ipBound) return;
        btn.__ipBound = true;
        btn.addEventListener('click', function () {
          open(btn.getAttribute('data-target'), btn.getAttribute('data-preview'));
        });
      });
    }
  };

  if (document.readyState !== 'loading') window.ImgPick.init();
  else document.addEventListener('DOMContentLoaded', function () { window.ImgPick.init(); });
})();
