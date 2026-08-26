<?php /** 自定义表单前台页（左右结构） */ ?>
<!-- ============ 表单横幅 ============ -->
<section class="q-band">
    <div class="q-grid-overlay"></div>
    <div class="q-container">
        <span class="q-kicker">Form</span>
        <h1 class="q-band__title"><em><?= e($def['title'] ?: $def['name']) ?></em></h1>
        <?php if (!empty($def['remark'])): ?>
        <p class="q-band__info"><?= e($def['remark']) ?></p>
        <?php endif; ?>
        <nav class="q-crumb">
            <a href="index.php">首页</a><span class="sep">/</span>
            <span class="cur"><?= e($def['title'] ?: $def['name']) ?></span>
        </nav>
    </div>
</section>

<!-- ============ 左右结构：表单主体 + 侧栏 ============ -->
<section class="q-section q-section--tight">
    <div class="q-container q-detail-layout">
        <main class="q-detail-main">
            <div class="q-form-wrap">
                <?php if ($okMsg): ?>
                <div class="q-form-msg q-form-msg--ok">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="26" height="26"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="M22 4 12 14.01l-3-3"/></svg>
                    <h3><?= e($okMsg) ?></h3>
                    <a class="q-btn q-btn--grad q-btn--sm" href="index.php" style="margin-top:18px">返回首页</a>
                </div>
                <?php else: ?>
                <div class="q-form-card q-reveal">
                    <?php if ($errMsg): ?>
                    <div class="q-form-msg q-form-msg--err"><?= e($errMsg) ?></div>
                    <?php endif; ?>
                    <h2><?= e($def['title'] ?: $def['name']) ?></h2>
                    <p class="tip"><?= e($def['remark'] ?: '请填写以下信息，我们将尽快与您联系。') ?></p>
                    <?= $formHtml /* 已转义字段标签/选项 */ ?>
                </div>
                <?php endif; ?>
            </div>
        </main>
        <?php
        $sideNav  = $navFlat ?? [];
        $hot      = $newsFoot ?? [];
        $curCatId = 0;
        require __DIR__ . '/side_col.php';
        ?>
    </div>
</section>

<script>
function qFormCheck(form) {
  var ok = true;
  form.querySelectorAll('[required]').forEach(function (el) {
    var v = el.value.trim();
    if (el.type === 'checkbox' || el.type === 'radio') {
      var group = form.querySelectorAll('input[name="' + el.name + '"]:checked');
      if (!group.length) { ok = false; el.style.outline = '2px solid #ef4444'; }
      return;
    }
    if (!v) { ok = false; el.style.outline = '2px solid #ef4444'; }
  });
  if (!ok) { alert('请完整填写必填项'); return false; }
  return true;
}
</script>
