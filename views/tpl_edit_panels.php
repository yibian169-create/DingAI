<?php
/** 主题设置 - 单页一览式面板
 *  所有配置在同一页展示，点右下角「保存全部配置」一次性提交。
 *  与首页 DIY 的关系：这里控制全局品牌/配色/SEO/联系/备案/语言；
 *  首页各模块的文案、图片、排序、显隐在「首页 DIY」里可视化编辑。
 */
$tab = $tab ?? '';                     // 已废弃内部 Tab，保留兼容
$from = $from ?? '';                   // 来源：tpls 或 settings
$settingsBaseUrl = $settingsBaseUrl ?? 'admin.php?m=settings';

// 后台主题设置：无条件从数据库读取最新配置，确保回显稳定。
// 即使调用方传入的 $data 为空或过期，这里也会强制读库，避免表单不显示已保存值
// （此前“保存后后台变空白”正是因为回显未取到值，而保存又把空值写回了库）。
$rawData = [];
if (function_exists('settings_all')) {
    $rawData = settings_all();
}
if (empty($rawData) && function_exists('DB') && function_exists('current_site_id')) {
    try {
        foreach (DB::all('SELECT `key`,`value` FROM settings WHERE site_id=?', [current_site_id()]) as $r) {
            $rawData[$r['key']] = $r['value'];
        }
    } catch (Throwable $e) {
        $rawData = [];
    }
}
// 优先用库里最新值；库为空时才退回到调用方传入的 $data（理论上不会走到这里）
$data = !empty($rawData) ? $rawData : ([] + ($data ?? []));
$theme = $data['theme'] ?? 'aurora';
$themes = [
    'aurora' => ['极光青', '#22d3ee', '#818cf8', '#e879f9'],
    'tech'   => ['科技紫', '#a855f7', '#6366f1', '#ec4899'],
    'jade'   => ['翡翠绿', '#10b981', '#22d3ee', '#84cc16'],
    'solar'  => ['活力橙', '#f97316', '#f59e0b', '#ef4444'],
    'custom' => ['自定义', null, null, null],
];

// 首页模块概览（与 DIY 共用同一套 key）
$homeModules = [
    ['key' => 'hero',         'name' => '首屏 Hero', 'desc' => '大标题、副标题、CTA'],
    ['key' => 'scenario',     'name' => '业务场景',  'desc' => '业务/服务场景展示'],
    ['key' => 'stats',        'name' => '数据展示',  'desc' => '数字滚动统计'],
    ['key' => 'capabilities', 'name' => '核心能力',  'desc' => '能力/优势卡片'],
    ['key' => 'about',        'name' => '关于我们',  'desc' => '公司介绍文案'],
    ['key' => 'workflow',     'name' => '流程',      'desc' => '服务/合作流程'],
    ['key' => 'cta',          'name' => '行动号召',  'desc' => '底部转化横幅'],
    ['key' => 'contact',      'name' => '联系我们',  'desc' => '联系表单与信息'],
    ['key' => 'ticker',       'name' => '滚动条',    'desc' => '顶部公告滚动'],
    ['key' => 'products',     'name' => '产品展示',  'desc' => '产品列表'],
    ['key' => 'news',         'name' => '文章动态',  'desc' => '最新文章'],
];
$layout = json_decode($data['home_layout'] ?? '[]', true) ?: [];
$layoutMap = [];
foreach ($layout as $it) {
    $layoutMap[$it['key'] ?? ''] = $it;
}

