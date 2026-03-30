#!/bin/bash
# Brave Love v0.1 GitHub 发布脚本

# 使用说明：
# 1. 修改下面的 GITHUB_USERNAME 为你的 GitHub 用户名
# 2. 确保已在 GitHub 创建同名仓库
# 3. 运行: chmod +x GITHUB-PUBLISH.sh && ./GITHUB-PUBLISH.sh

GITHUB_USERNAME="yourname"
REPO_NAME="brave-love"

# 设置 Git 配置（如未设置）
echo "🔧 检查 Git 配置..."
if [ -z "$(git config --global user.name)" ]; then
    echo "请输入你的 GitHub 用户名:"
    read git_name
    git config --global user.name "$git_name"
fi

if [ -z "$(git config --global user.email)" ]; then
    echo "请输入你的 GitHub 邮箱:"
    read git_email
    git config --global user.email "$git_email"
fi

# 添加远程仓库
echo "🔗 添加远程仓库..."
git remote add origin "https://github.com/$GITHUB_USERNAME/$REPO_NAME.git" 2>/dev/null || git remote set-url origin "https://github.com/$GITHUB_USERNAME/$REPO_NAME.git"

# 推送到 GitHub
echo "📤 推送代码到 GitHub..."
git branch -M main
git push -u origin main

# 创建标签
echo "🏷️ 创建版本标签..."
git tag -a v0.1 -m "Brave Love v0.1 - Initial release"
git push origin v0.1

echo ""
echo "✅ 发布完成！"
echo ""
echo "📦 仓库地址: https://github.com/$GITHUB_USERNAME/$REPO_NAME"
echo "🏷️  版本标签: https://github.com/$GITHUB_USERNAME/$REPO_NAME/releases/tag/v0.1"
echo ""
echo "📝 下一步："
echo "1. 访问 https://github.com/$GITHUB_USERNAME/$REPO_NAME/releases"
echo "2. 点击 'Create a new release' 或 'Draft a new release'"
echo "3. 选择标签 v0.1"
echo "4. 上传 brave-love-v0.1.zip 作为附件"
echo "5. 发布 Release"
