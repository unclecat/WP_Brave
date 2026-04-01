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
<!-- 页面Hero区域 -->
<section class="page-hero-section">
    <div class="page-hero-bg" style="background-image: url('<?php echo !empty($hero_bg) ? esc_url($hero_bg) : 'https://images.unsplash.com/photo-1518199266791-5375a83190b7?w=1920'; ?>');"></div>
    <div class="page-hero-overlay"></div>
    <!-- 页面标题在内容区域显示 -->
    <!-- 波浪 -->
    <div class="waves-area">
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
// 获取已审核的评论
$comments = get_comments(array(
    'status' => 'approve', // 只显示已审核的
    'orderby' => 'comment_date',
    'order' => 'DESC',
    'number' => 50,
));
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
                $avatar = get_avatar_url($comment->comment_author_email, array('size' => 50));
                $time = human_time_diff(strtotime($comment->comment_date), current_time('timestamp')) . '前';
            ?>
                <article class="blessing-card">
                    <div class="blessing-card-header">
                        <img src="<?php echo esc_url($avatar); ?>" alt="" class="blessing-avatar">
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

        <!-- 加载更多 -->
        <?php 
        $total_comments = get_comments_number();
        if ($total_comments > 50) : 
        ?>
            <div class="blessing-load-more">
                <button type="button" class="btn-load-more" id="loadMore">
                    加载更多祝福
                </button>
            </div>
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
