<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Template Name: 随笔说说
 *
 * @package Brave_Love
 */

get_header();
get_template_part(
    'template-parts/page-hero',
    null,
    array(
        'title' => '📝 随笔说说',
        'subtitle' => '朝暮与年岁并往，我们一同走过寻常与漫长。',
    )
);

// 获取筛选参数
$filter_year = isset($_GET['filter_year']) ? absint(wp_unslash($_GET['filter_year'])) : 0;
$filter_month = isset($_GET['filter_month']) ? absint(wp_unslash($_GET['filter_month'])) : 0;
$filter_day = isset($_GET['filter_day']) ? absint(wp_unslash($_GET['filter_day'])) : 0;

$paged = max(1, absint(get_query_var('paged')));

// 构建查询参数
$args = array(
    'post_type' => 'note',
    'posts_per_page' => 12,
    'paged' => $paged,
    'orderby' => 'date',
    'order' => 'DESC',
);

// 添加日期筛选
if ($filter_year) {
    $args['date_query'] = array(
        array(
            'year' => $filter_year,
        ),
    );
    
    if ($filter_month) {
        $args['date_query'][0]['month'] = $filter_month;
        
        if ($filter_day) {
            $args['date_query'][0]['day'] = $filter_day;
        }
    }
}

$query = new WP_Query($args);

// 获取所有年份
$years = brave_get_note_years();

// 获取选中年的月份
$months = array();
if ($filter_year) {
    $months = brave_get_note_months($filter_year);
}

// 获取选中月的天数
$days = array();
if ($filter_year && $filter_month) {
    $days = brave_get_note_days($filter_year, $filter_month);
}

// 当前用户是否已登录
$is_logged_in = is_user_logged_in();
$current_user = wp_get_current_user();

$notes_filter_label = '';

if ($filter_year) {
    $notes_filter_label = $filter_year . '年';

    if ($filter_month) {
        $notes_filter_label .= ' ' . $filter_month . '月';

        if ($filter_day) {
            $notes_filter_label .= ' ' . $filter_day . '日';
        }
    }

    $notes_filter_label .= ' · 共 ' . intval($query->found_posts) . ' 条';
}
?>

