# Brave Love v0.2.1 发布说明

## 🔒 安全修复版

本次更新修复了一处安全漏洞，建议所有用户升级。

---

## 🐛 修复内容

### 安全漏洞修复

**问题**: Meta Box 中 ID 输出未转义  
**风险**: 低危 XSS 漏洞  
**文件**: `inc/meta-boxes.php`  
**行号**: 114, 192

**修复前**:
```php
<option value="<?php echo $memory->ID; ?>">
<option value="<?php echo $moment->ID; ?>">
```

**修复后**:
```php
<option value="<?php echo intval($memory->ID); ?>">
<option value="<?php echo intval($moment->ID); ?>">
```

**影响范围**: 后台编辑点滴/相册时的关联选择框

---

## 📊 安全测试报告

- **总体评分**: 85/100 (良好)
- **SQL 注入**: ✅ 已防护
- **XSS 攻击**: ✅ 已防护 (修复后)
- **CSRF 攻击**: ✅ 已防护
- **数据验证**: ✅ 已实施

详细报告见: [SECURITY-REPORT.md](https://github.com/unclecat/WP_Brave/blob/main/SECURITY-REPORT.md)

---

## 📁 新增文件

| 文件 | 说明 |
|------|------|
| `SECURITY-REPORT.md` | 完整安全测试报告 |
| `tests/security-scan.php` | 安全扫描工具脚本 |

---

## 💾 升级方法

1. 备份当前主题
2. 下载 `brave-love.zip`
3. 删除旧版本主题
4. 上传新版本并激活

---

## 📋 版本历史

- **v0.2.1** (2026-03-30) - 安全修复
- **v0.2.0** (2026-03-30) - 正式版发布
- **v0.1.x** - 开发测试版

---

**建议**: 所有使用 v0.2.0 及以下版本的用户建议升级到此版本。
