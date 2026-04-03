<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Template Name: 点点滴滴
 *
 * @package Brave_Love
 */

get_header();
get_template_part(
    'template-parts/page-hero',
    null,
    array(
        'title' => '💖 点点滴滴',
        'subtitle' => '岁月为笔，你我为墨，写下一段独属于我们的故事。',
    )
);

// 获取筛选参数
$current_year = isset($_GET['filter_year']) ? absint(wp_unslash($_GET['filter_year'])) : 0;

// 获取当前页码
$paged = get_query_var('paged') ? get_query_var('paged') : 1;

// 获取每页文章数设置
$per_page = get_theme_mod('brave_moments_per_page', 7);

// 获取所有年份（用于导航）
$all_years = brave_get_moment_years();

// 年份筛选链接基准地址
$moments_base_url = get_permalink();

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

// 使用真正的年份筛选，而不是当前页锚点跳转
if ($current_year > 0) {
    $filtered_ids = brave_get_moment_ids_by_year($current_year);
    $args['post__in'] = !empty($filtered_ids) ? $filtered_ids : array(0);
    $args['orderby'] = 'post__in';
    unset($args['meta_key']);
}

// 使用 WP_Query 支持分页
$query = new WP_Query($args);

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
    <div class="page-shell page-shell-narrow">
        <?php if (!empty($all_years)) : ?>
        <div class="content-filter-shell moments-filter-shell">
            <div class="moments-filter-controls content-filter-actions">
                <a href="<?php echo esc_url($moments_base_url); ?>" class="filter-btn <?php echo $current_year === 0 ? 'active' : ''; ?>">
                    全部
                </a>

                <div class="filter-group">
                    <button class="filter-dropdown-toggle <?php echo $current_year > 0 ? 'has-value' : ''; ?>" data-toggle="moments-year">
                        <?php echo $current_year > 0 ? esc_html($current_year . '年') : '年份'; ?>
                    </button>
                    <div class="filter-dropdown" id="moments-year-dropdown">
                        <?php foreach ($all_years as $year) : ?>
                            <a href="<?php echo esc_url(add_query_arg('filter_year', $year, $moments_base_url)); ?>"
                               class="filter-option <?php echo $current_year === intval($year) ? 'active' : ''; ?>">
                                <?php echo esc_html($year); ?>年
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($years)) : ?>

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
                                // 其他用户，优先使用站点内头像，否则回退到主题占位头像
                                $author_avatar = brave_get_person_avatar_url($author_id, $author_name, 40, 'ff5162');
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
                                                <img src="<?php echo brave_esc_avatar_url($author_avatar); ?>" alt="" class="author-avatar">
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
            $base = trailingslashit($moments_base_url) . 'page/%#%/';
            
            if (!$wp_rewrite->using_permalinks()) {
                $base = add_query_arg('paged', '%#%', $moments_base_url);
            }
        ?>
            <nav class="pagination" style="margin-top: 2rem; text-align: center;">
                <?php
                echo paginate_links(array(
                    'base' => $base,
                    'format' => '',
                    'current' => max(1, $paged),
                    'total' => $query->max_num_pages,
                    'prev_text' => '← 上一页',
                    'next_text' => '下一页 →',
                    'mid_size' => 2,
                    'end_size' => 1,
                    'add_args' => array_filter(array(
                        'filter_year' => $current_year ?: false,
                    )),
                ));
                ?>
            </nav>
        <?php endif; ?>
        
        <?php else : ?>
            <div class="timeline-empty">
                <div class="timeline-empty-icon">📝</div>
                <?php if ($current_year > 0) : ?>
                    <p class="timeline-empty-text"><?php echo esc_html($current_year); ?> 年还没有记录任何点滴</p>
                    <p class="timeline-empty-hint"><a href="<?php echo esc_url($moments_base_url); ?>">查看全部点滴</a></p>
                <?php else : ?>
                    <p class="timeline-empty-text"><?php _e('还没有记录任何点滴，快去添加吧！', 'brave-love'); ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php
get_footer();
