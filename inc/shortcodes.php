<?php
/**
 * 短代码
 *
 * @package Brave_Love
 */

/**
 * 恋爱计时器短代码
 */
function brave_timer_shortcode($atts) {
    $atts = shortcode_atts(array(
        'show_seconds' => 'true',
    ), $atts);

    $start_date = get_theme_mod('brave_love_start_date', '2020-01-01');
    $show_seconds = $atts['show_seconds'] === 'true';

    ob_start();
    ?>
    <div class="timer-section">
        <p class="timer-title">我们风雨同舟已经一起走过</p>
        <div class="timer-display" id="love-timer" data-start="<?php echo esc_attr($start_date); ?>">
            计算中...
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('love_timer', 'brave_timer_shortcode');

/**
 * 纪念日短代码
 */
function brave_anniversary_shortcode($atts) {
    ob_start();
    
    $anniversaries = brave_get_anniversaries();
    
    if (!empty($anniversaries)) :
    ?>
    <div class="anniversary-section">
        <h3 class="anniversary-title">💕 特别的日子</h3>
        <div class="anniversary-scroll">
            <?php foreach ($anniversaries as $item) : ?>
                <div class="anniversary-card <?php echo $item['is_countdown'] ? 'countdown' : 'countup'; ?>">
                    <div class="anniversary-name"><?php echo esc_html($item['name']); ?></div>
                    <div class="anniversary-days">
                        <?php echo $item['is_countdown'] ? '还有' : '已经'; ?> 
                        <?php echo $item['days']; ?>
                        <span>天</span>
                    </div>
                    <div class="anniversary-date"><?php echo esc_html($item['date']); ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
    endif;
    
    return ob_get_clean();
}
add_shortcode('anniversaries', 'brave_anniversary_shortcode');

/**
 * 入口卡片短代码
 */
function brave_entries_shortcode($atts) {
    ob_start();
    
    $entries = array(
        array(
            'icon' => get_theme_mod('brave_icon_moments', '💖'),
            'title' => __('点点滴滴', 'brave-love'),
            'desc' => __('记录生活', 'brave-love'),
            'link' => get_theme_mod('brave_page_moments') ? get_permalink(get_theme_mod('brave_page_moments')) : '#',
        ),
        array(
            'icon' => get_theme_mod('brave_icon_list', '📜'),
            'title' => __('恋爱清单', 'brave-love'),
            'desc' => __('一起做的事', 'brave-love'),
            'link' => get_theme_mod('brave_page_list') ? get_permalink(get_theme_mod('brave_page_list')) : '#',
        ),
        array(
            'icon' => get_theme_mod('brave_icon_memory', '📷'),
            'title' => __('甜蜜相册', 'brave-love'),
            'desc' => __('美好回忆', 'brave-love'),
            'link' => get_theme_mod('brave_page_memories') ? get_permalink(get_theme_mod('brave_page_memories')) : '#',
        ),
        array(
            'icon' => get_theme_mod('brave_icon_notes', '📝'),
            'title' => __('随笔说说', 'brave-love'),
            'desc' => __('心情碎片', 'brave-love'),
            'link' => get_theme_mod('brave_page_notes') ? get_permalink(get_theme_mod('brave_page_notes')) : '#',
        ),
        array(
            'icon' => get_theme_mod('brave_icon_blessing', '💌'),
            'title' => __('祝福留言', 'brave-love'),
            'desc' => __('收到祝福', 'brave-love'),
            'link' => get_theme_mod('brave_page_blessing') ? get_permalink(get_theme_mod('brave_page_blessing')) : '#',
        ),
    );
    ?>
    <div class="entry-section">
        <div class="entry-grid">
            <?php foreach ($entries as $entry) : ?>
                <a href="<?php echo esc_url($entry['link']); ?>" class="entry-card">
                    <div class="entry-icon"><?php echo $entry['icon']; ?></div>
                    <div class="entry-title"><?php echo esc_html($entry['title']); ?></div>
                    <div class="entry-desc"><?php echo esc_html($entry['desc']); ?></div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('entries', 'brave_entries_shortcode');

/**
 * 最新点滴短代码
 */
function brave_latest_moments_shortcode($atts) {
    $atts = shortcode_atts(array(
        'count' => 5,
    ), $atts);

    $moments = get_posts(array(
        'post_type' => 'moment',
        'posts_per_page' => intval($atts['count']),
        'orderby' => 'meta_value',
        'meta_key' => '_meet_date',
        'order' => 'DESC',
    ));

    if (empty($moments)) {
        return '<p class="text-center">' . __('暂无点滴记录', 'brave-love') . '</p>';
    }

    ob_start();
    ?>
    <div class="timeline">
        <?php foreach ($moments as $moment) : 
            $meet_date = get_post_meta($moment->ID, '_meet_date', true);
            $location = get_post_meta($moment->ID, '_meet_location', true);
        ?>
        <div class="timeline-item">
            <div class="timeline-dot"></div>
            <span class="timeline-date"><?php echo esc_html($meet_date); ?></span>
            <div class="timeline-content">
                <h4 class="timeline-title"><?php echo esc_html($moment->post_title); ?></h4>
                <div class="timeline-text"><?php echo wp_trim_words($moment->post_content, 30); ?></div>
                <?php if ($location) : ?>
                    <div class="timeline-location">
                        <span>📍</span> <?php echo esc_html($location); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('latest_moments', 'brave_latest_moments_shortcode');
