<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>仪表盘 - 得应盯后台</title>
<link rel="stylesheet" href="static/css/admin.css">
<script>
/* ECharts 多源加载器：本地 -> 国内 staticfile -> jsdelivr -> unpkg，任一成功即可 */
(function(){
  function loadScript(src, onload, onerror){
    var s=document.createElement('script'); s.src=src; s.async=true;
    s.onload=onload; s.onerror=onerror;
    document.head.appendChild(s);
  }
  window.showLoadFail = function(){
    document.querySelectorAll('.dash-chart').forEach(function(el){
      el.innerHTML='<div style="padding:20px;color:var(--muted);text-align:center;line-height:1.8">'+
        '可视化资源加载失败，请检查网络<br><small>建议把 echarts.min.js / china.js 放到 static/vendor/echarts/ 目录后刷新</small>'+
        '</div>';
    });
  };
  window.__loadEcharts = function(cdnIdx, cb){
    var list=[
      'static/vendor/echarts/echarts.min.js',
      'https://cdn.staticfile.org/echarts/5.5.0/echarts.min.js',
      'https://cdn.jsdelivr.net/npm/echarts@5.5.0/dist/echarts.min.js',
      'https://unpkg.com/echarts@5.5.0/dist/echarts.min.js'
    ];
    if(cdnIdx>=list.length){ showLoadFail(); return; }
    loadScript(list[cdnIdx], function(){ cb && cb(); }, function(){ window.__loadEcharts(cdnIdx+1, cb); });
  };
  window.__loadChinaJs = function(cdnIdx, cb){
    var list=[
      'static/vendor/echarts/china.js',
      'https://cdn.staticfile.org/echarts/5.5.0/map/js/china.js',
      'https://cdn.jsdelivr.net/npm/echarts@5.5.0/map/js/china.js',
      'https://unpkg.com/echarts@5.5.0/map/js/china.js'
    ];
    if(cdnIdx>=list.length){ cb && cb(); return; } // 地图缺失也继续，有回退柱状图
    loadScript(list[cdnIdx], function(){ cb && cb(); }, function(){ window.__loadChinaJs(cdnIdx+1, cb); });
  };
})();
</script>
<style>
.dashboard-wrap{max-width:1600px;margin:0 auto}
.sec-title{font-size:16px;font-weight:700;margin:6px 0 14px;color:var(--text);display:flex;align-items:center;gap:8px}
.kpis{display:grid;grid-template-columns:repeat(6,1fr);gap:16px;margin-bottom:20px}
.kpi{background:var(--card);border:1px solid var(--line);border-radius:var(--radius);padding:18px;position:relative;overflow:hidden;box-shadow:var(--shadow);transition:transform .2s,box-shadow .2s}
.kpi:hover{transform:translateY(-3px);box-shadow:var(--shadow-lg)}
.kpi::after{content:"";position:absolute;top:0;right:0;width:70px;height:70px;background:linear-gradient(135deg,transparent 50%,rgba(79,70,229,.06) 50%);border-bottom-left-radius:70px}
.kpi .label{font-size:12.5px;color:var(--muted);margin-bottom:6px}
.kpi .num{font-size:26px;font-weight:800;background:linear-gradient(90deg,var(--primary),var(--primary-2));-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent}
.kpi .trend{font-size:12px;color:var(--ok);margin-top:4px;display:flex;align-items:center;gap:4px}
.kpi .trend.down{color:var(--danger)}
.overview{display:grid;grid-template-columns:repeat(5,1fr);gap:16px;margin-bottom:22px}
.ov{background:var(--card);border:1px solid var(--line);border-radius:var(--radius);padding:18px;box-shadow:var(--shadow);position:relative;overflow:hidden;transition:transform .2s,box-shadow .2s}
.ov:hover{transform:translateY(-3px);box-shadow:var(--shadow-lg)}
.ov::after{content:"";position:absolute;top:0;right:0;width:60px;height:60px;background:linear-gradient(135deg,transparent 50%,rgba(79,70,229,.06) 50%);border-bottom-left-radius:60px}
.ov .label{font-size:12.5px;color:var(--muted);margin-bottom:6px}
.ov .num{font-size:26px;font-weight:800;background:linear-gradient(90deg,var(--primary),var(--primary-2));-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent}
.ov .more{margin-top:6px;font-size:12px}
.ov .more a{color:var(--primary);text-decoration:none}
.ov .more a:hover{text-decoration:underline}
@media (max-width:1200px){.overview{grid-template-columns:repeat(3,1fr)}}
@media (max-width:640px){.overview{grid-template-columns:repeat(2,1fr)}}
.dash-grid{display:grid;grid-template-columns:repeat(12,1fr);gap:16px}
.dash-panel{background:var(--card);border:1px solid var(--line);border-radius:var(--radius);padding:18px;box-shadow:var(--shadow)}
.dash-panel h3{font-size:14px;font-weight:700;margin-bottom:14px;display:flex;align-items:center;gap:8px}
.dash-panel h3::before{content:"";width:4px;height:14px;border-radius:3px;background:linear-gradient(180deg,var(--primary),var(--primary-2))}
.dash-chart{height:260px}
.dash-chart.tall{height:420px}
.growth-kpis{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:16px}
.gkpi{background:var(--card);border:1px solid var(--line);border-radius:var(--radius);padding:16px;box-shadow:var(--shadow);position:relative;overflow:hidden}
.gkpi .label{font-size:12.5px;color:var(--muted);margin-bottom:6px}
.gkpi .num{font-size:24px;font-weight:800;background:linear-gradient(90deg,var(--primary),var(--primary-2));-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent}
.gkpi .sub{font-size:12px;margin-top:4px}
.gkpi .sub.up{color:var(--ok)}
.gkpi .sub.down{color:var(--danger)}
.art-rank{list-style:none;margin:0;padding:0}
.art-rank li{display:flex;align-items:center;gap:12px;padding:11px 0;border-bottom:1px solid var(--line);font-size:13px}
.art-rank li:last-child{border-bottom:none}
.art-rank .idx{width:22px;height:22px;border-radius:6px;background:var(--bg);color:var(--muted);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:12px;flex-shrink:0}
.art-rank li:nth-child(1) .idx{background:linear-gradient(135deg,#f59e0b,#fbbf24);color:#fff}
.art-rank li:nth-child(2) .idx{background:linear-gradient(135deg,#94a3b8,#cbd5e1);color:#fff}
.art-rank li:nth-child(3) .idx{background:linear-gradient(135deg,#b45309,#d97706);color:#fff}
.art-rank .info{flex:1;min-width:0}
.art-rank .t{color:var(--text);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-weight:600}
.art-rank .c{color:var(--muted);font-size:11.5px;margin-top:2px}
.art-rank .bar{height:5px;border-radius:3px;background:var(--bg);margin-top:5px;overflow:hidden}
.art-rank .bar>i{display:block;height:100%;border-radius:3px;background:linear-gradient(90deg,var(--primary),var(--primary-2))}
.art-rank .v{color:var(--primary);font-weight:700;font-size:13px;flex-shrink:0;min-width:54px;text-align:right}
/* 产品热度榜：缩略图 + 价格徽章 + 热度条 */
.pro-rank{list-style:none;margin:0;padding:0}
.pro-rank li{display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid var(--line);font-size:13px}
.pro-rank li:last-child{border-bottom:none}
.pro-rank .idx{width:22px;height:22px;border-radius:6px;background:var(--bg);color:var(--muted);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:12px;flex-shrink:0}
.pro-rank li:nth-child(1) .idx{background:linear-gradient(135deg,#f59e0b,#fbbf24);color:#fff}
.pro-rank li:nth-child(2) .idx{background:linear-gradient(135deg,#94a3b8,#cbd5e1);color:#fff}
.pro-rank li:nth-child(3) .idx{background:linear-gradient(135deg,#b45309,#d97706);color:#fff}
.pro-rank .thumb{width:42px;height:42px;border-radius:8px;background:var(--bg);object-fit:cover;flex-shrink:0;border:1px solid var(--line)}
.pro-rank .thumb.ph{display:flex;align-items:center;justify-content:center;color:var(--muted);font-size:18px}
.pro-rank .info{flex:1;min-width:0}
.pro-rank .t{color:var(--text);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-weight:600}
.pro-rank .c{color:var(--muted);font-size:11.5px;margin-top:2px;display:flex;align-items:center;gap:8px}
.pro-rank .price{color:var(--primary-2);font-weight:700;font-size:11.5px}
.pro-rank .bar{height:5px;border-radius:3px;background:var(--bg);margin-top:5px;overflow:hidden}
.pro-rank .bar>i{display:block;height:100%;border-radius:3px;background:linear-gradient(90deg,#f472b6,#fbbf24)}
.pro-rank .v{color:#f472b6;font-weight:700;font-size:13px;flex-shrink:0;min-width:54px;text-align:right}
@media (max-width:1200px){.growth-kpis{grid-template-columns:repeat(2,1fr)}}
.dash-col-4{grid-column:span 4}
.dash-col-5{grid-column:span 5}
.dash-col-7{grid-column:span 7}
.dash-col-6{grid-column:span 6}
.dash-col-3{grid-column:span 3}
.dash-col-12{grid-column:span 12}
.mock-tag{display:inline-block;font-size:11px;padding:2px 8px;border-radius:20px;background:rgba(79,70,229,.1);color:var(--primary);margin-left:8px;font-weight:600}
.live-list{max-height:260px;overflow-y:auto}
.live-item{display:flex;align-items:center;gap:10px;padding:9px 0;border-bottom:1px solid var(--line);font-size:12.5px}
.live-item:last-child{border-bottom:none}
.live-item .dot{width:7px;height:7px;border-radius:50%;background:var(--primary);animation:pulse 1.4s infinite}
.live-item .ip{font-family:ui-monospace,SFMono-Regular,Menlo,monospace;color:var(--primary);font-weight:600;min-width:100px}
.live-item .loc{color:var(--text);flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.live-item .time{color:var(--muted);white-space:nowrap}
.live-item .device{padding:2px 8px;border-radius:999px;background:var(--bg);color:var(--muted);font-size:11px}
@keyframes pulse{0%{opacity:1;transform:scale(1)}50%{opacity:.5;transform:scale(.85)}100%{opacity:1;transform:scale(1)}}
@media (max-width:1200px){.kpis{grid-template-columns:repeat(3,1fr)}.dash-col-4,.dash-col-5,.dash-col-7,.dash-col-6,.dash-col-3{grid-column:span 12}}
@media (max-width:640px){.kpis{grid-template-columns:repeat(2,1fr)}}
</style>
<script>
(function(){try{var t=localStorage.getItem('dy_theme')||'light';document.documentElement.setAttribute('data-theme',t);}catch(e){}})();
</script>
</head>
<body>
<div class="layout">
  <?php require __DIR__ . '/sidebar.php'; ?>
  <div class="main">
    <div class="dashboard-wrap">
      <div class="topbar">
        <h1>仪表盘 <?php if (empty($has_real)): ?><span class="mock-tag" style="background:rgba(245,158,11,.12);color:#f59e0b">暂无访问数据</span><?php endif; ?></h1>
        <?php
          $self = $_SERVER['SCRIPT_NAME'] ?? '/admin.php';
          $dir  = str_replace('\\', '/', dirname($self));
          $home = $dir === '/' || $dir === '\\' ? '/index.php' : $dir . '/index.php';
        ?>
        <div class="right">
          <a class="tb-link" href="<?= e($home) ?>" target="_blank" title="新窗口打开前台首页">网站首页</a>
          <form method="post" action="admin.php?m=clear_cache" style="display:inline">
            <button class="tb-btn" type="submit" onclick="return confirm('确认清空服务器缓存（OPCache / 临时文件）？')">清空缓存</button>
          <?= csrf_field() ?>
</form>
          <span>欢迎，<?= e($admin_name) ?></span>
          <form method="post" action="admin.php?m=logout" style="display:inline"><?= csrf_field() ?><button type="submit" class="logout-link">退出</button></form>
        </div>
      </div>
      <?php if (!empty($flash)): ?><div class="msg"><?= e($flash) ?></div><?php endif; ?>

      <div class="overview">
        <div class="ov"><div class="label">栏目数</div><div class="num"><?= number_format($data['cat_n'] ?? 0) ?></div><div class="more"><a href="admin.php?m=categories">管理 →</a></div></div>
        <div class="ov"><div class="label">文章数</div><div class="num"><?= number_format($data['art_n'] ?? 0) ?></div><div class="more"><a href="admin.php?m=articles">管理 →</a></div></div>
        <div class="ov"><div class="label">产品数</div><div class="num"><?= number_format($data['pro_n'] ?? 0) ?></div><div class="more"><a href="admin.php?m=products">管理 →</a></div></div>
        <div class="ov"><div class="label">图片素材</div><div class="num"><?= number_format($data['upload_n'] ?? 0) ?></div><div class="more"><a href="admin.php?m=uploads">空间 →</a></div></div>
        <div class="ov"><div class="label">分站数</div><div class="num"><?= number_format($data['city_n'] ?? 0) ?></div><div class="more"><a href="admin.php?m=citysites">分站 →</a></div></div>
      </div>
      <h2 class="sec-title">📊 数据作战大屏</h2>

      <div class="kpis">
        <div class="kpi"><div class="label">今日 PV</div><div class="num"><?= number_format($stats['pv_today']) ?></div><div class="trend"><?= pct_trend($stats['pv_today'], $stats['pv_yesterday']) ?></div></div>
        <div class="kpi"><div class="label">今日 UV</div><div class="num"><?= number_format($stats['uv_today']) ?></div><div class="trend"><?= pct_trend($stats['uv_today'], $stats['uv_yesterday']) ?></div></div>
        <div class="kpi"><div class="label">总访问量</div><div class="num"><?= number_format($stats['total_pv']) ?></div><div class="trend">累计 PV</div></div>
        <div class="kpi"><div class="label">独立访客</div><div class="num"><?= number_format($stats['total_uv']) ?></div><div class="trend">累计 UV</div></div>
        <div class="kpi"><div class="label">移动端占比</div><div class="num"><?= mobile_pct($stats['devices']) ?></div><div class="trend">设备分布</div></div>
        <div class="kpi"><div class="label">表单提交</div><div class="num"><?= number_format(array_sum(array_column($stats['form_cities'] ?? [], 'n'))) ?></div><div class="trend">按地域汇总</div></div>
      </div>

      <h2 class="sec-title">📈 用量增长分析</h2>
      <div class="growth-kpis">
        <div class="gkpi"><div class="label">近 30 天 PV</div><div class="num"><?= number_format($stats['pv_30d']) ?></div><div class="sub up">累计访问量</div></div>
        <div class="gkpi"><div class="label">近 30 天 UV</div><div class="num"><?= number_format($stats['uv_30d']) ?></div><div class="sub up">累计访客</div></div>
        <div class="gkpi"><div class="label">周环比增长</div><div class="num"><?= ($stats['week_growth']>=0?'+':'') . $stats['week_growth'] ?>%</div><div class="sub <?= $stats['week_growth']>=0?'up':'down' ?>"><?= $stats['week_growth']>=0?'▲ 较上周增长':'▼ 较上周下滑' ?></div></div>
        <div class="gkpi"><div class="label">日均访问</div><div class="num"><?= number_format(round($stats['pv_30d']/30)) ?></div><div class="sub up">PV / 天</div></div>
      </div>

      <div class="dash-grid">
        <div class="dash-panel dash-col-12"><h3>近 30 天用量增长趋势（PV 柱 + UV 线，双轴）</h3><div id="growthChart" class="dash-chart tall"></div></div>
        <div class="dash-panel dash-col-7"><h3>全国访问地图 · 城市热力</h3><div id="mapChart" class="dash-chart tall"></div></div>
        <div class="dash-panel dash-col-5"><h3>访问地域 TOP10（气泡）</h3><div id="geoChart" class="dash-chart tall"></div></div>
        <div class="dash-panel dash-col-7"><h3>近 7 天访问趋势（PV / UV）</h3><div id="trendChart" class="dash-chart"></div></div>
        <div class="dash-panel dash-col-5"><h3>设备类型分布</h3><div id="deviceChart" class="dash-chart"></div></div>
        <div class="dash-panel dash-col-6"><h3>表单提交地域</h3><div id="formGeoChart" class="dash-chart"></div></div>
        <div class="dash-panel dash-col-6"><h3>实时访问</h3><div id="liveLog" class="live-list"></div></div>
        <div class="dash-panel dash-col-6"><h3>24 小时访问分布</h3><div id="hourChart" class="dash-chart"></div></div>
        <div class="dash-panel dash-col-6"><h3>来源渠道占比</h3><div id="sourceChart" class="dash-chart"></div></div>
        <div class="dash-panel dash-col-6"><h3>🔥 用户偏好的文章（阅读 Top10）</h3>
          <ul class="art-rank">
          <?php
            $topArts = $stats['top_articles'] ?? [];
            $maxViews = $topArts ? max(array_column($topArts, 'views')) : 1;
            foreach ($topArts as $i => $a):
          ?>
            <li>
              <span class="idx"><?= $i+1 ?></span>
              <div class="info">
                <div class="t" title="<?= e($a['title']) ?>"><?= e(mb_substr($a['title'],0,22)) ?></div>
                <div class="c"><?= e($a['cat_name'] ?? '未分类') ?> · <?= number_format($a['views']) ?> 次阅读</div>
                <div class="bar"><i style="width:<?= round($a['views']/$maxViews*100) ?>%"></i></div>
              </div>
              <span class="v"><?= number_format($a['views']) ?></span>
            </li>
          <?php endforeach; ?>
          <?php if (!$topArts): ?><li style="color:var(--muted);justify-content:center">暂无文章阅读数据</li><?php endif; ?>
          </ul>
        </div>
        <div class="dash-panel dash-col-6"><h3>🛒 用户喜欢的产品（热度 Top10）</h3>
          <ul class="pro-rank">
          <?php
            $topPros = $stats['top_products'] ?? [];
            $maxPros = $topPros ? max(array_column($topPros, 'views')) : 1;
            foreach ($topPros as $i => $p):
              $cover = !empty($p['cover']) ? e($p['cover']) : '';
          ?>
            <li>
              <span class="idx"><?= $i+1 ?></span>
              <?php if ($cover): ?>
                <img class="thumb" src="<?= $cover ?>" alt="" loading="lazy">
              <?php else: ?>
                <span class="thumb ph">🛒</span>
              <?php endif; ?>
              <div class="info">
                <div class="t" title="<?= e($p['title']) ?>"><?= e(mb_substr($p['title'],0,22)) ?></div>
                <div class="c">
                  <span><?= e($p['cat_name'] ?? '未分类') ?></span>
                  <?php if (!empty($p['price'])): ?><span class="price"><?= e($p['price']) ?></span><?php endif; ?>
                  <span><?= number_format($p['views']) ?> 热度</span>
                </div>
                <div class="bar"><i style="width:<?= round($p['views']/$maxPros*100) ?>%"></i></div>
              </div>
              <span class="v"><?= number_format($p['views']) ?></span>
            </li>
          <?php endforeach; ?>
          <?php if (!$topPros): ?><li style="color:var(--muted);justify-content:center">暂无产品浏览数据</li><?php endif; ?>
          </ul>
        </div>
        <div class="dash-panel dash-col-12">
          <h3>使用流程</h3>
          <p style="color:var(--muted);font-size:13.5px;line-height:2">
            1. 栏目管理 → 建父栏目和子栏目（子栏目自动进入前台导航下拉）；<br>
            2. 内容管理 / 产品管理 → 发布内容，勾选「推荐」上首页；<br>
            3. 图片空间 → 先传图片，复制 URL 到内容封面/正文；<br>
            4. 全国分站 → 先打开开关，再添加城市，前台 ?city=城市名 生效；<br>
            5. 模板中心 → 改站名、电话、首页文案、SEO。
          </p>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
var STATS = <?= json_encode($stats, JSON_UNESCAPED_UNICODE) ?>;
<?php
  // 地图散点坐标（PHP 侧构造，避免前端维护坐标）
  $mapPoints = map_visit_points($stats['cities'] ?? []);
  if (empty($mapPoints)) {
      // 没有坐标的城市（如 Demo 中的"河北"省级），尝试用表单地域兜底
      $mapPoints = map_visit_points(array_map(function($f){return ['city'=>$f['city'],'n'=>$f['n']];}, $stats['form_cities'] ?? []));
  }
?>
var MAP_POINTS = <?= json_encode($mapPoints, JSON_UNESCAPED_UNICODE) ?>;
var charts={};
var isDark=document.documentElement.getAttribute('data-theme')==='dark';
var textColor=isDark?'#e6ebf2':'#1e2533';
var mutedColor=isDark?'#8a97a8':'#7a8699';
var lineColor=isDark?'#27313f':'#e3e8f0';
function common(){return {textStyle:{fontFamily:"'Segoe UI','PingFang SC','Microsoft YaHei',sans-serif"},tooltip:{backgroundColor:isDark?'#182230':'#fff',borderColor:lineColor,textStyle:{color:textColor}},legend:{textStyle:{color:mutedColor}},grid:{left:10,right:20,top:40,bottom:20,containLabel:true}};}

function initDashboardCharts(){
  if(typeof echarts==='undefined'){ showLoadFail(); return; }

(function trend(){
  var days=STATS.trend.map(function(i){return i.date;});
  charts.trend=echarts.init(document.getElementById('trendChart'));
  charts.trend.setOption(Object.assign(common(),{
    legend:{data:['PV','UV'],top:0},
    xAxis:{type:'category',data:days,axisLine:{lineStyle:{color:lineColor}},axisLabel:{color:mutedColor}},
    yAxis:{type:'value',splitLine:{lineStyle:{color:lineColor,type:'dashed'}},axisLabel:{color:mutedColor}},
    series:[
      {name:'PV',type:'line',smooth:true,data:STATS.trend.map(function(i){return i.pv;}),itemStyle:{color:'#4f46e5'},areaStyle:{color:new echarts.graphic.LinearGradient(0,0,0,1,[{offset:0,color:isDark?'rgba(124,131,255,.35)':'rgba(79,70,229,.25)'},{offset:1,color:isDark?'rgba(124,131,255,.02)':'rgba(79,70,229,.02)'}])}},
      {name:'UV',type:'line',smooth:true,data:STATS.trend.map(function(i){return i.uv;}),itemStyle:{color:'#22d3ee'}}
    ]
  }));
})();

(function device(){
  var d=STATS.devices||{};
  var data=[{value:d.mobile||0,name:'移动端',itemStyle:{color:'#4f46e5'}},{value:d.desktop||0,name:'桌面端',itemStyle:{color:'#22d3ee'}},{value:d.tablet||0,name:'平板',itemStyle:{color:'#f472b6'}}];
  charts.device=echarts.init(document.getElementById('deviceChart'));
  charts.device.setOption(Object.assign(common(),{legend:{bottom:0},series:[{type:'pie',radius:['42%','68%'],center:['50%','46%'],data:data,label:{color:textColor},itemStyle:{borderRadius:6,borderColor:isDark?'#182230':'#fff',borderWidth:2}}]}));
})();

(function geo(){
  var rows=(STATS.cities||[]).slice().reverse();
  charts.geo=echarts.init(document.getElementById('geoChart'));
  charts.geo.setOption(Object.assign(common(),{
    grid:{left:10,right:20,top:10,bottom:10,containLabel:true},
    xAxis:{type:'value',splitLine:{lineStyle:{color:lineColor,type:'dashed'}},axisLabel:{color:mutedColor}},
    yAxis:{type:'category',data:rows.map(function(r){return r.city;}),axisLine:{lineStyle:{color:lineColor}},axisLabel:{color:mutedColor}},
    series:[{type:'bar',data:rows.map(function(r){return r.n;}),itemStyle:{borderRadius:[0,6,6,0],color:new echarts.graphic.LinearGradient(0,0,1,0,[{offset:0,color:'#4f46e5'},{offset:1,color:'#7c83ff'}])},barWidth:14}]
  }));
})();

(function formGeo(){
  var rows=STATS.form_cities||[];
  var colors=['#4f46e5','#22d3ee','#f472b6','#fbbf24','#94a3b8'];
  charts.formGeo=echarts.init(document.getElementById('formGeoChart'));
  charts.formGeo.setOption(Object.assign(common(),{
    legend:{bottom:0},
    series:[{type:'pie',radius:'68%',center:['50%','46%'],data:rows.map(function(r,i){return {value:r.n,name:r.city,itemStyle:{color:colors[i%colors.length]}};}),label:{color:textColor},itemStyle:{borderRadius:6,borderColor:isDark?'#182230':'#fff',borderWidth:2}}]
  }));
})();

(function hour(){
  var h=[]; for(var i=0;i<24;i++) h.push(i+':00');
  var data=STATS.hours||[];
  charts.hour=echarts.init(document.getElementById('hourChart'));
  charts.hour.setOption(Object.assign(common(),{
    xAxis:{type:'category',data:h,axisLine:{lineStyle:{color:lineColor}},axisLabel:{color:mutedColor,interval:2}},
    yAxis:{type:'value',splitLine:{lineStyle:{color:lineColor,type:'dashed'}},axisLabel:{color:mutedColor}},
    series:[{type:'bar',data:data,itemStyle:{borderRadius:[4,4,0,0],color:new echarts.graphic.LinearGradient(0,0,0,1,[{offset:0,color:'#7c83ff'},{offset:1,color:'#4f46e5'}])},barWidth:'60%'}]
  }));
})();

(function source(){
  var s=STATS.sources||{};
  var data=[{value:s['搜索引擎']||0,name:'搜索引擎',itemStyle:{color:'#4f46e5'}},{value:s['直接访问']||0,name:'直接访问',itemStyle:{color:'#22d3ee'}},{value:s['社交媒体']||0,name:'社交媒体',itemStyle:{color:'#f472b6'}},{value:s['外部链接']||0,name:'外部链接',itemStyle:{color:'#fbbf24'}}];
  charts.source=echarts.init(document.getElementById('sourceChart'));
  charts.source.setOption(Object.assign(common(),{legend:{bottom:0},series:[{type:'pie',radius:['40%','65%'],center:['50%','46%'],data:data,label:{color:textColor},itemStyle:{borderRadius:6,borderColor:isDark?'#182230':'#fff',borderWidth:2}}]}));
})();

(function growth(){
  var g=STATS.growth||[];
  var days=g.map(function(i){return i.date;});
  charts.growth=echarts.init(document.getElementById('growthChart'));
  charts.growth.setOption(Object.assign(common(),{
    legend:{data:['PV','UV'],top:0},
    tooltip:{trigger:'axis'},
    xAxis:{type:'category',data:days,axisLine:{lineStyle:{color:lineColor}},axisLabel:{color:mutedColor,interval:3}},
    yAxis:[
      {type:'value',name:'PV',splitLine:{lineStyle:{color:lineColor,type:'dashed'}},axisLabel:{color:mutedColor}},
      {type:'value',name:'UV',splitLine:{show:false},axisLabel:{color:mutedColor}}
    ],
    series:[
      {name:'PV',type:'bar',data:g.map(function(i){return i.pv;}),itemStyle:{borderRadius:[3,3,0,0],color:new echarts.graphic.LinearGradient(0,0,0,1,[{offset:0,color:'#7c83ff'},{offset:1,color:'#4f46e5'}])},barWidth:'55%'},
      {name:'UV',type:'line',yAxisIndex:1,smooth:true,data:g.map(function(i){return i.uv;}),itemStyle:{color:'#22d3ee'},areaStyle:{color:'rgba(34,211,238,.12)'}}
    ]
  }));
})();

(function mapChart(){
  var el=document.getElementById('mapChart');
  charts.map=echarts.init(el);
  // 中国地图扩展未加载（离线）时，回退为 TOP10 柱状
  if (typeof echarts.getMap !== 'function' || !echarts.getMap('china')) {
    var rows=(STATS.cities||[]).slice().reverse();
    charts.map.setOption(Object.assign(common(),{
      grid:{left:10,right:20,top:10,bottom:10,containLabel:true},
      xAxis:{type:'value',splitLine:{lineStyle:{color:lineColor,type:'dashed'}},axisLabel:{color:mutedColor}},
      yAxis:{type:'category',data:rows.map(function(r){return r.city;}),axisLine:{lineStyle:{color:lineColor}},axisLabel:{color:mutedColor}},
      series:[{type:'bar',data:rows.map(function(r){return r.n;}),itemStyle:{borderRadius:[0,6,6,0],color:new echarts.graphic.LinearGradient(0,0,1,0,[{offset:0,color:'#4f46e5'},{offset:1,color:'#22d3ee'}])},barWidth:14}]
    }));
    return;
  }
  var maxV=MAP_POINTS.reduce(function(m,p){return Math.max(m,p.value[2]||0);},1);
  charts.map.setOption({
    backgroundColor:'transparent',
    tooltip:{trigger:'item',formatter:function(p){return p.name+'<br/>访问量：'+(p.value?p.value[2]:0);}},
    visualMap:{min:0,max:maxV,calculable:true,left:10,bottom:10,text:['高','低'],textStyle:{color:mutedColor},inRange:{color:['#e0e7ff','#7c83ff','#4f46e5','#f472b6']}},
    geo:{map:'china',roam:true,zoom:1.2,itemStyle:{areaColor:isDark?'#1a2330':'#eef2f8',borderColor:isDark?'#2c3a4d':'#c7d2e0'},emphasis:{itemStyle:{areaColor:isDark?'#243345':'#dbe4f0'},label:{show:false}}},
    series:[
      {name:'访问量',type:'effectScatter',coordinateSystem:'geo',data:MAP_POINTS,symbolSize:function(v){return Math.max(8, Math.sqrt(v[2]/maxV)*44);},
        rippleEffect:{brushType:'stroke',scale:3},itemStyle:{shadowBlur:8,shadowColor:'rgba(124,131,255,.6)'},encode:{value:2}},
      {name:'热力',type:'heatmap',coordinateSystem:'geo',data:MAP_POINTS,pointSize:14,blurSize:18,opacity:0.5}
    ]
  });
})();

window.addEventListener('resize',function(){Object.values(charts).forEach(function(c){c.resize();});});
}

window.__loadEcharts(0, function(){
  window.__loadChinaJs(0, function(){
    initDashboardCharts();
  });
});

(function live(){
  var container=document.getElementById('liveLog');
  var list=<?= json_encode($stats['recent'] ?? [], JSON_UNESCAPED_UNICODE) ?>;
  var devices={'mobile':'移动端','desktop':'桌面端','tablet':'平板','unknown':'未知'};
  function fmt(ts){ if(!ts)return '刚刚'; var d=new Date(ts); return (d.getMonth()+1)+'-'+d.getDate()+' '+d.getHours()+':'+(d.getMinutes()<10?'0':'')+d.getMinutes(); }
  function add(item){
    var div=document.createElement('div'); div.className='live-item';
    div.innerHTML='<span class="dot"></span><span class="ip">'+(item.ip||'0.0.0.0')+'</span><span class="loc">'+(item.city||'未知')+' · '+(item.page||'/')+'</span><span class="device">'+(devices[item.device]||item.device)+'</span><span class="time">'+fmt(item.created_at)+'</span>';
    container.insertBefore(div,container.firstChild);
    if(container.children.length>12) container.removeChild(container.lastChild);
  }
  if(list.length){ list.forEach(add); }
  // 新安装站点（无真实访问记录）：不模拟假数据，直接反馈真实（空）数据并提示
  if (list.length === 0) {
    var empty=document.createElement('div'); empty.style.cssText='color:var(--muted);font-size:12.5px;padding:20px 0;text-align:center';
    empty.textContent='暂无实时访问记录，前台被访问后将自动统计';
    container.appendChild(empty);
  }
})();
</script>
</body>
</html>
