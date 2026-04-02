<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * The template for displaying archive pages
 *
 * @package Brave_Love
 */

get_header();
?>

<section class="content-section">
    <div class="section-header">
        <h1 class="section-title">
            <?php
            if (is_category()) :
                single_cat_title();
            elseif (is_tag()) :
                single_tag_title();
            elseif (is_author()) :
                printf(__('作者：%s', 'brave-love'), '<span>' . get_the_author() . '</span>');
            elseif (is_year()) :
                printf(__('年份：%s', 'brave-love'), '<span>' . get_the_date('Y') . '</span>');
            elseif (is_month()) :
                printf(__('月份：%s', 'brave-love'), '<span>' . get_the_date('F Y') . '</span>');
            elseif (is_day()) :
                printf(__('日期：%s', 'brave-love'), '<span>' . get_the_date() . '</span>');
            elseif (is_tax('list_category')) :
                single_term_title();
            else :
                _e('归档', 'brave-love');
            endif;
            ?>
        </h1>
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
            <p style="color: #666;"><?php _e('该分类下暂无内容', 'brave-love'); ?></p>
        </div>
    <?php endif; ?>
</section>

<?php
get_footer();
