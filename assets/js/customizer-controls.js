(function($) {
    function updateOrder($control) {
        var order = [];

        $control.find('.brave-sortable-item').each(function(index) {
            order.push($(this).data('value'));
            $(this).find('.brave-sortable-order').text(index + 1);
        });

        $control.find('.brave-sortable-input').val(order.join(',')).trigger('change');
    }

    function initSortableControl($control) {
        var $list = $control.find('.brave-sortable-list');

        if (!$list.length || $list.data('braveSortableReady')) {
            return;
        }

        $list.data('braveSortableReady', true);
        updateOrder($control);

        if (!$.fn.sortable) {
            return;
        }

        $list.sortable({
            items: '> .brave-sortable-item',
            axis: 'y',
            handle: '.brave-sortable-handle',
            placeholder: 'brave-sortable-placeholder',
            forcePlaceholderSize: true,
            tolerance: 'pointer',
            start: function(event, ui) {
                ui.item.addClass('is-sorting');
            },
            stop: function(event, ui) {
                ui.item.removeClass('is-sorting');
                updateOrder($control);
            }
        });
    }

    function initAll(context) {
        $(context).find('.brave-sortable-control').each(function() {
            initSortableControl($(this));
        });
    }

    $(document).ready(function() {
        initAll(document);
    });

    if (window.wp && wp.customize) {
        wp.customize.bind('ready', function() {
            initAll(document);
        });
    }
})(jQuery);
