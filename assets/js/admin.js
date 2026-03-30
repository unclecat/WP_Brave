/**
 * Brave Admin JavaScript
 */

(function($) {
    'use strict';

    var mediaUploader;
    var targetInput;
    var targetPreview;

    // 初始化媒体上传
    function initMediaUploader() {
        $('.brave-add-images').on('click', function(e) {
            e.preventDefault();
            
            var $button = $(this);
            targetInput = $('#' + $button.data('target') + '_input');
            targetPreview = $button.siblings('.brave-gallery-preview');

            if (mediaUploader) {
                mediaUploader.open();
                return;
            }

            mediaUploader = wp.media({
                title: '选择图片',
                button: {
                    text: '添加到相册'
                },
                multiple: true,
                library: {
                    type: 'image'
                }
            });

            mediaUploader.on('select', function() {
                var attachments = mediaUploader.state().get('selection').map(function(attachment) {
                    attachment = attachment.toJSON();
                    return {
                        id: attachment.id,
                        url: attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url
                    };
                });

                // 获取现有图片
                var existingIds = targetInput.val() ? targetInput.val().split(',').filter(Boolean) : [];
                var existingAttachments = targetPreview.find('.brave-gallery-item').map(function() {
                    return {
                        id: $(this).data('id'),
                        url: $(this).find('img').attr('src')
                    };
                }).get();

                // 合并新旧图片
                var allAttachments = existingAttachments.concat(attachments);
                var allIds = existingIds.concat(attachments.map(function(a) { return a.id; }));

                // 更新预览
                updateGalleryPreview(allAttachments);

                // 更新隐藏字段
                targetInput.val(allIds.join(','));
            });

            mediaUploader.open();
        });

        // 删除图片
        $(document).on('click', '.brave-remove-image', function(e) {
            e.preventDefault();
            e.stopPropagation();

            var $item = $(this).closest('.brave-gallery-item');
            var $preview = $item.closest('.brave-gallery-preview');
            var $input = $preview.siblings('input[type="hidden"]');

            $item.remove();

            // 更新隐藏字段
            var ids = $preview.find('.brave-gallery-item').map(function() {
                return $(this).data('id');
            }).get();
            $input.val(ids.join(','));
        });

        // 拖拽排序
        if ($.fn.sortable) {
            $('.brave-gallery-preview.sortable').sortable({
                items: '.brave-gallery-item',
                cursor: 'move',
                tolerance: 'pointer',
                update: function(event, ui) {
                    var $preview = $(this);
                    var $input = $preview.siblings('input[type="hidden"]');
                    var ids = $preview.find('.brave-gallery-item').map(function() {
                        return $(this).data('id');
                    }).get();
                    $input.val(ids.join(','));
                }
            });
        }
    }

    // 更新画廊预览
    function updateGalleryPreview(attachments) {
        var html = '';
        attachments.forEach(function(attachment) {
            html += '<div class="brave-gallery-item" data-id="' + attachment.id + '">';
            html += '<img src="' + attachment.url + '" alt="">';
            html += '<span class="brave-remove-image">×</span>';
            html += '</div>';
        });
        targetPreview.html(html);
    }

    // 初始化
    $(document).ready(function() {
        initMediaUploader();
    });

})(jQuery);
