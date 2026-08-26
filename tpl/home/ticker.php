<?php /** 首页：滚动字幕 */
$hm = []; if (!empty($settings['home_modules'])) { $hm = json_decode($settings['home_modules'], true) ?: []; }
$tk = $hm['ticker'] ?? [];
$itemsRaw = $tk['items'] ?? '';
if (is_string($itemsRaw) && trim($itemsRaw) !== '') {
    $items = array_values(array_filter(array_map('trim', preg_split('/\r?\n/', trim($itemsRaw)))));
} else {
    $items = ['获客打法','转化漏斗','私域复购','口碑裂变','同城拓客','短视频引流','1v1 陪跑','7 天见效'];
}
?>
<div class="q-ticker">
    <div class="q-ticker__track">
        <?php foreach ($items as $it): ?><span><i></i><?= e($it) ?></span><?php endforeach; ?>
        <?php foreach ($items as $it): ?><span><i></i><?= e($it) ?></span><?php endforeach; ?>
    </div>
</div>