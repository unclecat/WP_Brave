# Brave Love v0.7.5

本次是一个补丁版本，主要补齐主题预览图，并清理本地 Docker 测试环境的兼容性警告。

## 本次更新

- 新增正式的 `screenshot.png`，WordPress 主题列表中可以正常显示 `brave-love` 预览图
- 移除 `tests/docker-compose.yml` 中已废弃的 `version` 字段，避免 Docker Compose v2 输出兼容性警告
- 延续上一补丁版本已经完成的年份筛选修复、相册缓存、本地字体与静态资源本地化方案

## 关键文件

- `screenshot.png`
- `tests/docker-compose.yml`
- `functions.php`
- `style.css`
- `CHANGELOG.md`

## 验证结果

- `php -l` 已通过全部 24 个 PHP 文件语法检查
- `node --check` 已通过 `assets/js/brave.js` 与 `assets/js/memory.js`
- `bash tests/check-theme-simple.sh` 已通过
- `docker compose -f tests/docker-compose.yml config` 已验证不再输出 `version` 废弃警告

## 已知限制

- 天气模块仍依赖 `Open-Meteo` 实时接口，离线环境下不会返回实时天气数据
- GitHub Release 资产上传仍依赖 tag push 触发 Actions 工作流

## 下载与更新

1. 下载 `brave-love.zip`
2. 解压后上传到 `wp-content/themes/brave-love/`
3. 覆盖原主题文件
4. 清除站点与浏览器缓存

**版本**: 0.7.5  
**发布日期**: 2026-04-02  
**更新日志**: [CHANGELOG.md](./CHANGELOG.md)
