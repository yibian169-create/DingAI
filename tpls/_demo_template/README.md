# 模板 demo · 暖色「活力橙」风格

这是 deyingding-php 系统的一个**完整可运行**模板示例，演示"如何用最少的工作量做出一个完全不同的视觉风格"。

## 这个 demo 做了什么

- **改配色**：从默认深色科技风 → 暖色调（活力橙 #ff6b35 + 金黄 #ffb627 + 番茄红 #e63946）
- **改背景**：页面背景从黑色 → 米白渐变（`#fff8f0` → `#ffeede`）
- **首屏重做**：去掉神经网络 canvas + AI 对话卡，换成纯渐变 + 几何径向光晕
- **数据统计**：渐变数字（`-webkit-background-clip: text`）+ 卡片悬浮动效
- **能力卡**：悬浮上移 + 橙色边框高亮
- **联系我们**：整块改成橙黄渐变 + 毛玻璃信息卡

## 文件结构

```
style.css   # 全部样式（约 150 行，覆盖每个区块）
main.js     # 交互效果（打字机 + 滚动揭示 + 数字递增 + 平滑滚动）
README.md   # 本文件
```

## 怎么用

1. 把这个文件夹打成 zip（**注意是压缩文件夹内的文件**，不是压缩文件夹本身）：
   ```
   _demo_template/style.css
   _demo_template/main.js
   _demo_template/README.md
   ```
2. 后台「模板中心 → 选择模板 → 导入模板（超管）」上传 zip
3. 启用 → 立刻看到暖色风格首页

## 让 AI 帮你改造

把整个文件夹喂给 ChatGPT / Claude / Gemini，然后告诉 AI：

> 这是 deyingding-php 的模板 demo。style.css 里覆盖了每个区块的样式（.q-hero / .q-stats / .q-feat / .q-contact 等）。
> 我想要 [你的需求，例如：]
> - 改用蓝色科技风（主色 #00d4ff）
> - Hero 加个粒子动效
> - 能力卡从 6 个改成 3 个大卡片
>
> 请帮我修改 style.css 和 main.js。

AI 会基于这个 demo 帮你改造。

## 可改的关键点（直接告诉 AI 改这里）

| 想改什么 | 改哪一行 |
|---|---|
| **主色调** | `:root { --c1: ...; --c2: ...; --c3: ...; }`（style.css 第 6-8 行）|
| **页面背景** | `body { background: ...; }`（style.css 第 20 行）|
| **首屏渐变** | `.q-hero { background: ...; }`（style.css 第 27 行）|
| **按钮配色** | `.q-btn--grad { background: ...; }`（style.css 第 70 行）|
| **数字渐变** | `.q-stats .q-stat__num { background: ...; }`（style.css 第 88 行）|

## 调试技巧

- 浏览器 F12 → Elements → 搜索 `.q-hero` 看默认 HTML 结构
- 修改 style.css 后强制刷新 Ctrl + F5（避免缓存）
- 后台「首页 DIY」改顺序/显隐，**不需要改模板**
- 想看 CSS 变量有哪些：F12 控制台输入 `getComputedStyle(document.documentElement).getPropertyValue('--c1')`

## 进阶玩法

- 接入 Web Font：在 `fonts/` 放字体的 woff2，CSS 里 `@font-face` 引入
- 多主题切换：复制 style.css 出 v2-v5 各一份，用 CSS 类切换
- 暗色/亮色切换：把所有颜色用 CSS 变量，body.dark 加变量覆盖

## 已知限制

- demo 不含 `images/` 和 `fonts/` 目录（如需可自己加，导入时一起打包）
- demo 的 JS 效果仅为基础 4 种，复杂动画（如背景粒子）需要更多 main.js 代码
- demo 不影响「主题设置」里的 3 色（主题设置是后台填的，模板只是"默认皮肤"）