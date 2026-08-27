-- =====================================================
-- deyingding-php 数据库建表脚本（PHP 8 + PDO）
-- 用法：
--   CREATE DATABASE deyingding DEFAULT CHARSET utf8mb4 COLLATE utf8mb4_general_ci;
--   USE deyingding;
--   source database.sql;
-- 本文件为完整 schema（含运行期 ensure_schema 补建的全部表与字段），
-- 安装向导导入后即得到可用数据库，无需依赖运行期自愈。
-- 注意：所有注释均独占一行（行首 --），避免使用行内 -- 注释，
--       否则会被安装向导的按分号切分误截断。
-- =====================================================

SET NAMES utf8mb4;

-- SaaS 站点用户（1 用户 = 1 站点，site_id 即 user_id）
CREATE TABLE IF NOT EXISTS users (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username   VARCHAR(50)  NOT NULL UNIQUE COMMENT '登录名（也是站点标识）',
    password   VARCHAR(255) NOT NULL COMMENT 'password_hash 加密',
    email      VARCHAR(100) DEFAULT '',
    phone      VARCHAR(30)  DEFAULT '',
    site_name  VARCHAR(100) DEFAULT '' COMMENT '网站名称',
    status     TINYINT NOT NULL DEFAULT 0 COMMENT '0待开通 1已开通 2停用',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='SaaS站点用户';

-- 管理员
CREATE TABLE IF NOT EXISTS admin_users (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username   VARCHAR(50)  NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL COMMENT 'password_hash 加密',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='后台管理员';

-- 栏目（树形，支持子栏目）
CREATE TABLE IF NOT EXISTS categories (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    site_id    INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '所属站点',
    pid        INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '父栏目ID，0=顶级',
    name       VARCHAR(100) NOT NULL,
    type       VARCHAR(20)  NOT NULL DEFAULT 'article' COMMENT 'article文章 product产品 single单页 download下载',
    sort       INT NOT NULL DEFAULT 0,
    status     TINYINT NOT NULL DEFAULT 1 COMMENT '1启用 0停用',
    seo_title  VARCHAR(255) DEFAULT '',
    seo_keywords VARCHAR(255) DEFAULT '',
    seo_description VARCHAR(500) DEFAULT '',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY idx_pid (pid), KEY idx_site (site_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='栏目';

-- 文章
CREATE TABLE IF NOT EXISTS articles (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    site_id    INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '所属站点',
    cat_id     INT UNSIGNED NOT NULL DEFAULT 0,
    title      VARCHAR(200) NOT NULL,
    summary    VARCHAR(500) DEFAULT '',
    cover      VARCHAR(500) DEFAULT '',
    content    LONGTEXT,
    tags       VARCHAR(200) DEFAULT '',
    views      INT UNSIGNED NOT NULL DEFAULT 0,
    recommend  TINYINT NOT NULL DEFAULT 0 COMMENT '1推荐',
    status     TINYINT NOT NULL DEFAULT 1 COMMENT '1发布 0下架',
    seo_title  VARCHAR(255) DEFAULT '',
    seo_keywords VARCHAR(255) DEFAULT '',
    seo_description VARCHAR(500) DEFAULT '',
    geo_summary TEXT,
    geo_faq    MEDIUMTEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_cat (cat_id), KEY idx_recommend (recommend), KEY idx_site (site_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='文章';

-- 产品
CREATE TABLE IF NOT EXISTS products (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    site_id    INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '所属站点',
    cat_id     INT UNSIGNED NOT NULL DEFAULT 0,
    title      VARCHAR(200) NOT NULL,
    summary    VARCHAR(500) DEFAULT '',
    cover      VARCHAR(500) DEFAULT '',
    price      VARCHAR(50)  DEFAULT '' COMMENT '价格/面议',
    content    LONGTEXT,
    views      INT UNSIGNED NOT NULL DEFAULT 0,
    recommend  TINYINT NOT NULL DEFAULT 0 COMMENT '1推荐',
    status     TINYINT NOT NULL DEFAULT 1,
    seo_title  VARCHAR(255) DEFAULT '',
    seo_keywords VARCHAR(255) DEFAULT '',
    seo_description VARCHAR(500) DEFAULT '',
    geo_summary TEXT,
    geo_faq    MEDIUMTEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_cat (cat_id), KEY idx_recommend (recommend), KEY idx_site (site_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='产品';

-- 图片空间
CREATE TABLE IF NOT EXISTS folders (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    site_id    INT UNSIGNED NOT NULL DEFAULT 0,
    name       VARCHAR(100) NOT NULL COMMENT '文件夹名',
    sort       INT NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='图片文件夹';

CREATE TABLE IF NOT EXISTS uploads (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    site_id    INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '所属站点',
    folder_id  INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '所属文件夹，0=未分类',
    name       VARCHAR(200) NOT NULL COMMENT '原文件名',
    path       VARCHAR(500) NOT NULL COMMENT '相对路径',
    size       INT UNSIGNED NOT NULL DEFAULT 0,
    ext        VARCHAR(10)  DEFAULT '',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY idx_folder (folder_id), KEY idx_site (site_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='图片空间';

-- 全国分站
CREATE TABLE IF NOT EXISTS city_sites (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    site_id    INT UNSIGNED NOT NULL DEFAULT 0,
    city       VARCHAR(50) NOT NULL COMMENT '城市名',
    pinyin     VARCHAR(50) NOT NULL DEFAULT '' COMMENT '城市拼音（URL 后缀）',
    province   VARCHAR(20) NOT NULL DEFAULT '' COMMENT '所属省份（34 省级行政区）',
    content    MEDIUMTEXT COMMENT '分站专属正文（AI 生成，写回分站表）',
    content_title VARCHAR(200) NOT NULL DEFAULT '' COMMENT '分站内容标题',
    content_at DATETIME DEFAULT NULL COMMENT '内容生成时间',
    content_status TINYINT NOT NULL DEFAULT 0 COMMENT '内容状态 0未/1已/2中/3失败',
    content_err VARCHAR(255) NOT NULL DEFAULT '' COMMENT '生成失败原因',
    article_id INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '关联文章 ID（可选同步发布）',
    title_suffix VARCHAR(50) DEFAULT '' COMMENT '标题后缀，如 - 保定分站',
    keywords   VARCHAR(255) DEFAULT '',
    description VARCHAR(500) DEFAULT '',
    status     TINYINT NOT NULL DEFAULT 1 COMMENT '1启用 0停用',
    sort       INT NOT NULL DEFAULT 0,
    tdk_try_at DATETIME DEFAULT NULL COMMENT '上次 AI SEO 尝试时间（含失败）',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY idx_pinyin (pinyin), KEY idx_site (site_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='全国分站';

-- 自定义表单（定义 + 提交数据）
CREATE TABLE IF NOT EXISTS form_defs (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    site_id     INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '所属站点',
    name        VARCHAR(100) NOT NULL COMMENT '表单名称',
    title       VARCHAR(200) DEFAULT '' COMMENT '展示标题',
    remark      TEXT COMMENT '表单说明',
    fields      LONGTEXT COMMENT '字段定义 JSON',
    submit_text VARCHAR(50) DEFAULT '提交',
    status      TINYINT NOT NULL DEFAULT 1 COMMENT '1启用 0停用',
    show_nav    TINYINT NOT NULL DEFAULT 0 COMMENT '1前端导航显示 0不显示',
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY idx_site (site_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='自定义表单定义';

CREATE TABLE IF NOT EXISTS form_data (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    site_id    INT UNSIGNED NOT NULL DEFAULT 0,
    form_id    INT UNSIGNED NOT NULL DEFAULT 0,
    data       LONGTEXT COMMENT '提交数据 JSON',
    ip         VARCHAR(50) DEFAULT '',
    province   VARCHAR(60) NOT NULL DEFAULT '',
    city       VARCHAR(60) NOT NULL DEFAULT '',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY idx_form (form_id), KEY idx_site (site_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='表单提交数据';

-- 站点配置
CREATE TABLE IF NOT EXISTS settings (
    id    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    site_id INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '所属站点',
    `key` VARCHAR(100) NOT NULL,
    `value` TEXT COMMENT '长文本配置（首页布局/商家介绍/GEO 广告）改用 TEXT，避免 VARCHAR(2000) 截断',
    UNIQUE KEY uk_site_key (site_id, `key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='站点配置';

-- AI 自动发文日志（防重复 + 已用关键词）
CREATE TABLE IF NOT EXISTS ai_post_log (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    site_id INT UNSIGNED NOT NULL DEFAULT 0,
    keyword VARCHAR(120) NOT NULL DEFAULT '',
    model VARCHAR(60) NOT NULL DEFAULT '',
    has_image TINYINT NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_site_date (site_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='AI自动发文日志';

-- 访问日志（IP / 地域 / 设备 / 页面 / 来源）
CREATE TABLE IF NOT EXISTS visits (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='访问日志';

-- GEO 词条库（可复用问答资产，支持一键同步为文章）
CREATE TABLE IF NOT EXISTS geo_entries (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='GEO词条库';

-- 下载专区（可分类、可下载源码）
CREATE TABLE IF NOT EXISTS downloads (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='下载专区';

-- 初始配置（单站点官网）
INSERT INTO settings (site_id,`key`,`value`) VALUES
(0,'site_name','得应盯'),
(0,'site_title',''),
(0,'phone','18732237111'),
(0,'email','hello@example.com'),
(0,'address','保定市朝阳南大街519号得应盯'),
(0,'footer_text','帮中小老板把业务推出去，让客户主动找上门。'),
(0,'techsupport_text',''),
(0,'techsupport_url',''),
(0,'theme','aurora'),
(0,'custom_c1','#22d3ee'),
(0,'custom_c2','#818cf8'),
(0,'custom_c3','#e879f9'),
(0,'beian',''),
(0,'copyright_year',''),
(0,'hero_title','业务拓展实战帮手'),
(0,'hero_sub','专注帮中小老板做业务拓展：获客、转化、口碑，让生意自己跑起来。'),
(0,'about_text','我们陪中小老板做业务拓展，用一套可落地的方法 + 工具，把流量变成客户、把客户变成复购。'),
(0,'stat1','500'),(0,'stat1_label','服务老板'),
(0,'stat2','30'),(0,'stat2_label','拓展打法'),
(0,'stat3','92'),(0,'stat3_label','续费满意度'),
(0,'stat4','7'),(0,'stat4_label','天见效'),
(0,'seo_keywords','业务拓展,中小老板,获客,客户转化,口碑营销'),
(0,'seo_description','得应盯 - 帮中小老板做业务拓展的实战服务'),
(0,'contact_phone',''),(0,'contact_phone2',''),(0,'contact_wx_qr',''),(0,'contact_mp_qr',''),
(0,'home_layout','[{"key":"hero","show":1},{"key":"scenario","show":1},{"key":"stats","show":1},{"key":"capabilities","show":1},{"key":"about","show":1},{"key":"workflow","show":1},{"key":"cta","show":1},{"key":"contact","show":1}]'),
(0,'city_enable','0')
ON DUPLICATE KEY UPDATE `value`=VALUES(`value`);
