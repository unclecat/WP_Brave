/**
 * Brave Love Theme JavaScript
 */

(function($) {
    'use strict';

    // 恋爱计时器
    function initLoveTimer() {
        var $timer = $('#love-timer');
        if (!$timer.length) return;

        var startDate = braveData.love_start_date || '2020-01-01';
        var start = new Date(startDate + 'T00:00:00');

        function updateTimer() {
            var now = new Date();
            var diff = now - start;
            
            if (diff < 0) {
                $timer.html('还没开始呢~');
                return;
            }

            var days = Math.floor(diff / (1000 * 60 * 60 * 24));
            var hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            var minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            var seconds = Math.floor((diff % (1000 * 60)) / 1000);

            $('#timer-days').text(days);
            $('#timer-hours').text(hours);
            $('#timer-minutes').text(minutes);
            $('#timer-seconds').text(seconds);
        }

        updateTimer();
        setInterval(updateTimer, 1000);
    }

    // 导航栏滚动效果
    function initNavbar() {
        var $navbar = $('.navbar-brave');
        if (!$navbar.length) return;

        $(window).on('scroll', function() {
            if ($(window).scrollTop() > 50) {
                $navbar.addClass('scrolled');
            } else {
                $navbar.removeClass('scrolled');
            }
        });
    }

    // 返回顶部
    function initBackToTop() {
        var $btn = $('#backToTop');
        if (!$btn.length) return;

        $(window).on('scroll', function() {
            if ($(window).scrollTop() > 200) {
                $btn.addClass('show');
            } else {
                $btn.removeClass('show');
            }
        });

        $btn.on('click', function() {
            $('html, body').animate({ scrollTop: 0 }, 300);
        });
    }

    // PhotoSwipe 5 初始化
    function initPhotoSwipe() {
        if (typeof PhotoSwipe === 'undefined') return;

        // 相册卡片点击 - 使用 PhotoSwipe Lightbox
        $('.memory-card').on('click', function() {
            var photosData = $(this).data('photos');
            var title = $(this).data('title');
            
            if (!photosData || !photosData.length) return;

            var items = photosData.map(function(photo) {
                return {
                    src: photo.url,
                    width: 0,
                    height: 0,
                    alt: title + (photo.title ? ' - ' + photo.title : '')
                };
            });

            // 预加载获取图片尺寸
            var loadedCount = 0;
            items.forEach(function(item, index) {
                var img = new Image();
                img.onload = function() {
                    items[index].width = this.naturalWidth;
                    items[index].height = this.naturalHeight;
                    loadedCount++;
                };
                img.src = item.src;
            });

            // 创建 PhotoSwipe
            var pswp = new PhotoSwipe({
                dataSource: items,
                bgOpacity: 0.9,
                showHideAnimationType: 'fade',
                pswpModule: PhotoSwipe
            });

            pswp.init();
        });

        // 说说图片点击
        $('.note-image').on('click', function(e) {
            e.stopPropagation();
            var $this = $(this);
            var $images = $this.closest('.note-images').find('.note-image');
            var index = $images.index($this);

            var items = [];
            $images.each(function() {
                var src = $(this).data('full') || $(this).attr('src');
                items.push({
                    src: src,
                    width: 0,
                    height: 0
                });
            });

            // 预加载获取图片尺寸
            items.forEach(function(item, idx) {
                var img = new Image();
                img.onload = function() {
                    items[idx].width = this.naturalWidth;
                    items[idx].height = this.naturalHeight;
                };
                img.src = item.src;
            });

            var pswp = new PhotoSwipe({
                dataSource: items,
                index: index,
                bgOpacity: 0.9,
                showHideAnimationType: 'fade',
                pswpModule: PhotoSwipe
            });

            pswp.init();
        });
    }

    // 时间轴图片灯箱
    function initTimelineLightbox() {
        $('.timeline-image').on('click', function(e) {
            if ($(this).is('a')) return;
            
            var src = $(this).attr('src');
            if (!src) return;

            var fullSrc = src.replace('-300x300', '').replace('-150x150', '').replace('-400x400', '');

            var items = [{
                src: fullSrc,
                width: 0,
                height: 0
            }];

            var img = new Image();
            img.onload = function() {
                items[0].width = this.naturalWidth;
                items[0].height = this.naturalHeight;
            };
            img.src = fullSrc;

            var pswp = new PhotoSwipe({
                dataSource: items,
                bgOpacity: 0.9,
                showHideAnimationType: 'fade',
                pswpModule: PhotoSwipe
            });

            pswp.init();
        });
    }

    // 滚动渐显动画
    function initScrollReveal() {
        var $elements = $('.fade-in');
        if (!$elements.length) return;

        var observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    $(entry.target).addClass('visible');
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        });

        $elements.each(function() {
            observer.observe(this);
        });
    }

    // 清单项展开/收起
    function initListToggle() {
        $('.list-toggle').on('click', function(e) {
            e.stopPropagation();
            var $detail = $(this).closest('.list-item').find('.list-detail');
            $detail.toggleClass('show');
            $(this).text($detail.hasClass('show') ? '▲' : '▼');
        });
    }

    // 初始化
    $(document).ready(function() {
        initLoveTimer();
        initNavbar();
        initBackToTop();
        initPhotoSwipe();
        initTimelineLightbox();
        initScrollReveal();
        initListToggle();
    });

})(jQuery);
