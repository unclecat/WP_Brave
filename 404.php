<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @package Brave_Love
 */

get_header();
?>

<section class="content-section">
    <div class="text-center" style="padding: 4rem 1rem;">
        <h1 class="section-title" style="font-size: 4rem; margin-bottom: 1rem;">404</h1>
        <p style="color: #999; font-size: 2rem; margin-bottom: 1rem;">😢</p>
        <p style="color: #666; margin-bottom: 2rem;"><?php _e('哎呀，页面走丢了...', 'brave-love'); ?></p>
        <a href="<?php echo esc_url(home_url('/')); ?>" class="btn-submit" style="display: inline-block; text-decoration: none;">
            <?php _e('返回首页', 'brave-love'); ?>
        </a>
    </div>
</section>

<?php
get_footer();
