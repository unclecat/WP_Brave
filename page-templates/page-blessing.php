<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Template Name: 祝福留言
 *
 * @package Brave_Love
 */

get_header();
get_template_part(
    'template-parts/page-hero',
    null,
    array(
        'title' => '💌 祝福留言',
        'subtitle' => '我们的故事，不长不短，刚好是一生。',
    )
);

// 当前页的祝福留言分页
$comments_per_page = 50;
$blessing_page = isset($_GET['blessing_page']) ? max(1, intval(wp_unslash($_GET['blessing_page']))) : 1;
$filter_year = isset($_GET['filter_year']) ? max(0, intval(wp_unslash($_GET['filter_year']))) : 0;
$comment_offset = ($blessing_page - 1) * $comments_per_page;
$current_page_id = absint(get_the_ID());
global $wpdb;

$comment_years = array_map(
    'intval',
    (array) $wpdb->get_col(
        $wpdb->prepare(
            "SELECT DISTINCT YEAR(comment_date)
            FROM {$wpdb->comments}
            WHERE comment_post_ID = %d
                AND comment_approved = '1'
            ORDER BY YEAR(comment_date) DESC",
            $current_page_id
        )
    )
);

$comment_query_args = array(
    'post_id' => $current_page_id,
    'status' => 'approve',
    'orderby' => 'comment_date',
    'order' => 'DESC',
);

if ($filter_year) {
    $comment_query_args['date_query'] = array(
        array(
            'year' => $filter_year,
        ),
    );
}

// 获取当前留言页的已审核评论
$comments = get_comments(array_merge($comment_query_args, array(
    // 只显示已审核的
    'number' => $comments_per_page,
    'offset' => $comment_offset,
)));

$total_comments = get_comments(array_merge($comment_query_args, array(
    'count' => true,
)));

$max_comment_pages = $total_comments > 0 ? (int) ceil($total_comments / $comments_per_page) : 1;
$blessing_filter_label = '';

if ($filter_year) {
    $blessing_filter_label = $filter_year . '年 · 共 ' . intval($total_comments) . ' 条';
}
?>

<section class="content-section">
    <div class="page-shell page-shell-narrow">
        <?php if (!empty($comment_years)) : ?>
        <div class="content-filter-shell blessing-filter-shell">
            <?php if ($blessing_filter_label) : ?>
                <div class="content-filter-heading">
                    <p class="content-filter-meta"><?php echo esc_html($blessing_filter_label); ?></p>
                </div>
            <?php endif; ?>

            <div class="blessing-filter-bar content-filter-actions">
                <div class="filter-group">
                    <a href="<?php echo esc_url(remove_query_arg(array('filter_year', 'blessing_page'))); ?>"
                       class="filter-btn <?php echo !$filter_year ? 'active' : ''; ?>">
                        全部
                    </a>
                </div>

                <div class="filter-group">
                    <button class="filter-dropdown-toggle <?php echo $filter_year ? 'has-value' : ''; ?>" data-toggle="year">
                        <?php echo $filter_year ? esc_html($filter_year . '年') : '年份'; ?>
                    </button>

                    <div class="filter-dropdown" id="year-dropdown">
                        <?php foreach ($comment_years as $year) : ?>
                            <a href="<?php echo esc_url(add_query_arg(array('filter_year' => $year, 'blessing_page' => false))); ?>"
                               class="filter-option <?php echo $filter_year === $year ? 'active' : ''; ?>">
                                <?php echo esc_html($year . '年'); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- 祝福瀑布流 -->
        <?php if (!empty($comments)) : ?>
        <div class="blessing-waterfall" id="blessingWaterfall">
            <?php foreach ($comments as $comment) : 
                $avatar = brave_get_blessing_avatar_url($comment);
                $time = mysql2date('Y.m.d', $comment->comment_date);
            ?>
                <article class="blessing-card">
                    <div class="blessing-card-header">
                        <img src="<?php echo brave_esc_avatar_url($avatar); ?>" alt="<?php echo esc_attr($comment->comment_author); ?>" class="blessing-avatar" loading="lazy">
                        <div class="blessing-author-info">
                            <span class="blessing-author"><?php echo esc_html($comment->comment_author); ?></span>
                            <span class="blessing-time"><?php echo esc_html($time); ?></span>
                        </div>
                    </div>
                    <div class="blessing-content">
                        <?php echo wpautop(esc_html($comment->comment_content)); ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <?php if ($max_comment_pages > 1) : ?>
            <nav class="pagination">
                <?php
                echo paginate_links(array(
                    'base' => esc_url_raw(add_query_arg(array(
                        'blessing_page' => '%#%',
                        'filter_year' => $filter_year ?: false,
                    ))),
                    'format' => '',
                    'current' => $blessing_page,
                    'total' => $max_comment_pages,
                    'prev_text' => '← 上一页',
                    'next_text' => '下一页 →',
                ));
                ?>
            </nav>
        <?php endif; ?>
        <?php else : ?>
        <div class="blessing-empty">
            <div class="empty-icon">💌</div>
            <p class="empty-text">还没有收到祝福，快来写下第一条吧！</p>
        </div>
        <?php endif; ?>

        <!-- 发送祝福表单 - 固定在底部 -->
        <?php if (comments_open()) : ?>
        <div class="blessing-form-section">
            <div class="blessing-form-header">
                <span class="form-icon">✨</span>
                <span class="form-title">写下对我们的祝福</span>
            </div>
            
            <div class="blessing-form-box">
                <form action="<?php echo esc_url(site_url('/wp-comments-post.php')); ?>" method="post" class="blessing-form">
                    <input type="hidden" name="comment_post_ID" value="<?php echo esc_attr(get_the_ID()); ?>" id="comment_post_ID">
                    <input type="hidden" name="comment_parent" value="0" id="comment_parent">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <input type="text" name="author" class="form-input" placeholder="你的昵称 *" required>
                        </div>
                        <div class="form-group">
                            <input type="email" name="email" class="form-input" placeholder="你的邮箱 *" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <textarea name="comment" class="form-textarea" placeholder="写下你的祝福..." rows="4" required></textarea>
                    </div>
                    
                    <div class="form-notice">
                        <span class="notice-icon">💡</span>
                        <span class="notice-text">祝福发送后需要审核才会显示</span>
                    </div>
                    
                    <div class="form-submit">
                        <button type="submit" class="btn-submit-blessing">
                            <span>发送祝福</span>
                            <span class="btn-icon">💌</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <?php else : ?>
        <div class="blessing-closed">
            <p>留言板已关闭</p>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php
get_footer();
