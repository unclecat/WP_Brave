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

    function initTravelPlanDays() {
        var $daysList = $('#brave-travel-days');

        if (!$daysList.length) {
            return;
        }

        var template = $('#tmpl-brave-travel-day-card').html() || '';

        function refreshDayLabels() {
            $daysList.find('.brave-travel-day-card').each(function(index) {
                $(this)
                    .attr('data-day-index', index)
                    .find('.brave-travel-day-badge')
                    .text('Day ' + (index + 1));
            });
        }

        $(document).on('click', '.brave-travel-day-add', function(e) {
            e.preventDefault();

            if (!template) {
                return;
            }

            $daysList.append(template);
            refreshDayLabels();
        });

        $(document).on('click', '.brave-travel-day-remove', function(e) {
            e.preventDefault();

            var $cards = $daysList.find('.brave-travel-day-card');

            if ($cards.length <= 1) {
                $cards.find('input[type="text"], input[type="date"], textarea').val('');
                return;
            }

            $(this).closest('.brave-travel-day-card').remove();
            refreshDayLabels();
        });

        refreshDayLabels();
    }

    $(document).ready(function() {
        initMediaField();
        initTravelPlanDays();
    });

})(jQuery);
