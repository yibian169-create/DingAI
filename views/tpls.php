<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>模板中心 - 得应盯后台</title>
<link rel="stylesheet" href="static/css/admin.css">
<script>
(function(){try{var t=localStorage.getItem('dy_theme')||'light';document.documentElement.setAttribute('data-theme',t);}catch(e){}})();
</script>
<style>
/* 模板中心标签页 */
.mc-tab{display:flex;gap:8px;border-bottom:2px solid var(--line);margin-bottom:22px}
.mc-tab a{padding:12px 20px;font-size:14.5px;color:var(--muted);border-bottom:3px solid transparent;margin-bottom:-2px;text-decoration:none;font-weight:600;border-radius:8px 8px 0 0}
.mc-tab a.active{color:var(--primary);border-bottom-color:var(--primary);background:#eef2ff}
.mc-tab a:hover{color:var(--primary)}

/* 选择模板 */
.tpl-gallery{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:16px}
.tpl-card{border:2px solid var(--line);border-radius:14px;padding:18px;background:var(--card);position:relative;display:flex;flex-direction:column;gap:10px}
.tpl-card.active{border-color:var(--primary)}
.tpl-card .tag{position:absolute;top:12px;right:12px;font-size:11px;padding:3px 10px;border-radius:999px;background:#eef2ff;color:var(--primary)}
.tpl-card .tag.builtin{background:#e0f2fe;color:#0ea5e9}
.tpl-card h3{font-size:16px;font-weight:700;margin:0}
.tpl-card p{color:var(--muted);font-size:12.5px;margin:0;line-height:1.6}
.tpl-card .actions{display:flex;gap:8px;margin-top:auto;flex-wrap:wrap}
.tpl-card .actions form{display:inline}
.tpl-card .colorbar{display:flex;gap:6px;margin:8px 0 4px}
.tpl-card .colorbar span{width:22px;height:22px;border-radius:50%;border:2px solid var(--line);box-shadow:0 2px 5px rgba(0,0,0,.08)}

/* 主题设置（与 tpl_edit_panels.php 共用） */
.tpl-tab{display:flex;gap:0;border-bottom:2px solid var(--line);margin-bottom:24px}
.tpl-tab a{padding:12px 20px;font-size:14.5px;color:var(--muted);border-bottom:3px solid transparent;margin-bottom:-2px;text-decoration:none;font-weight:600}
.tpl-tab a.active{color:var(--primary);border-bottom-color:var(--primary)}
.tpl-tab a:hover{color:var(--primary)}
.tpl-panel{background:var(--card);border:1px solid var(--line);border-radius:14px;padding:24px 28px;box-shadow:0 2px 8px rgba(15,23,42,.04);margin-bottom:18px}
.tpl-panel h3{margin:0 0 4px;font-size:17px;font-weight:700;display:flex;align-items:center;gap:10px}
.tpl-panel h3::before{content:'';width:4px;height:18px;border-radius:2px;background:linear-gradient(135deg,var(--c1,#4f46e5),var(--c2,#818cf8))}
.tpl-panel .desc{color:var(--muted);font-size:13px;margin:0 0 18px}
.theme-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:12px;margin-top:6px}
.theme-card{position:relative;padding:16px 14px;border:2px solid var(--line);border-radius:12px;cursor:pointer;text-align:center;transition:all .2s;background:var(--card)}
.theme-card:hover{border-color:var(--c1,#4f46e5);transform:translateY(-2px);box-shadow:0 8px 20px rgba(79,70,229,.15)}
.theme-card input{position:absolute;opacity:0;pointer-events:none}
.theme-card .dot{display:inline-flex;gap:3px;margin-bottom:8px}
.theme-card .dot i{width:18px;height:18px;border-radius:50%}
.theme-card .name{font-size:13px;font-weight:600;color:var(--text);display:block}
.theme-card.checked{border-color:var(--primary);background:#eef2ff}
.theme-card.checked::after{content:'✓';position:absolute;top:6px;right:8px;color:var(--primary);font-weight:800;font-size:14px}
.theme-custom{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-top:14px}
.theme-custom label{font-size:12.5px;color:var(--muted);display:flex;align-items:center;gap:10px}
.theme-custom input[type=color]{width:48px;height:32px;border:1px solid var(--line);border-radius:8px;padding:2px;cursor:pointer}
.tpl-save-bar{position:sticky;bottom:0;background:linear-gradient(180deg,transparent,rgba(241,245,249,.95) 40%);padding:14px 0;text-align:right;z-index:5}

/* 首页 DIY - 可视化卡片拖拽 */
.diy-list{display:grid;grid-template-columns:repeat(auto-fill,minmax(310px,1fr));gap:14px;align-items:stretch;grid-auto-rows:1fr}
.diy-item{display:flex;align-items:center;gap:14px;padding:16px;background:var(--card);border:1px solid var(--line);border-radius:14px;cursor:grab;transition:box-shadow .2s,border-color .2s,opacity .2s;position:relative;min-height:92px;box-sizing:border-box}
.diy-item:hover{border-color:var(--primary);box-shadow:0 6px 18px rgba(79,70,229,.12)}
.diy-item.dragging{opacity:.55;box-shadow:0 10px 26px rgba(15,23,42,.18);cursor:grabbing}
.diy-item.is-off{opacity:.55;filter:grayscale(.4)}
.diy-item .num{width:24px;height:24px;flex:none;display:grid;place-items:center;background:#eef2ff;color:var(--primary);border-radius:50%;font-size:11px;font-weight:800}
.diy-item .handle{color:var(--muted);font-size:18px;cursor:grab;line-height:1;flex:none}
.diy-item .icon{width:46px;height:46px;flex:none;border-radius:12px;display:grid;place-items:center;font-size:22px;background:linear-gradient(135deg,var(--c1,#4f46e5),var(--c2,#818cf8));box-shadow:0 4px 10px rgba(79,70,229,.22)}
.diy-item .meta{flex:1;min-width:0;display:flex;flex-direction:column;justify-content:center;gap:3px;min-height:0}
.diy-item .name{font-size:14.5px;font-weight:700;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;line-height:1.35}
.diy-item .desc{font-size:12px;color:var(--muted);line-height:1.35;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
/* 显隐开关 */
.diy-item .sw{position:relative;flex:none;display:inline-flex;align-items:center;gap:8px;cursor:pointer;user-select:none;margin-left:auto}
.diy-item .sw input{position:absolute;opacity:0;width:0;height:0}
.diy-item .sw .track{width:42px;height:24px;border-radius:999px;background:#cbd5e1;transition:background .2s;position:relative;flex:none}
.diy-item .sw .track::after{content:'';position:absolute;top:3px;left:3px;width:18px;height:18px;border-radius:50%;background:#fff;transition:transform .2s;box-shadow:0 1px 3px rgba(0,0,0,.25)}
.diy-item .sw input:checked + .track{background:var(--primary)}
.diy-item .sw input:checked + .track::after{transform:translateX(18px)}
.diy-item .sw .txt{font-size:12px;color:var(--muted);min-width:28px;text-align:left}
.diy-hint{color:var(--muted);font-size:13px;margin-bottom:16px}
.diy-hint b{color:var(--primary)}
.diy-tip{display:inline-flex;align-items:center;gap:6px;margin-left:8px;font-size:12px;color:var(--muted);background:var(--card);border:1px dashed var(--line);padding:3px 10px;border-radius:999px}

/* 可视化首页编辑器 */
.ve{display:grid;grid-template-columns:240px minmax(0,1fr);gap:0;height:calc(100vh - 170px);min-height:480px;border:1px solid var(--line);border-radius:14px;overflow:hidden;background:var(--card);position:relative}
.ve-lib,.ve-stage{min-width:0}
.ve-lib{background:var(--card-2);display:flex;flex-direction:column;overflow:hidden}
.ve-lib__head,.ve-prop__head{padding:12px 14px;font-weight:700;font-size:14px;border-bottom:1px solid var(--line);flex:none}
/* 高级属性：浮动按钮 + 抽屉（不再占右侧 grid） */
.ve-fab{position:absolute;right:14px;bottom:14px;z-index:50;display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:999px;background:linear-gradient(115deg,#22d3ee,#818cf8);color:#07101f;font-size:13px;font-weight:700;cursor:pointer;border:none;box-shadow:0 6px 18px rgba(34,211,238,.35);transition:transform .15s}
.ve-fab:hover{transform:translateY(-2px)}
.ve-fab[hidden]{display:none}
.ve-prop{position:absolute;right:14px;bottom:60px;width:320px;max-height:calc(100% - 80px);background:var(--card-2);border:1px solid var(--line);border-radius:12px;display:flex;flex-direction:column;overflow:hidden;box-shadow:0 12px 40px rgba(0,0,0,.25);z-index:60}
.ve-prop.collapsed{display:none}
.ve-prop__head{display:flex;align-items:center;justify-content:space-between;gap:8px;cursor:pointer;user-select:none}
.ve-prop__head:hover{background:var(--card)}
.ve-prop__close{background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.12);color:var(--muted);cursor:pointer;font-size:14px;padding:0 8px;border-radius:6px;line-height:1;height:24px}
.ve-prop__close:hover{color:#f87171;border-color:#f87171}
.ve-lib__list{flex:1;overflow:auto;padding:10px;display:flex;flex-direction:column;gap:8px}
.ve-lib__item{display:flex;align-items:center;gap:10px;padding:10px 12px;background:var(--card);border:1px solid var(--line);border-radius:10px;cursor:grab}
.ve-lib__item.dragging{opacity:.5}
.ve-lib__item.sel{border-color:var(--primary);box-shadow:0 0 0 2px rgba(79,70,229,.15)}
.ve-lib__item .ic{width:32px;height:32px;flex:none;border-radius:8px;display:grid;place-items:center;font-size:16px;background:linear-gradient(135deg,var(--c1,#4f46e5),var(--c2,#818cf8));color:#fff}
.ve-lib__item .nm{flex:1;min-width:0;font-size:13px;font-weight:600;line-height:1.3}
.ve-lib__item .nm small{display:block;font-weight:400;color:var(--muted);font-size:11px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.ve-lib__item .eye{cursor:pointer;color:var(--muted);font-size:13px;flex:none}
.ve-lib__item.off{opacity:.5}
.ve-lib__add{padding:10px;border-top:1px solid var(--line);display:flex;gap:6px}
.ve-lib__add select{flex:1;padding:6px;border:1px solid var(--line);border-radius:8px;font-size:13px;background:var(--card);color:var(--text)}
.ve-stage{display:flex;flex-direction:column;overflow:hidden;background:var(--card-2)}
.ve-toolbar{display:flex;align-items:center;justify-content:space-between;padding:8px 12px;border-bottom:1px solid var(--line);background:var(--card)}
.ve-dev{display:flex;gap:4px}
.ve-dev button{padding:4px 12px;border:1px solid var(--line);background:var(--card);border-radius:8px;cursor:pointer;font-size:13px;color:var(--text)}
.ve-dev button.active{background:var(--primary);color:#fff;border-color:var(--primary)}
.ve-frame-wrap{flex:1;overflow:auto;padding:16px;display:flex;justify-content:center;background:#eef1f6}
.ve-frame-wrap iframe{border:0;background:#fff;border-radius:12px;box-shadow:0 4px 20px rgba(15,23,42,.12);transition:width .2s;width:100%;height:100%}
.ve-frame-wrap.mb iframe{width:390px;height:780px}
.ve-prop__body{flex:1;overflow:auto;padding:14px}
.ve-field{margin-bottom:14px}
.ve-field label{display:block;font-size:12px;color:var(--muted);margin-bottom:4px}
.ve-field input,.ve-field textarea,.ve-field select{width:100%;padding:8px 10px;border:1px solid var(--line);border-radius:8px;font-size:13px;box-sizing:border-box;font-family:inherit;background:var(--card);color:var(--text)}
.ve-field textarea{resize:vertical;min-height:60px}
.ve-link select{margin-bottom:6px;font-weight:600}
.ve-link__pick select{margin-top:2px;font-weight:400}
/* 轮播图片编辑器 */
.ve-imgs{display:flex;flex-direction:column;gap:8px;margin-bottom:8px}
.ve-img{position:relative;border:1px solid var(--line);border-radius:8px;overflow:hidden;background:var(--card)}
.ve-img img{width:100%;height:92px;object-fit:cover;display:block;background:#eef1f6}
.ve-img__ops{position:absolute;right:6px;bottom:6px;display:flex;gap:4px}
.ve-img__ops button{width:24px;height:24px;border:none;border-radius:6px;background:rgba(15,23,42,.72);color:#fff;font-size:13px;cursor:pointer;line-height:1}
.ve-img__ops button:hover{background:var(--primary)}
.ve-img__ops button[data-img-del]:hover{background:#ef4444}
.ve-img__add{display:flex;gap:6px;flex-wrap:wrap}
.ve-img__add select{flex:1.4;min-width:120px}
.ve-img__add input{flex:1.6;min-width:120px}
.ve-note{font-size:12px;color:var(--muted);line-height:1.6;background:var(--card);border:1px dashed var(--line);border-radius:8px;padding:10px}
.ve-prop__group{font-size:12px;color:var(--primary);font-weight:700;margin:14px 0 6px;padding-top:8px;border-top:1px dashed var(--line)}
.ve-savebar{position:sticky;bottom:0;margin-top:14px;padding:12px 0;text-align:right;border-top:1px solid var(--line);background:linear-gradient(180deg,transparent,#fff 40%)}
/* 开发文档 */
.doc-box{position:relative;background:#0f172a;border-radius:14px;padding:18px}
.doc-box pre{margin:0;color:var(--line);font-size:13px;line-height:1.7;white-space:pre-wrap;word-break:break-word;max-height:520px;overflow:auto}
.doc-actions{display:flex;justify-content:flex-end;gap:10px;margin-bottom:12px}
</style>
</head>
<body>
<div class="layout">
  <?php require __DIR__ . '/sidebar.php'; ?>
  <div class="main">
    <div class="topbar"><h1>模板中心</h1><div class="right"><span><?= e($admin_name) ?></span><form method="post" action="admin.php?m=logout" style="display:inline"><?= csrf_field() ?><button type="submit" class="logout-link">退出</button></form></div></div>
    <?php if (!empty($flash)): ?><div class="msg"><?= e($flash) ?></div><?php endif; ?>
    <?php if (!empty($err)): ?><div class="err"><?= e($err) ?></div><?php endif; ?>

    <?php $activeTab = $_GET['tab'] ?? 'tpls'; ?>
    <nav class="mc-tab">
      <a href="admin.php?m=tpls&tab=tpls" class="<?= $activeTab==='tpls'?'active':'' ?>">选择模板</a>
      <a href="admin.php?m=tpls&tab=diy" class="<?= $activeTab==='diy'?'active':'' ?>">首页 DIY</a>
      <a href="admin.php?m=tpls&tab=theme" class="<?= $activeTab==='theme'?'active':'' ?>">主题设置</a>
      <a href="admin.php?m=tpls&tab=doc" class="<?= $activeTab==='doc'?'active':'' ?>">开发文档</a>
    </nav>

    <?php if ($activeTab === 'tpls'): ?>
    <!-- ===== 选择模板 ===== -->
    <div class="panel">
      <h2>选择模板</h2>
      <p style="color:var(--muted);font-size:13px;margin-bottom:16px">点击“启用”即可切换前台视觉风格。系统默认不加载额外模板，使用“主题配色”设置。</p>
      <div class="tpl-gallery">
        <!-- 系统默认 -->
        <div class="tpl-card <?= $active === '' ? 'active' : '' ?>">
          <?php if ($active === ''): ?><span class="tag">使用中</span><?php endif; ?>
          <h3>系统默认</h3>
          <p>使用主题配色设置，不加载行业模板</p>
          <div class="actions">
            <?php if ($active !== ''): ?>
            <form method="post" action="admin.php?m=tpl_activate">
              <input type="hidden" name="name" value="">
              <button class="btn btn-p btn-s" type="submit">启用</button>
            <?= csrf_field() ?>
</form>
            <?php else: ?>
            <span class="btn btn-s" style="opacity:.6;cursor:default">当前使用中</span>
            <?php endif; ?>
          </div>
        </div>

        <?php
        $tplNames = $tplNames ?? [];
        $colorDots = [
          'catering'    => ['#ff7a45','#ff4d4f','#ffc53d'],
          'snack'       => ['#f6c600','#ff7a45','#52c41a'],
          'hometextile' => ['#8ab4f8','#c58af9','#f4b8a7'],
          'family'      => ['#36cfc9','#597ef7','#ffec3d'],
        ];
        foreach ($tpls as $t): $isAct = $active === $t['name']; $displayName = $tplNames[$t['name']] ?? $t['name']; ?>
        <div class="tpl-card <?= $isAct ? 'active' : '' ?>">
          <?php if ($isAct): ?><span class="tag">使用中</span><?php endif; ?>
          <span class="tag <?= $t['type'] === 'builtin' ? 'builtin' : '' ?>"><?= $t['type'] === 'builtin' ? '内置' : '站点' ?></span>
          <h3><?= e($displayName) ?></h3>
          <?php if (!empty($colorDots[$t['name']])): ?>
          <div class="colorbar">
            <?php foreach ($colorDots[$t['name']] as $dot): ?><span style="background:<?= e($dot) ?>"></span><?php endforeach; ?>
          </div>
          <?php endif; ?>
          <p>
            <?= $t['hasCss'] ? '✓ style.css' : '✗ 缺 style.css' ?> · <?= $t['hasJs'] ? '✓ main.js' : '—' ?><br>
            文件 <?= (int)$t['files'] ?> 个 · <?= e($t['time']) ?>
          </p>
          <div class="actions">
            <?php if (!$isAct): ?>
            <form method="post" action="admin.php?m=tpl_activate">
              <input type="hidden" name="name" value="<?= e($t['name']) ?>">
              <button class="btn btn-p btn-s" type="submit">启用</button>
            <?= csrf_field() ?>
</form>
            <?php else: ?>
            <span class="btn btn-s" style="opacity:.6;cursor:default">当前使用中</span>
            <?php endif; ?>
            <?php if ($t['type'] === 'builtin' && $t['hasCss']): ?>
            <a class="btn btn-s" href="tpl_preview.php?tpl=<?= e($t['name']) ?>" target="_blank">预览</a>
            <?php elseif ($t['hasCss']): ?>
            <a class="btn btn-s" href="tpls/site_<?= (int)current_site_id() ?>/<?= e($t['name']) ?>/style.css" target="_blank">预览CSS</a>
            <?php endif; ?>
            <?php if ($t['type'] === 'site' && is_admin()): ?>
            <form method="post" action="admin.php?m=tpl_del" onsubmit="return confirm('删除站点模板？')">
              <input type="hidden" name="name" value="<?= e($t['name']) ?>">
              <button class="btn btn-s btn-d" type="submit">删除</button>
            <?= csrf_field() ?>
</form>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <?php if (is_admin()): ?>
    <div class="panel">
      <h2>导入模板（超管）</h2>
      <p style="color:var(--muted);font-size:13px;margin-bottom:12px">仅平台管理员可上传模板。zip 内只能包含 css/js/图片/字体等静态资源，禁止 .php。</p>
      <form method="post" action="admin.php?m=tpl_upload" enctype="multipart/form-data">
        <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
          <input type="file" name="tplzip" accept=".zip" required>
          <button class="btn btn-p" type="submit">上传导入</button>
        </div>
      <?= csrf_field() ?>
</form>
    </div>
    <?php endif; ?>

    <?php elseif ($activeTab === 'diy'): ?>
    <!-- ===== 首页可视化编辑器 ===== -->
    <?php
    // Base registry of all supported sections.
    $baseAllowedSections = [
      'hero'         => '首屏 Hero',
      'scenario'     => '电商孵化',
      'stats'        => '数据统计',
      'capabilities' => '核心能力',
      'about'        => '关于我们 / AI员工',
      'products'     => '产品精选',
      'workflow'     => '服务流程',
      'news'         => '新闻动态',
      'cta'          => '底部 CTA',
      'contact'      => '联系我们',
      'ticker'       => '滚动字幕',
      'board'        => '布料拼贴 Hero',
      'collections'  => '空间系列',
      'story'        => '材质故事',
      'timeline'     => '制造流程(竖排)',
      'quote'        => '工艺引语',
    ];
    $baseDiyIcons = [
      'hero'=>'🚀','scenario'=>'🍄','stats'=>'📊','capabilities'=>'⚡','about'=>'🤖','products'=>'🛍️',
      'workflow'=>'🔄','news'=>'📰','cta'=>'💡','contact'=>'📞','ticker'=>'📜',
      'board'=>'🧵','collections'=>'🛋️','story'=>'🌿','timeline'=>'🪡','quote'=>'💬',
    ];
    $baseDiyDesc = [
      'hero'=>'首屏主视觉与 AI 对话卡片','scenario'=>'电商孵化 · 像素人成长动效','stats'=>'核心数据展示',
      'capabilities'=>'电商孵化一对一解答','about'=>'AI 数字员工介绍','products'=>'产品精选列表',
      'workflow'=>'四步服务流程','news'=>'新闻动态列表','cta'=>'底部行动号召',
      'contact'=>'联系我们 + 右侧悬浮边栏','ticker'=>'滚动字幕条',
      'board'=>'不对称分栏首屏 + 悬浮图卡','collections'=>'空间系列横向滑动','story'=>'材质故事交替分屏',
      'timeline'=>'四道工序竖排时间线','quote'=>'工艺引语大标题',
    ];

    // Load the active template's preset home.json (if any).
    $activeTpl = $active ?? '';
    $tplHomeConfig = null;
    if ($activeTpl !== '') {
      $tplDir = resolve_tpl_dir($activeTpl);
      if ($tplDir && is_file($tplDir . '/home.json')) {
        $tplHomeConfig = json_decode(file_get_contents($tplDir . '/home.json'), true);
        if (!is_array($tplHomeConfig)) { $tplHomeConfig = null; }
      }
    }

    // If a template preset exists, restrict the DIY library to the sections the template uses.
    if (!empty($tplHomeConfig['home_layout']) && is_array($tplHomeConfig['home_layout'])) {
      $allowedSections = [];
      $diyIcons = [];
      $diyDesc = [];
      foreach ($tplHomeConfig['home_layout'] as $item) {
        $k = $item['key'] ?? '';
        if (isset($baseAllowedSections[$k])) {
          $allowedSections[$k] = $baseAllowedSections[$k];
          $diyIcons[$k] = $baseDiyIcons[$k] ?? '📦';
          $diyDesc[$k] = $baseDiyDesc[$k] ?? '';
        }
      }
    } else {
      $allowedSections = $baseAllowedSections;
      $diyIcons = $baseDiyIcons;
      $diyDesc = $baseDiyDesc;
    }

    // Read the latest stored layout / modules directly from DB (bypass settings_all cache).
    $diySettings = [];
    if (!empty($sid)) {
      foreach (DB::all("SELECT `key`,`value` FROM settings WHERE site_id=? AND `key` IN ('home_layout','home_modules')", [$sid]) as $r) {
        $diySettings[$r['key']] = $r['value'];
      }
    }

    $raw = $diySettings['home_layout'] ?? ($data['home_layout'] ?? '');
    $layout = [];
    if ($raw) {
      $decoded = json_decode($raw, true);
      if (is_array($decoded)) {
        foreach ($decoded as $item) {
          $k = $item['key'] ?? '';
          if (isset($allowedSections[$k])) { $layout[] = ['key' => $k, 'show' => !empty($item['show'])]; }
        }
      }
    }
    // Fall back to the template preset layout, then the system default.
    if (!$layout && !empty($tplHomeConfig['home_layout']) && is_array($tplHomeConfig['home_layout'])) {
      foreach ($tplHomeConfig['home_layout'] as $item) {
        $k = $item['key'] ?? '';
        if (isset($allowedSections[$k])) { $layout[] = ['key' => $k, 'show' => !empty($item['show'])]; }
      }
    }
    if (!$layout) {
      foreach (['hero','scenario','stats','capabilities','about','workflow','cta','contact'] as $k) { $layout[] = ['key' => $k, 'show' => true]; }
    }

    $modules = [];
    $modulesRaw = $diySettings['home_modules'] ?? ($data['home_modules'] ?? '');
    if (!empty($modulesRaw)) { $modules = json_decode($modulesRaw, true) ?: []; }
    if (!$modules && !empty($tplHomeConfig['home_modules']) && is_array($tplHomeConfig['home_modules'])) {
      $modules = $tplHomeConfig['home_modules'];
    }

    $veInit = [
      'allowed' => $allowedSections, 'icons' => $diyIcons, 'desc' => $diyDesc,
      'layout' => $layout, 'modules' => (object)$modules, 'links' => $veLinks ?? null,
      'baseAllowed' => $baseAllowedSections, 'baseIcons' => $baseDiyIcons, 'baseDesc' => $baseDiyDesc,
    ];
    ?>
    <div class="ve" id="ve">
      <aside class="ve-lib">
        <div class="ve-lib__head">组件库（拖拽排序）</div>
        <div class="ve-lib__list" id="veLib"></div>
        <div class="ve-lib__add">
          <select id="veAddSel"><option value="">+ 添加组件…</option></select>
          <button class="btn btn-s" type="button" onclick="veAdd()">添加</button>
        </div>
      </aside>
      <main class="ve-stage">
        <div class="ve-toolbar">
          <div class="ve-dev">
            <button type="button" data-dev="pc" class="active" onclick="veDev('pc',this)">PC</button>
            <button type="button" data-dev="mb" onclick="veDev('mb',this)">手机</button>
          </div>
          <span style="font-size:12px;color:var(--muted)">🖱 双击文字直接编辑 · 点按钮配置链接</span>
          <button class="btn btn-s" type="button" onclick="veRefresh()">刷新预览</button>
        </div>
        <div class="ve-frame-wrap pc" id="veFrameWrap">
          <iframe id="veFrame" name="veFrame" src="about:blank"></iframe>
          <form id="vePreviewForm" method="post" action="index.php?act=home&ve=1&preview=1" target="veFrame" style="display:none">
            <input type="hidden" name="preview_lm" id="vePreviewLm">
            <input type="hidden" name="preview_md" id="vePreviewMd">
          </form>
        </div>
      </main>
      <aside class="ve-prop collapsed" id="vePropPanel" hidden>
        <div class="ve-prop__head" id="vePropHead">
          <span class="ve-prop__title">⚙ 高级属性</span>
          <button type="button" class="ve-prop__close" title="关闭" onclick="vePropClose(event)">×</button>
        </div>
        <div class="ve-prop__body" id="vePropBody">
          <div class="ve-note"><b>💡 双击文字可编辑</b> · 点按钮可配文字/链接</div>
        </div>
      </aside>
      <button type="button" class="ve-fab" id="veFab" onclick="vePropToggle()" hidden>⚙ 高级属性</button>
    </div>
    <form id="veForm" method="post" action="admin.php?m=visual_home_save">
      <input type="hidden" name="layout" id="veLayoutInput">
      <input type="hidden" name="modules" id="veModulesInput">
      <div class="ve-savebar">
        <button class="btn btn-p" type="button" onclick="veSave()">保存布局与配置</button>
        <button class="btn btn-s" type="button" onclick="veReset()" style="margin-left:8px">恢复默认</button>
      </div>
    <?= csrf_field() ?>
</form>
    <script>window.VE_INIT = <?= json_encode($veInit, JSON_UNESCAPED_UNICODE) ?>;</script>
<?php elseif ($activeTab === 'theme'): ?>
    <!-- ===== 主题设置 ===== -->
    <?php
    $tab = $_GET['sett'] ?? 'global';
    $from = 'tpls';
    $settingsBaseUrl = 'admin.php?m=tpls&tab=theme&sett=';
    $data = $data ?? settings_all();   // 修复：从 settings 表读取已保存值回显
    require __DIR__ . '/tpl_edit_panels.php';
    ?>

    <?php else: ?>
    <!-- ===== 模板开发文档 ===== -->
    <div class="panel">
      <h2>模板开发文档</h2>
      <p style="color:var(--muted);font-size:13px;margin-bottom:14px">按规范打包即可被系统识别为有效模板，下方文档可一键复制。</p>
      <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-bottom:14px;padding:14px 16px;background:linear-gradient(135deg,rgba(34,211,238,.08),rgba(129,140,248,.08));border:1px dashed rgba(34,211,238,.35);border-radius:12px">
        <div style="flex:1;min-width:220px">
          <div style="font-size:14px;font-weight:700;color:var(--text);margin-bottom:4px">🎁 下载 demo 模板</div>
          <div style="font-size:12.5px;color:var(--muted);line-height:1.6">含 style.css + main.js + home.json + README 完整示例。行业模板 zip 直接后台导入并启用，系统会自动应用其首页布局。</div>
        </div>
        <a class="btn btn-p" href="admin.php?m=tpl_demo_download">🎨 通用 demo（活力橙）</a>
      </div>
      <div class="doc-actions">
        <button type="button" class="btn btn-s" id="copyDocBtn">一键复制下方文档</button>
      </div>
      <div class="doc-box">
<pre id="devDoc">━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 模板打包与开发指南
 适用：deyingding-php 自定义模板导入
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

【一】目录结构（必须严格遵守）
├── style.css         必填；模板全部样式（覆盖默认主题）
├── main.js           可选；模板交互脚本（绑定在 DOMContentLoaded）
├── home.json         可选；模板默认首页布局（含 home_layout + home_modules）
├── images/           可选；图片资源（PNG / JPG / SVG / WEBP）
└── fonts/            可选；自定义字体（woff2 / ttf / otf）

※ 压缩后文件名只能含英文 / 数字 / 下划线 / 中横线，禁止中文 / 空格 / 特殊符号
※ 禁止包含任何 .php / .php3 / .phtml / .htaccess / .htpasswd 等可执行文件
※ 禁止使用 ../ 或 / 开头的路径（防 zip-slip）
※ 缺 style.css 会被直接拒绝导入
※ 若模板含 home.json，后台「启用」该模板时会自动应用其首页布局，无需手动粘贴

压缩示例（推荐用 7-Zip / 系统自带压缩 → .zip 格式）：
  my_template/
    style.css
    main.js
    images/
      hero_bg.jpg
      logo.svg
    fonts/
      brand.woff2

导入路径：后台「模板中心 → 选择模板 → 导入模板（超管）」上传 zip 即可。

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

【二】首页布局约定（16 个区块，顺序/显隐在后台 DIY 调整）

系统支持的区块 key（顺序按用户 DIY 配置）：

  key            默认显示  说明
  ────────────  ────────  ─────────────────────────────────────
  hero           ✔          首屏 Hero（标题/副标题/CTA 按钮/标签）
  scenario       ✔          场景方案（电商孵化长图文 + 像素人动效）
  stats          ✔          数据统计（4 组数字 + 单位 + 说明）
  capabilities   ✔          核心能力（6 个能力卡 + 底部 CTA）
  about          ✔          关于我们（AI 员工长图文）
  products       ✘          产品精选（自动读取 products 表数据）
  workflow       ✔          服务流程（4 步流程图）
  news           ✘          新闻动态（自动读取 articles 表数据）
  cta            ✔          底部 CTA（标题/描述/按钮）
  contact        ✔          联系我们（电话/邮箱/地址/二维码 + 主副按钮）
  ticker         ✘          滚动字幕（可配置条目）
  board          ✘          布料拼贴 Hero（家纺/编辑型杂志风）
  collections    ✘          空间系列横向滑动卡片
  story          ✘          材质故事交替分屏
  timeline       ✘          制造流程竖排时间线
  quote          ✘          工艺引语大标题

※ 模板 style.css 不需要包含布局代码 —— 每个区块的 HTML 在 tpl/home/{key}.php 里
※ 模板只负责「视觉皮肤」：覆盖 .q-hero / .q-section / .q-cards / .q-ticker / .ve-* / .ht-* 等类的样式
※ 区块顺序和显隐完全由「后台 → 首页 DIY」控制，模板不用管
※ 模板可自带 home.json，启用时自动写入默认布局；复杂数组型内容（collections/story/timeline）也在 home.json 中统一配置，启用后自动生效

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

【三】默认模板「系统默认」的风格布局

■ 整体调性：现代深色科技风（暗黑背景 + 青色/紫色/粉色高亮 + 玻璃拟态）

■ 配色：3 色方案（来自「主题设置」）
  --c1  主色（默认 #22d3ee 青）
  --c2  辅色（默认 #818cf8 紫）
  --c3  点缀（默认 #e879f9 粉）

■ 字体：系统默认无衬线（-apple-system / Segoe UI / PingFang SC / Microsoft YaHei）

■ 11 个区块的视觉规范：

  ① 首屏 Hero
    - 全屏高度，黑色渐变背景 + 神经网络 canvas 动效
    - 左侧主标题（大字号 + <em> 渐变色高亮）
    - 右侧悬浮「AI 助手对话卡片」（动态打字效果）
    - 底部 4 个胶囊标签（AIGC 自动成文 / GEO 生成式优化 / RAG 知识库 / 大模型 获客）

  ② 场景方案 scenario
    - 左右两栏布局
    - 左侧：场景介绍 + 多个 pill 标签 + 4 个 ✓ 要点 + 底部 CTA
    - 右侧：像素人吃蘑菇变大动效（mario 风格）

  ③ 数据统计 stats
    - 4 列等宽网格
    - 每个：超大数字（带渐变色）+ 单位 + 两行说明（用 | 换行）

  ④ 核心能力 capabilities
    - 6 个能力卡（3×2 网格）
    - 每张卡片：编号 01~06 + SVG 图标 + 标题 + 描述
    - 底部「报名导师一对一孵化」CTA

  ⑤ 关于我们 about
    - 左右两栏
    - 左侧：标题 + 描述 + ✓ 列表（6 条）+ CTA
    - 右侧：AI Flow 流程图可视化

  ⑥ 产品精选 products（默认关）
    - 顶部「更多」按钮（右上角）
    - 4 列产品卡片网格（封面图 + 标题 + 描述 + 链接到详情）

  ⑦ 服务流程 workflow
    - 4 步流程横向排列
    - 每步：大编号 01~04 + 标题 + 描述

  ⑧ 新闻动态 news（默认关）
    - 顶部「更多」按钮（右上角）
    - 3 列文章卡片网格（封面图 + 标题 + 摘要 + 链接到详情）

  ⑨ 底部 CTA cta
    - 全宽居中卡片
    - 标题 + 描述（支持 HTML）+ 渐变按钮

  ⑩ 联系我们 contact
    - 顶部：标题/描述 + 主/副按钮（可配置：下载/表单提交/普通跳转）
    - 左侧：电话 / 邮箱 / 地址 列表
    - 右侧：微信 + 公众号二维码

  ⑪ 滚动字幕 ticker（默认关）
    - 顶部滚动条
    - 每条：标签 + 圆点

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

【四】可复用的 CSS 类名（直接覆盖即可换肤）

布局类：
  .q-section          通用区块容器（最大宽度 + 上下 padding）
  .q-container        内容居中容器
  .q-head-row         带右上角按钮的标题行
  .q-title            大标题（带渐变色）
  .q-kicker           顶部小标签（彩色英文 / 中文）
  .q-desc             副描述
  .q-btn, .q-btn--grad  渐变按钮（主）
  .q-btn--ghost       幽灵按钮（次要）

区块类：
  .q-hero, .q-hero__title, .q-hero__sub, .q-hero__cta, .q-hero__tags
  .q-scenario, .q-scenario__copy, .q-scenario__pills, .q-scenario__points
  .q-stats, .q-stat, .q-stat__num, .q-stat__label
  .q-feat, .q-feat__idx, .q-feat__ico, .q-feat__cta
  .q-split, .q-split__list
  .q-cards, .q-card, .q-card__thumb
  .q-flow, .q-flow__step, .q-flow__num
  .q-news, .q-news__item, .q-news__thumb
  .q-cta, .q-cta__row
  .q-contact, .q-contact__row, .q-contact__qrs
  .q-ticker, .q-ticker__track

颜色变量（来自主题设置，可在模板内引用）：
  var(--c1)            主色
  var(--c2)            辅色
  var(--c3)            点缀
  var(--surface)       表面色（卡片背景）
  var(--border)        边框色
  var(--border-strong) 强调边框
  var(--text)          主文本色
  var(--muted)         次要文本
  var(--grad-soft)     渐变背景（弱）

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

【五】main.js 写法建议（可选）

(function(){
  // DOMContentLoaded 时绑定交互
  // 例：Hero 标题打字机效果
  var el = document.querySelector('.q-hero__title');
  if (el && el.dataset.typed === undefined) {
    el.dataset.typed = '1';
    var text = el.textContent;
    el.textContent = '';
    var i = 0;
    var t = setInterval(function(){
      el.textContent = text.slice(0, ++i);
      if (i >= text.length) clearInterval(t);
    }, 80);
  }
  // 例：滚动揭示（IntersectionObserver）
  if ('IntersectionObserver' in window) {
    var io = new IntersectionObserver(function(es){
      es.forEach(function(e){
        if (e.isIntersecting) {
          e.target.classList.add('is-visible');
          io.unobserve(e.target);
        }
      });
    }, {threshold:0.15});
    document.querySelectorAll('.q-reveal').forEach(function(el){ io.observe(el); });
  }
})();

※ 严禁在 main.js 里覆盖 window.fetch / XMLHttpRequest / document.cookie 等全局 API
※ 严禁请求外部域名（防 CSRF / XSS）
※ 严禁在 main.js 里给 .ve-* 类（系统后台 DIY 编辑器使用）添加副作用

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

【六】调试技巧

✓ 在「选择模板」点击「预览」可立即查看模板效果
✓ 强制刷新前台页面（Ctrl + F5）避免浏览器缓存旧 CSS
✓ 用浏览器 DevTools → Elements → 搜索 .q-hero / .q-section 看默认 HTML 结构
✓ 想做截图 demo，可临时修改 tpl/home.php 加 echo $modules['hero']['title']

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

【七】一键复刻「系统默认」风格的小贴士

如果想让自己的模板走「系统默认」的现代深色科技风：
  1. 只准备 style.css（不需要任何 HTML / JS 文件）
  2. 在 style.css 里只覆盖配色变量：
       :root { --c1: #00d4ff; --c2: #ff6b9d; --c3: #ffd54f; }
  3. 不动布局类（.q-section / .q-cards / .q-flow 等）
  4. 压缩成 zip 即可导入

这样 5 分钟就能做一个新风格模板，无需写一行 HTML。

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
</pre>
      </div>
    </div>
    <?php endif; ?>

  </div>
</div>

<script>
/* ===== 全国分站重构（2025）DIY 编辑器异常捕获 — 任何抛错都显式呈现，不再静默崩溃 ===== */
window.addEventListener('error', function(ev){
  console.error('[DIY 错误]', ev.message, ev.filename + ':' + ev.lineno);
  var panel = document.getElementById('veLib');
  if (panel) {
    panel.insertAdjacentHTML('afterbegin',
      '<div style="background:#fef2f2;border:1px solid #fca5a5;color:#dc2626;padding:12px 14px;border-radius:8px;font-size:13px;margin-bottom:12px"><b>⚠ DIY 编辑器出错</b><br>' +
      '<code style="font-size:12px;color:#7f1d1d">' + (ev.message || '未知错误') + '</code><br>' +
      '<small style="color:#991b1b">请截图此错误反馈给管理员。可刷新页面重试。</small></div>');
  }
});
(function() {
  var INIT = window.VE_INIT || {allowed:{}, icons:{}, desc:{}, layout:[], modules:{}};
  // 后端数据异常时的多重兜底，确保 DIY 编辑器至少能渲染出来
  if (!INIT.allowed || Object.keys(INIT.allowed).length === 0) {
    INIT.allowed = INIT.baseAllowed || {hero:'首屏 Hero',scenario:'电商孵化',stats:'数据统计',capabilities:'核心能力',about:'关于我们 / AI员工',products:'产品精选',workflow:'服务流程',news:'新闻动态',cta:'底部 CTA',contact:'联系我们',ticker:'滚动字幕',board:'布料拼贴 Hero',collections:'空间系列',story:'材质故事',timeline:'制造流程(竖排)',quote:'工艺引语'};
    INIT.icons = INIT.baseIcons || {hero:'🚀',scenario:'🍄',stats:'📊',capabilities:'⚡',about:'🤖',products:'🛍️',workflow:'🔄',news:'📰',cta:'💡',contact:'📞',ticker:'📜',board:'🧵',collections:'🛋️',story:'🌿',timeline:'🪡',quote:'💬'};
    INIT.desc = INIT.baseDesc || {};
  }
  if (!INIT.layout || !INIT.layout.length) {
    INIT.layout = [{key:'hero',show:1},{key:'scenario',show:1},{key:'stats',show:1},{key:'capabilities',show:1},{key:'about',show:1},{key:'workflow',show:1},{key:'cta',show:1},{key:'contact',show:1}];
  }
  if (!INIT.modules || typeof INIT.modules !== 'object') { INIT.modules = {}; }
  var SCHEMA = {
    hero: { fields:[
      {k:'kicker',label:'顶部小标签',t:'text'},
      {k:'title',label:'主标题（支持 <br> 换行）',t:'textarea'},
      {k:'sub',label:'副标题',t:'textarea'},
      {k:'btn_text',label:'主按钮文案（如「获取报价」）',t:'text'},
      {k:'btn_url',label:'主按钮跳转链接',t:'text'},
      {k:'tags',label:'标签组（英文逗号分隔）',t:'text'},
      {k:'images',label:'轮播图片（可多张，自动轮播）',t:'images'},
      {k:'slider_interval',label:'轮播间隔（秒，默认5）',t:'text'},
    ]},
    stats: { fields:[
      {k:'stat1_num',label:'数字1',t:'text'},{k:'stat1_suffix',label:'单位1',t:'text'},{k:'stat1_label',label:'文案1（用 | 换行）',t:'textarea'},
      {k:'stat2_num',label:'数字2',t:'text'},{k:'stat2_suffix',label:'单位2',t:'text'},{k:'stat2_label',label:'文案2（用 | 换行）',t:'textarea'},
      {k:'stat3_num',label:'数字3',t:'text'},{k:'stat3_suffix',label:'单位3',t:'text'},{k:'stat3_label',label:'文案3（用 | 换行）',t:'textarea'},
      {k:'stat4_num',label:'数字4',t:'text'},{k:'stat4_suffix',label:'单位4',t:'text'},{k:'stat4_label',label:'文案4（用 | 换行）',t:'textarea'},
    ]},
    cta: { fields:[
      {k:'title',label:'标题',t:'text'},
      {k:'sub',label:'描述（支持 HTML）',t:'textarea'},
      {k:'btn_text',label:'按钮文案',t:'text'},
      {k:'btn_url',label:'按钮链接',t:'text'},
    ]},
    scenario: { fields:[
      {k:'kicker',label:'顶部小标签',t:'text'},
      {k:'title',label:'主标题（支持 <br> 换行）',t:'textarea'},
      {k:'desc',label:'副描述',t:'textarea'},
      {k:'cta_text',label:'CTA 按钮文案',t:'text'},
      {k:'cta_url',label:'CTA 按钮链接',t:'text'},
    ]},
    capabilities: { fields:[
      {k:'kicker',label:'顶部小标签',t:'text'},
      {k:'title',label:'主标题',t:'textarea'},
      {k:'desc',label:'副描述',t:'textarea'},
      {k:'cta_text',label:'底部 CTA 按钮文案',t:'text'},
      {k:'cta_url',label:'底部 CTA 按钮链接',t:'text'},
    ]},
    about: { fields:[
      {k:'kicker',label:'顶部小标签',t:'text'},
      {k:'title',label:'主标题',t:'textarea'},
      {k:'desc',label:'副描述',t:'textarea'},
      {k:'cta_text',label:'底部 CTA 按钮文案',t:'text'},
      {k:'cta_url',label:'底部 CTA 按钮链接',t:'text'},
    ]},
    products: { fields:[
      {k:'kicker',label:'顶部小标签',t:'text'},
      {k:'title',label:'主标题',t:'textarea'},
      {k:'desc',label:'副描述',t:'textarea'},
      {k:'more_text',label:'右上角"更多"按钮文案',t:'text'},
    ]},
    workflow: { fields:[
      {k:'kicker',label:'顶部小标签',t:'text'},
      {k:'title',label:'主标题',t:'textarea'},
      {k:'desc',label:'副描述',t:'textarea'},
    ]},
    news: { fields:[
      {k:'kicker',label:'顶部小标签',t:'text'},
      {k:'title',label:'主标题',t:'textarea'},
      {k:'desc',label:'副描述',t:'textarea'},
      {k:'more_text',label:'右上角"全部动态"按钮文案',t:'text'},
    ]},
    ticker: { fields:[
      {k:'items',label:'滚动条目（每行一条）',t:'textarea'},
    ]},
    board: { fields:[
      {k:'kicker',label:'顶部小标签',t:'text'},
      {k:'title',label:'主标题（支持 <br> 换行）',t:'textarea'},
      {k:'sub',label:'副标题',t:'textarea'},
      {k:'btn_text',label:'主按钮文案',t:'text'},
      {k:'btn_url',label:'主按钮链接',t:'text'},
      {k:'badge',label:'悬浮徽章文字',t:'text'},
      {k:'trust',label:'信任徽章（每行 主|副）',t:'textarea'},
      {k:'images',label:'拼贴图卡（取前 3 张，每行一个地址）',t:'images'},
    ]},
    collections: { note:'空间卡片（img/title/sub）在模板启用时由 home.json 自动写入。', fields:[
      {k:'kicker',label:'顶部小标签',t:'text'},
      {k:'title',label:'主标题（支持 <br> 换行）',t:'textarea'},
    ]},
    story: { note:'分屏内容（img/heading/text/stat）在模板启用时由 home.json 自动写入。', fields:[
      {k:'kicker',label:'顶部小标签',t:'text'},
      {k:'title',label:'主标题（支持 <br> 换行）',t:'textarea'},
    ]},
    timeline: { note:'流程条目（num/heading/text）在模板启用时由 home.json 自动写入。', fields:[
      {k:'kicker',label:'顶部小标签',t:'text'},
      {k:'title',label:'主标题（支持 <br> 换行）',t:'textarea'},
    ]},
    quote: { fields:[
      {k:'text',label:'引语正文',t:'textarea'},
      {k:'by',label:'署名',t:'text'},
    ]},
    contact: {
      fields:[
        {k:'kicker',label:'顶部小标签',t:'text'},
        {k:'title',label:'主标题（支持 <br> 换行）',t:'textarea'},
        {k:'desc',label:'副描述',t:'textarea'},
      ],
      buttons:[
        {k:'cta_text',label:'主按钮文案（下载/表单提交按钮）',t:'text'},
        {k:'cta_url',label:'主按钮链接（在下方选择 文章/表单/下载）',t:'link'},
        {k:'sub_text',label:'副按钮文案',t:'text'},
        {k:'sub_url',label:'副按钮链接（在下方选择 文章/表单/下载）',t:'link'},
      ],
      note:'电话、邮箱、二维码请在「主题设置 → 联系我们」中配置；下方「主/副按钮」点链接框可切换 文章 / 表单 / 下载。'
    },
    default: { note:'该模块暂无可配置项，仅支持排序与显隐。' }
  };
  function esc(x){ return String(x==null?'':x).replace(/[&<>"]/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c];}); }
  function schemaOf(k){ return SCHEMA[k] || SCHEMA.default; }
  var lib = document.getElementById('veLib');
  var sel = document.getElementById('veAddSel');
  var propHead = document.getElementById('vePropHead');
  var propBody = document.getElementById('vePropBody');
  var frame = document.getElementById('veFrame');
  var selKey = null;
  var dragEl = null;
  var modules = JSON.parse(JSON.stringify(INIT.modules || {}));
  var layout = (INIT.layout || []).map(function(x){ return {key:x.key, show:x.show?1:0}; });
  // 进入页面时自动选中第一个显示的组件（让用户立刻看到右侧有可编辑字段）
  if (layout.length && layout[0].show) { selKey = layout[0].key; }
  var rt;
  function veRefresh(){
    clearTimeout(rt);
    rt = setTimeout(function(){
      var fm = document.getElementById('vePreviewForm');
      var lm = document.getElementById('vePreviewLm');
      var md = document.getElementById('vePreviewMd');
      if (fm && lm && md) {
        lm.value = JSON.stringify(layout);
        md.value = JSON.stringify(modules);
        fm.submit();
      } else {
        // fallback（旧浏览器或 form 不存在）
        frame.src = 'index.php?act=home&ve=1&preview=1&preview_lm=' + encodeURIComponent(JSON.stringify(layout)) + '&preview_md=' + encodeURIComponent(JSON.stringify(modules)) + '&_t=' + new Date().getTime();
      }
    }, 400);
  }
  // 画布联动：iframe 内编辑文字 / 按钮配置 → 父窗口更新 + 防抖刷新
  window.addEventListener('message', function(ev){
    var d = ev.data;
    if(!d) return;
    if(d.ve === 'update' && d.key && typeof d.field === 'string'){
      modules[d.key] = modules[d.key] || {};
      modules[d.key][d.field] = d.value;
      clearTimeout(window._veUpdRt);
      window._veUpdRt = setTimeout(veRefresh, 250);
      // 同步刷新抽屉内当前组件的对应字段
      var p = document.getElementById('vePropPanel');
      if(selKey === d.key && p && !p.classList.contains('collapsed')){ renderProp(); }
    }
  });
  window.vePropToggle = function(){
    var p = document.getElementById('vePropPanel');
    var fab = document.getElementById('veFab');
    if(!p) return;
    var nowOpen = p.classList.toggle('collapsed') === false;
    if(fab) fab.hidden = nowOpen;
  };
  window.vePropClose = function(ev){
    if(ev) ev.stopPropagation();
    var p = document.getElementById('vePropPanel');
    var fab = document.getElementById('veFab');
    if(p) p.classList.add('collapsed');
    if(fab) fab.hidden = false;
  };
  function renderLib(){
    lib.innerHTML = '';
    layout.forEach(function(sec){
      var k = sec.key;
      var it = document.createElement('div');
      it.className = 've-lib__item' + (sec.show?'':' off') + (selKey===k?' sel':'');
      it.draggable = true; it.dataset.key = k;
      it.innerHTML = '<span class="ic">'+(INIT.icons[k]||'📦')+'</span>'
        + '<span class="nm">'+esc(INIT.allowed[k]||k)+'<small>'+(INIT.desc[k]||'')+'</small></span>'
        + '<span class="eye" title="显示/隐藏">'+(sec.show?'👁':'🚫')+'</span>';
      it.addEventListener('click', function(e){ if(e.target.classList.contains('eye')) return; select(k); });
      it.querySelector('.eye').addEventListener('click', function(e){ e.stopPropagation(); sec.show = sec.show?0:1; renderLib(); veRefresh(); });
      it.addEventListener('dragstart', function(){ dragEl = it; it.classList.add('dragging'); });
      it.addEventListener('dragend', function(){
        it.classList.remove('dragging'); dragEl = null;
        var order = [];
        lib.querySelectorAll('.ve-lib__item').forEach(function(node){
          var k = node.dataset.key; if(!k) return;
          var s = layout.find(function(x){ return x.key === k; });
          order.push(s ? {key:k, show:s.show?1:0} : {key:k, show:1});
        });
        layout = order;
        renderLib();
        veRefresh();
      });
      it.addEventListener('dragover', function(e){ e.preventDefault(); if(!dragEl||dragEl===it) return; var r=it.getBoundingClientRect(); if(e.clientY < r.top+r.height/2){ lib.insertBefore(dragEl, it); } else { lib.insertBefore(dragEl, it.nextSibling); } });
      lib.appendChild(it);
    });
    var used = {}; layout.forEach(function(x){ used[x.key]=1; });
    var html = '<option value="">+ 添加组件…</option>';
    Object.keys(INIT.allowed).forEach(function(k){ if(!used[k]) html += '<option value="'+k+'">'+esc(INIT.allowed[k])+'</option>'; });
    sel.innerHTML = html;
  }
  function select(k){
    selKey = k; renderLib(); renderProp();
    // 通知 iframe 滚动到对应组件（PC/移动端都滚）
    var f = document.getElementById('veFrame');
    if(f && f.contentWindow){ f.contentWindow.postMessage({ve:'scroll-to', key:k}, '*'); }
  }
  /* ===== 链接类型选择器（文章 / 产品 / 表单 / 下载 / 自定义） ===== */
  var LINKS = INIT.links || {articles:[], products:[], forms:[], downloads:[]};
  var LK_NAMES = {custom:'自定义链接', article:'文章', product:'产品', form:'表单提交', download:'下载文件'};
  function parseLink(url){
    if(!url) return {type:'custom', id:''};
    var m;
    if((m = url.match(/index\.php\?act=detail&type=article&id=(\d+)/))) return {type:'article', id:m[1]};
    if((m = url.match(/index\.php\?act=detail&type=product&id=(\d+)/))) return {type:'product', id:m[1]};
    if((m = url.match(/index\.php\?act=form&id=(\d+)/))) return {type:'form', id:m[1]};
    if((m = url.match(/index\.php\?act=(?:download|detail&type=download)&id=(\d+)/))) return {type:'download', id:m[1]};
    if(/^(?:uploads\/|[a-z]+:\/\/)/i.test(url) || /\.(zip|rar|7z|pdf|docx?|xlsx?|pptx?|apk|exe)(?:[?#]|$)/i.test(url)) return {type:'download', id:url};
    return {type:'custom', id:url};
  }
  function linkToUrl(type, id){
    id = id || '';
    if(type==='article') return 'index.php?act=detail&type=article&id=' + id;
    if(type==='product') return 'index.php?act=detail&type=product&id=' + id;
    if(type==='form')    return 'index.php?act=form&id=' + id;
    return id; // download / custom 直接存 URL 或路径
  }
  function renderLinkField(f, cfg){
    var val = cfg[f.k] != null ? cfg[f.k] : '';
    var pl = parseLink(val);
    var html = '<div class="ve-field ve-link"><label>' + esc(f.label) + '</label>';
    html += '<select data-lk-type="' + f.k + '">';
    ['custom','article','product','form','download'].forEach(function(t){
      html += '<option value="' + t + '"' + (pl.type===t ? ' selected' : '') + '>' + LK_NAMES[t] + '</option>';
    });
    html += '</select>';
    html += '<div class="ve-link__pick" data-lk-pick="' + f.k + '">';
    if(pl.type==='article' || pl.type==='product'){
      var arr = pl.type==='article' ? LINKS.articles : LINKS.products;
      html += '<select data-lk-id="' + f.k + '"><option value="">选择' + (pl.type==='article'?'文章':'产品') + '…</option>';
      arr.forEach(function(a){ html += '<option value="' + a.id + '"' + (String(a.id)===String(pl.id) ? ' selected' : '') + '>' + esc(a.title) + '</option>'; });
      html += '</select>';
    } else if(pl.type==='form'){
      html += '<select data-lk-id="' + f.k + '"><option value="">选择表单…</option>';
      LINKS.forms.forEach(function(a){ html += '<option value="' + a.id + '"' + (String(a.id)===String(pl.id) ? ' selected' : '') + '>' + esc(a.name) + '</option>'; });
      html += '</select>';
    } else if(pl.type==='download'){
      html += '<input type="text" data-lk-id="' + f.k + '" value="' + esc(pl.id) + '" placeholder="下载文件路径，如 uploads/xx.zip">';
      if(LINKS.downloads.length){
        html += '<select data-lk-file="' + f.k + '"><option value="">— 或从下载专区选 —</option>';
        LINKS.downloads.forEach(function(a){ html += '<option value="' + esc(a.file_url) + '">' + esc(a.title) + '</option>'; });
        html += '</select>';
      }
    } else {
      html += '<input type="text" data-lk-id="' + f.k + '" value="' + esc(pl.id) + '" placeholder="粘贴网址（http:// 开头），或切换到上方类型选内容">';
    }
    html += '</div></div>';
    return html;
  }
  function isLinkField(k){
    return /(?:^|_)(?:url)$/.test(k) || k==='btn_url' || k==='cta_url';
  }
  /* ===== 轮播图片编辑器（缩略图 + 添加/删除/排序） ===== */
  function imgsOf(k){
    modules[selKey] = modules[selKey] || {};
    var v = modules[selKey][k];
    if(Array.isArray(v)) return v;
    if(typeof v === 'string' && v){ return v.split(/[\r\n,]+/).map(function(s){return s.trim();}).filter(Boolean); }
    return [];
  }
  function setImgs(k, arr){
    modules[selKey] = modules[selKey] || {};
    modules[selKey][k] = arr;
  }
  function renderImagesField(f, cfg){
    var arr = imgsOf(f.k);
    var html = '<div class="ve-field"><label>'+esc(f.label)+'</label>';
    html += '<div class="ve-imgs" data-ve-imgs="'+f.k+'">';
    if(!arr.length){ html += '<div class="ve-note" style="margin-bottom:8px">暂无图片，从下方添加（多张自动轮播，第一张为主图）</div>'; }
    arr.forEach(function(url, i){
      html += '<div class="ve-img"><img src="'+esc(url)+'" loading="lazy" onerror="this.style.visibility=\'hidden\'"><div class="ve-img__ops"><button type="button" data-img-up="'+i+'" title="上移">↑</button><button type="button" data-img-down="'+i+'" title="下移">↓</button><button type="button" data-img-del="'+i+'" title="删除">×</button></div></div>';
    });
    html += '</div>';
    // 添加控件
    html += '<div class="ve-img__add">';
    if(LINKS.images && LINKS.images.length){
      html += '<select data-img-add="'+f.k+'"><option value="">— 从图片空间添加 —</option>';
      LINKS.images.forEach(function(im){ html += '<option value="'+esc(im.path)+'">'+esc(im.name)+'</option>'; });
      html += '</select>';
    }
    html += '<input type="text" data-img-url="'+f.k+'" placeholder="或直接粘贴图片地址">';
    html += '<button type="button" class="btn btn-s" data-img-addurl="'+f.k+'">添加</button>';
    html += '</div></div>';
    return html;
  }
  function renderProp(){
    if(!selKey){ propHead.textContent='属性面板'; propBody.innerHTML='<div class="ve-note">点左侧组件或中间预览区的区块即可编辑内容。<br><br>主题色、站点名等页面级设置请在「主题设置」标签页配置。</div>'; return; }
    var sc = schemaOf(selKey);
    propHead.textContent = '属性 · ' + (INIT.allowed[selKey]||selKey);
    var cfg = modules[selKey] || {}; var html = '';
    if(sc.note) html += '<div class="ve-note">'+esc(sc.note)+'</div>';
    (sc.fields||[]).forEach(function(f){
      var val = cfg[f.k] != null ? cfg[f.k] : '';
      if(f.t === 'images'){ html += renderImagesField(f, cfg); return; }
      if(isLinkField(f.k)){ html += renderLinkField(f, cfg); return; }
      html += '<div class="ve-field"><label>'+esc(f.label)+'</label>';
      if(f.t==='textarea'){ html += '<textarea data-k="'+f.k+'">'+esc(val)+'</textarea>'; }
      else { html += '<input type="text" data-k="'+f.k+'" value="'+esc(val)+'">'; }
      html += '</div>';
    });
    // 按钮组（如 contact 组件）
    if(sc.buttons && sc.buttons.length){
      html += '<div class="ve-prop__group">主按钮</div>';
      sc.buttons.slice(0,3).forEach(function(f){
        var val = cfg[f.k] != null ? cfg[f.k] : '';
        if(isLinkField(f.k)){ html += renderLinkField(f, cfg); return; }
        html += '<div class="ve-field"><label>'+esc(f.label)+'</label>';
        html += '<input type="text" data-k="'+f.k+'" value="'+esc(val)+'">';
        html += '</div>';
      });
      html += '<div class="ve-prop__group">副按钮</div>';
      sc.buttons.slice(3).forEach(function(f){
        var val = cfg[f.k] != null ? cfg[f.k] : '';
        if(isLinkField(f.k)){ html += renderLinkField(f, cfg); return; }
        html += '<div class="ve-field"><label>'+esc(f.label)+'</label>';
        html += '<input type="text" data-k="'+f.k+'" value="'+esc(val)+'">';
        html += '</div>';
      });
    }
    if((sc.fields||[]).length===0 && !sc.buttons && !sc.note){ html += '<div class="ve-note">该模块暂无可配置项，仅支持排序与显隐。</div>'; }
    propBody.innerHTML = html;
    propBody.querySelectorAll('[data-k]').forEach(function(el){
      el.addEventListener('input', function(){ modules[selKey] = modules[selKey] || {}; modules[selKey][el.dataset.k] = el.value; veRefresh(); });
    });
    // 链接类型切换
    propBody.querySelectorAll('[data-lk-type]').forEach(function(sel){
      sel.addEventListener('change', function(){
        var k = sel.dataset.lkType;
        modules[selKey] = modules[selKey] || {};
        var pl = parseLink(modules[selKey][k] || '');
        var keepId = (sel.value === pl.type) ? pl.id : '';
        modules[selKey][k] = linkToUrl(sel.value, keepId);
        renderProp(); veRefresh();
      });
    });
    // 链接 id 选择/输入
    propBody.querySelectorAll('[data-lk-id]').forEach(function(el){
      var ev = el.tagName==='SELECT' ? 'change' : 'input';
      el.addEventListener(ev, function(){
        var k = el.dataset.lkId;
        var typeSel = propBody.querySelector('[data-lk-type="'+k+'"]');
        var type = typeSel ? typeSel.value : 'custom';
        modules[selKey] = modules[selKey] || {};
        modules[selKey][k] = linkToUrl(type, el.value);
        veRefresh();
      });
    });
    // 下载专区快捷选择
    propBody.querySelectorAll('[data-lk-file]').forEach(function(sel){
      sel.addEventListener('change', function(){
        if(!sel.value) return;
        var k = sel.dataset.lkFile;
        modules[selKey] = modules[selKey] || {};
        modules[selKey][k] = sel.value;
        renderProp(); veRefresh();
      });
    });
    // ===== 轮播图片编辑：删除 / 排序 =====
    propBody.querySelectorAll('[data-ve-imgs]').forEach(function(box){
      var k = box.dataset.veImgs;
      box.querySelectorAll('[data-img-del]').forEach(function(btn){
        btn.addEventListener('click', function(){
          var arr = imgsOf(k); arr.splice(+btn.dataset.imgDel, 1); setImgs(k, arr); renderProp(); veRefresh();
        });
      });
      box.querySelectorAll('[data-img-up]').forEach(function(btn){
        btn.addEventListener('click', function(){
          var arr = imgsOf(k), i = +btn.dataset.imgUp;
          if(i > 0){ var t = arr[i-1]; arr[i-1] = arr[i]; arr[i] = t; setImgs(k, arr); renderProp(); veRefresh(); }
        });
      });
      box.querySelectorAll('[data-img-down]').forEach(function(btn){
        btn.addEventListener('click', function(){
          var arr = imgsOf(k), i = +btn.dataset.imgDown;
          if(i < arr.length - 1){ var t = arr[i+1]; arr[i+1] = arr[i]; arr[i] = t; setImgs(k, arr); renderProp(); veRefresh(); }
        });
      });
    });
    // ===== 轮播图片：图片空间添加 =====
    propBody.querySelectorAll('[data-img-add]').forEach(function(sel){
      sel.addEventListener('change', function(){
        if(!sel.value) return;
        var k = sel.dataset.imgAdd;
        var arr = imgsOf(k);
        if(arr.indexOf(sel.value) === -1){ arr.push(sel.value); setImgs(k, arr); }
        sel.value = ''; renderProp(); veRefresh();
      });
    });
    // ===== 轮播图片：粘贴 URL 添加 =====
    propBody.querySelectorAll('[data-img-addurl]').forEach(function(btn){
      btn.addEventListener('click', function(){
        var k = btn.dataset.imgAddurl;
        var inp = propBody.querySelector('[data-img-url="'+k+'"]');
        var v = inp ? inp.value.trim() : '';
        if(!v) return;
        var arr = imgsOf(k);
        if(arr.indexOf(v) === -1){ arr.push(v); setImgs(k, arr); }
        if(inp) inp.value = '';
        renderProp(); veRefresh();
      });
    });
  }
  window.veDev = function(dev, btn){ try{ document.getElementById('veFrameWrap').className='ve-frame-wrap '+dev; document.querySelectorAll('.ve-dev button').forEach(function(b){ b.classList.remove('active'); }); btn.classList.add('active'); if(selKey){ var f = document.getElementById('veFrame'); if(f && f.contentWindow){ setTimeout(function(){ f.contentWindow.postMessage({ve:'scroll-to', key:selKey}, '*'); }, 60); } } }catch(e){ console.error('[veDev]', e); } };
  window.veAdd = function(){ try{ var k=sel.value; if(!k) return; layout.push({key:k,show:1}); modules[k]=modules[k]||{}; selKey=k; renderLib(); renderProp(); veRefresh(); }catch(e){ console.error('[veAdd]', e); } };
  window.veRefresh = veRefresh;
  window.veReset = function(){ try{ if(!confirm('恢复默认布局（hero→contact 共 8 个模块），并清空自定义配置？')) return; layout=['hero','scenario','stats','capabilities','about','workflow','cta','contact'].map(function(k){return {key:k,show:1};}); modules={}; selKey=null; renderLib(); renderProp(); veRefresh(); }catch(e){ console.error('[veReset]', e); } };
  window.veSave = function(){ try{ document.getElementById('veLayoutInput').value=JSON.stringify(layout); document.getElementById('veModulesInput').value=JSON.stringify(modules); document.getElementById('veForm').submit(); }catch(e){ console.error('[veSave]', e); alert('保存失败：' + (e.message||e)); } };
  renderLib();
  veRefresh();
})();


(function() {
  var btn = document.getElementById('copyDocBtn');
  var pre = document.getElementById('devDoc');
  if (!btn || !pre) return;
  btn.addEventListener('click', function() {
    var text = pre.textContent;
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(text).then(function() {
        btn.textContent = '已复制';
        setTimeout(function(){ btn.textContent = '一键复制'; }, 1500);
      });
    } else {
      var ta = document.createElement('textarea');
      ta.value = text;
      document.body.appendChild(ta);
      ta.select();
      document.execCommand('copy');
      document.body.removeChild(ta);
      btn.textContent = '已复制';
      setTimeout(function(){ btn.textContent = '一键复制'; }, 1500);
    }
  });
})();
</script>
</body>
</html>
