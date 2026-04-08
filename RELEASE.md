# Brave Love v1.0.10

Brave Love `v1.0.10` 是一次聚焦 `天气模块结构整理与文案收口` 的 patch release。

这次更新不改已有前台入口，也不需要做数据库迁移，重点是把首页天气这一块的代码结构整理干净，并把后台状态提示和贴心提醒文案再收口一轮。对于后续继续迭代天气能力、排查 QWeather 配置问题，会更轻松一些。

## 本次发布亮点

### 1) 天气服务完成结构拆分
- 保留 `inc/weather-service.php` 作为原入口文件
- 具体实现拆到 `inc/weather/config.php`
- `inc/weather/client.php` 负责接口请求与缓存
- `inc/weather/support.php` 负责通用格式化与辅助判断
- `inc/weather/copy.php` 负责贴心提醒与标签文案
- `inc/weather/payload.php` 负责城市天气数据组装
- `inc/weather/rest.php` 负责首页 payload 与 REST 输出

### 2) 后台配置状态提示更清晰
- 设置页现在会更明确地区分 `当前实际生效`
- 同时展示 `Host 当前使用 / Key 当前使用`
- 也会保留 `后台已保存 Host / Key` 的状态说明
- 缺少配置时会直接提示缺项，减少排查误判

### 3) 贴心提醒文案统一润色
- 调整首页天气“贴心提醒”的组合逻辑
- 减少重复用词、重复语气词和别扭表达
- 保持现有功能不变，只优化实际展示出来的文案观感

## 重点变更文件

- `inc/weather-service.php`
- `inc/weather/config.php`
- `inc/weather/client.php`
- `inc/weather/support.php`
- `inc/weather/copy.php`
- `inc/weather/payload.php`
- `inc/weather/rest.php`
- `inc/weather-admin.php`
- `functions.php`
- `style.css`
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
- `php tests/check-theme.php`
- `php tests/security-scan.php`
- 本地运行验证：`http://127.0.0.1:8080/`
- 本地运行验证：`http://127.0.0.1:8080/about-us/`
- 本地接口验证：`http://127.0.0.1:8080/wp-json/brave-love/v1/weather`
- Docker 运行时验证：`brave_get_qweather_config()` / `brave_get_home_weather_payload()`

## 升级说明

如果你正在使用 `v1.0.9`：

1. 这次无需做数据库迁移
2. 后台 `QWeather API Host / API Key` 配置方式保持不变
3. 原来的 `inc/weather-service.php` 引入路径仍然保留，不会影响主题加载
4. 如果你后面还要继续扩展天气卡片，建议直接在新的 `inc/weather/*.php` 模块内继续维护

## 发布资产

- `brave-love.zip`
- GitHub Release Notes（当前文件）
- `CHANGELOG.md`
- `TEST-REPORT.md`

**版本**: `1.0.10`
**发布日期**: `2026-04-08`
**更新日志**: `CHANGELOG.md`
