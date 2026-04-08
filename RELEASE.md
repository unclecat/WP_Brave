# Brave Love v1.0.9

Brave Love `v1.0.9` 是一次聚焦 `首页天气配置体验` 的 patch release。

这次更新把 QWeather 的配置入口真正搬进了后台。现在你不一定要再去改 `wp-config.php`，直接在 `设置 -> 天气城市` 里就能填写 `QWeather API Host` 和 `QWeather API Key`。如果某个环境仍然希望用服务器级配置，也继续兼容：`wp-config.php / 环境变量` 依然优先，后台保存值则作为兜底。

## 本次发布亮点

### 1) 后台可直接配置 QWeather
- 在 `设置 -> 天气城市` 中新增 `QWeather API Host`
- 在 `设置 -> 天气城市` 中新增 `QWeather API Key`
- 后台保存后即可作为天气服务配置来源，不再强依赖修改 `wp-config.php`

### 2) 保留服务器级配置优先级
- 如果服务器环境里已经配置了 `QWEATHER_API_HOST` / `QWEATHER_API_KEY`
- 当前版本会优先使用服务器配置
- 后台保存值不会把服务器级配置覆盖掉，而是作为后备来源

### 3) 配置状态更直观
- 设置页会显示当前 `Host` 和 `Key` 的生效来源
- 已保存的后台 `API Key` 不明文回显
- 支持“留空保持不变”与“清空后台已保存 Key”

### 4) 保存后自动刷新天气缓存
- 现在修改天气凭证或城市配置后，会自动刷新缓存
- 避免刚保存完配置，前台还在继续读旧天气结果

## 重点变更文件

- `inc/weather-service.php`
- `inc/weather-admin.php`
- `style.css`
- `functions.php`
- `README.md`
- `docs/USER-GUIDE.md`
- `CHANGELOG.md`
- `RELEASE.md`
- `TEST-REPORT.md`

## 验证结果

以下检查已在本地执行：

- `php -l inc/weather-service.php`
- `php -l inc/weather-admin.php`
- 全量 `php -l`
- `node --check assets/js/brave.js`
- `bash tests/check-theme-simple.sh`
- `php tests/check-theme.php`
- `php tests/security-scan.php`
- 本地接口验证：`http://localhost:8080/wp-json/brave-love/v1/weather`
- 本地数据库写入测试：后台 QWeather Host / Key 选项可保存并被主题读取

## 升级说明

如果你正在使用 `v1.0.8`：

1. 可以继续沿用 `wp-config.php` / 环境变量配置，不需要迁移
2. 也可以改成在后台 `设置 -> 天气城市` 中直接填写 Host / Key
3. 如果同一环境里后台和服务器级配置同时存在，服务器配置仍然优先
4. `QWeather API Host` 就是和风天气给你的接口域名，例如 `nb7aarhnan.re.qweatherapi.com`

## 发布资产

- `brave-love.zip`
- GitHub Release Notes（当前文件）
- `CHANGELOG.md`
- `TEST-REPORT.md`

**版本**: `1.0.9`
**发布日期**: `2026-04-08`
**更新日志**: `CHANGELOG.md`