<section class="content-section">
    <div class="page-shell page-shell-narrow">
        <div class="content-filter-shell notes-filter-shell">
            <?php if ($notes_filter_label) : ?>
                <div class="content-filter-heading">
                    <p class="content-filter-meta"><?php echo esc_html($notes_filter_label); ?></p>
                </div>
            <?php endif; ?>

            <div class="notes-filter-bar content-filter-actions">
                <!-- 全部按钮 -->
                <div class="filter-group">
                    <a href="<?php echo esc_url(remove_query_arg(array('filter_year', 'filter_month', 'filter_day'))); ?>" 
                       class="filter-btn <?php echo !$filter_year ? 'active' : ''; ?>">
                        全部
                    </a>
                </div>

                <!-- 年份筛选 - 始终显示 -->
                <div class="filter-group">
                    <button class="filter-dropdown-toggle <?php echo $filter_year ? 'has-value' : ''; ?>" data-toggle="year">
                        <?php echo $filter_year ? esc_html($filter_year . '年') : '年份'; ?>
                    </button>
                    <div class="filter-dropdown" id="year-dropdown">
                        <?php if (!empty($years)) : ?>
                            <?php foreach ($years as $year) : ?>
                                <a href="<?php echo esc_url(add_query_arg(array('filter_year' => $year, 'filter_month' => false, 'filter_day' => false))); ?>" 
                                   class="filter-option <?php echo $filter_year == $year ? 'active' : ''; ?>">
                                    <?php echo esc_html($year); ?>年
                                </a>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <span class="filter-option disabled">暂无数据</span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- 月份筛选 - 选择年份后才显示 -->
                <?php if ($filter_year) : ?>
                <div class="filter-group">
                    <button class="filter-dropdown-toggle <?php echo $filter_month ? 'has-value' : ''; ?>" data-toggle="month">
                        <?php echo $filter_month ? esc_html($filter_month . '月') : '月份'; ?>
                    </button>
                    <div class="filter-dropdown" id="month-dropdown">
                        <?php if (!empty($months)) : ?>
                            <?php foreach ($months as $month) : ?>
                                <a href="<?php echo esc_url(add_query_arg(array('filter_month' => $month, 'filter_day' => false))); ?>" 
                                   class="filter-option <?php echo $filter_month == $month ? 'active' : ''; ?>">
                                    <?php echo esc_html($month); ?>月
                                </a>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <span class="filter-option disabled">暂无数据</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- 日期筛选 - 选择月份后才显示 -->
                <?php if ($filter_year && $filter_month) : ?>
                <div class="filter-group">
                    <button class="filter-dropdown-toggle <?php echo $filter_day ? 'has-value' : ''; ?>" data-toggle="day">
                        <?php echo $filter_day ? esc_html($filter_day . '日') : '日期'; ?>
                    </button>
                    <div class="filter-dropdown" id="day-dropdown">
                        <?php if (!empty($days)) : ?>
                            <?php foreach ($days as $day) : ?>
                                <a href="<?php echo esc_url(add_query_arg('filter_day', $day)); ?>" 
                                   class="filter-option <?php echo $filter_day == $day ? 'active' : ''; ?>">
                                    <?php echo esc_html($day); ?>日
                                </a>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <span class="filter-option disabled">暂无数据</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

    <!-- 瀑布流说说列表 -->
    <?php if ($query->have_posts()) : ?>
        <div class="notes-waterfall" id="notesWaterfall">
            <?php while ($query->have_posts()) : $query->the_post(); 
                $note_mood = get_post_meta(get_the_ID(), '_note_mood', true);
                $note_miss_level_raw = absint(get_post_meta(get_the_ID(), '_note_miss_level', true));
                $note_miss_level = $note_miss_level_raw > 0 ? max(1, min(5, $note_miss_level_raw)) : 3;
                
                $author_id = get_the_author_meta('ID');
                $author_name = get_the_author();
                
                // 获取头像：优先使用主题设置的头像，否则使用 WordPress 头像
                $boy_user_id = absint(get_theme_mod('brave_boy_user_id'));
                $girl_user_id = absint(get_theme_mod('brave_girl_user_id'));
                
                if ($boy_user_id > 0 && $author_id == $boy_user_id) {
                    // 作者是男生
                    $author_avatar = brave_get_couple_avatar('boy', 100);
                } elseif ($girl_user_id > 0 && $author_id == $girl_user_id) {
                    // 作者是女生
                    $author_avatar = brave_get_couple_avatar('girl', 100);
                } else {
                    // 其他用户，优先使用站点内头像，否则回退到主题占位头像
                    $author_avatar = brave_get_person_avatar_url($author_id, $author_name, 100, 'ff5162');
                }
                
                $post_date = get_the_date('Y-m-d H:i');
            ?>
                <article class="note-waterfall-card">
                    <div class="note-card-header">
                        <img src="<?php echo brave_esc_avatar_url($author_avatar); ?>" alt="" class="note-author-avatar">
                        <div class="note-author-info">
                            <span class="note-author-name"><?php echo esc_html($author_name); ?></span>
                            <span class="note-datetime"><?php echo esc_html($post_date); ?></span>
                        </div>
                    </div>
                    
                    <div class="note-card-body">
                        <?php if ($note_mood) : ?>
                            <div class="note-mood-badge"><?php echo esc_html($note_mood); ?></div>
                        <?php endif; ?>
                        
                        <div class="note-content">
                            <?php the_content(); ?>
                        </div>
                    </div>
                    
                    <div class="note-card-footer">
                        <div class="note-miss-level">
                            <span class="note-miss-chip">
                                <span class="miss-label">思念度</span>
                                <span class="miss-stars">
                                    <?php echo str_repeat('⭐', $note_miss_level); ?>
                                </span>
                            </span>
                        </div>
                    </div>
                </article>
            <?php endwhile; ?>
        </div>

        <!-- 分页 -->
        <?php if ($query->max_num_pages > 1) : ?>
            <nav class="notes-pagination">
                <?php
                $base = get_pagenum_link(999999999);
                $base = str_replace('999999999', '%#%', $base);
                
                echo paginate_links(array(
                    'base' => $base,
                    'format' => '',
                    'current' => $paged,
                    'total' => $query->max_num_pages,
                    'prev_text' => '← 上一页',
                    'next_text' => '下一页 →',
                    'mid_size' => 2,
                    'add_args' => array_filter(array(
                        'filter_year' => $filter_year ?: false,
                        'filter_month' => $filter_month ?: false,
                        'filter_day' => $filter_day ?: false,
                    )),
                ));
                ?>
            </nav>
        <?php endif; ?>
        
        <?php wp_reset_postdata(); ?>
    <?php else : ?>
        <div class="notes-empty">
            <div class="notes-empty-icon">📝</div>
            <p class="notes-empty-text">还没有发布任何说说</p>
            <?php if (!$is_logged_in) : ?>
                <p class="notes-empty-hint">登录后可以发布你的第一条说说</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- 登录用户发布表单 - 移到说说列表下方 -->
    <?php if ($is_logged_in) : 
        // 获取当前用户头像
        $current_user_id = $current_user->ID;
        $boy_user_id = absint(get_theme_mod('brave_boy_user_id'));
        $girl_user_id = absint(get_theme_mod('brave_girl_user_id'));
        
        if ($boy_user_id > 0 && $current_user_id == $boy_user_id) {
            $current_avatar = brave_get_couple_avatar('boy', 100);
        } elseif ($girl_user_id > 0 && $current_user_id == $girl_user_id) {
            $current_avatar = brave_get_couple_avatar('girl', 100);
        } else {
            $current_avatar = brave_get_person_avatar_url($current_user_id, $current_user->display_name, 100, 'ff5162');
        }
    ?>
    <div class="note-publish-form">
        <div class="publish-header">
            <img src="<?php echo brave_esc_avatar_url($current_avatar); ?>" alt="" class="publish-avatar">
            <span class="publish-name"><?php echo esc_html($current_user->display_name); ?></span>
        </div>
        <form id="quick-note-form" method="post" action="">
            <?php wp_nonce_field('publish_note_action', 'publish_note_nonce'); ?>
            <textarea name="note_content" class="publish-textarea" placeholder="写下此刻的心情..." required></textarea>
            
            <div class="publish-options">
                <div class="option-group">
                    <label>心情</label>
                    <div class="mood-selector">
                        <button type="button" class="mood-btn" data-mood="😊">😊</button>
                        <button type="button" class="mood-btn" data-mood="😢">😢</button>
                        <button type="button" class="mood-btn" data-mood="🥰">🥰</button>
                        <button type="button" class="mood-btn" data-mood="😌">😌</button>
                        <button type="button" class="mood-btn" data-mood="🤔">🤔</button>
                        <button type="button" class="mood-btn" data-mood="😴">😴</button>
                    </div>
                    <input type="hidden" name="note_mood" id="selected-mood" value="😊">
                </div>
                
                <div class="option-group">
                    <label>思念度</label>
                    <div class="miss-level-selector">
                        <button type="button" class="star-btn" data-level="1">⭐</button>
                        <button type="button" class="star-btn" data-level="2">⭐</button>
                        <button type="button" class="star-btn" data-level="3">⭐</button>
                        <button type="button" class="star-btn" data-level="4">⭐</button>
                        <button type="button" class="star-btn" data-level="5">⭐</button>
                    </div>
                    <input type="hidden" name="note_miss_level" id="selected-miss-level" value="3">
                </div>
            </div>
            
            <div class="publish-actions">
                <button type="submit" name="publish_note" class="publish-submit">发布说说</button>
            </div>
        </form>
    </div>
    <?php endif; ?>
    </div>
</section>

<?php
get_footer();
