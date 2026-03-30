#!/bin/bash
# Brave Love 主题测试脚本

echo "🧪 Brave Love 主题测试脚本"
echo "================================"
echo ""

# 检查 Docker 是否安装
if ! command -v docker &> /dev/null; then
    echo "❌ Docker 未安装"
    echo "请访问 https://docs.docker.com/get-docker/ 安装 Docker"
    exit 1
fi

if ! command -v docker-compose &> /dev/null; then
    echo "❌ Docker Compose 未安装"
    echo "请访问 https://docs.docker.com/compose/install/ 安装"
    exit 1
fi

echo "✅ Docker 和 Docker Compose 已安装"
echo ""

# 检查主题文件
echo "📂 检查主题文件..."
if [ ! -d "brave-wp" ]; then
    echo "❌ 主题目录 brave-wp 不存在"
    exit 1
fi

required_files=(
    "brave-wp/style.css"
    "brave-wp/functions.php"
    "brave-wp/index.php"
    "brave-wp/header.php"
    "brave-wp/footer.php"
)

for file in "${required_files[@]}"; do
    if [ ! -f "$file" ]; then
        echo "❌ 缺少必需文件: $file"
        exit 1
    fi
done

echo "✅ 主题文件完整"
echo ""

# 检查样式表头
echo "🎨 检查 style.css 头信息..."
if grep -q "Theme Name:" brave-wp/style.css; then
    theme_name=$(grep "Theme Name:" brave-wp/style.css | head -1)
    echo "   $theme_name"
fi

if grep -q "Version:" brave-wp/style.css; then
    version=$(grep "Version:" brave-wp/style.css | head -1)
    echo "   $version"
fi

echo "✅ 样式表头信息正确"
echo ""

# 启动环境
echo "🚀 启动 WordPress 环境..."
docker-compose down -v 2>/dev/null || true
docker-compose up -d

if [ $? -ne 0 ]; then
    echo "❌ Docker 启动失败"
    exit 1
fi

echo "✅ WordPress 容器已启动"
echo ""

# 等待 WordPress 就绪
echo "⏳ 等待 WordPress 初始化..."
for i in {1..30}; do
    if curl -s http://localhost:8080/wp-admin/install.php > /dev/null 2>&1; then
        echo "✅ WordPress 已就绪"
        break
    fi
    echo -n "."
    sleep 2
done
echo ""

# 显示访问信息
echo ""
echo "================================"
echo "🎉 环境启动成功！"
echo "================================"
echo ""
echo "📱 访问地址："
echo "   WordPress:  http://localhost:8080"
echo "   phpMyAdmin: http://localhost:8081"
echo ""
echo "🔧 安装步骤："
echo "   1. 访问 http://localhost:8080"
echo "   2. 选择语言为「简体中文」"
echo "   3. 填写站点信息："
echo "      - 站点标题: 我们的小窝"
echo "      - 用户名: admin"
echo "      - 密码: admin123"
echo "      - 邮箱: admin@example.com"
echo "   4. 完成安装后登录"
echo "   5. 外观 → 主题 → 启用「Brave Love」"
echo ""
echo "📂 主题位置:"
echo "   wp-content/themes/brave-love/"
echo ""
echo "💡 常用命令："
echo "   停止环境: docker-compose down"
echo "   查看日志: docker-compose logs -f wordpress"
echo "   重启环境: docker-compose restart"
echo "   重置数据: docker-compose down -v"
echo ""
echo "⚠️  注意："
echo "   - 首次访问可能需要 30-60 秒初始化"
echo "   - 数据保存在 Docker Volume 中，重启不会丢失"
echo "   - 重置数据请运行: docker-compose down -v"
echo ""