// 锚点导航
$sections = [
    'global'   => ['icon' => '🏢', 'label' => '品牌与联系'],
    'theme'    => ['icon' => '🎨', 'label' => '主题风格'],
    'support'  => ['icon' => '🛠️', 'label' => '技术支持'],
    'diy'      => ['icon' => '🧩', 'label' => '首页 DIY'],
    'home'     => ['icon' => '📝', 'label' => '首页回退文案'],
    'lang'     => ['icon' => '🌐', 'label' => '语言项'],
    'seo'      => ['icon' => '🔍', 'label' => 'SEO·联系·备案'],
    'security' => ['icon' => '🔒', 'label' => '后台安全'],
];
?>
<style>
/* ---------- 主题设置 - 单页一览式 ---------- */
.ts-wrap{--ts-gap:18px;display:grid;grid-template-columns:220px 1fr;gap:var(--ts-gap);align-items:start}
.ts-nav{position:sticky;top:14px;background:var(--card);border:1px solid var(--line);border-radius:14px;padding:14px;box-shadow:0 2px 10px rgba(15,23,42,.04)}
.ts-nav__title{font-size:13px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.6px;margin:0 0 10px;padding-left:8px}
.ts-nav__list{list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:4px}
.ts-nav__list a{display:flex;align-items:center;gap:10px;padding:9px 10px;border-radius:10px;color:var(--text);text-decoration:none;font-size:13.5px;transition:all .15s}
.ts-nav__list a:hover{background:rgba(99,102,241,.08);color:var(--primary)}
.ts-nav__list a.active{background:linear-gradient(135deg,var(--c1,#4f46e5),var(--c2,#818cf8));color:#fff;box-shadow:0 4px 12px rgba(79,70,229,.22)}
.ts-main{display:flex;flex-direction:column;gap:var(--ts-gap)}
.ts-card{background:var(--card);border:1px solid var(--line);border-radius:16px;padding:24px 26px;box-shadow:0 2px 10px rgba(15,23,42,.04);scroll-margin-top:18px}
.ts-card h3{margin:0 0 4px;font-size:17px;font-weight:700;display:flex;align-items:center;gap:10px}
.ts-card h3 .ico{font-size:20px;line-height:1}
.ts-card .desc{color:var(--muted);font-size:13px;margin:0 0 18px}
.ts-card .desc mark{background:rgba(99,102,241,.12);color:var(--primary);padding:1px 5px;border-radius:4px;font-size:12px}

.ts-fields{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:16px}
.ts-fields .field.full{grid-column:1/-1}
.ts-fields label{display:block;font-size:12.5px;font-weight:600;color:var(--text);margin-bottom:6px}
.ts-fields input[type="text"],
.ts-fields input[type="color"],
.ts-fields textarea,
.ts-fields select{width:100%;box-sizing:border-box;padding:10px 12px;border:1px solid var(--line);border-radius:10px;background:var(--input-bg,#fff);color:var(--text);font-size:13.5px;transition:border .15s,box-shadow .15s}
.ts-fields input:focus,
.ts-fields textarea:focus{outline:none;border-color:var(--primary);box-shadow:0 0 0 3px rgba(99,102,241,.12)}
.ts-fields textarea{resize:vertical}
.ts-fields input::placeholder,
.ts-fields textarea::placeholder{color:#94a3b8}

.theme-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:12px;margin-top:6px}
.theme-card{position:relative;padding:16px 12px;border:2px solid var(--line);border-radius:12px;cursor:pointer;text-align:center;transition:all .2s;background:var(--card)}
.theme-card:hover{border-color:var(--c1,#4f46e5);transform:translateY(-2px);box-shadow:0 8px 20px rgba(79,70,229,.15)}
.theme-card input{position:absolute;opacity:0;pointer-events:none}
.theme-card .dot{display:inline-flex;gap:3px;margin-bottom:8px}
.theme-card .dot i{width:18px;height:18px;border-radius:50%}
.theme-card .name{font-size:13px;font-weight:600;color:var(--text);display:block}
.theme-card.checked{border-color:var(--primary);background:rgba(99,102,241,.08)}
.theme-card.checked::after{content:'✓';position:absolute;top:6px;right:8px;color:var(--primary);font-weight:800;font-size:14px}
.theme-custom{display:grid;grid-template-columns:repeat(auto-fit,minmax(120px,1fr));gap:16px;margin-top:16px;padding:18px;background:rgba(99,102,241,.04);border:1px dashed rgba(99,102,241,.25);border-radius:12px}
.theme-custom label{display:flex;align-items:center;gap:10px;font-size:13px;cursor:pointer}
.theme-custom input[type="color"]{width:44px;height:32px;padding:2px}

.diy-link{display:inline-flex;align-items:center;gap:8px;margin-left:auto;font-size:13px}
.diy-card{background:linear-gradient(135deg,rgba(99,102,241,.08),rgba(34,211,238,.08));border-color:rgba(99,102,241,.2)}
.diy-module-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(170px,1fr));gap:12px;margin-top:10px}
.diy-module-card{border:1px solid var(--line);border-radius:12px;padding:14px;background:var(--card);transition:all .18s}
.diy-module-card.on{border-color:rgba(99,102,241,.45);background:rgba(99,102,241,.06)}
.diy-module-card.off{opacity:.6}
.diy-module-top{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:6px}
.diy-module-top strong{font-size:14px}
.diy-badge{font-size:11px;padding:3px 8px;border-radius:20px}
.diy-badge.badge-on{background:rgba(16,185,129,.12);color:#10b981}
.diy-badge.badge-off{background:var(--toolbar-bg);color:var(--muted)}
.diy-module-desc{font-size:12px;color:var(--muted);line-height:1.45}

.imgpick-preview{width:44px;height:44px;border-radius:10px;border:1px solid var(--line);object-fit:cover;display:block;background:var(--toolbar-bg);flex:none}
.imgpick-preview[hidden]{display:none}
.imgpick-row{display:flex;align-items:center;gap:10px}
.imgpick-row input{flex:1}

.switch{display:inline-flex;align-items:center;gap:10px;cursor:pointer;font-size:13.5px}
.switch input{width:42px;height:24px;appearance:none;background:var(--line);border-radius:12px;position:relative;cursor:pointer;transition:background .2s}
.switch input::after{content:'';position:absolute;top:2px;left:2px;width:20px;height:20px;background:#fff;border-radius:50%;transition:transform .2s;box-shadow:0 1px 3px rgba(0,0,0,.15)}
.switch input:checked{background:var(--primary)}
.switch input:checked::after{transform:translateX(18px)}

.ts-savebar{position:sticky;bottom:16px;display:flex;justify-content:flex-end;z-index:10}
.ts-savebar .inner{display:flex;align-items:center;gap:14px;background:linear-gradient(180deg,transparent,rgba(241,245,249,.92) 40%);padding:14px 0}
.ts-savebar button{padding:13px 34px;font-size:14.5px;border-radius:12px;box-shadow:0 10px 28px rgba(79,70,229,.28)}
.ts-savebar .hint{font-size:12.5px;color:var(--muted);background:var(--card);border:1px solid var(--line);padding:8px 14px;border-radius:10px}

@media (max-width:860px){
    .ts-wrap{grid-template-columns:1fr}
    .ts-nav{position:static;display:flex;gap:8px;overflow:auto;padding:10px}
    .ts-nav__title{display:none}
    .ts-nav__list{flex-direction:row;gap:6px;min-width:max-content}
    .ts-nav__list a{white-space:nowrap}
    .theme-grid{grid-template-columns:repeat(2,1fr)}
}
</style>

<div class="ts-wrap">

<nav class="ts-nav">
  <div class="ts-nav__title">快速定位</div>
  <ul class="ts-nav__list">
    <?php foreach ($sections as $id => $info): ?>
    <li><a href="#sec-<?= $id ?>" class="nav-anchor" data-target="sec-<?= $id ?>"><span class="ico"><?= $info['icon'] ?></span><?= e($info['label']) ?></a></li>
    <?php endforeach; ?>
  </ul>
</nav>

<form method="post" action="admin.php?m=settings_save" id="tplForm" class="ts-main">
  <input type="hidden" name="tab" value="">
  <?php if ($from !== ''): ?><input type="hidden" name="from" value="<?= e($from) ?>"><?php endif; ?>

  <?php if (!empty($_GET['debug'])): ?>
  <!-- 调试面板：URL 加 ?debug=1 可见，用于排查表单回显问题 -->
  <section class="ts-card" style="background:#fffbeb;border-color:#fcd34d">
    <h3>🔧 调试信息</h3>
    <p class="desc">data 数组共 <?= count($data) ?> 个 key；来源：<?= e($from ?: 'settings') ?></p>
    <pre style="font-size:12px;max-height:240px;overflow:auto;background:#fff;border:1px solid #fde68a;border-radius:8px;padding:10px"><?= e(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)) ?></pre>
  </section>
  <?php endif; ?>

  <!-- ===== 品牌与联系方式 ===== -->
  <section class="ts-card" id="sec-global">
    <h3><span class="ico">🏢</span>品牌与联系方式</h3>
    <p class="desc">站点基础信息，会显示在导航、页脚、联系模块等全局位置 <mark>主站全局生效</mark></p>
    <div class="ts-fields">
      <div class="field">
        <label>站点名称（简称）</label>
        <input type="text" name="site_name" value="<?= e($data['site_name'] ?? '') ?>" placeholder="如：得应盯">
        <p style="margin:5px 0 0;font-size:12px;color:var(--muted)">显示在导航、页脚、LOGO 等品牌位置，不等同于网页标题。</p>
      </div>
      <div class="field"><label>联系电话</label><input type="text" name="phone" value="<?= e($data['phone'] ?? '') ?>" placeholder="前台默认联系电话"></div>
      <div class="field"><label>联系邮箱</label><input type="text" name="email" value="<?= e($data['email'] ?? '') ?>" placeholder="service@example.com"></div>
      <div class="field full"><label>公司地址</label><input type="text" name="address" value="<?= e($data['address'] ?? '') ?>" placeholder="公司详细地址"></div>
      <div class="field full"><label>页脚简介</label><input type="text" name="footer_text" value="<?= e($data['footer_text'] ?? '') ?>" placeholder="一句话介绍，显示在页脚"></div>
    </div>
  </section>

  <!-- ===== 主题风格 ===== -->
  <section class="ts-card" id="sec-theme">
    <h3><span class="ico">🎨</span>主题风格</h3>
    <p class="desc">全站配色主题；选择「自定义」时可自由配置三主色 <mark>主站全局生效</mark></p>
    <div class="theme-grid">
      <?php foreach ($themes as $key => $info): ?>
      <label class="theme-card <?= $theme === $key ? 'checked' : '' ?>" onclick="selectTheme('<?= $key ?>')">
        <input type="radio" name="theme" value="<?= $key ?>" <?= $theme === $key ? 'checked' : '' ?> data-preset="<?= $key ?>">
        <span class="dot">
          <?php if ($key !== 'custom'): ?>
          <i style="background:<?= $info[1] ?>"></i><i style="background:<?= $info[2] ?>"></i><i style="background:<?= $info[3] ?>"></i>
          <?php else: ?>
          <i style="background:<?= e($data['custom_c1'] ?? '#22d3ee') ?>"></i>
          <i style="background:<?= e($data['custom_c2'] ?? '#818cf8') ?>"></i>
          <i style="background:<?= e($data['custom_c3'] ?? '#e879f9') ?>"></i>
          <?php endif; ?>
        </span>
        <span class="name"><?= $info[0] ?></span>
      </label>
      <?php endforeach; ?>
    </div>
    <div class="theme-custom" id="customRow" style="<?= $theme === 'custom' ? '' : 'display:none' ?>">
      <label>主色 <input type="color" name="custom_c1" value="<?= e($data['custom_c1'] ?? '#22d3ee') ?>"></label>
      <label>辅色 <input type="color" name="custom_c2" value="<?= e($data['custom_c2'] ?? '#818cf8') ?>"></label>
      <label>点缀 <input type="color" name="custom_c3" value="<?= e($data['custom_c3'] ?? '#e879f9') ?>"></label>
    </div>
  </section>

  <!-- ===== 技术支持 ===== -->
  <section class="ts-card" id="sec-support">
    <h3><span class="ico">🛠️</span>技术支持</h3>
    <p class="desc">页脚右侧展示技术服务商信息（可留空不显示） <mark>主站页脚生效</mark></p>
    <div class="ts-fields">
      <div class="field"><label>技术支持名称</label><input type="text" name="techsupport_text" value="<?= e($data['techsupport_text'] ?? '') ?>" placeholder="如：QQ: 18732237111"></div>
      <div class="field full"><label>技术支持链接</label><input type="text" name="techsupport_url" value="<?= e($data['techsupport_url'] ?? '') ?>" placeholder="https://wpa.qq.com/msgrd?v=3&uin=18732237111"></div>
    </div>
  </section>

  <!-- ===== 首页 DIY（入口+关系说明） ===== -->
  <section class="ts-card diy-card" id="sec-diy">
    <h3 style="display:flex;align-items:center;flex-wrap:wrap;gap:10px">
      <span class="ico">🧩</span>首页模块与可视化编辑
      <a href="admin.php?m=tpls&tab=diy" class="btn btn-p diy-link">打开首页 DIY →</a>
    </h3>
    <p class="desc">
      <mark>这里不控制首页模块内容</mark>。首页各模块（Hero、业务场景、数据、产品、联系等）的
      <strong>文案、图片、排序、显隐</strong>请在「首页 DIY」里可视化拖拽编辑；本页下方的「首页回退文案」仅在没有 DIY 配置时作为兜底显示。
    </p>
    <div class="diy-module-grid">
      <?php foreach ($homeModules as $m): $on = !empty($layoutMap[$m['key']]['show']); ?>
      <div class="diy-module-card <?= $on ? 'on' : 'off' ?>">
        <div class="diy-module-top">
          <strong><?= e($m['name']) ?></strong>
          <span class="diy-badge <?= $on ? 'badge-on' : 'badge-off' ?>"><?= $on ? '已启用' : '未启用' ?></span>
        </div>
        <div class="diy-module-desc"><?= e($m['desc']) ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- ===== 首页回退文案 ===== -->
  <section class="ts-card" id="sec-home">
    <h3><span class="ico">📝</span>首页回退文案</h3>
    <p class="desc">当「首页 DIY」没有配置对应模块时，前台会显示这里的文字 <mark>仅作兜底，优先级低于 DIY</mark></p>
    <div class="ts-fields">
      <div class="field"><label>首屏主标题</label><input type="text" name="hero_title" value="<?= e($data['hero_title'] ?? '') ?>" placeholder="首页大标题"></div>
      <div class="field full"><label>首屏副标题</label><textarea name="hero_sub" rows="2" placeholder="首页副标题或一句话介绍"><?= e($data['hero_sub'] ?? '') ?></textarea></div>
      <div class="field full"><label>关于我们文案</label><textarea name="about_text" rows="3" placeholder="公司简介"><?= e($data['about_text'] ?? '') ?></textarea></div>
      <?php for ($i = 1; $i <= 4; $i++): $suf = ['','万+','万+','%'][$i-1] ?? '+'; ?>
      <div class="field"><label>数字 <?= $i ?> <?= $suf ? '（后缀 ' . $suf . '）' : '' ?></label><input type="text" name="stat<?= $i ?>" value="<?= e($data['stat' . $i] ?? '') ?>"></div>
      <div class="field"><label>标签 <?= $i ?></label><input type="text" name="stat<?= $i ?>_label" value="<?= e($data['stat' . $i . '_label'] ?? '') ?>"></div>
      <?php endfor; ?>
    </div>
  </section>

  <!-- ===== 语言项 ===== -->
  <section class="ts-card" id="sec-lang">
    <h3><span class="ico">🌐</span>语言项</h3>
    <p class="desc">前台常用按钮与提示文字，可在后台统一修改 <mark>主站全局生效</mark></p>
    <div class="ts-fields">
      <div class="field"><label>返回首页</label><input type="text" name="lang_home" value="<?= e($data['lang_home'] ?? '返回首页') ?>"></div>
      <div class="field"><label>查看更多</label><input type="text" name="lang_more" value="<?= e($data['lang_more'] ?? '查看更多') ?>"></div>
      <div class="field"><label>联系我们</label><input type="text" name="lang_contact" value="<?= e($data['lang_contact'] ?? '联系我们') ?>"></div>
      <div class="field"><label>立即咨询</label><input type="text" name="lang_consult" value="<?= e($data['lang_consult'] ?? '立即咨询') ?>"></div>
      <div class="field"><label>了解详情</label><input type="text" name="lang_read_more" value="<?= e($data['lang_read_more'] ?? '了解详情') ?>"></div>
      <div class="field"><label>暂无内容提示</label><input type="text" name="lang_empty" value="<?= e($data['lang_empty'] ?? '暂无内容，敬请期待') ?>"></div>
    </div>
  </section>

  <!-- ===== SEO · 联系 · 备案 ===== -->
  <section class="ts-card" id="sec-seo">
    <h3><span class="ico">🔍</span>SEO · 联系 · 备案</h3>
    <p class="desc">仅作用于主站首页及列表页默认标题/关键词/描述；栏目与内容页可单独覆盖 <mark>主站全局生效</mark></p>
    <div class="ts-fields">
      <div class="field full">
        <label>网站标题（&lt;title&gt;）</label>
        <input type="text" name="site_title" value="<?= e($data['site_title'] ?? '') ?>" placeholder="如：得应盯 - 企业官网 AI GEO CMS 建站系统">
        <p style="margin:5px 0 0;font-size:12px;color:var(--muted)">浏览器标签页与搜索引擎结果中显示的完整网站标题；留空则使用「站点名称（简称）」。</p>
      </div>
      <div class="field full"><label>SEO 关键词</label><input type="text" name="seo_keywords" value="<?= e($data['seo_keywords'] ?? '') ?>" placeholder="多个关键词用英文逗号分隔"></div>
      <div class="field full"><label>SEO 描述</label><textarea name="seo_description" rows="3" placeholder="120 字左右，吸引点击"><?= e($data['seo_description'] ?? '') ?></textarea></div>
      <div class="field"><label>服务电话</label><input type="text" name="contact_phone" value="<?= e($data['contact_phone'] ?? '') ?>" placeholder="留空使用全局联系电话"></div>
      <div class="field"><label>导师 / 第二电话</label><input type="text" name="contact_phone2" value="<?= e($data['contact_phone2'] ?? '') ?>" placeholder="如：138-0000-8888"></div>
      <div class="field full">
        <label>负责人微信二维码图片 URL</label>
        <div class="imgpick-row">
          <input type="text" id="contact_wx_qr" name="contact_wx_qr" value="<?= e($data['contact_wx_qr'] ?? '') ?>" placeholder="上传图片后填入地址，或留空">
          <label class="btn btn-s dyimg-upload-label" style="cursor:pointer;position:relative;overflow:hidden">
            <input type="file" accept="image/*" style="position:absolute;inset:0;opacity:0;cursor:pointer" onchange="dyImgUpload(this,'contact_wx_qr')">
            <span class="dyimg-btn-txt">上传</span>
          </label>
          <button type="button" class="btn btn-s" onclick="dyImgPicker('contact_wx_qr')">图片空间</button>
          <img id="prev_contact_wx_qr" class="imgpick-preview" src="<?= e($data['contact_wx_qr'] ?? '') ?>" alt="预览" <?= empty($data['contact_wx_qr']) ? 'hidden' : '' ?>>
        </div>
      </div>
      <div class="field full">
        <label>微信公众号二维码图片 URL</label>
        <div class="imgpick-row">
          <input type="text" id="contact_mp_qr" name="contact_mp_qr" value="<?= e($data['contact_mp_qr'] ?? '') ?>" placeholder="上传图片后填入地址，或留空">
          <label class="btn btn-s dyimg-upload-label" style="cursor:pointer;position:relative;overflow:hidden">
            <input type="file" accept="image/*" style="position:absolute;inset:0;opacity:0;cursor:pointer" onchange="dyImgUpload(this,'contact_mp_qr')">
            <span class="dyimg-btn-txt">上传</span>
          </label>
          <button type="button" class="btn btn-s" onclick="dyImgPicker('contact_mp_qr')">图片空间</button>
          <img id="prev_contact_mp_qr" class="imgpick-preview" src="<?= e($data['contact_mp_qr'] ?? '') ?>" alt="预览" <?= empty($data['contact_mp_qr']) ? 'hidden' : '' ?>>
        </div>
      </div>
      <div class="field"><label>ICP 备案号</label><input type="text" name="beian" value="<?= e($data['beian'] ?? '') ?>" placeholder="如：冀ICP备xxxxxxxx号"></div>
      <div class="field"><label>版权起始年份</label><input type="text" name="copyright_year" value="<?= e($data['copyright_year'] ?? date('Y')) ?>"></div>
    </div>
  </section>

  <!-- ===== 后台安全 ===== -->
  <section class="ts-card" id="sec-security">
    <h3><span class="ico">🔒</span>后台安全</h3>
    <p class="desc">登录页滑动验证（防暴力破解；需要频繁测试时可关闭）</p>
    <label class="switch">
      <input type="checkbox" name="login_captcha" value="1" <?= ($data['login_captcha'] ?? '1') === '1' ? 'checked' : '' ?>>
      启用后台登录滑动验证
    </label>
  </section>

  <!-- 保存条 -->
  <div class="ts-savebar">
    <div class="inner">
      <span class="hint">所有字段一次性提交保存（含清空后的空值），保存后立即同步到主站前台</span>
      <button type="submit" class="btn btn-p">保存全部配置</button>
    </div>
  </div>

  <?= csrf_field() ?>
</form>

</div><!-- /.ts-wrap -->

<script>
function selectTheme(key) {
  var cards = document.querySelectorAll('.theme-card');
  cards.forEach(function (c) { c.classList.toggle('checked', c.querySelector('input').value === key); });
  document.getElementById('customRow').style.display = (key === 'custom') ? '' : 'none';
}

// 锚点导航高亮 + 平滑滚动
document.querySelectorAll('.nav-anchor').forEach(function (a) {
  a.addEventListener('click', function (e) {
    e.preventDefault();
    var id = this.getAttribute('href').slice(1);
    var el = document.getElementById(id);
    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
  });
});
var navAnchors = Array.from(document.querySelectorAll('.nav-anchor'));
var sections = navAnchors.map(function(a){ return document.getElementById(a.getAttribute('href').slice(1)); }).filter(Boolean);
function updateActive(){
  var cur = sections[0];
  sections.forEach(function(sec){
    var rect = sec.getBoundingClientRect();
    if (rect.top <= 80) cur = sec;
  });
  navAnchors.forEach(function(a){ a.classList.toggle('active', a.getAttribute('href').slice(1) === cur.id); });
}
window.addEventListener('scroll', updateActive, { passive: true });
updateActive();
</script>

<!-- 图片上传/图片空间选择器（与原有逻辑一致） -->
<div class="dyimg-modal" id="dyImgPickerModal" style="display:none" onclick="if(event.target===this)dyImgPickerClose()">
  <div class="dyimg-modal__overlay"></div>
  <div class="dyimg-modal__box">
    <div class="dyimg-modal__head">
      <h3>从图片空间选择</h3>
      <button type="button" class="btn btn-s" onclick="dyImgPickerClose()">关闭</button>
    </div>
    <div class="dyimg-modal__body" id="dyImgPickerBody"><div class="dyimg-empty">加载中...</div></div>
  </div>
</div>
<div class="dyimg-toast" id="dyImgToast" style="display:none"></div>

<style>
.dyimg-modal{display:none;position:fixed;inset:0;z-index:200;align-items:center;justify-content:center;padding:20px}
.dyimg-modal__overlay{position:absolute;inset:0;background:rgba(2,6,23,.6)}
.dyimg-modal__box{position:relative;background:var(--card);border:1px solid var(--line);border-radius:14px;width:min(760px,92vw);max-height:86vh;display:flex;flex-direction:column;box-shadow:0 20px 60px rgba(0,0,0,.35)}
.dyimg-modal__head{display:flex;justify-content:space-between;align-items:center;padding:14px 18px;border-bottom:1px solid var(--line)}
.dyimg-modal__head h3{margin:0;font-size:15px}
.dyimg-modal__body{padding:16px 18px;overflow:auto;display:grid;grid-template-columns:repeat(4,1fr);gap:12px}
.dyimg-item{cursor:pointer;border:1px solid var(--line);border-radius:10px;overflow:hidden;background:var(--card);transition:all .18s}
.dyimg-item:hover{border-color:var(--primary);transform:translateY(-2px);box-shadow:0 6px 18px rgba(79,70,229,.12)}
.dyimg-item img{width:100%;aspect-ratio:1;object-fit:cover;display:block}
.dyimg-item__name{padding:7px 9px;font-size:11.5px;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.dyimg-empty{color:var(--muted);text-align:center;padding:30px 0;grid-column:1/-1}
.dyimg-toast{position:fixed;left:50%;bottom:40px;transform:translateX(-50%);background:#111827;color:#fff;padding:10px 22px;border-radius:10px;font-size:13.5px;z-index:9999;box-shadow:0 10px 30px rgba(0,0,0,.35);display:none}
[data-theme="dark"] .dyimg-toast{background:#0f172a;color:#f8fafc}
</style>

<script>
(function () {
  var currentTarget = null;
  var toastEl = document.getElementById('dyImgToast');
  function dyImgToast(msg) {
    if (!toastEl) return;
    toastEl.textContent = msg;
    toastEl.style.display = 'block';
    clearTimeout(toastEl._t);
    toastEl._t = setTimeout(function () { toastEl.style.display = 'none'; }, 2200);
  }
  window.dyImgSet = function (targetId, url) {
    var input = document.getElementById(targetId);
    if (!input) return;
    input.value = url;
    var prev = document.getElementById('prev_' + targetId);
    if (prev) {
      prev.src = url || '';
      prev.hidden = !url;
    }
  };
  window.dyImgUpload = function (fileInput, targetId) {
    if (!fileInput.files || !fileInput.files[0]) return;
    var label = fileInput.closest('.dyimg-upload-label');
    var txt = label ? label.querySelector('.dyimg-btn-txt') : null;
    if (txt) txt.textContent = '上传中...';
    fileInput.disabled = true;
    var fd = new FormData();
    fd.append('file', fileInput.files[0]);
    fetch('admin.php?m=upload_json', { method: 'POST', body: fd })
      .then(function (r) { return r.json(); })
      .then(function (j) {
        if (j.ok) { dyImgSet(targetId, j.url); dyImgToast('上传成功'); }
        else { alert(j.msg || '上传失败'); }
      })
      .catch(function (e) { alert('上传出错：' + e.message); })
      .finally(function () {
        if (txt) txt.textContent = '上传';
        fileInput.disabled = false;
        fileInput.value = '';
      });
  };
  window.dyImgPicker = function (targetId) {
    currentTarget = targetId;
    var modal = document.getElementById('dyImgPickerModal');
    var body = document.getElementById('dyImgPickerBody');
    if (!modal || !body) return;
    modal.style.display = 'flex';
    body.innerHTML = '<div class="dyimg-empty"><span class="spinner" style="display:inline-block"></span> 加载图片空间...</div>';
    fetch('admin.php?m=uploads_picker')
      .then(function (r) { return r.json(); })
      .then(function (j) {
        if (!j.ok || !j.list || !j.list.length) {
          body.innerHTML = '<div class="dyimg-empty">暂无图片，请先去「图片空间」上传</div>';
          return;
        }
        body.innerHTML = '';
        j.list.forEach(function (it) {
          var el = document.createElement('div');
          el.className = 'dyimg-item';
          el.title = it.name || '';
          el.innerHTML = '<img src="' + (it.url || '') + '" alt="' + (it.name || '') + '"><div class="dyimg-item__name">' + (it.name || '') + '</div>';
          el.onclick = function () { dyImgSet(targetId, it.url); dyImgPickerClose(); dyImgToast('已选择图片'); };
          body.appendChild(el);
        });
      })
      .catch(function (e) { body.innerHTML = '<div class="dyimg-empty" style="color:var(--danger)">加载失败：' + e.message + '</div>'; });
  };
  window.dyImgPickerClose = function () {
    var modal = document.getElementById('dyImgPickerModal');
    if (modal) modal.style.display = 'none';
    currentTarget = null;
  };
})();
</script>
