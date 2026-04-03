# Brave Love v1.0.0

Brave Love 1.0 正式发布。

这是一次完整的产品化收口版本：不仅把前台体验和后台配置打磨到稳定可发布状态，也同步完成了主题元信息、GitHub 文档、主题截图、发布说明与发版清单的统一升级。

## 本次发布亮点

### 1) 正式产品化资料
- 重写 `README.md`，补齐产品定位、功能结构、技术栈、安装方式与质量保证说明
- 新增 GitHub About 专用文案文件 `./.github/ABOUT.md`
- 更新 WordPress 主题头信息，让后台主题详情展示更完整、更正式
- 重新生成 `screenshot.png`，用于 WordPress 后台主题预览

### 2) 前后台发布前审计
- 复查前台模板、后台管理页、Customizer 和 Meta Box 的输入校验与输出转义
- 加固日期、经纬度、布尔开关、用户 ID、重定向和旧数据管理流程
- 统一 PV 统计逻辑，避免后台、Ajax、REST、预览等请求污染统计数据

### 3) 体验层收口
- 保持首页、关于我们、点点滴滴、恋爱清单、甜蜜相册、随笔说说、祝福留言的整体视觉统一
- 延续默认浅色、手动切换深色的体验策略
- 首页天气、纪念日、页脚导航、关于我们故事线等模块已进入 1.0 定版状态

## 重点变更文件

- `style.css`
- `functions.php`
- `README.md`
- `CHANGELOG.md`
- `RELEASE.md`
- `RELEASE-CHECKLIST.md`
- `TEST-REPORT.md`
- `./.github/ABOUT.md`
- `screenshot.png`

## 技术栈

- WordPress 6.0+
- PHP 7.4+
- Bootstrap 5.3.2
- PhotoSwipe 5.4.2
- Open-Meteo Forecast / Air Quality API
- 本地化前端依赖与主题内缓存策略

## 验证结果

以下检查已在本地执行：

- `find . -name '*.php' -not -path './tests/*' -print0 | xargs -0 -n1 php -l`
- `php tests/check-theme.php`
- `php tests/security-scan.php`
- `bash tests/check-theme-simple.sh`
- 本地 WordPress 运行态页面冒烟验证：首页、关于我们、点点滴滴、恋爱清单、甜蜜相册、随笔说说、祝福留言

## 升级说明

如果你是从 `0.7.x` 升级到 `1.0.0`：

1. 覆盖主题文件后，先清除站点缓存与浏览器缓存
2. 到“设置 -> 固定链接”点击一次“保存更改”
3. 到“外观 -> 自定义 -> Brave 主题设置”复查：
   - 页面链接
   - 页脚导航
   - 天气与纪念日
   - 访问统计 / 自定义代码
4. 如站点启用了自定义统计代码，请保存后再做一次前台检查

## 发布资产

- `brave-love-1.0.0.zip`
- `brave-love.zip`
- GitHub Release Notes（当前文件）
- `CHANGELOG.md`
- `TEST-REPORT.md`

打包说明：

- 安装包根目录为 `brave-love/`
- 已排除 `.git`、`.github`、`tests`、`.DS_Store` 等开发期文件
- 已确认 `screenshot.png` 以非空 PNG 文件形式包含在安装包内

**版本**: `1.0.0`  
**发布日期**: `2026-04-03`  
**更新日志**: `CHANGELOG.md`
