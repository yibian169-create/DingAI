/* ============================================================
 * deyingding 模板 · 家纺工厂「暖黑亚麻金」动效旗舰版 v3
 * 动效清单：
 *   1. Hero 丝线飘动画布（家纺主题：线）
 *   2. 主标题逐字浮现（带旋转入场）
 *   3. 滚动视差（Hero 内容随滚动缓慢移动）
 *   4. 入场动画（淡入上移 / 左移 / 右移 / 缩放，方向可配 data-dir）
 *   5. 数据数字递增
 *   6. 能力卡 3D 倾斜跟随鼠标
 * ============================================================ */

(function () {
    'use strict';

    /* ================= 1. Hero 丝线飘动画布 ================= */
    var hero = document.querySelector('.q-hero');
    var canvas = document.getElementById('ve-threads');
    var ctx = canvas && canvas.getContext('2d');
    if (hero && canvas && ctx) {
        var W = 0, H = 0, DPR = Math.min(window.devicePixelRatio || 1, 2);
        var lines = [];
        var LINES_N = 90;

        function resize() {
            W = hero.clientWidth; H = hero.clientHeight;
            canvas.width = W * DPR; canvas.height = H * DPR;
            ctx.setTransform(DPR, 0, 0, DPR, 0, 0);
        }
        function makeLine() {
            return {
                x: Math.random() * W,
                y: Math.random() * H,
                vx: (Math.random() - 0.5) * 0.28,
                vy: (Math.random() - 0.5) * 0.28,
                len: 46 + Math.random() * 90,
                alpha: 0.05 + Math.random() * 0.14,
                hue: Math.random() > 0.6 ? '125,148,113' : '169,187,153'
            };
        }
        function step() {
            ctx.clearRect(0, 0, W, H);
            for (var i = 0; i < lines.length; i++) {
                var l = lines[i];
                l.x += l.vx; l.y += l.vy;
                if (l.x < -l.len) l.x = W + l.len; if (l.x > W + l.len) l.x = -l.len;
                if (l.y < -l.len) l.y = H + l.len; if (l.y > H + l.len) l.y = -l.len;
                ctx.strokeStyle = 'rgba(' + l.hue + ',' + l.alpha + ')';
                ctx.lineWidth = 1;
                ctx.beginPath();
                ctx.moveTo(l.x, l.y);
                ctx.lineTo(l.x + l.len * 0.8, l.y + l.len * 0.4);
                ctx.stroke();
            }
            requestAnimationFrame(step);
        }
        resize();
        window.addEventListener('resize', resize);
        for (var i = 0; i < LINES_N; i++) lines.push(makeLine());
        requestAnimationFrame(step);
    }

    /* ================= 2. 主标题逐字浮现 ================= */
    var titleEl = document.querySelector('.q-hero__title');
    if (titleEl) {
        var html = titleEl.innerHTML;
        // 拆成字符，保留 <em> 整体
        var parts = html.split(/(<[^>]+>)/g);
        var out = '';
        var delay = 0;
        parts.forEach(function (p) {
            if (p.indexOf('<') === 0) { out += p; return; }
            var chars = p.split('');
            chars.forEach(function (c) {
                if (c === '\n') { out += '<br>'; return; }
                out += '<span class="ve-char" style="--d:' + delay + 'ms">' + c + '</span>';
                delay += 38;
            });
        });
        titleEl.innerHTML = out;
        requestAnimationFrame(function () {
            requestAnimationFrame(function () { titleEl.classList.add('in'); });
        });
    }

    /* ================= 3. 滚动视差（Hero 内容缓移） ================= */
    var heroCopy = document.querySelector('.q-hero__copy');
    if (heroCopy) {
        var ticking = false;
        function parallax() {
            var y = window.scrollY;
            if (y < window.innerHeight * 1.2) {
                heroCopy.style.transform = 'translateY(' + (y * 0.22) + 'px)';
                heroCopy.style.opacity = String(Math.max(0, 1 - y / (window.innerHeight * 0.85)));
            }
            ticking = false;
        }
        window.addEventListener('scroll', function () {
            if (!ticking) { requestAnimationFrame(parallax); ticking = true; }
        }, { passive: true });
    }

    /* ================= 4. 入场动画（观察器） ================= */
    var revealEls = document.querySelectorAll('.q-reveal');
    if (revealEls.length && 'IntersectionObserver' in window) {
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (e) {
                if (e.isIntersecting) {
                    e.target.classList.add('is-visible');
                    io.unobserve(e.target);
                }
            });
        }, { threshold: 0.12 });
        revealEls.forEach(function (el) { io.observe(el); });
    } else {
        revealEls.forEach(function (el) { el.classList.add('is-visible'); });
    }

    /* ================= 5. 数据数字递增 ================= */
    var counters = document.querySelectorAll('.q-stat__num');
    if (counters.length && 'IntersectionObserver' in window) {
        var cio = new IntersectionObserver(function (entries) {
            entries.forEach(function (e) {
                if (!e.isIntersecting) return;
                var el = e.target;
                var m = (el.textContent || '').match(/([\d.,]+)/);
                if (!m) return;
                var raw = m[1].replace(/,/g, '');
                var target = parseFloat(raw);
                if (isNaN(target)) return;
                var dur = 1500;
                var t0 = performance.now();
                var fmt = function (n) {
                    return n % 1 === 0 ? Math.floor(n).toLocaleString() : n.toFixed(1);
                };
                (function raf(now) {
                    var p = Math.min(1, (now - t0) / dur);
                    var cur = target * (1 - Math.pow(1 - p, 3));
                    el.textContent = el.textContent.replace(m[1], fmt(cur));
                    if (p < 1) requestAnimationFrame(raf);
                })(t0);
                cio.unobserve(el);
            });
        }, { threshold: 0.4 });
        counters.forEach(function (c) { cio.observe(c); });
    }

    /* ================= 6. 能力卡 3D 倾斜（跟随鼠标） ================= */
    var cards = document.querySelectorAll('.q-feat');
    if (cards.length && window.matchMedia('(hover: hover)').matches) {
        cards.forEach(function (card) {
            card.addEventListener('mousemove', function (ev) {
                var r = card.getBoundingClientRect();
                var px = (ev.clientX - r.left) / r.width - 0.5;
                var py = (ev.clientY - r.top) / r.height - 0.5;
                card.style.transform = 'perspective(720px) rotateY(' + (px * 7) + 'deg) rotateX(' + (-py * 7) + 'deg) translateY(-4px)';
            });
            card.addEventListener('mouseleave', function () {
                card.style.transform = '';
            });
        });
    }
})();

/* ============================================================
 * 安全规则：
 * - 不要覆盖 fetch / XMLHttpRequest / document.cookie
 * - 不要请求外部域名（防 XSS / CSRF）
 * - 不要给 .ve-* 类（后台 DIY 编辑器使用）加副作用
 * - 报错必须 try/catch，不要让模板脚本拖垮整站
 * ============================================================ */