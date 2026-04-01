# Brave Love v0.6.25 发布说明

## ✨ 本次更新

### 🐛 修复页面Hero背景图浅色模式显示问题（关键修复）
- **关键修复**: 给 `.page-hero-section` 添加 `isolation: isolate` 创建新的层叠上下文
- **避免负z-index问题**: 使用非负z-index (0, 1, 3) 替代负z-index (-2, -1)
- **问题原因**: 负z-index元素会被body的背景色覆盖（仅在浅色模式下明显，因为body背景是浅灰色）
- **新增**: Hero测试页面 (page-test-hero.php) 用于诊断显示问题

---

**版本**: 0.6.25  
**发布日期**: 2026-03-30  
**兼容性**: WordPress 6.0+, PHP 7.4+
