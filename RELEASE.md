# Brave Love v0.7.6

本次是一个补丁版本，重点优化祝福留言页的头像视觉表现。

## 本次更新

- 祝福留言页改用主题内置的卡通头像池，不再显示基于昵称生成的文字头像
- 新增 8 张本地 SVG 卡通头像，整体风格更统一，也更适合恋爱主题的页面氛围
- 头像分配采用稳定随机策略：同一昵称/邮箱组合会固定落在同一张头像上，避免刷新后头像频繁变化
- 同步优化祝福卡片头像尺寸、描边和阴影，提升浅色/深色模式下的观感

## 关键文件

- `assets/images/blessing-avatars/*`
- `inc/helpers.php`
- `page-templates/page-blessing.php`
- `style.css`
- `functions.php`
- `CHANGELOG.md`

## 验证结果

- `php -l inc/helpers.php` 已通过
- `php -l page-templates/page-blessing.php` 已通过
- 本地 Docker WordPress 环境已验证祝福留言页输出本地卡通头像资源
- 祝福留言页测试评论已成功命中 `assets/images/blessing-avatars/avatar-02.svg`

## 已知限制

- 天气模块仍依赖 `Open-Meteo` 实时接口，离线环境下不会返回实时天气数据
- GitHub Release 资产上传仍依赖 tag push 触发 Actions 工作流

## 下载与更新

1. 下载 `brave-love.zip`
2. 解压后上传到 `wp-content/themes/brave-love/`
3. 覆盖原主题文件
4. 清除站点与浏览器缓存

**版本**: 0.7.6  
**发布日期**: 2026-04-02  
**更新日志**: [CHANGELOG.md](./CHANGELOG.md)
