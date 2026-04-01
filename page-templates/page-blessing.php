<?php
/**
 * Template Name: 祝福留言
 *
 * @package Brave_Love
 */

get_header();

// 获取评论
$comments = get_comments(array(
    'status' => 'approve',
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

    <!-- 统计和发送按钮 -->
    <div class="blessing-header">
        <div class="blessing-count">
            <?php 
            $comment_count = get_comments_number();
            printf(
                __('累计收到 %s 条祝福', 'brave-love'),
                '<strong>' . $comment_count . '</strong>'
            ); 
            ?>
        </div>
        
        <?php if (comments_open()) : ?>
            <button type="button" class="blessing-toggle-btn" id="toggleForm">
                <span class="toggle-icon">✨</span>
                <span class="toggle-text">写下祝福</span>
            </button>
        <?php endif; ?>
    </div>

    <!-- 展开式表单 -->
    <?php if (comments_open()) : ?>
        <div class="blessing-form-wrapper" id="blessingForm" style="display: none;">
            <div class="blessing-form-inner">
                <h3 class="blessing-form-title">✨ 写下对我们的祝福</h3>
                <?php
                comment_form(array(
                    'class_form' => 'blessing-form',
                    'title_reply' => '',
                    'title_reply_to' => '',
                    'cancel_reply_link' => __('取消回复', 'brave-love'),
                    'label_submit' => __('发送祝福', 'brave-love'),
                    'submit_button' => '<button type="submit" class="btn-submit">%4$s</button>',
                    'comment_field' => '<div class="form-group"><textarea name="comment" class="form-control" placeholder="写下你的祝福..." rows="4" required></textarea></div>',
                    'fields' => array(
                        'author' => '<div class="form-row"><div class="form-group"><input type="text" name="author" class="form-control" placeholder="你的名字 *" required></div>',
                        'email' => '<div class="form-group"><input type="email" name="email" class="form-control" placeholder="你的邮箱 *" required></div></div>',
                    ),
                ));
                ?>
            </div>
        </div>
    <?php endif; ?>

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
        <?php if ($comment_count > 50) : ?>
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
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 展开/收起表单
    const toggleBtn = document.getElementById('toggleForm');
    const formWrapper = document.getElementById('blessingForm');
    
    if (toggleBtn && formWrapper) {
        toggleBtn.addEventListener('click', function() {
            const isVisible = formWrapper.style.display !== 'none';
            formWrapper.style.display = isVisible ? 'none' : 'block';
            toggleBtn.classList.toggle('active', !isVisible);
            
            if (!isVisible) {
                formWrapper.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        });
    }
});
</script>

<?php
get_footer();
