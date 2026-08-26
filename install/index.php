<?php
/**
 * deyingding-php 网页安装向导
 * 访问站点根目录自动跳转到这里；已安装则提示
 */
error_reporting(E_ALL & ~E_DEPRECATED);
date_default_timezone_set('PRC');

define('ROOT', dirname(__DIR__));
$LOCK = ROOT . '/install.lock';
$isInstalled = file_exists($LOCK);

/* 已安装则提示并退出 */
if ($isInstalled && ($_GET['do'] ?? '') !== 'recheck') {
    http_response_code(403);
    $installDirStillThere = is_dir(ROOT . '/install');
    $warnHtml = $installDirStillThere
        ? '<div class="warn" style="text-align:left;max-width:380px;margin:14px auto 0">⚠️ <b>安全警告：</b>服务器上 install 安装目录仍然存在！任何人都能访问它重新安装或探测系统。<br>请立即在宝塔 / 服务器上删除网站的 <b>install/</b> 目录（删除 install.lock 才能重新安装）。</div>'
        : '';
    echo '<!DOCTYPE html><html lang="zh-CN"><head><meta charset="UTF-8"><title>已安装</title></head>
      <body style="font-family:system-ui;display:flex;align-items:center;justify-content:center;min-height:100vh;background:#0b1120;color:#e8eef8;margin:0">
      <div style="text-align:center;padding:40px;border:1px solid rgba(255,255,255,.12);border-radius:16px;background:rgba(255,255,255,.03);max-width:480px">
        <div style="font-size:40px">✅</div>
        <h2 style="margin:10px 0">系统已安装完成</h2>
        <p style="color:#93a0b8;font-size:14px">无需重复安装。<a href="../admin.php" style="color:#22d3ee">进入后台 →</a></p>
        <p style="color:#5e6b85;font-size:12px;margin-top:8px">如需重装：删除服务器上 install.lock 文件后刷新本页</p>'
        . $warnHtml . '
      </div></body></html>';
    exit;
}

/* 环境检测 */
$env = [
    'PHP 版本 ≥ 8.0' => version_compare(PHP_VERSION, '8.0.0', '>='),
    'PDO 扩展'        => extension_loaded('pdo'),
    'PDO MySQL 驱动'  => extension_loaded('pdo_mysql'),
    'uploads 目录可写' => is_dir(ROOT . '/uploads') ? is_writable(ROOT . '/uploads') : is_writable(ROOT),
    'database.sql 存在' => file_exists(ROOT . '/database.sql'),
];
$envOk = !in_array(false, $env, true);

/* 处理安装提交 */
$result = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['do'] ?? '') === 'install') {
    $result = run_install($_POST, $envOk, $LOCK);
}

