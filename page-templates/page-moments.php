<?php
/**
 * Template Name: 点点滴滴
 *
 * @package Brave_Love
 */

get_header();

// 获取Hero背景图
$hero_bg = get_theme_mod('brave_hero_bg');

?>
<!-- 页面Hero区域 -->
<?php 
$hero_bg_url = !empty($hero_bg) ? $hero_bg : 'https://images.unsplash.com/photo-1518199266791-5375a83190b7?w=1920';
?>
<section class="page-hero-section" style="border: 3px solid red;">
    <!-- 纯内联样式，不使用page-hero-bg类 -->
    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-image: url('<?php echo esc_url($hero_bg_url); ?>'); background-size: cover; background-position: center; background-color: magenta; z-index: 0; border: 5px solid lime;"></div>
    <div class="page-hero-overlay" style="border: 5px solid blue;"></div>
    <!-- 页面标题在内容区域显示 -->
    <!-- 波浪 -->
    <div class="waves-area" style="border: 5px solid cyan;">
        <svg class="waves-svg" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 24 150 28" preserveAspectRatio="none">
            <defs>
                <path id="gentle-wave" d="M-160 44c30 0 58-18 88-18s 58 18 88 18 58-18 88-18 58 18 88 18v44h-352z"/>
            </defs>
            <g class="parallax">
                <use xlink:href="#gentle-wave" x="48" y="0"/>
                <use xlink:href="#gentle-wave" x="48" y="3"/>
                <use xlink:href="#gentle-wave" x="48" y="5"/>
                <use xlink:href="#gentle-wave" x="48" y="7"/>
            </g>
        </svg>
    </div>
</section>

<?php
// 获取当前页码
$paged = get_query_var('paged') ? get_query_var('paged') : 1;

// 获取每页文章数设置
$per_page = get_theme_mod('brave_moments_per_page', 7);

// 构建查询
$args = array(
    'post_type' => 'moment',
    'posts_per_page' => $per_page,
    'orderby' => 'meta_value',
    'meta_key' => '_meet_date',
    'order' => 'DESC',
    'post_status' => 'publish',
    'paged' => $paged,
);

// 使用 WP_Query 支持分页
$query = new WP_Query($args);

// 获取所有年份（用于导航）
$all_years = brave_get_moment_years();

// 当前页的文章按年份分组
$grouped_moments = array();
$years = array();

