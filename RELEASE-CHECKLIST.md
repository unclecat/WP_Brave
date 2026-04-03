# Brave Love Release Checklist

每次正式发布前，按下面顺序完成收口。

## 1. 版本与元信息

- 更新 `style.css` 中的 `Version`
- 更新 `functions.php` 中的 `BRAVE_VERSION`
- 复查 `Theme URI`、`Author`、`Author URI`、`Description`
- 如有需要，更新 `Requires at least`、`Tested up to`、`Requires PHP`

## 2. 文档与仓库展示

- 更新 `README.md`
- 更新 `CHANGELOG.md`
- 更新 `RELEASE.md`
- 更新 `TEST-REPORT.md`
- 更新 GitHub About 文案文件 `./.github/ABOUT.md`
- 复查 README 顶部徽章、版本号、发布链接、作者信息是否一致

## 3. 主题展示素材

- 重新生成或复查 `screenshot.png`
- 确认截图尺寸为 `1200x900`
- 确认 WordPress 后台主题卡片中显示的描述与截图风格一致
- 确认截图没有调试信息、占位数据异常、视觉断裂或加载失败状态

## 4. 本地质量检查

- 运行 `find . -name '*.php' -not -path './tests/*' -print0 | xargs -0 -n1 php -l`
- 运行 `php tests/check-theme.php`
- 运行 `php tests/security-scan.php`
- 运行 `bash tests/check-theme-simple.sh`
- 如本地运行环境可用，完成首页和现有 6 个页面的前台 smoke test
- 如涉及后台能力，复查天气城市、纪念日、Customizer 与旧相册管理页

## 5. 打包验证

- 确认主题目录名为 `brave-love`
- 确认安装包不包含 `.git`、`.github`、`tests`、`.DS_Store`
- 如需本地留档，生成 `brave-love-x.y.z.zip`
- 如同时提供无版本号资产，确认 `brave-love.zip` 与当前版本归档内容一致
- 确认 `screenshot.png` 已进入根目录安装包
- 确认 `screenshot.png` 在安装包内不是空文件

## 6. 提交与发版

- 确认 `git diff` 仅包含预期改动
- 提交代码到 `main`
- 确认 `git status` 干净
- 创建并推送 tag：`vX.Y.Z`
- 等待 GitHub Actions 自动创建 Release

## 7. 发布后复核

- 检查 GitHub Release 标题、正文和附件是否正确
- 检查 `brave-love.zip` 是否能正常下载
- 安装包上传到 WordPress 后，确认后台主题卡片的名称、描述、截图展示正常
- 如有必要，补充后续 hotfix 或文档修订
