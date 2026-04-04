# Brave Love v1.0.2

Brave Love `v1.0.2` 是一次聚焦首页天气可扩展性与代码清理的 patch release。

这次更新主要解决了“今日天气”模块最多只能显示 4 个城市的问题，并同步清理一批已经不再使用的 helper、旧注释和测试说明，让主题代码和文档更一致。

## 本次发布亮点

### 1) 首页天气不再限制 4 个城市
- 去掉天气城市保存阶段的 4 个上限
- 去掉前台读取天气城市阶段的 4 个上限
- 首页天气卡片现在会展示后台中已配置的全部城市

### 2) 主题冗余代码清理
- 删除未使用的 helper：头像 HTML 包装、说说配图读取、日期差计算、移动端判断、SVG 清理
- 删除未接入的说说图片提取函数，避免误导后续维护
- 清理遗留的旧 `@version` 注释，减少版本信息噪音

### 3) 文档与测试说明同步
- 更新 README 中的天气城市描述，不再写 2-4 个限制
- 修正测试文档里与当前说说功能不一致的检查项
- 保持现有 GitHub Release 打包与发版流程不变

## 重点变更文件

- `style.css`
- `functions.php`
- `inc/weather-admin.php`
- `inc/helpers.php`
- `inc/meta-boxes.php`
- `README.md`
- `CHANGELOG.md`
- `RELEASE.md`
- `TEST-REPORT.md`
- `tests/README-TEST.md`

## 验证结果

以下检查已在本地执行：

- `find . -name '*.php' -not -path './tests/*' -print0 | xargs -0 -n1 php -l`
- `php tests/check-theme.php`
- `php tests/security-scan.php`
- `bash tests/check-theme-simple.sh`
- 本地 WordPress 首页 `http://localhost:8080/` 天气模块验证
- 确认首页在当前数据下可渲染超过 4 个天气城市，且资源版本已更新到 `1.0.2`

## 升级说明

如果你正在使用 `v1.0.1`：

1. 覆盖主题文件后，先清除站点缓存与浏览器缓存
2. 到“设置 -> 固定链接”点击一次“保存更改”刷新重写规则
3. 如果之前在“天气城市”里录入了超过 4 个城市但前台没显示完整，升级后会自动显示全部有效城市
4. 作者网站外链保留为当前主题的默认展示策略，不在这次清理范围内

## 发布资产

- `brave-love.zip`
- GitHub Release Notes（当前文件）
- `CHANGELOG.md`
- `TEST-REPORT.md`

**版本**: `1.0.2`  
**发布日期**: `2026-04-04`  
**更新日志**: `CHANGELOG.md`
