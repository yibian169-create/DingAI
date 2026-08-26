/* ============================================================
 * deyingding template: Textile & Garment Factory "Dark Flame"
 * Effects:
 *   1. floating thread canvas (coral / amber / cyan threads)
 *   2. hero title char-by-char reveal
 *   3. scroll parallax on hero copy
 *   4. reveal animations on scroll
 *   5. stat counters
 *   6. 3D tilt on capability cards
 * Safety: entire script is disabled when ?ve=1 (editor mode).
 * ============================================================ */

(function () {
    'use strict';

    var IS_VE = (location.search || '').indexOf('ve=1') !== -1;
    if (IS_VE) return;

    var THREADS = ['255,90,60', '255,180,84', '76,201,240'];

    /* ================= 1. Floating thread canvas ================= */
    var hero = document.querySelector('.q-hero');
    var canvas = document.getElementById('ve-threads');
    if (hero && !canvas) {
        canvas = document.createElement('canvas');
        canvas.id = 've-threads';
        hero.insertBefore(canvas, hero.firstChild);
    }
    var ctx = canvas && canvas.getContext && canvas.getContext('2d');
    if (hero && canvas && ctx) {
        var W = 0, H = 0, DPR = Math.min(window.devicePixelRatio || 1, 2);
        var lines = [];
        var LINES_N = 80;

        function resize() {
            W = hero.clientWidth; H = hero.clientHeight;
            canvas.width = W * DPR; canvas.height = H * DPR;
            ctx.setTransform(DPR, 0, 0, DPR, 0, 0);
        }
        function makeLine() {
            return {
                x: Math.random() * W,
                y: Math.random() * H,
                vx: (Math.random() - 0.5) * 0.3,
                vy: (Math.random() - 0.5) * 0.3,
                len: 50 + Math.random() * 110,
                alpha: 0.05 + Math.random() * 0.16,
                hue: THREADS[(Math.random() * THREADS.length) | 0]
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
                ctx.lineWidth = 1.1;
                ctx.beginPath();
                ctx.moveTo(l.x, l.y);
                ctx.lineTo(l.x + l.len * 0.78, l.y + l.len * 0.42);
                ctx.stroke();
            }
            requestAnimationFrame(step);
        }
        resize();
        window.addEventListener('resize', resize);
        for (var i = 0; i < LINES_N; i++) lines.push(makeLine());
        requestAnimationFrame(step);
    }

    /* ================= 2. Hero title char reveal ================= */
    var titleEl = document.querySelector('.q-hero__title');
    if (titleEl) {
        var html = titleEl.innerHTML;
        var parts = html.split(/(<[^>]+>)/g);
        var out = '';
        var delay = 0;
        parts.forEach(function (p) {
            if (p.indexOf('<') === 0) { out += p; return; }
            var chars = p.split('');
            chars.forEach(function (c) {
                if (c === '\n') { out += '<br>'; return; }
                out += '<span class="ve-char" style="--d:' + delay + 'ms">' + c + '</span>';
                delay += 40;
            });
        });
        titleEl.innerHTML = out;
        requestAnimationFrame(function () {
            requestAnimationFrame(function () { titleEl.classList.add('in'); });
        });
    }

    /* ================= 3. Hero parallax ================= */
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

    /* ================= 4. Reveal animations ================= */
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

    /* ================= 5. Counters ================= */
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
                var dur = 1600;
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

    /* ================= 6. 3D tilt on capability cards ================= */
    var cards = document.querySelectorAll('.q-feat');
    if (cards.length && window.matchMedia('(hover: hover)').matches) {
        cards.forEach(function (card) {
            card.addEventListener('mousemove', function (ev) {
                var r = card.getBoundingClientRect();
                var px = (ev.clientX - r.left) / r.width - 0.5;
                var py = (ev.clientY - r.top) / r.height - 0.5;
                card.style.transform = 'perspective(760px) rotateY(' + (px * 8) + 'deg) rotateX(' + (-py * 8) + 'deg) translateY(-5px)';
            });
            card.addEventListener('mouseleave', function () {
                card.style.transform = '';
            });
        });
    }
})();

/* ============================================================
 * Safety rules:
 * - Does not override fetch / XHR / document.cookie
 * - No external requests (prevents XSS / CSRF)
 * - No side effects on .ve-* classes used by the DIY editor
 * - Entire script disabled in editor mode (?ve=1)
 * - Wrapped in IIFE to avoid global leaks
 * ============================================================ */
