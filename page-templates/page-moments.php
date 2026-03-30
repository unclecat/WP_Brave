<?php
/**
 * Template Name: 点点滴滴
 *
 * @package Brave_Love
 */

get_header();

// 构建查询
$args = array(
    'post_type' => 'moment',
    'posts_per_page' => -1,
    'orderby' => 'meta_value',
    'meta_key' => '_meet_date',
    'order' => 'DESC',
);

$moments = get_posts($args);

// 按年份分组
$grouped_moments = array();
$years = array();

foreach ($moments as $moment) {
    $meet_date = get_post_meta($moment->ID, '_meet_date', true);
    $year = !empty($meet_date) ? substr($meet_date, 0, 4) : '未知';
    
    if (!isset($grouped_moments[$year])) {
        $grouped_moments[$year] = array();
        $years[] = $year;
    }
    $grouped_moments[$year][] = $moment;
}

// 年份排序
rsort($years);
?>

<section class="content-section">
    <div class="section-header">
        <h1 class="section-title">💖 点点滴滴</h1>
        <p class="section-desc">记录我们的每一次见面，每一个瞬间</p>
    </div>

    <?php if (!empty($years)) : ?>
    <!-- 年份导航 -->
    <nav class="year-nav" id="yearNav">
        <a href="#all" class="year-nav-item active" data-year="all" onclick="return false;">全部</a>
        <?php foreach ($years as $year) : ?>
            <a href="#year-<?php echo esc_attr($year); ?>" class="year-nav-item" data-year="<?php echo esc_attr($year); ?>" onclick="return false;">
                <?php echo esc_html($year); ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <!-- 时间轴 -->
    <div class="timeline-wrapper" id="timelineWrapper">
        <?php foreach ($years as $year) : ?>
            <div class="year-group" id="year-<?php echo esc_attr($year); ?>" data-year="<?php echo esc_attr($year); ?>">
                <div class="year-badge"><?php echo esc_html($year); ?></div>
                
                <div class="timeline">
                    <?php foreach ($grouped_moments[$year] as $moment) : 
                        $meet_date = get_post_meta($moment->ID, '_meet_date', true);
                        $location = get_post_meta($moment->ID, '_meet_location', true);
                        $mood = get_post_meta($moment->ID, '_mood', true);
                        $related_memory = get_post_meta($moment->ID, '_related_memory', true);
                        $moment_summary = get_post_meta($moment->ID, '_moment_summary', true);
                        $has_thumbnail = has_post_thumbnail($moment->ID);
                        $moment_link = get_permalink($moment->ID);
                    ?>
                        <article class="timeline-card" data-moment-id="<?php echo esc_attr($moment->ID); ?>">
                            <a href="<?php echo esc_url($moment_link); ?>" class="timeline-card-link">
                                <div class="timeline-card-inner">
                                    <div class="timeline-card-header">
                                        <div class="timeline-card-date">
                                            <span class="date-day"><?php echo esc_html(substr($meet_date, 8, 2)); ?></span>
                                            <span class="date-month"><?php echo esc_html(substr($meet_date, 5, 2)); ?>月</span>
                                        </div>
                                        <div class="timeline-card-meta">
                                            <h4 class="timeline-card-title"><?php echo esc_html($moment->post_title); ?></h4>
                                            <?php if ($mood) : ?>
                                                <span class="timeline-card-mood" title="<?php echo esc_attr(brave_get_mood_text($mood)); ?>">
                                                    <?php echo brave_get_mood_emoji($mood); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <?php if ($has_thumbnail || $related_memory) : ?>
                                        <div class="timeline-card-media">
                                            <?php if ($has_thumbnail) : ?>
                                                <?php echo get_the_post_thumbnail($moment->ID, 'medium', array('class' => 'timeline-card-image')); ?>
                                            <?php else : 
                                                $memory_photos = brave_get_memory_photos($related_memory, 'thumbnail');
                                                if (!empty($memory_photos)) : ?>
                                                    <img src="<?php echo esc_url($memory_photos[0]['url']); ?>" alt="" class="timeline-card-image">
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="timeline-card-body">
                                        <div class="timeline-card-excerpt">
                                            <?php 
                                            // 优先显示自定义摘要
                                            if (!empty($moment_summary)) {
                                                echo wpautop($moment_summary);
                                            } else {
                                                // 无摘要时截取内容前120字
                                                echo wpautop(wp_trim_words($moment->post_content, 120));
                                            }
                                            ?>
                                        </div>
                                        
                                        <?php if ($location) : ?>
                                            <div class="timeline-card-location">
                                                <span class="location-icon">📍</span>
                                                <span><?php echo esc_html($location); ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="timeline-card-footer">
                                        <span class="view-detail">查看详情 →</span>
                                    </div>
                                </div>
                            </a>
                        </article>
                    <?php endforeach; ?>
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
