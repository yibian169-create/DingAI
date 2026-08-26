/* ============================================================
 * deyingding 模板 demo · main.js 示例
 * 演示两个常见交互：打字机效果 + 滚动揭示
 * ============================================================ */

(function () {
    /* ---------- 1. 打字机效果（首屏 Hero 主标题） ---------- */
    var titleEl = document.querySelector('.q-hero__title');
    if (titleEl && !titleEl.dataset.typed) {
        titleEl.dataset.typed = '1';
        var text = titleEl.textContent.trim();
        titleEl.textContent = '';
        var i = 0;
        var tick = setInterval(function () {
            titleEl.textContent = text.slice(0, ++i);
            if (i >= text.length) clearInterval(tick);
        }, 60);
    }

    /* ---------- 2. 滚动揭示（所有 .q-reveal 区块） ---------- */
    if ('IntersectionObserver' in window) {
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (e) {
                if (e.isIntersecting) {
                    e.target.classList.add('is-visible');
                    io.unobserve(e.target);
                }
            });
        }, { threshold: 0.15 });
        document.querySelectorAll('.q-reveal').forEach(function (el) {
            io.observe(el);
        });
    }

    /* ---------- 3. 平滑滚动（锚点链接） ---------- */
    document.querySelectorAll('a[href^^="#"]').forEach(function (a) {
        a.addEventListener('click', function (ev) {
            var id = a.getAttribute('href').slice(1);
            var target = document.getElementById(id);
            if (target) {
                ev.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    /* ---------- 4. 数据统计数字递增动画（demo 可选） ---------- */
    var counters = document.querySelectorAll('.q-stat__num');
    if (counters.length && 'IntersectionObserver' in window) {
        var cio = new IntersectionObserver(function (entries) {
            entries.forEach(function (e) {
                if (!e.isIntersecting) return;
                var el = e.target;
                var numMatch = (el.textContent || '').match(/(\d+)/);
                if (!numMatch) return;
                var target = parseInt(numMatch[1], 10);
                var start = 0, dur = 1200;
                var t0 = performance.now();
                var raf = function (now) {
                    var p = Math.min(1, (now - t0) / dur);
                    var cur = Math.floor(start + (target - start) * (1 - Math.pow(1 - p, 3)));
                    el.textContent = el.textContent.replace(/\d+/, cur);
                    if (p < 1) requestAnimationFrame(raf);
                };
                requestAnimationFrame(raf);
                cio.unobserve(el);
            });
        }, { threshold: 0.4 });
        counters.forEach(function (c) { cio.observe(c); });
    }
})();

/* ============================================================
 * 安全规则：
 * - 不要覆盖 fetch / XMLHttpRequest / document.cookie
 * - 不要请求外部域名（防 XSS / CSRF）
 * - 不要给 .ve-* 类（后台 DIY 编辑器使用）加副作用
 * - 报错必须 try/catch，不要让模板脚本拖垮整站
 * ============================================================ */