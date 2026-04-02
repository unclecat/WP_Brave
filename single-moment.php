<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * The template for displaying single moment posts
 *
 * @package Brave_Love
 */

get_header();

while (have_posts()) :
    the_post();
    
    $meet_date = get_post_meta(get_the_ID(), '_meet_date', true);
    $location = get_post_meta(get_the_ID(), '_meet_location', true);
    $mood = get_post_meta(get_the_ID(), '_mood', true);
?>

<section class="content-section">
    <article id="post-<?php the_ID(); ?>" <?php post_class('moment-article'); ?>>
        
        <!-- 头部信息 -->
        <header class="moment-header">
            <div class="moment-header-row">
                <h1 class="moment-title"><?php the_title(); ?></h1>
                
                <div class="moment-date-tag">
                    <?php if ($meet_date) : ?>
                        <span><?php echo esc_html(substr($meet_date, 0, 4)); ?></span>
                        <span class="date-sep">/</span>
                        <span><?php echo esc_html(substr($meet_date, 5, 2)); ?></span>
                        <span class="date-sep">/</span>
                        <span><?php echo esc_html(substr($meet_date, 8, 2)); ?></span>
                    <?php else : ?>
                        <span>--</span>
                    <?php endif; ?>
                </div>
            </div>
        </header>

        <!-- 摘要 -->
        <?php 
        $moment_summary = get_post_meta(get_the_ID(), '_moment_summary', true);
        if (!empty($moment_summary)) : 
        ?>
            <div class="moment-summary">
                <?php echo wpautop($moment_summary); ?>
            </div>
        <?php endif; ?>

        <!-- 特色图片 -->
        <?php if (has_post_thumbnail()) : ?>
            <div class="moment-featured-image">
                <?php the_post_thumbnail('large', array('alt' => get_the_title())); ?>
            </div>
        <?php endif; ?>

        <!-- 内容 -->
        <div class="moment-content">
            <?php the_content(); ?>
        </div>

        <!-- 元信息 -->
        <div class="moment-meta">
            <?php if ($location || $mood) : ?>
                <div class="moment-meta-row">
                    <?php if ($location) : ?>
                        <span class="meta-tag">
                            <span class="meta-icon">📍</span>
                            <span><?php echo esc_html($location); ?></span>
                        </span>
                    <?php endif; ?>
                    
                    <?php if ($mood) : ?>
                        <span class="meta-tag">
                            <span class="meta-icon"><?php echo brave_get_mood_emoji($mood); ?></span>
                            <span><?php echo brave_get_mood_text($mood); ?></span>
                        </span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- 导航 -->
        <nav class="moment-navigation">
            <div class="nav-links">
                <?php
                $prev_post = get_previous_post();
                $next_post = get_next_post();
                ?>
                
                <?php if ($prev_post) : ?>
                    <a href="<?php echo esc_url(get_permalink($prev_post)); ?>" class="nav-prev">
                        <span class="nav-label">← 上一篇</span>
                        <span class="nav-title"><?php echo esc_html(get_the_title($prev_post)); ?></span>
                    </a>
                <?php else : ?>
                    <div class="nav-placeholder"></div>
                <?php endif; ?>
                
                <a href="<?php echo esc_url(get_permalink(get_page_by_path('moments'))); ?>" class="nav-back">
                    <span>📖 返回列表</span>
                </a>
                
                <?php if ($next_post) : ?>
                    <a href="<?php echo esc_url(get_permalink($next_post)); ?>" class="nav-next">
                        <span class="nav-label">下一篇 →</span>
                        <span class="nav-title"><?php echo esc_html(get_the_title($next_post)); ?></span>
                    </a>
                <?php else : ?>
                    <div class="nav-placeholder"></div>
                <?php endif; ?>
            </div>
        </nav>
    </article>
</section>

<?php
endwhile;
get_footer();
