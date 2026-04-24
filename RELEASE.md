# Brave Love v1.2.2

Brave Love `v1.2.2` 是一次以 `首页天气稳定性 + 本地验证体验` 为重点的 maintenance release。

这次更新不涉及数据库迁移，也不改现有后台内容结构；重点是把首页天气继续从“能用”收口到“多城市更稳、异常更可控、发版前更容易验证”。

## 本次发布亮点

### 1) 首页天气改成 summary / detail 两段式
- 首页天气卡片现在优先读取轻量 summary 数据
- AQI、紫外线、主污染物、空气预报与贴心提醒等 detail 数据在打开弹窗时再按需拉取
- 城市数量变多时，首页首屏不会再一上来顺序请求整套重接口

### 2) 单城市异常不再拖累整包天气
- 现在即使某一个城市坐标填错，或第三方接口对某个城市短时失败
- 也不会再把所有城市一起锁回旧快照
- 正常城市仍会继续显示最近的新鲜数据

### 3) 天气缓存陈旧度控制更明确
- 每份天气 backup 现在都有最大可接受陈旧时间
- 如果第三方接口持续异常，过旧的 backup 不会被无限期拿来继续展示
- 前台“稍早缓存”提示和实际数据新鲜度更一致

### 4) 本地验证链路更顺手
- `tests/test-theme.sh` 现已兼容 `docker compose`
- 如果 WordPress 没有在等待窗口内真正就绪，脚本会正确失败，不再误报“环境启动成功”
- `tests/security-scan.php` 默认不再改写仓库，只有显式传入 `--write-report` 才导出报告

## 重点变更文件

- `functions.php`
- `style.css`
- `inc/weather/client.php`
- `inc/weather/payload.php`
- `inc/weather/rest.php`
- `inc/weather/snapshot.php`
- `tests/security-scan.php`
- `tests/test-theme.sh`
- `README.md`
- `CHANGELOG.md`
- `RELEASE.md`

## 验证结果

以下检查已在本地执行：

- `php -l functions.php`
- `php -l inc/weather/client.php`
- `php -l inc/weather/payload.php`
- `php -l inc/weather/rest.php`
- `php -l inc/weather/snapshot.php`
- `php -l tests/security-scan.php`
- `node --check assets/js/brave.js`
- `php tests/check-theme.php`
- `php tests/security-scan.php`
- `php tests/security-scan.php --write-report=/tmp/brave-security-report.md`
- `bash tests/test-theme.sh`
- Docker 内首页天气 summary / detail 验证
- 浏览器首页天气卡片与天气弹窗可视回归

## 升级说明

如果你正在使用 `v1.2.1`：

1. 这次无需做数据库迁移
2. 不涉及内容字段变更，现有页面和后台配置可直接继续使用
3. 首页天气会改为“卡片轻量 + 弹窗详情”的请求模型，多城市配置下首页更稳
4. 新环境部署或主题切换后，旅行计划详情页的 rewrite 会自动收口一次
5. 发布后如静态资源缓存较重，建议刷新一次缓存

## 发布资产

- `brave-love.zip`
- GitHub Release Notes（当前文件）
- `CHANGELOG.md`
- `TEST-REPORT.md`

**版本**: `1.2.2`
**发布日期**: `2026-04-24`
**更新日志**: `CHANGELOG.md`
