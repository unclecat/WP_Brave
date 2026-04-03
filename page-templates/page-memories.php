<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Template Name: 甜蜜相册
 *
 * @package Brave_Love
 */

global $wp_rewrite;

get_header();
get_template_part(
    'template-parts/page-hero',
    null,
    array(
        'title' => '📷 甜蜜相册',
        'subtitle' => '人间烟火，山河远阔，无一不是你我同行的见证。',
    )
);

// 获取设置
$per_page = max(1, absint(get_theme_mod('brave_gallery_per_page', 12)));
$show_info = brave_theme_mod_enabled('brave_gallery_show_info', true);
$gallery_base_url = get_permalink();

// 获取筛选参数 - 使用 filter_year 避免与 WordPress 保留参数 year 冲突
$current_year = isset($_GET['filter_year']) ? absint(wp_unslash($_GET['filter_year'])) : 0;
$paged = max(1, absint(get_query_var('paged')));

// 获取照片数据
$photo_data = brave_get_all_moment_photos(array(
    'posts_per_page' => $per_page,
    'paged' => $paged,
    'filter_year' => $current_year,
));

$photos = $photo_data['photos'];
$total_photos = $photo_data['total_photos'];
$max_num_pages = $photo_data['max_num_pages'];

// 获取可用年份
$years = brave_get_gallery_years();
?>

<section class="content-section">
    <div class="page-shell page-shell-narrow">
        <?php if (!empty($years)) : ?>
        <div class="content-filter-shell gallery-filter-shell">
            <div class="moments-filter-controls content-filter-actions">
                <a href="<?php echo esc_url($gallery_base_url); ?>" class="filter-btn <?php echo $current_year === 0 ? 'active' : ''; ?>">
                    <?php _e('全部', 'brave-love'); ?>
                </a>

                <div class="filter-group">
                    <button class="filter-dropdown-toggle <?php echo $current_year > 0 ? 'has-value' : ''; ?>" data-toggle="gallery-year">
                        <?php echo $current_year > 0 ? esc_html($current_year . '年') : '年份'; ?>
                    </button>
                    <div class="filter-dropdown" id="gallery-year-dropdown">
                        <?php foreach ($years as $year) : 
                            // 切换年份时始终回到第一页，避免沿用上一页的分页路径
                            $year_url = add_query_arg('filter_year', $year, $gallery_base_url);
                        ?>
                            <a href="<?php echo esc_url($year_url); ?>" class="filter-option <?php echo $current_year === $year ? 'active' : ''; ?>">
                                <?php echo esc_html($year); ?>年
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($photos)) : ?>
        <!-- 瀑布流容器 -->
        <div class="gallery-waterfall" id="galleryWaterfall">
            <?php foreach ($photos as $index => $photo) : 
                // 根据宽高比计算合适的高度类
                $ratio_class = '';
                if ($photo['aspect_ratio'] < 0.8) {
                    $ratio_class = 'tall';      // 竖图
                } elseif ($photo['aspect_ratio'] > 1.5) {
                    $ratio_class = 'wide';      // 横图
                }
                
                // 信息面板数据
                $info_data = array(
                    'date' => $photo['date_formatted'] . ' ' . $photo['weekday'],
                    'location' => $photo['location'],
                    'mood' => $photo['mood_emoji'] . ' ' . $photo['mood_text'],
                    'moment' => $photo['moment_title'],
                    'momentUrl' => $photo['moment_url'],
                    'summary' => $photo['summary'],
                );
            ?>
                <div class="gallery-item <?php echo esc_attr($ratio_class); ?>" 
                     data-index="<?php echo esc_attr($index); ?>"
                     data-pswp-width="<?php echo esc_attr($photo['width']); ?>"
                     data-pswp-height="<?php echo esc_attr($photo['height']); ?>">
                    
                    <a href="<?php echo esc_url($photo['url']); ?>" 
                       class="gallery-item-link"
                       data-caption="<?php echo esc_attr($photo['caption']); ?>"
                       data-info="<?php echo esc_attr(json_encode($info_data)); ?>">
                        
                        <img src="<?php echo esc_url($photo['thumb']); ?>" 
                             alt="<?php echo esc_attr($photo['alt'] ? $photo['alt'] : $photo['title']); ?>"
                             class="gallery-item-img"
                             loading="lazy">
                        
                        <?php if ($show_info) : ?>
                        <div class="gallery-item-overlay">
                            <div class="gallery-item-meta">
                                <span class="gallery-item-date"><?php echo esc_html($photo['date_formatted']); ?></span>
                                <?php if ($photo['location']) : ?>
                                    <span class="gallery-item-location">📍 <?php echo esc_html($photo['location']); ?></span>
                                <?php endif; ?>
                                <?php if ($photo['mood']) : ?>
                                    <span class="gallery-item-mood"><?php echo esc_html($photo['mood_emoji']); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- 分页/加载更多 -->
        <?php if ($photo_data['max_num_pages'] > 1) : 
            // 构建分页基础URL
            if ($wp_rewrite->using_permalinks()) {
                // 固定链接格式: /memories/page/2/?year=2025
                $base = trailingslashit($gallery_base_url) . 'page/%#%/';
            } else {
                // 默认格式: /?page_id=123&paged=2&year=2025
                $base = add_query_arg('paged', '%#%', $gallery_base_url);
            }
            
            // 年份参数通过 add_args 传递
            $paginate_args = array('filter_year' => $current_year > 0 ? $current_year : false);
        ?>
            <nav class="gallery-pagination">
                <?php
                echo paginate_links(array(
                    'base' => $base,
                    'format' => '',
                    'current' => max(1, $paged),
                    'total' => $photo_data['max_num_pages'],
                    'prev_text' => '← ' . __('上一页', 'brave-love'),
                    'next_text' => __('下一页', 'brave-love') . ' →',
                    'mid_size' => 2,
                    'end_size' => 1,
                    'add_args' => array_filter($paginate_args),
                ));
                ?>
            </nav>
        <?php endif; ?>

        <?php else : ?>
        <!-- 空状态 -->
        <div class="gallery-empty">
            <div class="gallery-empty-icon">📷</div>
            <?php if ($current_year > 0) : ?>
                <h3 class="gallery-empty-title"><?php echo esc_html($current_year); ?> 年还没有照片</h3>
                <p class="gallery-empty-desc">试试查看其他年份，或返回全部相册。</p>
                <a href="<?php echo esc_url($gallery_base_url); ?>" class="gallery-empty-btn">查看全部照片</a>
            <?php else : ?>
                <h3 class="gallery-empty-title"><?php _e('还没有照片', 'brave-love'); ?></h3>
                <p class="gallery-empty-desc">
                    <?php _e('在「点点滴滴」中添加文章并上传照片，它们会自动显示在这里。', 'brave-love'); ?>
                </p>
                <a href="<?php echo esc_url(admin_url('post-new.php?post_type=moment')); ?>" class="gallery-empty-btn">
                    <?php _e('添加第一篇点滴', 'brave-love'); ?>
                </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- PhotoSwipe 模板 -->
