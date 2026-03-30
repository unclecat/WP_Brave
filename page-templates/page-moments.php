<?php
/**
 * Template Name: 点点滴滴
 *
 * @package Brave_Love
 */

get_header();

// 获取筛选年份
$current_year = isset($_GET['year']) ? intval($_GET['year']) : 0;

// 构建查询
$args = array(
    'post_type' => 'moment',
    'posts_per_page' => -1,
    'orderby' => 'meta_value',
    'meta_key' => '_meet_date',
    'order' => 'DESC',
);

if ($current_year > 0) {
    $args['meta_query'] = array(
        array(
            'key' => '_meet_date',
            'value' => array($current_year . '-01-01', $current_year . '-12-31'),
            'compare' => 'BETWEEN',
            'type' => 'DATE',
        ),
    );
}

$moments = get_posts($args);
$years = brave_get_moment_years();
?>

<section class="content-section">
    <div class="section-header">
        <h1 class="section-title">💖 点点滴滴</h1>
        <p class="section-desc">记录我们的每一次见面，每一个瞬间</p>
    </div>

    <!-- 年份筛选 -->
    <?php if (!empty($years)) : ?>
    <div class="memory-filters">
        <a href="<?php echo esc_url(get_permalink()); ?>" class="memory-filter <?php echo $current_year === 0 ? 'active' : ''; ?>">
            <?php _e('全部', 'brave-love'); ?>
        </a>
        <?php foreach ($years as $year) : ?>
            <a href="<?php echo esc_url(add_query_arg('year', $year, get_permalink())); ?>" class="memory-filter <?php echo $current_year === intval($year) ? 'active' : ''; ?>">
                <?php echo esc_html($year); ?>
            </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- 时间轴 -->
    <?php if (!empty($moments)) : ?>
        <div class="timeline">
            <?php foreach ($moments as $moment) : 
                $meet_date = get_post_meta($moment->ID, '_meet_date', true);
                $location = get_post_meta($moment->ID, '_meet_location', true);
                $mood = get_post_meta($moment->ID, '_mood', true);
                $related_memory = get_post_meta($moment->ID, '_related_memory', true);
                $has_thumbnail = has_post_thumbnail($moment->ID);
            ?>
                <div class="timeline-item">
                    <div class="timeline-dot"></div>
                    <span class="timeline-date"><?php echo esc_html($meet_date); ?></span>
                    <div class="timeline-content">
                        <div class="timeline-header">
                            <h4 class="timeline-title">
                                <?php echo esc_html($moment->post_title); ?>
                            </h4>
                            <?php if ($mood) : ?>
                                <span class="timeline-mood" title="<?php echo esc_attr(brave_get_mood_text($mood)); ?>">
                                    <?php echo brave_get_mood_emoji($mood); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="timeline-text">
                            <?php echo wpautop(wp_trim_words($moment->post_content, 60)); ?>
                        </div>
                        
                        <?php if ($has_thumbnail || $related_memory) : ?>
                            <div class="timeline-images">
                                <?php if ($has_thumbnail) : ?>
                                    <?php echo get_the_post_thumbnail($moment->ID, 'thumbnail', array('class' => 'timeline-image')); ?>
                                <?php endif; ?>
                                
                                <?php if ($related_memory) : 
                                    $memory_photos = brave_get_memory_photos($related_memory, 'thumbnail');
                                    $show_photos = array_slice($memory_photos, 0, $has_thumbnail ? 3 : 4);
                                    foreach ($show_photos as $photo) : ?>
                                        <img src="<?php echo esc_url($photo['url']); ?>" alt="" class="timeline-image">
                                    <?php endforeach; 
                                    $remaining = count($memory_photos) - count($show_photos);
                                    if ($remaining > 0) : ?>
                                        <a href="<?php echo esc_url(get_permalink($related_memory)); ?>" class="timeline-image-more">
                                            +<?php echo $remaining; ?>
                                        </a>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($location) : ?>
                            <div class="timeline-meta">
                                <span class="timeline-location"><?php echo esc_html($location); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else : ?>
        <div class="timeline-empty">
            <div class="timeline-empty-icon">📝</div>
            <p class="timeline-empty-text"><?php _e('还没有记录任何点滴，快去添加吧！', 'brave-love'); ?></p>
        </div>
    <?php endif; ?>
</section>

<?php
get_footer();
