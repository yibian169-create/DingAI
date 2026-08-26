<?php
/**
 * deyingding-php 配图队列处理脚本（CLI 计划任务）
 * ----------------------------------------------------------
 * 作用：后台异步处理 ai_img_queue 队列里的 AI 配图任务。
 *      生图单次要 30~120 秒，若在网页请求里同步执行会占住
 *      PHP-FPM worker 导致后台"卡死"，故改为队列 + 定时处理。
 *
 * 宝塔配置（二选一）：
 *   ① 计划任务 → Shell 脚本，每分钟执行（一次处理 1 篇）：
 *        php /www/wwwroot/ding/cron.php
 *   ② 一次处理多篇（最多 20）：
 *        php /www/wwwroot/ding/cron.php 5
 *
 * 兜底：即使没配计划任务，前台任意访问（index.php）也会触发处理 1 篇。
 */
define('CRON_CLI', true);
require __DIR__ . '/config.php';
require __DIR__ . '/lib/db.php';
require __DIR__ . '/lib/funcs.php';

$max = (int)($argv[1] ?? 1);
$max = max(1, min(20, $max));
$processed = 0;
for ($i = 0; $i < $max; $i++) {
    if (!ai_img_queue_pick_one()) {
        break;
    }
    $processed++;
}
echo 'ai_img_queue: processed ' . $processed . " task(s)\n";
