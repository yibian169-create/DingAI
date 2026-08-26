/* ============================================================
   PQNOVA - 深色高级科技风企业网站模板
   static/js/main.js
   ============================================================ */
(function () {
  "use strict";

  /* ---------- 头部滚动状态 ---------- */
  var header = document.getElementById("qHeader");
  function onScroll() {
    if (!header) return;
    if (window.scrollY > 40) header.classList.add("is-scrolled");
    else header.classList.remove("is-scrolled");
  }
  window.addEventListener("scroll", onScroll, { passive: true });
  onScroll();

  /* ---------- 导航下拉子菜单 ----------
     桌面：鼠标悬停展开（弹层 fixed 定位，导航再长也不被撑乱）
     移动端：点击父项展开/收起 */
  var drops = document.querySelectorAll(".q-drop");
  var hoverable = window.matchMedia("(hover: hover) and (pointer: fine)").matches;

  function isMobile() {
    return window.innerWidth <= 860;
  }
  function closeAllDrops() {
    drops.forEach(function (d) { d.classList.remove("open"); });
  }
  function openDrop(item) {
    var menu = item.querySelector(".q-drop__menu");
    if (!menu) return;
    if (!isMobile()) {
      var link = item.querySelector(":scope > .q-nav__link") || item;
      var r = link.getBoundingClientRect();
      var left = r.left + r.width / 2;
      left = Math.max(100, Math.min(left, window.innerWidth - 110));
      menu.style.top = (r.bottom + 6) + "px";
      menu.style.left = left + "px";
      menu.style.position = "fixed";
      menu.style.transform = "translateX(-50%)";
    } else {
      menu.style.position = "";
      menu.style.top = "";
      menu.style.left = "";
      menu.style.transform = "";
    }
    closeAllDrops();
    item.classList.add("open");
  }

  drops.forEach(function (item) {
    var link = item.querySelector(":scope > .q-nav__link");
    if (!link) return;
    // 桌面：悬停展开 / 移出收起
    if (hoverable) {
      item.addEventListener("mouseenter", function () { openDrop(item); });
      item.addEventListener("mouseleave", function () { item.classList.remove("open"); });
    }
    // 移动端：点击切换；桌面点击正常跳转
    link.addEventListener("click", function (e) {
      if (isMobile()) {
        e.preventDefault();
        item.classList.toggle("open");
      }
    });
    link.addEventListener("mouseenter", function () {
      if (hoverable) openDrop(item);
    });
  });
  // 滚动时收起所有下拉，避免弹层悬浮在错误位置
  window.addEventListener("scroll", closeAllDrops, { passive: true });
  window.addEventListener("resize", closeAllDrops, { passive: true });

  /* ---------- 移动端菜单（右侧抽屉） ---------- */
  var burger = document.getElementById("qBurger");
  var nav = document.getElementById("qNav");
  var navBackdrop = document.getElementById("qNavBackdrop");
  function openMobileNav() {
    if (!nav) return;
    burger && burger.classList.add("is-open");
    nav.classList.add("open");
    if (navBackdrop) navBackdrop.classList.add("is-open");
    document.body.style.overflow = "hidden";
  }
  function closeMobileNav() {
    if (!nav) return;
    burger && burger.classList.remove("is-open");
    nav.classList.remove("open");
    if (navBackdrop) navBackdrop.classList.remove("is-open");
    document.body.style.overflow = "";
    closeAllDrops();
  }
  if (burger && nav) {
    burger.addEventListener("click", function () {
      if (nav.classList.contains("open")) closeMobileNav();
      else openMobileNav();
    });
    if (navBackdrop) navBackdrop.addEventListener("click", closeMobileNav);
    // 点击导航链接后收起（下拉父项点击仅展开子菜单，不收抽屉）
    nav.querySelectorAll("a").forEach(function (a) {
      if (a.parentElement.classList.contains("q-drop")) return;
      a.addEventListener("click", function () { closeMobileNav(); });
    });
  }
  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape" && nav && nav.classList.contains("open")) closeMobileNav();
  });
  // 切到桌面时强制关闭抽屉，避免残留
  window.addEventListener("resize", function () {
    if (nav && !isMobile() && nav.classList.contains("open")) closeMobileNav();
  });

  /* ---------- 返回顶部 ---------- */
  var topBtn = document.getElementById("qTop");
  if (topBtn) {
    window.addEventListener("scroll", function () {
      if (window.scrollY > 500) topBtn.classList.add("show");
      else topBtn.classList.remove("show");
    }, { passive: true });
    topBtn.addEventListener("click", function () {
      window.scrollTo({ top: 0, behavior: "smooth" });
    });
  }

  /* ---------- 入场动画 ---------- */
  var revealEls = document.querySelectorAll(".q-reveal");
  if ("IntersectionObserver" in window) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) {
        if (e.isIntersecting) {
          e.target.classList.add("in");
          io.unobserve(e.target);
        }
      });
    }, { threshold: 0.12 });
    revealEls.forEach(function (el) { io.observe(el); });
  } else {
    revealEls.forEach(function (el) { el.classList.add("in"); });
  }

  /* ---------- 数字滚动 ---------- */
  var counters = document.querySelectorAll("[data-count]");
  if ("IntersectionObserver" in window && counters.length) {
    var co = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) {
        if (!e.isIntersecting) return;
        var el = e.target;
        var target = parseFloat(el.getAttribute("data-count"));
        var suffix = el.getAttribute("data-suffix") || "";
        var decimals = (el.getAttribute("data-decimals")) ? parseInt(el.getAttribute("data-decimals"), 10) : 0;
        var dur = 1400, start = null;
        function step(ts) {
          if (!start) start = ts;
          var p = Math.min((ts - start) / dur, 1);
          var eased = 1 - Math.pow(1 - p, 3);
          var val = target * eased;
          el.textContent = val.toFixed(decimals) + suffix;
          if (p < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
        el.dataset.ddHandled = "1";
        co.unobserve(el);
      });
    }, { threshold: 0.4 });
    counters.forEach(function (el) { co.observe(el); });
  }

  /* ---------- 首页仪表盘进度条动画 ---------- */
  var bars = document.querySelectorAll(".q-dash-bar i[data-w]");
  var barObs = "IntersectionObserver" in window
    ? new IntersectionObserver(function (entries) {
        entries.forEach(function (e) {
          if (e.isIntersecting) {
            e.target.style.width = e.target.getAttribute("data-w") + "%";
            barObs.unobserve(e.target);
          }
        });
      }, { threshold: 0.5 })
    : null;
  bars.forEach(function (b) { if (barObs) barObs.observe(b); });

  /* ---------- 圆环进度 ---------- */
  var rings = document.querySelectorAll(".q-mini-ring[data-pct]");
  if (rings.length) {
    var ro = "IntersectionObserver" in window
      ? new IntersectionObserver(function (entries) {
          entries.forEach(function (e) {
            if (!e.isIntersecting) return;
            animateRing(e.target);
            ro.unobserve(e.target);
          });
        }, { threshold: 0.5 })
      : null;
    function animateRing(ring) {
      var pct = parseFloat(ring.getAttribute("data-pct")) || 0;
      var c = ring.querySelector("circle");
      if (!c) return;
      var len = 2 * Math.PI * c.r.baseVal.value;
      c.style.strokeDasharray = len;
      c.style.strokeDashoffset = len;
      // 强制重绘后过渡
      ring.getBoundingClientRect();
      c.style.transition = "stroke-dashoffset 1.4s cubic-bezier(.2,.7,.2,1)";
      c.style.strokeDashoffset = len * (1 - pct / 100);
    }
    rings.forEach(function (r) { if (ro) ro.observe(r); else animateRing(r); });
  }
})();

