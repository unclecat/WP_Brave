# Brave Love Release Checklist

每次发布新版本时，按下面顺序执行。

## 1. 更新内容

- 更新 `style.css` 中的 `Version`
- 更新 `functions.php` 中的 `BRAVE_VERSION`
- 在 `CHANGELOG.md` 顶部追加新版本记录
- 更新 `RELEASE.md` 为当前版本的发布说明

## 2. 本地检查

- 运行 `bash tests/check-theme-simple.sh`
- 确认工作区改动符合预期
- 如有条件，补充运行级 WordPress 测试

## 3. 打包验证

- 确认主题目录名为 `brave-love`
- 确认安装包不包含 `.git`、`.github`、`tests`、`.DS_Store`
- 如需本地留档，生成 `brave-love-X.Y.Z.zip`

## 4. 提交代码

- 提交版本相关改动到 `main`
- 确认 `git status` 干净

## 5. 发布到 GitHub

- 创建并推送 tag：`vX.Y.Z`
- 等待 GitHub Actions 自动创建 Release
- 检查 Release 页面正文是否来自 `RELEASE.md`
- 检查 Release 资产 `brave-love.zip` 是否上传成功

## 6. 发布后确认

- 打开 GitHub Release 页面确认版本号正确
- 测试下载 `brave-love.zip`
- 如有必要，在 `main` 继续补充后续修复
