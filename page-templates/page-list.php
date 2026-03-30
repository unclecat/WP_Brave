<?php
/**
 * Template Name: 恋爱清单
 *
 * @package Brave_Love
 */

get_header();

// 获取筛选分类
$current_cat = isset($_GET['cat']) ? intval($_GET['cat']) : 0;

// 构建查询
$args = array(
    'post_type' => 'love_list',
    'posts_per_page' => -1,
    'orderby' => 'date',
    'order' => 'ASC',
);

if ($current_cat > 0) {
    $args['tax_query'] = array(
        array(
            'taxonomy' => 'list_category',
            'field' => 'term_id',
            'terms' => $current_cat,
        ),
    );
}

$items = get_posts($args);
$progress = brave_get_list_progress();
$categories = get_terms(array(
    'taxonomy' => 'list_category',
    'hide_empty' => false,
));
?>

<section class="content-section">
    <div class="section-header">
        <h1 class="section-title">📜 恋爱清单</h1>
        <p class="section-desc">一百件事记录着我们的点点滴滴</p>
    </div>

    <!-- 进度圆环 -->
    <div class="list-progress">
        <div class="progress-circle">
            <svg width="100" height="100" viewBox="0 0 100 100">
                <circle class="progress-circle-bg" cx="50" cy="50" r="42"/>
                <circle class="progress-circle-bar" cx="50" cy="50" r="42" 
                    stroke-dasharray="264" 
                    stroke-dashoffset="<?php echo 264 - (264 * $progress['percentage'] / 100); ?>"/>
            </svg>
            <div class="progress-text">
                <div class="progress-number"><?php echo $progress['done']; ?></div>
                <div class="progress-total">/ <?php echo $progress['total']; ?></div>
            </div>
        </div>
        <p style="color: #666; font-size: 0.9rem;">已完成 <?php echo $progress['percentage']; ?>%</p>
    </div>

    <!-- 分类筛选 -->
    <?php if (!empty($categories) && !is_wp_error($categories)) : ?>
    <div class="memory-filters">
        <a href="<?php echo esc_url(get_permalink()); ?>" class="memory-filter <?php echo $current_cat === 0 ? 'active' : ''; ?>">
            <?php _e('全部', 'brave-love'); ?>
        </a>
        <?php foreach ($categories as $cat) : ?>
            <a href="<?php echo esc_url(add_query_arg('cat', $cat->term_id, get_permalink())); ?>" class="memory-filter <?php echo $current_cat === $cat->term_id ? 'active' : ''; ?>">
                <?php echo esc_html($cat->name); ?>
            </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- 清单列表 -->
    <?php if (!empty($items)) : ?>
        <div class="list-grid">
            <?php foreach ($items as $item) : 
                $is_done = get_post_meta($item->ID, '_is_done', true);
                $done_date = get_post_meta($item->ID, '_done_date', true);
                $has_thumbnail = has_post_thumbnail($item->ID);
            ?>
                <div class="list-item <?php echo $is_done ? 'done' : ''; ?>" data-id="<?php echo $item->ID; ?>">
                    <div class="list-header">
                        <div class="list-checkbox">
                            <?php if ($is_done) echo '✓'; ?>
                        </div>
                        <div class="list-title"><?php echo esc_html($item->post_title); ?></div>
                        <?php if ($is_done && ($item->post_content || $has_thumbnail)) : ?>
                            <button type="button" class="list-toggle" onclick="this.closest('.list-item').querySelector('.list-detail').classList.toggle('show');">
                                ▼
                            </button>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($is_done && ($item->post_content || $has_thumbnail)) : ?>
                        <div class="list-detail">
                            <?php if ($has_thumbnail) : ?>
                                <?php echo get_the_post_thumbnail($item->ID, 'large', array('class' => 'list-image')); ?>
                            <?php endif; ?>
                            <?php if ($item->post_content) : ?>
                                <div style="font-size: 0.875rem; color: #666; line-height: 1.6;">
                                    <?php echo wpautop(esc_html($item->post_content)); ?>
                                </div>
                            <?php endif; ?>
                            <?php if ($done_date) : ?>
                                <div class="list-date">完成于 <?php echo esc_html($done_date); ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else : ?>
        <div class="text-center" style="padding: 3rem 1rem;">
            <p style="color: #999; margin-bottom: 1rem;">📝</p>
            <p style="color: #666;"><?php _e('还没有添加任何事项，快去规划你们的清单吧！', 'brave-love'); ?></p>
        </div>
    <?php endif; ?>
</section>

<?php
get_footer();
