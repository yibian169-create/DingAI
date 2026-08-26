<?php
/** 模板编辑表单面板（可被 tpl_edit.php 或模板中心页复用） */
$data = $data ?? [];
$tab = $tab ?? 'global';
$from = $from ?? '';
$settingsBaseUrl = $settingsBaseUrl ?? 'admin.php?m=settings&tab=';
$theme = $data['theme'] ?? 'aurora';
$themes = [
    'aurora' => ['极光青', '#22d3ee', '#818cf8', '#e879f9'],
    'tech'   => ['科技紫', '#a855f7', '#6366f1', '#ec4899'],
    'jade'   => ['翡翠绿', '#10b981', '#22d3ee', '#84cc16'],
    'solar'  => ['活力橙', '#f97316', '#f59e0b', '#ef4444'],
    'custom' => ['自定义', null, null, null],
];
?>
<nav class="tpl-tab">
  <a href="<?= e($settingsBaseUrl) ?>global" class="<?= $tab==='global'?'active':'' ?>">全局</a>
  <a href="<?= e($settingsBaseUrl) ?>home"   class="<?= $tab==='home'?'active':'' ?>">首页</a>
  <a href="<?= e($settingsBaseUrl) ?>lang"   class="<?= $tab==='lang'?'active':'' ?>">语言项</a>
  <a href="<?= e($settingsBaseUrl) ?>other"  class="<?= $tab==='other'?'active':'' ?>">其它</a>
</nav>

