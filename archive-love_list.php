<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * 恋爱清单存档页面模板
 * Template for love_list CPT archive
 *
 * @package Brave_Love
 */

get_header();
get_template_part(
    'template-parts/page-hero',
    null,
    array(
        'context' => 'love_list_archive',
        'title' => '💕 恋爱清单',
        'subtitle' => '记录我们想一起做的每一件事',
    )
);

global $wp_query;

// 获取当前分类筛选
$current_cat = null;
$current_status = isset($_GET['filter_status']) ? sanitize_key(wp_unslash($_GET['filter_status'])) : '';

if (!in_array($current_status, array('done', 'pending'), true)) {
    $current_status = '';
}

if (is_tax('list_category')) {
    $queried_object = get_queried_object();
    if ($queried_object instanceof WP_Term) {
        $current_cat = $queried_object;
    }
} elseif (get_query_var('list_category')) {
    $current_cat = get_term_by('slug', get_query_var('list_category'), 'list_category');
}

$current_cat_id = $current_cat instanceof WP_Term ? (int) $current_cat->term_id : 0;
$status_labels = array(
    'done' => '已完成',
    'pending' => '待完成',
);

// 获取进度
$progress = brave_get_list_progress($current_cat_id);
$pending_count = max(0, intval($progress['total']) - intval($progress['done']));

// 获取所有分类
$categories = get_terms(array(
    'taxonomy' => 'list_category',
    'hide_empty' => false,
));

$current_base_url = $current_cat instanceof WP_Term ? get_term_link($current_cat) : get_post_type_archive_link('love_list');
$done_url = ('done' === $current_status)
    ? remove_query_arg('filter_status', $current_base_url)
    : add_query_arg('filter_status', 'done', $current_base_url);
$pending_url = ('pending' === $current_status)
    ? remove_query_arg('filter_status', $current_base_url)
    : add_query_arg('filter_status', 'pending', $current_base_url);

$result_parts = array();

if ($current_cat) {
    $result_parts[] = sprintf('当前分类：%s', $current_cat->name);
}

if ($current_status) {
    $result_parts[] = sprintf('状态：%s', $status_labels[$current_status]);
}

$result_parts[] = sprintf('共 %d 项计划', intval($wp_query->found_posts));
$result_label = implode(' · ', $result_parts);
?>

