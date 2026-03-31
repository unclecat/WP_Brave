# 数据库清理指南

## 清理旧相册数据 (memory CPT)

### 方法1：使用 SQL 命令（推荐）

```sql
-- 1. 删除旧相册文章
DELETE FROM wp_posts WHERE post_type = 'memory';

-- 2. 删除旧相册相关的 postmeta
DELETE FROM wp_postmeta WHERE post_id NOT IN (SELECT ID FROM wp_posts);

-- 3. 删除旧相册标签（taxonomy）
DELETE FROM wp_term_relationships WHERE object_id NOT IN (SELECT ID FROM wp_posts);

-- 4. 删除孤立的 term_taxonomy
DELETE FROM wp_term_taxonomy WHERE count = 0;

-- 5. 删除孤立的 terms
DELETE FROM wp_terms WHERE term_id NOT IN (SELECT term_id FROM wp_term_taxonomy);
```

### 方法2：使用插件

1. 安装 "Post Type Switcher" 插件
2. 将旧 memory 文章转换为 moment 类型
3. 或者安装 "Bulk Delete" 插件批量删除

### 方法3：WordPress 代码片段

添加到主题的 functions.php（执行后删除）：

```php
// 删除所有旧相册文章
$memories = get_posts(array(
    'post_type' => 'memory',
    'posts_per_page' => -1,
    'post_status' => 'any',
));

foreach ($memories as $memory) {
    wp_delete_post($memory->ID, true); // true = 强制删除，不放入回收站
}
```

### 清理缓存

清理后访问：
- 后台 → 设置 → 固定链接 → 点击保存（刷新重写规则）
- 如果有缓存插件，清空所有缓存