<form method="post" action="admin.php?m=settings_save" id="tplForm">
  <input type="hidden" name="tab" value="<?= e($tab) ?>">
  <?php if ($from !== ''): ?><input type="hidden" name="from" value="<?= e($from) ?>"><?php endif; ?>

  <?php if ($tab === 'global'): ?>
  <!-- ===== 全局 ===== -->
  <div class="tpl-panel">
    <h3>联系方式</h3>
    <p class="desc">站点的对外联系方式，显示在前台页脚与导航头部</p>
    <div class="fg">
      <div class="field"><label>站点名称</label><input type="text" name="site_name" value="<?= e($data['site_name'] ?? '') ?>"></div>
      <div class="field"><label>联系电话</label><input type="text" name="phone" value="<?= e($data['phone'] ?? '') ?>"></div>
      <div class="field"><label>联系邮箱</label><input type="text" name="email" value="<?= e($data['email'] ?? '') ?>"></div>
      <div class="field full"><label>公司地址</label><input type="text" name="address" value="<?= e($data['address'] ?? '') ?>"></div>
      <div class="field full"><label>页脚简介</label><input type="text" name="footer_text" value="<?= e($data['footer_text'] ?? '') ?>"></div>
    </div>
  </div>

  <div class="tpl-panel">
    <h3>技术支持</h3>
    <p class="desc">页脚右侧展示技术服务商信息（可留空不显示）</p>
    <div class="fg">
      <div class="field"><label>技术支持名称</label><input type="text" name="techsupport_text" value="<?= e($data['techsupport_text'] ?? '') ?>" placeholder="如：QQ: 18732237111"></div>
      <div class="field"><label>技术支持链接</label><input type="text" name="techsupport_url" value="<?= e($data['techsupport_url'] ?? '') ?>" placeholder="https://wpa.qq.com/msgrd?v=3&uin=18732237111"></div>
    </div>
  </div>

  <div class="tpl-panel">
    <h3>联系我们（首页板块 + 悬浮边栏）</h3>
    <p class="desc">配置首页「联系我们」板块与右侧悬浮边栏的电话和二维码；留空则使用上方全局联系方式或显示占位</p>
    <div class="fg">
      <div class="field"><label>服务电话</label><input type="text" name="contact_phone" value="<?= e($data['contact_phone'] ?? '') ?>" placeholder="留空使用全局联系电话"></div>
      <div class="field"><label>导师 / 第二电话</label><input type="text" name="contact_phone2" value="<?= e($data['contact_phone2'] ?? '') ?>" placeholder="如：138-0000-8888"></div>
      <div class="field full imgpick-field">
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
      <div class="field full imgpick-field">
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
    </div>
  </div>

  <div class="tpl-panel">
    <h3>SEO 优化（全站）</h3>
    <p class="desc">全站默认的搜索引擎优化信息，栏目/内容可单独覆盖</p>
    <div class="fg">
      <div class="field full"><label>SEO 关键词</label><input type="text" name="seo_keywords" value="<?= e($data['seo_keywords'] ?? '') ?>"></div>
      <div class="field full"><label>SEO 描述</label><input type="text" name="seo_description" value="<?= e($data['seo_description'] ?? '') ?>"></div>
    </div>
  </div>

  <?php elseif ($tab === 'home'): ?>
  <!-- ===== 首页 ===== -->
  <div class="tpl-panel">
    <h3>模板配色</h3>
    <p class="desc">选择站点主题色系，全站按钮、渐变、强调色将跟随；选「自定义」可配置三主色</p>
    <div class="theme-grid">
      <?php foreach ($themes as $key => $info): ?>
      <label class="theme-card <?= $theme === $key ? 'checked' : '' ?>" onclick="selectTheme('<?= $key ?>')">
        <input type="radio" name="theme" value="<?= $key ?>" <?= $theme === $key ? 'checked' : '' ?> data-preset="<?= $key ?>">
        <span class="dot">
          <?php if ($key !== 'custom'): ?>
          <i style="background:<?= $info[1] ?>"></i>
          <i style="background:<?= $info[2] ?>"></i>
          <i style="background:<?= $info[3] ?>"></i>
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
    <div class="theme-custom" id="customRow" style="<?php echo $theme === 'custom' ? '' : 'display:none'; ?>">
      <label>主色 <input type="color" name="custom_c1" value="<?= e($data['custom_c1'] ?? '#22d3ee') ?>"></label>
      <label>辅色 <input type="color" name="custom_c2" value="<?= e($data['custom_c2'] ?? '#818cf8') ?>"></label>
      <label>点缀 <input type="color" name="custom_c3" value="<?= e($data['custom_c3'] ?? '#e879f9') ?>"></label>
    </div>
  </div>

  <div class="tpl-panel">
    <h3>Hero 首屏</h3>
    <p class="desc">首页首屏标题、副标题；渐变词会跟随主题色</p>
    <div class="fg">
      <div class="field"><label>主标题渐变词</label><input type="text" name="hero_title" value="<?= e($data['hero_title'] ?? '') ?>"></div>
      <div class="field full"><label>副标题</label><textarea name="hero_sub" rows="2"><?= e($data['hero_sub'] ?? '') ?></textarea></div>
    </div>
  </div>

  <div class="tpl-panel">
    <h3>关于我们</h3>
    <p class="desc">关于板块的大段文案</p>
    <div class="fg">
      <div class="field full"><label>关于文案</label><textarea name="about_text" rows="3"><?= e($data['about_text'] ?? '') ?></textarea></div>
    </div>
  </div>

  <div class="tpl-panel">
    <h3>数据统计（4 组数字 + 标签）</h3>
    <p class="desc">首页中部"数字滚动"模块。后缀固定（+ / 万+ / 万+ / %），后台只填数字</p>
    <div class="fg">
      <?php for ($i = 1; $i <= 4; $i++): $suf = ['','万+','万+','%'][$i-1] ?? '+'; ?>
      <div class="field"><label>数字 <?= $i ?><?= $suf ? '（后缀 ' . $suf . '）' : '' ?></label><input type="text" name="stat<?= $i ?>" value="<?= e($data['stat' . $i] ?? '') ?>"></div>
      <div class="field"><label>标签 <?= $i ?></label><input type="text" name="stat<?= $i ?>_label" value="<?= e($data['stat' . $i . '_label'] ?? '') ?>"></div>
      <?php endfor; ?>
    </div>
  </div>

  <?php elseif ($tab === 'lang'): ?>
  <!-- ===== 语言项 ===== -->
  <div class="tpl-panel">
    <h3>常用按钮文案</h3>
    <p class="desc">前台常用按钮文字，可在后台统一修改</p>
    <div class="fg">
      <div class="field"><label>返回首页</label><input type="text" name="lang_home" value="<?= e($data['lang_home'] ?? '返回首页') ?>"></div>
      <div class="field"><label>查看更多</label><input type="text" name="lang_more" value="<?= e($data['lang_more'] ?? '查看更多') ?>"></div>
      <div class="field"><label>联系我们</label><input type="text" name="lang_contact" value="<?= e($data['lang_contact'] ?? '联系我们') ?>"></div>
      <div class="field"><label>立即咨询</label><input type="text" name="lang_consult" value="<?= e($data['lang_consult'] ?? '立即咨询') ?>"></div>
      <div class="field"><label>了解详情</label><input type="text" name="lang_read_more" value="<?= e($data['lang_read_more'] ?? '了解详情') ?>"></div>
      <div class="field"><label>暂无内容提示</label><input type="text" name="lang_empty" value="<?= e($data['lang_empty'] ?? '暂无内容，敬请期待') ?>"></div>
    </div>
  </div>

  <?php else: ?>
  <!-- ===== 其它 ===== -->
  <div class="tpl-panel">
    <h3>备案与版权</h3>
    <p class="desc">底部显示的备案号与版权年份（可不填）</p>
    <div class="fg">
      <div class="field"><label>ICP 备案号</label><input type="text" name="beian" value="<?= e($data['beian'] ?? '') ?>" placeholder="如：冀ICP备xxxxxxxx号"></div>
      <div class="field"><label>版权起始年份</label><input type="text" name="copyright_year" value="<?= e($data['copyright_year'] ?? date('Y')) ?>"></div>
    </div>
  </div>

  <div class="tpl-panel">
    <h3>后台安全</h3>
    <p class="desc">登录页滑动验证（防暴力破解；需要频繁测试时可关闭）</p>
    <label class="switch"><input type="checkbox" name="login_captcha" value="1" <?= ($data['login_captcha'] ?? '1') === '1' ? 'checked' : '' ?>> 启用后台登录滑动验证</label>
  </div>

  <div class="tpl-panel">
    <h3>全国分站开关</h3>
    <p class="desc">开启后前台自动启用分站 SEO（标题/关键词按城市替换）。详细城市管理请到「全国分站」菜单</p>
    <p><a href="admin.php?m=citysites" class="btn btn-p" style="display:inline-block;text-decoration:none">前往全国分站管理 →</a></p>
  </div>
  <?php endif; ?>

  <div class="tpl-save-bar">
    <button type="submit" class="btn btn-p" style="padding:12px 36px;font-size:14.5px">保存本页配置</button>
  </div>
  <?= csrf_field() ?>
