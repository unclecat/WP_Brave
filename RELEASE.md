# Brave Love v0.7.4 发布说明

> 维护说明
> 发布新版本时，优先只修改以下内容：
> 1. 标题中的版本号
> 2. `版本概述`
> 3. `主要变更`
> 4. `主要文件变更`
> 5. `测试结果`
> 6. 文末的版本号和发布日期
> 其余结构尽量保持不变，这样 GitHub Release 文案会长期稳定。

## 版本概述

本次发布是一个补丁版本，重点修复相册与时间线的年份筛选一致性问题，补上相册缓存机制，并将主题剩余的静态外链资源本地化。

## 主要变更

### 1. 点点滴滴与相册年份逻辑修复
- 修复“点点滴滴”年份导航原先仅做页面锚点跳转、不能真正按年份筛选的问题
- 统一“甜蜜相册”年份筛选、展示日期、年份列表的取值规则：优先 `_meet_date`，缺失时回退 `post_date`
- 修复跨年或缺少 `_meet_date` 的点滴内容在年份筛选中缺失或归类错误的问题

### 2. 相册性能优化
- 为相册聚合结果增加 transient 缓存，减少每次打开相册页时的全量遍历开销
- 为单篇 `moment` 的照片提取结果增加 post meta 缓存，并在文章、附件、发布状态变化时自动失效
- 保持相册页在数据量上升后的响应更稳定

### 3. 前端资源本地化与可用性改进
- 将 Inter 字体、Bootstrap、PhotoSwipe 改为主题内本地资源加载，减少对静态 CDN 的依赖
- 首页 Hero 默认背景移除 Unsplash 外链，改为主题内渐变兜底
- 移除 viewport 中的 `user-scalable=no`，恢复移动端缩放能力

### 4. 头像外链清理
- 将情侣头像、普通用户头像、评论头像的兜底方案统一为主题内联 SVG 占位头像
- 移除主题对 `ui-avatars.com`、`qlogo.cn` 等第三方头像服务的依赖
- 保留天气模块对 `Open-Meteo` 的实时数据请求，不影响天气功能

## 主要文件变更

| 文件 | 变更类型 | 说明 |
|------|----------|------|
| `inc/helpers.php` | 修改 | 统一点滴/相册日期规则，增加相册缓存与头像本地兜底能力 |
| `page-templates/page-moments.php` | 修改 | 将年份导航改为真实年份筛选，并接入本地头像逻辑 |
| `page-templates/page-memories.php` | 修改 | 相册年份筛选与分页逻辑统一到有效日期口径 |
| `functions.php` | 修改 | 本地化 Bootstrap / PhotoSwipe 加载，更新版本号与评论头像逻辑 |
| `header.php` | 修改 | 首页默认 Hero 背景改为本地渐变兜底 |
| `assets/css/fonts.css` | 新增 | 接入本地 Inter 字体 |
| `assets/fonts/inter/*` | 新增 | 收录 Inter 字体文件与许可证 |
| `assets/vendor/bootstrap/*` | 新增 | 收录 Bootstrap 本地发行文件 |
| `assets/vendor/photoswipe/*` | 新增 | 收录 PhotoSwipe 本地发行文件 |

## 测试结果

### 已完成
- `php -l` 已通过全部 24 个 PHP 文件语法检查
- `node --check` 已通过 `assets/js/brave.js` 与 `assets/js/memory.js`
- 本地 Docker WordPress 环境已完成运行级验证：首页、点点滴滴、甜蜜相册、随笔说说、恋爱清单均可正常访问
- 已验证“点点滴滴”按年份筛选生效，`_meet_date` 缺失时会正确回退到发布日期
- 已验证“甜蜜相册”按年份筛选时会正确显示回退日期，且缓存可自动失效并重建
- 已验证首页与相册页输出的 Bootstrap / PhotoSwipe / 字体资源均为本地路径
- 已验证主题页面不再输出 `jsDelivr`、`Google Fonts`、`Unsplash`、`ui-avatars`、`qlogo.cn`、`Gravatar` 等静态外链头像/资源

### 当前阻塞
- GitHub Release 资产上传仍依赖 tag push 触发 Actions 工作流，是否成功还取决于远端 Actions 配置是否正常

## 已知限制

1. 天气模块仍依赖 `Open-Meteo` 实时接口，离线环境下不会返回实时天气数据。
2. GitHub Release 资产上传依赖 tag push 触发 Actions 工作流，是否成功还取决于远端 Actions 配置是否正常。

## 安装与更新

### 直接覆盖更新
1. 下载 `brave-love.zip`
2. 解压后通过 FTP 上传到 `wp-content/themes/brave-love/`
3. 覆盖所有文件
4. 清除浏览器缓存

### 注意事项
- 主题文件夹名称必须是 `brave-love`
- 主题设置和数据通常会保留
- 建议更新前备份当前主题

## 系统要求

- WordPress 6.0+
- PHP 7.4+
- 推荐 PHP 8.0+

**版本**: 0.7.4  
**发布日期**: 2026-04-02  
**更新日志**: [CHANGELOG.md](./CHANGELOG.md)
