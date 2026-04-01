#!/bin/bash
# 创建测试数据脚本（WordPress 安装后运行）

echo "📝 创建测试数据..."
echo ""

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
THEME_DIR="$(dirname "$SCRIPT_DIR")"

# 检查 WP-CLI 容器是否存在，不存在则创建
if ! docker ps | grep -q wp_cli; then
    echo "🚀 启动 WP-CLI 容器..."
    docker run -it --rm \
        --name wp_cli \
        --network wordpress_default \
        --volumes-from brave_wp_app \
        -v "$THEME_DIR":/var/www/html/wp-content/themes/brave-love:ro \
        wordpress:cli \
        /bin/bash -c "
            # 等待数据库就绪
            sleep 5
            
            # 检查 WordPress 是否已安装
            if ! wp core is-installed --allow-root 2>/dev/null; then
                echo '❌ WordPress 尚未安装，请先完成安装向导'
                exit 1
            fi
            
            echo '✅ WordPress 已安装'
            echo ''
            
            # 启用主题
            echo '🔧 启用 Brave Love 主题...'
            wp theme activate brave-love --allow-root
            
            # 创建测试页面
            echo '📄 创建测试页面...'
            
            # 首页
            wp post create --post_type=page --post_title='首页' --post_status=publish --page_template='page-templates/page-home.php' --allow-root 2>/dev/null || echo '首页已存在'
            
            # 点点滴滴
            wp post create --post_type=page --post_title='点点滴滴' --post_status=publish --page_template='page-templates/page-moments.php' --allow-root 2>/dev/null || echo '点点滴滴页面已存在'
            
            # 甜蜜相册
            wp post create --post_type=page --post_title='甜蜜相册' --post_status=publish --page_template='page-templates/page-memories.php' --allow-root 2>/dev/null || echo '甜蜜相册页面已存在'
            
            # 随笔说说
            wp post create --post_type=page --post_title='随笔说说' --post_status=publish --page_template='page-templates/page-notes.php' --allow-root 2>/dev/null || echo '随笔说说页面已存在'
            
            # 祝福留言
            wp post create --post_type=page --post_title='祝福留言' --post_status=publish --page_template='page-templates/page-blessing.php' --allow-root 2>/dev/null || echo '祝福留言页面已存在'
            
            echo '✅ 测试页面创建完成'
            echo ''
            
            # 设置首页
            echo '🏠 设置首页...'
            wp option update show_on_front page --allow-root
            wp option update page_on_front \$(wp post list --post_type=page --post_title='首页' --field=ID --allow-root) --allow-root
            
            echo '✅ 首页设置完成'
            echo ''
            
            # 创建测试内容
            echo '📝 创建测试内容...'
            
            # 添加测试点滴
            wp post create --post_type=moment --post_title='第一次见面' --post_content='还记得那天阳光明媚，我们在咖啡厅相遇...' --post_status=publish --meta_input='{\"_meet_date\":\"2023-05-20\",\"_meet_location\":\"星巴克\",\"_mood\":\"romantic\"}' --allow-root 2>/dev/null || echo '第一次见面已存在'
            
            wp post create --post_type=moment --post_title='第一次看电影' --post_content='一起看了一部浪漫的爱情电影，你的手好温暖...' --post_status=publish --meta_input='{\"_meet_date\":\"2023-06-01\",\"_meet_location\":\"万达影城\",\"_mood\":\"happy\"}' --allow-root 2>/dev/null || echo '第一次看电影已存在'
            
            wp post create --post_type=moment --post_title='一起去旅行' --post_content='我们的第一次旅行，去了美丽的海边...' --post_status=publish --meta_input='{\"_meet_date\":\"2023-10-01\",\"_meet_location\":\"三亚\",\"_mood\":\"excited\"}' --allow-root 2>/dev/null || echo '一起去旅行已存在'
            
            # 添加测试清单
            wp post create --post_type=love_list --post_title='一起看日出' --post_content='' --post_status=publish --allow-root 2>/dev/null || echo '一起看日出已存在'
            wp post create --post_type=love_list --post_title='一起去海边' --post_content='' --post_status=publish --meta_input='{\"_is_done\":1,\"_done_date\":\"2023-10-01\"}' --allow-root 2>/dev/null || echo '一起去海边已存在'
            wp post create --post_type=love_list --post_title='一起做蛋糕' --post_content='' --post_status=publish --allow-root 2>/dev/null || echo '一起做蛋糕已存在'
            wp post create --post_type=love_list --post_title='一起养宠物' --post_content='' --post_status=publish --allow-root 2>/dev/null || echo '一起养宠物已存在'
            wp post create --post_type=love_list --post_title='一起去游乐园' --post_content='' --post_status=publish --allow-root 2>/dev/null || echo '一起去游乐园已存在'
            
            # 添加测试说说
            wp post create --post_type=note --post_title='' --post_content='今天天气真好，想你了 💕' --post_status=publish --meta_input='{\"_note_mood\":\"😊\"}' --allow-root 2>/dev/null || echo '说说1已存在'
            
            wp post create --post_type=note --post_title='' --post_content='刚刚看到一对老夫妻牵手散步，希望我们也能白头到老' --post_status=publish --meta_input='{\"_note_mood\":\"🥰\"}' --allow-root 2>/dev/null || echo '说说2已存在'
            
            wp post create --post_type=note --post_title='' --post_content='纪念我们的第100天！🎉' --post_status=publish --meta_input='{\"_note_mood\":\"🎉\"}' --allow-root 2>/dev/null || echo '说说3已存在'
            
            echo '✅ 测试内容创建完成'
            echo ''
            
            # 添加测试相册
            wp post create --post_type=memory --post_title='第一次旅行' --post_content='三亚之旅的美好回忆' --post_status=publish --meta_input='{\"_memory_date\":\"2023-10-01\",\"_memory_location\":\"三亚\"}' --allow-root 2>/dev/null || echo '相册已存在'
            
            echo '✅ 测试相册创建完成'
            echo ''
            
            echo '================================'
            echo '🎉 测试数据创建完成！'
            echo '================================'
            echo ''
            echo '🔗 访问地址：'
            echo '   http://localhost:8080'
            echo ''
            echo '👤 登录信息：'
            echo '   用户名: admin'
            echo '   密码: admin123'
            echo ''
        "
else
    echo "WP-CLI 容器已在运行"
fi