/* ===================== 白天 / 夜晚模式切换（后台侧边栏 + 登录页通用） ===================== */
(function () {
  function initTheme() {
    var sw = document.getElementById('themeSwitch');
    if (!sw || sw.__themeBound) return;
    sw.__themeBound = true;
    var ic = document.getElementById('tsIcon'), lb = document.getElementById('tsLabel');
    function sync() {
      var t = document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
      if (ic) ic.textContent = t === 'dark' ? '☀️' : '🌙';
      if (lb) lb.textContent = t === 'dark' ? '白天模式' : '夜晚模式';
    }
    sync();
    sw.addEventListener('click', function () {
      var cur = document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
      var next = cur === 'dark' ? 'light' : 'dark';
      document.documentElement.setAttribute('data-theme', next);
      try { localStorage.setItem('dy_theme', next); } catch (e) {}
      sync();
    });
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTheme);
  } else {
    initTheme();
  }
})();

/* === demo ai-home scripts === */
(function () {
  "use strict";
  var reduced = window.matchMedia('(prefers-reduced-motion:reduce)').matches;

  /* 揭示动画（与内置 reveal 互补：已 .in 的跳过，避免重复触发） */
  var revEls = document.querySelectorAll('.q-reveal');
  if ('IntersectionObserver' in window) {
    var rio = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) {
        if (e.isIntersecting && !e.target.classList.contains('in')) {
          e.target.classList.add('in');
          rio.unobserve(e.target);
        }
      });
    }, { threshold: 0.15 });
    revEls.forEach(function (el) { rio.observe(el); });
  } else {
    revEls.forEach(function (el) { el.classList.add('in'); });
  }

  /* 数字滚动计数（与内置计数器去重：内置已处理的元素跳过） */
  var counters = document.querySelectorAll('[data-count]');
  if ('IntersectionObserver' in window && counters.length) {
    var co = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) {
        if (!e.isIntersecting) return;
        var el = e.target;
        if (el.dataset.ddHandled === '1') return;
        el.dataset.ddHandled = '1';
        var target = +el.dataset.count;
        if (reduced) { el.textContent = target.toLocaleString(); co.unobserve(el); return; }
        var dur = 1400, t0 = performance.now();
        (function step(t) {
          var p = Math.min(1, (t - t0) / dur);
          el.textContent = Math.floor(target * (1 - Math.pow(1 - p, 3))).toLocaleString();
          if (p < 1) requestAnimationFrame(step);
        })(t0);
        co.unobserve(el);
      });
    }, { threshold: 0.5 });
    counters.forEach(function (el) { co.observe(el); });
  }

  /* 神经网络粒子 */
  var cv = document.getElementById('net');
  if (cv) {
    var ctx = cv.getContext('2d');
    var W, H, nodes = [], DOT = 78;
    function resize() {
      var dpr = Math.min(window.devicePixelRatio || 1, 2);
      W = cv.offsetWidth; H = cv.offsetHeight;
      cv.width = W * dpr; cv.height = H * dpr;
      ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
      init();
    }
    function init() {
      var n = Math.min(80, Math.floor(W * H / 14000));
      nodes = [];
      for (var i = 0; i < n; i++) nodes.push({ x: Math.random() * W, y: Math.random() * H, vx: (Math.random() - .5) * .4, vy: (Math.random() - .5) * .4 });
    }
    function draw() {
      ctx.clearRect(0, 0, W, H);
      for (var i = 0; i < nodes.length; i++) {
        var p = nodes[i];
        p.x += p.vx; p.y += p.vy;
        if (p.x < 0 || p.x > W) p.vx *= -1;
        if (p.y < 0 || p.y > H) p.vy *= -1;
      }
      for (var a = 0; a < nodes.length; a++) {
        for (var b = a + 1; b < nodes.length; b++) {
          var n1 = nodes[a], n2 = nodes[b];
          var dx = n1.x - n2.x, dy = n1.y - n2.y, d = Math.hypot(dx, dy);
          if (d < DOT) {
            var o = (1 - d / DOT) * .55;
            ctx.strokeStyle = 'rgba(129,140,248,' + o + ')';
            ctx.lineWidth = 1;
            ctx.beginPath(); ctx.moveTo(n1.x, n1.y); ctx.lineTo(n2.x, n2.y); ctx.stroke();
          }
        }
      }
      for (var c = 0; c < nodes.length; c++) {
        ctx.fillStyle = 'rgba(34,211,238,.9)';
        ctx.beginPath(); ctx.arc(nodes[c].x, nodes[c].y, 2, 0, 7); ctx.fill();
      }
    }
    function tick() { if (!document.hidden) draw(); requestAnimationFrame(tick); }
    window.addEventListener('resize', resize);
    resize();
    if (reduced) { draw(); } else { tick(); }
  }

  /* AI 对话流式生成：GEO 优化文章 */
  var chat = document.getElementById('chat');
  var articleCard = document.querySelector('#articleRow .article-card');
  if (chat) {
    var answer = "已接收文章，正在进行 GEO 生成式引擎优化：提取核心问答 → 生成 FAQPage 结构化数据 → 映射到主流 AI 引擎。";
    var tasks = [['解析文章主题与实体', '工业传感器'], ['提取用户高频问题', '5 组 FAQ'], ['生成结构化数据', 'FAQPage JSON-LD'], ['映射 AI 引擎', 'DeepSeek/豆包/元宝/千问']];
    function typeWrite(el, text, i) {
      i = i || 0;
      if (i <= text.length) { el.innerHTML = text.slice(0, i) + '<span class="cursor"></span>'; setTimeout(function () { typeWrite(el, text, i + 1); }, 20); }
      else { el.innerHTML = text; showTasks(); }
    }
    function showTasks() {
      var wrap = document.createElement('div'); wrap.className = 'ai-tasks'; chat.appendChild(wrap);
      tasks.forEach(function (t, k) {
        var r = document.createElement('div'); r.className = 'ai-task';
        r.innerHTML = '<span class="chk"></span><span>' + t[0] + ' · ' + t[1] + '</span>';
        wrap.appendChild(r);
        setTimeout(function () { r.classList.add('done'); }, 300 + k * 420);
      });
      setTimeout(function () {
        var s = document.createElement('div'); s.className = 'ai-score';
        s.innerHTML = '<div class="ring"><svg width="54" height="54"><circle cx="27" cy="27" r="23" fill="none" stroke="rgba(255,255,255,.1)" stroke-width="5"/><circle id="prog" cx="27" cy="27" r="23" fill="none" stroke="url(#gg)" stroke-width="5" stroke-linecap="round" stroke-dasharray="144" stroke-dashoffset="144"/></svg><span class="pct">0%</span></div><div class="txt">预计被 AI 引擎引用概率<br><b>81%</b><span class="tag-geo"># GEO 生成式优化完成</span></div><defs><linearGradient id="gg" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#22d3ee"/><stop offset="1" stop-color="#e879f9"/></linearGradient></defs>';
        chat.appendChild(s);
        var circ = document.getElementById('prog'), pct = s.querySelector('.pct'), v = 0;
        var t = setInterval(function () { v += 2; circ.style.strokeDashoffset = 144 - (144 * v / 100); pct.textContent = v + '%'; if (v >= 81) { clearInterval(t); pct.textContent = '81%'; } }, 22);
        setTimeout(showFaq, 900);
      }, 300 + tasks.length * 420);
    }
    function showFaq() {
      var wrap = document.createElement('div'); wrap.className = 'faq-list';
      var faqs = [
        ['工业传感器选型要考虑哪些参数？', '量程、精度、输出信号、环境耐受性、响应时间。'],
        ['热电偶和热电阻有什么区别？', '热电偶测温范围宽但精度略低；热电阻精度高但范围较窄。'],
        ['压力传感器怎么选？', '根据介质、压力范围、输出形式（4-20mA/0-10V/数字）匹配工况。']
      ];
      faqs.forEach(function (f, k) {
        var r = document.createElement('div'); r.className = 'faq-item';
        r.style.opacity = '0'; r.style.transform = 'translateY(8px)'; r.style.transition = 'all .4s';
        r.innerHTML = '<b>Q：' + f[0] + '</b><span>' + f[1] + '</span>';
        wrap.appendChild(r);
        setTimeout(function () { r.style.opacity = '1'; r.style.transform = 'translateY(0)'; }, k * 220);
      });
      chat.appendChild(wrap);
      if (articleCard) articleCard.classList.add('is-done');
    }
    setTimeout(function () {
      if (articleCard) articleCard.classList.add('is-scanning');
      var think = document.createElement('div'); think.className = 'chat-row bot';
      think.innerHTML = '<div class="ava b">AI</div><div class="bubble"><div class="thinking"><i></i><i></i><i></i></div></div>';
      chat.appendChild(think);
      setTimeout(function () {
        think.querySelector('.bubble').innerHTML = '';
        var b = document.createElement('div'); b.className = 'bubble';
        think.querySelector('.bubble').replaceWith(b);
        typeWrite(b, answer);
      }, 1800);
    }, 1000);
  }

  /* 马里奥式像素人：吃蘑菇变大（循环） */
  var marioScene = document.getElementById('pixelMarioScene'),
      marioHero = document.getElementById('marioHero'),
      marioLabel = document.getElementById('marioLabel'),
      marioPoof = document.getElementById('marioPoof');
  if (marioScene && marioHero) {
    var mushrooms = marioScene.querySelectorAll('.mario-mushroom');
    var marioTimer = null, marioLoopTimer = null, marioVisible = false;
    function resetMario() {
      marioHero.classList.add('no-trans');
      marioHero.classList.remove('is-stage-1', 'is-stage-2', 'is-stage-3', 'is-stage-4', 'is-veteran');
      marioPoof.classList.remove('is-pop');
      mushrooms.forEach(function (m) { m.classList.add('no-trans'); m.classList.remove('is-eaten'); });
      if (marioLabel) marioLabel.textContent = '电商小白';
      void marioHero.offsetWidth;
      marioHero.classList.remove('no-trans');
      mushrooms.forEach(function (m) { m.classList.remove('no-trans'); });
    }
    function runMarioCycle() {
      if (!marioVisible) return;
      mushrooms.forEach(function (m, i) {
        marioTimer = setTimeout(function () {
          if (!marioVisible) return;
          m.classList.add('is-eaten');
          marioHero.classList.add('is-stage-' + (i + 1));
          if (i === mushrooms.length - 1) {
            marioTimer = setTimeout(function () {
              if (!marioVisible) return;
              marioHero.classList.remove('is-stage-1', 'is-stage-2', 'is-stage-3', 'is-stage-4');
              marioHero.classList.add('is-veteran');
              if (marioLabel) marioLabel.textContent = '电商老登';
              marioPoof.classList.add('is-pop');
              marioLoopTimer = setTimeout(function () {
                resetMario();
                marioLoopTimer = setTimeout(runMarioCycle, 900);
              }, 1800);
            }, 400);
          }
        }, i * 900);
      });
    }
    var marioIo = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) {
        if (e.isIntersecting) {
          if (!marioVisible) { marioVisible = true; resetMario(); marioLoopTimer = setTimeout(runMarioCycle, 400); }
        } else {
          marioVisible = false;
          clearTimeout(marioTimer); clearTimeout(marioLoopTimer);
          resetMario();
        }
      });
    }, { threshold: 0.35 });
    marioIo.observe(marioScene);
  }

  /* AI 员工核心功能：节点依次亮起 + 信号流动 */
  var flowPanel = document.getElementById('aiFlowPanel'),
      flow = document.getElementById('aiFlow');
  if (flowPanel && flow) {
    var flowNodes = flow.querySelectorAll('.ai-flow__node');
    var played = false;
    var fio = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) {
        if (!e.isIntersecting || played) return;
        played = true;
        flow.classList.add('is-playing');
        flowNodes.forEach(function (n, i) { setTimeout(function () { n.classList.add('active'); }, 260 + i * 480); });
        setTimeout(function () { flow.classList.add('is-done'); }, 260 + flowNodes.length * 480 + 400);
      });
    }, { threshold: 0.35 });
    fio.observe(flowPanel);
  }

  /* 右侧悬浮联系侧边栏 */
  var sideContact = document.getElementById('sideContact');
  if (sideContact) {
    var tab = sideContact.querySelector('.side-contact__tab');
    var close = sideContact.querySelector('.side-contact__close');
    var backdrop = document.querySelector('.side-contact__backdrop');
    function openSide() { sideContact.classList.add('is-open'); }
    function closeSide() { sideContact.classList.remove('is-open'); }
    if (tab) tab.addEventListener('click', openSide);
    if (close) close.addEventListener('click', closeSide);
    if (backdrop) backdrop.addEventListener('click', closeSide);
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeSide(); });
  }
})();

