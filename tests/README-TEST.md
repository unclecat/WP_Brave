# Brave Love 本地测试指南

## 📦 测试环境文件

已为你准备以下测试工具：

| 文件 | 用途 |
|------|------|
| `docker-compose.yml` | Docker 环境配置 |
| `test-theme.sh` | 一键启动测试环境 |
| `setup-test-data.sh` | 创建测试数据 |
| `check-theme-simple.sh` | 主题代码检查 |
| `check-theme.php` | PHP 语法检查（需安装 PHP） |

---

## 🚀 快速开始（推荐）

### 前置要求

- [Docker](https://docs.docker.com/get-docker/)
- [Docker Compose](https://docs.docker.com/compose/install/)

### 1. 检查主题（可选）

```bash
bash tests/check-theme-simple.sh
```

这会检查：
- ✅ 文件完整性
- ✅ 代码统计
- ✅ 安全性检查
- ✅ 国际化检查

### 2. 启动 WordPress 环境

```bash
bash tests/test-theme.sh
```

此脚本会：
- 检查 Docker 是否安装
- 检查主题文件完整性
- 启动 WordPress + MySQL + phpMyAdmin
- 显示访问地址和安装步骤

**访问地址：**
- WordPress: http://localhost:8080
- phpMyAdmin: http://localhost:8081

### 3. 完成 WordPress 安装

1. 访问 http://localhost:8080
2. 选择语言 → 「简体中文」
3. 填写信息：
   - 站点标题: `我们的小窝`
   - 用户名: `admin`
   - 密码: `admin123`（或自定义）
   - 邮箱: `admin@example.com`
4. 点击「安装 WordPress」
5. 登录后台

### 4. 启用主题

1. 外观 → 主题
2. 找到「Brave Love」
3. 点击「启用」

### 5. 创建测试数据

```bash
bash tests/setup-test-data.sh
```

此脚本会自动：
- 创建 6 个必需页面
- 设置首页
- 添加 3 条点滴记录
- 添加 5 条清单事项
- 添加 3 条说说
- 添加 1 个相册

---

## 📱 测试清单

### 基础功能测试

- [ ] 首页显示正常（计时器、纪念日、入口卡片）
- [ ] 计时器实时更新
- [ ] 点击入口卡片跳转到对应页面
- [ ] 导航栏滚动有毛玻璃效果

### 点点滴滴
- [ ] 时间轴显示正常
- [ ] 按年份筛选工作
- [ ] 心情表情显示
- [ ] 点击查看详情

### 恋爱清单
- [ ] 进度圆环显示正确
- [ ] 已完成/未完成区分
- [ ] 点击展开详情
- [ ] 分类筛选（如有分类）

### 甜蜜相册
- [ ] 瀑布流布局
- [ ] 点击图片打开 PhotoSwipe 灯箱
- [ ] 手势滑动切换图片
- [ ] 双指缩放图片

### 随笔说说
- [ ] 卡片式布局
- [ ] 心情徽章显示
- [ ] 思念度显示
- [ ] 分页功能

### 祝福留言
- [ ] 评论表单显示
- [ ] 提交祝福成功
- [ ] QQ 邮箱头像识别

### 响应式测试
- [ ] Chrome DevTools → iPhone 12 Pro
- [ ] 检查移动端布局
- [ ] 测试触摸操作

---

## 🛠️ 常用命令

```bash
# 启动环境
docker-compose -f tests/docker-compose.yml up -d

# 停止环境
docker-compose -f tests/docker-compose.yml down

# 重启环境
docker-compose -f tests/docker-compose.yml restart

# 查看日志
docker-compose -f tests/docker-compose.yml logs -f wordpress

# 重置所有数据（包括数据库）
docker-compose -f tests/docker-compose.yml down -v

# 进入 WordPress 容器
docker exec -it brave_wp_app bash

# 使用 WP-CLI
docker run -it --rm \
    --volumes-from brave_wp_app \
    --network wordpress_default \
    wordpress:cli wp --allow-root
```

---

## 🔧 手动配置（如果脚本失败）

### 创建页面

后台 → 页面 → 新建页面：

| 页面标题 | 模板 | 用途 |
|---------|------|------|
| 首页 | 首页 - 关于我们 | 计时器 + 入口 |
| 点点滴滴 | 点点滴滴 | 时间轴记录 |
| 恋爱清单 | 恋爱清单 | 100件事 |
| 甜蜜相册 | 甜蜜相册 | 照片管理 |
| 随笔说说 | 随笔说说 | 心情记录 |
| 祝福留言 | 祝福留言 | 留言板 |

### 设置首页

设置 → 阅读 → 首页显示：
- 选择「一个静态页面」
- 首页：选择「首页」

### 配置主题

外观 → 自定义 → Brave 主题设置：
1. **基本信息**：设置恋爱起始日期
2. **Hero 区域**：上传背景图和头像
3. **纪念日**：后台 → 设置 → 纪念日 → 添加
4. **页面链接**：选择各模块对应页面

---

## 🐛 故障排除

### 端口被占用

编辑 `docker-compose.yml`，修改端口映射：

```yaml
wordpress:
  ports:
    - "8082:80"  # 改为 8082

phpmyadmin:
  ports:
    - "8083:80"  # 改为 8083
```

### 权限错误

```bash
# Linux/Mac
sudo chown -R $USER:$USER brave-wp/

# 或给 Docker 权限
sudo chmod 777 wp_data/ db_data/
```

### 样式不生效

检查浏览器开发者工具：
1. Network 面板 → 查看 CSS 是否 404
2. 确认主题文件夹名称为 `brave-love`

### CPT 404 错误

设置 → 固定链接 → 直接点击「保存更改」

---

## 📝 导出生产环境

测试完成后，可以导出数据库用于生产环境：

```bash
# 导出数据库
docker exec brave_wp_db mysqldump -u wordpress -pwordpress wordpress > brave-love-export.sql

# 打包主题
cd brave-wp && zip -r ../brave-love-prod.zip . -x ".git/*" -x ".DS_Store"
```

---

## 🎯 性能测试

```bash
# 查看容器资源使用
docker stats

# 检查日志中的慢查询
docker-compose -f tests/docker-compose.yml logs -f db | grep -i slow
```

---

## 📚 参考

- [WordPress Docker 官方镜像](https://hub.docker.com/_/wordpress)
- [Docker Compose 文档](https://docs.docker.com/compose/)
- [WP-CLI 文档](https://wp-cli.org/)

---

**测试完成后，主题就可以发布到生产环境了！** 🚀