function run_install(array $p, bool $envOk, string $lock): array
{
    if (!$envOk) {
        return ['ok' => false, 'msg' => '环境检测未通过，请先解决上方 ✗ 项'];
    }
    $dbHost = trim($p['db_host'] ?? '127.0.0.1');
    $dbPort = trim($p['db_port'] ?? '3306');
    $dbName = trim($p['db_name'] ?? '');
    $dbUser = trim($p['db_user'] ?? '');
    $dbPass = $p['db_pass'] ?? '';
    $adminU = trim($p['admin_user'] ?? 'admin');
    $adminP = $p['admin_pass'] ?? '';
    $adminP2 = $p['admin_pass2'] ?? '';

    if ($dbName === '' || $dbUser === '') {
        return ['ok' => false, 'msg' => '数据库名和用户名不能为空'];
    }
    if (mb_strlen($adminP) < 6) {
        return ['ok' => false, 'msg' => '管理员密码至少 6 位'];
    }
    if ($adminP !== $adminP2) {
        return ['ok' => false, 'msg' => '两次输入的密码不一致'];
    }

    // 1. 测试连接（无库名连接，允许建库）
    $dsnBase = sprintf('mysql:host=%s;port=%s;charset=utf8mb4', $dbHost, $dbPort);
    try {
        $pdo = new PDO($dsnBase, $dbUser, $dbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5,
        ]);
    } catch (Throwable $e) {
        return ['ok' => false, 'msg' => '数据库连接失败：' . $e->getMessage() . '（请检查地址/端口/账号/密码）'];
    }

    // 2. 自动创建数据库（如不存在）
    try {
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . str_replace('`', '', $dbName) . "` DEFAULT CHARSET utf8mb4 COLLATE utf8mb4_general_ci");
    } catch (Throwable $e) {
        return ['ok' => false, 'msg' => '自动建库失败：' . $e->getMessage() . '（可先在 phpMyAdmin 手动建库）'];
    }

    // 3. 重新连接目标库并执行 database.sql
    try {
        $pdo->exec("USE `" . str_replace('`', '', $dbName) . "`");
        $sql = file_get_contents(ROOT . '/database.sql');
        $sql = preg_replace('/^\s*--.*$/m', '', $sql);
        $stmts = array_filter(array_map('trim', explode(';', $sql)), fn($s) => $s !== '');
        $run = 0;
        foreach ($stmts as $s) {
            try {
                $pdo->exec($s);
                $run++;
            } catch (PDOException $e) {
                // 表已存在等错误忽略，继续
            }
        }
        if ($run < 5) {
            return ['ok' => false, 'msg' => '建表异常（执行成功 ' . $run . ' 条），请检查数据库权限'];
        }
    } catch (Throwable $e) {
        return ['ok' => false, 'msg' => '建表失败：' . $e->getMessage()];
    }

    // 3.5 确保 admin_users 表结构正确（旧库升级：password 列必须为 VARCHAR(255)，
    //     旧版本若为 VARCHAR(32) 会把 bcrypt 哈希截断，导致登录永远失败）
    try {
        $pdo->exec('CREATE TABLE IF NOT EXISTS admin_users (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
    } catch (Throwable $e) {
    }
    try {
        $pdo->exec('ALTER TABLE admin_users MODIFY COLUMN password VARCHAR(255) NOT NULL');
    } catch (Throwable $e) {
    }

    // 4. 创建 / 更新管理员（始终以本次填写的账号密码为准：
    //    复用旧库时若 admin_users 已有数据，原逻辑会跳过创建，导致用户填的账号密码不生效）
    try {
        $st = $pdo->prepare('INSERT INTO admin_users(username,password) VALUES(?,?) ON DUPLICATE KEY UPDATE password=VALUES(password)');
        $st->execute([$adminU, password_hash($adminP, PASSWORD_DEFAULT)]);
    } catch (Throwable $e) {
        return ['ok' => false, 'msg' => '创建管理员失败：' . $e->getMessage()];
    }

    // 4.5 安装自检：用刚填的密码验证数据库里写入的 hash，
    //     防止"安装成功但登录不上"（密码未写入 / 写入被截断 / 编码问题都会在这里直接报错）
    try {
        $chk = $pdo->query('SELECT password FROM admin_users WHERE username=' . $pdo->quote($adminU))->fetchColumn();
        if ($chk === false || !is_string($chk) || !password_verify($adminP, (string)$chk)) {
            return ['ok' => false, 'msg' => '管理员密码写入自检失败：数据库中保存的密码无法用你填的密码解开。请重试安装，并注意密码不要含特殊字符、不要全角输入'];
        }
    } catch (Throwable $e) {
        return ['ok' => false, 'msg' => '管理员密码自检异常：' . $e->getMessage()];
    }

    // 5. 写入 config.php
    $cfg = "<?php\n"
        . "/**\n * deyingding-php 全局配置（由安装向导生成，请勿手动修改）\n */\n"
        . "define('DB_HOST', '" . addslashes($dbHost) . "');\n"
        . "define('DB_PORT', '" . addslashes($dbPort) . "');\n"
        . "define('DB_NAME', '" . addslashes($dbName) . "');\n"
        . "define('DB_USER', '" . addslashes($dbUser) . "');\n"
        . "define('DB_PASS', '" . addslashes($dbPass) . "');\n\n"
        . "define('APP_NAME', 'deyingding');\n"
        . "define('UPLOAD_DIR', __DIR__ . '/uploads/');\n"
        . "define('UPLOAD_URL', 'uploads/');\n\n"
        . "if (session_status() === PHP_SESSION_NONE) {\n"
        . "    session_set_cookie_params(['samesite' => 'Lax', 'httponly' => true, 'path' => '/']);\n"
        . "    session_start();\n"
        . "}\n"
        . "define('APP_SECRET', '" . bin2hex(random_bytes(16)) . "');\n";
    if (file_put_contents(ROOT . '/config.php', $cfg) === false) {
        return ['ok' => false, 'msg' => '写入 config.php 失败，请检查目录写权限'];
    }

    // 6. 写安装锁
    file_put_contents($lock, date('Y-m-d H:i:s'));

    return [
        'ok' => true,
        'msg' => '安装成功',
        'admin' => $adminU,
        'dbname' => $dbName,
    ];
}

