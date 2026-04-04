# Brave Love v1.0.4

Brave Love `v1.0.4` 是一次面向 GitHub 项目页与新用户上手体验的文档版 patch release。

这次发布不改动主题前台交互逻辑，重点是把项目首页说明补齐到“可直接照着搭站”的程度：除了重写 `README.md`，还新增了一份独立的 `docs/USER-GUIDE.md`，专门给站长日常维护使用。

## 本次发布亮点

### 1) 新增独立用户使用手册
- 新增 `docs/USER-GUIDE.md`
- 从站长视角拆解主题怎么安装、页面怎么创建、后台去哪里维护
- 增加日常维护工作流、常见问题和内容分工建议

### 2) GitHub 项目页说明重写
- `README.md` 从功能概览升级为完整使用说明
- 补齐首页、关于我们、点点滴滴、恋爱清单、甜蜜相册、随笔说说、祝福留言的具体使用方法
- 明确记录天气、纪念日、摘要、相册聚合、评论审核等关键逻辑

### 3) 文档版发布收口
- 同步更新主题版本号到 `1.0.4`
- 同步更新发布说明与测试报告，保证 GitHub Release 和仓库文档口径一致

## 重点变更文件

- `style.css`
- `functions.php`
- `README.md`
- `docs/USER-GUIDE.md`
- `CHANGELOG.md`
- `RELEASE.md`
- `TEST-REPORT.md`

## 验证结果

以下检查已在本地执行：

- `php -l functions.php`
- `bash tests/check-theme-simple.sh`
- `php tests/check-theme.php`
- 本地首页 `http://localhost:8080/` 资源版本号校验（确认已切到 `ver=1.0.4`）
- README / 用户手册 / Release / Changelog 版本一致性检查

## 升级说明

如果你正在使用 `v1.0.3`：

1. 覆盖主题文件后，先清除站点缓存与浏览器缓存
2. 到“设置 -> 固定链接”点击一次“保存更改”刷新重写规则
3. 如果你只是想看使用说明，优先查看 `README.md` 和 `docs/USER-GUIDE.md`
4. 这次发布不涉及数据库结构变更，也不需要执行新的迁移步骤

## 发布资产

- `brave-love.zip`
- GitHub Release Notes（当前文件）
- `CHANGELOG.md`
- `TEST-REPORT.md`
- `docs/USER-GUIDE.md`

**版本**: `1.0.4`  
**发布日期**: `2026-04-04`  
**更新日志**: `CHANGELOG.md`