</form>

<script>
function selectTheme(key) {
  var cards = document.querySelectorAll('.theme-card');
  cards.forEach(function (c) { c.classList.toggle('checked', c.querySelector('input').value === key); });
  document.getElementById('customRow').style.display = (key === 'custom') ? '' : 'none';
}
</script>

<!-- 联系我们二维码：上传 + 图片空间选择器 -->
<style>
.imgpick-preview{width:40px;height:40px;border-radius:8px;border:1px solid var(--line);object-fit:cover;display:block;background:var(--toolbar-bg);flex:none}
.imgpick-preview[hidden]{display:none}
.imgpick-field{margin-bottom:4px}
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

<div class="dyimg-modal" id="dyImgPickerModal" style="display:none" onclick="if(event.target===this)dyImgPickerClose()">
  <div class="dyimg-modal__overlay"></div>
  <div class="dyimg-modal__box">
    <div class="dyimg-modal__head">
      <h3>从图片空间选择</h3>
      <button type="button" class="btn btn-s" onclick="dyImgPickerClose()">关闭</button>
    </div>
    <div class="dyimg-modal__body" id="dyImgPickerBody">
      <div class="dyimg-empty">加载中...</div>
    </div>
  </div>
</div>
<div class="dyimg-toast" id="dyImgToast" style="display:none"></div>

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
        if (j.ok) {
          dyImgSet(targetId, j.url);
          dyImgToast('上传成功');
        } else {
          alert(j.msg || '上传失败');
        }
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
