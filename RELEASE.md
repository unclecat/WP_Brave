# Brave Love v1.0.6

Brave Love `v1.0.6` 是一次聚焦 `恋爱清单` 排序体验的 patch release。

这次更新把你刚提出的两个点一起收口了：前台 `恋爱清单` 页面不再是“无明显排序”，而是改成 `待完成在前、已完成在后`；已完成部分再按 `完成日期` 从近到远排列。后台 `恋爱清单` 管理列表也同步默认使用同样的排序逻辑，打开后台就能先看到还没完成的事项。

## 本次发布亮点

### 1) 恋爱清单前台排序更符合使用场景
- 待完成事项自动排在最前
- 已完成事项自动排在后面
- 已完成事项按完成日期倒序排列，越近完成的越靠前

### 2) 后台维护列表同步同一逻辑
- 后台 `恋爱清单` 列表默认优先展示待完成事项
- 已完成事项同样按完成日期从近到远排列
- 如果后台手动点击其他排序列，仍然尊重后台显式排序

### 3) 文档与版本信息同步更新
- 更新 `README.md` 的恋爱清单说明与当前版本号
- 更新 `docs/USER-GUIDE.md` 的恋爱清单使用说明
- 同步更新 `CHANGELOG.md`、`TEST-REPORT.md` 与主题版本元信息

## 重点变更文件

- `functions.php`
- `style.css`
- `README.md`
- `docs/USER-GUIDE.md`
- `CHANGELOG.md`
- `RELEASE.md`
- `TEST-REPORT.md`

## 验证结果

以下检查已在本地执行：

- `php -l functions.php`
- 本地 WordPress Docker 环境中验证恋爱清单前台主列表排序
- 本地 WordPress Docker 环境中验证恋爱清单后台默认列表排序
- 验证后台显式 `orderby=title` 时不会被默认排序逻辑覆盖

## 升级说明

如果你正在使用 `v1.0.5`：

1. 覆盖主题文件后，先清除站点缓存与浏览器缓存
2. 恋爱清单无需额外迁移数据，已有 `_is_done` 和 `_done_date` 会直接参与新排序
3. 如果某条已完成事项没填完成日期，它仍会排在已完成分组里，但会落在有完成日期的事项后面

## 发布资产

- `brave-love.zip`
- GitHub Release Notes（当前文件）
- `CHANGELOG.md`
- `TEST-REPORT.md`
- `docs/USER-GUIDE.md`

**版本**: `1.0.6`  
**发布日期**: `2026-04-06`  
**更新日志**: `CHANGELOG.md`
