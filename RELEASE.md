# Brave Love v1.0.8

Brave Love `v1.0.8` 是一次聚焦 `首页天气模块` 收口与发版整理的 patch release。

这次更新把最近一轮天气改造里已经确认下线的旧逻辑彻底清掉，同时把首页天气弹窗在桌面端和手机端的展示继续打磨了一遍。对站点使用者来说，最直接的变化是：首页天气详情更紧凑、更清晰，贴心提醒与空气预报信息保留了下来，但旧的分钟降雨链路不再继续消耗接口请求，也不会再误伤 `稍早数据` 状态。

## 本次发布亮点

### 1) 首页天气详情弹窗继续打磨
- 保留当前“体感 + 贴心提醒 + 今日降雨概率 + 空气预报”的信息结构
- 统一桌面端与手机端的排版节奏，信息分区更稳定
- 让提醒文案、标签、空气质量相关信息在小屏下也能自然换行

### 2) 移除旧分钟降雨依赖
- 删除已不再展示的 `minutely` 分钟降雨接口请求
- 删除对应返回字段，避免前端继续携带无用数据
- `stale` 状态不再被这个旧链路影响，天气状态判断更符合实际

### 3) 恢复天气坐标精度
- QWeather 请求经纬度从 2 位小数恢复到 4 位小数
- 对空气质量、降雨概率和边界城区天气数据更友好
- 后台原本录入的 4 位精度现在可以完整参与请求

### 4) 清理已移除模块残留
- 删除首页天气弹窗已下线“健康提醒”模块的残留样式
- 同步清理移动端和深色模式中的相关 CSS 引用
- 减少后续继续迭代天气模块时的样式噪音

## 重点变更文件

- `assets/css/brave.css`
- `assets/js/brave.js`
- `inc/weather-service.php`
- `page-templates/page-home.php`
- `style.css`
- `functions.php`
- `CHANGELOG.md`
- `RELEASE.md`
- `TEST-REPORT.md`
- `README.md`
- `.github/ABOUT.md`

## 验证结果

以下检查已在本地执行：

- `find . -name '*.php' -not -path './tests/wordpress/*' -print0 | xargs -0 -n1 php -l`
- `node --check assets/js/brave.js`
- `bash tests/check-theme-simple.sh`
- `php tests/check-theme.php`
- `php tests/security-scan.php`
- 本地接口验证：`http://localhost:8080/wp-json/brave-love/v1/weather`
- 本地浏览器轻量回归：桌面端与手机端首页天气弹窗

## 升级说明

如果你正在使用 `v1.0.7`：

1. 覆盖主题文件后，先清理站点缓存和浏览器缓存
2. 天气配置方式仍然是 `QWEATHER_API_HOST + QWEATHER_API_KEY`，无需额外迁移
3. 已保存的天气城市坐标不需要重填，这版会直接按更高精度发起请求
4. 首页不再使用旧分钟降雨数据链路，贴心提醒只保留当前版本展示的天气信息

## 发布资产

- `brave-love.zip`
- GitHub Release Notes（当前文件）
- `CHANGELOG.md`
- `TEST-REPORT.md`

**版本**: `1.0.8`
**发布日期**: `2026-04-08`
**更新日志**: `CHANGELOG.md`