<section class="love-list-section">
    <div class="love-list-overview page-shell page-shell-narrow">
        <div class="love-list-overview-card">
            <div class="love-list-progress">
                <div class="progress-stats">
                    <a href="<?php echo esc_url($done_url); ?>" class="stat-item stat-item-link <?php echo 'done' === $current_status ? 'active' : ''; ?>">
                        <span class="stat-number"><?php echo intval($progress['done']); ?></span>
                        <span class="stat-label">已完成</span>
                    </a>
                    <div class="stat-divider"></div>
                    <a href="<?php echo esc_url($pending_url); ?>" class="stat-item stat-item-link <?php echo 'pending' === $current_status ? 'active' : ''; ?>">
                        <span class="stat-number"><?php echo $pending_count; ?></span>
                        <span class="stat-label">待完成</span>
                    </a>
                    <div class="stat-divider"></div>
                    <a href="<?php echo esc_url($current_base_url); ?>" class="stat-item stat-item-link <?php echo '' === $current_status ? 'active' : ''; ?>">
                        <span class="stat-number"><?php echo intval($progress['percentage']); ?>%</span>
                        <span class="stat-label">完成度</span>
                    </a>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?php echo intval($progress['percentage']); ?>%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- 分类筛选 -->
    <?php if (!empty($categories) && !is_wp_error($categories)) : ?>
    <div class="love-list-filter-wrap page-shell page-shell-narrow">
        <div class="love-list-filter-panel">
            <span class="love-list-filter-label">筛选分类</span>
            <a href="<?php echo esc_url($current_status ? add_query_arg('filter_status', $current_status, get_post_type_archive_link('love_list')) : get_post_type_archive_link('love_list')); ?>" 
               class="filter-btn <?php echo $current_cat_id === 0 ? 'active' : ''; ?>">
                全部
            </a>
            <?php foreach ($categories as $cat) : ?>
                <?php
                $category_url = get_term_link($cat);
                if ($current_status) {
                    $category_url = add_query_arg('filter_status', $current_status, $category_url);
                }
                ?>
                <a href="<?php echo esc_url($category_url); ?>" 
                   class="filter-btn <?php echo $current_cat_id === $cat->term_id ? 'active' : ''; ?>">
                    <?php echo esc_html($cat->name); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- 清单内容 -->
    <div class="love-list-content">
        <div class="love-list-content-inner page-shell page-shell-narrow">
            <?php if (have_posts()) : ?>
                <div class="love-list-meta">
                    <p class="love-list-result-count"><?php echo esc_html($result_label); ?></p>
                </div>

                <div class="love-list-grid">
                    <?php 
                    $index = 0;
                    while (have_posts()) : 
                        the_post();
                        $index++;
                        $is_done = get_post_meta(get_the_ID(), '_is_done', true);
                        $done_date = get_post_meta(get_the_ID(), '_done_date', true);
                        $has_thumbnail = has_post_thumbnail();
                    ?>
                        <div class="love-list-card <?php echo $is_done ? 'done' : ''; ?>" data-id="<?php echo esc_attr(get_the_ID()); ?>">
                            <div class="card-headline">
                                <span class="card-number"><?php echo sprintf('%02d', $index); ?></span>
                                <span class="card-state <?php echo $is_done ? 'is-done' : 'is-pending'; ?>">
                                    <?php echo $is_done ? '已完成' : '待完成'; ?>
                                </span>
                            </div>

                            <div class="card-content">
                                <h3 class="card-title"><?php the_title(); ?></h3>

                                <?php if ($is_done && $done_date) : ?>
                                    <div class="card-date">
                                        <span>📅</span>
                                        <span><?php echo esc_html($done_date); ?> 完成</span>
                                    </div>
                                <?php else : ?>
                                    <div class="card-date card-date-placeholder">
                                        <span>⏳</span>
                                        <span>等你们把它变成回忆</span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <?php if ($is_done && (get_the_content() || $has_thumbnail)) : ?>
                                <div class="card-detail" id="love-list-detail-<?php echo esc_attr(get_the_ID()); ?>">
                                    <?php if ($has_thumbnail) : ?>
                                        <div class="detail-image">
                                            <?php the_post_thumbnail('medium', array('class' => 'card-image')); ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (get_the_content()) : ?>
                                        <div class="detail-text">
                                            <?php the_content(); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <button
                                    type="button"
                                    class="toggle-detail"
                                    onclick="toggleDetail(this)"
                                    aria-expanded="false"
                                    aria-controls="love-list-detail-<?php echo esc_attr(get_the_ID()); ?>"
                                >
                                    <span>查看详情</span>
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M6 9l6 6 6-6"/>
                                    </svg>
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                </div>
                
            <?php else : ?>
                <div class="empty-state">
                    <div class="empty-icon">📝</div>
                    <p class="empty-text">还没有添加任何事项</p>
                    <p class="empty-subtext">快去后台添加你们的恋爱清单吧！</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<script>
function toggleDetail(btn) {
    const card = btn.closest('.love-list-card');
    if (!card) {
        return;
    }

    const detail = card.querySelector('.card-detail');
    const span = btn.querySelector('span');

    if (!detail || !span) {
        return;
    }

    card.classList.toggle('expanded');
    const isExpanded = card.classList.contains('expanded');

    if (isExpanded) {
        detail.style.maxHeight = detail.scrollHeight + 'px';
        span.textContent = '收起详情';
    } else {
        detail.style.maxHeight = '0';
        span.textContent = '查看详情';
    }

    btn.setAttribute('aria-expanded', isExpanded ? 'true' : 'false');
}
</script>

<?php
get_footer();
