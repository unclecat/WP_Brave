<?php
/**
 * 主题诊断工具
 * 
 * 使用方法：
 * 1. 将此文件复制到 WordPress 根目录
 * 2. 访问 http://你的域名/diagnose.php
 * 3. 查看诊断信息
 */

define('WP_USE_THEMES', false);
require_once('wp-load.php');

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Brave Love 主题诊断</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, sans-serif; padding: 20px; max-width: 800px; margin: 0 auto; }
        h1 { color: #ff5162; }
        h2 { color: #333; border-bottom: 2px solid #ff5162; padding-bottom: 10px; }
        .success { color: #4caf50; background: #e8f5e9; padding: 10px; border-radius: 4px; }
        .error { color: #f44336; background: #ffebee; padding: 10px; border-radius: 4px; }
        .warning { color: #ff9800; background: #fff3e0; padding: 10px; border-radius: 4px; }
        .info { background: #f5f5f5; padding: 10px; border-radius: 4px; margin: 10px 0; }
        code { background: #f5f5f5; padding: 2px 6px; border-radius: 3px; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { text-align: left; padding: 10px; border-bottom: 1px solid #ddd; }
        th { background: #f5f5f5; }
    </style>
</head>
<body>
    <h1>💕 Brave Love 主题诊断工具</h1>
    
    <?php
    $errors = [];
    $warnings = [];
    
    // 检查主题状态
    echo '<h2>1. 主题状态</h2>';
    $current_theme = wp_get_theme();
    echo '<div class="info">当前主题: <strong>' . $current_theme->get('Name') . '</strong></div>';
    
    if ($current_theme->get('Name') !== 'Brave Love') {
        $errors[] = '当前激活的不是 Brave Love 主题';
        echo '<div class="error">❌ 当前激活的不是 Brave Love 主题，请前往 外观 → 主题 启用</div>';
    } else {
        echo '<div class="success">✅ Brave Love 主题已激活</div>';
    }
    
    // 检查文件
    echo '<h2>2. 主题文件检查</h2>';
    $theme_dir = get_template_directory();
    $required_files = [
        'style.css',
        'functions.php',
        'index.php',
        'header.php',
        'footer.php',
        'page-templates/page-home.php',
    ];
    
    echo '<table>';
    echo '<tr><th>文件</th><th>状态</th></tr>';
    foreach ($required_files as $file) {
        $exists = file_exists($theme_dir . '/' . $file);
        echo '<tr>';
        echo '<td>' . $file . '</td>';
        echo '<td>' . ($exists ? '<span class="success">✅ 存在</span>' : '<span class="error">❌ 缺失</span>') . '</td>';
        echo '</tr>';
        if (!$exists) {
            $errors[] = "缺少文件: $file";
        }
    }
    echo '</table>';
    
    // 检查首页设置
    echo '<h2>3. 首页设置</h2>';
    $show_on_front = get_option('show_on_front');
    $page_on_front = get_option('page_on_front');
    
    echo '<div class="info">首页显示模式: <code>' . $show_on_front . '</code></div>';
    
    if ($show_on_front === 'page') {
        if ($page_on_front) {
            $front_page = get_post($page_on_front);
            if ($front_page) {
                echo '<div class="success">✅ 静态首页已设置</div>';
                echo '<div class="info">首页页面: <strong>' . $front_page->post_title . '</strong> (ID: ' . $page_on_front . ')</div>';
                
                // 检查模板
                $template = get_page_template_slug($page_on_front);
                echo '<div class="info">使用模板: <code>' . ($template ?: '默认模板') . '</code></div>';
                
                if ($template !== 'page-templates/page-home.php') {
                    $warnings[] = '首页没有使用「首页 - 关于我们」模板';
                    echo '<div class="warning">⚠️ 首页没有使用「首页 - 关于我们」模板</div>';
                    echo '<div class="info">建议: 编辑首页页面，选择模板「首页 - 关于我们」</div>';
                } else {
                    echo '<div class="success">✅ 首页模板正确</div>';
                }
            } else {
                $errors[] = '设置的首页页面不存在';
                echo '<div class="error">❌ 设置的首页页面不存在</div>';
            }
        } else {
            $warnings[] = '没有设置具体的静态首页页面';
            echo '<div class="warning">⚠️ 没有设置具体的静态首页页面</div>';
        }
    } else {
        $warnings[] = '当前显示最新文章，建议设置为静态页面';
        echo '<div class="warning">⚠️ 当前显示最新文章，建议设置为静态页面</div>';
        echo '<div class="info">建议设置: 设置 → 阅读 → 首页显示 → 一个静态页面</div>';
    }
    
    // 检查页面
    echo '<h2>4. 页面检查</h2>';
    $required_pages = [
        'moments' => '点点滴滴',
        'list' => '恋爱清单',
        'photo' => '甜蜜相册',
        'notes' => '随笔说说',
        'blessing' => '祝福留言',
    ];
    
    echo '<table>';
    echo '<tr><th>页面</th><th>状态</th><th>模板</th></tr>';
    foreach ($required_pages as $slug => $name) {
        $page = get_page_by_path($slug);
        if ($page) {
            $template = get_page_template_slug($page->ID);
            echo '<tr>';
            echo '<td>' . $name . '</td>';
            echo '<td><span class="success">✅ 已创建</span></td>';
            echo '<td><code>' . ($template ?: '默认') . '</code></td>';
            echo '</tr>';
        } else {
            echo '<tr>';
            echo '<td>' . $name . '</td>';
            echo '<td><span class="error">❌ 未创建</span></td>';
            echo '<td>-</td>';
            echo '</tr>';
            $warnings[] = "缺少页面: $name";
        }
    }
    echo '</table>';
    
    // 检查主题设置
    echo '<h2>5. 主题设置</h2>';
    $love_start = get_theme_mod('brave_love_start_date');
    $boy_name = get_theme_mod('brave_boy_name');
    $girl_name = get_theme_mod('brave_girl_name');
    
    echo '<table>';
    echo '<tr><th>设置项</th><th>值</th><th>状态</th></tr>';
    
    echo '<tr>';
    echo '<td>恋爱起始日期</td>';
    echo '<td>' . ($love_start ?: '未设置') . '</td>';
    echo '<td>' . ($love_start ? '<span class="success">✅</span>' : '<span class="warning">⚠️ 建议设置</span>') . '</td>';
    echo '</tr>';
    
    echo '<tr>';
    echo '<td>男生昵称</td>';
    echo '<td>' . ($boy_name ?: '他') . '</td>';
    echo '<td>' . ($boy_name ? '<span class="success">✅</span>' : '<span class="warning">⚠️ 建议设置</span>') . '</td>';
    echo '</tr>';
    
    echo '<tr>';
    echo '<td>女生昵称</td>';
    echo '<td>' . ($girl_name ?: '她') . '</td>';
    echo '<td>' . ($girl_name ? '<span class="success">✅</span>' : '<span class="warning">⚠️ 建议设置</span>') . '</td>';
    echo '</tr>';
    
    echo '</table>';
    
    // 检查纪念日
    echo '<h2>6. 纪念日设置</h2>';
    $anniversaries = get_option('brave_anniversaries', []);
    if (empty($anniversaries)) {
        $warnings[] = '没有设置纪念日';
        echo '<div class="warning">⚠️ 没有设置纪念日</div>';
        echo '<div class="info">前往: 后台 → 设置 → 纪念日 → 添加纪念日</div>';
    } else {
        echo '<div class="success">✅ 已设置 ' . count($anniversaries) . ' 个纪念日</div>';
        echo '<ul>';
        foreach ($anniversaries as $item) {
            echo '<li>' . esc_html($item['name']) . ' - ' . esc_html($item['date']) . '</li>';
        }
        echo '</ul>';
    }
    
    // 快速修复
    echo '<h2>7. 快速修复</h2>';
    if (!empty($errors) || !empty($warnings)) {
        echo '<div class="info">发现以下问题，请按顺序修复：</div>';
        echo '<ol>';
        
        if (!empty($errors)) {
            foreach ($errors as $error) {
                echo '<li class="error">' . $error . '</li>';
            }
        }
        
        if (!empty($warnings)) {
            foreach ($warnings as $warning) {
                echo '<li class="warning">' . $warning . '</li>';
            }
        }
        
        echo '</ol>';
        
        echo '<h3>修复步骤：</h3>';
        echo '<ol>';
        echo '<li><strong>设置静态首页：</strong><br>';
        echo '设置 → 阅读 → 首页显示 → 选择「一个静态页面」→ 首页选择你创建的「首页」页面</li>';
        echo '<li><strong>选择首页模板：</strong><br>';
        echo '页面 → 首页 → 编辑 → 页面属性 → 模板 → 选择「首页 - 关于我们」</li>';
        echo '<li><strong>配置主题：</strong><br>';
        echo '外观 → 自定义 → Brave 主题设置 → 填写情侣信息和起始日期</li>';
        echo '<li><strong>添加纪念日：</strong><br>';
        echo '后台 → 设置 → 纪念日 → 添加特别的日子</li>';
        echo '</ol>';
    } else {
        echo '<div class="success">✅ 所有设置都正确！如果首页仍显示异常，请尝试：<br>';
        echo '1. 清除浏览器缓存<br>';
        echo '2. 设置 → 固定链接 → 保存（刷新重写规则）<br>';
        echo '3. 检查是否有缓存插件，清除缓存</div>';
    }
    
    // 技术信息
    echo '<h2>8. 技术信息</h2>';
    echo '<table>';
    echo '<tr><th>项目</th><th>值</th></tr>';
    echo '<tr><td>WordPress 版本</td><td>' . get_bloginfo('version') . '</td></tr>';
    echo '<tr><td>PHP 版本</td><td>' . phpversion() . '</td></tr>';
    echo '<tr><td>主题目录</td><td><code>' . get_template_directory() . '</code></td></tr>';
    echo '<tr><td>主题版本</td><td>' . wp_get_theme()->get('Version') . '</td></tr>';
    echo '</table>';
    ?>
    
    <hr>
    <p style="text-align: center; color: #999;">
        Brave Love 主题诊断工具 | 
        <a href="<?php echo home_url(); ?>">返回首页</a> | 
        <a href="<?php echo admin_url(); ?>">进入后台</a>
    </p>
</body>
</html>
