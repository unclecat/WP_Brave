# 本地测试指南

## 环境要求

- PHP 7.4+
- MySQL 5.7+ 或 MariaDB 10.3+
- WordPress 6.0+

---

## 方法一：使用 Local WP（推荐）

[Local WP](https://localwp.com/) 是免费的 WordPress 本地开发工具，支持 Windows/Mac/Linux。

### 安装步骤

1. 下载并安装 [Local WP](https://localwp.com/)
2. 创建新站点：
   - 点击「+」→「Create a new site」
   - 站点名称：`brave-love-test`
   - 环境选择：Preferred（推荐）
   - WordPress 用户名/密码：自定义
3. 启动站点
4. 打开站点文件夹（右键站点 → Reveal in Finder/Explorer）
5. 进入 `app/public/wp-content/themes/`
6. 将 `brave-wp` 文件夹复制到这里
7. WordPress 后台 → 外观 → 主题 → 启用「Brave Love」

---

## 方法二：使用 MAMP/XAMPP

### macOS (MAMP)

1. 下载并安装 [MAMP](https://www.mamp.info/)
2. 启动 MAMP，点击「Start Servers」
3. 打开 `/Applications/MAMP/htdocs/`
4. 下载 [WordPress](https://wordpress.org/download/) 并解压到这里
5. 将 `brave-wp` 文件夹复制到 `wordpress/wp-content/themes/`
6. 访问 `http://localhost:8888/wordpress`
7. 完成 WordPress 安装向导
8. 启用「Brave Love」主题

### Windows (XAMPP)

1. 下载并安装 [XAMPP](https://www.apachefriends.org/)
2. 启动 XAMPP Control Panel
3. 启动 Apache 和 MySQL
4. 打开 `C:\xampp\htdocs\`
5. 下载 WordPress 并解压到这里
6. 将 `brave-wp` 文件夹复制到 `wordpress/wp-content/themes/`
7. 访问 `http://localhost/wordpress`
8. 完成 WordPress 安装
9. 启用「Brave Love」主题

---

## 方法三：使用 Docker

如果你熟悉 Docker，可以使用 `wordpress` 官方镜像：

```bash
# 创建 docker-compose.yml
cat > docker-compose.yml << 'EOF'
version: '3'

services:
  db:
    image: mysql:5.7
    volumes:
      - db_data:/var/lib/mysql
    environment:
      MYSQL_ROOT_PASSWORD: root
      MYSQL_DATABASE: wordpress
      MYSQL_USER: wordpress
      MYSQL_PASSWORD: wordpress

  wordpress:
    image: wordpress:latest
    ports:
      - "8080:80"
    volumes:
      - ./brave-wp:/var/www/html/wp-content/themes/brave-wp
    environment:
      WORDPRESS_DB_HOST: db:3306
      WORDPRESS_DB_USER: wordpress
      WORDPRESS_DB_PASSWORD: wordpress
      WORDPRESS_DB_NAME: wordpress

volumes:
  db_data:
EOF

# 启动
docker-compose up -d

# 访问 http://localhost:8080
```

---

## 方法四：使用 PHP 内置服务器（简单测试）

```bash
# 进入主题目录
cd brave-wp

# 启动 PHP 内置服务器
php -S localhost:8000

# 访问 http://localhost:8000
```

注意：此方法仅用于预览静态文件，无法测试 WordPress 功能。

---

## 测试清单

安装完成后，按以下顺序测试：

### 1. 基础配置 ✅
- [ ] 设置 → 固定链接 → 保存
- [ ] 外观 → Brave 主题设置 → 填写情侣信息
- [ ] 设置 → 纪念日 → 添加测试纪念日

### 2. 创建测试页面 ✅
- [ ] 创建「首页」，选择「首页 - 关于我们」模板
- [ ] 创建「点点滴滴」，选择「点点滴滴」模板
- [ ] 创建「恋爱清单」，选择「恋爱清单」模板
- [ ] 创建「甜蜜相册」，选择「甜蜜相册」模板
- [ ] 创建「随笔说说」，选择「随笔说说」模板
- [ ] 创建「祝福留言」，选择「祝福留言」模板
- [ ] 在主题设置中链接各页面

### 3. 添加测试内容 ✅
- [ ] 添加 3-5 条「点点滴滴」记录
- [ ] 添加 10 条「恋爱清单」事项（部分标记完成）
- [ ] 创建 2-3 个「甜蜜相册」，上传照片
- [ ] 发布 5 条「随笔说说」，测试九宫格图片
- [ ] 在祝福留言页面发送测试留言

### 4. 功能测试 ✅
- [ ] 首页计时器实时更新
- [ ] 纪念日倒计时/正计时正确显示
- [ ] 点击入口卡片跳转正确
- [ ] 时间轴按年份筛选正常
- [ ] 恋爱清单进度圆环显示正确
- [ ] 相册瀑布流加载正常
- [ ] PhotoSwipe 灯箱可正常打开图片
- [ ] 评论功能正常（QQ 头像识别）

### 5. 响应式测试 ✅
- [ ] Chrome DevTools → 切换 iPhone 12 Pro 尺寸
- [ ] 检查移动端布局是否正常
- [ ] 测试触摸滑动（相册灯箱）
- [ ] 测试返回顶部按钮
- [ ] 切换 iPad 尺寸测试平板布局

### 6. 浏览器测试 ✅
- [ ] Chrome（桌面 + 手机模式）
- [ ] Safari（Mac/iOS）
- [ ] Firefox
- [ ] Edge

---

## 常见问题排查

### 白屏/500错误
```bash
# 查看 PHP 错误日志
tail -f /Applications/MAMP/logs/php_error.log
# 或
tail -f /var/log/apache2/error.log
```

### 样式加载失败
- 检查主题文件夹名称是否为 `brave-wp`
- 检查网络面板是否有 404 错误

### 图片上传失败
```bash
# 检查目录权限
chmod 755 wp-content/uploads
chown -R www-data:www-data wp-content/uploads
```

### CPT 404 错误
- 设置 → 固定链接 → 保存（刷新重写规则）

---

## 导出生产环境

测试完成后，可以导出数据库和文件用于生产环境：

```bash
# 导出数据库（使用 Local WP 或 phpMyAdmin）
mysqldump -u root -p wordpress > brave-love-backup.sql

# 打包主题
cd wp-content/themes/
zip -r brave-love-v0.1.zip brave-wp/
```
