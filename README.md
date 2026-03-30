# Brave Love 💕

一个浪漫的 WordPress 情侣主题，灵感来源于 Typecho 主题 [Brave](https://github.com/zwying0814/Brave)。

[![Version](https://img.shields.io/badge/version-0.1-blue.svg)](https://github.com/yourname/brave-love)
[![WordPress](https://img.shields.io/badge/WordPress-6.0%2B-green.svg)](https://wordpress.org/)
[![License](https://img.shields.io/badge/license-GPL%20v2-red.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

![Theme Preview](screenshot.png)

## ✨ 功能特性

### 🏠 首页 - 关于我们
- 💕 恋爱正计时（实时显示已相恋天数、时、分、秒）
- 📅 纪念日管理（支持倒计时和正数计时）
- 🎯 快捷入口卡片（5 大模块一键直达）
- 🌊 浪漫的 Hero 区域（背景图、双头像、跳动的爱心、波浪动画）

### 💖 点点滴滴
- 记录每次见面的时间、地点、做的事
- 时间轴展示，按年份筛选
- 支持心情标记和关联相册
- 移动端优化的时间轴布局

### 📜 恋爱清单
- 记录想一起做的 100 件事
- 完成状态标记和进度统计（环形进度图）
- 支持分类筛选（旅行、美食、生活等）
- 完成后可上传纪念照片和记录

### 📷 甜蜜相册
- 按主题/事件管理照片
- 瀑布流展示，支持年份和标签筛选
- PhotoSwipe 5 灯箱，支持手势滑动和缩放
- 每张照片独立展示，可关联相关点滴

### 📝 随笔说说
- 朋友圈式的短内容发布
- 支持心情表情和多图上传（九宫格）
- 点赞评论互动

### 💌 祝福留言
- 访客留言祝福板
- 优化的移动端评论表单
- QQ 邮箱自动识别头像

## 📱 移动优先设计

- 📱 Hero 区手机高度 40vh，头像 64px，波浪 2.5rem
- 📊 时间轴手机端单列左线布局
- 🖼️ 相册瀑布流手机端双列（超小屏单列）
- 👆 所有按钮 44px+ 点击区域
- ⚡ 图片懒加载，节省流量
- 🎯 导航栏滚动后毛玻璃效果

## 🚀 安装方法

### 方法一：下载安装

1. 下载最新版本：[Releases](https://github.com/yourname/brave-love/releases)
2. WordPress 后台 → 外观 → 主题 → 上传主题
3. 启用主题

### 方法二：Git 克隆

```bash
cd wp-content/themes/
git clone https://github.com/yourname/brave-love.git
```

## ⚙️ 配置步骤

### 1. 创建页面

创建以下页面并选择对应模板：

| 页面名称 | 页面模板 | 用途 |
|---------|---------|------|
| 首页 | 首页 - 关于我们 | 显示计时器 + 入口卡片 |
| 点点滴滴 | 点点滴滴 | 记录每次见面 |
| 恋爱清单 | 恋爱清单 | 一起做的事 |
| 甜蜜相册 | 甜蜜相册 | 照片管理 |
| 随笔说说 | 随笔说说 | 心情记录 |
| 祝福留言 | 祝福留言 | 访客留言 |

### 2. 主题设置

进入 **外观 → 自定义 → Brave 主题设置**，配置：

- **基本信息**：恋爱起始日期、导航栏文字
- **Hero 区域**：背景图、情侣头像和昵称
- **纪念日**：后台 → 设置 → 纪念日 → 添加特别日子
- **页面链接**：指定各模块对应的页面

### 3. 固定链接

设置 → 固定链接 → 选择「文章名」→ 保存

## 📝 使用指南

### 添加点滴记录

1. 后台 → 点点滴滴 → 添加点滴
2. 填写标题、内容
3. 设置见面日期、地点、心情
4. 可选择关联已有相册
5. 设置特色图片作为封面

### 添加恋爱清单

1. 后台 → 恋爱清单 → 添加事项
2. 填写想做的事情
3. 完成后勾选「已完成」，设置完成日期
4. 可上传纪念照片和记录感想

### 添加相册

1. 后台 → 甜蜜相册 → 添加相册
2. 填写相册主题和描述
3. 设置拍摄日期和地点
4. 在「照片管理」中上传多张照片（支持拖拽排序）
5. 可选择关联相关点滴

### 添加说说

1. 后台 → 随笔说说 → 添加说说
2. 选择心情表情
3. 填写短内容（无需标题）
4. 可选上传配图（最多9张）

## 🛠️ 开发环境

```bash
# 本地开发推荐
WordPress 6.0+
PHP 7.4+
MySQL 5.7+
```

## 📂 文件结构

```
brave-love/
├── style.css              # 主题样式
├── functions.php          # 核心功能
├── header.php             # 头部模板
├── footer.php             # 页脚模板
├── index.php              # 默认模板
├── single.php             # 文章详情
├── archive.php            # 归档页
├── search.php             # 搜索页
├── 404.php                # 404页面
├── page-templates/        # 页面模板
│   ├── page-home.php
│   ├── page-moments.php
│   ├── page-list.php
│   ├── page-memories.php
│   ├── page-notes.php
│   └── page-blessing.php
├── inc/                   # 功能文件
│   ├── post-types.php     # 自定义文章类型
│   ├── meta-boxes.php     # 自定义字段
│   ├── customizer.php     # 主题设置
│   ├── shortcodes.php     # 短代码
│   └── helpers.php        # 工具函数
├── assets/                # 静态资源
│   ├── css/brave.css
│   ├── js/brave.js
│   └── js/admin.js
└── languages/             # 语言包
```

## 🎨 自定义样式

在「Brave 主题设置 → 自定义代码 → 自定义 CSS」中添加：

```css
/* 修改主色调 */
:root {
    --primary-color: #ff6b81;
}

/* 自定义字体 */
body {
    font-family: 'PingFang SC', 'Microsoft YaHei', sans-serif;
}
```

## 🐛 常见问题

### Q: 计时器显示不正确？
A: 请检查「Brave 主题设置 → 基本信息 → 恋爱起始日期」是否正确设置。

### Q: 相册图片无法上传？
A: 检查 WordPress 上传目录权限，确保 `wp-content/uploads` 可写。

### Q: 固定链接出现 404？
A: 设置 → 固定链接 → 直接点击保存（刷新重写规则）。

### Q: 如何修改入口卡片顺序？
A: 当前版本为固定顺序，后续版本将支持拖拽排序。

## 📋 更新日志

### 0.1.4
- ✨ 新增双计时器功能
- ✨ 恋爱计时器文字可自定义
- ✨ 新增纪念日倒计时（精确到分钟）
- ✨ 倒计时文字、纪念日名称可自定义
- 🎉 倒计时归零显示庆祝信息

### 0.1.1
- 🐛 修复计时器日期解析问题，兼容多种日期格式
- 🐛 修复相册照片管理界面，添加 admin CSS 样式
- 💄 首页显示起始日期提示，方便排查问题

### 0.1
- ✨ 初始版本发布
- 💕 支持 5 大核心模块（点点滴滴、恋爱清单、甜蜜相册、随笔说说、祝福留言）
- 📱 移动优先响应式设计
- 🖼️ PhotoSwipe 5 灯箱集成
- 📅 纪念日倒计时/正计时功能

## 🤝 贡献

欢迎提交 Issue 和 Pull Request！

## 📄 许可证

GPL v2 or later

## 🙏 致谢

- 灵感来源于 [Brave Typecho Theme](https://github.com/zwying0814/Brave) by Veen Zhao
- [Bootstrap](https://getbootstrap.com/)
- [PhotoSwipe](https://photoswipe.com/)

---

💕 用 Brave Love 记录你们的爱情故事
