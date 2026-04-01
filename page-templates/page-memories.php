<?php
/**
 * Template Name: 甜蜜相册
 *
 * @package Brave_Love
 */

global $wp_rewrite;

get_header();

// 获取Hero背景图
$hero_bg = get_theme_mod('brave_hero_bg');

?>
<!-- 页面Hero区域 -->
<section class="page-hero-section">
    <div class="page-hero-bg" style="background-image: url('<?php echo !empty($hero_bg) ? esc_url($hero_bg) : 'https://images.unsplash.com/photo-1518199266791-5375a83190b7?w=1920'; ?>');"></div>
    <div class="page-hero-content">
        <h1 class="page-hero-title">📷 甜蜜相册</h1>
    </div>
    <!-- 波浪 -->
    <div class="waves-area">
        <svg class="waves-svg" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 24 150 28" preserveAspectRatio="none">
            <defs>
                <path id="gentle-wave" d="M-160 44c30 0 58-18 88-18s 58 18 88 18 58-18 88-18 58 18 88 18v44h-352z"/>
            </defs>
            <g class="parallax">
                <use xlink:href="#gentle-wave" x="48" y="0"/>
                <use xlink:href="#gentle-wave" x="48" y="3"/>
                <use xlink:href="#gentle-wave" x="48" y="5"/>
                <use xlink:href="#gentle-wave" x="48" y="7"/>
            </g>
        </svg>
    </div>
</section>

<?php
// 获取设置
$per_page = get_theme_mod('brave_gallery_per_page', 12);
$show_info = get_theme_mod('brave_gallery_show_info', true);

// 获取筛选参数 - 使用 filter_year 避免与 WordPress 保留参数 year 冲突
$current_year = isset($_GET['filter_year']) ? intval($_GET['filter_year']) : 0;
$paged = get_query_var('paged') ? get_query_var('paged') : 1;

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

// 将照片数据传递给 JS - 在 footer 中内嵌
add_action('wp_footer', function() use ($photos, $show_info) {
    echo '<script>window.braveGalleryData = ' . json_encode(array(
        'photos' => $photos,
        'showInfo' => $show_info,
    )) . ';</script>';
}, 5);
?>

<section class="content-section">
    <div class="section-header">
        <h1 class="section-title">📷 甜蜜相册</h1>
        <p class="section-desc">记录我们在一起的每个瞬间</p>
    </div>

    <?php if (!empty($years)) : ?>
    <!-- 年份筛选 - 使用锚点方式避免URL冲突 -->
    <nav class="gallery-year-nav">
        <a href="<?php echo esc_url(remove_query_arg(array('filter_year', 'paged'))); ?>" class="gallery-year-item <?php echo $current_year === 0 ? 'active' : ''; ?>">
            <?php _e('全部', 'brave-love'); ?>
        </a>
        <?php foreach ($years as $year) : 
            // 构建年份筛选URL，确保与分页兼容
            $year_url = add_query_arg(array('filter_year' => $year, 'paged' => false));
        ?>
            <a href="<?php echo esc_url($year_url); ?>" class="gallery-year-item <?php echo $current_year === $year ? 'active' : ''; ?>">
                <?php echo esc_html($year); ?>
            </a>
        <?php endforeach; ?>
    </nav>
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
            // 获取当前页面URL（不带查询参数）
            $current_url = get_permalink();
            
            // 构建分页基础URL
            if ($wp_rewrite->using_permalinks()) {
                // 固定链接格式: /memories/page/2/?year=2025
                $base = trailingslashit($current_url) . 'page/%#%/';
            } else {
                // 默认格式: /?page_id=123&paged=2&year=2025
                $base = add_query_arg('paged', '%#%', $current_url);
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
            <h3 class="gallery-empty-title"><?php _e('还没有照片', 'brave-love'); ?></h3>
            <p class="gallery-empty-desc">
                <?php _e('在「点点滴滴」中添加文章并上传照片，它们会自动显示在这里。', 'brave-love'); ?>
            </p>
            <a href="<?php echo esc_url(admin_url('post-new.php?post_type=moment')); ?>" class="gallery-empty-btn">
                <?php _e('添加第一篇点滴', 'brave-love'); ?>
            </a>
        </div>
    <?php endif; ?>
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
            <a href="#" class="pswp-info-link" target="_blank">
                <?php _e('查看点滴详情 →', 'brave-love'); ?>
            </a>
        </div>
    </div>
</div>

<?php
get_footer();
