/**
 * 甜蜜相册交互脚本
 *
 * @package Brave_Love
 * @version 0.3.0
 */

(function() {
    'use strict';

    // 等待 DOM 和 PhotoSwipe 加载完成
    document.addEventListener('DOMContentLoaded', function() {
        initGallery();
    });

    /**
     * 初始化相册
     */
    function initGallery() {
        const gallery = document.getElementById('galleryWaterfall');
        if (!gallery) return;

        const items = gallery.querySelectorAll('.gallery-item');
        if (items.length === 0) return;

        // 为每个项目绑定点击事件
        items.forEach(function(item, index) {
            const link = item.querySelector('.gallery-item-link');
            if (!link) return;

            link.addEventListener('click', function(e) {
                e.preventDefault();
                openPhotoSwipe(index, items);
            });
        });
    }

    /**
     * 打开 PhotoSwipe
     */
    function openPhotoSwipe(index, items) {
        if (typeof PhotoSwipe === 'undefined' || typeof PhotoSwipeLightbox === 'undefined') {
            console.warn('PhotoSwipe not loaded');
            return;
        }

        // 构建 PhotoSwipe 数据
        const pswpItems = Array.from(items).map(function(item) {
            const link = item.querySelector('.gallery-item-link');
            const img = item.querySelector('.gallery-item-img');
            const width = parseInt(item.dataset.pswpWidth) || 1920;
            const height = parseInt(item.dataset.pswpHeight) || 1080;
            
            return {
                src: link.href,
                width: width,
                height: height,
                alt: img ? img.alt : '',
                caption: link.dataset.caption || '',
                info: link.dataset.info ? JSON.parse(link.dataset.info) : null,
            };
        });

        // 创建 PhotoSwipe 实例
        const pswp = new PhotoSwipe({
            dataSource: pswpItems,
            index: index,
            pswpModule: PhotoSwipe,
            bgOpacity: 0.9,
            showHideAnimationType: 'zoom',
            imageClickAction: 'next',
            tapAction: 'toggle-controls',
            doubleTapAction: 'zoom',
        });

        // 添加自定义信息面板
        pswp.on('change', function() {
            updateCustomInfo(pswp.currSlide.data);
        });

        pswp.on('afterInit', function() {
            updateCustomInfo(pswp.currSlide.data);
            
            // 添加点击切换信息面板显示
            const customInfo = document.getElementById('pswpCustomInfo');
            if (customInfo) {
                customInfo.addEventListener('click', function(e) {
                    if (e.target.tagName !== 'A') {
                        e.stopPropagation();
                    }
                });
            }
        });

        // 初始化
        pswp.init();
    }

    /**
     * 更新自定义信息面板
     */
    function updateCustomInfo(data) {
        const customInfo = document.getElementById('pswpCustomInfo');
        if (!customInfo || !data.info) return;

        const info = data.info;
        
        // 更新日期
        const dateEl = customInfo.querySelector('.pswp-info-date');
        if (dateEl) dateEl.textContent = info.date || '';
        
        // 更新地点
        const locationEl = customInfo.querySelector('.pswp-info-location');
        if (locationEl) {
            if (info.location) {
                locationEl.textContent = '📍 ' + info.location;
                locationEl.style.display = 'inline-flex';
            } else {
                locationEl.style.display = 'none';
            }
        }
        
        // 更新心情
        const moodEl = customInfo.querySelector('.pswp-info-mood');
        if (moodEl) {
            if (info.mood && info.mood.trim()) {
                moodEl.textContent = info.mood;
                moodEl.style.display = 'inline-flex';
            } else {
                moodEl.style.display = 'none';
            }
        }
        
        // 更新摘要
        const summaryEl = customInfo.querySelector('.pswp-info-summary');
        if (summaryEl) {
            if (info.summary) {
                // 去除 HTML 标签
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = info.summary;
                summaryEl.textContent = tempDiv.textContent || tempDiv.innerText || '';
                summaryEl.style.display = '-webkit-box';
            } else {
                summaryEl.style.display = 'none';
            }
        }
        
        // 更新链接
        const linkEl = customInfo.querySelector('.pswp-info-link');
        if (linkEl) {
            if (info.momentUrl) {
                linkEl.href = info.momentUrl;
                linkEl.style.display = 'inline-block';
            } else {
                linkEl.style.display = 'none';
            }
        }

        // 显示面板
        customInfo.classList.add('visible');
    }

    /**
     * 懒加载优化
     */
    function initLazyLoad() {
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver(function(entries, observer) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        if (img.dataset.src) {
                            img.src = img.dataset.src;
                            img.removeAttribute('data-src');
                        }
                        observer.unobserve(img);
                    }
                });
            }, {
                rootMargin: '50px 0px',
                threshold: 0.01
            });

            document.querySelectorAll('.gallery-item-img[data-src]').forEach(function(img) {
                imageObserver.observe(img);
            });
        }
    }

    // 初始化懒加载
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initLazyLoad);
    } else {
        initLazyLoad();
    }

})();
