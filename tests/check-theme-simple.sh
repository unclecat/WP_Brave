#!/bin/bash
# 主题检查脚本（不依赖 PHP）

echo "🔍 Brave Love 主题代码检查"
echo "================================"
echo ""

cd "$(dirname "$0")"
THEME_DIR="brave-wp"

# 检查目录
if [ ! -d "$THEME_DIR" ]; then
    echo "❌ 主题目录不存在: $THEME_DIR"
    exit 1
fi

echo "✅ 主题目录存在"
echo ""

# 检查必需文件
echo "📁 检查必需文件..."
required_files=(
    "style.css"
    "index.php"
    "functions.php"
    "header.php"
    "footer.php"
    "single.php"
    "archive.php"
    "search.php"
    "404.php"
    "inc/post-types.php"
    "inc/meta-boxes.php"
    "inc/customizer.php"
    "inc/helpers.php"
    "page-templates/page-home.php"
)

missing=0
for file in "${required_files[@]}"; do
    if [ -f "$THEME_DIR/$file" ]; then
        echo "   ✅ $file"
    else
        echo "   ❌ $file (缺失)"
        ((missing++))
    fi
done

echo ""
if [ $missing -eq 0 ]; then
    echo "✅ 所有必需文件都存在"
else
    echo "❌ 缺少 $missing 个文件"
fi
echo ""

# 检查 style.css
echo "🎨 检查 style.css..."
if [ -f "$THEME_DIR/style.css" ]; then
    theme_name=$(grep -i "Theme Name:" "$THEME_DIR/style.css" | head -1 | cut -d':' -f2 | xargs)
    version=$(grep -i "Version:" "$THEME_DIR/style.css" | head -1 | cut -d':' -f2 | xargs)
    echo "   主题名: $theme_name"
    echo "   版本: $version"
    
    # 检查必需字段
    for field in "Theme Name:" "Version:" "Description:" "Author:" "License:" "Text Domain:"; do
        if grep -q "$field" "$THEME_DIR/style.css"; then
            echo "   ✅ $field"
        else
            echo "   ⚠️  $field (建议添加)"
        fi
    done
else
    echo "   ❌ style.css 不存在"
fi
echo ""

# 统计代码
echo "📊 代码统计..."
php_files=$(find "$THEME_DIR" -name "*.php" | wc -l)
css_files=$(find "$THEME_DIR" -name "*.css" | wc -l)
js_files=$(find "$THEME_DIR" -name "*.js" | wc -l)
php_lines=$(find "$THEME_DIR" -name "*.php" -exec cat {} \; 2>/dev/null | wc -l)
css_lines=$(find "$THEME_DIR" -name "*.css" -exec cat {} \; 2>/dev/null | wc -l)
js_lines=$(find "$THEME_DIR" -name "*.js" -exec cat {} \; 2>/dev/null | wc -l)

echo "   PHP 文件: $php_files 个, 约 $php_lines 行"
echo "   CSS 文件: $css_files 个, 约 $css_lines 行"
echo "   JS 文件:  $js_files 个, 约 $js_lines 行"
echo "   总代码行: $((php_lines + css_lines + js_lines)) 行"
echo ""

# 检查文件大小
echo "📦 文件大小..."
theme_size=$(du -sh "$THEME_DIR" | cut -f1)
zip_size=$(du -sh brave-love-v0.1.zip 2>/dev/null | cut -f1 || echo "未打包")
echo "   主题目录: $theme_size"
echo "   ZIP 包: $zip_size"
echo ""

# 检查模板文件
echo "📄 页面模板..."
templates=$(find "$THEME_DIR/page-templates" -name "*.php" 2>/dev/null | wc -l)
echo "   模板数量: $templates"
find "$THEME_DIR/page-templates" -name "*.php" -exec basename {} \; 2>/dev/null | while read file; do
    echo "   - $file"
done
echo ""

# 检查安全性
echo "🔒 安全检查..."
dangerous=0
for func in "eval(" "exec(" "system(" "shell_exec(" "passthru("; do
    count=$(grep -r "$func" "$THEME_DIR" --include="*.php" 2>/dev/null | wc -l)
    if [ $count -gt 0 ]; then
        echo "   ⚠️  发现 $func ($count 处)"
        ((dangerous++))
    fi
done

if [ $dangerous -eq 0 ]; then
    echo "✅ 未发现危险函数"
else
    echo "⚠️  发现 $dangerous 个危险函数，请检查是否为必需"
fi
echo ""

# 检查国际化
echo "🌍 国际化检查..."
text_domain=$(grep "Text Domain:" "$THEME_DIR/style.css" 2>/dev/null | cut -d':' -f2 | xargs)
if [ -n "$text_domain" ]; then
    echo "   Text Domain: $text_domain"
    # 检查是否正确使用
    usage=$(grep -r "'$text_domain'" "$THEME_DIR" --include="*.php" 2>/dev/null | wc -l)
    echo "   使用次数: $usage"
else
    echo "   ⚠️  未定义 Text Domain"
fi
echo ""

# 最终报告
echo "================================"
echo "📋 检查报告"
echo "================================"
echo ""

if [ $missing -eq 0 ]; then
    echo "✅ 文件完整性检查通过"
else
    echo "❌ 缺少 $missing 个必需文件"
fi

echo ""
echo "🚀 下一步："
echo "   1. 运行 ./test-theme.sh 启动 WordPress 测试环境"
echo "   2. 访问 http://localhost:8080 完成安装"
echo "   3. 运行 ./setup-test-data.sh 创建测试数据"
echo ""
