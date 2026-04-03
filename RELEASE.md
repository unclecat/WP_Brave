# Brave Love v1.0.1

Brave Love `v1.0.1` 是一次聚焦“关于我们”页面故事时间线摘要体验的 patch release。

这次更新不改动页面结构和时间线排序，只修正摘要来源：以后故事节点卡片只显示编辑器中的原生摘要，未填写摘要时前台不再从正文自动截取一段内容。

## 本次发布亮点

### 1) 关于我们时间线摘要逻辑修复
- 故事节点卡片改为读取 WordPress 原生 `post_excerpt`
- 当编辑器摘要为空时，前台不再自动从正文生成默认摘要
- 时间线卡片的摘要展示与后台编辑器输入保持一一对应

### 2) 后台编辑入口统一
- 为 `story_milestone` 启用 WordPress 原生摘要支持
- 移除旧的自定义“一句话摘要”字段，避免后台同时存在两套摘要来源
- 正式环境后续维护时，直接使用编辑器摘要面板即可

### 3) 版本与发布收口
- 更新 `style.css` 与 `functions.php` 中的版本号到 `1.0.1`
- 更新 `README.md`、`CHANGELOG.md`、`RELEASE.md` 与 `TEST-REPORT.md`
- 保持 GitHub tag 发版流程与现有 `main` 分支发布方式一致

## 重点变更文件

- `style.css`
- `functions.php`
- `inc/post-types.php`
- `inc/meta-boxes.php`
- `page-templates/page-about.php`
- `README.md`
- `CHANGELOG.md`
- `RELEASE.md`
- `TEST-REPORT.md`

## 验证结果

以下检查已在本地执行：

- `find . -name '*.php' -not -path './tests/*' -print0 | xargs -0 -n1 php -l`
- `php tests/check-theme.php`
- `php tests/security-scan.php`
- `bash tests/check-theme-simple.sh`
- 本地 WordPress `http://localhost:8080/about-us/` 页面验证
- 临时写入 / 清空故事节点 `post_excerpt`，确认摘要“有值时显示、为空时隐藏”

## 升级说明

如果你正在使用 `v1.0.0`：

1. 覆盖主题文件后，先清除站点缓存与浏览器缓存
2. 到“设置 -> 固定链接”点击一次“保存更改”刷新重写规则
3. 以后维护“关于我们”故事节点摘要时，请直接使用编辑器自带的“摘要”面板
4. 如后台没有显示摘要面板，可在编辑器右上角的偏好设置中开启对应面板

## 发布资产

- `brave-love.zip`
- GitHub Release Notes（当前文件）
- `CHANGELOG.md`
- `TEST-REPORT.md`

**版本**: `1.0.1`  
**发布日期**: `2026-04-04`  
**更新日志**: `CHANGELOG.md`
