# Brave Love v1.0.5

Brave Love `v1.0.5` 是一次聚焦首页天气后台交互体验的 patch release。

这次更新主要补上了你刚提出的需求：`设置 -> 天气城市` 现在支持直接拖拽排序。调整并保存后，首页 `今日天气` 模块会按后台拖拽后的顺序输出，不再需要通过删除重加或手动猜顺序来控制。

## 本次发布亮点

### 1) 天气城市支持拖拽排序
- 天气管理页新增拖拽手柄
- 支持直接拖拽整行调整顺序
- 保存后首页天气卡片按该顺序展示

### 2) 后台交互更直观
- 移除原来的上移 / 下移按钮式排序交互
- 增加拖拽占位样式和排序序号刷新
- 保留原有添加 / 删除城市流程，不影响已有配置

### 3) 文档同步更新
- 更新 `README.md` 中首页天气与后台天气管理说明
- 更新 `docs/USER-GUIDE.md`，补充拖拽排序使用方式
- 同步更新版本号、发布说明与测试报告

## 重点变更文件

- `style.css`
- `functions.php`
- `inc/weather-admin.php`
- `assets/css/admin.css`
- `README.md`
- `docs/USER-GUIDE.md`
- `CHANGELOG.md`
- `RELEASE.md`
- `TEST-REPORT.md`

## 验证结果

以下检查已在本地执行：

- `php -l functions.php`
- `php -l inc/weather-admin.php`
- 本地后台天气管理页输出校验，确认已包含拖拽手柄、`sortable` 初始化与拖拽图标
- 本地首页 `http://localhost:8080/` 顺序验证，确认天气卡片按已保存城市顺序展示

## 升级说明

如果你正在使用 `v1.0.4`：

1. 覆盖主题文件后，先清除站点缓存与浏览器缓存
2. 到“设置 -> 固定链接”点击一次“保存更改”刷新重写规则
3. 进入 `设置 -> 天气城市`，直接拖拽左侧手柄调整顺序
4. 调整完成后点击“保存设置”，首页即可按新顺序显示

## 发布资产

- `brave-love.zip`
- GitHub Release Notes（当前文件）
- `CHANGELOG.md`
- `TEST-REPORT.md`
- `docs/USER-GUIDE.md`

**版本**: `1.0.5`  
**发布日期**: `2026-04-04`  
**更新日志**: `CHANGELOG.md`
