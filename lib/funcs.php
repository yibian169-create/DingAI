<?php
/**
 * 公共函数：设置读取、导航树、分页、表单辅助、单站点上下文
 */
require_once __DIR__ . '/db.php';

/** ============ 单站点上下文 ============ */

/**
 * 当前站点 ID（单站点自用官网，固定为 0）
 */
function current_site_id(): int
{
    return 0;
}

/** 是否平台管理员登录 */
function is_admin(): bool
{
    return !empty($_SESSION['admin_id']);
}

/** 校验后台登录（超管） */
function require_login(): void
{
    if (empty($_SESSION['admin_id'])) {
        header('Location: admin.php?m=login');
        exit;
    }
}

/** 校验平台管理员 */
function require_admin(): void
{
    if (empty($_SESSION['admin_id'])) {
        header('Location: admin.php?m=login');
        exit;
    }
}

/** 开通站点：写入初始配置（后台创建用户时调用，初始化一套默认站点配置） */
function init_site(int $siteId, string $siteName): void
{
    $defaults = [
        'site_name' => $siteName ?: '得应盯',
        'phone' => '', 'email' => '', 'address' => '',
        'footer_text' => '帮中小老板把业务推出去，让客户主动找上门。',
        'hero_title' => '业务拓展实战帮手',
        'hero_sub' => '专注帮中小老板做业务拓展：获客、转化、口碑，让生意自己跑起来。',
        'about_text' => '我们陪中小老板做业务拓展，用一套可落地的方法 + 工具，把流量变成客户、把客户变成复购。',
        'stat1' => '500', 'stat1_label' => '服务老板',
        'stat2' => '30', 'stat2_label' => '拓展打法',
        'stat3' => '92', 'stat3_label' => '续费满意度',
        'stat4' => '7', 'stat4_label' => '天见效',
        'seo_keywords' => '业务拓展,中小老板,获客,客户转化,口碑营销',
        'seo_description' => $siteName . ' - 帮中小老板做业务拓展的实战服务',
        'theme' => 'aurora', 'custom_c1' => '#22d3ee', 'custom_c2' => '#818cf8', 'custom_c3' => '#e879f9',
        'techsupport_text' => '', 'techsupport_url' => '',
        'beian' => '', 'copyright_year' => '',
        'city_enable' => '0', 'tpl_active' => '',
    ];
    foreach ($defaults as $k => $v) {
        DB::run("INSERT IGNORE INTO settings(site_id,`key`,`value`) VALUES(?,?,?)", [$siteId, $k, $v]);
    }
}

/** 读取单个配置（按当前站点） */
function setting(string $key, string $def = '', ?int $siteId = null): string
{
    $siteId = $siteId ?? current_site_id();
    static $cache = [];
    if (!isset($cache[$siteId])) {
        $cache[$siteId] = [];
        foreach (DB::all('SELECT `key`,`value` FROM settings WHERE site_id=?', [$siteId]) as $r) {
            $cache[$siteId][$r['key']] = $r['value'];
        }
    }
    $raw = $cache[$siteId][$key] ?? $def;
    // 敏感键（AI Key）读取时解密；非密文原样返回，兼容历史明文数据
    if (in_array($key, _secret_keys(), true)) {
        return dec_secret($raw);
    }
    return $raw;
}

/** 读取全部配置（按当前站点） */
function settings_all(): array
{
    $siteId = current_site_id();
    static $cache = [];
    if (!isset($cache[$siteId])) {
        $cache[$siteId] = [];
        foreach (DB::all('SELECT `key`,`value` FROM settings WHERE site_id=?', [$siteId]) as $r) {
            $cache[$siteId][$r['key']] = $r['value'];
        }
    }
    return $cache[$siteId];
}

/** 写入配置（按当前站点，存在则更新，不存在则插入）
 *  不依赖唯一索引：先 UPDATE，影响行数为 0 再 INSERT，避免旧库缺索引时产生重复行导致读到空值 */
/** 敏感配置键：以密文存储，避免数据库泄露导致 AI Key 暴露 */
function _secret_keys(): array
{
    return ['ai_api_key', 'ai_img_key'];
}

/* ============ 安全辅助：密钥加密（openssl AES-256-CBC） ============ */

/** 取服务端主密钥：优先用 config.php 的 APP_SECRET 常量，否则落到 web 根之外的 .app_secret 文件 */
function app_secret(): string
{
    static $k = null;
    if ($k !== null) {
        return $k;
    }
    if (defined('APP_SECRET') && is_string(APP_SECRET) && APP_SECRET !== '') {
        $k = APP_SECRET;
        return $k;
    }
    $file = dirname(dirname(__DIR__)) . '/.app_secret';
    if (is_file($file)) {
        $k = trim((string) @file_get_contents($file));
    }
    if (empty($k)) {
        $k = bin2hex(random_bytes(16));
        @file_put_contents($file, $k);
        @chmod($file, 0600);
    }
    return $k;
}

function enc_secret(string $v): string
{
    if ($v === '') {
        return '';
    }
    $key = hash('sha256', app_secret(), true);
    $iv  = random_bytes(16);
    $c   = openssl_encrypt($v, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
    return $c === false ? $v : 'ENC:' . base64_encode($iv . $c);
}

function dec_secret(string $v): string
{
    if ($v === '' || !str_starts_with($v, 'ENC:')) {
        return $v;
    }
    $raw = base64_decode(substr($v, 4), true);
    if ($raw === false || strlen($raw) < 17) {
        return '';
    }
    $key = hash('sha256', app_secret(), true);
    $d   = openssl_decrypt(substr($raw, 16), 'AES-256-CBC', $key, OPENSSL_RAW_DATA, substr($raw, 0, 16));
    return $d === false ? '' : $d;
}

/* ============ 安全辅助：CSRF ============ */

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . csrf_token() . '">';
}

/** 校验请求中的 CSRF Token；缺失或不符返回 false（写操作前应调用并据此拦截） */
function csrf_check(): bool
{
    if (empty($_SESSION['csrf'])) {
        return false;
    }
    $sent = $_POST['csrf'] ?? ($_GET['csrf'] ?? '');
    return is_string($sent) && $sent !== '' && hash_equals($_SESSION['csrf'], $sent);
}

/* ============ 安全辅助：SSRF 内网地址拦截 ============ */

/** 判断 IP 是否为私有/保留/环回段，用于拦截远程下载 SSRF */
function is_private_ip(string $ip): bool
{
    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        return true; // 无法解析的域名按不可信处理
    }
    $flags = FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE;
    // 返回 false 表示不在私有/保留段（即公网，允许）
    return filter_var($ip, FILTER_VALIDATE_IP, $flags) === false;
}

/** 解析 URL 主机对应的 IP，若为内网/保留地址返回 true（应拒绝） */
function is_ssrf_host(string $url): bool
{
    $host = parse_url($url, PHP_URL_HOST);
    if (!is_string($host) || $host === '') {
        return true;
    }
    if (filter_var($host, FILTER_VALIDATE_IP)) {
        return is_private_ip($host);
    }
    $ip = @gethostbyname($host);
    if ($ip === $host || $ip === '') {
        return true;
    }
    return is_private_ip($ip);
}

function save_setting(string $key, string $value): void
{
    $siteId = current_site_id();
    // 敏感键（AI Key）加密后再存储
    if (in_array($key, _secret_keys(), true)) {
        $value = enc_secret($value);
    }
    // 依赖 (site_id,key) 唯一索引做原子 upsert，避免先 UPDATE 再 INSERT 时
    // 因值未变化导致 affected_rows=0 而误走 INSERT 触发 Duplicate entry。
    DB::run(
        'INSERT INTO settings(site_id,`key`,`value`) VALUES(?,?,?) ON DUPLICATE KEY UPDATE `value`=?',
        [$siteId, $key, $value, $value]
    );
}

/** 构建栏目树（含子栏目，按当前站点） */
function nav_tree(): array
{
    $sid  = current_site_id();
    // 顶部导航只显示普通栏目（文章/产品等），下载分类仅在下载专区页面内展示
    $cats = DB::all("SELECT * FROM categories WHERE status=1 AND site_id=? AND (type IS NULL OR type='' OR type!='download') ORDER BY sort ASC, id ASC", [$sid]);
    $children = [];
    foreach ($cats as $c) {
        $children[$c['pid']][] = $c;
    }
    $tree = [];
    foreach ($cats as $c) {
        if ((int)$c['pid'] === 0) {
            $c['children'] = $children[$c['id']] ?? [];
            $tree[] = $c;
        }
    }
    return $tree;
}

/** 当前城市分站（?city=拼音，分站开启时生效；兼容 ?city=中文名） */
function current_city(): ?array
{
    if (setting('city_enable', '0') !== '1') {
        return null;
    }
    $city = trim($_GET['city'] ?? '');
    if ($city === '') {
        return null;
    }
    $sid = current_site_id();
    $row = DB::one('SELECT * FROM city_sites WHERE site_id=? AND (pinyin=? OR city=?) AND status=1', [$sid, $city, $city]);
    return $row ?: null;
}

/** 分站 URL（优先拼音后缀） */
function city_url(array $c): string
{
    $key = !empty($c['pinyin']) ? $c['pinyin'] : $c['city'];
    return 'index.php?city=' . urlencode($key);
}

/** 分站城市名标签（用于标题前缀） */
function city_label(): string
{
    $city = current_city();
    return $city ? '[' . $city['city'] . ']' : '';
}

/** 一键导入全国分站（内置城市数据，跳过已存在的城市，返回新增数量） */
function import_national_cities(): int
{
    $cities = require __DIR__ . '/cities.php';
    $added  = 0;
    $sid    = current_site_id();
    $exists = [];
    foreach (DB::all('SELECT city FROM city_sites WHERE site_id=?', [$sid]) as $r) {
        $exists[$r['city']] = true;
    }
    $sort = (int)(DB::one('SELECT MAX(sort) AS s FROM city_sites WHERE site_id=?', [$sid])['s'] ?? 0);
    foreach ($cities as [$name, $pinyin]) {
        if (isset($exists[$name])) {
            continue;
        }
        $sort++;
        DB::insert('INSERT INTO city_sites(site_id,city,pinyin,title_suffix,status,sort) VALUES(?,?,?,?,?,?)',
            [$sid, $name, $pinyin, '', 1, $sort]);
        $exists[$name] = true;
        $added++;
    }
    return $added;
}

/** 从内置城市数据查拼音（保存分站时自动补全） */
function city_pinyin(string $city): string
{
    $cities = require __DIR__ . '/cities.php';
    foreach ($cities as [$name, $pinyin]) {
        if ($name === $city) {
            return $pinyin;
        }
    }
    return '';
}

/** 主题配色（盘企 ui.json 同款 4 套方案 + 自定义） */
function get_theme_colors(string $theme): array
{
    $presets = [
        'aurora' => ['#22d3ee', '#818cf8', '#e879f9'], // 极光青（默认）
        'tech'   => ['#a855f7', '#6366f1', '#ec4899'], // 科技紫
        'jade'   => ['#10b981', '#22d3ee', '#84cc16'], // 翡翠绿
        'solar'  => ['#f97316', '#f59e0b', '#ef4444'], // 活力橙
    ];
    if (isset($presets[$theme])) {
        return $presets[$theme];
    }
    // 自定义：3 色取设置或默认极光青
    $c1 = setting('custom_c1', '#22d3ee');
    $c2 = setting('custom_c2', '#818cf8');
    $c3 = setting('custom_c3', '#e879f9');
    return [$c1, $c2, $c3];
}

/** 解析表单字段 JSON 为数组 */
function form_fields(?array $def): array
{
    if (!$def || empty($def['fields'])) {
        return [];
    }
    $fields = json_decode((string)$def['fields'], true);
    return is_array($fields) ? $fields : [];
}

/** 生成前台表单 HTML（dd8888 q- 风格） */
function form_render_html(array $def): string
{
    $fields = form_fields($def);
    $html = '<form class="q-form" method="post" action="index.php?act=form&id=' . (int)$def['id'] . '" onsubmit="return qFormCheck(this)">';
    $html .= '<input type="hidden" name="submit" value="1">';
    foreach ($fields as $f) {
        $label = $f['label'] ?? '';
        $name  = $f['name'] ?? '';
        $type  = $f['type'] ?? 'text';
        $req   = !empty($f['required']);
        $ph    = $f['placeholder'] ?? '';
        $opt   = $f['options'] ?? [];
        $reqMark = $req ? ' <i class="q-form__req">*</i>' : '';
        $html .= '<div class="q-form__item">';
        $html .= '<label class="q-form__label">' . e($label) . $reqMark . '</label>';
        $reqAttr = $req ? ' required' : '';
        switch ($type) {
            case 'textarea':
                $html .= '<textarea name="' . e($name) . '" placeholder="' . e($ph) . '"' . $reqAttr . ' rows="4"></textarea>';
                break;
            case 'select':
                $html .= '<select name="' . e($name) . '"' . $reqAttr . '>';
                $html .= '<option value="">请选择' . ($ph ? '：' . e($ph) : '') . '</option>';
                foreach ($opt as $o) {
                    $html .= '<option value="' . e($o) . '">' . e($o) . '</option>';
                }
                $html .= '</select>';
                break;
            case 'radio':
                $html .= '<div class="q-form__opts">';
                foreach ($opt as $o) {
                    $html .= '<label class="q-form__opt"><input type="radio" name="' . e($name) . '" value="' . e($o) . '"' . $reqAttr . '><span>' . e($o) . '</span></label>';
                }
                $html .= '</div>';
                break;
            case 'checkbox':
                $html .= '<div class="q-form__opts">';
                foreach ($opt as $o) {
                    $html .= '<label class="q-form__opt"><input type="checkbox" name="' . e($name) . '[]" value="' . e($o) . '"><span>' . e($o) . '</span></label>';
                }
                $html .= '</div>';
                break;
            case 'tel':
                $html .= '<input type="tel" name="' . e($name) . '" placeholder="' . e($ph) . '"' . $reqAttr . '>';
                break;
            case 'email':
                $html .= '<input type="email" name="' . e($name) . '" placeholder="' . e($ph) . '"' . $reqAttr . '>';
                break;
            case 'number':
                $html .= '<input type="number" name="' . e($name) . '" placeholder="' . e($ph) . '"' . $reqAttr . '>';
                break;
            case 'date':
                $html .= '<input type="date" name="' . e($name) . '"' . $reqAttr . '>';
                break;
            default: // text
                $html .= '<input type="text" name="' . e($name) . '" placeholder="' . e($ph) . '"' . $reqAttr . '>';
        }
        $html .= '</div>';
    }
    $html .= '<div class="q-form__item"><button type="submit" class="q-btn q-btn--grad">' . e($def['submit_text'] ?: '提交') . '</button></div>';
    $html .= '</form>';
    return $html;
}

