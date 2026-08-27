<?php
/**
 * deyingding-php 后台入口
 * 路由: admin.php?m=模块名
 */

// 长任务（AI 生图、大数据导出等）需要立即放开 PHP 执行时间和内存。
// 放在文件最开头、任何 require 之前，确保 30s 默认 max_execution_time 不会先触发 fatal error。
// 兼容 disable_functions：先 ini_set（一般不被禁），fallback set_time_limit
if (function_exists('ini_set')) { @ini_set('max_execution_time', '0'); }
if (function_exists('ini_set')) { @ini_set('memory_limit', '512M'); }
if (function_exists('set_time_limit')) { @set_time_limit(0); }

/* 未安装 → 跳转安装向导 */
if (!file_exists(__DIR__ . '/install.lock')) {
    header('Location: install/index.php');
    exit;
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/funcs.php';
/* 确保会话已启动：登录态与权限校验依赖 $_SESSION，不能依赖 config.php 是否恰好含 session_start() */
if (session_status() === PHP_SESSION_NONE) {
    // SameSite=Lax 可大幅降低跨站 CSRF 风险；HttpOnly 防 JS 读取
    session_set_cookie_params(['samesite' => 'Lax', 'httponly' => true, 'path' => '/']);
    session_start();
}
ensure_schema(); // 旧库自动升级（users 表 + site_id 列）

$m   = $_GET['m'] ?? 'dashboard';
$sid = current_site_id();

/* ---------- CSRF 守卫：所有写操作 POST 动作必须携带有效 token ---------- */
$csrf_protected = [
    'category_save', 'category_del', 'article_save', 'article_del', 'article_toggle',
    'api_save', 'api_fetch_models', 'ai_save_api', 'ai_seo', 'ai_geo', 'ai_plan', 'ai_generate', 'ai_illustrate', 'ai_unillustrated', 'ai_img_queue', 'ai_post_now',
    'geo_advert_save', 'geo_generate', 'geo_generate_batch', 'geo_sync_article', 'geo_del',
    'geo_kw_distill', 'geo_kw_del', 'geo_kw_feed', 'geo_kb_add', 'geo_kb_del',
    'geo_eeat', 'geo_schema', 'geo_uniq', 'geo_distribute',
    'geo_check', 'geo_monitor_settings', 'geo_monitor_del',
    'geo_competitor_settings', 'geo_monitor_competitor', 'geo_monitor_negative',
    'product_save', 'product_del', 'product_toggle',
    'download_cat', 'download_cat_del', 'download_save', 'download_del', 'download_file_upload', 'download_desc_save',
    'folder_save', 'folder_del', 'upload_do', 'upload_del', 'upload_json',
    'city_enable', 'city_import', 'city_tdk_all', 'ai_city_tdk_one', 'city_content_run', 'city_content_save', 'city_clear_content', 'city_notice', 'city_brand_save', 'city_save', 'city_del', 'city_clear_tdk', 'city_clear_all',
    'form_save', 'form_del', 'form_data_del',
    'settings_save', 'home_layout_save', 'visual_home_save',
    'tpl_upload', 'tpl_activate', 'tpl_del',
    'clear_cache',
];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($m, $csrf_protected, true, ) && !csrf_check()) {
    if (($_POST['ajax'] ?? '') === '1'
        || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')) {
        http_response_code(403);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'msg' => 'CSRF 校验失败，请刷新页面后重试']);
    } else {
        redirect('admin.php?m=' . urlencode($m), 'CSRF 校验失败，请刷新页面后重试');
    }
    exit;
}

/* ---------- 登录 / 登出（超管单通道） ---------- */
/* ---------- 滑动验证：生成 token（登录页加载时调用） ---------- */
if ($m === 'captcha_new') {
    header('Content-Type: application/json; charset=utf-8');
    $token = bin2hex(random_bytes(16));
    $_SESSION['captcha_token'] = $token;
    $_SESSION['captcha_pass']  = false;
    echo json_encode(['ok' => true, 'token' => $token]);
    exit;
}

if ($m === 'login') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // 滑动验证（后台设置 login_captcha 可关闭；防暴力破解）
        if (setting('login_captcha', '1') === '1') {
            $cap = json_decode((string)($_POST['captcha'] ?? ''), true);
            $capToken = (string)($_POST['captcha_token'] ?? '');
            $capOk = is_array($cap)
                && $capToken !== '' && !empty($_SESSION['captcha_token'])
                && hash_equals((string)$_SESSION['captcha_token'], $capToken)
                && (int)($cap['time'] ?? 0) >= 250 && (int)($cap['time'] ?? 0) <= 20000
                && (float)($cap['ratio'] ?? 0) >= 0.93
                && (int)($cap['points'] ?? 0) >= 3;
            if (!$capOk) {
                render('login.php', ['err' => '请先完成滑动验证']);
                exit;
            }
            unset($_SESSION['captcha_token'], $_SESSION['captcha_pass']);
        }
        $user = trim($_POST['username'] ?? '');
        $pass = $_POST['password'] ?? '';
        $row = DB::one('SELECT * FROM admin_users WHERE username=?', [$user]);
        $ok = false;
        if ($row) {
            // 标准 hash 校验
            if (password_verify($pass, $row['password'])) {
                $ok = true;
                // 仅当哈希算法/成本已升级时才重新哈希，避免每次登录都写库
                if (password_needs_rehash($row['password'], PASSWORD_DEFAULT)) {
                    try {
                        DB::run('UPDATE admin_users SET password=? WHERE id=?', [password_hash($pass, PASSWORD_DEFAULT), $row['id']]);
                    } catch (Throwable $e) {
                        // 升级失败不影响本次登录
                    }
                }
            }
            // 兼容历史明文 / 非 bcrypt 密码：校验通过后自动升级为 hash
            elseif (!str_starts_with($row['password'], '$2y$') && $pass === $row['password']) {
                $ok = true;
                try {
                    DB::run('UPDATE admin_users SET password=? WHERE id=?', [password_hash($pass, PASSWORD_DEFAULT), $row['id']]);
                } catch (Throwable $e) {
                    // 升级失败不影响本次登录，下次继续尝试兼容
                }
            }
        }
        if ($ok) {
            session_regenerate_id(true);
            $_SESSION['admin_id']   = (int)$row['id'];
            $_SESSION['admin_name'] = $row['username'];
            redirect('admin.php', '登录成功');
        }
        render('login.php', ['err' => '用户名或密码错误']);
        exit;
    }
    render('login.php', []);
    exit;
}
if ($m === 'logout') {
    // 登出必须为 POST + CSRF，避免被恶意页面伪造 GET 提前登出
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_check()) {
        http_response_code(403);
        exit('CSRF 校验失败');
    }
    session_destroy();
    redirect('admin.php?m=login');
}
require_login();

/* ---------- 仪表盘 ---------- */
if ($m === 'dashboard') {
    $data = [
        'cat_n'   => DB::one('SELECT COUNT(*) AS n FROM categories WHERE site_id=?', [$sid])['n'],
        'art_n'   => DB::one('SELECT COUNT(*) AS n FROM articles WHERE site_id=?', [$sid])['n'],
        'pro_n'   => DB::one('SELECT COUNT(*) AS n FROM products WHERE site_id=?', [$sid])['n'],
        'upload_n'=> DB::one('SELECT COUNT(*) AS n FROM uploads WHERE site_id=?', [$sid])['n'],
        'city_n'  => DB::one('SELECT COUNT(*) AS n FROM city_sites WHERE site_id=?', [$sid])['n'],
    ];
    $stats = dashboard_stats();
    $hasReal = (int)DB::one('SELECT COUNT(*) AS n FROM visits WHERE site_id=?', [$sid])['n'] > 0;
    // 新安装站点（无真实访问）：直接反馈 0，不使用演示假数据
    render('dashboard.php', array_merge($data, ['stats' => $stats, 'has_real' => $hasReal]));
    exit;
}

/* ---------- 平台用户管理已移除（单站点自用，不开放注册） ---------- */

/* ---------- 清空服务器缓存（OPCache + 临时文件）—— 必须在 require_login 之后，需登录 + CSRF ---------- */
if ($m === 'clear_cache') {
    $msg = [];
    if (function_exists('opcache_reset')) {
        if (@opcache_reset()) { $msg[] = 'OPCache 已重置'; } else { $msg[] = 'OPCache 重置失败（部分主机无权限）'; }
    } else {
        $msg[] = 'OPCache 未启用，无需重置';
    }
    // 清理可能的临时缩略图 / 临时文件
    $tmp = glob(UPLOAD_DIR . '{*.thumb.*,.DS_Store,~*}', GLOB_BRACE);
    if ($tmp) {
        array_map('unlink', $tmp);
        $msg[] = '清理临时文件 ' . count($tmp) . ' 个';
    }
    // 清空模板缓存（如有）
    $tplCache = __DIR__ . '/static/cache';
    if (is_dir($tplCache)) {
        $files = glob($tplCache . '/*');
        if ($files) { array_map('unlink', $files); $msg[] = '模板缓存 ' . count($files) . ' 个'; }
    }
    redirect('admin.php', '缓存已清空：' . implode('；', $msg));
}

/* ---------- 栏目管理（按站点） ---------- */
if ($m === 'categories') {
    // 栏目管理只显示普通栏目，下载分类在「下载专区」单独管理
    $cats = DB::all("SELECT * FROM categories WHERE site_id=? AND (type IS NULL OR type='' OR type!='download') ORDER BY sort ASC, id ASC", [$sid]);
    render('categories.php', ['cats' => $cats]);
    exit;
}
if ($m === 'category_save') {
    $id  = (int)($_POST['id'] ?? 0);
    $pid = (int)($_POST['pid'] ?? 0);
    if ($id > 0 && $id === $pid) {
        $pid = 0; // 防止把父栏目设为自己
    }
    $data = [
        (int)$pid, trim($_POST['name'] ?? ''), trim($_POST['type'] ?? 'article'),
        (int)($_POST['sort'] ?? 0), (int)($_POST['status'] ?? 1),
        trim($_POST['seo_title'] ?? ''), trim($_POST['seo_keywords'] ?? ''),
        trim($_POST['seo_description'] ?? ''),
    ];
    if ($data[1] === '') {
        redirect('admin.php?m=categories', '栏目名称不能为空');
    }
    if ($id > 0) {
        DB::run('UPDATE categories SET pid=?,name=?,type=?,sort=?,status=?,seo_title=?,seo_keywords=?,seo_description=? WHERE id=? AND site_id=?',
            [...$data, $id, $sid]);
    } else {
        DB::insert('INSERT INTO categories(site_id,pid,name,type,sort,status,seo_title,seo_keywords,seo_description) VALUES(?,?,?,?,?,?,?,?,?)', [$sid, ...$data]);
    }
    redirect('admin.php?m=categories', '栏目已保存');
}
if ($m === 'category_del') {
    $id = (int)($_POST['id'] ?? 0);
    DB::run('DELETE FROM categories WHERE id=? AND site_id=?', [$id, $sid]);
    redirect('admin.php?m=categories', '栏目已删除');
}

