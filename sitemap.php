<?php
/**
 * 全站 XML Sitemap（百度/必应收录入口）
 * 访问：/sitemap.php
 * 内容：首页、栏目列表、全部文章、全部产品、城市分站（每城独立页）
 * 城市分站 URL 跟随 city_pretty 开关：开启输出伪静态 /beijing/，未开启输出 index.php?city=beijing
 */

/* 未安装 → 直接 404 */
if (!file_exists(__DIR__ . '/install.lock')) {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/funcs.php';
ensure_schema();

$sid  = current_site_id();
$host = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
$base = $host . '/index.php';
$today = date('Y-m-d');

$urls = [];

/* 首页 */
$urls[] = [$host . '/', $today, '1.0'];

/* 城市分站列表页 */
if (setting('city_enable', '0') === '1') {
    $urls[] = [$host . '/index.php?act=city', $today, '0.8'];
}

/* 栏目列表 */
foreach (DB::all('SELECT id FROM categories WHERE site_id=? AND status=1 ORDER BY sort ASC', [$sid]) as $c) {
    $urls[] = [$base . '?act=list&cat=' . (int)$c['id'], $today, '0.7'];
}

/* 文章详情 */
foreach (DB::all('SELECT id FROM articles WHERE site_id=? AND status=1 ORDER BY id DESC LIMIT 3000', [$sid]) as $a) {
    $urls[] = [$base . '?act=detail&type=article&id=' . (int)$a['id'], $today, '0.6'];
}

/* 产品详情 */
foreach (DB::all('SELECT id FROM products WHERE site_id=? AND status=1 ORDER BY id DESC LIMIT 1000', [$sid]) as $p) {
    $urls[] = [$base . '?act=detail&type=product&id=' . (int)$p['id'], $today, '0.6'];
}

/* 城市分站（每城独立可收录页） */
if (setting('city_enable', '0') === '1') {
    foreach (DB::all('SELECT * FROM city_sites WHERE site_id=? AND status=1 ORDER BY sort ASC', [$sid]) as $c) {
        $u = city_url($c);
        $loc = strpos($u, '://') === false ? $host . '/' . ltrim($u, '/') : $u;
        $urls[] = [$loc, $today, '0.8'];
    }
}

header('Content-Type: application/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($urls as [$loc, $lastmod, $prio]) {
    echo '  <url><loc>' . htmlspecialchars($loc, ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</loc><lastmod>' . $lastmod . '</lastmod><changefreq>daily</changefreq><priority>' . $prio . '</priority></url>' . "\n";
}
echo '</urlset>' . "\n";
