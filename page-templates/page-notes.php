<?php
/**
 * Template Name: 随笔说说
 *
 * @package Brave_Love
 * @version 0.4.1
 */

get_header();

// 获取Hero背景图
$hero_bg = get_theme_mod('brave_hero_bg');

?>
<!-- 页面Hero区域 -->
<?php 
$hero_bg_url = !empty($hero_bg) ? $hero_bg : 'https://images.unsplash.com/photo-1518199266791-5375a83190b7?w=1920';
?>
<section class="page-hero-section" style="background-image: url('<?php echo esc_url($hero_bg_url); ?>'); background-size: cover; background-position: center;">
    <div class="page-hero-overlay"></div>
    <!-- 页面标题在内容区域显示 -->
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
// 获取筛选参数
$filter_year = isset($_GET['filter_year']) ? intval($_GET['filter_year']) : 0;
$filter_month = isset($_GET['filter_month']) ? intval($_GET['filter_month']) : 0;
$filter_day = isset($_GET['filter_day']) ? intval($_GET['filter_day']) : 0;

$paged = get_query_var('paged') ? get_query_var('paged') : 1;

// 构建查询参数
$args = array(
    'post_type' => 'note',
    'posts_per_page' => 12,
    'paged' => $paged,
    'orderby' => 'date',
    'order' => 'DESC',
);

// 添加日期筛选
if ($filter_year) {
    $args['date_query'] = array(
        array(
            'year' => $filter_year,
        ),
    );
    
    if ($filter_month) {
        $args['date_query'][0]['month'] = $filter_month;
        
        if ($filter_day) {
            $args['date_query'][0]['day'] = $filter_day;
        }
    }
}

$query = new WP_Query($args);

// 获取所有年份
$years = brave_get_note_years();

// 获取选中年的月份
$months = array();
if ($filter_year) {
    $months = brave_get_note_months($filter_year);
}

// 获取选中月的天数
$days = array();
if ($filter_year && $filter_month) {
    $days = brave_get_note_days($filter_year, $filter_month);
}

// 当前用户是否已登录
$is_logged_in = is_user_logged_in();
$current_user = wp_get_current_user();
?>

