# Brave Love v1.1.0

Brave Love `v1.1.0` 是一次聚焦 `首页 Hero 视觉收口` 的 minor release。

这次更新不涉及数据库迁移，也不改后台数据结构，重点是把首页顶部双头像区域的视觉语言统一起来：头像外侧的彗星星轨更像真正的星星，中间玻璃心与心电图的关系也更自然。

## 本次发布亮点

### 1) Hero 彗星星轨更贴脸、更像星星
- 头像外层星轨进一步贴近头像
- 星点从普通亮点收口为更明确的星形粒子
- 亮度、柔光和闪烁节奏统一增强
- 移除头部多余的最外侧星点，彗星头更干净

### 2) 中间玻璃心完成统一收口
- 将中间爱心从偏果冻感样式改为更轻透的玻璃心
- 多次微调心的大小与垂直位置
- 让心电图更自然地从玻璃心下半部穿过
- 保留温柔感，同时与头像星轨风格保持一致

### 3) 心电图细节更完整
- 增加玻璃心左右两侧的波动细节
- 避免心跳只集中在中心一小段
- 整体节奏更像连续流动的心电图

## 重点变更文件

- `header.php`
- `style.css`
- `functions.php`
- `README.md`
- `CHANGELOG.md`
- `RELEASE.md`
- `TEST-REPORT.md`

## 验证结果

以下检查已在本地执行：

- `find . -path './tests' -prune -o -name '*.php' -type f -print0 | xargs -0 -n1 php -l`
- `node --check assets/js/brave.js`
- `node --check assets/js/admin.js`
- `node --check assets/js/memory.js`
- `bash tests/check-theme-simple.sh`
- `php tests/security-scan.php`
- `git diff --check`
- `curl -I http://127.0.0.1:8080/`

## 升级说明

如果你正在使用 `v1.0.10`：

1. 这次无需做数据库迁移
2. 不涉及后台字段变更，现有内容可直接继续使用
3. 主要变化集中在首页 Hero 区视觉表现
4. 如浏览器缓存较重，建议发布后刷新一次静态资源缓存

## 发布资产

- `brave-love.zip`
- GitHub Release Notes（当前文件）
- `CHANGELOG.md`
- `TEST-REPORT.md`

**版本**: `1.1.0`
**发布日期**: `2026-04-08`
**更新日志**: `CHANGELOG.md`