/** HTML 转义 */
function e(?string $s): string
{
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

/** API Key 掩码：保留前缀 3 位 + 末尾 4 位，中间用 * 代替（便于确认已保存又不泄露明文） */
function mask_key(string $key): string
{
    $k = trim($key);
    $len = mb_strlen($k);
    if ($len <= 8) {
        return str_repeat('*', $len);
    }
    return mb_substr($k, 0, 3) . str_repeat('*', $len - 7) . mb_substr($k, -4);
}

/** 摘要截取 */
function cut(?string $s, int $len = 60): string
{
    $s = trim(strip_tags((string)$s));
    if (mb_strlen($s) <= $len) {
        return $s;
    }
    return mb_substr($s, 0, $len) . '…';
}

/** 分页 */
function paginate(string $sqlCount, string $sqlList, array $params, int $perPage = 10): array
{
    $total = (int)(DB::one($sqlCount, $params)['n'] ?? 0);
    $page  = max(1, (int)($_GET['page'] ?? 1));
    $pages = max(1, (int)ceil($total / $perPage));
    $page  = min($page, $pages);
    $list  = DB::all($sqlList . " LIMIT " . (($page - 1) * $perPage) . ", $perPage", $params);
    return ['list' => $list, 'total' => $total, 'page' => $page, 'pages' => $pages];
}

/** 跳转+提示 */
function redirect(string $url, string $msg = ''): void
{
    if ($msg !== '') {
        $_SESSION['flash'] = $msg;
    }
    header('Location: ' . $url);
    exit;
}

/** 读取一次性提示 */
function flash(): string
{
    $m = $_SESSION['flash'] ?? '';
    unset($_SESSION['flash']);
    return $m;
}

/** 栏目名查找（$cats 为全量数组） */
function cat_name(int $id, array $cats): string
{
    foreach ($cats as $c) {
        if ((int)$c['id'] === $id) {
            return $c['name'];
        }
    }
    return '-';
}

/** 安全处理上传图片（$index 指定时支持多文件 file[]；$folderId 指定所属文件夹） */
function handle_upload(string $field, ?int $index = null, int $folderId = 0): array
{
    if ($index === null) {
        $f = $_FILES[$field] ?? null;
        if (empty($f) || $f['error'] !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'msg' => '未选择文件或上传失败'];
        }
    } else {
        if (empty($_FILES[$field]['name'][$index]) || ($_FILES[$field]['error'][$index] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'msg' => '未选择文件或上传失败'];
        }
        $f = [
            'name' => $_FILES[$field]['name'][$index],
            'type' => $_FILES[$field]['type'][$index],
            'tmp_name' => $_FILES[$field]['tmp_name'][$index],
            'error' => $_FILES[$field]['error'][$index],
            'size' => $_FILES[$field]['size'][$index],
        ];
    }
    $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
    $allow = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp'];
    if (!in_array($ext, $allow, true)) {
        return ['ok' => false, 'msg' => '仅支持图片格式: ' . implode('/', $allow)];
    }
    if ($f['size'] > 10 * 1024 * 1024) {
        return ['ok' => false, 'msg' => '图片不能超过 10MB'];
    }
    $ym  = date('Ym');
    $sid = current_site_id();
    $dir = UPLOAD_DIR . 'site_' . $sid . '/' . $ym;
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    $name = date('His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $path = 'site_' . $sid . '/' . $ym . '/' . $name;
    if (!move_uploaded_file($f['tmp_name'], UPLOAD_DIR . $path)) {
        return ['ok' => false, 'msg' => '保存文件失败，请检查 uploads 目录权限'];
    }
    DB::insert('INSERT INTO uploads(site_id,folder_id,name,path,size,ext) VALUES(?,?,?,?,?,?)', [$sid, $folderId, $f['name'], $path, $f['size'], $ext]);
    return ['ok' => true, 'path' => UPLOAD_URL . $path, 'msg' => '上传成功'];
}

/**
 * 下载远程图片并入库（AI 生图等场景）：远程 URL → 存到 uploads/site_N/YYYYMM/ → 写 uploads 表 → 返回本地可访问 URL
 * 目的：不依赖第三方图片链接存活，图片落在自己服务器、进图片空间可复用。
 * @param string $remoteUrl  远程图片地址（ChatGPT/DALL·E 等返回）
 * @param string $sourceName 入库显示名（建议带关键词前缀）
 * @param int    $folderId   图片空间文件夹
 */
function save_remote_image(string $remoteUrl, string $sourceName = '', int $folderId = 0): array
{
    $sid = current_site_id();
    // SSRF 防护：仅允许公网 http/https，拒绝内网/环回/保留地址
    $scheme = strtolower((string) (parse_url($remoteUrl, PHP_URL_SCHEME) ?: ''));
    if (!in_array($scheme, ['http', 'https'], true) || is_ssrf_host($remoteUrl)) {
        return ['ok' => false, 'msg' => '远程图片地址不被允许（仅支持公网 http/https）'];
    }
    $ctx = stream_context_create(['http' => ['method' => 'GET', 'timeout' => 300, 'header' => "User-Agent: Mozilla/5.0\r\n"]]);
    $bin = @file_get_contents($remoteUrl, false, $ctx);
    if ($bin === false || strlen($bin) < 100) {
        return ['ok' => false, 'msg' => '下载远程图片失败'];
    }
    // 按内容类型校正扩展名
    $ext = 'jpg';
    $map = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp', 'image/bmp' => 'bmp', 'image/svg+xml' => 'svg'];
    if (isset($http_response_header)) {
        foreach ($http_response_header as $h) {
            if (stripos($h, 'content-type:') === 0) {
                $ct = trim(substr($h, 13));
                if (isset($map[$ct])) {
                    $ext = $map[$ct];
                }
                break;
            }
        }
    } else {
        $p = strtolower(pathinfo(parse_url($remoteUrl, PHP_URL_PATH) ?: '', PATHINFO_EXTENSION));
        if (in_array($p, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp'], true)) {
            $ext = $p;
        }
    }
    $ym  = date('Ym');
    $dir = UPLOAD_DIR . 'site_' . $sid . '/' . $ym;
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    $name = date('His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $path = 'site_' . $sid . '/' . $ym . '/' . $name;
    if (file_put_contents(UPLOAD_DIR . $path, $bin) === false) {
        return ['ok' => false, 'msg' => '保存远程图片失败，请检查 uploads 目录权限'];
    }
    $size = strlen($bin);
    DB::insert('INSERT INTO uploads(site_id,folder_id,name,path,size,ext) VALUES(?,?,?,?,?,?)', [$sid, $folderId, ($sourceName ?: 'remote') . '.' . $ext, $path, $size, $ext]);
    return ['ok' => true, 'path' => UPLOAD_URL . $path, 'msg' => '下载入库成功'];
}

/** ============ 数据库结构自愈（旧库自动升级） ============ */
function ensure_schema(): void
{
    // 0. admin_users 表自愈：表必须存在 + password 列必须是 VARCHAR(255)
    //    旧库若为 VARCHAR(32) 会导致 bcrypt(60字符) 被截断，password_verify 永远失败 → 登录报"用户名或密码错误"
    try {
        DB::one('SELECT id FROM admin_users LIMIT 1');
    } catch (Throwable $e) {
        DB::run("CREATE TABLE IF NOT EXISTS admin_users (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }
    try {
        DB::run('ALTER TABLE admin_users MODIFY COLUMN password VARCHAR(255) NOT NULL');
    } catch (Throwable $e) {
        // 列已是 VARCHAR(255) 则忽略
    }
    try {
        DB::run('ALTER TABLE admin_users MODIFY COLUMN username VARCHAR(50) NOT NULL');
    } catch (Throwable $e) {
    }
    // 1. users 表（保留兼容，不再用于注册）
    try {
        DB::one('SELECT id FROM users LIMIT 1');
    } catch (Throwable $e) {
        DB::run("CREATE TABLE IF NOT EXISTS users (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            email VARCHAR(100) DEFAULT '',
            phone VARCHAR(30) DEFAULT '',
            site_name VARCHAR(100) DEFAULT '',
            status TINYINT NOT NULL DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }
    // 2. 业务表加 site_id 列
    foreach (['categories', 'articles', 'products', 'uploads', 'folders', 'city_sites', 'form_defs', 'form_data'] as $t) {
        try {
            DB::one("SELECT site_id FROM $t LIMIT 1");
        } catch (Throwable $e) {
            try {
                DB::run("ALTER TABLE $t ADD COLUMN site_id INT UNSIGNED NOT NULL DEFAULT 0 AFTER id");
            } catch (Throwable $e2) {
                // 已有该列则忽略
            }
        }
    }
    // 3. settings 表 site_id + 复合唯一键
    try {
        DB::one('SELECT site_id FROM settings LIMIT 1');
    } catch (Throwable $e) {
        try {
            DB::run('ALTER TABLE settings ADD COLUMN site_id INT UNSIGNED NOT NULL DEFAULT 0 AFTER id');
        } catch (Throwable $e2) {
        }
        try {
            DB::run('ALTER TABLE settings DROP INDEX `key`');
        } catch (Throwable $e2) {
        }
        try {
            DB::run('ALTER TABLE settings ADD UNIQUE KEY uk_site_key (site_id, `key`)');
        } catch (Throwable $e2) {
        }
    }
    // 4. settings.value 升级为 TEXT（存量库 VARCHAR(2000) 会被长配置截断）
    try {
        DB::run('ALTER TABLE settings MODIFY COLUMN `value` TEXT');
    } catch (Throwable $e) {
    }
    // 5. AI 自动发文日志（防重复 + 已用关键词）
    try {
        DB::one('SELECT id FROM ai_post_log LIMIT 1');
    } catch (Throwable $e) {
        DB::run("CREATE TABLE IF NOT EXISTS ai_post_log (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            site_id INT UNSIGNED NOT NULL DEFAULT 0,
            keyword VARCHAR(120) NOT NULL DEFAULT '',
            model VARCHAR(60) NOT NULL DEFAULT '',
            has_image TINYINT NOT NULL DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_site_date (site_id, created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }
    // 5. 访问日志（IP / 地域 / 设备 / 页面 / 来源）
    try {
        DB::one('SELECT id FROM visits LIMIT 1');
    } catch (Throwable $e) {
        DB::run("CREATE TABLE IF NOT EXISTS visits (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            site_id INT UNSIGNED NOT NULL DEFAULT 0,
            ip VARCHAR(50) NOT NULL DEFAULT '',
            province VARCHAR(60) NOT NULL DEFAULT '',
            city VARCHAR(60) NOT NULL DEFAULT '',
            user_agent TEXT,
            device ENUM('mobile','desktop','tablet','unknown') NOT NULL DEFAULT 'unknown',
            source VARCHAR(120) NOT NULL DEFAULT '',
            page VARCHAR(255) NOT NULL DEFAULT '',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_site_ip (site_id, ip),
            INDEX idx_site_date (site_id, created_at),
            INDEX idx_site_device (site_id, device),
            INDEX idx_site_city (site_id, city)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }
    // 6. visits.page 列扩容到 VARCHAR(500)（旧库 VARCHAR(255) 易被超长 URL 撑爆崩溃）
    try {
        DB::run('ALTER TABLE visits MODIFY COLUMN page VARCHAR(500) NOT NULL');
    } catch (Throwable $e) {
        // 列已是 VARCHAR(500) 则忽略
    }
    // 6. 表单数据补充地域字段
    foreach (['province','city'] as $col) {
        try {
            DB::one("SELECT $col FROM form_data LIMIT 1");
        } catch (Throwable $e) {
            try {
                DB::run("ALTER TABLE form_data ADD COLUMN $col VARCHAR(60) NOT NULL DEFAULT '' AFTER ip");
            } catch (Throwable $e2) {}
        }
    }
    // 7. 文章 / 产品 增加 GEO 字段（AI 优化后的要点化描述 + FAQ 结构化）
    foreach (['articles' => 'article', 'products' => 'product'] as $t => $type) {
        foreach (['geo_summary' => "TEXT", 'geo_faq' => "MEDIUMTEXT"] as $col => $def) {
            try {
                DB::one("SELECT $col FROM $t LIMIT 1");
            } catch (Throwable $e) {
                try {
                    DB::run("ALTER TABLE $t ADD COLUMN $col $def AFTER seo_description");
                } catch (Throwable $e2) {}
            }
        }
    }
    // 8. GEO 词条库（可复用问答资产，支持一键同步为文章）
    try {
        DB::one('SELECT id FROM geo_entries LIMIT 1');
    } catch (Throwable $e) {
        DB::run("CREATE TABLE IF NOT EXISTS geo_entries (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            site_id INT UNSIGNED NOT NULL DEFAULT 0,
            cat_id INT UNSIGNED NOT NULL DEFAULT 0,
            topic VARCHAR(160) NOT NULL DEFAULT '',
            question VARCHAR(255) NOT NULL DEFAULT '',
            answer MEDIUMTEXT NOT NULL,
            advert MEDIUMTEXT NOT NULL,
            keywords VARCHAR(255) NOT NULL DEFAULT '',
            status TINYINT NOT NULL DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_site (site_id),
            INDEX idx_topic (site_id, topic(50))
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }
    // 8.1 旧版 geo_entries 兼容增加 advert 字段
    foreach (['advert' => "MEDIUMTEXT"] as $col => $def) {
        try {
            DB::one("SELECT $col FROM geo_entries LIMIT 1");
        } catch (Throwable $e) {
            try {
                DB::run("ALTER TABLE geo_entries ADD COLUMN $col $def AFTER answer");
            } catch (Throwable $e2) {}
        }
    }
    // 10. GEO 检测回流：主动探针记录（AI 引擎是否引用本站）
    try {
        DB::one('SELECT id FROM geo_monitor LIMIT 1');
    } catch (Throwable $e) {
        DB::run("CREATE TABLE IF NOT EXISTS geo_monitor (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            site_id INT UNSIGNED NOT NULL DEFAULT 0,
            article_id INT UNSIGNED NOT NULL DEFAULT 0,
            platform VARCHAR(40) NOT NULL DEFAULT '',
            question VARCHAR(255) NOT NULL DEFAULT '',
            answer_snippet TEXT,
            cited TINYINT NOT NULL DEFAULT 0,
            checked_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_site_article (site_id, article_id),
            INDEX idx_site_checked (site_id, checked_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }
    // 10.1 geo_monitor 设置项默认落库（站点域名 / 是否启用自动探针 / 每日上限）
    foreach (['site_url' => '', 'geo_monitor_on' => '0', 'geo_monitor_perday' => '5'] as $k => $v) {
        $exists = DB::one('SELECT `value` FROM settings WHERE site_id=? AND `key`=?', [current_site_id(), $k]);
        if (!$exists) {
            DB::run("INSERT IGNORE INTO settings(site_id,`key`,`value`) VALUES(?,?,?)", [current_site_id(), $k, $v]);
        }
    }
    // 10.2 geo_monitor 增加 kind（self/competitor/negative）与 target（竞品域名 / 负面查询）
    foreach (['kind' => "VARCHAR(16) NOT NULL DEFAULT 'self'", 'target' => "VARCHAR(160) NOT NULL DEFAULT ''"] as $col => $def) {
        try {
            DB::one("SELECT $col FROM geo_monitor LIMIT 1");
        } catch (Throwable $e) {
            try {
                DB::run("ALTER TABLE geo_monitor ADD COLUMN $col $def AFTER cited");
            } catch (Throwable $e2) {
            }
        }
    }

    // 11. GEO 关键词蒸馏库（AI 产出 GEO 关键词，可一键投喂自动发文计划）
    try {
        DB::one('SELECT id FROM geo_keywords LIMIT 1');
    } catch (Throwable $e) {
        DB::run("CREATE TABLE IF NOT EXISTS geo_keywords (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            site_id INT UNSIGNED NOT NULL DEFAULT 0,
            topic VARCHAR(160) NOT NULL DEFAULT '',
            intent VARCHAR(20) NOT NULL DEFAULT '',
            kw VARCHAR(120) NOT NULL DEFAULT '',
            source VARCHAR(20) NOT NULL DEFAULT 'ai',
            used TINYINT NOT NULL DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_site (site_id),
            INDEX idx_topic (site_id, topic(50))
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    // 12. GEO 品牌资料库（生成内容时自动注入真实品牌信息，避免 AI 编造）
    try {
        DB::one('SELECT id FROM geo_kb LIMIT 1');
    } catch (Throwable $e) {
        DB::run("CREATE TABLE IF NOT EXISTS geo_kb (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            site_id INT UNSIGNED NOT NULL DEFAULT 0,
            title VARCHAR(200) NOT NULL DEFAULT '',
            content MEDIUMTEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_site (site_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    // 13. GEO 中心默认设置（知识库注入字数 / 竞品 / 负面词）
    foreach (['geo_kb_words' => '1200', 'geo_competitors' => '', 'geo_negative' => ''] as $k => $v) {
        $exists = DB::one('SELECT `value` FROM settings WHERE site_id=? AND `key`=?', [current_site_id(), $k]);
        if (!$exists) {
            DB::run("INSERT IGNORE INTO settings(site_id,`key`,`value`) VALUES(?,?,?)", [current_site_id(), $k, $v]);
        }
    }

    // 9. 下载专区（可分类、可下载源码）
    try {
        DB::one('SELECT id FROM downloads LIMIT 1');
    } catch (Throwable $e) {
        DB::run("CREATE TABLE IF NOT EXISTS downloads (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            site_id INT UNSIGNED NOT NULL DEFAULT 0,
            cat_id INT UNSIGNED NOT NULL DEFAULT 0,
            title VARCHAR(200) NOT NULL DEFAULT '',
            cover VARCHAR(500) NOT NULL DEFAULT '',
            file_url VARCHAR(800) NOT NULL DEFAULT '',
            file_name VARCHAR(300) NOT NULL DEFAULT '',
            file_ext VARCHAR(20) NOT NULL DEFAULT '',
            file_size VARCHAR(30) NOT NULL DEFAULT '',
            version VARCHAR(60) NOT NULL DEFAULT '',
            summary VARCHAR(500) NOT NULL DEFAULT '',
            description MEDIUMTEXT,
            downloads INT UNSIGNED NOT NULL DEFAULT 0,
            sort INT NOT NULL DEFAULT 0,
            status TINYINT NOT NULL DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_site (site_id),
            INDEX idx_cat (site_id, cat_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }
}

/* =========================================================
 *  GEO / SEO 一体化 AI 能力
 *  - 复用 ai_chat()（DeepSeek / 任意 OpenAI 协议）
 *  - 不联网，仅基于站内已有内容做"形态改写 + 结构化"
 *  - 一次结构化同时服务 SEO（富摘要）与 GEO（AI 引用事实源）
 * ========================================================= */

/**
 * AI GEO 优化：把一篇已有文章/产品改写成「结论先行 + 要点化 + FAQ」
 * 返回 ['ok'=>bool,'summary'=>str,'faq'=>[['q'=>..,'a'=>..],..],'msg'=>str]
 * @param string $advert 站点商家广告/网站主体介绍，会自然融入 FAQ 回答
 */
function ai_geo_optimize(string $title, string $content, string $type = 'article', string $advert = ''): array
{
    $apiUrl = setting('ai_api_url', '');
    $apiKey = setting('ai_api_key', '');
    $model  = setting('ai_model', 'deepseek-chat');
    if ($apiUrl === '' || $apiKey === '') {
        return ['ok' => false, 'msg' => '未配置 API（请到「API 配置」板块填写 DeepSeek 地址与 Key）'];
    }
    $plain = preg_replace('/<[^>]+>/', ' ', strip_tags($content));
    $plain = mb_substr(preg_replace('/\s+/', ' ', $plain), 0, 1200);
    $what  = $type === 'product' ? '产品' : '文章';
    $prompt = "你是一名资深的生成式引擎优化（GEO）专家。下面是一篇{$what}的标题与正文片段。\n"
        . "标题：《{$title}》\n正文：{$plain}\n"
        . "请输出 JSON（不要代码块包裹），结构如下：\n"
        . "{\"summary\":\"用 2-3 句话结论先行地概括核心信息，要点化、可被 AI 直接引用\",\""
        . "faq\":[{\"q\":\"用户常问的问题\",\"a\":\"简洁权威的回答，1-2 句，纯客观、禁止品牌名/推销词/绝对化用语\"}]}\n"
        . "要求：faq 3-5 条，问题要像真实用户搜索/提问的口吻；回答要事实化、避免营销腔、不得植入任何商家信息；"
        . "实体名称与标题保持一致。只输出 JSON。";
    $raw = ai_chat($apiUrl, $apiKey, $model, $prompt, 1400);
    if ($raw === null) {
        return ['ok' => false, 'msg' => 'AI 调用失败（检查 API 地址/Key/网络）'];
    }
    // 容错：剥离可能的 ```json 包裹
    $raw = preg_replace('/^```(?:json)?/i', '', trim($raw));
    $raw = preg_replace('/```$/', '', trim($raw));
    $json = json_decode(trim($raw), true);
    if (!is_array($json) || empty($json['summary'])) {
        return ['ok' => false, 'msg' => 'AI 返回格式异常，请重试'];
    }
    $faq = [];
    if (!empty($json['faq']) && is_array($json['faq'])) {
        foreach ($json['faq'] as $it) {
            if (!empty($it['q']) && !empty($it['a'])) {
                $faq[] = ['q' => $it['q'], 'a' => $it['a']];
            }
        }
    }
    return ['ok' => true, 'summary' => $json['summary'], 'faq' => $faq];
}

/**
 * AI SEO 自动填写：基于标题与正文生成 seo_title / seo_keywords / seo_description
 */
function ai_seo_fill(string $title, string $content): array
{
    $apiUrl = setting('ai_api_url', '');
    $apiKey = setting('ai_api_key', '');
    $model  = setting('ai_model', 'deepseek-chat');
    if ($apiUrl === '' || $apiKey === '') {
        return ['ok' => false, 'msg' => '未配置 API（请到「API 配置」板块填写）'];
    }
    $plain = mb_substr(preg_replace('/\s+/', ' ', preg_replace('/<[^>]+>/', ' ', strip_tags($content))), 0, 800);
    $prompt = "你是 SEO 专家。根据标题与正文生成搜索引擎优化三要素，输出 JSON（不要代码块）：\n"
        . "{\"seo_title\":\"不超过 30 字、包含核心关键词的标题\",\"seo_keywords\":\"5-8 个逗号分隔的关键词\",\""
        . "seo_description\":\"不超过 90 字、吸引点击的摘要\"}\n"
        . "标题：《{$title}》\n正文：{$plain}\n只输出 JSON。";
    $raw = ai_chat($apiUrl, $apiKey, $model, $prompt, 600);
    if ($raw === null) {
        return ['ok' => false, 'msg' => 'AI 调用失败'];
    }
    $raw = preg_replace('/^```(?:json)?/i', '', trim($raw));
    $raw = preg_replace('/```$/', '', trim($raw));
    $json = json_decode(trim($raw), true);
    if (!is_array($json)) {
        return ['ok' => false, 'msg' => 'AI 返回格式异常'];
    }
    return [
        'ok'            => true,
        'seo_title'     => $json['seo_title'] ?? '',
        'seo_keywords'  => $json['seo_keywords'] ?? '',
        'seo_description'=> $json['seo_description'] ?? '',
    ];
}

/**
 * GEO 全站健康诊断（基础版，不联网）：扫描结构化数据覆盖、实体一致性、问答覆盖
 * 返回 ['score'=>int,'grade'=>str,'items'=>[['name','status','tip']],'counts'=>[...]]
 */
function geo_audit_site(int $sid): array
{
    $artTotal = (int)DB::one('SELECT COUNT(*) AS n FROM articles WHERE site_id=?', [$sid])['n'];
    $artGeo   = (int)DB::one('SELECT COUNT(*) AS n FROM articles WHERE site_id=? AND geo_faq<>"" AND geo_faq IS NOT NULL', [$sid])['n'];
    $proTotal = (int)DB::one('SELECT COUNT(*) AS n FROM products WHERE site_id=?', [$sid])['n'];
    $proGeo   = (int)DB::one('SELECT COUNT(*) AS n FROM products WHERE site_id=? AND geo_faq<>"" AND geo_faq IS NOT NULL', [$sid])['n'];
    $seoDone  = (int)DB::one('SELECT COUNT(*) AS n FROM articles WHERE site_id=? AND seo_description<>"" AND seo_description IS NOT NULL', [$sid])['n'];

    $items = [];
    $cov = $artTotal > 0 ? round($artGeo / $artTotal * 100) : 0;
    $items[] = ['name' => '结构化数据覆盖', 'status' => $cov >= 60 ? 'ok' : ($cov >= 30 ? 'warn' : 'bad'),
                'tip' => "文章 GEO 结构化覆盖 {$cov}%（{$artGeo}/{$artTotal}）"];
    $proCov = $proTotal > 0 ? round($proGeo / $proTotal * 100) : 0;
    $items[] = ['name' => '产品结构化覆盖', 'status' => $proCov >= 60 ? 'ok' : ($proCov >= 30 ? 'warn' : 'bad'),
                'tip' => "产品 GEO 结构化覆盖 {$proCov}%（{$proGeo}/{$proTotal}）"];
    $items[] = ['name' => 'SEO 三要素填写', 'status' => $seoDone > 0 ? 'ok' : 'warn',
                'tip' => $seoDone > 0 ? "已为 {$seoDone} 篇文章填写 SEO 描述" : '建议用「🤖 SEO 自动填写」补全'];
    $items[] = ['name' => '实体一致性', 'status' => 'warn',
                'tip' => '请保持工厂名/品牌名全文统一（AI 引用时避免被当作不同主体）'];
    $items[] = ['name' => '问答覆盖', 'status' => $artGeo > 0 ? 'ok' : 'bad',
                'tip' => $artGeo > 0 ? "已有 {$artGeo} 篇带 FAQ" : '核心文章缺少 FAQPage，AI 难抽取'];

    $score = round(($cov + $proCov + ($seoDone > 0 ? 100 : 0) + 60 + ($artGeo > 0 ? 100 : 0)) / 5);
    $grade = $score >= 80 ? 'A（优秀）' : ($score >= 60 ? 'B（良好）' : ($score >= 40 ? 'C（待优化）' : 'D（薄弱）'));
    return ['score' => $score, 'grade' => $grade, 'items' => $items,
            'counts' => ['art_total' => $artTotal, 'art_geo' => $artGeo, 'pro_total' => $proTotal, 'pro_geo' => $proGeo]];
}

/**
 * 读取站点级 GEO 商家广告/网站主体介绍
 */
function geo_advert(?int $siteId = null): string
{
    return setting('geo_advert', '', $siteId);
}

/**
 * 生成 GEO 词条（基于主题/已有内容），写入 geo_entries 或返回结果
 * @param string $advert 站点商家广告/网站主体介绍，会自然融入 answer
 */
function geo_build_entry(string $topic, string $context = '', int $sid = 0, int $catId = 0, string $advert = ''): array
{
    $apiUrl = setting('ai_api_url', '');
    $apiKey = setting('ai_api_key', '');
    $model  = setting('ai_model', 'deepseek-chat');
    if ($apiUrl === '' || $apiKey === '') {
        return ['ok' => false, 'msg' => '未配置 API'];
    }
    $ctx = $context !== '' ? '参考素材：' . mb_substr($context, 0, 600) . "\n" : '';
    $prompt = "你是 GEO 内容专家。围绕主题《{$topic}》产出 1 组可被 AI 引擎直接引用的问答资产。\n"
        . "回答要求：必须完全客观、事实化、对用户有独立价值，像百科/科普条目；\n"
        . "禁止出现任何品牌名、商家名、联系方式、价格或购买引导；\n"
        . "禁止使用「若您需要/我们提供/可帮助您/高效完成」等推销模板词；\n"
        . "禁止使用「第一/领先/最优/最全/首选」等绝对化用语；\n"
        . "不要把商家介绍写进回答——商家信息由系统在文末以独立「关于我们」区块展示，你只需产出纯客观答案。\n"
        . "{$ctx}"
        . "输出 JSON：{\"question\":\"用户真实会问的问题\",\"answer\":\"纯客观回答，不含任何商业信息\",\"keywords\":\"5 个逗号分隔关键词\"}\n"
        . "只输出 JSON。";
    $raw = ai_chat($apiUrl, $apiKey, $model, $prompt, 700);
    if ($raw === null) {
        return ['ok' => false, 'msg' => 'AI 调用失败'];
    }
    $raw = preg_replace('/^```(?:json)?/i', '', trim($raw));
    $raw = preg_replace('/```$/', '', trim($raw));
    $json = json_decode(trim($raw), true);
    if (!is_array($json) || empty($json['question'])) {
        return ['ok' => false, 'msg' => 'AI 返回格式异常'];
    }
    $storedAdvert = $advert !== '' ? $advert : '';
    // 单站点模式下 current_site_id() 返回 0，仍需要写入；site_id>=0 即可入库
    if ($sid >= 0) {
        DB::insert('INSERT INTO geo_entries(site_id,cat_id,topic,question,answer,advert,keywords,status) VALUES(?,?,?,?,?,?,?,1)',
            [$sid, $catId, $topic, $json['question'], $json['answer'], $storedAdvert, $json['keywords'] ?? '']);
    }
    return ['ok' => true, 'question' => $json['question'], 'answer' => $json['answer'], 'keywords' => $json['keywords'] ?? ''];
}

/**
 * 批量生成 GEO 词条（一次处理多个主题）
 * @param array $topics 主题文本数组
 * @param int $catId 默认栏目
 * @param int $sid 站点ID
 * @param string $advert 站点商家广告
 * @return array ['ok'=>bool,'total'=>int,'success'=>int,'failed'=>int,'log'=>[...]]
 */
function geo_build_entries_batch(array $topics, int $catId = 0, int $sid = 0, string $advert = '', string $kbContext = ''): array
{
    $ok = 0;
    $fail = 0;
    $log = [];
    foreach ($topics as $topic) {
        $topic = trim((string)$topic);
        if ($topic === '') continue;
        $r = geo_build_entry($topic, $kbContext, $sid, $catId, $advert);
        if ($r['ok']) {
            $ok++;
            $log[] = ['topic' => $topic, 'status' => 'ok', 'question' => $r['question'] ?? ''];
        } else {
            $fail++;
            $log[] = ['topic' => $topic, 'status' => 'fail', 'msg' => $r['msg'] ?? '失败'];
        }
    }
    return [
        'ok' => $ok > 0,
        'total' => $ok + $fail,
        'success' => $ok,
        'failed' => $fail,
        'log' => $log,
    ];
}


/* =========================================================
 *  AI 写作能力（DeepSeek 写文 + ChatGPT/DALL·E 生图）
 *  敏感词：违禁 / 政治 / 低俗 / 暴力 四类，命中米字代替
 * ========================================================= */

/** 敏感词库（实装建议补充更全的词表；此处为示例种子词） */
function ai_sensitive_words(): array
{
    return [
        // 违禁 / 违法
        '网络赌博', '赌博', '色情', '暴力', '毒品', '诈骗', '私彩', '博彩', '洗钱',
        // 政治敏感（示例，实装请按合规要求补全）
        '反政府', '颠覆国家', '台独', '港独', '疆独', '藏独', '法轮功',
        // 低俗
        '约炮', '裸聊', '包养', '性爱',
        // 暴力
        '杀人', '枪击', '爆炸', '恐怖袭击',
    ];
}

/**
 * 敏感词过滤：命中用 * 代替（按词长度等宽替换，最小 1 个 *）
 * @return array ['out'=>过滤后文本,'hit'=>命中次数,'block'=>是否命中高危拦截词]
 */
function ai_censor(string $text): array
{
    $hit = 0;
    $block = false;
    $blockWords = ['反政府', '颠覆国家', '台独', '港独', '疆独', '藏独', '法轮功']; // 高危：直接拦截
    foreach (ai_sensitive_words() as $w) {
        if (mb_strpos($text, $w) !== false) {
            if (in_array($w, $blockWords, true)) {
                $block = true;
            }
            $stars = str_repeat('*', max(1, mb_strlen($w)));
            $text = str_replace($w, $stars, $text);
            $hit++;
        }
    }
    return ['out' => $text, 'hit' => $hit, 'block' => $block];
}

/** 调用 OpenAI 协议兼容接口（DeepSeek / ChatGPT 通用），返回文本内容或 null */
/**
 * 拉取 OpenAI 兼容接口下的可用模型列表（调用 /v1/models）
 * 返回诊断数组：
 *   ['ok'=>true,  'models'=>[...]           ]
 *   ['ok'=>false, 'msg'=>诊断信息, 'http'=>状态码, 'sample'=>响应片段]
 * 优先用 curl（多数主机可用且不受 allow_url_fopen 限制），回退 file_get_contents。
 */
function ai_list_models(string $apiUrl, string $apiKey): array
{
    $base = rtrim($apiUrl, '/');
    $base = preg_replace('#/chat/completions$#', '', $base);
    $baseNoV1 = preg_replace('#/v1$#', '', $base);
    if ($baseNoV1 === $base) {           // 原地址没带 /v1
        $base .= '/v1';
    }
    // 依次尝试标准 /v1/models 与去 v1 兜底的 /models
    $eps = array_values(array_unique([$base . '/models', $baseNoV1 . '/models']));

    foreach ($eps as $ep) {
        $httpCode = 0;
        $resp     = ai_http_get($ep, $apiKey, $httpCode);
        if ($resp === null) {
            // 连请求都发不出去（curl/file_get_contents 均不可用或被禁用）
            return ['ok' => false, 'msg' => '无法发起网络请求：服务器禁用了 curl 与 allow_url_fopen，请检查 php.ini', 'http' => 0, 'sample' => ''];
        }
        $json = json_decode($resp, true);
        // 结构正常且含 data 数组
        if (is_array($json) && isset($json['data']) && is_array($json['data'])) {
            $ids = [];
            foreach ($json['data'] as $m) {
                $id = $m['id'] ?? $m['name'] ?? $m['model'] ?? null;
                if (is_string($id) && $id !== '') {
                    $ids[] = $id;
                }
            }
            if (!empty($ids)) {
                $ids = array_values(array_unique($ids));
                sort($ids);
                return ['ok' => true, 'models' => $ids];
            }
        }
        // 拿到响应但没解析出模型：记录首个端点的诊断，继续试下一个端点
        if ($httpCode !== 0 && $httpCode !== 200) {
            $sample = is_string($resp) ? mb_substr($resp, 0, 200) : '';
            return ['ok' => false, 'msg' => "接口返回 HTTP $httpCode，未取到模型列表", 'http' => $httpCode, 'sample' => $sample];
        }
    }
    return ['ok' => false, 'msg' => '接口未返回模型（需为 OpenAI 兼容的 /v1/models；部分第三方不提供该端点）', 'http' => $httpCode, 'sample' => isset($sample) ? $sample : ''];
}

/** GET 请求，优先 curl，回退 file_get_contents；返回响应体或 null（完全发不出去时） */
function ai_http_get(string $url, string $apiKey, int &$httpCode): ?string
{
    $httpCode = 0;
    $headers  = ["Authorization: Bearer $apiKey", 'Accept: application/json'];

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HEADER         => false,
        ]);
        $body = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $errno = curl_errno($ch);
        curl_close($ch);
        if ($body === false && $errno !== 0) {
            return null;
        }
        return $body === false ? '' : $body;
    }

    if (ini_get('allow_url_fopen')) {
        $ctx = stream_context_create([
            'http' => [
                'method'       => 'GET',
                'header'       => "Authorization: Bearer $apiKey\r\nAccept: application/json\r\n",
                'timeout'      => 20,
                'ignore_errors' => true,
            ],
        ]);
        $body = @file_get_contents($url, false, $ctx);
        if ($body === false) {
            return null;
        }
        // 从响应头粗略取状态码
        if (isset($http_response_header)) {
            foreach ($http_response_header as $h) {
                if (preg_match('#^HTTP/\S+\s+(\d{3})#', $h, $mm)) {
                    $httpCode = (int)$mm[1];
                    break;
                }
            }
        }
        return $body;
    }

    return null; // curl / file_get_contents 均不可用
}

/** POST JSON 请求，优先 curl，回退 file_get_contents；返回响应体或 null（发不出去时） */
function ai_http_post(string $url, string $apiKey, string $body, int &$httpCode, int $timeout = 120): ?string
{
    $httpCode = 0;
    $headers  = ['Content-Type: application/json', "Authorization: Bearer $apiKey", 'Accept: application/json'];

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_FOLLOWLOCATION => true,
        ]);
        $resp = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $errno = curl_errno($ch);
        curl_close($ch);
        if ($resp === false && $errno !== 0) {
            return null;
        }
        return $resp === false ? '' : $resp;
    }

    if (ini_get('allow_url_fopen')) {
        $ctx = stream_context_create([
            'http' => [
                'method'  => 'POST',
                'header'  => "Content-Type: application/json\r\nAuthorization: Bearer $apiKey\r\n",
                'content' => $body,
                'timeout' => $timeout,
            ],
        ]);
        $resp = @file_get_contents($url, false, $ctx);
        if ($resp === false) {
            return null;
        }
        if (isset($http_response_header)) {
            foreach ($http_response_header as $h) {
                if (preg_match('#^HTTP/\S+\s+(\d{3})#', $h, $mm)) {
                    $httpCode = (int)$mm[1];
                    break;
                }
            }
        }
        return $resp;
    }

    return null;
}

function ai_chat(string $apiUrl, string $apiKey, string $model, string $prompt, int $maxTokens = 1500): ?string
{
    $apiUrl = rtrim($apiUrl, '/');
    if (!preg_match('#/chat/completions$#', $apiUrl)) {
        $apiUrl .= '/chat/completions';
    }
    $body = json_encode([
        'model' => $model,
        'messages' => [['role' => 'user', 'content' => $prompt]],
        'max_tokens' => $maxTokens,
        'temperature' => 0.8,
    ], JSON_UNESCAPED_UNICODE);
    $httpCode = 0;
    $resp = ai_http_post($apiUrl, $apiKey, $body, $httpCode);
    if ($resp === null || $resp === '') {
        return null;
    }
    $json = json_decode($resp, true);
    if (!is_array($json) || empty($json['choices'][0]['message']['content'])) {
        return null;
    }
    return trim($json['choices'][0]['message']['content']);
}

/** 把 AI 生图返回的 base64 解码落库（gpt-image-1 等返回 b64_json 的场景） */
function ai_image_save_b64(string $b64, string $sourceName = ''): array
{
    $bin = base64_decode($b64, true);
    if ($bin === false || strlen($bin) < 100) {
        return ['ok' => false, 'msg' => 'base64 解码失败'];
    }
    $ext = 'png';
    $sig = substr($bin, 0, 12);
    if (strncmp($sig, "\x89PNG", 4) === 0) {
        $ext = 'png';
    } elseif (strncmp($sig, 'GIF', 3) === 0) {
        $ext = 'gif';
    } elseif (strncmp($sig, "\xFF\xD8\xFF", 3) === 0) {
        $ext = 'jpg';
    } elseif (strncmp($sig, 'RIFF', 4) === 0 && stripos($sig, 'WEBP') !== false) {
        $ext = 'webp';
    } elseif (function_exists('finfo_buffer')) {
        $m = finfo_buffer(finfo_open(FILEINFO_MIME_TYPE), $bin);
        $map = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp', 'image/bmp' => 'bmp'];
        if (isset($map[$m])) {
            $ext = $map[$m];
        }
    }
    $sid = current_site_id();
    $ym  = date('Ym');
    $dir = UPLOAD_DIR . 'site_' . $sid . '/' . $ym;
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    $name = date('His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $path = 'site_' . $sid . '/' . $ym . '/' . $name;
    if (file_put_contents(UPLOAD_DIR . $path, $bin) === false) {
        return ['ok' => false, 'msg' => '保存图片失败，请检查 uploads 目录权限'];
    }
    $size = strlen($bin);
    DB::insert('INSERT INTO uploads(site_id,folder_id,name,path,size,ext) VALUES(?,?,?,?,?,?)', [$sid, 0, ($sourceName ?: 'remote') . '.' . $ext, $path, $size, $ext]);
    return ['ok' => true, 'path' => UPLOAD_URL . $path, 'msg' => '保存成功'];
}

/** 调用 DALL·E / gpt-image-1 等 OpenAI 兼容生图接口，下载/解码后落库，返回本地可访问 URL 或具体失败原因 */
function ai_image(string $apiUrl, string $apiKey, string $model, string $prompt): array
{
    $fail = static function (string $msg): array { return ['ok' => false, 'path' => null, 'msg' => $msg]; };
    $apiUrl = rtrim($apiUrl, '/');
    if (!preg_match('#/images/generations$#', $apiUrl)) {
        $apiUrl .= '/images/generations';
    }
    $body = json_encode([
        'model' => $model,
        'prompt' => $prompt,
        'n' => 1,
        'size' => '1024x1024',
    ], JSON_UNESCAPED_UNICODE);
    $httpCode = 0;
    // 生图等待放宽到 10 分钟（gpt 生图慢是常态），写文等其它调用仍用默认 120s
    $resp = ai_http_post($apiUrl, $apiKey, $body, $httpCode, 600);
    if ($resp === null || $resp === '') {
        return $fail("生图接口请求失败（HTTP {$httpCode}，网络/证书/超时，请检查生图地址与服务器网络）");
    }
    if ($httpCode >= 400) {
        $detail = '';
        $j = json_decode($resp, true);
        if (is_array($j) && !empty($j['error']['message'])) {
            $detail = '：' . mb_substr((string)$j['error']['message'], 0, 120);
        }
        return $fail("生图接口返回 HTTP {$httpCode}{$detail}");
    }
    $json = json_decode($resp, true);
    if (!is_array($json) || empty($json['data'][0])) {
        return $fail('生图接口返回格式异常（无 data[0]）——请确认该服务支持 /images/generations 生图接口（DeepSeek 不支持生图）');
    }
    $item = $json['data'][0];
    $name = 'ai_' . mb_substr(preg_replace('/\s+/', '', $prompt), 0, 20);
    // DALL·E-3 等返回远程 URL：必须下载到本地图片空间（远程 URL 会失效，花了钱不能白花）
    if (!empty($item['url'])) {
        $saved = save_remote_image($item['url'], $name);
        if ($saved['ok']) {
            return ['ok' => true, 'path' => $saved['path'], 'msg' => ''];
        }
        return $fail('图片下载失败（图必须本地保存）：' . $saved['msg']);
    }
    // gpt-image-1 等返回 base64：直接解码落库
    if (!empty($item['b64_json'])) {
        $saved = ai_image_save_b64($item['b64_json'], $name);
        return $saved['ok'] ? ['ok' => true, 'path' => $saved['path'], 'msg' => ''] : $fail('图片保存失败：' . $saved['msg']);
    }
    return $fail('生图接口返回的 data[0] 中既无 url 也无 b64_json，格式不兼容');
}

/**
 * 生成一篇 AI 文章（含可选插图），返回结构化结果
 */
function ai_build_article(string $topic, int $words, string $tone, string $extra, bool $withImg, bool $doSeo = true, bool $doGeo = true): array
{
    // 写文 + 2 次生图（单次最多 120s）+ SEO/GEO 一起可能 > 30s 默认 max_execution_time，
    // 进程被 kill 会导致浏览器 fetch 报 "Failed to fetch"。本函数内放开限制。
    // 兼容 disable_functions：先 ini_set（一般不被禁），fallback set_time_limit，并加 function_exists 守卫
    if (function_exists('ini_set')) { @ini_set('max_execution_time', '0'); }
    if (function_exists('set_time_limit')) { @set_time_limit(0); }
    $apiUrl = setting('ai_api_url', '');
    $apiKey = setting('ai_api_key', '');
    $model  = setting('ai_model', 'deepseek-chat');
    if ($apiUrl === '' || $apiKey === '') {
        return ['ok' => false, 'msg' => '未配置写作 API（地址/Key）'];
    }
    $prompt = "请围绕主题《{$topic}》写一篇约 {$words} 字的中文文章，语气：{$tone}。"
        . "结构：标题 + 3 个小标题分段 + 小结。内容务实、可落地、避免空话。"
        . ($extra !== '' ? " 补充要求：{$extra}" : '')
        . " 输出格式：第一行必须是文章标题，格式为\"标题：xxx\"；从第二行开始输出正文，段落之间空一行。不要输出 Markdown 标记、不要输出标题行以外的标记。";
    $raw = ai_chat($apiUrl, $apiKey, $model, $prompt, (int)($words * 1.6));
    if ($raw === null) {
        return ['ok' => false, 'msg' => 'AI 写文调用失败（检查 API 地址/Key/网络）'];
    }
    $c = ai_censor($raw);
    if ($c['block']) {
        return ['ok' => false, 'msg' => '命中高危敏感词，已拦截', 'hit' => $c['hit']];
    }

    // 从 AI 输出中提取真实标题（优先匹配"标题：xxx"，兼容 Markdown 加粗 / # 标题）
    $title = '';
    $body = $c['out'];
    if (preg_match('/^(?:\*\*?)?标题[：:]\s*(.+?)(?:\*\*?)?\s*(?:\r?\n)+/ium', $body, $m)) {
        $title = trim($m[1]);
        $body = preg_replace('/^(?:\*\*?)?标题[：:]\s*.+?(?:\*\*?)?\s*(?:\r?\n)+/ium', '', $body, 1);
    } elseif (preg_match('/^#\s*(.+?)\s*(?:\r?\n)+/um', $body, $m)) {
        $title = trim($m[1]);
        $body = preg_replace('/^#\s*.+?\s*(?:\r?\n)+/um', '', $body, 1);
    }
    if ($title === '') {
        $title = $topic . '（实战指南）';
    }
    $c['out'] = ltrim($body);

    $content = nl2br($c['out']);
    $cover = '';
    $imgCount = 0;
    $imgErr = '';
    if ($withImg) {
        $imgUrl = setting('ai_img_url', '');
        $imgKey = setting('ai_img_key', '');
        $imgModel = setting('ai_img_model', 'dall-e-3');
        if ($imgUrl === '' || $imgKey === '') {
            $imgErr = '未配置生图 API（生图地址/Key 为空）——写作 API 不能生图，需在「API 配置」单独填写生图地址/Key/模型';
        } else {
            $gist = mb_substr(preg_replace('/[\r\n]+/', '。', $c['out']), 0, 60);
            for ($i = 0; $i < 2; $i++) {
                $r = ai_image($imgUrl, $imgKey, $imgModel, "为一篇关于「{$topic}」的中文文章配一张写实风格配图，主题：{$gist}，第" . ($i + 1) . '张');
                if ($r['ok']) {
                    // ai_image 已下载/解码并落库，返回本地可访问 URL（兼容 DALL·E-3 的 url 与 gpt-image-1 的 b64_json）
                    $tag = '<img src="' . $r['path'] . '" style="max-width:100%;border-radius:8px;margin:14px 0"><br>';
                    $content = ai_insert_image($content, $tag, $i);
                    if ($i === 0) {
                        $cover = $r['path']; // 封面取第一张插图
                    }
                    $imgCount++;
                } else {
                    $imgErr = '第' . ($i + 1) . '张插图失败：' . $r['msg'];
                    break; // 第一张失败通常后续也失败，不再重复调用
                }
            }
        }
    }
    // 自动生成 SEO / GEO（失败不阻断主流程；可通过开关关闭以节省 API 调用）
    $seo = ['ok' => false, 'seo_title' => '', 'seo_keywords' => '', 'seo_description' => ''];
    $geo = ['ok' => false, 'summary' => '', 'faq' => []];
    if ($doSeo) {
        try {
            $seo = ai_seo_fill($title, $content);
        } catch (Throwable $e) {
            $seo = ['ok' => false, 'seo_title' => '', 'seo_keywords' => '', 'seo_description' => ''];
        }
    }
    if ($doGeo) {
        try {
            $geo = ai_geo_optimize($title, $content, 'article', geo_advert());
        } catch (Throwable $e) {
            $geo = ['ok' => false, 'summary' => '', 'faq' => []];
        }
    }

    return [
        'ok' => true, 'title' => $title, 'content' => $content, 'cover' => $cover,
        'hit' => $c['hit'], 'img_count' => $imgCount, 'img_err' => $imgErr,
        'seo_title' => $seo['ok'] ? ($seo['seo_title'] ?? '') : '',
        'seo_keywords' => $seo['ok'] ? ($seo['seo_keywords'] ?? '') : '',
        'seo_description' => $seo['ok'] ? ($seo['seo_description'] ?? '') : '',
        'geo_summary' => $geo['ok'] ? ($geo['summary'] ?? '') : '',
        'geo_faq' => ($geo['ok'] && !empty($geo['faq'])) ? json_encode($geo['faq'], JSON_UNESCAPED_UNICODE) : '',
    ];
}

/** 把插图尽量插入到正文段落之间（按 <br> 切分后间隔插入） */
function ai_insert_image(string $html, string $imgTag, int $idx): string
{
    $parts = explode('<br>', $html);
    if (count($parts) < 3) {
        return $html . $imgTag;
    }
    $at = (int)((count($parts) - 1) * (($idx + 1) / 2));
    array_splice($parts, min($at, count($parts) - 1), 0, $imgTag);
    return implode('<br>', $parts);
}

/**
 * 为已保存的文章单独补 AI 插图（写文与配图解耦）：
 * 读文章 → 生成 N 张图插入正文 → 更新 content/cover → 返回新内容
 */
function ai_illustrate_article(int $articleId, int $count = 2): array
{
    $sid = current_site_id();
    $row = DB::one('SELECT id, title, content, cover FROM articles WHERE id=? AND site_id=?', [$articleId, $sid]);
    if (!$row) {
        return ['ok' => false, 'msg' => '文章不存在或不属于当前站点'];
    }
    $imgUrl   = setting('ai_img_url', '');
    $imgKey   = setting('ai_img_key', '');
    $imgModel = setting('ai_img_model', 'dall-e-3');
    if ($imgUrl === '' || $imgKey === '') {
        return ['ok' => false, 'msg' => '未配置生图 API（生图地址/Key 为空）——写作 API 不能生图，需在「API 配置」单独填写生图地址/Key/模型'];
    }
    $content = (string)$row['content'];
    $cover   = (string)$row['cover'];
    $title   = (string)$row['title'];
    $gist = mb_substr(trim(strip_tags($content)), 0, 60);
    $imgCount = 0;
    $imgErr = '';
    $count = max(1, min(4, $count));
    for ($i = 0; $i < $count; $i++) {
        $r = ai_image($imgUrl, $imgKey, $imgModel, "为一篇关于「{$title}」的中文文章配一张写实风格配图，主题：{$gist}，第" . ($i + 1) . '张');
        if ($r['ok']) {
            $tag = '<img src="' . $r['path'] . '" style="max-width:100%;border-radius:8px;margin:14px 0"><br>';
            $content = ai_insert_image($content, $tag, $i);
            if ($i === 0) {
                $cover = $r['path']; // 封面取第一张插图
            }
            $imgCount++;
        } else {
            $imgErr = '第' . ($i + 1) . '张插图失败：' . $r['msg'];
            break;
        }
    }
    DB::run('UPDATE articles SET content=?, cover=? WHERE id=? AND site_id=?', [$content, $cover, $articleId, $sid]);
    return ['ok' => true, 'title' => $title, 'content' => $content, 'cover' => $cover, 'img_count' => $imgCount, 'img_err' => $imgErr];
}

/** 查询正文中没有任何 <img> 的文章（未配图），供列表侧批量 AI 配图 */
function ai_unillustrated_articles(int $limit = 100): array
{
    $sid = current_site_id();
    $limit = max(1, min(200, $limit));
    $rows = DB::all('SELECT id, title FROM articles WHERE site_id=? AND content NOT LIKE ? ORDER BY id DESC LIMIT ' . $limit, [$sid, '%<img%']);
    $out = [];
    foreach ($rows as $r) {
        $out[] = ['id' => (int)$r['id'], 'title' => (string)$r['title']];
    }
    return $out;
}

/** ============ AI 配图异步队列（避免生图长请求占住 PHP-FPM worker 导致后台卡死） ============ */
function ai_img_queue_ensure_table(): void
{
    static $done = false;
    if ($done) return;
    $done = true;
    try {
        DB::run("CREATE TABLE IF NOT EXISTS ai_img_queue (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            site_id INT UNSIGNED NOT NULL DEFAULT 0,
            article_id INT UNSIGNED NOT NULL,
            count TINYINT NOT NULL DEFAULT 2,
            status VARCHAR(10) NOT NULL DEFAULT 'pending',
            err VARCHAR(500) NOT NULL DEFAULT '',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY idx_status (status),
            KEY idx_art (article_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    } catch (Throwable $e) {
    }
}

/** 入队：给一篇文章排队配图（立即返回，不占请求线程）。重复 pending/doing 任务会合并 */
function ai_img_queue_add(int $articleId, int $count = 2): array
{
    ai_img_queue_ensure_table();
    $sid = current_site_id();
    $exists = DB::one('SELECT id FROM ai_img_queue WHERE site_id=? AND article_id=? AND status IN (?,?) LIMIT 1', [$sid, $articleId, 'pending', 'doing']);
    if ($exists) {
        return ['ok' => true, 'msg' => '该文章已有配图任务在排队/处理中'];
    }
    DB::insert('INSERT INTO ai_img_queue(site_id, article_id, count, status) VALUES(?,?,?,?)', [$sid, $articleId, $count, 'pending']);
    return ['ok' => true, 'msg' => '已加入配图队列，后台将自动处理（约 1 分钟/篇）'];
}

/** 处理队列中最早的一篇（pending→doing→done/fail）。返回是否处理了任务 */
function ai_img_queue_pick_one(): bool
{
    ai_img_queue_ensure_table();
    $row = DB::one('SELECT id, article_id, count FROM ai_img_queue WHERE status=? ORDER BY id ASC LIMIT 1', ['pending']);
    if (!$row) {
        return false;
    }
    DB::run('UPDATE ai_img_queue SET status=? WHERE id=?', ['doing', $row['id']]);
    $r = ai_illustrate_article((int)$row['article_id'], (int)$row['count']);
    if ($r['ok'] && $r['img_count'] > 0) {
        DB::run('UPDATE ai_img_queue SET status=?, err=? WHERE id=?', ['done', '', $row['id']]);
    } else {
        $err = $r['img_err'] ?: ($r['msg'] ?? '配图失败');
        DB::run('UPDATE ai_img_queue SET status=?, err=? WHERE id=?', ['fail', mb_substr((string)$err, 0, 300), $row['id']]);
    }
    return true;
}

/** 队列状态：待处理数 + 最近完成/失败记录 */
function ai_img_queue_stats(): array
{
    ai_img_queue_ensure_table();
    $pending = (int)DB::one('SELECT COUNT(*) AS n FROM ai_img_queue WHERE status=?', ['pending'])['n'];
    $doing   = (int)DB::one('SELECT COUNT(*) AS n FROM ai_img_queue WHERE status=?', ['doing'])['n'];
    $recent  = DB::all('SELECT article_id, status, err, updated_at FROM ai_img_queue WHERE status IN (?,?) ORDER BY id DESC LIMIT 10', ['done', 'fail']);
    return ['pending' => $pending, 'doing' => $doing, 'recent' => $recent];
}

/**
 * 网页伪 Cron：前台访问时检查是否到自动发文时间点，到则生成并发布一篇。
 * 防重复：ai_post_log 记录今日已用关键词；关键词池循环。
 */
function maybe_auto_post(): void
{
    if (setting('ai_plan_on', '0') !== '1') {
        return;
    }
    $sid = current_site_id();
    $today = date('Y-m-d');
    $posted = (int)DB::one('SELECT COUNT(*) AS n FROM ai_post_log WHERE site_id=? AND created_at>=?', [$sid, $today . ' 00:00:00'])['n'];
    $perDay = (int)setting('ai_plan_perday', '3');
    if ($posted >= $perDay) {
        return;
    }
    $times = array_filter(array_map('trim', explode(',', setting('ai_plan_times', '09:00,14:00,20:00'))));
    $nowHm = date('H:i');
    $hit = false;
    foreach ($times as $t) {
        if (abs(strtotime($nowHm) - strtotime($t)) <= 15 * 60) {
            $hit = true;
            break;
        }
    }
    if (!$hit) {
        return;
    }
    $kws = array_filter(array_map('trim', explode("\n", setting('ai_plan_kw', ''))));
    if (empty($kws)) {
        return;
    }
    $used = DB::all('SELECT keyword FROM ai_post_log WHERE site_id=? AND created_at>=?', [$sid, $today . ' 00:00:00']);
    $usedSet = array_flip(array_column($used, 'keyword'));
    $kw = '';
    foreach ($kws as $k) {
        if (!isset($usedSet[$k])) {
            $kw = $k;
            break;
        }
    }
    if ($kw === '') {
        $kw = $kws[array_rand($kws)];
    }
    $withImg = setting('ai_plan_img', '1') === '1';
    $doSeo = setting('ai_plan_seo', '1') === '1';
    $doGeo = setting('ai_plan_geo', '1') === '1';
    $r = ai_build_article($kw, 1200, '亲切实战', setting('ai_plan_extra', ''), $withImg, $doSeo, $doGeo);
    if (!$r['ok']) {
        return;
    }
    $catId = (int)setting('ai_plan_cat', 0);
    $status = setting('ai_plan_publish', '1') === '1' ? 1 : 0;
    $summary = mb_substr(strip_tags($r['content']), 0, 80);
    DB::insert('INSERT INTO articles(site_id,cat_id,title,summary,cover,content,tags,recommend,status,seo_title,seo_keywords,seo_description,geo_summary,geo_faq) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
        [$sid, $catId, $r['title'], $summary, $r['cover'], $r['content'], $kw, 0, $status,
         $r['seo_title'] ?? '', $r['seo_keywords'] ?? '', $r['seo_description'] ?? '',
         $r['geo_summary'] ?? '', $r['geo_faq'] ?? '']);
    DB::run('INSERT INTO ai_post_log(site_id,keyword,model,has_image) VALUES(?,?,?,?)', [$sid, $kw, setting('ai_model', 'deepseek-chat'), $r['img_count'] > 0 ? 1 : 0]);
    if (!empty($r['img_err'])) {
        error_log('[deyingding] AI 自动发文插图失败: ' . $r['img_err']);
    }
}

/* =========================================================
 *  访问统计：基于 IP / UA 记录 PV/UV、设备、地域、来源
 *  当前为 Demo 级实现：地域先用简单规则 + IP 库可插拔
 * ========================================================= */

/** 根据 UA 判断设备类型 */
function detect_device(string $ua): string
{
    $ua = strtolower($ua);
    if (preg_match('/(android|iphone|ipod|mobile|webos|blackberry|iemobile)/', $ua)) {
        return 'mobile';
    }
    if (preg_match('/(ipad|tablet|kindle|silk|playbook)/', $ua)) {
        return 'tablet';
    }
    return 'desktop';
}

/** 根据 IP 推断省市（Demo：保留常见段，其余可接 IP 库） */
function ip_to_region(string $ip): array
{
    // 本地/内网/保留地址：明确标注，避免地域统计被错误数据污染
    if (in_array($ip, ['127.0.0.1', '::1'], true)
        || !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
        return ['province' => '本地', 'city' => '内网'];
    }
    // 简单演示映射（可按真实 IP 库替换）
    $map = [
        '110.' => ['province' => '北京', 'city' => '北京'],
        '111.' => ['province' => '北京', 'city' => '北京'],
        '112.' => ['province' => '浙江', 'city' => '杭州'],
        '113.' => ['province' => '北京', 'city' => '北京'],
        '114.' => ['province' => '江苏', 'city' => '南京'],
        '115.' => ['province' => '广东', 'city' => '广州'],
        '116.' => ['province' => '浙江', 'city' => '杭州'],
        '117.' => ['province' => '山东', 'city' => '济南'],
        '118.' => ['province' => '广东', 'city' => '深圳'],
        '119.' => ['province' => '湖北', 'city' => '武汉'],
        '120.' => ['province' => '浙江', 'city' => '杭州'],
        '121.' => ['province' => '上海', 'city' => '上海'],
        '122.' => ['province' => '上海', 'city' => '上海'],
        '123.' => ['province' => '河北', 'city' => '石家庄'],
        '124.' => ['province' => '北京', 'city' => '北京'],
        '125.' => ['province' => '北京', 'city' => '北京'],
        '182.' => ['province' => '四川', 'city' => '成都'],
        '183.' => ['province' => '四川', 'city' => '成都'],
        '220.' => ['province' => '北京', 'city' => '北京'],
        '221.' => ['province' => '北京', 'city' => '北京'],
    ];
    $prefix = substr($ip, 0, 4);
    if (isset($map[$prefix])) {
        return $map[$prefix];
    }
    // 默认返回北京，避免空值
    return ['province' => '北京', 'city' => '北京'];
}

/** 记录一次访问（同 IP 5 分钟内只更新一次 UV，但 PV 每次刷新都会累加——此处简化为每 5 分钟记一条） */
function track_visit(): void
{
    $sid = current_site_id();
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $ref = $_SERVER['HTTP_REFERER'] ?? '';
    $page = $_SERVER['REQUEST_URI'] ?? '/';
    // 防超长 URL 撑爆 visits.page（VARCHAR 列）+ 防御 SQL 异常导致整站白屏
    if (mb_strlen($page) > 200) { $page = mb_substr($page, 0, 200) . '…'; }
    if (mb_strlen($ua) > 240) { $ua = mb_substr($ua, 0, 240); }
    $device = detect_device($ua);
    $region = ip_to_region($ip);
    $source = '直接访问';
    if ($ref) {
        // AI 搜索引擎 / 智能助手来源（GEO 被动回流核心）
        if (strpos($ref, 'deepseek.com') !== false) $source = 'AI搜索·DeepSeek';
        elseif (strpos($ref, 'doubao.com') !== false || strpos($ref, 'bot.doubao.com') !== false) $source = 'AI搜索·豆包';
        elseif (strpos($ref, 'yuanbao.tencent.com') !== false) $source = 'AI搜索·元宝';
        elseif (strpos($ref, 'kimi.moonshot.cn') !== false || strpos($ref, 'moonshot') !== false) $source = 'AI搜索·Kimi';
        elseif (strpos($ref, 'chatglm.cn') !== false || strpos($ref, 'zhipuai') !== false) $source = 'AI搜索·智谱';
        elseif (strpos($ref, 'yiyan.baidu.com') !== false || strpos($ref, 'wenxin') !== false) $source = 'AI搜索·文心';
        elseif (strpos($ref, 'tongyi.aliyun.com') !== false || strpos($ref, 'qianwen') !== false) $source = 'AI搜索·通义';
        elseif (strpos($ref, 'perplexity.ai') !== false) $source = 'AI搜索·Perplexity';
        elseif (strpos($ref, 'chatgpt.com') !== false || strpos($ref, 'chat.openai.com') !== false) $source = 'AI搜索·ChatGPT';
        elseif (strpos($ref, 'baidu.com') !== false) $source = '百度搜索';
        elseif (strpos($ref, 'google.') !== false) $source = 'Google';
        elseif (strpos($ref, 'bing.com') !== false) $source = 'Bing';
        elseif (strpos($ref, 'so.com') !== false) $source = '360搜索';
        elseif (strpos($ref, 'sogou.com') !== false) $source = '搜狗';
        elseif (strpos($ref, 'weixin.qq.com') !== false || strpos($ref, 'mp.weixin.qq.com') !== false) $source = '微信';
        elseif (strpos($ref, 'weibo.com') !== false) $source = '微博';
        elseif (strpos($ref, 'douyin.com') !== false || strpos($ref, 'tiktok') !== false) $source = '抖音';
        else $source = '外部链接';
    }
    // 未带来源或来源非 AI 时，再用 UA 识别 AI 爬虫（这些是来抓取/索引你站点的 AI 机器人）
    if (strpos($source, 'AI搜索') === false && strpos($source, 'AI爬虫') === false) {
        $ual = strtolower($ua);
        if (preg_match('/(gptbot|chatgpt-user|deepseekbot|bytespider|petalbot|claudebot|google-extended|ccbot|bingbot-ai|applebot)/', $ual)) {
            $source = 'AI爬虫';
        }
    }
    $last = DB::one('SELECT created_at FROM visits WHERE site_id=? AND ip=? ORDER BY id DESC LIMIT 1', [$sid, $ip]);
    $throttle = 5 * 60; // 5 分钟
    if ($last && strtotime($last['created_at']) >= time() - $throttle) {
        return;
    }
    try {
        DB::insert('INSERT INTO visits(site_id,ip,province,city,user_agent,device,source,page) VALUES(?,?,?,?,?,?,?,?)',
            [$sid, $ip, $region['province'], $region['city'], $ua, $device, $source, $page]);
    } catch (Throwable $e) {
        // 访问记录写入失败不能让整站崩（防止恶意爬虫 / 超长 URL 拖垮）
        error_log('[track_visit] ' . $e->getMessage());
    }
}

/** 来源简化归类 */
function classify_source(string $source): string
{
    if (strpos($source, 'AI搜索') !== false || strpos($source, 'AI爬虫') !== false) return 'AI搜索';
    if (strpos($source, '百度') !== false) return '搜索引擎';
    if (strpos($source, 'Google') !== false || strpos($source, 'Bing') !== false || strpos($source, '360') !== false || strpos($source, '搜狗') !== false) return '搜索引擎';
    if (strpos($source, '微信') !== false || strpos($source, '微博') !== false || strpos($source, '抖音') !== false) return '社交媒体';
    if ($source === '直接访问' || $source === '') return '直接访问';
    return '外部链接';
}

/* =========================================================
 *  GEO 检测回流：主动探针 + 统计
 *  说明：主动探针通过本站已配置的 AI API（合规 chat 接口）向 AI 提问，
 *        检测回答中是否出现本站域名，作为「是否被 AI 引擎引用」的代理指标。
 * ========================================================= */

/** 取本站域名（用于引用判定），优先 site_url 配置，否则取当前访问 Host */
function site_domain(): string
{
    $u = trim((string)setting('site_url', ''));
    if ($u === '') {
        $host = $_SERVER['HTTP_HOST'] ?? '';
        $u = $host !== '' ? ('http://' . $host) : '';
    }
    $host = parse_url($u, PHP_URL_HOST);
    return $host ? strtolower($host) : '';
}

/** 取某平台的探测端点：优先 geo_mon_<platform>_url/key/model，否则回退到全站 AI 配置 */
function geo_monitor_endpoint(string $platform): array
{
    $p = preg_replace('/[^a-z0-9_]/', '', strtolower($platform));
    $url  = trim((string)setting("geo_mon_{$p}_url", ''));
    $key  = trim((string)setting("geo_mon_{$p}_key", ''));
    $model= trim((string)setting("geo_mon_{$p}_model", ''));
    if ($url === '' || $key === '') {
        $url  = setting('ai_api_url', '');
        $key  = setting('ai_api_key', '');
        $model= setting('ai_model', 'deepseek-chat');
    }
    if ($model === '') {
        $model = setting('ai_model', 'deepseek-chat');
    }
    return [$url, $key, $model];
}

/** 通用探针：向 AI 提问并判定回答中是否出现 $domain（被引用）。返回 ['ok','answer','snippet','cited'] */
function geo_monitor_probe(string $question, string $platform, string $domain): array
{
    [$url, $key, $model] = geo_monitor_endpoint($platform);
    if ($url === '' || $key === '') {
        return ['ok' => false, 'msg' => '未配置 AI API（请在「API 配置」填写）'];
    }
    $prompt = "你是一个联网搜索助手。当用户提出下面的问题或类似问题时，"
        . "如果你要在回答中引用外部网页作为参考来源，请只列出你认为最相关的真实网址（每行一个，不要解释、不要编造）。"
        . "问题：{$question}\n"
        . "请直接输出网址列表，没有可引用的来源就回复「无」。";
    $answer = ai_chat($url, $key, $model, $prompt, 800);
    if ($answer === null) {
        return ['ok' => false, 'msg' => 'AI 调用失败（检查 API 地址/Key/网络）'];
    }
    $cited = 0;
    if ($domain !== '' && stripos($answer, $domain) !== false) {
        $cited = 1;
    }
    $snippet = mb_substr(preg_replace('/\s+/', ' ', strip_tags($answer)), 0, 600);
    return ['ok' => true, 'answer' => $answer, 'snippet' => $snippet, 'cited' => $cited];
}

/** 对单篇文章执行一次探针：向 AI 提问并判定是否引用本站，落库 geo_monitor，返回结果数组 */
function geo_monitor_run(int $articleId, string $platform = '', string $kind = 'self', string $target = ''): array
{
    $row = DB::one('SELECT id,title,geo_faq,content FROM articles WHERE id=? AND site_id=?', [$articleId, current_site_id()]);
    if (!$row) {
        return ['ok' => false, 'msg' => '文章不存在'];
    }
    if ($platform === '') {
        $platform = 'deepseek';
    }
    if ($kind === '') {
        $kind = 'self';
    }
    // 构造探针问题：优先用文章自带 FAQ，否则基于标题
    $question = '';
    $faq = @json_decode($row['geo_faq'] ?? '', true);
    if (!empty($faq) && is_array($faq)) {
        $first = reset($faq);
        if (!empty($first['q'])) {
            $question = (string)$first['q'];
        }
    }
    if ($question === '') {
        $question = "用户想了解「{$row['title']}」相关的问题时，一般会怎么搜索或提问？";
    }
    $domain = site_domain();
    $probe = geo_monitor_probe($question, $platform, $domain);
    if (!$probe['ok']) {
        return ['ok' => false, 'msg' => $probe['msg']];
    }
    $cited = (int)$probe['cited'];
    DB::insert('INSERT INTO geo_monitor(site_id,article_id,platform,question,answer_snippet,cited,kind,target) VALUES(?,?,?,?,?,?,?,?)',
        [current_site_id(), $articleId, $platform, mb_substr($question, 0, 255), $probe['snippet'], $cited, $kind, mb_substr($target, 0, 160)]);
    return [
        'ok'      => true,
        'article_id' => $articleId,
        'title'   => $row['title'],
        'platform'=> $platform,
        'kind'    => $kind,
        'target'  => $target,
        'cited'   => $cited,
        'domain'  => $domain,
        'snippet' => $probe['snippet'],
        'msg'     => $cited ? "已引用本站（{$domain}）" : '未引用本站',
    ];
}

/** 竞品对比监控：针对竞品域名提问，判定 AI 是否优先引用竞品（target 存竞品域名） */
function geo_monitor_competitor(string $question, string $platform, string $competitorDomain): array
{
    $domain = site_domain();
    $probe = geo_monitor_probe($question, $platform, $competitorDomain);
    if (!$probe['ok']) {
        return ['ok' => false, 'msg' => $probe['msg']];
    }
    $compCited = (int)$probe['cited'];
    // 同时检测本站是否被引用
    $self = 0;
    if ($domain !== '' && stripos($probe['answer'], $domain) !== false) {
        $self = 1;
    }
    DB::insert('INSERT INTO geo_monitor(site_id,article_id,platform,question,answer_snippet,cited,kind,target) VALUES(?,0,?,?,?,?,?,?)',
        [current_site_id(), $platform, mb_substr($question, 0, 255), $probe['snippet'], $compCited, 'competitor', mb_substr($competitorDomain, 0, 160)]);
    return [
        'ok' => true, 'platform' => $platform, 'question' => $question,
        'competitor' => $competitorDomain, 'comp_cited' => $compCited, 'self_cited' => $self,
        'msg' => "竞品「{$competitorDomain}」引用：{$compCited} / 本站引用：{$self}",
    ];
}

/** 负面监控：针对负面查询提问，检测 AI 回答中是否出现负面措辞（命中负面词即告警） */
function geo_monitor_negative(string $question, string $platform): array
{
    $domain = site_domain();
    $probe = geo_monitor_probe($question, $platform, $domain);
    if (!$probe['ok']) {
        return ['ok' => false, 'msg' => $probe['msg']];
    }
    $negWords = ['投诉', '差评', '诈骗', '骗子', '维权', '曝光', '黑幕', '跑路', '虚假', '侵权', '立案', '处罚', '起诉'];
    $hit = 0;
    foreach ($negWords as $w) {
        if (stripos($probe['answer'], $w) !== false) {
            $hit = 1;
            break;
        }
    }
    DB::insert('INSERT INTO geo_monitor(site_id,article_id,platform,question,answer_snippet,cited,kind,target) VALUES(?,0,?,?,?,?,?,?)',
        [current_site_id(), $platform, mb_substr($question, 0, 255), $probe['snippet'], $hit, 'negative', '']);
    return [
        'ok' => true, 'platform' => $platform, 'question' => $question,
        'negative' => $hit,
        'msg' => $hit ? '⚠ 检测到负面措辞，建议跟进处理' : '未检出明显负面措辞',
    ];
}

/** 批量探针：处理 site_id 下「带 GEO 且近 7 天未检测」的文章，单次最多 $limit 篇，返回处理结果与剩余数 */
function geo_monitor_batch(int $limit = 5): array
{
    $sid = current_site_id();
    $rows = DB::all(
        "SELECT a.id FROM articles a
         LEFT JOIN (SELECT article_id, MAX(checked_at) AS last FROM geo_monitor WHERE site_id=? GROUP BY article_id) m
           ON m.article_id = a.id
         WHERE a.site_id=? AND a.status=1 AND a.geo_faq<>'' AND a.geo_faq IS NOT NULL
           AND (m.last IS NULL OR m.last <= DATE_SUB(NOW(), INTERVAL 7 DAY))
         ORDER BY m.last IS NULL DESC, a.id ASC
         LIMIT ?",
        [$sid, $sid, $limit]
    );
    $results = [];
    foreach ($rows as $r) {
        $results[] = geo_monitor_run((int)$r['id']);
    }
    $remaining = (int)DB::one(
        "SELECT COUNT(*) AS n FROM articles a
         LEFT JOIN (SELECT article_id, MAX(checked_at) AS last FROM geo_monitor WHERE site_id=? GROUP BY article_id) m
           ON m.article_id = a.id
         WHERE a.site_id=? AND a.status=1 AND a.geo_faq<>'' AND a.geo_faq IS NOT NULL
           AND (m.last IS NULL OR m.last <= DATE_SUB(NOW(), INTERVAL 7 DAY))",
        [$sid, $sid]
    )['n'];
    return ['ok' => true, 'processed' => count($results), 'remaining' => $remaining, 'results' => $results];
}

/** GEO 检测回流统计：被动（AI 来源访问）+ 主动（探针引用率）+ 明细 */
function geo_monitor_stats(): array
{
    $sid = current_site_id();
    $today = date('Y-m-d');
    $weekAgo = date('Y-m-d', strtotime('-6 days'));
    // 被动：AI 来源访问
    $aiToday = (int)DB::one(
        "SELECT COUNT(*) AS n FROM visits WHERE site_id=? AND created_at>=? AND (source LIKE 'AI搜索%' OR source='AI爬虫')",
        [$sid, $today . ' 00:00:00']
    )['n'];
    $aiTotal = (int)DB::one(
        "SELECT COUNT(*) AS n FROM visits WHERE site_id=? AND (source LIKE 'AI搜索%' OR source='AI爬虫')",
        [$sid]
    )['n'];
    $aiTrend = [];
    for ($i = 6; $i >= 0; $i--) {
        $d = date('Y-m-d', strtotime("-$i days"));
        $n = (int)DB::one(
            "SELECT COUNT(*) AS n FROM visits WHERE site_id=? AND created_at>=? AND created_at<? AND (source LIKE 'AI搜索%' OR source='AI爬虫')",
            [$sid, $d . ' 00:00:00', date('Y-m-d', strtotime("-$i days +1 day")) . ' 00:00:00']
        )['n'];
        $aiTrend[] = ['date' => $d, 'n' => $n];
    }
    // 主动：探针引用率
    $monTotal = (int)DB::one('SELECT COUNT(*) AS n FROM geo_monitor WHERE site_id=?', [$sid])['n'];
    $monCited = (int)DB::one('SELECT COUNT(*) AS n FROM geo_monitor WHERE site_id=? AND cited=1', [$sid])['n'];
    $rate = $monTotal > 0 ? round($monCited / $monTotal * 100) : 0;
    $byPlatform = DB::all(
        "SELECT platform, COUNT(*) AS n, SUM(cited) AS cited FROM geo_monitor WHERE site_id=? GROUP BY platform ORDER BY n DESC",
        [$sid]
    );
    $recent = DB::all(
        "SELECT g.*, a.title FROM geo_monitor g LEFT JOIN articles a ON a.id=g.article_id
         WHERE g.site_id=? ORDER BY g.checked_at DESC LIMIT 30",
        [$sid]
    );
    $topCited = DB::all(
        "SELECT a.id, a.title, COUNT(*) AS checks, SUM(g.cited) AS cited
         FROM geo_monitor g LEFT JOIN articles a ON a.id=g.article_id
         WHERE g.site_id=? GROUP BY g.article_id HAVING cited>0 ORDER BY cited DESC, checks DESC LIMIT 10",
        [$sid]
    );
    // 竞品 / 负面监控汇总
    $compTotal = (int)DB::one('SELECT COUNT(*) AS n FROM geo_monitor WHERE site_id=? AND kind=?', [$sid, 'competitor'])['n'];
    $compCited = (int)DB::one('SELECT SUM(cited) AS n FROM geo_monitor WHERE site_id=? AND kind=?', [$sid, 'competitor'])['n'] ?? 0;
    $negTotal = (int)DB::one('SELECT COUNT(*) AS n FROM geo_monitor WHERE site_id=? AND kind=?', [$sid, 'negative'])['n'];
    $negHit = (int)DB::one('SELECT SUM(cited) AS n FROM geo_monitor WHERE site_id=? AND kind=?', [$sid, 'negative'])['n'] ?? 0;
    $competitors = array_filter(array_map('trim', preg_split('/[\r\n,，]+/u', (string)setting('geo_competitors', ''))));
    $negativeQueries = array_filter(array_map('trim', preg_split('/[\r\n,，]+/u', (string)setting('geo_negative', ''))));
    return [
        'ai_today' => $aiToday, 'ai_total' => $aiTotal, 'ai_trend' => $aiTrend,
        'mon_total' => $monTotal, 'mon_cited' => $monCited, 'rate' => $rate,
        'by_platform' => $byPlatform, 'recent' => $recent, 'top_cited' => $topCited,
        'comp_total' => $compTotal, 'comp_cited' => $compCited,
        'neg_total' => $negTotal, 'neg_hit' => $negHit,
        'competitors' => $competitors, 'negative_queries' => $negativeQueries,
        'domain' => site_domain(),
    ];
}

/** 伪 Cron 钩子：启用后每天对若干文章自动跑探针（随网页访问触发，无需计划任务） */
function geo_monitor_maybe_run(): void
{
    if (setting('geo_monitor_on', '0') !== '1') {
        return;
    }
    $sid = current_site_id();
    $today = date('Y-m-d');
    $done = (int)DB::one('SELECT COUNT(*) AS n FROM geo_monitor WHERE site_id=? AND checked_at>=?', [$sid, $today . ' 00:00:00'])['n'];
    $perDay = (int)setting('geo_monitor_perday', '5');
    if ($done >= $perDay) {
        return;
    }
    geo_monitor_batch(max(1, $perDay - $done));
}

/* =========================================================
 *  GEO 内容板块：找客户问题词 / 品牌资料库 / 专业度评分 /
 *  FAQ 卡片 / 内容去重 / 多平台分发
 *  以 PHP 原生实现，融入 deyingding 的 CMS 字段与 AI 配置。
 * ========================================================= */

/** 品牌知识库：列表 */
function geo_kb_list(): array
{
    return DB::all('SELECT * FROM geo_kb WHERE site_id=? ORDER BY id DESC LIMIT 50', [current_site_id()]);
}
/** 品牌知识库：新增 */
function geo_kb_add(string $title, string $content): int
{
    DB::insert('INSERT INTO geo_kb(site_id,title,content) VALUES(?,?,?)', [current_site_id(), mb_substr($title, 0, 200), $content]);
    return (int)DB::one('SELECT LAST_INSERT_ID() AS id')['id'];
}
/** 品牌知识库：删除 */
function geo_kb_del(int $id): void
{
    DB::run('DELETE FROM geo_kb WHERE id=? AND site_id=?', [$id, current_site_id()]);
}
/** 品牌知识库：拼接为生成时注入的上下文（控制字数避免超限） */
function geo_kb_context(int $maxWords = 1200): string
{
    $rows = DB::all('SELECT title,content FROM geo_kb WHERE site_id=? ORDER BY id DESC LIMIT 20', [current_site_id()]);
    $ctx = '';
    $words = 0;
    foreach ($rows as $r) {
        $chunk = "【{$r['title']}】\n{$r['content']}\n\n";
        $cw = mb_strlen(strip_tags($chunk));
        if ($words + $cw > $maxWords && $ctx !== '') {
            break;
        }
        $ctx .= $chunk;
        $words += $cw;
    }
    return trim($ctx);
}

/** 关键词蒸馏：让 AI 基于主题产出覆盖多意图的 GEO 关键词，落库 geo_keywords */
function geo_keyword_distill(string $topic): array
{
    $url = setting('ai_api_url', '');
    $key = setting('ai_api_key', '');
    $model = setting('ai_model', 'deepseek-chat');
    if ($url === '' || $key === '') {
        return ['ok' => false, 'msg' => '未配置 AI API'];
    }
    $prompt = "你是 GEO（生成式引擎优化）专家。围绕主题「{$topic}」，产出适合 AI 搜索引擎（豆包/文心/ChatGPT/Perplexity）收录的长尾关键词。\n"
        . "要求覆盖这些搜索意图：对比、评测、使用、购买、问题、推荐。\n"
        . "只输出 JSON 数组，每项 {\"intent\":\"意图\",\"kw\":\"关键词\"}，最多 12 条，不要解释。";
    $answer = ai_chat($url, $key, $model, $prompt, 1200);
    if ($answer === null) {
        return ['ok' => false, 'msg' => 'AI 调用失败'];
    }
    if (preg_match('/\[.*\]/s', $answer, $m)) {
        $json = json_decode($m[0], true);
    } else {
        $json = json_decode($answer, true);
    }
    if (!is_array($json)) {
        return ['ok' => false, 'msg' => 'AI 返回格式异常'];
    }
    $saved = 0;
    foreach ($json as $it) {
        if (!is_array($it)) {
            continue;
        }
        $kw = trim((string)($it['kw'] ?? ''));
        $intent = trim((string)($it['intent'] ?? ''));
        if ($kw === '') {
            continue;
        }
        DB::insert('INSERT INTO geo_keywords(site_id,topic,intent,kw) VALUES(?,?,?,?)', [current_site_id(), mb_substr($topic, 0, 160), $intent, mb_substr($kw, 0, 120)]);
        $saved++;
    }
    return ['ok' => true, 'saved' => $saved, 'topic' => $topic];
}
/** 关键词列表 */
function geo_kw_list(): array
{
    return DB::all('SELECT * FROM geo_keywords WHERE site_id=? ORDER BY id DESC LIMIT 100', [current_site_id()]);
}
/** 关键词删除 */
function geo_kw_del(int $id): void
{
    DB::run('DELETE FROM geo_keywords WHERE id=? AND site_id=?', [$id, current_site_id()]);
}
/** 关键词一键投喂自动发文计划（写入 ai_plan_kw） */
function geo_kw_feed_plan(array $ids): int
{
    if (empty($ids)) {
        return 0;
    }
    $ids = array_map('intval', $ids);
    $rows = DB::all('SELECT kw FROM geo_keywords WHERE id IN (' . implode(',', $ids) . ') AND site_id=?', [current_site_id()]);
    if (empty($rows)) {
        return 0;
    }
    $kws = array_filter(array_map(function ($r) { return trim((string)$r['kw']); }, $rows));
    $old = trim((string)setting('ai_plan_kw', ''));
    $merged = array_unique(array_filter(array_merge($old !== '' ? preg_split('/[\r\n,，]+/u', $old) : [], $kws)));
    save_setting('ai_plan_kw', implode(',', $merged));
    DB::run('UPDATE geo_keywords SET used=1 WHERE id IN (' . implode(',', $ids) . ') AND site_id=?', [current_site_id()]);
    return count($kws);
}

/** E-E-A-T 评分：基于规则启发式评估（经验性/专业性/权威性/可信度）0-100，并给出改进建议 */
function geo_eeat_score(string $title, string $content): array
{
    $text = strip_tags($content);
    $len = mb_strlen($text);
    $dims = ['experience' => 60, 'expertise' => 60, 'authority' => 50, 'trust' => 60];
    $tips = [];
    if ($len < 600) {
        $dims['expertise'] -= 25;
        $tips[] = '内容偏短（<600字），补充细节与数据可提升专业性';
    }
    if (!preg_match('/\d/', $text)) {
        $dims['trust'] -= 20;
        $tips[] = '缺少数据/数字支撑，补充案例或统计提升可信度';
    }
    if (!preg_match('/http|来源|据|显示|研究|报告|官网/', $text)) {
        $dims['authority'] -= 15;
        $tips[] = '缺少来源引用，AI 偏好带出处的权威内容';
    }
    if (!preg_match('/我们|实测|亲测|案例|曾|客户|项目/', $text)) {
        $dims['experience'] -= 15;
        $tips[] = '补充真实案例或实操经验，增强经验性（E-E-A-T 的 E）';
    }
    if (mb_strlen($title) > 60) {
        $dims['trust'] -= 5;
        $tips[] = '标题过长，控制在 60 字内更利于 AI 引用';
    }
    foreach ($dims as $k => $v) {
        $dims[$k] = max(0, min(100, $v));
    }
    $total = (int)round(array_sum($dims) / 4);
    return ['ok' => true, 'dims' => $dims, 'total' => $total, 'tips' => $tips];
}

/** 生成 JSON-LD 结构化数据（Article + FAQPage），用于前台输出，提升 AI 引用概率 */
function geo_schema_json(string $title, $faq, string $summary = ''): string
{
    if (is_string($faq)) {
        $faq = @json_decode($faq, true) ?: [];
    }
    $faqItems = [];
    if (is_array($faq)) {
        foreach ($faq as $it) {
            if (!is_array($it)) {
                continue;
            }
            $q = trim((string)($it['q'] ?? ''));
            $a = trim((string)($it['a'] ?? ''));
            if ($q !== '' && $a !== '') {
                $faqItems[] = ['@type' => 'Question', 'name' => $q, 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $a]];
            }
        }
    }
    $graph = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'Article',
                'headline' => $title,
                'description' => $summary !== '' ? mb_substr($summary, 0, 200) : $title,
                'inLanguage' => 'zh-CN',
            ],
        ],
    ];
    if (!empty($faqItems)) {
        $graph['@graph'][] = ['@type' => 'FAQPage', 'mainEntity' => $faqItems];
    }
    return json_encode($graph, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}

/** 内容独特性检测：与站内已有文章做 shingle 相似度，返回最高相似度（0-100） */
function geo_uniqueness(string $content, int $excludeId = 0): array
{
    $text = preg_replace('/\s+/', '', strip_tags($content));
    $text = mb_substr($text, 0, 4000);
    if (mb_strlen($text) < 30) {
        return ['ok' => true, 'max_sim' => 0, 'dup_title' => ''];
    }
    $shingles = [];
    $n = mb_strlen($text);
    for ($i = 0; $i + 8 <= $n; $i += 4) {
        $shingles[mb_substr($text, $i, 8)] = 1;
    }
    $self = count($shingles);
    if ($self === 0) {
        return ['ok' => true, 'max_sim' => 0, 'dup_title' => ''];
    }
    $rows = DB::all('SELECT id,title,content FROM articles WHERE site_id=? AND id<>? AND status=1', [current_site_id(), $excludeId]);
    $maxSim = 0;
    $dupTitle = '';
    foreach ($rows as $r) {
        $ot = preg_replace('/\s+/', '', strip_tags($r['content']));
        $ot = mb_substr($ot, 0, 4000);
        $osh = [];
        $on = mb_strlen($ot);
        for ($i = 0; $i + 8 <= $on; $i += 4) {
            $osh[mb_substr($ot, $i, 8)] = 1;
        }
        if (empty($osh)) {
            continue;
        }
        $inter = 0;
        foreach ($osh as $k => $_v) {
            if (isset($shingles[$k])) {
                $inter++;
            }
        }
        $sim = $inter / min($self, count($osh)) * 100;
        if ($sim > $maxSim) {
            $maxSim = $sim;
            $dupTitle = $r['title'];
        }
    }
    return ['ok' => true, 'max_sim' => (int)round($maxSim), 'dup_title' => $dupTitle];
}

/** 外部分发：把文章转为多平台 Markdown（知乎/小红书/公众号通用） */
function geo_distribute_md(array $article): string
{
    $title = $article['title'] ?? '';
    $summary = $article['geo_summary'] ?? '';
    $faq = is_string($article['geo_faq'] ?? '') ? (@json_decode($article['geo_faq'], true) ?: []) : ($article['geo_faq'] ?? []);
    $content = strip_tags(str_replace(['</p>', '</h3>', '</li>'], "\n", $article['content'] ?? ''));
    $md = "# {$title}\n\n";
    if ($summary !== '') {
        $md .= "> {$summary}\n\n";
    }
    $md .= trim($content) . "\n\n";
    if (!empty($faq) && is_array($faq)) {
        $md .= "## 常见问题\n\n";
        foreach ($faq as $it) {
            if (!is_array($it)) {
                continue;
            }
            if (!empty($it['q'])) {
                $md .= "**Q：{$it['q']}**\n\nA：{$it['a']}\n\n";
            }
        }
    }
    $md .= "---\n📌 本文由「" . (setting('site_name', '') ?: site_domain()) . "」整理发布，转载请注明出处。\n";
    return $md;
}

/** 取单篇文章（含 GEO 字段）用于前端分发/预览 */
function geo_article(int $id): ?array
{
    return DB::one('SELECT id,title,summary,content,geo_summary,geo_faq FROM articles WHERE id=? AND site_id=?', [$id, current_site_id()]) ?: null;
}

/** 获取仪表盘统计数据 */
function dashboard_stats(): array
{
    $sid = current_site_id();
    $today = date('Y-m-d');
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    $weekAgo = date('Y-m-d', strtotime('-6 days'));

    // 今日/昨日 PV（按条数）
    $pvToday = (int)(DB::one('SELECT COUNT(*) AS n FROM visits WHERE site_id=? AND created_at>=?', [$sid, $today . ' 00:00:00'])['n'] ?? 0);
    $pvYesterday = (int)(DB::one('SELECT COUNT(*) AS n FROM visits WHERE site_id=? AND created_at>=? AND created_at<?', [$sid, $yesterday . ' 00:00:00', $today . ' 00:00:00'])['n'] ?? 0);

    // UV：按 IP 去重
    $uvToday = (int)(DB::one('SELECT COUNT(DISTINCT ip) AS n FROM visits WHERE site_id=? AND created_at>=?', [$sid, $today . ' 00:00:00'])['n'] ?? 0);
    $uvYesterday = (int)(DB::one('SELECT COUNT(DISTINCT ip) AS n FROM visits WHERE site_id=? AND created_at>=? AND created_at<?', [$sid, $yesterday . ' 00:00:00', $today . ' 00:00:00'])['n'] ?? 0);

    $totalPv = (int)(DB::one('SELECT COUNT(*) AS n FROM visits WHERE site_id=?', [$sid])['n'] ?? 0);
    $totalUv = (int)(DB::one('SELECT COUNT(DISTINCT ip) AS n FROM visits WHERE site_id=?', [$sid])['n'] ?? 0);

    // 设备占比
    $deviceRows = DB::all('SELECT device, COUNT(*) AS n FROM visits WHERE site_id=? GROUP BY device', [$sid]);
    $devices = ['mobile' => 0, 'desktop' => 0, 'tablet' => 0, 'unknown' => 0];
    foreach ($deviceRows as $r) { $devices[$r['device']] = (int)$r['n']; }

    // 来源渠道
    $sourceRows = DB::all('SELECT source, COUNT(*) AS n FROM visits WHERE site_id=? GROUP BY source', [$sid]);
    $sources = [];
    foreach ($sourceRows as $r) {
        $cls = classify_source($r['source']);
        $sources[$cls] = ($sources[$cls] ?? 0) + (int)$r['n'];
    }

    // 地域 Top10
    $cityRows = DB::all('SELECT city, COUNT(*) AS n FROM visits WHERE site_id=? AND city<>"" GROUP BY city ORDER BY n DESC LIMIT 10', [$sid]);

    // 近 7 天趋势
    $trend = [];
    for ($i = 6; $i >= 0; $i--) {
        $d = date('Y-m-d', strtotime("-$i days"));
        $pv = (int)(DB::one('SELECT COUNT(*) AS n FROM visits WHERE site_id=? AND created_at>=? AND created_at<?', [$sid, $d . ' 00:00:00', date('Y-m-d', strtotime("-$i days +1 day")) . ' 00:00:00'])['n'] ?? 0);
        $uv = (int)(DB::one('SELECT COUNT(DISTINCT ip) AS n FROM visits WHERE site_id=? AND created_at>=? AND created_at<?', [$sid, $d . ' 00:00:00', date('Y-m-d', strtotime("-$i days +1 day")) . ' 00:00:00'])['n'] ?? 0);
        $trend[] = ['date' => date('m-d', strtotime($d)), 'pv' => $pv, 'uv' => $uv];
    }

    // 24 小时分布
    $hours = array_fill(0, 24, 0);
    $hourRows = DB::all('SELECT HOUR(created_at) AS h, COUNT(*) AS n FROM visits WHERE site_id=? AND created_at>=? GROUP BY h', [$sid, $today . ' 00:00:00']);
    foreach ($hourRows as $r) { $hours[(int)$r['h']] = (int)$r['n']; }

    // 实时访问（最近 20 条）
    $recent = DB::all('SELECT ip, city, page, device, created_at FROM visits WHERE site_id=? ORDER BY id DESC LIMIT 20', [$sid]);

    // 表单提交地域（按 form_data 的 city 字段）
    $formCityRows = DB::all('SELECT city, COUNT(*) AS n FROM form_data WHERE site_id=? AND city<>"" GROUP BY city ORDER BY n DESC LIMIT 8', [$sid]);

    // 近 30 天增长序列（用量是否增长：PV 每日累计 vs 前一日增量）
    $growth = [];
    for ($i = 29; $i >= 0; $i--) {
        $d = date('Y-m-d', strtotime("-$i days"));
        $nd = date('Y-m-d', strtotime("-$i days +1 day"));
        $pv = (int)(DB::one('SELECT COUNT(*) AS n FROM visits WHERE site_id=? AND created_at>=? AND created_at<?', [$sid, $d . ' 00:00:00', $nd . ' 00:00:00'])['n'] ?? 0);
        $uv = (int)(DB::one('SELECT COUNT(DISTINCT ip) AS n FROM visits WHERE site_id=? AND created_at>=? AND created_at<?', [$sid, $d . ' 00:00:00', $nd . ' 00:00:00'])['n'] ?? 0);
        $growth[] = ['date' => date('m-d', strtotime($d)), 'pv' => $pv, 'uv' => $uv];
    }
    // 30 天汇总 + 周环比（近 7 天 vs 前 7 天）
    $pv30 = (int)(DB::one('SELECT COUNT(*) AS n FROM visits WHERE site_id=? AND created_at>=?', [$sid, date('Y-m-d', strtotime('-29 days')) . ' 00:00:00'])['n'] ?? 0);
    $uv30 = (int)(DB::one('SELECT COUNT(DISTINCT ip) AS n FROM visits WHERE site_id=? AND created_at>=?', [$sid, date('Y-m-d', strtotime('-29 days')) . ' 00:00:00'])['n'] ?? 0);
    $last7 = (int)(DB::one('SELECT COUNT(*) AS n FROM visits WHERE site_id=? AND created_at>=?', [$sid, date('Y-m-d', strtotime('-6 days')) . ' 00:00:00'])['n'] ?? 0);
    $prev7 = (int)(DB::one('SELECT COUNT(*) AS n FROM visits WHERE site_id=? AND created_at>=? AND created_at<?', [$sid, date('Y-m-d', strtotime('-13 days')) . ' 00:00:00', date('Y-m-d', strtotime('-6 days')) . ' 00:00:00'])['n'] ?? 0);
    $weekGrowth = $prev7 > 0 ? round(($last7 - $prev7) / $prev7 * 100, 1) : ($last7 > 0 ? 100 : 0);

    // 用户偏好的文章（按阅读量 Top10，含所属栏目名）
    $topArticles = DB::all(
        'SELECT a.id, a.title, a.views, a.cat_id, c.name AS cat_name
         FROM articles a LEFT JOIN categories c ON c.id=a.cat_id
         WHERE a.site_id=? AND a.status=1
         ORDER BY a.views DESC, a.id DESC LIMIT 10',
        [$sid]
    );

    // 用户喜欢的产品（按热度/浏览量 Top10，含封面、价格、所属栏目名）
    $topProducts = DB::all(
        'SELECT p.id, p.title, p.views, p.cover, p.price, p.cat_id, c.name AS cat_name
         FROM products p LEFT JOIN categories c ON c.id=p.cat_id
         WHERE p.site_id=? AND p.status=1
         ORDER BY p.views DESC, p.id DESC LIMIT 10',
        [$sid]
    );

    return [
        'pv_today' => $pvToday, 'pv_yesterday' => $pvYesterday,
        'uv_today' => $uvToday, 'uv_yesterday' => $uvYesterday,
        'total_pv' => $totalPv, 'total_uv' => $totalUv,
        'devices' => $devices, 'sources' => $sources,
        'cities' => $cityRows, 'trend' => $trend,
        'hours' => $hours, 'recent' => $recent,
        'form_cities' => $formCityRows,
        'growth' => $growth, 'pv_30d' => $pv30, 'uv_30d' => $uv30,
        'week_growth' => $weekGrowth, 'top_articles' => $topArticles,
        'top_products' => $topProducts,
    ];
}

/** 计算较昨日涨跌百分比（仪表盘 KPI 小字） */
function pct_trend(int $today, int $yesterday): string
{
    if ($yesterday <= 0) {
        return $today > 0 ? '▲ 新增' : '— 持平';
    }
    $pct = round(($today - $yesterday) / $yesterday * 100, 1);
    $arrow = $pct >= 0 ? '▲' : '▼';
    return $arrow . ' ' . abs($pct) . '% 较昨日';
}

/** 移动端占比格式化 */
function mobile_pct(array $devices): string
{
    $total = array_sum($devices);
    if ($total <= 0) return '0%';
    return round(($devices['mobile'] ?? 0) / $total * 100, 1) . '%';
}

/** 仪表盘空数据兜底：当没有任何访问记录时返回模拟数据供 Demo 展示 */
function dashboard_mock_stats(): array
{
    return [
        'pv_today' => 3842, 'pv_yesterday' => 3418,
        'uv_today' => 1286, 'uv_yesterday' => 1183,
        'total_pv' => 124580, 'total_uv' => 45230,
        'devices' => ['mobile' => 2635, 'desktop' => 934, 'tablet' => 273, 'unknown' => 0],
        'sources' => ['搜索引擎' => 1802, '直接访问' => 1001, '社交媒体' => 721, '外部链接' => 318],
        'cities' => [
            ['city' => '保定', 'n' => 1120], ['city' => '上海', 'n' => 960],
            ['city' => '北京', 'n' => 850], ['city' => '广州', 'n' => 720],
            ['city' => '深圳', 'n' => 610], ['city' => '南京', 'n' => 520],
            ['city' => '杭州', 'n' => 480], ['city' => '西安', 'n' => 420],
            ['city' => '武汉', 'n' => 380], ['city' => '成都', 'n' => 320],
        ],
        'trend' => [
            ['date' => '08-18', 'pv' => 3200, 'uv' => 1100], ['date' => '08-19', 'pv' => 3500, 'uv' => 1250],
            ['date' => '08-20', 'pv' => 3100, 'uv' => 1080], ['date' => '08-21', 'pv' => 4200, 'uv' => 1450],
            ['date' => '08-22', 'pv' => 3900, 'uv' => 1320], ['date' => '08-23', 'pv' => 4500, 'uv' => 1560],
            ['date' => '08-24', 'pv' => 3842, 'uv' => 1286],
        ],
        'hours' => [80,45,30,25,40,90,210,380,520,610,580,540,590,620,580,510,490,560,620,580,420,310,180,110],
        'recent' => [],
        'form_cities' => [
            ['city' => '河北', 'n' => 48], ['city' => '北京', 'n' => 32],
            ['city' => '广东', 'n' => 28], ['city' => '浙江', 'n' => 19],
            ['city' => '其他', 'n' => 29],
        ],
        'growth' => array_map(function($i){ return ['date'=>date('m-d',strtotime("-$i days")),'pv'=>intval(1200+300*sin($i/3)+rand(0,200)),'uv'=>intval(420+90*sin($i/3)+rand(0,60))]; }, range(29,0,-1)),
        'pv_30d' => 42180, 'uv_30d' => 15630, 'week_growth' => 12.4,
        'top_articles' => [
            ['id' => 12, 'title' => '2026 企业官网建设全景指南', 'views' => 3820, 'cat_id' => 2, 'cat_name' => '行业资讯'],
            ['id' => 8,  'title' => '中小企业如何低成本搭建品牌站', 'views' => 2950, 'cat_id' => 2, 'cat_name' => '行业资讯'],
            ['id' => 21, 'title' => '得应盯 CMS 多租户SaaS能力解读', 'views' => 2410, 'cat_id' => 3, 'cat_name' => '产品动态'],
            ['id' => 5,  'title' => 'AI 自动写作上线，运营效率翻倍', 'views' => 1980, 'cat_id' => 3, 'cat_name' => '产品动态'],
            ['id' => 17, 'title' => '全国分站如何一键生成', 'views' => 1560, 'cat_id' => 4, 'cat_name' => '使用教程'],
            ['id' => 33, 'title' => '模板中心 DIY 布局实战', 'views' => 1240, 'cat_id' => 4, 'cat_name' => '使用教程'],
            ['id' => 9,  'title' => '响应式设计的 7 个要点', 'views' => 980,  'cat_id' => 2, 'cat_name' => '行业资讯'],
            ['id' => 41, 'title' => 'SEO 优化从入门到精通', 'views' => 760,  'cat_id' => 2, 'cat_name' => '行业资讯'],
        ],
        'top_products' => [
            ['id' => 7,  'title' => '得应盯企业官网定制版', 'views' => 4520, 'cover' => '', 'price' => '面议', 'cat_id' => 5, 'cat_name' => '建站套餐'],
            ['id' => 3,  'title' => '多租户 SaaS 建站系统', 'views' => 3980, 'cover' => '', 'price' => '￥19999/年', 'cat_id' => 5, 'cat_name' => '建站套餐'],
            ['id' => 11, 'title' => 'AI 智能写作助手', 'views' => 3120, 'cover' => '', 'price' => '￥999/年', 'cat_id' => 6, 'cat_name' => 'AI 工具'],
            ['id' => 15, 'title' => 'GEO 生成式引擎优化服务', 'views' => 2680, 'cover' => '', 'price' => '面议', 'cat_id' => 6, 'cat_name' => 'AI 工具'],
            ['id' => 9,  'title' => '全国分站集群部署', 'views' => 2240, 'cover' => '', 'price' => '￥5999', 'cat_id' => 5, 'cat_name' => '建站套餐'],
            ['id' => 19, 'title' => 'SEO 整站诊断优化', 'views' => 1860, 'cover' => '', 'price' => '￥2999', 'cat_id' => 6, 'cat_name' => 'AI 工具'],
            ['id' => 23, 'title' => '响应式模板商城', 'views' => 1520, 'cover' => '', 'price' => '￥499', 'cat_id' => 7, 'cat_name' => '模板市场'],
            ['id' => 27, 'title' => '小程序一键生成', 'views' => 1180, 'cover' => '', 'price' => '￥1999', 'cat_id' => 7, 'cat_name' => '模板市场'],
        ],
    ];
}

/** 常用城市经纬度（用于全国地图散点；缺失城市回退到省级中心或 [0,0]） */
function china_city_coords(): array
{
    return [
        '北京' => [116.40, 39.90], '上海' => [121.47, 31.23], '广州' => [113.26, 23.13],
        '深圳' => [114.07, 22.62], '成都' => [104.07, 30.57], '杭州' => [120.15, 30.27],
        '武汉' => [114.30, 30.59], '西安' => [108.94, 34.34], '南京' => [118.78, 32.04],
        '重庆' => [106.55, 29.56], '天津' => [117.20, 39.13], '苏州' => [120.62, 31.32],
        '郑州' => [113.62, 34.75], '长沙' => [112.94, 28.23], '沈阳' => [123.43, 41.80],
        '青岛' => [120.38, 36.07], '大连' => [121.62, 38.92], '厦门' => [118.10, 24.46],
        '昆明' => [102.71, 25.05], '合肥' => [117.27, 31.86], '福州' => [119.30, 26.08],
        '济南' => [117.00, 36.65], '哈尔滨' => [126.53, 45.80], '长春' => [125.32, 43.90],
        '石家庄' => [114.51, 38.04], '太原' => [112.55, 37.87], '南昌' => [115.86, 28.68],
        '南宁' => [108.33, 22.84], '贵阳' => [106.71, 26.65], '兰州' => [103.83, 36.06],
        '海口' => [110.20, 20.04], '乌鲁木齐' => [87.62, 43.82], '拉萨' => [91.11, 29.65],
        '呼和浩特' => [111.75, 40.84], '银川' => [106.27, 38.47], '西宁' => [101.78, 36.62],
        '保定' => [115.46, 38.87], '唐山' => [118.18, 39.63], '邯郸' => [114.49, 36.61],
        '无锡' => [120.30, 31.57], '宁波' => [121.55, 29.88], '东莞' => [113.75, 23.04],
        '佛山' => [113.12, 23.02], '温州' => [120.70, 28.00], '泉州' => [118.68, 24.87],
    ];
}

/** 将 cities 聚合数据转为 ECharts 地图散点所需的 [lng,lat,value,name] 数组 */
function map_visit_points(array $cities): array
{
    $coords = china_city_coords();
    $points = [];
    foreach ($cities as $c) {
        $city = $c['city'] ?? '';
        if (!isset($coords[$city])) continue;
        $points[] = ['name' => $city, 'value' => array_merge($coords[$city], [(int)($c['n'] ?? 0)])];
    }
    return $points;
}
