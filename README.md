# deyingding-php · 得应盯网络科技 PHP 版建站系统

PHP 8 现代技术（PDO 预处理 + 单入口 + 原生模板），**零框架依赖、零 Composer**，宝塔一键部署。
内置：**栏目管理（树形子栏目）、内容管理、产品管理、SEO 优化、图片空间、全国分站开关、站点配置**。

## 功能清单

| 模块 | 说明 |
| --- | --- |
| 栏目管理 | 树形父子栏目，子栏目自动进前台导航下拉；栏目级 SEO |
| 内容管理 | 文章 CRUD，推荐位/上下架/浏览量，篇级 SEO 标题关键词描述 |
| 产品管理 | 产品 CRUD，含价格字段，推荐上首页 |
| SEO 优化 | 全站 + 栏目 + 每篇内容三级 SEO，前台自动输出 title/keywords/description |
| 图片空间 | 上传(jpg/png/gif/webp ≤10MB)/预览/复制URL/删除，按月分目录 |
| 全国分站 | 总开关 + 城市管理；开启后前台 `?city=保定` 自动切换标题后缀与关键词（站群 SEO 思路） |
| 站点配置 | 站名/电话/地址/首页文案/统计数字/SEO 后台直接改 |
| 后台登录 | PHP session + password_hash，安装向导自定义账号密码 |

## 目录结构

```
deyingding-php/
├── index.php          # 前台入口（?act=home|list|detail|city，可选 ?city=）
├── admin.php          # 后台入口（?m=dashboard|categories|articles|products|uploads|citysites|settings）
├── install/index.php # 网页安装向导（环境检测+填库+自动建表+写配置）
├── config.php         # 数据库配置（**安装向导自动生成，已被 .gitignore 忽略，请勿提交**）
├── database.sql       # 建库建表脚本（install 自动执行，含 users/admin_users/categories… 等约 15 张表）
├── lib/
│   ├── db.php         # PDO 单例 + 查询助手（预处理防注入）
│   └── funcs.php      # 设置/导航树/分页/上传/登录校验等
├── static/css/        # admin.css 后台样式 + style.css 前台样式
├── static/js/         # 后台交互脚本（editor/imgpick/main）
├── static/vendor/     # 第三方前端库（如 echarts，按需放置，缺失时图表功能降级提示）
├── uploads/           # 图片空间目录（自动按月建子目录；仅 .htaccess 入库，上传文件不入库）
├── views/             # 后台页面（login/dashboard/sidebar/categories/articles/products/uploads/citysites/settings/pager/geo）
├── tpl/               # 前台核心模板（layout/home/list/detail/city）
└── tpls/              # 可导入的界面模板（含 _demo_template_* 演示模板，后台「模板中心」导入）
```

## 快速部署（宝塔，安装向导版）

1. **上传**：整个 deyingding-php 目录传到站点根目录（无需改任何代码）
2. **访问**：浏览器打开 `http://你的域名/` → 自动跳转安装向导（也可直接访问 `/install/index.php`）
3. **填表**：按向导填数据库地址/库名/账号/密码 + 设置管理员账号密码
4. **完成**：自动建库、建表、写 config.php、创建管理员 → 点击「进入后台」

> 环境要求：PHP 8.0+（推荐 8.2/8.3），MySQL 5.7+。安装向导会自动检测环境，不满足会明确提示。
> 安全提示：安装完成后请删除服务器上的 `install` 目录（有 install.lock 保护，不删也能正常使用，但删掉更安全）。
> 重新安装：删除服务器根目录的 `install.lock` 文件，再访问 `/install/index.php`。

## 全国分站怎么用

1. 后台「全国分站」→ 开关选「开启」并保存
2. 添加城市，如：城市=保定、标题后缀=`- 保定分站`、关键词/描述填保定版
3. 前台任意页面加 `?city=保定` → 顶部出现分站生效提示，页面标题自动带上分站后缀，关键词/描述切换为分站版
4. 首页导航自动出现「城市分站」入口

## 前台路由一览

```
index.php                      首页
index.php?act=list&cat=1       栏目列表（文章/产品自动识别）
index.php?act=detail&type=article&id=1   文章详情
index.php?act=detail&type=product&id=1   产品详情
index.php?act=city             城市分站列表
index.php?city=保定            分站模式（任意页面）
```

## 后续升级方向（README 记录）

- [ ] 富文本编辑器（后台正文，可接入 wangEditor CDN）
- [ ] 管理员密码修改
- [ ] 内容批量操作 / 回收站
- [ ] Redis 缓存 + 页面静态化（?act= 结构已预留）
- [ ] 多站点 SaaS（sites 表 + 每站配置隔离）
- [ ] 接入 deyingding 完整深色前端模板（当前前台为简洁版，结构一致可无缝替换）
- [ ] 前台在线报名 / 在线咨询表单（对应 admin 里的 signup/consult 思路，需加表与页面）

## 第三方依赖

- **ECharts（图表可视化）**：后台「数据看板」使用 ECharts 渲染地域/访问图表。仓库不内置该库，请自行到 [Apache ECharts 官网](https://echarts.apache.org/) 下载 `echarts.min.js` 与 `china.js`，放入 `static/vendor/echarts/` 目录；缺失时看板会显示降级提示，不影响其他功能。ECharts 使用 [Apache-2.0 协议](https://www.apache.org/licenses/LICENSE-2.0)。
- 其余功能**零第三方 PHP / JS 依赖**，无需 Composer，开箱即用。

## 开源协议

本项目采用 **[MIT 协议](./LICENSE)**（详见仓库根目录 `LICENSE` 文件）。可自由使用、修改、再分发，请保留版权声明。
