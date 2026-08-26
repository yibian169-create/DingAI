<?php /** 首页内容（按后台 DIY 布局自由组合） */
$allowedSections = [
    'hero'         => '首屏 Hero',
    'scenario'     => '场景方案',
    'ticker'       => '滚动字幕',
    'stats'        => '数据统计',
    'capabilities' => '核心能力',
    'about'        => '关于我们',
    'products'     => '产品精选',
    'workflow'     => '服务流程',
    'news'         => '新闻动态',
    'cta'          => '底部 CTA',
    'contact'      => '联系我们',
    'board'        => '布料拼贴 Hero',
    'collections'  => '空间系列',
    'story'        => '材质故事',
    'timeline'     => '制造流程(竖排)',
    'quote'        => '工艺引语',
];
$raw = $settings['home_layout'] ?? setting('home_layout', '');
$layout = [];
if ($raw) {
    $decoded = json_decode($raw, true);
    if (is_array($decoded)) {
        foreach ($decoded as $item) {
            $key = $item['key'] ?? '';
            if (isset($allowedSections[$key])) {
                $layout[] = ['key' => $key, 'show' => !empty($item['show'])];
            }
        }
    }
}
if (!$layout) {
    foreach ($allowedSections as $key => $label) {
        $layout[] = ['key' => $key, 'show' => true];
    }
}
$veMode = !empty($_GET['ve']) ? true : false;
foreach ($layout as $sec):
    if (empty($sec['show'])) { continue; }
    $file = __DIR__ . '/home/' . $sec['key'] . '.php';
    if (is_file($file)) {
        if ($veMode) { echo '<div class="ve-slot" data-ve="' . htmlspecialchars((string)$sec['key'], ENT_QUOTES, 'UTF-8') . '">'; }
        require $file;
        if ($veMode) { echo '</div>'; }
    }
endforeach;
