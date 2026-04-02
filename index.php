<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * The main template file
 *
 * @package Brave_Love
 */

get_header();
?>

<section class="content-section">
    <div class="section-header">
        <h1 class="section-title">💕 我们的故事</h1>
        <p class="section-desc">欢迎来到我们的小窝</p>
    </div>

    <?php if (have_posts()) : ?>
        <div class="timeline">
            <?php while (have_posts()) : the_post(); ?>
                <div class="timeline-item">
                    <div class="timeline-dot"></div>
                    <span class="timeline-date"><?php echo get_the_date(); ?></span>
                    <div class="timeline-content">
                        <h4 class="timeline-title">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h4>
                        <div class="timeline-text">
                            <?php the_excerpt(); ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <div class="pagination">
            <?php echo paginate_links(); ?>
        </div>
    <?php else : ?>
        <div class="text-center" style="padding: 3rem 1rem;">
            <p style="color: #999; margin-bottom: 1rem;">📝</p>
            <p style="color: #666;"><?php _e('还没有发布任何内容', 'brave-love'); ?></p>
        </div>
    <?php endif; ?>
</section>

<?php
get_footer();
