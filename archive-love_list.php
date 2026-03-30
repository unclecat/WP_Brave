<?php
/**
 * 恋爱清单存档页面模板
 * Template for love_list CPT archive
 *
 * @package Brave_Love
 */

get_header();

// 获取当前分类筛选
$current_cat = get_query_var('list_category') ? get_term_by('slug', get_query_var('list_category'), 'list_category') : null;
$current_cat_id = $current_cat ? $current_cat->term_id : 0;

// 获取进度
$progress = brave_get_list_progress();

// 获取所有分类
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
            <a href="<?php echo esc_url(get_post_type_archive_link('love_list')); ?>" 
               class="filter-btn <?php echo $current_cat_id === 0 ? 'active' : ''; ?>">
                全部
            </a>
            <?php foreach ($categories as $cat) : ?>
                <a href="<?php echo esc_url(get_term_link($cat)); ?>" 
                   class="filter-btn <?php echo $current_cat_id === $cat->term_id ? 'active' : ''; ?>">
                    <?php echo esc_html($cat->name); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- 清单内容 -->
    <div class="love-list-content">
        <div class="container">
            <?php if (have_posts()) : ?>
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
                            <!-- 序号标签 -->
                            <span class="card-number"><?php echo sprintf('%02d', $index); ?></span>
                            
                            <!-- 完成标记 -->
                            <?php if ($is_done) : ?>
                                <div class="done-badge">
                                    <span>✓</span>
                                </div>
                            <?php endif; ?>

                            <!-- 卡片内容 -->
                            <div class="card-content">
                                <h3 class="card-title"><?php the_title(); ?></h3>
                                
                                <?php if ($is_done && $done_date) : ?>
                                    <div class="card-date">
                                        <span>📅</span> <?php echo esc_html($done_date); ?> 完成
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- 详情展开 -->
                            <?php if ($is_done && (get_the_content() || $has_thumbnail)) : ?>
                                <div class="card-detail">
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
                                
                                <button type="button" class="toggle-detail" onclick="toggleDetail(this)">
                                    <span>查看详情</span>
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M6 9l6 6 6-6"/>
                                    </svg>
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                </div>
                
                <!-- 分页 -->
                <?php if (get_previous_posts_link() || get_next_posts_link()) : ?>
                    <div class="pagination" style="margin-top: 2rem; text-align: center;">
                        <?php 
                        echo paginate_links(array(
                            'prev_text' => '← 上一页',
                            'next_text' => '下一页 →',
                        )); 
                        ?>
                    </div>
                <?php endif; ?>
                
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
