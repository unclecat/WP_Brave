# Brave Love v0.7.2 发布说明

## 🎯 版本概述

本次发布是一个补丁版本，重点修复祝福留言页的数据范围错误，以及点点滴滴年份统计的查询范围问题。

---

## ✨ 主要变更

### 1. 祝福留言页修复
- 留言列表现在只显示当前“祝福留言”页面自己的评论
- 原先无实现的“加载更多”按钮改为可用的分页
- 评论头像改为走 `brave_get_avatar_url()`，QQ 邮箱头像支持生效

### 2. 点点滴滴年份统计修复
- 年份聚合查询现在限制为 `moment` 类型且仅统计已发布文章
- 避免其它文章类型中带有 `_meet_date` 元数据时污染年份导航

### 3. 发布与测试
- 保持 `tests/check-theme-simple.sh` 静态检查可通过
- 重新生成 `brave-love` 安装包

---

## 📁 主要文件变更

| 文件 | 变更类型 | 说明 |
|------|----------|------|
| `page-templates/page-blessing.php` | 修改 | 修复评论范围、分页和头像逻辑 |
| `inc/helpers.php` | 修改 | 修复点点滴滴年份统计查询范围 |
| `style.css` | 修改 | 更新版本号 |
| `functions.php` | 修改 | 更新版本号 |

---

## 🧪 测试结果

### 已完成
- ✅ `bash tests/check-theme-simple.sh` 通过
- ✅ 主题目录、必需文件、版本头信息检查通过
- ✅ 静态安全扫描未发现运行时危险函数
- ✅ 祝福留言页评论范围逻辑已收敛到当前页面
- ✅ 点点滴滴年份查询已限制到 `moment` 已发布内容

### 当前阻塞
- ⚠️ 本机未安装 `php`
- ⚠️ 本机未安装 `docker`
- ⚠️ 本机未安装 `docker-compose`
- ⚠️ 因此未完成真实 WordPress 运行级测试

---

## ⚠️ 已知限制

1. 当前机器缺少 `php`、`docker`、`docker-compose`，无法在本地完成端到端 WordPress 运行测试。
2. GitHub Release 资产上传依赖 tag push 触发 Actions 工作流，是否成功还取决于远端 Actions 配置是否正常。

---

## 📝 更新建议

### 直接覆盖更新（推荐）
1. 下载 `brave-love.zip`
2. 解压后通过FTP上传到 `wp-content/themes/brave-love/`
3. 覆盖所有文件
4. 清除浏览器缓存

### 注意事项
- 主题文件夹名称必须是 `brave-love`
- 所有主题设置数据会保留
- 建议更新前备份当前主题

---

## 📊 系统要求

- WordPress 6.0+
- PHP 7.4+
- 推荐 PHP 8.0+

---

**版本**: 0.7.2  
**发布日期**: 2026-04-01  
**更新日志**: [CHANGELOG.md](./CHANGELOG.md)
