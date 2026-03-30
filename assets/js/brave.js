/**
 * Brave Love Theme JavaScript
 */

(function($) {
    'use strict';

    // 恋爱计时器
    function initLoveTimer() {
        var $timer = $('#love-timer');
        if (!$timer.length) return;

        // 解析日期（兼容多种格式：YYYY-MM-DD 或 YYYY/MM/DD）
        var startDateStr = braveData.love_start_date || '2020-01-01';
        startDateStr = startDateStr.replace(/\//g, '-'); // 将 / 替换为 -
        
        var start = new Date(startDateStr + 'T00:00:00');
        
        // 检查日期是否有效
        if (isNaN(start.getTime())) {
            $timer.html('请设置正确的恋爱起始日期');
            return;
        }

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

    // PhotoSwipe 5 初始化 - 修复移动端支持
    function initPhotoSwipe() {
        // 检查 PhotoSwipe 是否加载
        if (typeof PhotoSwipe === 'undefined' || typeof PhotoSwipeLightbox === 'undefined') {
            console.log('PhotoSwipe not loaded');
            return;
        }

        // 相册卡片点击
        $('.memory-card').on('click touchstart', function(e) {
            // 防止重复触发
            if (e.type === 'touchstart') {
                e.preventDefault();
            }
            
            var $this = $(this);
            var photosData = $this.data('photos');
            var title = $this.data('title');
            
            if (!photosData || !photosData.length) {
                console.log('No photos in album');
                return;
            }

            // 准备图片数据
            var items = photosData.map(function(photo) {
                return {
                    src: photo.url,
                    width: photo.width || 0,
                    height: photo.height || 0,
                    alt: title + (photo.title ? ' - ' + photo.title : '')
                };
            });

            // 如果没有尺寸信息，先预加载获取
            var needLoading = items.some(function(item) {
                return item.width === 0 || item.height === 0;
            });

            if (needLoading) {
                var loadedCount = 0;
                var totalCount = items.length;
                
                items.forEach(function(item, index) {
                    if (item.width === 0 || item.height === 0) {
                        var img = new Image();
                        img.onload = function() {
                            items[index].width = this.naturalWidth;
                            items[index].height = this.naturalHeight;
                            loadedCount++;
                            
                            if (loadedCount === totalCount) {
                                openPhotoSwipe(items, 0);
                            }
                        };
                        img.onerror = function() {
                            // 如果加载失败，使用默认尺寸
                            items[index].width = 800;
                            items[index].height = 600;
                            loadedCount++;
                            
                            if (loadedCount === totalCount) {
                                openPhotoSwipe(items, 0);
                            }
                        };
                        img.src = item.src;
                    } else {
                        loadedCount++;
                    }
                });

                // 如果所有图片已有尺寸，直接打开
                if (loadedCount === totalCount) {
                    openPhotoSwipe(items, 0);
                }
            } else {
                openPhotoSwipe(items, 0);
            }
        });

        // 说说图片点击
        $('.note-image').on('click touchstart', function(e) {
            if (e.type === 'touchstart') {
                e.preventDefault();
            }
            
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

            // 预加载获取尺寸
            var loadedCount = 0;
            items.forEach(function(item, idx) {
                var img = new Image();
                img.onload = function() {
                    items[idx].width = this.naturalWidth;
                    items[idx].height = this.naturalHeight;
                    loadedCount++;
                    
                    if (loadedCount === items.length) {
                        openPhotoSwipe(items, index);
                    }
                };
                img.onerror = function() {
                    items[idx].width = 800;
                    items[idx].height = 600;
                    loadedCount++;
                    
                    if (loadedCount === items.length) {
                        openPhotoSwipe(items, index);
                    }
                };
                img.src = item.src;
            });
        });

        // 时间轴图片灯箱
        $('.timeline-image').on('click touchstart', function(e) {
            if (e.type === 'touchstart') {
                e.preventDefault();
            }
            
            if ($(this).is('a')) return;
            
            var src = $(this).attr('src');
            if (!src) return;

            var fullSrc = src.replace('-300x300', '').replace('-150x150', '').replace('-400x400', '');

            var img = new Image();
            img.onload = function() {
                openPhotoSwipe([{
                    src: fullSrc,
                    width: this.naturalWidth,
                    height: this.naturalHeight
                }], 0);
            };
            img.src = fullSrc;
        });
    }

    // 打开 PhotoSwipe
    function openPhotoSwipe(items, index) {
        // 确保所有图片都有尺寸
        items.forEach(function(item) {
            if (!item.width || item.width === 0) item.width = 800;
            if (!item.height || item.height === 0) item.height = 600;
        });

        // 使用 PhotoSwipeLightbox（支持移动端）
        var lightbox = new PhotoSwipeLightbox({
            dataSource: items,
            pswpModule: PhotoSwipe,
            bgOpacity: 0.9,
            showHideAnimationType: 'fade',
            initialZoomLevel: 'fit',
            secondaryZoomLevel: 1.5,
            maxZoomLevel: 1,
            pinchToClose: true,
            closeOnVerticalDrag: true,
            tapAction: 'toggle-controls',
            doubleTapAction: 'zoom',
            index: index
        });

        // 添加错误处理
        lightbox.on('itemLoadError', function(e) {
            console.warn('Image load error:', e.item.src);
        });

        lightbox.init();
        lightbox.loadAndOpen(index);
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
        initScrollReveal();
        initListToggle();
    });

})(jQuery);