if ($query->have_posts()) {
    while ($query->have_posts()) {
        $query->the_post();
        $meet_date = get_post_meta(get_the_ID(), '_meet_date', true);
        
        // 如果没有见面日期，使用发布日期
        if (empty($meet_date)) {
            $meet_date = get_the_date('Y-m-d');
        }
        
        $year = !empty($meet_date) ? substr($meet_date, 0, 4) : '未知';
        
        if (!isset($grouped_moments[$year])) {
            $grouped_moments[$year] = array();
            $years[] = $year;
        }
        $grouped_moments[$year][] = get_post();
    }
    wp_reset_postdata();
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
    <!-- 年份导航（显示所有年份，不仅当前页） -->
    <nav class="year-nav" id="yearNav">
        <a href="#all" class="year-nav-item active" data-year="all" onclick="return false;">全部</a>
        <?php foreach ($all_years as $year) : ?>
            <a href="#year-<?php echo esc_attr($year); ?>" class="year-nav-item" data-year="<?php echo esc_attr($year); ?>" onclick="return false;">
                <?php echo esc_html($year); ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <!-- 分页信息 -->
    <div class="pagination-info" style="text-align: center; margin-bottom: 1rem; color: var(--text-muted); font-size: 0.9rem;">
        共 <?php echo esc_html($query->found_posts); ?> 篇 / 第 <?php echo esc_html($paged); ?> 页
    </div>

    <!-- 时间轴 -->
    <div class="timeline-wrapper" id="timelineWrapper">
        <?php foreach ($years as $year) : ?>
            <div class="year-group" id="year-<?php echo esc_attr($year); ?>" data-year="<?php echo esc_attr($year); ?>">
                <div class="year-badge"><?php echo esc_html($year); ?></div>
                
                <div class="timeline">
                    <?php foreach ($grouped_moments[$year] as $moment) : 
                        $meet_date = get_post_meta($moment->ID, '_meet_date', true);
                        // 如果没有见面日期，使用发布日期
                        if (empty($meet_date)) {
                            $meet_date = get_the_date('Y-m-d', $moment->ID);
                        }
                        $location = get_post_meta($moment->ID, '_meet_location', true);
                        $mood = get_post_meta($moment->ID, '_mood', true);
                        $mood_text = brave_get_mood_text($mood);
                        $mood_emoji = brave_get_mood_emoji($mood);
                        $moment_summary = get_post_meta($moment->ID, '_moment_summary', true);
                        $has_thumbnail = has_post_thumbnail($moment->ID);
                        $moment_link = get_permalink($moment->ID);
                        
                        // 获取作者信息
                        $author_id = $moment->post_author;
                        $author_name = get_the_author_meta('display_name', $author_id);
                        
                        // 使用主题设置的头像（与 Hero 区域保持一致）
                        $boy_user_id = intval(get_theme_mod('brave_boy_user_id'));
                        $girl_user_id = intval(get_theme_mod('brave_girl_user_id'));
                        
                        if ($boy_user_id > 0 && $author_id == $boy_user_id) {
                            // 作者是男生
                            $author_avatar = brave_get_couple_avatar('boy', 40);
                        } elseif ($girl_user_id > 0 && $author_id == $girl_user_id) {
                            // 作者是女生
                            $author_avatar = brave_get_couple_avatar('girl', 40);
                        } else {
                            // 其他用户，使用默认头像
                            $author_avatar = 'https://ui-avatars.com/api/?name=' . urlencode($author_name) . '&size=40&background=ff5162&color=fff';
                        }
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
                                        </div>
                                        <div class="timeline-card-author">
                                            <img src="<?php echo esc_url($author_avatar); ?>" alt="" class="author-avatar">
                                            <span class="author-name"><?php echo esc_html($author_name); ?></span>
                                        </div>
                                    </div>
                                    
                                    <div class="timeline-card-body">
                                        <div class="timeline-card-content">
                                            <div class="timeline-card-excerpt">
                                                <?php 
                                                // 优先显示自定义摘要
                                                if (!empty($moment_summary)) {
                                                    echo wpautop(wp_kses_post($moment_summary));
                                                } else {
                                                    // 无摘要时截取内容前120字
                                                    echo wpautop(wp_trim_words($moment->post_content, 120));
                                                }
                                                ?>
                                            </div>
                                            
                                            <?php if ($has_thumbnail) : ?>
                                                <div class="timeline-card-media">
                                                    <?php echo get_the_post_thumbnail($moment->ID, 'medium', array('class' => 'timeline-card-image')); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="timeline-card-footer-meta">
                                            <?php if ($location) : ?>
                                                <div class="timeline-card-location">
                                                    <span class="location-icon">📍</span>
                                                    <span><?php echo esc_html($location); ?></span>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php if ($mood) : ?>
                                                <div class="timeline-card-mood">
                                                    <span class="mood-emoji"><?php echo $mood_emoji; ?></span>
                                                    <span class="mood-text"><?php echo esc_html($mood_text); ?></span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
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
    
    <!-- 分页导航 -->
    <?php if ($query->max_num_pages > 1) : 
        global $wp_rewrite;
        $format = '';
        $base = trailingslashit(get_permalink()) . '%_%';
        
        if ($wp_rewrite->using_permalinks()) {
            $format = 'page/%#%/';
        } else {
            $format = '?paged=%#%';
        }
    ?>
        <nav class="pagination" style="margin-top: 2rem; text-align: center;">
            <?php
            echo paginate_links(array(
                'base' => $base,
                'format' => $format,
                'current' => max(1, $paged),
                'total' => $query->max_num_pages,
                'prev_text' => '← 上一页',
                'next_text' => '下一页 →',
                'mid_size' => 2,
                'end_size' => 1,
            ));
            ?>
        </nav>
    <?php endif; ?>
    
    <?php else : ?>
        <div class="timeline-empty">
            <div class="timeline-empty-icon">📝</div>
            <p class="timeline-empty-text"><?php _e('还没有记录任何点滴，快去添加吧！', 'brave-love'); ?></p>
        </div>
    <?php endif; ?>
</section>

<?php
get_footer();
