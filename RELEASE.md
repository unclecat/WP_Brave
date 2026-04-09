# Brave Love v1.1.2

Brave Love `v1.1.2` 是一次以 `首页天气稳定性` 为重点的 maintenance release。

这次更新不涉及数据库迁移，也不改现有后台数据结构；重点是给首页天气补上一层服务器端整包快照缓存，让前台不再那么容易被第三方天气接口的短时波动直接打空。

## 本次发布亮点

### 1) 首页天气改为优先读取服务器快照
- 首页天气 REST 现在会优先返回服务器上最近 5 分钟的成功快照
- 同一时间段内的访客不会各自重新触发一次完整天气组装
- 首页天气卡片和弹窗因此更稳，也更不容易偶发空白

### 2) 接口短时失败时整包回退更完整
- 如果本轮天气重新拉取失败，但服务器上已有上一份成功快照
- 现在会继续返回这份旧快照，而不是让首页天气整块直接进入报错态
- 前台仍会保留“稍早缓存”的状态提示

### 3) 天气缓存职责继续拆清楚
- 新增 `inc/weather/snapshot.php`
- 首页天气快照的读取、保存、回退与 TTL 逻辑独立收口
- 后续如果继续做“服务器定时预热版”，可以直接在这层继续扩展

## 重点变更文件

- `functions.php`
- `style.css`
- `inc/weather-service.php`
- `inc/weather/rest.php`
- `inc/weather/snapshot.php`
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
- `curl http://127.0.0.1:8080/wp-json/brave-love/v1/weather`
- headless Chrome homepage screenshot regression (desktop + mobile)

## 升级说明

如果你正在使用 `v1.1.1`：

1. 这次无需做数据库迁移
2. 不涉及内容字段变更，现有页面和数据可直接继续使用
3. 首页天气会改为优先读取最近 5 分钟的服务器快照，接口短时波动时稳定性会更好
4. 发布后如静态资源缓存较重，建议刷新一次缓存

## 发布资产

- `brave-love.zip`
- GitHub Release Notes（当前文件）
- `CHANGELOG.md`
- `TEST-REPORT.md`

**版本**: `1.1.2`
**发布日期**: `2026-04-09`
**更新日志**: `CHANGELOG.md`
