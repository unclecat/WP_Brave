# Brave Love v1.0.3

Brave Love `v1.0.3` 是一次聚焦点点滴滴摘要统一与后台迁移收口的 patch release。

这次更新把点点滴滴摘要正式收口到 WordPress 原生摘要 `post_excerpt`：前台已统一优先读取原生摘要，同时新增后台迁移工具，方便把旧 `_moment_summary` 平滑迁移并按需清理。与此同时，也补上了首页计时器旧配置兼容和后台外链/冗余短代码的安全收口。

## 本次发布亮点

### 1) 点点滴滴摘要统一到 WordPress 原生摘要
- 点点滴滴列表页、详情页、相册摘要统一读取逻辑
- 前台优先读取原生摘要，迁移期继续兼容旧 `_moment_summary`
- 后台移除重复的自定义摘要输入框，后续维护统一走编辑器原生“摘要”面板

### 2) 新增后台摘要迁移工具
- 新增 `点点滴滴 -> 摘要迁移` 页面
- 支持一键把旧 `_moment_summary` 回填到 `post_excerpt`
- 支持只清理“已与原生摘要完全一致”的旧字段，避免误删冲突数据

### 3) 安全收口与冗余清理
- 首页计时器统一走 `brave_get_love_start_datetime()`，兼容旧站历史配置
- 删除未使用的短代码模块 `inc/shortcodes.php`
- 移除天气后台说明中的外部文档链接，减少后台外链暴露面

## 重点变更文件

- `style.css`
- `functions.php`
- `inc/helpers.php`
- `inc/meta-boxes.php`
- `inc/moment-excerpt-migration.php`
- `inc/weather-admin.php`
- `page-templates/page-home.php`
- `page-templates/page-moments.php`
- `single-moment.php`
- `README.md`
- `CHANGELOG.md`
- `RELEASE.md`
- `TEST-REPORT.md`

## 验证结果

以下检查已在本地执行：

- `php -l` 检查所有本次修改的 PHP 文件
- `php tests/check-theme.php`
- `php tests/security-scan.php`
- `bash tests/check-theme-simple.sh`
- 本地执行点点滴滴摘要迁移统计 -> 回填 -> 清理全流程验证
- 本地点点滴滴页面 `http://localhost:8080/?page_id=5` 渲染验证
- 本地首页 `http://localhost:8080/` 计时器与天气模块验证

## 升级说明

如果你正在使用 `v1.0.2`：

1. 覆盖主题文件后，先清除站点缓存与浏览器缓存
2. 到“设置 -> 固定链接”点击一次“保存更改”刷新重写规则
3. 打开 `点点滴滴 -> 摘要迁移`，先执行一次“迁移到原生摘要”
4. 确认前台显示正常后，再执行一次“清理已迁移旧字段”
5. 后续维护点点滴滴摘要时，统一使用编辑器自带的“摘要”面板

## 发布资产

- `brave-love.zip`
- GitHub Release Notes（当前文件）
- `CHANGELOG.md`
- `TEST-REPORT.md`

**版本**: `1.0.3`  
**发布日期**: `2026-04-04`  
**更新日志**: `CHANGELOG.md`