/* ---------- 文章管理（按站点） ---------- */
if ($m === 'articles') {
    $cats = DB::all('SELECT * FROM categories WHERE site_id=? ORDER BY sort ASC', [$sid]);
    $pg   = paginate(
        'SELECT COUNT(*) AS n FROM articles WHERE site_id=?',
        'SELECT * FROM articles WHERE site_id=? ORDER BY id DESC',
        [$sid], 20
    );
    render('articles.php', ['list' => $pg['list'], 'cats' => $cats, 'pg' => $pg, 'cfg' => settings_all(), 'sid' => $sid]);
    exit;
}
if ($m === 'article_save') {
    $id = (int)($_POST['id'] ?? 0);
    $data = [
        (int)($_POST['cat_id'] ?? 0), trim($_POST['title'] ?? ''),
        trim($_POST['summary'] ?? ''), trim($_POST['cover'] ?? ''),
        $_POST['content'] ?? '', trim($_POST['tags'] ?? ''),
        (int)($_POST['recommend'] ?? 0), (int)($_POST['status'] ?? 1),
        trim($_POST['seo_title'] ?? ''), trim($_POST['seo_keywords'] ?? ''),
        trim($_POST['seo_description'] ?? ''),
        trim($_POST['geo_summary'] ?? ''), trim($_POST['geo_faq'] ?? ''),
    ];
    if ($data[1] === '') {
        redirect('admin.php?m=articles', '标题不能为空');
    }
    // 封面兜底：留空时自动取正文第一张图（实现"封面取正文首图"）
    if ($data[3] === '' && $data[4] !== '') {
        if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/', $data[4], $mImg)) {
            $data[3] = $mImg[1];
        }
    }
    if ($id > 0) {
        DB::run('UPDATE articles SET cat_id=?,title=?,summary=?,cover=?,content=?,tags=?,recommend=?,status=?,seo_title=?,seo_keywords=?,seo_description=?,geo_summary=?,geo_faq=? WHERE id=? AND site_id=?',
            [...$data, $id, $sid]);
    } else {
        DB::insert('INSERT INTO articles(site_id,cat_id,title,summary,cover,content,tags,recommend,status,seo_title,seo_keywords,seo_description,geo_summary,geo_faq) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?)', [$sid, ...$data]);
    }
    redirect('admin.php?m=articles', '文章已保存');
}
if ($m === 'article_del') {
    DB::run('DELETE FROM articles WHERE id=? AND site_id=?', [(int)($_POST['id'] ?? 0), $sid]);
    redirect('admin.php?m=articles', '文章已删除');
}
if ($m === 'article_toggle') {
    // 列表快速切换：推荐/状态
    $id    = (int)($_POST['id'] ?? 0);
    $field = $_POST['field'] ?? '';
    if (in_array($field, ['recommend', 'status'], true) && $id > 0) {
        $row = DB::one('SELECT recommend, status FROM articles WHERE id=? AND site_id=?', [$id, $sid]);
        if ($row) {
            $new = $row[$field] ? 0 : 1;
            DB::run("UPDATE articles SET $field=? WHERE id=? AND site_id=?", [$new, $id, $sid]);
        }
    }
    redirect('admin.php?m=articles');
}

/* ---------- API 配置（全局独立板块，全站共用） ---------- */
if ($m === 'api_settings') {
    render('api_settings.php', ['cfg' => settings_all()]);
    exit;
}
if ($m === 'api_save') {
    require_admin();
    foreach (['ai_api_url', 'ai_api_key', 'ai_model', 'ai_img_url', 'ai_img_key', 'ai_img_model',
              'ip_geo_service', 'ip_geo_api_url', 'ip_geo_api_key'] as $k) {
        $v = trim((string)($_POST[$k] ?? ''));
        // 密钥字段：表单不回显明文，若未填写则保留已存储的原值，避免被清空
        if (in_array($k, ['ai_api_key', 'ai_img_key', 'ip_geo_api_key'], true) && $v === '') {
            $v = setting($k);
        }
        save_setting($k, $v);
    }
    // AJAX 保存（拉取模型前先静默保存当前输入）
    if (!empty($_POST['ajax']) && $_POST['ajax'] === '1') {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => true, 'msg' => '已自动保存']);
        exit;
    }
    // 兼容旧路由（原 AI 写 Tab 内的保存按钮），根据来源跳转
    $back = ($_POST['back'] ?? '') === 'articles' ? 'articles' : 'api_settings';
    redirect('admin.php?m=' . $back, 'API 配置已保存（全站通用）');
}

/* ---------- AJAX: 拉取 API 可用模型（OpenAI 兼容 /v1/models） ---------- */
if ($m === 'api_fetch_models') {
    require_admin();
    if (function_exists('ob_start')) { @ob_start(); }
    header('Content-Type: application/json; charset=utf-8');
    $which  = (string)($_POST['which'] ?? 'ai');
    $isImg  = ($which === 'img');
    $urlKey = $isImg ? 'ai_img_url'  : 'ai_api_url';
    $keyKey = $isImg ? 'ai_img_key'  : 'ai_api_key';
    // 优先用前端传来的（用户临时改的），否则回退到数据库已存的
    $url = trim((string)($_POST['url'] ?? ''));
    $key = trim((string)($_POST['key'] ?? ''));
    if ($url === '') { $url = setting($urlKey, ''); }
    if ($key === '') { $key = setting($keyKey); }
    if ($url === '' || $key === '') {
        echo json_encode(['ok' => false, 'msg' => '请先填写' . ($isImg ? '生图' : '写作') . ' API 地址与密钥（页面底部点「保存 API 配置」后再拉取）']);
        if (function_exists('ob_end_flush')) { @ob_end_flush(); }
        exit;
    }
    $res = ai_list_models($url, $key);
    if (!empty($res['ok'])) {
        echo json_encode(['ok' => true, 'models' => $res['models']]);
    } else {
        $msg = $res['msg'] ?? '拉取失败';
        if (!empty($res['http']))  { $msg .= '（HTTP ' . $res['http'] . '）'; }
        if (!empty($res['sample'])) { $msg .= ' 响应：' . $res['sample']; }
        echo json_encode(['ok' => false, 'msg' => $msg]);
    }
    if (function_exists('ob_end_flush')) { @ob_end_flush(); }
    exit;
}

/* ---------- AI 写作（并入「文章管理」） ---------- */
if ($m === 'ai_save_api') {
    require_admin();
    foreach (['ai_api_url', 'ai_api_key', 'ai_model', 'ai_img_url', 'ai_img_key', 'ai_img_model'] as $k) {
        save_setting($k, trim((string)($_POST[$k] ?? '')));
    }
    redirect('admin.php?m=api_settings', 'API 配置已保存（全站通用）');
}

/* ---------- AI SEO 自动填写（文章/产品共用） ---------- */
if ($m === 'ai_seo') {
    require_admin();
    header('Content-Type: application/json; charset=utf-8');
    $title   = trim((string)($_POST['title'] ?? ''));
    $content = (string)($_POST['content'] ?? '');
    if ($title === '') {
        echo json_encode(['ok' => false, 'msg' => '标题不能为空']);
        exit;
    }
    echo json_encode(ai_seo_fill($title, $content), JSON_UNESCAPED_UNICODE);
    exit;
}

/* ---------- AI GEO 优化（文章/产品共用） ---------- */
if ($m === 'ai_geo') {
    require_admin();
    header('Content-Type: application/json; charset=utf-8');
    $title   = trim((string)($_POST['title'] ?? ''));
    $content = (string)($_POST['content'] ?? '');
    $type    = trim((string)($_POST['type'] ?? 'article'));
    $advert  = trim((string)($_POST['advert'] ?? geo_advert($sid)));
    if ($title === '' || $content === '') {
        echo json_encode(['ok' => false, 'msg' => '请先填写标题与正文']);
        exit;
    }
    echo json_encode(ai_geo_optimize($title, $content, $type, $advert), JSON_UNESCAPED_UNICODE);
    exit;
}

