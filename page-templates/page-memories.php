<?php
/**
 * Template Name: 甜蜜相册
 *
 * @package Brave_Love
 */

get_header();

// 获取筛选参数
$current_year = isset($_GET['year']) ? intval($_GET['year']) : 0;
$current_tag = isset($_GET['tag']) ? intval($_GET['tag']) : 0;

// 构建查询
$args = array(
    'post_type' => 'memory',
    'posts_per_page' => -1,
    'orderby' => 'meta_value',
    'meta_key' => '_memory_date',
    'order' => 'DESC',
);

if ($current_year > 0) {
    $args['meta_query'] = array(
        array(
            'key' => '_memory_date',
            'value' => array($current_year . '-01-01', $current_year . '-12-31'),
            'compare' => 'BETWEEN',
            'type' => 'DATE',
        ),
    );
}

if ($current_tag > 0) {
    $tax_query = isset($args['tax_query']) ? $args['tax_query'] : array('relation' => 'AND');
    $tax_query[] = array(
        'taxonomy' => 'memory_tag',
        'field' => 'term_id',
        'terms' => $current_tag,
    );
    $args['tax_query'] = $tax_query;
}

$memories = get_posts($args);
$years = brave_get_memory_years();
$tags = get_terms(array(
    'taxonomy' => 'memory_tag',
    'hide_empty' => true,
));
?>

<section class="content-section">
    <div class="section-header">
        <h1 class="section-title">📷 甜蜜相册</h1>
        <p class="section-desc">每一张照片都是一段美好的回忆</p>
    </div>

    <!-- 年份筛选 -->
    <?php if (!empty($years)) : ?>
    <div class="memory-filters">
        <a href="<?php echo esc_url(remove_query_arg('year')); ?>" class="memory-filter <?php echo $current_year === 0 ? 'active' : ''; ?>">
            <?php _e('全部年份', 'brave-love'); ?>
        </a>
        <?php foreach ($years as $year) : ?>
            <a href="<?php echo esc_url(add_query_arg('year', $year)); ?>" class="memory-filter <?php echo $current_year === intval($year) ? 'active' : ''; ?>">
                <?php echo esc_html($year); ?>
            </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- 标签筛选 -->
    <?php if (!empty($tags) && !is_wp_error($tags)) : ?>
    <div class="memory-filters">
        <a href="<?php echo esc_url(remove_query_arg('tag')); ?>" class="memory-filter <?php echo $current_tag === 0 ? 'active' : ''; ?>">
            <?php _e('全部标签', 'brave-love'); ?>
        </a>
        <?php foreach ($tags as $tag) : ?>
            <a href="<?php echo esc_url(add_query_arg('tag', $tag->term_id)); ?>" class="memory-filter <?php echo $current_tag === $tag->term_id ? 'active' : ''; ?>">
                <?php echo esc_html($tag->name); ?>
            </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- 相册网格 -->
    <?php if (!empty($memories)) : ?>
        <div class="memory-grid" id="memory-grid">
            <?php foreach ($memories as $memory) : 
                $photo_count = brave_get_memory_photo_count($memory->ID);
                $memory_date = get_post_meta($memory->ID, '_memory_date', true);
                $photos = brave_get_memory_photos($memory->ID, 'memory-thumb');
                $cover = !empty($photos) ? $photos[0]['url'] : '';
                if (!$cover && has_post_thumbnail($memory->ID)) {
                    $cover = get_the_post_thumbnail_url($memory->ID, 'memory-thumb');
                }
            ?>
                <div class="memory-card" data-photos='<?php echo esc_attr(json_encode($photos)); ?>' data-title="<?php echo esc_attr($memory->post_title); ?>">
                    <?php if ($cover) : ?>
                        <img src="<?php echo esc_url($cover); ?>" alt="<?php echo esc_attr($memory->post_title); ?>" class="memory-cover" loading="lazy">
                    <?php else : ?>
                        <div class="memory-cover" style="background: linear-gradient(135deg, #f5f5f5 0%, #e0e0e0 100%); display: flex; align-items: center; justify-content: center; color: #999;">
                            <span style="font-size: 2rem;">📷</span>
                        </div>
                    <?php endif; ?>
                    <div class="memory-info">
                        <div class="memory-title"><?php echo esc_html($memory->post_title); ?></div>
                        <div class="memory-meta">
                            <span><?php echo esc_html($memory_date); ?></span>
                            <span class="memory-count">
                                <span>📷</span> <?php echo $photo_count; ?>
                            </span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else : ?>
        <div class="text-center" style="padding: 3rem 1rem;">
            <p style="color: #999; margin-bottom: 1rem;">📷</p>
            <p style="color: #666;"><?php _e('还没有上传任何照片，快去创建相册吧！', 'brave-love'); ?></p>
        </div>
    <?php endif; ?>
</section>

<?php
get_footer();
