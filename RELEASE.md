# Brave Love v0.7.8

本次版本重点完成两件事：一是补齐“关于我们”这条主线页面，二是把首页天气模块和多处前端交互做成更适合正式发版的状态。

## 本次更新

- 新增独立的“关于我们”页面模板，支持按故事节点维护一路走来的关键时间点，并可选关联到某一篇点点滴滴
- 首页新增“关于我们”入口卡片，首页 6 个入口卡片统一移除副标题，视觉更干净
- 首页天气模块整体重做：支持 5 个城市的实时天气卡片、详情弹层、逐小时趋势、日照信息、紫外线和今日穿搭建议
- 优化天气弹层尺寸、间距、层次与明暗模式表现，减少“面板过大、信息拥挤”的问题
- 抽离通用筛选下拉逻辑到公共脚本，去掉点点滴滴 / 甜蜜相册 / 随笔说说模板中的重复内联脚本
- 优化 PhotoSwipe 初始化逻辑，只在相册页存在图片卡片时才加载相册交互，减少非相册页的无效执行

## 关键文件

- `functions.php`
- `style.css`
- `assets/css/brave.css`
- `assets/css/about.css`
- `assets/css/notes.css`
- `assets/js/brave.js`
- `inc/customizer.php`
- `inc/helpers.php`
- `inc/meta-boxes.php`
- `inc/post-types.php`
- `page-templates/page-about.php`
- `page-templates/page-home.php`
- `page-templates/page-memories.php`
- `page-templates/page-moments.php`
- `page-templates/page-notes.php`
- `archive-love_list.php`
- `tests/setup-test-data.sh`
- `CHANGELOG.md`
- `TEST-REPORT.md`

## 验证结果

- `bash tests/check-theme-simple.sh` 已通过
- `php tests/check-theme.php` 已通过
- `php tests/security-scan.php` 已通过，结果为 `9 通过 / 0 警告 / 0 错误`
- 本地 WordPress 运行态已回归：首页、关于我们、点点滴滴、甜蜜相册、随笔说说、祝福留言、恋爱清单页面均可正常输出关键结构
- 本地首页已确认加载 `brave.css?ver=0.7.8` 与 `brave.js?ver=0.7.8`，缓存刷新路径正常

## 已知限制

- 天气模块仍依赖 `Open-Meteo` 实时接口，离线或接口异常时不会返回实时天气数据
- 当前验证仍以本地 PHP 检查、主题自带脚本和本地 WordPress HTTP 冒烟为主，未包含真实浏览器自动化回归

## 下载与更新

1. 下载 `brave-love.zip`
2. 解压后上传到 `wp-content/themes/brave-love/`
3. 覆盖原主题文件
4. 清除站点与浏览器缓存

**版本**: 0.7.8  
**发布日期**: 2026-04-03  
**更新日志**: [CHANGELOG.md](./CHANGELOG.md)
