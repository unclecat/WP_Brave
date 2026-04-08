# Brave Love v1.1.1

Brave Love `v1.1.1` 是一次以 `代码结构收口 + 发布稳定性` 为重点的 maintenance release。

这次更新不涉及数据库迁移，也不改现有后台数据结构；重点是把大文件拆清楚、把页面链接硬编码收口掉，并修复 PV 手动覆盖的边界问题，让后续维护和发版更稳。

## 本次发布亮点

### 1) 主题主样式完成拆分
- `style.css` 现在只保留 WordPress 主题头信息
- 前端样式统一由 `assets/css/theme-core.css` 和 `assets/css/brave.css` 注册加载
- 后续再调首页、天气、内容页样式时，不用在一个超大入口文件里硬找

### 2) Customizer / Helpers 大文件拆成模块
- `inc/customizer.php` 拆成 sanitize、控件、注册、输出、纪念日管理等子模块
- `inc/helpers.php` 拆成 core、avatar、content、gallery、pages 等子模块
- 后续排查页面链接、头像、相册、摘要、缓存时，职责边界更清晰

### 3) 页面链接与统计逻辑更稳
- 点点滴滴归档跳转、详情页返回链接、后台“甜蜜相册”提示统一改成动态页面链接
- 避免页面 slug 调整后，前台和后台还残留旧路径
- PV 手动设置今日/累计数值后，不会再被当前请求额外加 1

## 重点变更文件

- `functions.php`
- `style.css`
- `assets/css/theme-core.css`
- `inc/customizer.php`
- `inc/customizer-*.php`
- `inc/helpers.php`
- `inc/helpers-*.php`
- `inc/meta-boxes.php`
- `single-moment.php`
- `README.md`
- `CHANGELOG.md`
- `RELEASE.md`
- `TEST-REPORT.md`

## 验证结果

以下检查已在本地执行：

- `find . -name '*.php' -print0 | xargs -0 -n1 php -l`
- `node --check assets/js/brave.js`
- `node --check assets/js/admin.js`
- `node --check assets/js/memory.js`
- `php tests/check-theme.php`
- `bash tests/check-theme-simple.sh`
- `php tests/security-scan.php`
- `git diff --check`
- `curl http://127.0.0.1:8080/`
- `curl http://127.0.0.1:8080/about-us/`
- `curl http://127.0.0.1:8080/%E7%82%B9%E7%82%B9%E6%BB%B4%E6%BB%B4/`

## 升级说明

如果你正在使用 `v1.1.0`：

1. 这次无需做数据库迁移
2. 不涉及内容字段变更，现有页面和数据可直接继续使用
3. 如你自定义过页面别名，这次会比旧版本更稳，因为链接已尽量改为动态解析
4. 发布后如静态资源缓存较重，建议刷新一次缓存

## 发布资产

- `brave-love.zip`
- GitHub Release Notes（当前文件）
- `CHANGELOG.md`
- `TEST-REPORT.md`

**版本**: `1.1.1`
**发布日期**: `2026-04-08`
**更新日志**: `CHANGELOG.md`