<section class="content-section">
    <div class="section-header">
        <h1 class="section-title">📝 随笔说说</h1>
        <p class="section-desc">记录生活的点滴心情与思念</p>
    </div>

    <!-- 级联筛选器 -->
    <div class="notes-filter-bar">
        <!-- 全部按钮 -->
        <div class="filter-group">
            <a href="<?php echo esc_url(remove_query_arg(array('filter_year', 'filter_month', 'filter_day'))); ?>" 
               class="filter-btn <?php echo !$filter_year ? 'active' : ''; ?>">
                全部
            </a>
        </div>
        
        <!-- 年份筛选 - 始终显示 -->
        <div class="filter-group">
            <button class="filter-dropdown-toggle <?php echo $filter_year ? 'has-value' : ''; ?>" data-toggle="year">
                <?php echo $filter_year ? $filter_year . '年' : '年份'; ?>
            </button>
            <div class="filter-dropdown" id="year-dropdown">
                <?php if (!empty($years)) : ?>
                    <?php foreach ($years as $year) : ?>
                        <a href="<?php echo esc_url(add_query_arg(array('filter_year' => $year, 'filter_month' => false, 'filter_day' => false))); ?>" 
                           class="filter-option <?php echo $filter_year == $year ? 'active' : ''; ?>">
                            <?php echo $year; ?>年
                        </a>
                    <?php endforeach; ?>
                <?php else : ?>
                    <span class="filter-option disabled">暂无数据</span>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- 月份筛选 - 选择年份后才显示 -->
        <?php if ($filter_year) : ?>
        <div class="filter-group">
            <button class="filter-dropdown-toggle <?php echo $filter_month ? 'has-value' : ''; ?>" data-toggle="month">
                <?php echo $filter_month ? $filter_month . '月' : '月份'; ?>
            </button>
            <div class="filter-dropdown" id="month-dropdown">
                <?php if (!empty($months)) : ?>
                    <?php foreach ($months as $month) : ?>
                        <a href="<?php echo esc_url(add_query_arg(array('filter_month' => $month, 'filter_day' => false))); ?>" 
                           class="filter-option <?php echo $filter_month == $month ? 'active' : ''; ?>">
                            <?php echo $month; ?>月
                        </a>
                    <?php endforeach; ?>
                <?php else : ?>
                    <span class="filter-option disabled">暂无数据</span>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- 日期筛选 - 选择月份后才显示 -->
        <?php if ($filter_year && $filter_month) : ?>
        <div class="filter-group">
            <button class="filter-dropdown-toggle <?php echo $filter_day ? 'has-value' : ''; ?>" data-toggle="day">
                <?php echo $filter_day ? $filter_day . '日' : '日期'; ?>
            </button>
            <div class="filter-dropdown" id="day-dropdown">
                <?php if (!empty($days)) : ?>
                    <?php foreach ($days as $day) : ?>
                        <a href="<?php echo esc_url(add_query_arg('filter_day', $day)); ?>" 
                           class="filter-option <?php echo $filter_day == $day ? 'active' : ''; ?>">
                            <?php echo $day; ?>日
                        </a>
                    <?php endforeach; ?>
                <?php else : ?>
                    <span class="filter-option disabled">暂无数据</span>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- 瀑布流说说列表 -->
    <?php if ($query->have_posts()) : ?>
        <div class="notes-waterfall" id="notesWaterfall">
            <?php while ($query->have_posts()) : $query->the_post(); 
                $note_mood = get_post_meta(get_the_ID(), '_note_mood', true);
                $note_miss_level = get_post_meta(get_the_ID(), '_note_miss_level', true);
                if (empty($note_miss_level)) $note_miss_level = 3;
                
                $author_id = get_the_author_meta('ID');
                $author_name = get_the_author();
                
                // 获取头像：优先使用主题设置的头像，否则使用 WordPress 头像
                $boy_user_id = intval(get_theme_mod('brave_boy_user_id'));
                $girl_user_id = intval(get_theme_mod('brave_girl_user_id'));
                
                if ($boy_user_id > 0 && $author_id == $boy_user_id) {
                    // 作者是男生
                    $author_avatar = brave_get_couple_avatar('boy', 100);
                } elseif ($girl_user_id > 0 && $author_id == $girl_user_id) {
                    // 作者是女生
                    $author_avatar = brave_get_couple_avatar('girl', 100);
                } else {
                    // 其他用户，使用 WordPress 默认头像
                    $author_avatar = get_avatar_url($author_id);
                }
                
                $post_date = get_the_date('Y-m-d H:i');
            ?>
                <article class="note-waterfall-card">
                    <div class="note-card-header">
                        <img src="<?php echo esc_url($author_avatar); ?>" alt="" class="note-author-avatar">
                        <div class="note-author-info">
                            <span class="note-author-name"><?php echo esc_html($author_name); ?></span>
                            <span class="note-datetime"><?php echo esc_html($post_date); ?></span>
                        </div>
                    </div>
                    
                    <div class="note-card-body">
                        <?php if ($note_mood) : ?>
                            <div class="note-mood-badge"><?php echo esc_html($note_mood); ?></div>
                        <?php endif; ?>
                        
                        <div class="note-content">
                            <?php the_content(); ?>
                        </div>
                    </div>
                    
                    <div class="note-card-footer">
                        <div class="note-miss-level">
                            <span class="miss-label">思念</span>
                            <span class="miss-stars">
                                <?php echo str_repeat('⭐', $note_miss_level); ?>
                            </span>
                        </div>
                    </div>
                </article>
            <?php endwhile; ?>
        </div>

        <!-- 分页 -->
        <?php if ($query->max_num_pages > 1) : ?>
            <nav class="notes-pagination">
                <?php
                $base = get_pagenum_link(999999999);
                $base = str_replace('999999999', '%#%', $base);
                
                echo paginate_links(array(
                    'base' => $base,
                    'format' => '',
                    'current' => $paged,
                    'total' => $query->max_num_pages,
                    'prev_text' => '← 上一页',
                    'next_text' => '下一页 →',
                    'mid_size' => 2,
                    'add_args' => array_filter(array(
                        'filter_year' => $filter_year ?: false,
                        'filter_month' => $filter_month ?: false,
                        'filter_day' => $filter_day ?: false,
                    )),
                ));
                ?>
            </nav>
        <?php endif; ?>
        
        <?php wp_reset_postdata(); ?>
    <?php else : ?>
        <div class="notes-empty">
            <div class="notes-empty-icon">📝</div>
            <p class="notes-empty-text">还没有发布任何说说</p>
            <?php if (!$is_logged_in) : ?>
                <p class="notes-empty-hint">登录后可以发布你的第一条说说</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- 登录用户发布表单 - 移到说说列表下方 -->
    <?php if ($is_logged_in) : 
        // 获取当前用户头像
        $current_user_id = $current_user->ID;
        $boy_user_id = intval(get_theme_mod('brave_boy_user_id'));
        $girl_user_id = intval(get_theme_mod('brave_girl_user_id'));
        
        if ($boy_user_id > 0 && $current_user_id == $boy_user_id) {
            $current_avatar = brave_get_couple_avatar('boy', 100);
        } elseif ($girl_user_id > 0 && $current_user_id == $girl_user_id) {
            $current_avatar = brave_get_couple_avatar('girl', 100);
        } else {
            $current_avatar = get_avatar_url($current_user_id);
        }
    ?>
    <div class="note-publish-form">
        <div class="publish-header">
            <img src="<?php echo esc_url($current_avatar); ?>" alt="" class="publish-avatar">
            <span class="publish-name"><?php echo esc_html($current_user->display_name); ?></span>
        </div>
        <form id="quick-note-form" method="post" action="">
            <?php wp_nonce_field('publish_note_action', 'publish_note_nonce'); ?>
            <textarea name="note_content" class="publish-textarea" placeholder="写下此刻的心情..." required></textarea>
            
            <div class="publish-options">
                <div class="option-group">
                    <label>心情</label>
                    <div class="mood-selector">
                        <button type="button" class="mood-btn" data-mood="😊">😊</button>
                        <button type="button" class="mood-btn" data-mood="😢">😢</button>
                        <button type="button" class="mood-btn" data-mood="🥰">🥰</button>
                        <button type="button" class="mood-btn" data-mood="😌">😌</button>
                        <button type="button" class="mood-btn" data-mood="🤔">🤔</button>
                        <button type="button" class="mood-btn" data-mood="😴">😴</button>
                    </div>
                    <input type="hidden" name="note_mood" id="selected-mood" value="😊">
                </div>
                
                <div class="option-group">
                    <label>思念度</label>
                    <div class="miss-level-selector">
                        <button type="button" class="star-btn" data-level="1">⭐</button>
                        <button type="button" class="star-btn" data-level="2">⭐</button>
                        <button type="button" class="star-btn" data-level="3">⭐</button>
                        <button type="button" class="star-btn" data-level="4">⭐</button>
                        <button type="button" class="star-btn" data-level="5">⭐</button>
                    </div>
                    <input type="hidden" name="note_miss_level" id="selected-miss-level" value="3">
                </div>
            </div>
            
            <div class="publish-actions">
                <button type="submit" name="publish_note" class="publish-submit">发布说说</button>
            </div>
        </form>
    </div>
    <?php endif; ?>
