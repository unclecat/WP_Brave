<?php
/**
 * Template Name: 祝福留言
 *
 * @package Brave_Love
 */

get_header();

// 获取Hero背景图
$hero_bg = get_theme_mod('brave_hero_bg');

?>
<!-- Hero区域已移除 -->

<?php
// 当前页的祝福留言分页
$comments_per_page = 50;
$blessing_page = isset($_GET['blessing_page']) ? max(1, intval($_GET['blessing_page'])) : 1;
$comment_offset = ($blessing_page - 1) * $comments_per_page;
$current_page_id = get_the_ID();

// 获取当前留言页的已审核评论
$comments = get_comments(array(
    'post_id' => $current_page_id,
    'status' => 'approve', // 只显示已审核的
    'orderby' => 'comment_date',
    'order' => 'DESC',
    'number' => $comments_per_page,
    'offset' => $comment_offset,
));

$total_comments = get_comments(array(
    'post_id' => $current_page_id,
    'status' => 'approve',
    'count' => true,
));

$max_comment_pages = $total_comments > 0 ? (int) ceil($total_comments / $comments_per_page) : 1;
?>

<section class="content-section">
    <div class="section-header">
        <h1 class="section-title">💌 祝福留言</h1>
        <p class="section-desc">收到你们的祝福是我们最大的幸福</p>
    </div>

    <!-- 祝福瀑布流 -->
    <?php if (!empty($comments)) : ?>
        <div class="blessing-waterfall" id="blessingWaterfall">
            <?php foreach ($comments as $comment) : 
                $avatar = brave_get_blessing_avatar_url($comment);
                $time = human_time_diff(strtotime($comment->comment_date), current_time('timestamp')) . '前';
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
            <nav class="pagination" style="margin-top: 2rem; text-align: center;">
                <?php
                echo paginate_links(array(
                    'base' => esc_url_raw(add_query_arg('blessing_page', '%#%')),
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
                    <input type="hidden" name="comment_post_ID" value="<?php echo get_the_ID(); ?>" id="comment_post_ID">
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
</section>

<?php
get_footer();
