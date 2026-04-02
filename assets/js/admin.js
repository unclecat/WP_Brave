/**
 * Brave Love Admin JavaScript
 * 
 * 简化版 - 相册现在使用原生编辑器上传
 */

(function($) {
    'use strict';

    function initMediaField() {
        $(document).on('click', '.brave-media-select', function(e) {
            e.preventDefault();

            var $field = $(this).closest('.brave-media-field');
            var $input = $field.find('.brave-media-url');
            var $preview = $field.find('.brave-media-preview');
            var $previewImg = $preview.find('img');
            var frame = wp.media({
                title: '选择图片',
                button: {
                    text: '使用这张图片'
                },
                multiple: false,
                library: {
                    type: 'image'
                }
            });

            frame.on('select', function() {
                var attachment = frame.state().get('selection').first().toJSON();
                $input.val(attachment.url).trigger('change');
                $preview.addClass('has-image');
                $previewImg.attr('src', attachment.url).show();
            });

            frame.open();
        });

        $(document).on('click', '.brave-media-clear', function(e) {
            e.preventDefault();

            var $field = $(this).closest('.brave-media-field');
            var $input = $field.find('.brave-media-url');
            var $preview = $field.find('.brave-media-preview');
            var $previewImg = $preview.find('img');

            $input.val('');
            $preview.removeClass('has-image');
            $previewImg.attr('src', '').hide();
        });
    }

    // 相册编辑页增强
    function initMemoryEditor() {
        if (!$('body').hasClass('post-type-memory')) {
            return;
        }

        // 添加编辑器提示
        var $editor = $('#wp-content-editor-container');
        if ($editor.length && !$editor.prev('.memory-editor-tip').length) {
            $('<div class="memory-editor-tip" style="background: #e8f5e9; border-left: 4px solid #4caf50; padding: 10px 15px; margin-bottom: 10px; color: #2e7d32;">' +
                '<strong>📷 上传照片提示：</strong>点击上方「添加区块」→ 选择「图片」或「画廊」→ 上传照片' +
            '</div>').insertBefore($editor);
        }

        // 监听图片添加，更新照片计数
        if (typeof wp !== 'undefined' && wp.data && wp.data.subscribe) {
            var currentCount = 0;
            
            wp.data.subscribe(function() {
                var blocks = wp.data.select('core/block-editor').getBlocks();
                var imageCount = 0;
                
                blocks.forEach(function(block) {
                    if (block.name === 'core/image') {
                        imageCount++;
                    }
                    if (block.name === 'core/gallery' && block.attributes.ids) {
                        imageCount += block.attributes.ids.length;
                    }
                });
                
                if (imageCount !== currentCount) {
                    currentCount = imageCount;
                    updatePhotoCount(imageCount);
                }
            });
        }
    }

    // 更新照片计数显示
    function updatePhotoCount(count) {
        var $guide = $('#memory_upload_guide .inside');
        if ($guide.length) {
            var $countDiv = $guide.find('.photo-count-display');
            if (!$countDiv.length) {
                $countDiv = $('<div class="photo-count-display" style="margin-top: 10px; padding: 8px; background: #e3f2fd; border-radius: 4px; text-align: center;"></div>');
                $guide.append($countDiv);
            }
            
            if (count > 0) {
                $countDiv.html('✅ 已添加 <strong>' + count + '</strong> 张照片');
                $countDiv.css('background', '#e8f5e9');
            } else {
                $countDiv.html('⚠️ 尚未添加照片');
                $countDiv.css('background', '#fff3e0');
            }
        }
    }

    // 初始化
    $(document).ready(function() {
        initMediaField();
        initMemoryEditor();
    });

})(jQuery);
