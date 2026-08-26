<?php
/**
 * 后台公共侧栏，$m 为当前模块
 */
$m = $_GET['m'] ?? 'dashboard';
$nav = [
    'dashboard'  => '仪表盘',
    'categories' => '栏目管理',
    'articles'   => '文章管理',
    'products'   => '产品管理',
    'downloads'  => '下载专区',
    'uploads'    => '图片空间',
    'forms'      => '自定义表单',
    'citysites'  => '全国分站',
    'tpls'       => '模板中心',
    'geo'        => '🌐 GEO 中心',
    'api_settings' => '⚙ API 配置',
];
?>
<aside class="sidebar">
  <div class="brand">得应盯 · 官网后台</div>
  <div class="theme-switch" id="themeSwitch" title="切换白天 / 夜晚模式">
    <span id="tsIcon">🌙</span><span id="tsLabel">夜晚模式</span>
  </div>
  <script>
  (function(){
    function sync(){
      var t=document.documentElement.getAttribute('data-theme')==='dark'?'dark':'light';
      var ic=document.getElementById('tsIcon'),lb=document.getElementById('tsLabel');
      if(ic)ic.textContent=t==='dark'?'☀️':'🌙';
      if(lb)lb.textContent=t==='dark'?'白天模式':'夜晚模式';
    }
    sync();
    document.getElementById('themeSwitch').addEventListener('click',function(){
      var cur=document.documentElement.getAttribute('data-theme')==='dark'?'dark':'light';
      var next=cur==='dark'?'light':'dark';
      document.documentElement.setAttribute('data-theme',next);
      try{localStorage.setItem('dy_theme',next);}catch(e){}
      sync();
    });
  })();
  </script>
  <?php foreach ($nav as $k => $v): ?>
    <a href="admin.php?m=<?= $k ?>" class="<?= $m === $k ? 'active' : '' ?>"><?= $v ?></a>
  <?php endforeach; ?>
</aside>
