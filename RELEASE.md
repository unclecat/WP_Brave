# Brave Love v0.7.7

本次是一个补丁版本，重点完成内页布局统一、恋爱清单筛选增强，以及发版前的一轮安全与稳定性加固。

## 本次更新

- 恋爱清单新增可点击的“已完成 / 待完成 / 完成度”统计入口，支持按状态快速筛选条目
- 恋爱清单前台取消分页，避免少量卡片被拆到第二页；旧分页链接和无效查询参数会自动回到规范地址
- 甜蜜相册年份筛选改为下拉式交互，和点点滴滴、随笔说说保持一致
- 修复甜蜜相册筛选下拉被下方照片卡片遮挡的问题
- 祝福留言页改为更高密度的自适应卡片网格，桌面端展示节奏与恋爱清单更一致
- 统一主要内页的 Hero 宽度、筛选区节奏和内容容器宽度，减少页面间风格割裂
- 为模板、模板部件和 `inc/` 核心文件补充直接访问保护，并统一关键表单/查询参数的输入清洗逻辑

## 关键文件

- `functions.php`
- `archive-love_list.php`
- `assets/css/love-list.css`
- `assets/css/memory.css`
- `page-templates/page-memories.php`
- `page-templates/page-moments.php`
- `page-templates/page-notes.php`
- `page-templates/page-blessing.php`
- `style.css`
- `inc/helpers.php`
- `inc/meta-boxes.php`
- `inc/customizer.php`
- `inc/gallery-admin.php`
- `inc/weather-admin.php`
- `template-parts/page-hero.php`
- `CHANGELOG.md`
- `TEST-REPORT.md`

## 验证结果

- `find . -name '*.php' -not -path './tests/*' -print0 | xargs -0 -n1 php -l` 已通过
- `bash tests/check-theme-simple.sh` 已通过
- `php tests/check-theme.php` 已通过
- `php tests/security-scan.php` 已通过，直接访问保护告警已清零
- 本地 WordPress 运行态已冒烟验证：首页、点点滴滴、甜蜜相册、随笔说说、祝福留言、恋爱清单页面均可正常输出关键结构
- 恋爱清单状态筛选、canonical 输出、旧分页 302 回跳与无效参数清洗已在本地 HTTP 层验证通过

## 已知限制

- 天气模块仍依赖 `Open-Meteo` 实时接口，离线或接口异常时不会返回实时天气数据
- 当前验证以本地 PHP/HTTP 冒烟和主题自带脚本为主，未包含真实浏览器自动化回归

## 下载与更新

1. 下载 `brave-love.zip`
2. 解压后上传到 `wp-content/themes/brave-love/`
3. 覆盖原主题文件
4. 清除站点与浏览器缓存

**版本**: 0.7.7  
**发布日期**: 2026-04-02  
**更新日志**: [CHANGELOG.md](./CHANGELOG.md)
