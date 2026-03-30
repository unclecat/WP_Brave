<?php
/**
 * Template Name: 祝福留言
 *
 * @package Brave_Love
 */

get_header();
?>

<section class="content-section">
    <div class="section-header">
        <h1 class="section-title">💌 祝福留言</h1>
        <p class="section-desc">收到你们的祝福是我们最大的幸福</p>
    </div>

    <div class="comments-section">
        <?php if (have_comments() || comments_open()) : ?>
            
            <!-- 评论统计 -->
            <div class="comment-count">
                <?php 
                $comment_count = get_comments_number();
                printf(
                    __('累计已经收到 %s 条祝福', 'brave-love'),
                    '<strong>' . $comment_count . '</strong>'
                ); 
                ?>
            </div>

            <!-- 评论列表 -->
            <?php if (have_comments()) : ?>
                <div class="comment-list">
                    <?php
                    wp_list_comments(array(
                        'style' => 'div',
                        'short_ping' => true,
                        'avatar_size' => 40,
                        'callback' => 'brave_comment_callback',
                        'reverse_top_level' => true,
                    ));
                    ?>
                </div>

                <!-- 评论分页 -->
                <?php if (get_comment_pages_count() > 1) : ?>
                    <div class="text-center mt-4">
                        <?php
                        paginate_comments_links(array(
                            'prev_text' => __('← 上一页', 'brave-love'),
                            'next_text' => __('下一页 →', 'brave-love'),
                        ));
                        ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <!-- 评论表单 -->
            <?php if (comments_open()) : ?>
                <div class="comment-form-wrapper">
                    <h3 class="comment-form-title">✨ 写下对我们的祝福</h3>
                    <?php
                    comment_form(array(
                        'class_form' => 'comment-form',
                        'title_reply' => '',
                        'title_reply_to' => '',
                        'cancel_reply_link' => __('取消回复', 'brave-love'),
                        'label_submit' => __('发送祝福', 'brave-love'),
                        'submit_button' => '<button type="submit" class="btn-submit">%4$s</button>',
                    ));
                    ?>
                </div>
            <?php else : ?>
                <p class="text-center" style="color: #999; padding: 2rem;">
                    <?php _e('留言板已关闭', 'brave-love'); ?>
                </p>
            <?php endif; ?>

        <?php endif; ?>
    </div>
</section>

<?php
get_footer();