<div class="pswp" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="pswp__bg"></div>
    <div class="pswp__scroll-wrap">
        <div class="pswp__container">
            <div class="pswp__item"></div>
            <div class="pswp__item"></div>
            <div class="pswp__item"></div>
        </div>
        <div class="pswp__ui pswp__ui--hidden">
            <div class="pswp__top-bar">
                <div class="pswp__counter"></div>
                <button class="pswp__button pswp__button--close" title="<?php _e('关闭 (Esc)', 'brave-love'); ?>"></button>
                <button class="pswp__button pswp__button--share" title="<?php _e('分享', 'brave-love'); ?>"></button>
                <button class="pswp__button pswp__button--fs" title="<?php _e('全屏', 'brave-love'); ?>"></button>
                <button class="pswp__button pswp__button--zoom" title="<?php _e('缩放', 'brave-love'); ?>"></button>
                <div class="pswp__preloader">
                    <div class="pswp__preloader__icn">
                        <div class="pswp__preloader__cut">
                            <div class="pswp__preloader__donut"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="pswp__share-modal pswp__share-modal--hidden pswp__single-tap">
                <div class="pswp__share-tooltip"></div>
            </div>
            <button class="pswp__button pswp__button--arrow--left" title="<?php _e('上一张 (左箭头)', 'brave-love'); ?>"></button>
            <button class="pswp__button pswp__button--arrow--right" title="<?php _e('下一张 (右箭头)', 'brave-love'); ?>"></button>
            <div class="pswp__caption">
                <div class="pswp__caption__center"></div>
            </div>
        </div>
    </div>
    <!-- 自定义信息面板 -->
    <div class="pswp__custom-info" id="pswpCustomInfo">
        <div class="pswp-info-content">
            <div class="pswp-info-date"></div>
            <div class="pswp-info-location"></div>
            <div class="pswp-info-mood"></div>
            <div class="pswp-info-summary"></div>
            <a href="#" class="pswp-info-link" target="_blank" rel="noopener noreferrer">
                <?php _e('查看点滴详情 →', 'brave-love'); ?>
            </a>
        </div>
    </div>
</div>

<?php
get_footer();
