# v0.3 甜蜜相册重构计划

## 废弃内容
- ❌ 甜蜜相册 CPT (memory)
- ❌ 相册标签分类法 (memory_tag)
- ❌ 独立的相册页面模板

## 新增内容
- ✅ 瀑布流照片墙页面模板
- ✅ 从 moment 文章提取所有照片
- ✅ 智能加载更多/分页

## 数据结构

### 单张照片的数据
```php
$photo = array(
    'id'          => 123,              // 图片附件ID
    'url'         => '...',            // 大图URL
    'thumb'       => '...',            // 缩略图URL
    'title'       => '图片标题',        // 附件标题
    'caption'     => '图片描述',        // 附件说明
    'moment_id'   => 456,              // 所属moment文章ID
    'moment_title'=> '那次约会',        // moment标题
    'date'        => '2024-01-15',     // 见面日期
    'location'    => '西湖',           // 地点
    'summary'     => '摘要文字',        // moment摘要
    'aspect_ratio'=> 1.5,              // 宽高比(用于瀑布流)
);
```

## 技术实现

### 1. 照片聚合函数
```php
function brave_get_all_moment_photos($args = array()) {
    // 1. 查询所有 moment 文章
    // 2. 每篇文章提取特色图片 + 内容图片
    // 3. 按日期排序
    // 4. 返回照片数组
}
```

### 2. 瀑布流布局方案
选择 **CSS Columns** (最简单，浏览器支持好)
```css
.gallery-waterfall {
    column-count: 3;
    column-gap: 1rem;
}

.gallery-item {
    break-inside: avoid;
    margin-bottom: 1rem;
}

/* 响应式 */
@media (max-width: 768px) {
    .gallery-waterfall { column-count: 2; }
}

@media (max-width: 480px) {
    .gallery-waterfall { column-count: 1; }
}
```

### 3. 页面结构
```
page-templates/page-memory.php (新)
├── 获取照片数据
├── 瀑布流布局
│   ├── 每张照片卡片
│   │   ├── 图片 (懒加载)
│   │   ├── 悬停显示信息层
│   │   │   ├── 日期+地点
│   │   │   └── moment标题(链接)
│   │   └── 点击打开灯箱
├── 加载更多/分页
```

### 4. 交互功能
- 灯箱查看 (PhotoSwipe)
- 懒加载 (原生 loading="lazy")
- 无限滚动或分页

## 后台设置项
```php
// Customizer 新增:
- 相册每页照片数 (默认 12)
- 是否显示照片信息
- 是否启用无限滚动
```

## 数据库迁移
原有 memory 文章的数据不会丢失，只是不再展示。
如果用户想迁移，可以手动将 memory 内容合并到 moment。

## 优点
1. 简化管理 - 只需维护 moment，照片自动汇总
2. 更好的浏览体验 - 瀑布流更直观
3. 照片与故事关联 - 每张照片都知道来自哪个 moment
4. 减少重复工作 - 不用单独创建相册