/* ============================================================
 * 首页 Hero 轮播（系统级，所有模板可用）
 * 检测 .q-hero[data-slider]，自动播放 + 指示点切换
 * ============================================================ */
(function () {
  var hero = document.querySelector('.q-hero[data-slider]');
  if (!hero) return;
  var slides = hero.querySelectorAll('.q-hero__slide');
  var dots = hero.querySelectorAll('.q-hero__dots button');
  if (!slides.length) return;
  var idx = 0;
  var interval = parseInt(hero.getAttribute('data-interval') || '5', 10) * 1000;
  var timer = null;

  function go(i) {
    idx = (i + slides.length) % slides.length;
    slides.forEach(function (s, n) { s.classList.toggle('active', n === idx); });
    dots.forEach(function (d, n) { d.classList.toggle('on', n === idx); });
  }
  function play() { stop(); timer = setInterval(function () { go(idx + 1); }, interval); }
  function stop() { if (timer) { clearInterval(timer); timer = null; } }

  dots.forEach(function (d) {
    d.addEventListener('click', function () {
      go(parseInt(d.getAttribute('data-i'), 10));
      play(); // 点击后重置计时
    });
  });
  // 鼠标悬停暂停
  hero.addEventListener('mouseenter', stop);
  hero.addEventListener('mouseleave', play);

  play();
})();
