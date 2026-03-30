# Brave Love 更新指南

## ⚠️ 重要提示

主题文件夹名称必须是 `brave-love`，如果名称不同（如 `brave-wp`、`WP_Brave-main` 等），WordPress 会将其识别为不同主题，导致出现重复主题或无法覆盖更新。

---

## 🔄 更新方法（推荐）

### 方法 1：直接覆盖（最简单）

1. **下载新版 ZIP**
   ```
   https://github.com/unclecat/WP_Brave/releases/download/v0.1.1/brave-love-v0.1.1.zip
   ```

2. **解压到本地**
   ```bash
   unzip brave-love-v0.1.1.zip
   # 确保解压后是 brave-love/ 文件夹
   ```

3. **通过 FTP/SFTP 覆盖**
   - 连接到服务器
   - 进入 `wp-content/themes/`
   - 上传 `brave-love/` 文件夹，**覆盖**原有文件
   - 注意：不要改文件夹名称！

4. **清除缓存**
   - 刷新浏览器缓存（Ctrl+F5 / Cmd+Shift+R）
   - 如果有缓存插件，清除插件缓存

---

### 方法 2：WordPress 后台更新

**注意**：此方法可能因文件夹名称问题导致出现两个主题

1. 外观 → 主题
2. 停用「Brave Love」主题（切换到其他主题）
3. **删除旧版主题**（谨慎操作，确保已备份）
4. 外观 → 主题 → 上传主题
5. 选择 `brave-love-v0.1.1.zip`
6. 启用主题
7. 重新配置主题设置

---

### 方法 3：Git 更新（开发者推荐）

```bash
cd wp-content/themes/brave-love
git pull origin main
```

---

## 🗑️ 清理重复主题

如果已经出现了两个主题：

1. **外观 → 主题**
2. 识别哪个是旧版本：
   - 查看版本号（主题卡片上会显示）
   - 或者查看最后更新时间
3. **停用旧版本**
4. **删除旧版本**：
   - 点击旧版本主题
   - 右下角点击「删除」
5. 确保保留的文件夹名称为 `brave-love`

---

## 📁 正确的文件夹结构

```
wp-content/
└── themes/
    └── brave-love/           ← 必须是这个名称
        ├── style.css
        ├── functions.php
        ├── index.php
        ├── ...
```

**错误的文件夹名称**：
- ❌ `brave-wp/`
- ❌ `WP_Brave-main/`
- ❌ `WP_Brave/`
- ❌ `brave-love-0.1.1/`
- ❌ `brave-love-master/`

---

## 🔧 自动更新脚本（高级用户）

创建一个 `update-theme.sh` 脚本：

```bash
#!/bin/bash
# Brave Love 主题更新脚本

THEME_DIR="/var/www/html/wp-content/themes/brave-love"
TEMP_DIR="/tmp/brave-love-update"
BACKUP_DIR="/tmp/brave-love-backup-$(date +%Y%m%d)"

echo "🔄 开始更新 Brave Love 主题..."

# 备份当前主题
echo "📦 备份当前主题..."
cp -r "$THEME_DIR" "$BACKUP_DIR"

# 下载最新版本
echo "⬇️  下载最新版本..."
rm -rf "$TEMP_DIR"
mkdir -p "$TEMP_DIR"
cd "$TEMP_DIR"
wget -O brave-love.zip https://github.com/unclecat/WP_Brave/releases/download/v0.1.1/brave-love-v0.1.1.zip

# 解压
echo "📂 解压文件..."
unzip -q brave-love.zip

# 检查文件夹名称
if [ -d "brave-love" ]; then
    echo "✅ 文件夹名称正确"
else
    echo "❌ 文件夹名称错误，请检查 ZIP 包"
    exit 1
fi

# 覆盖文件
echo "📝 覆盖文件..."
rsync -av --delete "$TEMP_DIR/brave-love/" "$THEME_DIR/"

# 清理
echo "🧹 清理临时文件..."
rm -rf "$TEMP_DIR"

echo "✅ 更新完成！"
echo "📂 备份位置: $BACKUP_DIR"
echo "🔄 请刷新浏览器缓存查看效果"
```

使用方法：
```bash
chmod +x update-theme.sh
./update-theme.sh
```

---

## 🐛 常见问题

### Q: 上传后出现了两个「Brave Love」主题？
**A**: 这是因为文件夹名称不同。请删除旧版本，保留文件夹名称为 `brave-love` 的那个。

### Q: 更新后设置丢失了？
**A**: 主题设置存储在数据库中，正常更新不会丢失。如果丢失，请检查是否使用了正确的更新方法。

### Q: 如何确认当前主题文件夹名称？
**A**: 
1. 外观 → 主题
2. 右键点击主题预览图 → 检查元素
3. 查看链接中的文件夹名称

或使用 FTP 查看 `wp-content/themes/` 目录。

### Q: GitHub 下载的 ZIP 文件夹名不对？
**A**: GitHub 默认使用 `仓库名-分支名` 作为文件夹名（如 `WP_Brave-main`）。需要解压后重命名为 `brave-love` 再上传。

---

## 📞 获取帮助

如果更新遇到问题：
1. 查看 [RELEASE-NOTES.md](RELEASE-NOTES.md) 了解版本变更
2. 运行 `diagnose.php` 诊断工具
3. 提交 Issue 到 GitHub
