/**
 * Brave Love Admin JavaScript
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

    $(document).ready(function() {
        initMediaField();
    });

})(jQuery);
