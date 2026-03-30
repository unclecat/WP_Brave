</main><!-- #main -->

<!-- 页脚 -->
<footer class="footer">
    <div class="footer-text">
        &copy; <?php echo date('Y'); ?> <a href="<?php echo esc_url(home_url('/')); ?>"><?php bloginfo('name'); ?></a>
    </div>
    <div class="footer-text">
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
