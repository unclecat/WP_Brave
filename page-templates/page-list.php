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

<section class="love-list-section">
    <!-- 页面标题 -->
    <div class="love-list-header">
        <div class="container text-center">
            <h1 class="love-list-title">💕 恋爱清单 💕</h1>
            <p class="love-list-subtitle">记录我们的一百件小事</p>
        </div>
    </div>

    <!-- 进度统计 -->
    <div class="love-list-progress">
        <div class="progress-stats">
            <div class="stat-item">
                <span class="stat-number"><?php echo intval($progress['done']); ?></span>
                <span class="stat-label">已完成</span>
            </div>
            <div class="stat-divider"></div>
            <div class="stat-item">
                <span class="stat-number"><?php echo intval($progress['total']) - intval($progress['done']); ?></span>
                <span class="stat-label">待完成</span>
            </div>
            <div class="stat-divider"></div>
            <div class="stat-item">
                <span class="stat-number"><?php echo intval($progress['percentage']); ?>%</span>
                <span class="stat-label">完成度</span>
            </div>
        </div>
        <div class="progress-bar">
            <div class="progress-fill" style="width: <?php echo intval($progress['percentage']); ?>%"></div>
        </div>
    </div>

    <!-- 分类筛选 -->
    <?php if (!empty($categories) && !is_wp_error($categories)) : ?>
    <div class="love-list-filters">
        <div class="container">
            <a href="<?php echo esc_url(get_permalink()); ?>" 
               class="filter-btn <?php echo $current_cat === 0 ? 'active' : ''; ?>">
                全部
            </a>
            <?php foreach ($categories as $cat) : ?>
                <a href="<?php echo esc_url(add_query_arg('cat', $cat->term_id, get_permalink())); ?>" 
                   class="filter-btn <?php echo $current_cat === $cat->term_id ? 'active' : ''; ?>">
                    <?php echo esc_html($cat->name); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- 清单内容 -->
    <div class="love-list-content">
        <div class="container">
            <?php if (!empty($items)) : ?>
                <div class="love-list-grid">
                    <?php foreach ($items as $index => $item) : 
                        $is_done = get_post_meta($item->ID, '_is_done', true);
                        $done_date = get_post_meta($item->ID, '_done_date', true);
                        $has_thumbnail = has_post_thumbnail($item->ID);
                        $number = $index + 1;
                    ?>
                        <div class="love-list-card <?php echo $is_done ? 'done' : ''; ?>" data-id="<?php echo esc_attr($item->ID); ?>">
                            <!-- 序号标签 -->
                            <span class="card-number"><?php echo sprintf('%02d', $number); ?></span>
                            
                            <!-- 完成标记 -->
                            <?php if ($is_done) : ?>
                                <div class="done-badge">
                                    <span>✓</span>
                                </div>
                            <?php endif; ?>

                            <!-- 卡片内容 -->
                            <div class="card-content">
                                <h3 class="card-title"><?php echo esc_html($item->post_title); ?></h3>
                                
                                <?php if ($is_done && $done_date) : ?>
                                    <div class="card-date">
                                        <span>📅</span> <?php echo esc_html($done_date); ?> 完成
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- 详情展开 -->
                            <?php if ($is_done && ($item->post_content || $has_thumbnail)) : ?>
                                <div class="card-detail">
                                    <?php if ($has_thumbnail) : ?>
                                        <div class="detail-image">
                                            <?php echo get_the_post_thumbnail($item->ID, 'medium', array('class' => 'card-image')); ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($item->post_content) : ?>
                                        <div class="detail-text">
                                            <?php echo wpautop(esc_html($item->post_content)); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <button type="button" class="toggle-detail" onclick="toggleDetail(this)">
                                    <span>查看详情</span>
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M6 9l6 6 6-6"/>
                                    </svg>
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
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
    const detail = card.querySelector('.card-detail');
    const span = btn.querySelector('span');
    
    card.classList.toggle('expanded');
    
    if (card.classList.contains('expanded')) {
        detail.style.maxHeight = detail.scrollHeight + 'px';
        span.textContent = '收起详情';
    } else {
        detail.style.maxHeight = '0';
        span.textContent = '查看详情';
    }
}
</script>

<?php
get_footer();
