<?php
/**
 * deyingding-php 配置示例（非运行时文件）
 * ------------------------------------------------------------------
 * 生产环境请使用「安装向导」自动生成真正的 config.php，
 * 本文件仅展示配置结构，请勿直接改名为 config.php 使用
 * （安装向导会在写入时一并生成随机 APP_SECRET 主密钥）。
 *
 * 注意：config.php 已在 .gitignore 中忽略，切勿提交真实数据库凭证。
 */

define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'deyingding');
define('DB_USER', 'root');
define('DB_PASS', '');

define('APP_NAME', 'deyingding');
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('UPLOAD_URL', 'uploads/');

/*
 * APP_SECRET 由安装向导随机生成（bin2hex(random_bytes(16))），
 * 用于 AES-256-CBC 加密 AI 密钥等敏感设置。请勿写死固定值。
 * define('APP_SECRET', '在这里放随机十六进制串');
 */
