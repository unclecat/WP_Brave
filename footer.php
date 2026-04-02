<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
</main><!-- #main -->

<!-- 页脚 -->
<footer class="footer">
    <div class="footer-text footer-copyright">
        <?php if (get_theme_mod('brave_pv_enabled', '1')) : ?>
        <?php $pv = brave_get_pv_stats(); ?>
        <span class="pv-stats-inline">
            <span class="pv-label"><?php echo esc_html(brave_get_pv_display_text('today_prefix')); ?></span>
            <span class="pv-number"><?php echo number_format($pv['today_count']); ?></span>
            <span class="pv-label"><?php echo esc_html(brave_get_pv_display_text('today_suffix')); ?></span>
            <span class="pv-separator">·</span>
            <span class="pv-label"><?php echo esc_html(brave_get_pv_display_text('total_prefix')); ?></span>
            <span class="pv-number"><?php echo number_format($pv['total_count']); ?></span>
            <span class="pv-label"><?php echo esc_html(brave_get_pv_display_text('total_suffix')); ?></span>
        </span>
        <span class="pv-copyright-separator">©</span>
        <?php endif; ?>
        <?php echo date('Y'); ?> <a href="<?php echo esc_url(home_url('/')); ?>"><?php bloginfo('name'); ?></a>
    </div>
    <div class="footer-text footer-slogan">
        <?php _e('用 ❤️ 记录我们的故事', 'brave-love'); ?>
    </div>
</footer>

<!-- 返回顶部 -->
<button class="back-to-top" id="backToTop" aria-label="<?php _e('返回顶部', 'brave-love'); ?>">
    ↑
</button>

<?php wp_footer(); ?>
</body>
</html>
