/* ============================================================
 * deyingding template: Home Textile Factory "Linen Origin"
 * Effects:
 *   1. floating cotton particles
 *   2. woven linen line canvas
 *   3. hero title char-by-char reveal
 *   4. scroll parallax on hero copy
 *   5. reveal animations on scroll
 *   6. stat counters
 *   7. soft 3D tilt on capability cards
 * Safety: entire script is disabled when ?ve=1 (editor mode).
 * ============================================================ */

(function () {
    'use strict';

    var IS_VE = (location.search || '').indexOf('ve=1') !== -1;
    if (IS_VE) return;

    var COTTON_COLOR = '232, 224, 210';
    var WEAVE_COLOR = '125, 148, 113';

    /* ================= 1. Floating cotton particles ================= */
    var hero = document.querySelector('.q-hero');
    var cottonCanvas = document.getElementById('ve-cotton');
    if (hero && !cottonCanvas) {
        cottonCanvas = document.createElement('canvas');
        cottonCanvas.id = 've-cotton';
        hero.insertBefore(cottonCanvas, hero.firstChild);
    }
    var cctx = cottonCanvas && cottonCanvas.getContext && cottonCanvas.getContext('2d');
    if (hero && cottonCanvas && cctx) {
        var W = 0, H = 0, DPR = Math.min(window.devicePixelRatio || 1, 2);
        var flakes = [];
        var N = 55;

        function resizeC() {
            W = hero.clientWidth; H = hero.clientHeight;
            cottonCanvas.width = W * DPR; cottonCanvas.height = H * DPR;
            cctx.setTransform(DPR, 0, 0, DPR, 0, 0);
        }
        function makeFlake() {
            return {
                x: Math.random() * W,
                y: Math.random() * H,
                r: 2 + Math.random() * 5,
                vy: 0.2 + Math.random() * 0.5,
                vx: (Math.random() - 0.5) * 0.3,
                sway: Math.random() * Math.PI * 2,
                alpha: 0.12 + Math.random() * 0.22
            };
        }
        function stepC() {
            cctx.clearRect(0, 0, W, H);
            for (var i = 0; i < flakes.length; i++) {
                var f = flakes[i];
                f.y += f.vy; f.x += f.vx + Math.sin(f.sway) * 0.2; f.sway += 0.02;
                if (f.y > H + 10) { f.y = -10; f.x = Math.random() * W; }
                cctx.beginPath();
                cctx.arc(f.x, f.y, f.r, 0, Math.PI * 2);
                cctx.fillStyle = 'rgba(' + COTTON_COLOR + ',' + f.alpha + ')';
                cctx.fill();
            }
            requestAnimationFrame(stepC);
        }
        resizeC();
        window.addEventListener('resize', resizeC);
        for (var i = 0; i < N; i++) flakes.push(makeFlake());
        requestAnimationFrame(stepC);
    }

    /* ================= 2. Woven linen line canvas ================= */
    var weaveCanvas = document.getElementById('ve-weave');
    var wctx = weaveCanvas && weaveCanvas.getContext && weaveCanvas.getContext('2d');
    if (hero && weaveCanvas && wctx) {
        var WW = 0, WH = 0;
        var weaveLines = [];
        var WN = 18;

        function resizeW() {
            WW = hero.clientWidth; WH = hero.clientHeight;
            weaveCanvas.width = WW; weaveCanvas.height = WH;
        }
        function makeWLine(dir) {
            return {
                x: dir === 'h' ? 0 : Math.random() * WW,
                y: dir === 'h' ? Math.random() * WH : 0,
                len: dir === 'h' ? WW : WH,
                dir: dir,
                speed: 0.15 + Math.random() * 0.25,
                alpha: 0.04 + Math.random() * 0.08
            };
        }
        function stepW() {
            wctx.clearRect(0, 0, WW, WH);
            for (var i = 0; i < weaveLines.length; i++) {
                var l = weaveLines[i];
                if (l.dir === 'h') { l.x += l.speed; if (l.x > WW) l.x = -l.len; }
                else { l.y += l.speed; if (l.y > WH) l.y = -l.len; }
                wctx.strokeStyle = 'rgba(' + WEAVE_COLOR + ',' + l.alpha + ')';
                wctx.lineWidth = 1;
                wctx.beginPath();
                if (l.dir === 'h') { wctx.moveTo(l.x, l.y); wctx.lineTo(l.x + l.len, l.y); }
                else { wctx.moveTo(l.x, l.y); wctx.lineTo(l.x, l.y + l.len); }
                wctx.stroke();
            }
            requestAnimationFrame(stepW);
        }
        resizeW();
        window.addEventListener('resize', resizeW);
        for (var i = 0; i < WN; i++) {
            weaveLines.push(makeWLine(i % 2 === 0 ? 'h' : 'v'));
        }
        requestAnimationFrame(stepW);
    }

    /* ================= 3. Hero title char reveal ================= */
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
                delay += 32;
            });
        });
        titleEl.innerHTML = out;
        requestAnimationFrame(function () {
            requestAnimationFrame(function () { titleEl.classList.add('in'); });
        });
    }

    /* ================= 4. Hero parallax ================= */
    var heroCopy = document.querySelector('.q-hero__copy');
    if (heroCopy) {
        var ticking = false;
        function parallax() {
            var y = window.scrollY;
            if (y < window.innerHeight * 1.2) {
                heroCopy.style.transform = 'translateY(' + (y * 0.18) + 'px)';
                heroCopy.style.opacity = String(Math.max(0, 1 - y / (window.innerHeight * 0.9)));
            }
            ticking = false;
        }
        window.addEventListener('scroll', function () {
            if (!ticking) { requestAnimationFrame(parallax); ticking = true; }
        }, { passive: true });
    }

    /* ================= 5. Reveal animations ================= */
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

    /* ================= 6. Counters ================= */
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

    /* ================= 7. Soft 3D tilt on capability cards ================= */
    var cards = document.querySelectorAll('.q-feat');
    if (cards.length && window.matchMedia('(hover: hover)').matches) {
        cards.forEach(function (card) {
            card.addEventListener('mousemove', function (ev) {
                var r = card.getBoundingClientRect();
                var px = (ev.clientX - r.left) / r.width - 0.5;
                var py = (ev.clientY - r.top) / r.height - 0.5;
                card.style.transform = 'perspective(760px) rotateY(' + (px * 6) + 'deg) rotateX(' + (-py * 6) + 'deg) translateY(-8px)';
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
