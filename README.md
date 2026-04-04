# Brave Love

[![Version](https://img.shields.io/badge/version-1.0.2-ff5162.svg)](https://github.com/unclecat/WP_Brave/releases)
[![WordPress](https://img.shields.io/badge/WordPress-6.0%2B-21759b.svg)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-777bb4.svg)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-GPL%20v2%20or%20later-2ea44f.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

一款为情侣记录而生的 WordPress 主题，以更克制、更统一的杂志感排版呈现你们的故事、照片、清单与日常。

![Brave Love Theme Preview](./screenshot.png)

当前稳定版：`v1.0.2`。这次 patch release 聚焦两件事：去掉首页天气卡片的 4 城市上限，并清理一批未使用的主题 helper、旧注释和过时测试描述，让代码与文档更一致。

## 1.0 正式版定位

Brave Love 不是一个单纯的“恋爱博客模板”，而是一套完整的情侣纪念站主题：

- 用 `关于我们` 梳理一条真正可阅读的关系时间线
- 用 `点点滴滴` 按年份记录重要瞬间与文章内容
- 用 `恋爱清单` 管理想一起完成的计划与已完成的回忆
- 用 `甜蜜相册` 自动汇聚点滴中的照片，形成统一相册
- 用 `随笔说说` 留下碎片化心情、想念和短句
- 用 `祝福留言` 接住亲友祝福与公开互动
- 用 `首页天气 / 特别的日子 / 双计时器` 把日常氛围补完整

这次 `v1.0.0` 不是简单的版本号升级，而是一次正式产品发布：文档、主题元信息、后台展示、截图素材、发布说明、技术说明和发版清单都已按正式主题产品标准重新整理。

## 核心体验

### 1. 首页氛围
- Hero 区域支持情侣头像、站点品牌信息与浪漫动态氛围
- 双计时器同时承载“已经一起多久”和“距离特别日子还有多久”
- 天气卡片支持多城市实时概况、空气质量、紫外线、日出日落和穿搭建议
- 特别的日子模块统一首页视觉语气，适合作为纪念提醒入口

### 2. 关于我们
- 独立的 `story_milestone` 故事节点内容模型
- 支持日期、阶段、摘要、正文和关联点滴
- 页面顶部支持自定义 Hero 文案与背景图
- 适合写“写在前面 + 故事总览 + 时间线正文”的完整结构

### 3. 点点滴滴
- 年份筛选 + 杂志感时间轴布局
- 支持日期、地点、心情、自定义摘要和作者头像识别
- 详情页与列表页共享统一日期口径，缺失 `_meet_date` 时自动回退文章日期
- 与相册、关于我们等模块互相关联

### 4. 恋爱清单
- 独立 CPT `love_list` + 分类法 `list_category`
- 完成度、已完成、待完成三类状态统计
- 卡片详情可展开，适合记录计划背后的故事和配图
- 归档页自动收口 canonical，并清理旧分页参数

### 5. 甜蜜相册
- 自动从 `点点滴滴` 中提取特色图与正文图片
- 瀑布流布局 + PhotoSwipe 灯箱浏览
- 支持年份筛选、地点/心情信息和点滴详情跳转
- 内置缓存和失效策略，减少重复计算

### 6. 随笔说说
- 适合放日常短句、心情和想念度
- 支持年份 / 月份 / 日期三级筛选
- 支持前台快捷发布（需登录且具备发布权限）
- 头像优先复用情侣设置，减少视觉割裂

### 7. 祝福留言
- 独立留言页模板，按年份筛选公开留言
- 自定义评论表单与本地卡通头像池
- 所有新留言默认进入审核，适合对外开放

## 后台能力

### 自定义器（Customizer）
- 基本信息：恋爱起始时间、倒计时、导航副标题
- Hero 区域：全局 Hero、情侣头像、昵称、恋爱清单归档 Hero
- 页面链接：点点滴滴 / 甜蜜相册 / 随笔说说 / 祝福留言 / 关于我们
- 入口图标：各模块 Emoji 自定义
- 分页和相册设置：每页数量、信息显隐
- 页脚导航：首页和现有 6 个页面的自定义导航名与链接
- 自定义代码：附加 CSS、页脚统计代码
- 访问统计：PV 文案与手动覆盖数值

### 独立后台页
- `设置 -> 天气城市`：配置首页天气卡片的城市与坐标
- `设置 -> 纪念日`：维护特别日子列表
- `点点滴滴 -> 相册数据管理`：处理旧版 `memory` 数据迁移或清理

### 内容模型
- `moment`：点点滴滴
- `love_list`：恋爱清单
- `note`：随笔说说
- `story_milestone`：关于我们故事节点

## 视觉与交互原则

- 默认固定为浅色模式，不跟随系统自动切换
- 通过右上角悬浮按钮在浅色 / 深色之间手动切换
- 首页与内页统一采用更柔和的 editorial / magazine 节奏
- 重点模块减少突兀渐变、硬边内衬和不必要的强对比
- 手机端与桌面端都围绕“舒展、顺滑、可阅读”优化

## 技术栈

| 领域 | 方案 |
| --- | --- |
| CMS | WordPress 6.0+ |
| 运行环境 | PHP 7.4+ |
| UI 基础 | Bootstrap 5.3.2 |
| 图片灯箱 | PhotoSwipe 5.4.2 |
| 前端交互 | 原生 JS + jQuery（由 WordPress 提供） |
| 天气数据 | Open-Meteo Forecast + Air Quality API |
| 静态资源策略 | Bootstrap / PhotoSwipe / 字体本地化，避免核心界面依赖外部 CDN |
| 图片聚合 | 从 `moment` 内容和特色图自动生成相册索引 + transient/post meta 缓存 |

## 安装与发布

### 快速安装
1. 下载发布资产 `brave-love.zip`
2. 上传到 `wp-content/themes/` 或后台“外观 -> 主题 -> 上传主题”
3. 启用 `Brave Love`
4. 到“设置 -> 固定链接”点击一次“保存更改”刷新重写规则

### 推荐初始化流程
1. 创建并指定页面模板：首页、关于我们、点点滴滴、甜蜜相册、随笔说说、祝福留言
2. 在“外观 -> 自定义 -> Brave 主题设置”完成站点品牌、情侣头像、页面链接与页脚导航配置
3. 在“设置 -> 天气城市”中添加常用城市
4. 在“设置 -> 纪念日”中维护特别的日子
5. 补充点点滴滴、故事节点和恋爱清单数据，再检查首页聚合效果

## 目录结构

```text
brave-love/
├── assets/                  # CSS / JS / fonts / vendor
├── inc/                     # CPT、Customizer、Meta Box、后台页、工具函数
├── page-templates/          # 首页与主要内容页模板
├── template-parts/          # 通用模板片段
├── style.css                # 主题头信息与全局样式
├── functions.php            # 主题入口与资源注册
├── screenshot.png           # WordPress 后台主题截图
├── README.md                # 项目说明
├── CHANGELOG.md             # 更新日志
├── RELEASE.md               # 当前版本发布说明
├── RELEASE-CHECKLIST.md     # 发版清单
└── TEST-REPORT.md           # 最近一次发版前测试报告
```

## 质量保证

`v1.0.2` 版本已完成：

- 全量 PHP 语法检查
- 主题结构检查
- 主题安全扫描
- 本地 WordPress 运行态 smoke test
- 主题元信息 / Release / README / 截图一致性检查
- 前后台输入校验、重定向与显示输出的发版前审计

详情可查看：

- `CHANGELOG.md`
- `RELEASE.md`
- `TEST-REPORT.md`
- `RELEASE-CHECKLIST.md`

## GitHub 与发布信息

- Repository: <https://github.com/unclecat/WP_Brave>
- Author site: <https://www.1ink.ink/>
- Latest release: <https://github.com/unclecat/WP_Brave/releases>
- Release assets: `brave-love.zip`
- GitHub About copy: `./.github/ABOUT.md`

## License

GPL v2 or later
