# Brave Love 发布说明

## v0.1.1 (2024-03-30)

### 🐛 Bug 修复

#### 1. 修复计时器日期解析问题
- **问题**: 日期格式为 `YYYY/MM/DD` 时，计时器显示错误
- **修复**: 改进日期解析逻辑，自动兼容多种格式（`YYYY-MM-DD` 和 `YYYY/MM/DD`）
- **优化**: 添加日期有效性检查，无效日期时显示友好提示

#### 2. 修复相册照片管理界面
- **问题**: 新建相册后没有显示照片管理界面
- **修复**: 新增 `admin.css` 样式文件
- **新增**: 
  - 网格布局展示已上传照片
  - 拖拽排序功能
  - 删除按钮（×）
  - 空状态提示

#### 3. 首页显示优化
- 显示起始日期，方便用户确认设置是否正确
- 未设置日期时显示红色提示信息

### 📦 更新内容
```
assets/css/admin.css       # 新增：后台管理样式
assets/js/brave.js         # 修改：日期解析逻辑
page-templates/page-home.php # 修改：显示日期提示
inc/meta-boxes.php         # 修改：加载 admin 样式
```

---

## v0.1 (2024-03-30)

### ✨ 初始版本发布

#### 核心功能
- 💕 **首页/关于我们** - 恋爱计时器、纪念日管理、快捷入口
- 💖 **点点滴滴** - 时间轴记录每次见面
- 📜 **恋爱清单** - 100件事进度追踪
- 📷 **甜蜜相册** - 瀑布流照片管理
- 📝 **随笔说说** - 朋友圈式短内容
- 💌 **祝福留言** - 访客留言板

#### 设计特性
- 📱 移动优先响应式设计
- 🌊 SVG 波浪动画 + 跳动爱心
- 🖼️ PhotoSwipe 5 图片灯箱
- 📅 纪念日倒计时/正计时
- 📊 恋爱清单环形进度图
- 🎨 CSS 变量支持自定义主题色

#### 技术栈
- WordPress 6.0+
- Bootstrap 5.3
- PhotoSwipe 5
- 4 个自定义文章类型 (CPT)
- WordPress Customizer 主题设置

---

## 更新方法

### 自动更新（Git）
```bash
cd wp-content/themes/brave-love
git pull origin main
```

### 手动更新
1. 下载 `brave-love-v0.1.1.zip`
2. 解压覆盖 `wp-content/themes/brave-love/` 目录
3. 清除浏览器缓存（Ctrl+F5 / Cmd+Shift+R）

---

## 系统要求
- WordPress 6.0+
- PHP 7.4+
- MySQL 5.7+ / MariaDB 10.3+