/* ---------- 页面 ---------- */
function esc($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
$vals = [
    'db_host' => $_POST['db_host'] ?? '127.0.0.1',
    'db_port' => $_POST['db_port'] ?? '3306',
    'db_name' => $_POST['db_name'] ?? 'deyingding',
    'db_user' => $_POST['db_user'] ?? 'root',
    'db_pass' => $_POST['db_pass'] ?? '',
    'admin_user' => $_POST['admin_user'] ?? 'admin',
];
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>安装向导 - 得应盯建站系统</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI","PingFang SC","Microsoft YaHei",sans-serif;background:#0b1120;color:#e8eef8;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px}
.wrap{width:min(680px,100%)}
.head{text-align:center;margin-bottom:22px}
.head .logo{width:52px;height:52px;border-radius:14px;background:linear-gradient(115deg,#22d3ee,#818cf8,#e879f9);display:flex;align-items:center;justify-content:center;font-size:24px;font-weight:900;color:#07101f;margin:0 auto 12px}
.head h1{font-size:22px;font-weight:800}
.head p{color:#93a0b8;font-size:13px;margin-top:6px}
.steps{display:flex;gap:6px;margin-bottom:18px}
.steps div{flex:1;text-align:center;padding:9px 4px;border-radius:10px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);font-size:12.5px;color:#93a0b8}
.steps .on{background:rgba(129,140,248,.16);border-color:#818cf8;color:#c7d2fe}
.box{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.1);border-radius:16px;padding:24px;margin-bottom:14px}
.box h2{font-size:15px;margin-bottom:14px;color:#c7d2fe;font-weight:700}
.env li{list-style:none;display:flex;justify-content:space-between;padding:8px 2px;border-bottom:1px dashed rgba(255,255,255,.08);font-size:13.5px;color:#93a0b8}
.env li:last-child{border-bottom:none}
.env .ok{color:#34d399}.env .bad{color:#f87171}
label{display:block;font-size:12.5px;color:#93a0b8;margin-bottom:5px}
.fg{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.fg .full{grid-column:1/-1}
input{width:100%;padding:11px 13px;border-radius:10px;border:1px solid rgba(255,255,255,.14);background:rgba(255,255,255,.05);color:#e8eef8;font-size:14px;outline:none;font-family:inherit}
input:focus{border-color:#818cf8;box-shadow:0 0 0 3px rgba(129,140,248,.15)}
.tip{color:#5e6b85;font-size:12px;margin-top:5px}
.btn{display:block;width:100%;padding:13px;border:none;border-radius:10px;font-size:15px;font-weight:700;cursor:pointer;font-family:inherit;background:linear-gradient(115deg,#22d3ee,#818cf8,#e879f9);color:#07101f}
.btn:disabled{opacity:.4;cursor:not-allowed}
.msg{padding:12px 14px;border-radius:10px;font-size:13.5px;margin-bottom:14px}
.msg.err{background:rgba(248,113,113,.12);border:1px solid rgba(248,113,113,.4);color:#fca5a5}
.msg.ok{background:rgba(52,211,153,.12);border:1px solid rgba(52,211,153,.4);color:#6ee7b7}
.warn{background:rgba(248,113,113,.14);border:1px solid rgba(248,113,113,.5);color:#fca5a5;border-radius:10px;padding:12px 14px;font-size:13.5px;margin-top:14px;line-height:1.7;text-align:left}
.warn b{color:#fecaca}
.done{text-align:center;padding:16px 0}
.done .big{font-size:44px;margin-bottom:10px}
.done .btns{display:flex;gap:12px;justify-content:center;margin-top:18px;flex-wrap:wrap}
.done a{display:inline-block;padding:12px 26px;border-radius:10px;font-size:14px;font-weight:600;text-decoration:none}
.done a.p{background:linear-gradient(115deg,#22d3ee,#818cf8,#e879f9);color:#07101f}
.done a.g{background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.16);color:#e8eef8}
.foot{text-align:center;color:#5e6b85;font-size:12px;margin-top:14px}
</style>
</head>
<body>
<div class="wrap">
  <div class="head">
    <div class="logo">D</div>
    <h1>得应盯建站系统 · 安装向导</h1>
    <p>上传即安装：填数据库 → 自动建表 → 自动写配置，全程无需改代码</p>
  </div>

  <?php if ($result && !empty($result['ok'])): $r = $result; ?>
    <div class="box done">
      <div class="big">🎉</div>
      <h2 style="margin-bottom:6px"><?= esc($r['msg']) ?>！</h2>
      <p style="color:#93a0b8;font-size:13.5px">
        数据库 <b style="color:#c7d2fe"><?= esc($r['dbname']) ?></b> 已就绪<br>
        管理员账号：<b style="color:#22d3ee"><?= esc($r['admin']) ?></b>（密码 = 安装时填写的密码，已通过自检）
      </p>
      <p class="tip" style="margin-top:6px">登录失败时请先检查：密码大小写、输入法是否全角、是否多了空格</p>
      <div class="btns">
        <a class="p" href="../admin.php">进入管理后台 →</a>
        <a class="g" href="../index.php">查看网站前台 →</a>
      </div>
      <div class="warn" style="margin-top:16px">
        🔒 <b>安全必做：安装完成后，请立即删除服务器上的 <code>install/</code> 目录！</b><br>
        否则任何人都能访问 <code>/install/</code> 重新安装或探测数据库信息，造成站点被覆盖 / 数据泄露。<br>
        （删除 install.lock 文件即可在需要重装时重新启用安装向导。）
      </div>
    </div>
  <?php else: ?>
    <?php if ($result): ?>
      <div class="msg err">✗ <?= esc($result['msg']) ?></div>
    <?php endif; ?>

    <div class="steps">
      <div class="on">1 · 环境检测</div><div>2 · 数据库</div><div>3 · 管理员</div><div>4 · 完成</div>
    </div>

    <div class="box">
      <h2>环境检测</h2>
      <ul class="env">
        <?php foreach ($env as $k => $v): ?>
          <li><span><?= esc($k) ?></span><span class="<?= $v ? 'ok' : 'bad' ?>"><?= $v ? '✓ 正常' : '✗ 异常' ?></span></li>
        <?php endforeach; ?>
      </ul>
    </div>

    <form method="post" action="index.php">
      <input type="hidden" name="do" value="install">
      <div class="box">
        <h2>数据库配置</h2>
        <div class="fg">
          <div><label>数据库地址</label><input type="text" name="db_host" value="<?= esc($vals['db_host']) ?>" required></div>
          <div><label>端口</label><input type="text" name="db_port" value="<?= esc($vals['db_port']) ?>"></div>
          <div><label>数据库名 *</label><input type="text" name="db_name" value="<?= esc($vals['db_name']) ?>" required></div>
          <div><label>用户名 *</label><input type="text" name="db_user" value="<?= esc($vals['db_user']) ?>" required></div>
          <div class="full"><label>密码</label><input type="password" name="db_pass" value="<?= esc($vals['db_pass']) ?>"><p class="tip">数据库不存在会自动创建；连接失败会明确提示</p></div>
        </div>
      </div>

      <div class="box">
        <h2>管理员账号</h2>
        <div class="fg">
          <div><label>用户名</label><input type="text" name="admin_user" value="<?= esc($vals['admin_user']) ?>" required></div>
          <div><label>密码（≥6位）</label><input type="password" name="admin_pass" required></div>
          <div class="full"><label>确认密码</label><input type="password" name="admin_pass2" required></div>
        </div>
      </div>

      <button class="btn" type="submit" <?= $envOk ? '' : 'disabled' ?>>开始安装</button>
    </form>
  <?php endif; ?>

  <div class="foot">deyingding-php · PHP 8 建站系统</div>
</div>
</body>
</html>
