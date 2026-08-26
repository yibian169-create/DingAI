<?php
/**
 * 后台分页组件
 * 调用方需传入：$pg（数组，含 list/total/page/pages）
 * 也兼容旧用法：$pages / $page 单变量
 */
$_pages = isset($pg) ? (int)$pg['pages'] : (int)($pages ?? 0);
$_page  = isset($pg) ? (int)$pg['page']  : (int)($page  ?? 1);
$_m     = e($_GET['m'] ?? '');
$_extra = $_GET;
unset($_extra['page'], $_extra['m']);
$qs = http_build_query($_extra);
?>
<?php if ($_pages > 1): ?>
<div class="pager">
  <?php for ($i = 1; $i <= $_pages; $i++): ?>
    <?php $href = 'admin.php?m=' . $_m . '&page=' . $i . ($qs ? '&' . $qs : ''); ?>
    <?php if ($i === $_page): ?><b><?= $i ?></b><?php else: ?>
      <a href="<?= e($href) ?>"><?= $i ?></a>
    <?php endif; ?>
  <?php endfor; ?>
</div>
<?php endif; ?>
