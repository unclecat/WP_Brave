# Brave Love v0.7.1 发布说明

## 🎯 版本概述

本次发布是一个稳定性补丁版本，重点修复主题测试工具、前台发布权限边界、相册年份筛选和恋爱计时器配置读取问题，同时整理 GitHub 发布产物。

---

## ✨ 主要变更

### 1. 测试工具修复
- 修复 `tests/check-theme-simple.sh`、`tests/test-theme.sh`、`tests/setup-test-data.sh` 的路径假设
- 修复 `tests/docker-compose.yml` 的主题挂载路径
- 更新 `tests/README-TEST.md` 使说明与当前仓库结构一致

### 2. 前台发布权限修复
- 前台发布随笔说说时增加发布能力检查
- 限制没有发布权限的已登录用户直接写入公开内容
- 发布完成后改为 `wp_safe_redirect`

### 3. 功能修复
- 修复甜蜜相册年份筛选参数不生效的问题
- 修复恋爱天数和恋爱计时器短代码读取旧配置键的问题
- Meta Box 保存流程增加 autosave / revision / capability 保护

### 4. 发布与测试
- 新增当前测试覆盖报告 `TEST-REPORT.md`
- 生成干净的 `brave-love` 主题安装包

---

## 📁 主要文件变更

| 文件 | 变更类型 | 说明 |
|------|----------|------|
| `functions.php` | 修改 | 修复版本号、前台发布权限和安全重定向 |
| `inc/helpers.php` | 修改 | 增加恋爱起始时间兼容读取，修复相册年份筛选 |
| `inc/meta-boxes.php` | 修改 | 增加保存边界保护 |
| `inc/shortcodes.php` | 修改 | 统一读取恋爱起始时间 |
| `tests/*` | 修改 | 修复测试路径、挂载路径和测试说明 |
| `TEST-REPORT.md` | 新增 | 当前测试覆盖报告 |

---

## 🧪 测试结果

### 已完成
- ✅ `bash tests/check-theme-simple.sh` 通过
- ✅ 主题目录、必需文件、版本头信息检查通过
- ✅ 静态安全扫描未发现运行时危险函数
- ✅ 国际化 `Text Domain` 使用检查通过
- ✅ `tests/test-theme.sh` 现在会正确停在环境依赖检查，而不是因路径错误失败

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

**版本**: 0.7.1  
**发布日期**: 2026-04-01  
**更新日志**: [CHANGELOG.md](./CHANGELOG.md)
