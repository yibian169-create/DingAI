/* 棉叙 LINEN NARRATIVE · 家纺工厂模板动效
 * 纯展示型动效；DIY 可视化编辑（?ve=1）下同样安全运行，不干扰双击文字 / 按钮配链接。 */
(function () {
  "use strict";
  function ready(fn) {
    if (document.readyState !== "loading") fn();
    else document.addEventListener("DOMContentLoaded", fn);
  }
  ready(function () {
    /* 1. 滚动浮现 */
    var reveals = document.querySelectorAll(".q-reveal");
    if ("IntersectionObserver" in window && reveals.length) {
      var io = new IntersectionObserver(function (es) {
        es.forEach(function (e) {
          if (e.isIntersecting) { e.target.classList.add("in"); io.unobserve(e.target); }
        });
      }, { threshold: 0.16 });
      reveals.forEach(function (el) { io.observe(el); });
    } else {
      reveals.forEach(function (el) { el.classList.add("in"); });
    }

    /* 2. 数字递增（统计带） */
    var nums = document.querySelectorAll(".q-stat__num em[data-count]");
    var counted = false;
    function runCounters() {
      if (counted) return; counted = true;
      nums.forEach(function (em) {
        var end = parseInt(em.getAttribute("data-count"), 10) || 0;
        var s = 0, step = Math.max(1, Math.floor(end / 40));
        var t = setInterval(function () {
          s += step; if (s >= end) { s = end; clearInterval(t); }
          em.textContent = s;
        }, 28);
      });
    }
    if (nums.length) {
      var no = new IntersectionObserver(function (es) {
        es.forEach(function (e) { if (e.isIntersecting) runCounters(); });
      }, { threshold: 0.4 });
      var sec = document.querySelector(".q-stats");
      if (sec) no.observe(sec); else runCounters();
    }

    /* 3. 材质分屏 轻微视差 */
    var pics = document.querySelectorAll(".ht-split__pic img");
    if (pics.length) {
      var ticking = false;
      window.addEventListener("scroll", function () {
        if (ticking) return; ticking = true;
        requestAnimationFrame(function () {
          var y = window.pageYOffset || 0;
          pics.forEach(function (im) { im.style.transform = "translateY(" + (y * -0.025) + "px)"; });
          ticking = false;
        });
      }, { passive: true });
    }

    /* 4. 顶栏换标（棉叙 · 家纺智造） */
    var mark = document.querySelector(".q-logo__mark");
    if (mark && /ding/i.test(mark.textContent)) { mark.textContent = "棉叙"; }
  });
})();
