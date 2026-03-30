<?php
/**
 * Template Name: 随笔说说
 *
 * @package Brave_Love
 */

get_header();

$paged = get_query_var('paged') ? get_query_var('paged') : 1;

$args = array(
    'post_type' => 'note',
    'posts_per_page' => 10,
    'paged' => $paged,
    'orderby' => 'date',
    'order' => 'DESC',
);

$query = new WP_Query($args);
?>

<section class="content-section">
    <div class="section-header">
        <h1 class="section-title">📝 随笔说说</h1>
        <p class="section-desc">记录生活的点滴心情</p>
    </div>

    <!-- 说说列表 -->
    <?php if ($query->have_posts()) : ?>
        <div class="notes-stream">
            <?php while ($query->have_posts()) : $query->the_post(); 
                $note_mood = get_post_meta(get_the_ID(), '_note_mood', true);
                $note_images = brave_get_note_images(get_the_ID());
                $image_count = count($note_images);
            ?>
                <article class="note-card fade-in">
                    <div class="note-header">
                        <span class="note-time"><?php echo human_time_diff(get_the_time('U'), current_time('timestamp')) . __('前', 'brave-love'); ?></span>
                        <?php if ($note_mood) : ?>
                            <span class="note-mood"><?php echo esc_html($note_mood); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (get_the_content()) : ?>
                        <div class="note-content">
                            <?php the_content(); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($note_images)) : ?>
                        <div class="note-images count-<?php echo min($image_count, 9); ?>">
                            <?php foreach ($note_images as $index => $image) : 
                                if ($index >= 9) break;
                            ?>
                                <img src="<?php echo esc_url($image['url']); ?>" 
                                     data-full="<?php echo esc_url($image['url']); ?>"
                                     data-thumb="<?php echo esc_url($image['thumb']); ?>"
                                     alt="" 
                                     class="note-image"
                                     loading="lazy">
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </article>
            <?php endwhile; ?>
        </div>

        <!-- 分页 -->
        <?php if ($query->max_num_pages > 1) : ?>
            <div class="text-center mt-4">
                <?php
                echo paginate_links(array(
                    'total' => $query->max_num_pages,
                    'current' => $paged,
                    'prev_text' => __('← 上一页', 'brave-love'),
                    'next_text' => __('下一页 →', 'brave-love'),
                    'mid_size' => 1,
                ));
                ?>
            </div>
        <?php endif; ?>
        
        <?php wp_reset_postdata(); ?>
    <?php else : ?>
        <div class="text-center" style="padding: 3rem 1rem;">
            <p style="color: #999; margin-bottom: 1rem;">📝</p>
            <p style="color: #666;"><?php _e('还没有发布任何说说，快去记录心情吧！', 'brave-love'); ?></p>
        </div>
    <?php endif; ?>
</section>

<?php
get_footer();
