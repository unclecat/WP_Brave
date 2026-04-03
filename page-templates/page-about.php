<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Template Name: 关于我们
 *
 * @package Brave_Love
 */

get_header();

if (have_posts()) {
    the_post();
}

$page_id = get_the_ID();
$page_intro_raw = get_post_field('post_content', $page_id);
$page_intro = '' !== trim((string) $page_intro_raw) ? apply_filters('the_content', $page_intro_raw) : '';

$story_query = new WP_Query(
    array(
        'post_type' => 'story_milestone',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'meta_key' => '_story_date',
        'meta_type' => 'DATE',
        'orderby' => array(
            'meta_value' => 'ASC',
            'date' => 'ASC',
        ),
        'order' => 'ASC',
        'ignore_sticky_posts' => true,
    )
);

$story_posts = $story_query->posts;
$story_count = count($story_posts);
$first_story = $story_count > 0 ? $story_posts[0] : null;
$last_story = $story_count > 0 ? $story_posts[$story_count - 1] : null;

$format_story_date = static function ($date_value, $fallback = '') {
    $date_value = trim((string) $date_value);

    if ('' === $date_value) {
        return $fallback ? $fallback : __('待补充', 'brave-love');
    }

    $timestamp = strtotime($date_value);

    if (!$timestamp) {
        return $date_value;
    }

    return wp_date('Y年n月j日', $timestamp);
};

$start_date = $first_story ? get_post_meta($first_story->ID, '_story_date', true) : '';
$latest_date = $last_story ? get_post_meta($last_story->ID, '_story_date', true) : '';
$latest_story_title = __('待补充', 'brave-love');
$latest_story_date_label = '';

if ($last_story) {
    $latest_story_title = get_the_title($last_story);

    if ($latest_date) {
        $latest_story_date_label = $format_story_date($latest_date);
    }
}

get_template_part(
    'template-parts/page-hero',
    null,
    array(
        'post_id' => $page_id,
        'title' => '💞 关于我们',
        'subtitle' => '从初见走到相守，从陌生走到熟悉，这一路，皆是我们。',
    )
);
?>

<section class="content-section about-page-section">
    <div class="page-shell page-shell-narrow about-page-shell">
        <?php if ($page_intro || $story_count > 0) : ?>
            <div class="about-intro-grid">
                <?php if ($page_intro) : ?>
                    <article class="about-panel about-intro-card">
                        <span class="about-section-label"><?php esc_html_e('写在前面', 'brave-love'); ?></span>
                        <div class="about-intro-content">
                            <?php echo $page_intro; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                        </div>
                    </article>
                <?php endif; ?>

                <?php if ($story_count > 0) : ?>
                    <aside class="about-panel about-overview-card">
                        <span class="about-section-label"><?php esc_html_e('故事总览', 'brave-love'); ?></span>
                        <div class="about-overview-list">
                            <div class="about-overview-item">
                                <span class="about-overview-label"><?php esc_html_e('节点数量', 'brave-love'); ?></span>
                                <strong class="about-overview-value"><?php echo esc_html($story_count); ?></strong>
                            </div>

                            <div class="about-overview-item">
                                <span class="about-overview-label"><?php esc_html_e('故事起点', 'brave-love'); ?></span>
                                <strong class="about-overview-value"><?php echo esc_html($format_story_date($start_date)); ?></strong>
                            </div>

                            <div class="about-overview-item">
                                <span class="about-overview-label"><?php esc_html_e('最近节点', 'brave-love'); ?></span>
                                <div class="about-overview-stack">
                                    <?php if ($latest_story_date_label) : ?>
                                        <span class="about-overview-note"><?php echo esc_html($latest_story_date_label); ?></span>
                                    <?php endif; ?>

                                    <strong class="about-overview-value about-overview-value-secondary"><?php echo esc_html($latest_story_title); ?></strong>
                                </div>
                            </div>
                        </div>
                    </aside>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($story_query->have_posts()) : ?>
            <div class="about-timeline">
                <?php
                while ($story_query->have_posts()) :
                    $story_query->the_post();

                    $story_id = get_the_ID();
                    $story_date = get_post_meta($story_id, '_story_date', true);
                    $story_phase = get_post_meta($story_id, '_story_phase', true);
                    $story_summary = get_post_meta($story_id, '_story_summary', true);
                    $story_content_raw = get_the_content();
                    $story_content = '' !== trim(wp_strip_all_tags($story_content_raw))
                        ? apply_filters('the_content', $story_content_raw)
                        : '';

                    if (empty($story_summary)) {
                        $story_summary = wp_trim_words(wp_strip_all_tags($story_content_raw), 36, '...');
                    }

                    $related_moment_id = (int) get_post_meta($story_id, '_related_moment_id', true);
                    $related_moment_url = '';
                    $related_moment_label = '';

                    if ($related_moment_id > 0 && 'publish' === get_post_status($related_moment_id)) {
                        $related_moment_url = get_permalink($related_moment_id);
                        $related_moment_date = get_post_meta($related_moment_id, '_meet_date', true);
                        $related_moment_label = get_the_title($related_moment_id);

                        if ($related_moment_date) {
                            $related_moment_label = $format_story_date($related_moment_date) . ' · ' . $related_moment_label;
                        }
                    }
                    ?>
                    <article class="story-node">
                        <div class="story-node-marker" aria-hidden="true">
                            <span class="story-node-dot"></span>
                        </div>

                        <div class="story-node-card">
                            <header class="story-node-header">
                                <div class="story-node-meta">
                                    <span class="story-node-date"><?php echo esc_html($format_story_date($story_date, get_the_date('Y年n月j日'))); ?></span>

                                    <?php if (!empty($story_phase)) : ?>
                                        <span class="story-node-phase"><?php echo esc_html($story_phase); ?></span>
                                    <?php endif; ?>
                                </div>

                                <h2 class="story-node-title"><?php the_title(); ?></h2>

                                <?php if (!empty($story_summary)) : ?>
                                    <p class="story-node-summary"><?php echo esc_html($story_summary); ?></p>
                                <?php endif; ?>
                            </header>

                            <?php if (has_post_thumbnail()) : ?>
                                <figure class="story-node-media">
                                    <?php the_post_thumbnail('large', array('class' => 'story-node-image')); ?>
                                </figure>
                            <?php endif; ?>

                            <?php if ($story_content) : ?>
                                <div class="story-node-content">
                                    <?php echo $story_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($related_moment_url) : ?>
                                <footer class="story-node-footer">
                                    <a class="story-node-link" href="<?php echo esc_url($related_moment_url); ?>">
                                        <?php
                                        printf(
                                            /* translators: %s: related moment label */
                                            esc_html__('关联点点滴滴：%s', 'brave-love'),
                                            esc_html($related_moment_label)
                                        );
                                        ?>
                                    </a>
                                </footer>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>
        <?php else : ?>
            <div class="about-panel about-empty-state">
                <div class="about-empty-icon">🕊️</div>
                <h2 class="about-empty-title"><?php esc_html_e('故事线还没有开始整理', 'brave-love'); ?></h2>
                <p class="about-empty-text"><?php esc_html_e('先在后台添加几个“故事节点”，这里就会按时间顺序自动排成一条完整的时间线。', 'brave-love'); ?></p>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php
wp_reset_postdata();
get_footer();
?>