/* ---------- GEO AI 优化板块（全站诊断 + 词条库） ---------- */
if ($m === 'geo') {
    $tab = $_GET['tab'] ?? 'content';
    $audit = geo_audit_site($sid);
    $entries = DB::all('SELECT * FROM geo_entries WHERE site_id=? ORDER BY id DESC LIMIT 50', [$sid]);
    $articles = DB::all('SELECT id,title,geo_summary,geo_faq FROM articles WHERE site_id=? AND status=1 ORDER BY id DESC LIMIT 60', [$sid]);
    render('geo.php', [
        'tab'        => $tab,
        'audit'      => $audit,
        'entries'    => $entries,
        'cats'       => DB::all('SELECT * FROM categories WHERE site_id=? ORDER BY sort ASC', [$sid]),
        'geo_advert' => geo_advert($sid),
        'keywords'   => geo_kw_list(),
        'kb'         => geo_kb_list(),
        'stats'      => geo_monitor_stats(),
        'articles'   => $articles,
        'cfg'        => settings_all(),
        'cityList'   => DB::all('SELECT id, city, status FROM city_sites WHERE site_id=? ORDER BY sort ASC, id ASC', [$sid]),
        'cityEnable' => setting('city_enable', '0'),
    ]);
    exit;
}
if ($m === 'geo_advert_save') {
    require_admin();
    $advert = trim((string)($_POST['advert'] ?? ''));
    save_setting('geo_advert', $advert);
    redirect('admin.php?m=geo', '商家广告/网站主体介绍已保存');
}
if ($m === 'geo_generate') {
    require_admin();
    header('Content-Type: application/json; charset=utf-8');
    $topic = trim((string)($_POST['topic'] ?? ''));
    $ctx   = trim((string)($_POST['context'] ?? ''));
    $kbCtx = geo_kb_context();
    if ($kbCtx !== '') {
        $ctx = $ctx !== '' ? ($ctx . "\n真实品牌信息：\n" . $kbCtx) : $kbCtx;
    }
    $catId = (int)($_POST['cat'] ?? 0);
    $advert = trim((string)($_POST['advert'] ?? geo_advert($sid)));
    if ($topic === '') {
        echo json_encode(['ok' => false, 'msg' => '请输入主题']);
        exit;
    }
    echo json_encode(geo_build_entry($topic, $ctx, $sid, $catId, $advert), JSON_UNESCAPED_UNICODE);
    exit;
}
if ($m === 'geo_generate_batch') {
    require_admin();
    header('Content-Type: application/json; charset=utf-8');
    $text  = trim((string)($_POST['topics'] ?? ''));
    $catId = (int)($_POST['cat'] ?? 0);
    $advert = trim((string)($_POST['advert'] ?? geo_advert($sid)));
    $topics = array_unique(array_filter(array_map('trim', preg_split('/[\r\n,，]+/u', $text))));
    if (empty($topics)) {
        echo json_encode(['ok' => false, 'msg' => '请输入至少一个主题，每行一个或用逗号分隔']);
        exit;
    }
    if (count($topics) > 30) {
        echo json_encode(['ok' => false, 'msg' => '单次最多批量生成 30 个主题']);
        exit;
    }
    $kbCtx = geo_kb_context();
    echo json_encode(geo_build_entries_batch($topics, $catId, $sid, $advert, $kbCtx), JSON_UNESCAPED_UNICODE);
    exit;
}
if ($m === 'geo_sync_article') {
    require_admin();
    $id = (int)($_POST['id'] ?? 0);
    $row = DB::one('SELECT * FROM geo_entries WHERE id=? AND site_id=?', [$id, $sid]);
    if (!$row) {
        redirect('admin.php?m=geo', '词条不存在');
    }
    $advert = $row['advert'] ?? '';
    $content = "<h3>{$row['question']}</h3>" . nl2br(htmlspecialchars($row['answer'], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    if ($advert !== '') {
        $content .= '<hr><p><b>关于我们：</b>' . nl2br(htmlspecialchars($advert, ENT_QUOTES | ENT_HTML5, 'UTF-8')) . '</p>';
    }
    DB::insert('INSERT INTO articles(site_id,cat_id,title,summary,content,tags,status,geo_faq) VALUES(?,?,?,?,?,?,1,?)',
        [$sid, $row['cat_id'], $row['topic'], mb_substr($row['answer'], 0, 80), $content, $row['keywords'], json_encode([['q' => $row['question'], 'a' => $row['answer']]], JSON_UNESCAPED_UNICODE)]);
    redirect('admin.php?m=geo', '已同步为文章：' . $row['topic']);
}
if ($m === 'geo_del') {
    require_admin();
    DB::run('DELETE FROM geo_entries WHERE id=? AND site_id=?', [(int)($_POST['id'] ?? 0), $sid]);
    redirect('admin.php?m=geo', '词条已删除');
}

/* ---------- GEO 内容：关键词蒸馏 ---------- */
if ($m === 'geo_kw_distill') {
    require_admin();
    header('Content-Type: application/json; charset=utf-8');
    $topic = trim((string)($_POST['topic'] ?? ''));
    if ($topic === '') { echo json_encode(['ok' => false, 'msg' => '请输入主题']); exit; }
    echo json_encode(geo_keyword_distill($topic), JSON_UNESCAPED_UNICODE);
    exit;
}
if ($m === 'geo_kw_del') {
    require_admin();
    geo_kw_del((int)($_POST['id'] ?? 0));
    redirect('admin.php?m=geo&tab=content', '关键词已删除');
}
if ($m === 'geo_kw_feed') {
    require_admin();
    $ids = isset($_POST['ids']) && is_array($_POST['ids']) ? array_map('intval', $_POST['ids']) : [];
    $n = geo_kw_feed_plan($ids);
    redirect('admin.php?m=geo&tab=content', $n > 0 ? ("已投喂 {$n} 个关键词到自动发文计划") : '请先勾选关键词');
}

/* ---------- GEO 内容：品牌资料库 ---------- */
if ($m === 'geo_kb_add') {
    require_admin();
    $title = trim((string)($_POST['title'] ?? ''));
    $content = trim((string)($_POST['content'] ?? ''));
    if ($title === '' || $content === '') {
        redirect('admin.php?m=geo&tab=content', '标题与内容均不能为空');
    }
    geo_kb_add($title, $content);
    redirect('admin.php?m=geo&tab=content', '品牌知识已添加');
}
if ($m === 'geo_kb_del') {
    require_admin();
    geo_kb_del((int)($_POST['id'] ?? 0));
    redirect('admin.php?m=geo&tab=content', '知识条目已删除');
}

/* ---------- GEO 内容：专业度评分 / FAQ 卡片 / 去重 / 分发（AJAX 预览） ---------- */
if ($m === 'geo_eeat') {
    require_admin();
    header('Content-Type: application/json; charset=utf-8');
    $id = (int)($_POST['article_id'] ?? 0);
    if ($id > 0) {
        $row = DB::one('SELECT title,content FROM articles WHERE id=? AND site_id=?', [$id, $sid]);
        if (!$row) { echo json_encode(['ok' => false, 'msg' => '文章不存在']); exit; }
        echo json_encode(geo_eeat_score($row['title'], $row['content']), JSON_UNESCAPED_UNICODE);
    } else {
        $title = trim((string)($_POST['title'] ?? ''));
        $content = trim((string)($_POST['content'] ?? ''));
        echo json_encode(geo_eeat_score($title, $content), JSON_UNESCAPED_UNICODE);
    }
    exit;
}
if ($m === 'geo_schema') {
    require_admin();
    header('Content-Type: application/json; charset=utf-8');
    $id = (int)($_POST['article_id'] ?? 0);
    $row = DB::one('SELECT title,geo_summary,geo_faq FROM articles WHERE id=? AND site_id=?', [$id, $sid]);
    if (!$row) { echo json_encode(['ok' => false, 'msg' => '文章不存在']); exit; }
    echo json_encode(['ok' => true, 'json' => geo_schema_json($row['title'], $row['geo_faq'], $row['geo_summary'])], JSON_UNESCAPED_UNICODE);
    exit;
}
if ($m === 'geo_uniq') {
    require_admin();
    header('Content-Type: application/json; charset=utf-8');
    $id = (int)($_POST['article_id'] ?? 0);
    $row = DB::one('SELECT content FROM articles WHERE id=? AND site_id=?', [$id, $sid]);
    if (!$row) { echo json_encode(['ok' => false, 'msg' => '文章不存在']); exit; }
    echo json_encode(geo_uniqueness($row['content'], $id), JSON_UNESCAPED_UNICODE);
    exit;
}
if ($m === 'geo_distribute') {
    require_admin();
    header('Content-Type: application/json; charset=utf-8');
    $id = (int)($_POST['article_id'] ?? 0);
    $article = geo_article($id);
    if (!$article) { echo json_encode(['ok' => false, 'msg' => '文章不存在']); exit; }
    echo json_encode(['ok' => true, 'md' => geo_distribute_md($article)], JSON_UNESCAPED_UNICODE);
    exit;
}

/* ---------- GEO 检测回流（被动 AI 来源 + 主动探针） ---------- */
if ($m === 'geo_monitor') {
    // 深链兼容：统一进入 GEO 中心并定位到「监控」Tab
    $_GET['tab'] = 'monitor';
    $tab = 'monitor';
    $audit = geo_audit_site($sid);
    $entries = DB::all('SELECT * FROM geo_entries WHERE site_id=? ORDER BY id DESC LIMIT 50', [$sid]);
    $articles = DB::all('SELECT id,title,geo_summary,geo_faq FROM articles WHERE site_id=? AND status=1 ORDER BY id DESC LIMIT 60', [$sid]);
    render('geo.php', [
        'tab'        => $tab,
        'audit'      => $audit,
        'entries'    => $entries,
        'cats'       => DB::all('SELECT * FROM categories WHERE site_id=? ORDER BY sort ASC', [$sid]),
        'geo_advert' => geo_advert($sid),
        'keywords'   => geo_kw_list(),
        'kb'         => geo_kb_list(),
        'stats'      => geo_monitor_stats(),
        'articles'   => $articles,
        'cfg'        => settings_all(),
        'cityList'   => DB::all('SELECT id, city, status FROM city_sites WHERE site_id=? ORDER BY sort ASC, id ASC', [$sid]),
        'cityEnable' => setting('city_enable', '0'),
    ]);
    exit;
}
if ($m === 'geo_check') {
    require_admin();
    header('Content-Type: application/json; charset=utf-8');
    $articleId = (int)($_POST['article_id'] ?? 0);
    if ($articleId > 0) {
        $platform = trim((string)($_POST['platform'] ?? 'deepseek'));
        echo json_encode(geo_monitor_run($articleId, $platform), JSON_UNESCAPED_UNICODE);
        exit;
    }
    // article_id=0：批量探针（每次处理上限，前端可循环调用直到 remaining=0）
    $limit = max(1, min(20, (int)($_POST['limit'] ?? 5)));
    echo json_encode(geo_monitor_batch($limit), JSON_UNESCAPED_UNICODE);
    exit;
}
if ($m === 'geo_monitor_settings') {
    require_admin();
    save_setting('site_url', trim((string)($_POST['site_url'] ?? '')));
    save_setting('geo_monitor_on', isset($_POST['geo_monitor_on']) ? '1' : '0');
    save_setting('geo_monitor_perday', (string)max(1, min(50, (int)($_POST['geo_monitor_perday'] ?? 5))));
    redirect('admin.php?m=geo_monitor', 'GEO 检测回流设置已保存');
}
if ($m === 'geo_monitor_del') {
    require_admin();
    DB::run('DELETE FROM geo_monitor WHERE id=? AND site_id=?', [(int)($_POST['id'] ?? 0), $sid]);
    redirect('admin.php?m=geo_monitor', '记录已删除');
}

/* ---------- GEO 监控：竞品对比 + 负面监控 ---------- */
if ($m === 'geo_competitor_settings') {
    require_admin();
    save_setting('geo_competitors', trim((string)($_POST['geo_competitors'] ?? '')));
    save_setting('geo_negative', trim((string)($_POST['geo_negative'] ?? '')));
    save_setting('site_url', trim((string)($_POST['site_url'] ?? '')));
    save_setting('geo_monitor_on', isset($_POST['geo_monitor_on']) ? '1' : '0');
    save_setting('geo_monitor_perday', (string)max(1, min(50, (int)($_POST['geo_monitor_perday'] ?? 5))));
    redirect('admin.php?m=geo_monitor', 'GEO 监控设置已保存');
}
if ($m === 'geo_monitor_competitor') {
    require_admin();
    header('Content-Type: application/json; charset=utf-8');
    $domain = trim((string)($_POST['domain'] ?? ''));
    $question = trim((string)($_POST['question'] ?? ''));
    $platform = trim((string)($_POST['platform'] ?? 'deepseek'));
    if ($domain === '' || $question === '') {
        echo json_encode(['ok' => false, 'msg' => '请输入竞品域名与对比问题']);
        exit;
    }
    echo json_encode(geo_monitor_competitor($question, $platform, $domain), JSON_UNESCAPED_UNICODE);
    exit;
}
if ($m === 'geo_monitor_negative') {
    require_admin();
    header('Content-Type: application/json; charset=utf-8');
    $question = trim((string)($_POST['question'] ?? ''));
    $platform = trim((string)($_POST['platform'] ?? 'deepseek'));
    if ($question === '') {
        echo json_encode(['ok' => false, 'msg' => '请输入负面监控问题']);
        exit;
    }
    echo json_encode(geo_monitor_negative($question, $platform), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($m === 'ai_plan') {
    require_admin();
    save_setting('ai_plan_on', isset($_POST['on']) ? '1' : '0');
    save_setting('ai_plan_perday', (int)($_POST['perday'] ?? 3));
    save_setting('ai_plan_times', trim((string)($_POST['times'] ?? '09:00,14:00,20:00')));
    save_setting('ai_plan_kw', trim((string)($_POST['kw'] ?? '')));
    save_setting('ai_plan_extra', trim((string)($_POST['extra'] ?? '')));
    save_setting('ai_plan_cat', (int)($_POST['cat'] ?? 0));
    save_setting('ai_plan_img', isset($_POST['img']) ? '1' : '0');
    save_setting('ai_plan_publish', isset($_POST['publish']) ? '1' : '0');
    save_setting('ai_plan_seo', isset($_POST['seo']) ? '1' : '0');
    save_setting('ai_plan_geo', isset($_POST['geo']) ? '1' : '0');
    redirect('admin.php?m=articles', '自动发文计划已保存');
}

if ($m === 'ai_generate') {
    require_admin();
    // 缓冲所有意外输出（PHP warning/notice/deprecated），保证响应体是纯 JSON，否则前端 r.json() 报 "Unexpected token <"
    if (function_exists('ob_start')) { @ob_start(); }
    header('Content-Type: application/json; charset=utf-8');
    $topic = trim((string)($_POST['topic'] ?? ''));
    $words = (int)($_POST['words'] ?? 1200);
    $tone  = trim((string)($_POST['tone'] ?? '亲切实战'));
    $extra = trim((string)($_POST['extra'] ?? ''));
    $withImg = !empty($_POST['with_img']);
    $doSeo = !empty($_POST['seo']);
    $doGeo = !empty($_POST['geo']);
    if ($topic === '') {
        echo json_encode(['ok' => false, 'msg' => '请输入主题/关键词']);
        if (function_exists('ob_end_flush')) { @ob_end_flush(); }
        exit;
    }
    $r = ai_build_article($topic, $words, $tone, $extra, $withImg, $doSeo, $doGeo);
    echo json_encode($r, JSON_UNESCAPED_UNICODE);
    if (function_exists('ob_end_flush')) { @ob_end_flush(); }
    exit;
}

if ($m === 'ai_illustrate') {
    require_admin();
    // 入队异步处理：立即返回，避免生图长请求（60~120s/张）占住 PHP-FPM worker 导致后台卡死
    if (function_exists('ob_start')) { @ob_start(); }
    header('Content-Type: application/json; charset=utf-8');
    $articleId = (int)($_POST['article_id'] ?? 0);
    $count = max(1, min(4, (int)($_POST['count'] ?? 2)));
    if ($articleId <= 0) {
        echo json_encode(['ok' => false, 'msg' => '请先保存文章，再为文章配图']);
        if (function_exists('ob_end_flush')) { @ob_end_flush(); }
        exit;
    }
    echo json_encode(ai_img_queue_add($articleId, $count), JSON_UNESCAPED_UNICODE);
    if (function_exists('ob_end_flush')) { @ob_end_flush(); }
    exit;
}

if ($m === 'ai_img_queue') {
    require_admin();
    // 配图队列状态（文章列表页轮询展示）
    if (function_exists('ob_start')) { @ob_start(); }
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(ai_img_queue_stats(), JSON_UNESCAPED_UNICODE);
    if (function_exists('ob_end_flush')) { @ob_end_flush(); }
    exit;
}

if ($m === 'ai_unillustrated') {
    require_admin();
    // 查询正文没有 <img> 的文章（未配图），供文章列表侧批量配图
    if (function_exists('ob_start')) { @ob_start(); }
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => true, 'list' => ai_unillustrated_articles()], JSON_UNESCAPED_UNICODE);
    if (function_exists('ob_end_flush')) { @ob_end_flush(); }
    exit;
}

if ($m === 'ai_post_now') {
    require_admin();
    $kws = array_filter(array_map('trim', explode("\n", setting('ai_plan_kw', ''))));
    if (empty($kws)) {
        redirect('admin.php?m=articles', '请先在「自动发文计划」填写关键词池');
    }
    $kw = $kws[array_rand($kws)];
    $withImg = false; // 全国分站重构 2025：自动发文默认纯文字（文图分离、小服务器友好；如需配图 → 文章列表点「🎨 配图」入队或后台 ai_unillustrated 批量处理）
    $doSeo = setting('ai_plan_seo', '1') === '1';
    $doGeo = setting('ai_plan_geo', '1') === '1';
    $r = ai_build_article($kw, 1200, '亲切实战', setting('ai_plan_extra', ''), $withImg, $doSeo, $doGeo);
    if (!$r['ok']) {
        redirect('admin.php?m=articles', '生成失败：' . $r['msg']);
    }
    $catId = (int)setting('ai_plan_cat', 0);
    $status = setting('ai_plan_publish', '1') === '1' ? 1 : 0;
    $summary = mb_substr(strip_tags($r['content']), 0, 80);
    DB::insert('INSERT INTO articles(site_id,cat_id,title,summary,cover,content,tags,recommend,status,seo_title,seo_keywords,seo_description,geo_summary,geo_faq) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
        [$sid, $catId, $r['title'], $summary, $r['cover'], $r['content'], $kw, 0, $status,
         $r['seo_title'] ?? '', $r['seo_keywords'] ?? '', $r['seo_description'] ?? '',
         $r['geo_summary'] ?? '', $r['geo_faq'] ?? '']);
    DB::run('INSERT INTO ai_post_log(site_id,keyword,model,has_image) VALUES(?,?,?,?)', [$sid, $kw, setting('ai_model', 'deepseek-chat'), $r['img_count'] > 0 ? 1 : 0]);
    redirect('admin.php?m=articles', '已手动发布一篇：' . $r['title']);
}

if ($m === 'upload_json') {
    require_admin();
    header('Content-Type: application/json; charset=utf-8');
    $r = handle_upload('file');
    if ($r['ok']) {
        echo json_encode(['ok' => true, 'url' => $r['path']]);
    } else {
        echo json_encode(['ok' => false, 'msg' => $r['msg']]);
    }
    exit;
}

if ($m === 'uploads_picker') {
    require_admin();
    header('Content-Type: application/json; charset=utf-8');
    $rows = DB::all('SELECT path,name FROM uploads WHERE site_id=? ORDER BY id DESC LIMIT 60', [$sid]);
    echo json_encode(['ok' => true, 'list' => array_map(function ($x) {
        return ['url' => $x['path'], 'name' => $x['name']];
    }, $rows)]);
    exit;
}

/* ---------- 产品管理（按站点） ---------- */
if ($m === 'products') {
    $cats = DB::all('SELECT * FROM categories WHERE site_id=? ORDER BY sort ASC', [$sid]);
    $pg   = paginate(
        'SELECT COUNT(*) AS n FROM products WHERE site_id=?',
        'SELECT * FROM products WHERE site_id=? ORDER BY id DESC',
        [$sid], 20
    );
    render('products.php', ['list' => $pg['list'], 'cats' => $cats, 'pg' => $pg]);
    exit;
}
if ($m === 'product_save') {
    $id = (int)($_POST['id'] ?? 0);
    $data = [
        (int)($_POST['cat_id'] ?? 0), trim($_POST['title'] ?? ''),
        trim($_POST['summary'] ?? ''), trim($_POST['cover'] ?? ''),
        trim($_POST['price'] ?? '面议'), $_POST['content'] ?? '',
        (int)($_POST['recommend'] ?? 0), (int)($_POST['status'] ?? 1),
        trim($_POST['seo_title'] ?? ''), trim($_POST['seo_keywords'] ?? ''),
        trim($_POST['seo_description'] ?? ''),
        trim($_POST['geo_summary'] ?? ''), trim($_POST['geo_faq'] ?? ''),
    ];
    if ($data[1] === '') {
        redirect('admin.php?m=products', '标题不能为空');
    }
    if ($id > 0) {
        DB::run('UPDATE products SET cat_id=?,title=?,summary=?,cover=?,price=?,content=?,recommend=?,status=?,seo_title=?,seo_keywords=?,seo_description=?,geo_summary=?,geo_faq=? WHERE id=? AND site_id=?',
            [...$data, $id, $sid]);
    } else {
        DB::insert('INSERT INTO products(site_id,cat_id,title,summary,cover,price,content,recommend,status,seo_title,seo_keywords,seo_description,geo_summary,geo_faq) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?)', [$sid, ...$data]);
    }
    redirect('admin.php?m=products', '产品已保存');
}
if ($m === 'product_del') {
    DB::run('DELETE FROM products WHERE id=? AND site_id=?', [(int)($_POST['id'] ?? 0), $sid]);
    redirect('admin.php?m=products', '产品已删除');
}
if ($m === 'product_toggle') {
    $id    = (int)($_POST['id'] ?? 0);
    $field = $_POST['field'] ?? '';
    if (in_array($field, ['recommend', 'status'], true) && $id > 0) {
        $row = DB::one('SELECT recommend, status FROM products WHERE id=? AND site_id=?', [$id, $sid]);
        if ($row) {
            $new = $row[$field] ? 0 : 1;
            DB::run("UPDATE products SET $field=? WHERE id=? AND site_id=?", [$new, $id, $sid]);
        }
    }
    redirect('admin.php?m=products');
}

/* ---------- 下载专区（可分类 · 可下载源码） ---------- */
if ($m === 'downloads') {
    $cats = DB::all('SELECT * FROM categories WHERE site_id=? AND type=? ORDER BY sort ASC, id ASC', [$sid, 'download']);
    $fid  = (int)($_GET['cat'] ?? 0);
    $where = 'site_id=?';
    $params = [$sid];
    if ($fid > 0) {
        $where .= ' AND cat_id=?';
        $params[] = $fid;
    }
    $pg = paginate(
        "SELECT COUNT(*) AS n FROM downloads WHERE $where",
        "SELECT * FROM downloads WHERE $where ORDER BY sort ASC, id DESC",
        $params, 20
    );
    render('downloads.php', [
        'list'     => $pg['list'],
        'cats'     => $cats,
        'fid'      => $fid,
        'pg'       => $pg,
        'desc'     => setting('download_desc', ''),
    ]);
    exit;
}
if ($m === 'download_cat') {
    // 下载专区分类快速管理：复用栏目表 type=download
    $id  = (int)($_POST['id'] ?? 0);
    $pid = 0;
    $data = [
        (int)$pid, trim($_POST['name'] ?? ''), 'download',
        (int)($_POST['sort'] ?? 0), (int)($_POST['status'] ?? 1),
        trim($_POST['seo_title'] ?? ''), trim($_POST['seo_keywords'] ?? ''),
        trim($_POST['seo_description'] ?? ''),
    ];
    if ($data[1] === '') {
        redirect('admin.php?m=downloads', '分类名称不能为空');
    }
    if ($id > 0) {
        DB::run('UPDATE categories SET pid=?,name=?,type=?,sort=?,status=?,seo_title=?,seo_keywords=?,seo_description=? WHERE id=? AND site_id=?',
            [...$data, $id, $sid]);
    } else {
        DB::insert('INSERT INTO categories(site_id,pid,name,type,sort,status,seo_title,seo_keywords,seo_description) VALUES(?,?,?,?,?,?,?,?,?)', [$sid, ...$data]);
    }
    redirect('admin.php?m=downloads', '分类已保存');
}
if ($m === 'download_cat_del') {
    $cid = (int)($_POST['id'] ?? 0);
    if ($cid > 0) {
        DB::run('DELETE FROM categories WHERE id=? AND type=? AND site_id=?', [$cid, 'download', $sid]);
        // 该分类下的下载项归为未分类
        DB::run('UPDATE downloads SET cat_id=0 WHERE cat_id=? AND site_id=?', [$cid, $sid]);
    }
    redirect('admin.php?m=downloads', '分类已删除');
}
if ($m === 'download_save') {
    $id = (int)($_POST['id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    if ($title === '') {
        redirect('admin.php?m=downloads', '标题不能为空');
    }
    $data = [
        (int)($_POST['cat_id'] ?? 0), $title,
        trim($_POST['cover'] ?? ''), trim($_POST['file_url'] ?? ''),
        trim($_POST['file_name'] ?? ''), trim($_POST['file_ext'] ?? ''),
        trim($_POST['file_size'] ?? ''), trim($_POST['version'] ?? ''),
        trim($_POST['summary'] ?? ''), $_POST['description'] ?? '',
        (int)($_POST['sort'] ?? 0), (int)($_POST['status'] ?? 1),
    ];
    if ($id > 0) {
        DB::run('UPDATE downloads SET cat_id=?,title=?,cover=?,file_url=?,file_name=?,file_ext=?,file_size=?,version=?,summary=?,description=?,sort=?,status=? WHERE id=? AND site_id=?',
            [...$data, $id, $sid]);
    } else {
        DB::insert('INSERT INTO downloads(site_id,cat_id,title,cover,file_url,file_name,file_ext,file_size,version,summary,description,sort,status) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?)', [$sid, ...$data]);
    }
    $back = (int)($_POST['cat_id'] ?? 0);
    redirect('admin.php?m=downloads' . ($back ? '&cat=' . $back : ''), '下载项已保存');
}
if ($m === 'download_del') {
    DB::run('DELETE FROM downloads WHERE id=? AND site_id=?', [(int)($_POST['id'] ?? 0), $sid]);
    redirect('admin.php?m=downloads', '下载项已删除');
}
if ($m === 'download_file_upload') {
    require_admin();
    header('Content-Type: application/json; charset=utf-8');
    if (empty($_FILES['file']['tmp_name'])) {
        echo json_encode(['ok' => false, 'msg' => '未选择文件']);
        exit;
    }
    $f = $_FILES['file'];
    if ($f['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['ok' => false, 'msg' => '上传失败（错误码 ' . $f['error'] . '）']);
        exit;
    }
    $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
    // 危险类型（php/html/exe/js/json/css）一律禁止，避免上传即 RCE / 挂马
    $allow = ['zip', 'rar', '7z', 'gz', 'tar', 'txt', 'md', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'apk'];
    if (!in_array($ext, $allow, true)) {
        echo json_encode(['ok' => false, 'msg' => '不支持的文件类型：' . $ext]);
        exit;
    }
    if ($f['size'] > 200 * 1024 * 1024) {
        echo json_encode(['ok' => false, 'msg' => '文件不能超过 200MB']);
        exit;
    }
    $ym  = date('Ym');
    $dir = UPLOAD_DIR . 'site_' . $sid . '/' . $ym;
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    $name = date('His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $path = 'site_' . $sid . '/' . $ym . '/' . $name;
    if (!move_uploaded_file($f['tmp_name'], UPLOAD_DIR . $path)) {
        echo json_encode(['ok' => false, 'msg' => '保存失败，请检查 uploads 目录权限']);
        exit;
    }
    $size = $f['size'];
    $sizeStr = $size >= 1048576 ? round($size / 1048576, 2) . ' MB' : (($size >= 1024) ? round($size / 1024, 1) . ' KB' : $size . ' B');
    echo json_encode([
        'ok'       => true,
        'url'      => UPLOAD_URL . $path,
        'name'     => $f['name'],
        'ext'      => $ext,
        'size'     => $sizeStr,
        'msg'      => '上传成功',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
if ($m === 'download_desc_save') {
    save_setting('download_desc', trim($_POST['download_desc'] ?? ''));
    redirect('admin.php?m=downloads', '下载专区说明已保存');
}

/* ---------- 图片空间（文件夹管理） ---------- */
if ($m === 'uploads') {
    // 旧库兼容：自动补充 folder_id 列与 folders 表
    try {
        DB::one('SELECT folder_id FROM uploads LIMIT 1');
    } catch (Throwable $e) {
        DB::run('ALTER TABLE uploads ADD COLUMN folder_id INT UNSIGNED NOT NULL DEFAULT 0 COMMENT "所属文件夹" AFTER id');
    }
    DB::run("CREATE TABLE IF NOT EXISTS folders (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        sort INT NOT NULL DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $fid = (int)($_GET['fid'] ?? 0);
    $folders = DB::all('SELECT f.*, (SELECT COUNT(*) FROM uploads u WHERE u.folder_id=f.id AND u.site_id=f.site_id) AS cnt FROM folders f WHERE f.site_id=? ORDER BY f.sort ASC, f.id ASC', [$sid]);
    if ($fid > 0) {
        $pg = paginate(
            'SELECT COUNT(*) AS n FROM uploads WHERE folder_id=? AND site_id=?',
            'SELECT * FROM uploads WHERE folder_id=? AND site_id=? ORDER BY id DESC',
            [$fid, $sid], 24
        );
    } else {
        $pg = paginate(
            'SELECT COUNT(*) AS n FROM uploads WHERE site_id=?',
            'SELECT * FROM uploads WHERE site_id=? ORDER BY id DESC',
            [$sid], 24
        );
    }
    render('uploads.php', ['list' => $pg['list'], 'pg' => $pg, 'folders' => $folders, 'fid' => $fid]);
    exit;
}
if ($m === 'folder_save') {
    $id   = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    if ($name === '') {
        redirect('admin.php?m=uploads', '文件夹名不能为空');
    }
    if ($id > 0) {
        DB::run('UPDATE folders SET name=? WHERE id=? AND site_id=?', [$name, $id, $sid]);
    } else {
        DB::insert('INSERT INTO folders(site_id,name) VALUES(?,?)', [$sid, $name]);
    }
    redirect('admin.php?m=uploads', '文件夹已保存');
}
if ($m === 'folder_del') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        DB::run('UPDATE uploads SET folder_id=0 WHERE folder_id=? AND site_id=?', [$id, $sid]);
        DB::run('DELETE FROM folders WHERE id=? AND site_id=?', [$id, $sid]);
    }
    redirect('admin.php?m=uploads', '文件夹已删除，其中的图片已移至「全部图片」');
}
if ($m === 'upload_do') {
    $folderId = (int)($_POST['folder_id'] ?? 0);
    // 支持多文件（file[]）
    if (isset($_FILES['file']['name']) && is_array($_FILES['file']['name'])) {
        $ok = 0; $errs = [];
        foreach ($_FILES['file']['name'] as $i => $name) {
            $r = handle_upload('file', $i, $folderId);
            if ($r['ok']) { $ok++; } else { $errs[] = $r['msg']; }
        }
        $msg = $ok ? "成功上传 {$ok} 张" : '';
        if ($errs) { $msg .= ($msg ? '，' : '') . '失败 ' . count($errs) . ' 张：' . implode('；', array_unique($errs)); }
        redirect('admin.php?m=uploads&fid=' . $folderId, $msg ?: '未选择文件');
    }
    $r = handle_upload('file', null, $folderId);
    redirect('admin.php?m=uploads&fid=' . $folderId, $r['msg']);
}
if ($m === 'upload_del') {
    $id = (int)($_POST['id'] ?? 0);
    $row = DB::one('SELECT * FROM uploads WHERE id=? AND site_id=?', [$id, $sid]);
    if ($row) {
        @unlink(UPLOAD_DIR . $row['path']);
        DB::run('DELETE FROM uploads WHERE id=? AND site_id=?', [$id, $sid]);
    }
    redirect('admin.php?m=uploads', '图片已删除');
}
/* 编辑器图片库接口：返回 JSON 图片列表（分页，按站点） */
if ($m === 'pic_lib') {
    header('Content-Type: application/json; charset=utf-8');
    $per  = 30;
    $page = max(1, (int)($_GET['page'] ?? 1));
    $total = (int)(DB::one('SELECT COUNT(*) AS n FROM uploads WHERE site_id=?', [$sid])['n'] ?? 0);
    $pages = max(1, (int)ceil($total / $per));
    $list = DB::all('SELECT id,name,path FROM uploads WHERE site_id=? ORDER BY id DESC LIMIT ' . (($page - 1) * $per) . ", $per", [$sid]);
    foreach ($list as &$r) {
        $r['url'] = UPLOAD_URL . $r['path'];
    }
    echo json_encode(['list' => $list, 'total' => $total, 'pages' => $pages, 'page' => $page], JSON_UNESCAPED_UNICODE);
    exit;
}

/* ---------- 全国分站（按站点） ---------- */
if ($m === 'citysites') {
    // 旧库兼容：无 pinyin 列时自动补充（无需重建数据库）
    try {
        DB::one('SELECT pinyin FROM city_sites LIMIT 1');
    } catch (Throwable $e) {
        DB::run('ALTER TABLE city_sites ADD COLUMN pinyin VARCHAR(50) NOT NULL DEFAULT "" COMMENT "城市拼音（URL 后缀）" AFTER city');
    }
    // 旧库兼容：无 tdk_try_at 列时自动补充（AI SEO 跑过时间戳，跑过含失败就不重试）
    try {
        DB::one('SELECT tdk_try_at FROM city_sites LIMIT 1');
    } catch (Throwable $e) {
        DB::run('ALTER TABLE city_sites ADD COLUMN tdk_try_at DATETIME DEFAULT NULL COMMENT "上次 AI SEO 尝试时间（含失败）" AFTER description');
    }
    render('citysites.php', [
        'enable' => setting('city_enable', '0'),
        'list'   => DB::all('SELECT * FROM city_sites WHERE site_id=? ORDER BY sort ASC, id ASC', [$sid]),
    ]);
    exit;
}
if ($m === 'city_enable') {
    $on = $_POST['enable'] === '1' ? '1' : '0';
    // UPSERT：没有记录时自动插入，避免首次保存不生效
    DB::run("INSERT INTO settings(site_id,`key`,`value`) VALUES(?,?,?) ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)", [$sid, 'city_enable', $on]);
    // 开启分站时，若还没有任何城市，自动一键导入全国站点
    if ($on === '1') {
        $n = (int)(DB::one('SELECT COUNT(*) AS n FROM city_sites WHERE site_id=?', [$sid])['n'] ?? 0);
        if ($n === 0) {
            $added = import_national_cities();
            redirect('admin.php?m=citysites', "全国分站已开启，自动导入 {$added} 个城市分站");
        }
    }
    redirect('admin.php?m=citysites', '全国分站开关已更新');
}
if ($m === 'city_import') {
    $added = import_national_cities();
    redirect('admin.php?m=citysites', "导入完成，新增 {$added} 个城市分站");
}
if ($m === 'city_tdk_all') {
    require_admin();
    $industry = trim((string)($_POST['industry'] ?? '网站建设'));
    $r = city_tdk_auto($industry);
    redirect('admin.php?m=citysites', "已为 {$r['updated']} 个分站生成独立 SEO（共 {$r['total']} 城）");
}
if ($m === 'ai_city_tdk_one') {
    require_admin();
    // 强力接管：清掉所有嵌套 buffer + 抑制 PHP Warning/Notice（防止 deprecation/undefined 污染 JSON 响应）
    while (ob_get_level() > 0) { @ob_end_clean(); }
    @ini_set('display_errors', '0');
    if (function_exists('set_error_handler')) {
        set_error_handler(function ($errno, $errstr) { return true; }, E_ALL & ~E_ERROR & ~E_PARSE & ~E_USER_ERROR);
    }
    if (function_exists('ob_start')) { @ob_start(); }
    header('Content-Type: application/json; charset=utf-8');
    header('X-Accel-Buffering: no');
    try {
        $cityId   = (int)($_POST['city_id'] ?? 0);
        $industry = trim((string)($_POST['industry'] ?? '网站建设'));
        $city = DB::one('SELECT * FROM city_sites WHERE id=? AND site_id=?', [$cityId, $sid]);
        if (!$city) {
            echo json_encode(['ok' => false, 'msg' => '城市不存在']);
            if (function_exists('ob_end_flush')) { @ob_end_flush(); }
            exit;
        }
        // 已填过 SEO 的城市跳过（避免重复扣 AI 额度；想强制覆盖可手动编辑该分站 title_suffix 后再删掉重跑）
        if (trim((string)$city['title_suffix']) !== '' && trim((string)$city['keywords']) !== '' && trim((string)$city['description']) !== '') {
            echo json_encode(['ok' => false, 'dup' => true, 'msg' => $city['city'] . ' 已有 SEO，跳过（清空标题后缀后重跑可强制覆盖）']);
            if (function_exists('ob_end_flush')) { @ob_end_flush(); }
            exit;
        }
        $r = ai_city_tdk($city['city'], $industry);
        if (!$r['ok']) {
            // 失败也置 tdk_try_at（避免无限重复尝试浪费 AI 额度）
            DB::run('UPDATE city_sites SET tdk_try_at=NOW() WHERE id=? AND site_id=?', [$city['id'], $sid]);
            echo json_encode(['ok' => false, 'msg' => 'AI 返回失败：' . $r['msg']]);
            if (function_exists('ob_end_flush')) { @ob_end_flush(); }
            exit;
        }
        // 落库（每城独立 TDK，覆盖模板版；同时记 tdk_try_at）
        DB::run('UPDATE city_sites SET title_suffix=?,keywords=?,description=?,tdk_try_at=NOW() WHERE id=? AND site_id=?', [$r['title_suffix'], $r['keywords'], $r['description'], $city['id'], $sid]);
        echo json_encode(['ok' => true, 'msg' => '已更新《' . $city['city'] . '》的 SEO', 'title_suffix' => $r['title_suffix']]);
        if (function_exists('ob_end_flush')) { @ob_end_flush(); }
        exit;
    } catch (Throwable $e) {
        @error_log('[ai_city_tdk_one] exception: ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
        if (!headers_sent()) { header('Content-Type: application/json; charset=utf-8'); }
        echo json_encode(['ok' => false, 'msg' => '服务异常：' . $e->getMessage()]);
        if (function_exists('ob_end_flush')) { @ob_end_flush(); }
        exit;
    }
}
if ($m === 'city_content_run') {
    // ===== 全国分站内容重构（2025）：写回分站表，不写 articles =====
    require_admin();
    if (function_exists('ob_start')) { @ob_start(); }
    header('Content-Type: application/json; charset=utf-8');
    $cityId   = (int)($_POST['city_id'] ?? 0);
    $industry = trim((string)($_POST['industry'] ?? ''));
    if ($industry === '') { $industry = '网站建设'; }
    $force    = (int)($_POST['force'] ?? 0); // 1 = 强制重生成（不管 content_status）
    $words    = (int)($_POST['words'] ?? 1200);

    $city = DB::one('SELECT * FROM city_sites WHERE id=? AND site_id=? AND status=1', [$cityId, $sid]);
    if (!$city) {
        echo json_encode(['ok' => false, 'msg' => '城市不存在或已停用']);
        if (function_exists('ob_end_flush')) { @ob_end_flush(); }
        exit;
    }
    $existingContent = trim((string)($city['content'] ?? ''));
    // 精确去重：content_status=1 + content 非空 → 跳过（不再 LIKE 模糊匹配 articles 表）
    if (!$force && (int)$city['content_status'] === 1 && $existingContent !== '') {
        echo json_encode([
            'ok' => false, 'dup' => true,
            'msg' => $city['city'] . ' 已生成分站内容（' . (string)$city['content_at'] . '），跳过',
            'content_status' => 1,
            'content_title' => (string)$city['content_title'],
        ]);
        if (function_exists('ob_end_flush')) { @ob_end_flush(); }
        exit;
    }
    // 标记「生成中」便于 UI 实时状态（断线也能查到）
    DB::run('UPDATE city_sites SET content_status=2, content_err=? WHERE id=? AND site_id=?', ['生成中…', $cityId, $sid]);
    $r = ai_city_content($city['city'], $industry, $words);
    if (!$r['ok']) {
        DB::run('UPDATE city_sites SET content_status=3, content_err=? WHERE id=? AND site_id=?', [mb_substr($r['msg'] ?? '未知原因', 0, 200), $cityId, $sid]);
        echo json_encode(['ok' => false, 'msg' => 'AI 生成失败：' . ($r['msg'] ?? ''), 'content_status' => 3]);
        if (function_exists('ob_end_flush')) { @ob_end_flush(); }
        exit;
    }
    // 落库：直接写回 city_sites.content（不走 articles 表）
    DB::run('UPDATE city_sites SET content_title=?, content=?, content_at=NOW(), content_status=1, content_err=? WHERE id=? AND site_id=?',
        [$r['title'], $r['content'], '', $cityId, $sid]);
    echo json_encode([
        'ok' => true,
        'msg' => '已生成《' . $r['title'] . '》',
        'title' => $r['title'],
        'content_status' => 1,
    ]);
    if (function_exists('ob_end_flush')) { @ob_end_flush(); }
    exit;
}
if ($m === 'city_content_save') {
    // 手工保存分站内容（含编辑/清空）
    require_admin();
    if (function_exists('ob_start')) { @ob_start(); }
    header('Content-Type: application/json; charset=utf-8');
    $cityId = (int)($_POST['city_id'] ?? 0);
    $title  = trim((string)($_POST['content_title'] ?? ''));
    $content = (string)($_POST['content'] ?? '');
    if ($cityId <= 0) {
        echo json_encode(['ok' => false, 'msg' => '城市 ID 缺失']);
        if (function_exists('ob_end_flush')) { @ob_end_flush(); }
        exit;
    }
    if ($content === '') {
        // 清空（彻底重置）
        DB::run('UPDATE city_sites SET content_title=?, content=?, content_at=NULL, content_status=0, content_err=? WHERE id=? AND site_id=?',
            ['', '', '', $cityId, $sid]);
        echo json_encode(['ok' => true, 'msg' => '已清空分站内容']);
    } else {
        DB::run('UPDATE city_sites SET content_title=?, content=?, content_at=NOW(), content_status=1, content_err=? WHERE id=? AND site_id=?',
            [$title, $content, '', $cityId, $sid]);
        echo json_encode(['ok' => true, 'msg' => '已保存']);
    }
    if (function_exists('ob_end_flush')) { @ob_end_flush(); }
    exit;
}
if ($m === 'city_save') {
    $id = (int)($_POST['id'] ?? 0);
    $city = trim($_POST['city'] ?? '');
    $pinyin = trim($_POST['pinyin'] ?? '');
    if ($pinyin === '') {
        $pinyin = city_pinyin($city); // 内置数据自动补全拼音
    }
    $data = [
        $city, $pinyin, trim($_POST['title_suffix'] ?? ''),
        trim($_POST['keywords'] ?? ''), trim($_POST['description'] ?? ''),
        (int)($_POST['status'] ?? 1), (int)($_POST['sort'] ?? 0),
    ];
    if ($data[0] === '') {
        redirect('admin.php?m=citysites', '城市名不能为空');
    }
    if ($data[1] === '') {
        redirect('admin.php?m=citysites', '请填写城市拼音（URL 后缀，如 baoding）');
    }
    if ($id > 0) {
        DB::run('UPDATE city_sites SET city=?,pinyin=?,title_suffix=?,keywords=?,description=?,status=?,sort=? WHERE id=? AND site_id=?',
            [...$data, $id, $sid]);
    } else {
        DB::insert('INSERT INTO city_sites(site_id,city,pinyin,title_suffix,keywords,description,status,sort) VALUES(?,?,?,?,?,?,?,?)', [$sid, ...$data]);
    }
    redirect('admin.php?m=citysites', '分站已保存');
}
if ($m === 'city_del') {
    DB::run('DELETE FROM city_sites WHERE id=? AND site_id=?', [(int)($_POST['id'] ?? 0), $sid]);
    redirect('admin.php?m=citysites', '分站已删除');
}
if ($m === 'city_clear_tdk') {
    require_admin();
    // 清空所有分站的 SEO 字段（标题/关键词/描述）+ 重置尝试时间戳（让 AI 可重新跑）
    $n = (int)DB::one('SELECT COUNT(*) AS n FROM city_sites WHERE site_id=?', [$sid])['n'];
    DB::run('UPDATE city_sites SET title_suffix=?, keywords=?, description=?, tdk_try_at=NULL WHERE site_id=?', ['', '', '', $sid]);
    redirect('admin.php?m=citysites', "已清空 {$n} 个分站的 SEO 字段（城市保留），可立刻重新跑模板版/AI 版生成");
}
if ($m === 'city_clear_content') {
    // ===== 全国分站重构（2025）：清空所有分站的「内容」字段（与 SEO 清空分开）=====
    require_admin();
    $n = (int)DB::one('SELECT COUNT(*) AS n FROM city_sites WHERE site_id=?', [$sid])['n'];
    DB::run('UPDATE city_sites SET content_title=?, content=?, content_at=NULL, content_status=0, content_err=? WHERE site_id=?',
        ['', '', '', $sid]);
    redirect('admin.php?m=citysites', "已清空 {$n} 个分站的内容（content）字段，可立刻重新跑 AI 内容生成");
}
if ($m === 'city_clear_all') {
    require_admin();
    // 彻底删除所有分站（兜底，谨慎）+ 重置 AUTO_INCREMENT（避免 ID 叠加）
    $n = (int)DB::one('SELECT COUNT(*) AS n FROM city_sites WHERE site_id=?', [$sid])['n'];
    DB::run('DELETE FROM city_sites WHERE site_id=?', [$sid]);
    try { DB::run('ALTER TABLE city_sites AUTO_INCREMENT = 1'); } catch (Throwable $e) { /* ignore */ }
    redirect('admin.php?m=citysites', "已彻底删除 {$n} 个分站（ID 已重置），请重新「一键导入全国分站」");
}
if ($m === 'city_notice') {
    require_admin();
    $notice = trim((string)($_POST['notice'] ?? ''));
    save_setting('city_notice', $notice);
    redirect('admin.php?m=citysites', '分站公告已保存' . ($notice === '' ? '（已清空，前台不显示公告）' : ''));
}
if ($m === 'city_brand_save') {
    // ===== 全国分站重构（2025）：分站顶部 banner 品牌/意向词直接保存 =====
    require_admin();
    $brand = trim((string)($_POST['city_banner_brand'] ?? ''));
    if (mb_strlen($brand) > 30) {
        $brand = mb_substr($brand, 0, 30);
    }
    save_setting('city_banner_brand', $brand);
    // 同时把这值也写入 settings.site_name 兜底（让 AI title_suffix 末尾品牌词也同步；如果主站站名不被外部使用，无副作用）
    if ($brand !== '') {
        save_setting('site_name', $brand);
    }
    redirect('admin.php?m=citysites', '分站顶部品牌横幅已保存 · 前台立即生效');
}

/* ---------- 自定义表单 ---------- */
if (in_array($m, ['forms', 'form_save', 'form_del', 'form_data', 'form_data_export', 'form_data_del'], true)) {
    // 旧库兼容：自动建表
    DB::run("CREATE TABLE IF NOT EXISTS form_defs (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        title VARCHAR(200) DEFAULT '',
        remark TEXT,
        fields LONGTEXT,
        submit_text VARCHAR(50) DEFAULT '提交',
        status TINYINT NOT NULL DEFAULT 1,
        show_nav TINYINT NOT NULL DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    // 旧库兼容：无 show_nav 列时自动补充
    try {
        DB::one('SELECT show_nav FROM form_defs LIMIT 1');
    } catch (Throwable $e) {
        DB::run('ALTER TABLE form_defs ADD COLUMN show_nav TINYINT NOT NULL DEFAULT 0 COMMENT "前端导航显示" AFTER status');
    }
    DB::run("CREATE TABLE IF NOT EXISTS form_data (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        form_id INT UNSIGNED NOT NULL DEFAULT 0,
        data LONGTEXT,
        ip VARCHAR(50) DEFAULT '',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        KEY idx_form (form_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}
if ($m === 'forms') {
    $list = DB::all("SELECT f.*, (SELECT COUNT(*) FROM form_data d WHERE d.form_id=f.id AND d.site_id=f.site_id) AS cnt FROM form_defs f WHERE f.site_id=? ORDER BY f.id DESC", [$sid]);
    foreach ($list as &$f) {
        $f['field_n'] = count(form_fields($f));
    }
    unset($f);
    render('forms.php', ['list' => $list]);
    exit;
}
if ($m === 'form_save') {
    $id   = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    if ($name === '') {
        redirect('admin.php?m=forms', '表单名称不能为空');
    }
    $fieldsRaw = trim($_POST['fields_json'] ?? '');
    if ($fieldsRaw === '') {
        redirect('admin.php?m=forms', '请至少添加一个字段');
    }
    $fields = json_decode($fieldsRaw, true);
    if (!is_array($fields) || !count($fields)) {
        redirect('admin.php?m=forms', '字段格式错误');
    }
    $clean = [];
    foreach ($fields as $f) {
        $label = trim($f['label'] ?? '');
        $nameF = trim($f['name'] ?? '');
        if ($label === '') {
            continue;
        }
        if ($nameF === '') {
            $nameF = 'f_' . (count($clean) + 1);
        }
        $nameF = preg_replace('/[^a-zA-Z0-9_]/', '_', $nameF);
        if ($nameF === '') {
            $nameF = 'f_' . (count($clean) + 1);
        }
        $clean[] = [
            'label'       => $label,
            'name'        => $nameF,
            'type'        => in_array($f['type'] ?? '', ['text','textarea','tel','email','number','date','select','radio','checkbox'], true) ? $f['type'] : 'text',
            'required'    => !empty($f['required']) ? 1 : 0,
            'placeholder' => trim($f['placeholder'] ?? ''),
            'options'     => is_array($f['options'] ?? null) ? array_values(array_filter(array_map('trim', $f['options']))) : [],
        ];
    }
    if (!count($clean)) {
        redirect('admin.php?m=forms', '字段内容不完整');
    }
    $fieldsJson = json_encode($clean, JSON_UNESCAPED_UNICODE);
    $data = [$name, trim($_POST['title'] ?? ''), trim($_POST['remark'] ?? ''), $fieldsJson,
             trim($_POST['submit_text'] ?? '') ?: '提交', (int)($_POST['status'] ?? 1), (int)($_POST['show_nav'] ?? 0)];
    if ($id > 0) {
        DB::run('UPDATE form_defs SET name=?,title=?,remark=?,fields=?,submit_text=?,status=?,show_nav=? WHERE id=? AND site_id=?', [...$data, $id, $sid]);
    } else {
        DB::insert('INSERT INTO form_defs(site_id,name,title,remark,fields,submit_text,status,show_nav) VALUES(?,?,?,?,?,?,?,?)', [$sid, ...$data]);
    }
    redirect('admin.php?m=forms', '表单已保存');
}
if ($m === 'form_del') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        DB::run('DELETE FROM form_data WHERE form_id=? AND site_id=?', [$id, $sid]);
        DB::run('DELETE FROM form_defs WHERE id=? AND site_id=?', [$id, $sid]);
    }
    redirect('admin.php?m=forms', '表单及其数据已删除');
}
if ($m === 'form_data') {
    $fid = (int)($_GET['fid'] ?? 0);
    $def = DB::one('SELECT * FROM form_defs WHERE id=? AND site_id=?', [$fid, $sid]);
    if (!$def) {
        redirect('admin.php?m=forms', '表单不存在');
    }
    $pg = paginate('SELECT COUNT(*) AS n FROM form_data WHERE form_id=? AND site_id=?', 'SELECT * FROM form_data WHERE form_id=? AND site_id=? ORDER BY id DESC', [$fid, $sid], 20);
    $rows = [];
    foreach ($pg['list'] as $d) {
        $rows[] = ['id' => $d['id'], 'data' => json_decode((string)$d['data'], true) ?: [], 'ip' => $d['ip'], 'created_at' => $d['created_at']];
    }
    render('form_data.php', ['def' => $def, 'fields' => form_fields($def), 'rows' => $rows, 'pg' => $pg]);
    exit;
}
if ($m === 'form_data_export') {
    $fid = (int)($_GET['fid'] ?? 0);
    $def = DB::one('SELECT * FROM form_defs WHERE id=? AND site_id=?', [$fid, $sid]);
    if (!$def) {
        exit('表单不存在');
    }
    $fields = form_fields($def);
    $all = DB::all('SELECT * FROM form_data WHERE form_id=? AND site_id=? ORDER BY id ASC', [$fid, $sid]);
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="form_' . $def['name'] . '_' . date('Ymd_His') . '.csv"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF");
    $head = ['ID', '提交时间', 'IP'];
    foreach ($fields as $f) {
        $head[] = $f['label'];
    }
    fputcsv($out, $head);
    foreach ($all as $d) {
        $row = [$d['id'], $d['created_at'], $d['ip']];
        $data = json_decode((string)$d['data'], true) ?: [];
        foreach ($fields as $f) {
            $v = $data[$f['name']] ?? '';
            if (is_array($v)) {
                $v = implode('、', $v);
            }
            $row[] = (string)$v;
        }
        fputcsv($out, $row);
    }
    fclose($out);
    exit;
}
if ($m === 'form_data_del') {
    DB::run('DELETE FROM form_data WHERE id=? AND site_id=?', [(int)($_POST['id'] ?? 0), $sid]);
    redirect('admin.php?m=form_data&fid=' . (int)($_POST['fid'] ?? 0), '记录已删除');
}

/* ---------- 模板编辑 ---------- */
if ($m === 'settings') {
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    render('tpl_edit.php', ['data' => settings_all()]);
    exit;
}
if ($m === 'settings_save') {
    try {
        $keys = [
            // 全局
            'site_name','site_title','phone','email','address','footer_text','techsupport_text','techsupport_url',
            'seo_keywords','seo_description',
            // 联系我们
            'contact_phone','contact_phone2','contact_wx_qr','contact_mp_qr',
            // 首页
            'theme','custom_c1','custom_c2','custom_c3',
            'hero_title','hero_sub','about_text',
            'stat1','stat1_label','stat2','stat2_label','stat3','stat3_label','stat4','stat4_label',
            // 语言项
            'lang_home','lang_more','lang_contact','lang_consult','lang_read_more','lang_empty',
            // 其它
            'beian','copyright_year',
            // 安全
            'login_captcha',
        ];
        // 调试模式：打开后能直接看到后台收到了什么、写进了没有
        $debugMode = !empty($_GET['debug']) || !empty($_POST['_debug']);
        $debug = [];
        if ($debugMode) {
            $debug[] = '【调试模式】POST 总字段数：' . count($_POST);
            $debug[] = 'CSRF 提交值：' . ($_POST['csrf'] ?? '⟨缺失⟩');
            $debug[] = 'SESSION CSRF：' . ($_SESSION['csrf'] ?? '⟨未生成⟩');
            $debug[] = '当前站点 ID：' . current_site_id();
        }
        foreach ($keys as $k) {
            if ($k === 'login_captcha') { // 复选框：未勾选时 POST 无该字段，需显式写 0
                $val = isset($_POST[$k]) ? '1' : '0';
                save_setting($k, $val);
                if ($debugMode) {
                    $debug[] = "[{$k}] POST存在=" . (isset($_POST[$k]) ? '是' : '否') . " -> 保存值={$val}";
                }
                continue;
            }
            // 单页一览式：所有字段都在 POST 中。
            // 防御规则：仅当提交值非空时才写入；若提交为空、但库里该字段已有非空值，则跳过，
            // 避免“回显异常时空表单把已保存内容误清空”（这是此前后台保存后变空白的根因）。
            if (array_key_exists($k, $_POST)) {
                $val = trim((string)$_POST[$k]);
                $dbCur = setting($k);
                if ($val === '' && $dbCur !== '' && $dbCur !== null) {
                    if ($debugMode) {
                        $debug[] = "[{$k}] 提交为空且DB已有值(" . strlen((string)$dbCur) . "字符)，跳过（保留原值）";
                    }
                    continue;
                }
                save_setting($k, $val);
                if ($debugMode) {
                    $raw = DB::one('SELECT `value` FROM settings WHERE site_id=? AND `key`=?', [current_site_id(), $k]);
                    $dbVal = $raw['value'] ?? '⟨查无⟩';
                    $debug[] = "[{$k}] POST长度=" . strlen($_POST[$k]) . " -> 保存值={$val} -> DB当前值={$dbVal}";
                }
            } elseif ($debugMode) {
                $debug[] = "[{$k}] POST中不存在，跳过";
            }
        }
        $tab = $_POST['tab'] ?? '';
        $from = $_POST['from'] ?? '';
        if ($debugMode) {
            header('Content-Type: text/plain; charset=utf-8');
            echo implode("\n", $debug);
            echo "\n\n调试完成。若上面某 key 的「DB当前值」不等于「保存值」，说明写入未落库。";
            exit;
        }
        if ($from === 'tpls') {
            $redirect = 'admin.php?m=tpls&tab=theme' . ($tab ? '&sett=' . urlencode($tab) : '');
        } else {
            $redirect = 'admin.php?m=settings' . ($tab ? '&tab=' . urlencode($tab) : '');
        }
        // 保存后立刻失效配置缓存，避免 PHP-FPM 同一 worker 重定向后读到旧空值
        settings_clear_cache($sid);
        redirect($redirect, '配置已保存');
    } catch (Throwable $e) {
        // 保存失败不再静默：把真实错误抛到页面，便于定位（数据库/权限/字段等）
        http_response_code(500);
        header('Content-Type: text/html; charset=utf-8');
        echo '<pre style="padding:24px;margin:0;font:13px/1.7 monospace;color:#b91c1c;background:#fff;white-space:pre-wrap">'
            . "配置保存失败（已捕获异常，便于排查）：\n\n"
            . htmlspecialchars($e->getMessage()) . "\n\n"
            . htmlspecialchars($e->getTraceAsString())
            . "</pre>";
        exit;
    }
}

/* ---------- 首页 DIY 布局保存 ---------- */
if ($m === 'home_layout_save') {
    $allowed = ['hero','scenario','stats','capabilities','about','workflow','cta','contact','ticker','products','news'];
    $order = (array)($_POST['order'] ?? []);
    $show  = (array)($_POST['show'] ?? []);
    $layout = [];
    foreach ($order as $k) {
        if (in_array($k, $allowed, true)) {
            $layout[] = ['key' => $k, 'show' => in_array($k, $show, true) ? 1 : 0];
        }
    }
    if (!$layout) {
        $default = ['hero','scenario','stats','capabilities','about','workflow','cta','contact'];
        $layout = array_map(fn($k) => ['key' => $k, 'show' => 1], $default);
    }
    $json = json_encode($layout, JSON_UNESCAPED_UNICODE);
    DB::run("INSERT INTO settings(site_id,`key`,`value`) VALUES(?,?,?) ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)", [$sid, 'home_layout', $json]);
    settings_clear_cache($sid);
    redirect('admin.php?m=tpls&tab=diy', '首页布局已保存');
}

/* ---------- 可视化首页编辑器：布局 + 模块配置 ---------- */
if ($m === 'visual_home_save') {
    $allowed = ['hero','scenario','stats','capabilities','about','workflow','cta','contact','ticker','products','news','board','collections','story','timeline','quote'];
    // 布局
    $layoutRaw = $_POST['layout'] ?? '[]';
    $decoded = json_decode($layoutRaw, true);
    $layout = [];
    if (is_array($decoded)) {
        foreach ($decoded as $it) {
            $k = $it['key'] ?? '';
            if (in_array($k, $allowed, true)) {
                $layout[] = ['key' => $k, 'show' => !empty($it['show']) ? 1 : 0];
            }
        }
    }
    if (!$layout) {
        $default = ['hero','scenario','stats','capabilities','about','workflow','cta','contact'];
        $layout = array_map(fn($k) => ['key' => $k, 'show' => 1], $default);
    }
    $layoutJson = json_encode($layout, JSON_UNESCAPED_UNICODE);
    DB::run("INSERT INTO settings(site_id,`key`,`value`) VALUES(?,?,?) ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)", [$sid, 'home_layout', $layoutJson]);
    // 模块配置（每个模块的字段值）
    $modulesRaw = $_POST['modules'] ?? '{}';
    $modDecoded = json_decode($modulesRaw, true);
    if (!is_array($modDecoded)) { $modDecoded = []; }
    // 只保留允许的 key，且值必须是字符串/数组
    $clean = [];
    foreach ($modDecoded as $k => $cfg) {
        if (!in_array($k, $allowed, true) || !is_array($cfg)) { continue; }
        $row = [];
        foreach ($cfg as $fk => $fv) {
            if (is_array($fv)) { $row[$fk] = $fv; }            // 保留嵌套数组（images 列表 / items 列表）
            else { $row[$fk] = is_string($fv) ? $fv : (string)$fv; }
        }
        $clean[$k] = $row;
    }
    $modJson = json_encode($clean, JSON_UNESCAPED_UNICODE);
    DB::run("INSERT INTO settings(site_id,`key`,`value`) VALUES(?,?,?) ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)", [$sid, 'home_modules', $modJson]);
    settings_clear_cache($sid);
    redirect('admin.php?m=tpls&tab=diy', '首页布局与模块配置已保存');
}

/* ---------- 模板中心（内置模板 / 历史站点模板） ---------- */
$builtinDir = __DIR__ . '/tpls/builtin';
$siteTplDir = __DIR__ . '/tpls/site_' . $sid;

/** 内置模板中文显示名（仅保留：系统默认 + 家纺家居·暖调） */
$TPL_NAMES = [
    'default'     => '系统默认',
    '_demo_template_home_textile2'   => '家纺家居·暖调',
];

/** 解析模板目录（内置优先，其次站点历史模板，再次演示模板 _demo*） */
function resolve_tpl_dir(string $name): ?string
{
    $safe = preg_replace('/[^a-zA-Z0-9_-]/', '', $name);
    if ($safe === '') {
        return null;
    }
    $builtin = __DIR__ . '/tpls/builtin/' . $safe;
    if (is_dir($builtin)) {
        return $builtin;
    }
    $site = __DIR__ . '/tpls/site_' . current_site_id() . '/' . $safe;
    if (is_dir($site)) {
        return $site;
    }
    // 兼容演示模板目录（_demo_template*，即内置行业模板）
    $demo = __DIR__ . '/tpls/' . $safe;
    if (is_dir($demo) && is_file($demo . '/style.css')) {
        return $demo;
    }
    // 保留别名：旧数据/外部引用若用 home_textile2 仍可解析到暖调模板
    $map = [
        'hometextile2' => '_demo_template_home_textile2',
        'home_textile2' => '_demo_template_home_textile2',
    ];
    if (isset($map[$safe])) {
        $mapped = __DIR__ . '/tpls/' . $map[$safe];
        if (is_dir($mapped)) {
            return $mapped;
        }
    }
    return null;
}

/**
 * 自动应用模板包内的 home.json 到当前站点
 * 若模板目录存在 home.json 且合法，则覆盖 home_layout / home_modules
 */
function apply_tpl_home_json(string $tplName, int $siteId): bool
{
    $dir = resolve_tpl_dir($tplName);
    if (!$dir) {
        return false;
    }
    $jsonFile = $dir . '/home.json';
    if (!is_file($jsonFile)) {
        return false;
    }
    $raw = file_get_contents($jsonFile);
    if ($raw === false) {
        return false;
    }
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        return false;
    }
    $layout = $data['home_layout'] ?? null;
    $modules = $data['home_modules'] ?? null;
    if (!is_array($layout) && !is_array($modules)) {
        return false;
    }
    if (is_array($layout)) {
        DB::run("INSERT INTO settings(site_id,`key`,`value`) VALUES(?,?,?) ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)", [$siteId, 'home_layout', json_encode($layout, JSON_UNESCAPED_UNICODE)]);
    }
    if (is_array($modules)) {
        DB::run("INSERT INTO settings(site_id,`key`,`value`) VALUES(?,?,?) ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)", [$siteId, 'home_modules', json_encode($modules, JSON_UNESCAPED_UNICODE)]);
    }
    settings_clear_cache($siteId);
    return true;
}

if ($m === 'tpls') {
    $tpls = [];
    // 内置模板：仅扫描「系统默认」与「家纺家居·暖调」
    $scanDirs = [];
    if (is_dir($builtinDir)) { $scanDirs[] = $builtinDir; }
    foreach ([__DIR__ . '/tpls/_demo_template_home_textile2'] as $d) {
        if (is_dir($d)) { $scanDirs[] = $d; }
    }
    foreach ($scanDirs as $d) {
        $name = basename($d);
        // 跳过：非模板目录（无 style.css 且无 home.json）
        if (!is_file($d . '/style.css') && !is_file($d . '/home.json')) { continue; }
        $tpls[] = [
            'name'   => $name,
            'type'   => 'builtin',
            'hasCss' => is_file($d . '/style.css'),
            'hasJs'  => is_file($d . '/main.js'),
            'files'  => count(glob($d . '/*')) + count(glob($d . '/images/*')) + count(glob($d . '/img/*')),
            'time'   => '内置',
        ];
    }
    // 站点历史模板（兼容旧数据）
    if (is_dir($siteTplDir)) {
        foreach (glob($siteTplDir . '/*', GLOB_ONLYDIR) as $d) {
            $name = basename($d);
            $tpls[] = [
                'name'   => $name,
                'type'   => 'site',
                'hasCss' => is_file($d . '/style.css'),
                'hasJs'  => is_file($d . '/main.js'),
                'files'  => count(glob($d . '/*')) + count(glob($d . '/images/*')) + count(glob($d . '/img/*')),
                'time'   => date('Y-m-d H:i', filemtime($d)),
            ];
        }
    }
    // 兼容旧库：DIY 链接选择器查询的表可能不存在，捕获异常避免整页 500
    $veLinks = ['articles' => [], 'products' => [], 'forms' => [], 'downloads' => [], 'images' => []];
    try { $veLinks['articles']  = DB::all('SELECT id,title FROM articles WHERE site_id=? AND status=1 ORDER BY id DESC LIMIT 300', [$sid]); } catch (Throwable $e) {}
    try { $veLinks['products']  = DB::all('SELECT id,title FROM products WHERE site_id=? AND status=1 ORDER BY id DESC LIMIT 200', [$sid]); } catch (Throwable $e) {}
    try { $veLinks['forms']     = DB::all('SELECT id,name FROM form_defs WHERE site_id=? AND status=1 ORDER BY id DESC LIMIT 100', [$sid]); } catch (Throwable $e) {}
    try { $veLinks['downloads'] = DB::all('SELECT id,title,file_url FROM downloads WHERE site_id=? AND status=1 ORDER BY id DESC LIMIT 200', [$sid]); } catch (Throwable $e) {}
    try { $veLinks['images']    = DB::all('SELECT path,name FROM uploads WHERE site_id=? ORDER BY id DESC LIMIT 60', [$sid]); } catch (Throwable $e) {}
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    render('tpls.php', [
        'tpls' => $tpls,
        'active' => setting('tpl_active', ''),
        'err' => '',
        'tplNames' => $TPL_NAMES,
        'data' => settings_all(),
        'veLinks' => $veLinks,
    ]);
    exit;
}

/* ---------- 下载 demo 模板（让用户用 AI 改造） ---------- */
if ($m === 'tpl_demo_download') {
    $demo = $_GET['demo'] ?? '';
    // 当前仅开放「家纺家居·暖调」demo 下载
    $demoDir = '_demo_template_home_textile2';
    $src = __DIR__ . '/tpls/' . $demoDir;
    if (!is_dir($src)) {
        http_response_code(404);
        exit('demo 模板目录不存在');
    }
    if (!class_exists('ZipArchive')) {
        http_response_code(500);
        exit('服务器未启用 PHP ZipArchive 扩展，请联系管理员');
    }
    $tmp = tempnam(sys_get_temp_dir(), 'dyddemo_');
    $zip = new ZipArchive();
    $zip->open($tmp, ZipArchive::CREATE | ZipArchive::OVERWRITE);
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($src, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    foreach ($it as $f) {
        $rel = substr($f->getPathname(), strlen($src) + 1);
        $rel = str_replace('\\', '/', $rel);
        if ($f->isDir()) {
            $zip->addEmptyDir($rel);
        } else {
            $zip->addFile($f->getRealPath(), $rel);
        }
    }
    $zip->close();
    $fn = 'deyingding_template_home_textile2.zip';
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $fn . '"');
    header('Content-Length: ' . filesize($tmp));
    header('Cache-Control: no-cache, must-revalidate');
    readfile($tmp);
    @unlink($tmp);
    exit;
}

if ($m === 'tpl_upload') {
    if (!is_admin()) {
        redirect('admin.php?m=tpls', '仅平台管理员可上传模板');
    }
    if (!class_exists('ZipArchive')) {
        redirect('admin.php?m=tpls', '服务器未启用 PHP ZipArchive 扩展');
    }
    if (empty($_FILES['tplzip']['tmp_name'])) {
        redirect('admin.php?m=tpls', '请选择模板压缩包');
    }
    $tmp = $_FILES['tplzip']['tmp_name'];
    $ext = strtolower(pathinfo($_FILES['tplzip']['name'], PATHINFO_EXTENSION));
    if ($ext !== 'zip') {
        redirect('admin.php?m=tpls', '仅支持 zip 压缩包');
    }
    // 模板名：取 zip 文件名（去掉 .zip）
    $tplName = preg_replace('/[^a-zA-Z0-9_-]/', '', pathinfo($_FILES['tplzip']['name'], PATHINFO_FILENAME));
    if ($tplName === '' || strlen($tplName) > 40) {
        redirect('admin.php?m=tpls', '模板名需为英文/数字，且不超过 40 字符');
    }
    if (is_dir($builtinDir . '/' . $tplName)) {
        redirect('admin.php?m=tpls', '该名称与内置模板冲突');
    }
    if (!is_dir($siteTplDir)) {
        @mkdir($siteTplDir, 0755, true);
    }
    $dest = $siteTplDir . '/' . $tplName;
    if (is_dir($dest)) {
        redirect('admin.php?m=tpls', '该模板名已存在，请重命名 zip 后再导入');
    }
    @mkdir($dest, 0755, true);
    $za = new ZipArchive();
    if ($za->open($tmp) !== true) {
        redirect('admin.php?m=tpls', '无法打开 zip 压缩包');
    }
    // 安全校验：禁止越径 / 危险扩展名
    $allowedExts = ['css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'webp', 'svg', 'ico', 'woff', 'woff2', 'ttf', 'otf', 'eot', 'json', 'txt', 'md'];
    for ($i = 0; $i < $za->numFiles; $i++) {
        $n = $za->getNameIndex($i);
        if (strpos($n, '__MACOSX') === 0 || substr($n, -1) === '/') {
            continue;
        }
        if (strpos($n, '..') !== false || strpos($n, '\\') !== false || $n[0] === '/') {
            $za->close();
            redirect('admin.php?m=tpls', '压缩包包含非法路径');
        }
        $ext2 = strtolower(pathinfo($n, PATHINFO_EXTENSION));
        if (!in_array($ext2, $allowedExts, true)) {
            $za->close();
            redirect('admin.php?m=tpls', '压缩包包含不允许的文件类型：' . $ext2);
        }
    }
    // 解压：跳过 __MACOSX；若压缩包内只有一个顶层文件夹则去掉该层
    $hasSingleDir = true;
    $first = null;
    for ($i = 0; $i < $za->numFiles; $i++) {
        $n = $za->getNameIndex($i);
        if (strpos($n, '__MACOSX') === 0 || substr($n, -1) === '/') {
            continue;
        }
        $top = explode('/', $n)[0];
        if ($first === null) { $first = $top; } elseif ($top !== $first) { $hasSingleDir = false; break; }
    }
    $za->close();
    $za = new ZipArchive();
    $za->open($tmp);
    for ($i = 0; $i < $za->numFiles; $i++) {
        $n = $za->getNameIndex($i);
        if (strpos($n, '__MACOSX') === 0) {
            continue;
        }
        $strip = ($hasSingleDir && $first !== null) ? $first . '/' : '';
        $out = $strip === '' ? $n : substr($n, strlen($strip));
        if ($out === '') {
            continue;
        }
        if (strpos($out, '..') !== false || strpos($out, '\\') !== false) {
            continue;
        }
        $target = $dest . '/' . $out;
        if (substr($n, -1) === '/') {
            @mkdir($target, 0755, true);
        } else {
            @mkdir(dirname($target), 0755, true);
            copy('zip://' . $tmp . '#' . $n, $target);
        }
    }
    $za->close();
    // 校验必须有 style.css
    if (!is_file($dest . '/style.css')) {
        if (is_dir($dest)) {
            $remove = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dest, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($remove as $f) {
                $f->isDir() ? @rmdir($f->getPathname()) : @unlink($f->getPathname());
            }
            @rmdir($dest);
        }
        redirect('admin.php?m=tpls', '模板缺少 style.css（压缩包需包含完整的 style.css）');
    }
    redirect('admin.php?m=tpls', '模板导入成功');
}

if ($m === 'tpl_activate') {
    $name = trim($_POST['name'] ?? '');
    if ($name === '') {
        DB::run("UPDATE settings SET `value`='' WHERE `key`='tpl_active' AND site_id=?", [$sid]);
        // 恢复默认模板时，清空当前站点的首页布局/模块数据，避免默认模板渲染到旧行业模板数据导致崩溃
        DB::run("DELETE FROM settings WHERE site_id=? AND `key` IN ('home_layout','home_modules')", [$sid]);
        redirect('admin.php?m=tpls', '已恢复系统默认模板，并清空首页布局缓存');
    }
    if (!preg_match('/^[a-zA-Z0-9_-]+$/', $name) || !resolve_tpl_dir($name)) {
        redirect('admin.php?m=tpls', '模板不存在');
    }
    DB::run("INSERT INTO settings(site_id,`key`,`value`) VALUES(?,?,?) ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)", [$sid, 'tpl_active', $name]);
    $applied = apply_tpl_home_json($name, $sid);
    $displayName = $TPL_NAMES[$name] ?? $name;
    $msg = '已启用模板：' . $displayName;
    if ($applied) {
        $msg .= '，并已自动应用该模板的首页布局';
    }
    redirect('admin.php?m=tpls', $msg);
}

if ($m === 'tpl_del') {
    if (!is_admin()) {
        redirect('admin.php?m=tpls', '仅平台管理员可删除模板');
    }
    $name = trim($_POST['name'] ?? '');
    if (!preg_match('/^[a-zA-Z0-9_-]+$/', $name)) {
        redirect('admin.php?m=tpls', '模板名不合法');
    }
    if (is_dir($builtinDir . '/' . $name)) {
        redirect('admin.php?m=tpls', '内置模板不可删除');
    }
    if (setting('tpl_active', '') === $name) {
        DB::run("UPDATE settings SET `value`='' WHERE `key`='tpl_active' AND site_id=?", [$sid]);
        // 删除当前启用模板时，也清空首页布局/模块数据，避免默认模板渲染异常
        DB::run("DELETE FROM settings WHERE site_id=? AND `key` IN ('home_layout','home_modules')", [$sid]);
    }
    $d = $siteTplDir . '/' . $name;
    if (is_dir($d)) {
        $remove = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($d, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($remove as $f) {
            $f->isDir() ? @rmdir($f->getPathname()) : @unlink($f->getPathname());
        }
        @rmdir($d);
    }
    redirect('admin.php?m=tpls', '模板已删除');
}

/* ---------- 渲染 ---------- */
function render(string $tpl, array $data): void
{
    $data['flash'] = flash();
    $data['admin_name'] = $_SESSION['admin_name'] ?? '';
    $data['is_admin'] = is_admin();
    $data['csrf_token'] = csrf_token();
    // 某些视图会重新赋值 $data（如主题设置面板），所以先捕获 token，避免视图执行后读不到
    $csrfToken = $data['csrf_token'];
    extract($data, EXTR_SKIP);
    ob_start();
    require __DIR__ . '/views/' . $tpl;
    $html = ob_get_clean();
    // 注入 CSRF Token 元信息 + 全局 fetch 包装（自动为 FormData POST 附加 token）
    $token = e($csrfToken);
    $inject = '<meta name="csrf-token" content="' . $token . '">' . "\n"
        . '<script>(function(){'
        . 'window.__CSRF__=function(){var m=document.querySelector(\'meta[name="csrf-token"]\');return m?m.getAttribute("content"):"";};'
        . 'var _f=window.fetch;'
        . 'window.fetch=function(u,o){o=o||{};if((o.method||"").toUpperCase()==="POST"){var b=o.body,t=window.__CSRF__();'
        . 'if(t&&b instanceof FormData){b.append("csrf",t);}'
        . 'else if(t&&typeof b==="string"&&b.indexOf("csrf=")===-1){o.body=b+(b?"&":"")+"csrf="+encodeURIComponent(t);}}'
        . 'return _f.apply(this,arguments);};'
        . '})();</script>';
    if (stripos($html, '</head>') !== false) {
        $html = str_ireplace('</head>', $inject . "\n</head>", $html);
    } elseif (preg_match('/<body[^>]*>/i', $html)) {
        $html = preg_replace('/<body[^>]*>/i', '$0' . $inject, $html, 1);
    } else {
        $html = $inject . $html;
    }
    echo $html;
}
