# Brave Love v0.7.9

本次是一个收口型补丁版本，重点解决主题元信息和仓库文档不一致的问题，让 WordPress 后台展示、项目仓库和实际发布身份保持一致。

## 本次更新

- 修正主题头信息中的 `Theme URI`，改为当前仓库地址 `https://github.com/unclecat/WP_Brave`
- 修正主题头信息中的作者信息，统一为 `unclecat` 与作者站点 `https://www.1ink.ink/`
- 更新 README 中的版本徽章、当前稳定版说明和项目作者信息
- 补齐 README 对“关于我们”页面、天气模块和当前页面结构的描述，使文档与现有功能一致

## 关键文件

- `style.css`
- `functions.php`
- `README.md`
- `CHANGELOG.md`
- `RELEASE.md`
- `TEST-REPORT.md`

## 验证结果

- `bash tests/check-theme-simple.sh` 已通过
- `php tests/check-theme.php` 已通过
- 已确认主题头信息与 README 不再残留旧作者、旧占位仓库地址和旧版本号

## 已知限制

- 当前验证仍以主题静态检查与元数据一致性检查为主，未额外执行浏览器自动化回归

## 下载与更新

1. 下载 `brave-love.zip`
2. 解压后上传到 `wp-content/themes/brave-love/`
3. 覆盖原主题文件
4. 清除站点与浏览器缓存

**版本**: 0.7.9  
**发布日期**: 2026-04-03  
**更新日志**: [CHANGELOG.md](./CHANGELOG.md)
