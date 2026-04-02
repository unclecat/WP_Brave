<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * The template for displaying all single posts
 *
 * @package Brave_Love
 */

get_header();

while (have_posts()) :
    the_post();
?>

<section class="content-section">
    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
        <header class="section-header">
            <h1 class="section-title"><?php the_title(); ?></h1>
            <p class="section-desc">
                <time datetime="<?php echo get_the_date('c'); ?>"><?php echo get_the_date(); ?></time>
            </p>
        </header>

        <div class="timeline-content" style="max-width: 100%;">
            <?php if (has_post_thumbnail()) : ?>
                <div style="margin-bottom: 1.5rem;">
                    <?php the_post_thumbnail('large', array('style' => 'width: 100%; border-radius: 12px;')); ?>
                </div>
            <?php endif; ?>

            <div class="entry-content" style="font-size: 1rem; line-height: 1.8; color: var(--text-color);">
                <?php the_content(); ?>
            </div>

            <?php
            // 根据文章类型显示额外信息
            $post_type = get_post_type();
            
            if ($post_type === 'moment') :
                $meet_date = get_post_meta(get_the_ID(), '_meet_date', true);
                $location = get_post_meta(get_the_ID(), '_meet_location', true);
                $mood = get_post_meta(get_the_ID(), '_mood', true);
            ?>
                <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color);">
                    <?php if ($meet_date) : ?>
                        <p style="margin-bottom: 0.5rem;"><strong>见面日期：</strong><?php echo esc_html($meet_date); ?></p>
                    <?php endif; ?>
                    <?php if ($location) : ?>
                        <p style="margin-bottom: 0.5rem;"><strong>地点：</strong><?php echo esc_html($location); ?></p>
                    <?php endif; ?>
                    <?php if ($mood) : ?>
                        <p style="margin-bottom: 0.5rem;"><strong>心情：</strong><?php echo brave_get_mood_emoji($mood) . ' ' . brave_get_mood_text($mood); ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ($post_type === 'love_list') :
                $is_done = get_post_meta(get_the_ID(), '_is_done', true);
                $done_date = get_post_meta(get_the_ID(), '_done_date', true);
            ?>
                <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color);">
                    <p style="margin-bottom: 0.5rem;"><strong>状态：</strong><?php echo $is_done ? '✅ 已完成' : '⭕ 待完成'; ?></p>
                    <?php if ($is_done && $done_date) : ?>
                        <p><strong>完成日期：</strong><?php echo esc_html($done_date); ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>


        </div>

        <nav class="navigation post-navigation" style="margin-top: 2rem;">
            <div class="nav-links" style="display: flex; justify-content: space-between; gap: 1rem;">
                <?php
                previous_post_link('<div class="nav-previous">%link</div>', '← %title');
                next_post_link('<div class="nav-next">%link</div>', '%title →');
                ?>
            </div>
        </nav>
    </article>
</section>

<?php
endwhile;

get_footer();