</section>

<script>
// 级联筛选下拉菜单
document.addEventListener('DOMContentLoaded', function() {
    const toggles = document.querySelectorAll('.filter-dropdown-toggle');
    
    toggles.forEach(function(toggle) {
        toggle.addEventListener('click', function(e) {
            e.stopPropagation();
            const target = this.getAttribute('data-toggle');
            const dropdown = document.getElementById(target + '-dropdown');
            
            // 关闭其他下拉菜单
            document.querySelectorAll('.filter-dropdown').forEach(function(d) {
                if (d !== dropdown) d.classList.remove('show');
            });
            
            // 切换当前下拉菜单
            dropdown.classList.toggle('show');
        });
    });
    
    // 点击外部关闭下拉菜单
    document.addEventListener('click', function() {
        document.querySelectorAll('.filter-dropdown').forEach(function(d) {
            d.classList.remove('show');
        });
    });
    
    // 心情选择
    const moodBtns = document.querySelectorAll('.mood-btn');
    const moodInput = document.getElementById('selected-mood');
    
    moodBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            moodBtns.forEach(function(b) { b.classList.remove('active'); });
            this.classList.add('active');
            moodInput.value = this.getAttribute('data-mood');
        });
    });
    
    // 默认选中第一个心情
    if (moodBtns.length > 0) {
        moodBtns[0].classList.add('active');
    }
    
    // 思念度选择
    const starBtns = document.querySelectorAll('.star-btn');
    const missInput = document.getElementById('selected-miss-level');
    
    function updateStars(level) {
        starBtns.forEach(function(btn, index) {
            if (index < level) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });
        missInput.value = level;
    }
    
    starBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            const level = parseInt(this.getAttribute('data-level'));
            updateStars(level);
        });
    });
    
    // 默认选中3星
    updateStars(3);
});
</script>

<?php
get_footer();
