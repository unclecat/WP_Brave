/**
 * Brave Love Theme JavaScript
 */

(function($) {
    'use strict';

    // 恋爱计时器（显示天/时/分/秒 - 实时跳动）
    function initLoveTimer() {
        var $timer = $('#love-timer');
        if (!$timer.length) return;

        // 解析日期时间（格式：YYYY-MM-DD HH:MM）
        var datetimeStr = braveData.love_start_datetime || '2020-01-01 00:00';
        datetimeStr = datetimeStr.replace(/\//g, '-'); // 将 / 替换为 -
        
        var start = new Date(datetimeStr.replace(' ', 'T') + ':00');
        
        // 检查日期是否有效
        if (isNaN(start.getTime())) {
            $timer.html('请设置正确的恋爱起始时间');
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
        setInterval(updateTimer, 1000); // 每秒更新一次，实现实时跳动
    }

    // 纪念日倒计时（显示天/时/分/秒 - 实时跳动）
    function initAnniversaryCountdown() {
        var $countdown = $('#anniversary-countdown');
        if (!$countdown.length) return;

        var datetimeStr = braveData.next_anniversary_datetime || '';
        if (!datetimeStr) return;

        // 解析日期时间（格式：YYYY-MM-DD HH:MM）
        var target = new Date(datetimeStr.replace(' ', 'T') + ':00');
        
        // 检查日期是否有效
        if (isNaN(target.getTime())) {
            $countdown.html('请设置正确的纪念日时间');
            return;
        }

        function updateCountdown() {
            var now = new Date();
            var diff = target - now;
            
            if (diff <= 0) {
                // 纪念日已到达
                $('#countdown-days').text(0);
                $('#countdown-hours').text(0);
                $('#countdown-minutes').text(0);
                $('#countdown-seconds').text(0);
                
                // 显示庆祝信息
                if (!$countdown.next('.celebration-message').length) {
                    $('<div class="celebration-message">🎉 今天就是我们的特别日子！</div>').insertAfter($countdown);
                }
                return;
            }

            var days = Math.floor(diff / (1000 * 60 * 60 * 24));
            var hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            var minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            var seconds = Math.floor((diff % (1000 * 60)) / 1000);

            $('#countdown-days').text(days);
            $('#countdown-hours').text(hours);
            $('#countdown-minutes').text(minutes);
            $('#countdown-seconds').text(seconds);
        }

        updateCountdown();
        setInterval(updateCountdown, 1000); // 每秒更新一次，实现实时跳动
    }

    // 天气小组件 - 实时总览 + 详情面板
    function initWeather() {
        var $weatherSection = $('.weather-section');
        if (!$weatherSection.length) return;

        var $weatherCards = $('.weather-card');
        if (!$weatherCards.length) return;

        var $modal = $('#weather-modal');
        var $modalContent = $('.weather-modal-content');
        var $modalClose = $('#weather-modal-close');
        var weatherCache = {};
        var activeIndex = null;
        var refreshDelay = (window.braveData && Number(braveData.weather_refresh_ms)) || (30 * 60 * 1000);
        var weatherApiUrl = window.braveData && braveData.weather_api_url ? braveData.weather_api_url : '';

        function escapeHtml(value) {
            return String(value == null ? '' : value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function formatClock(value) {
            if (!value) {
                return '--:--';
            }

            if (value.indexOf('T') !== -1) {
                var parts = value.split('T');
                if (parts[1]) {
                    return parts[1].slice(0, 5);
                }
            }

            return /^\d{2}:\d{2}$/.test(value) ? value : '--:--';
        }

        function formatUpdateText(value, stale) {
            var prefix = formatClock(value);
            var text = prefix === '--:--' ? '更新时间暂不可用' : prefix + ' 更新';

            if (stale) {
                text += ' · 稍早缓存';
            }

            return text;
        }

        function formatAqiValue(value) {
            return typeof value === 'number' && !isNaN(value) ? String(Math.round(value)) : '--';
        }

        function formatPrimaryPollutant(data) {
            var name = data.primaryPollutant || '暂无';
            var amount = data.primaryPollutantValue ? ' · ' + data.primaryPollutantValue : '';
            var unit = data.primaryPollutantUnit ? ' ' + data.primaryPollutantUnit : '';
            return name + amount + unit;
        }

        function formatWindText(data) {
            var speed = typeof data.wind === 'number' ? data.wind + ' km/h' : '-- km/h';
            var extras = [];

            if (data.windDir) {
                extras.push(data.windDir);
            }

            if (data.windScale) {
                extras.push(data.windScale + '级');
            }

            return extras.length ? speed + ' · ' + extras.join(' ') : speed;
        }

        function setCardError($card, message) {
            $card
                .removeClass('is-loading')
                .addClass('is-error')
                .attr('data-weather', 'cloudy');

            $card.find('.weather-card-status').text('暂缺');
            $card.find('.weather-icon').text('🌫️');
            $card.find('.weather-temp').text('--°');
            $card.find('.weather-desc').text(message || '天气暂不可用');
            $card.find('.weather-range').text('--° ~ --°');
            $card.find('.weather-precip').text('降水 --%');
        }

        function updateCard($card, data) {
            var statusText = data.warning && data.warning.badge ? data.warning.badge : (data.stale ? '稍早数据' : (data.isDay ? '白天' : '夜间'));
            var tempText = typeof data.temp === 'number' ? data.temp + '°' : '--°';
            var rangeText = (typeof data.tempMin === 'number' ? data.tempMin : '--') + '° ~ ' + (typeof data.tempMax === 'number' ? data.tempMax : '--') + '°';
            var precipText = typeof data.precipitationMax === 'number' ? data.precipitationMax : '--';

            $card
                .removeClass('is-loading is-error')
                .attr('data-weather', data.weatherType || 'sunny');

            $card.find('.weather-card-status').text(statusText);
            $card.find('.weather-icon').text(data.icon || '🌤️');
            $card.find('.weather-temp').text(tempText);
            $card.find('.weather-desc').text(data.desc || '天气同步中');
            $card.find('.weather-range').text(rangeText);
            $card.find('.weather-precip').text('降水 ' + precipText + '%');
        }

        function renderHourlyTrend(items) {
            var $hourly = $('#modal-hourly');

            if (!$hourly.length) {
                return;
            }

            if (!items || !items.length) {
                $hourly.html('<div class="weather-hourly-empty">暂时没有更多短时趋势数据</div>');
                return;
            }

            var html = items.map(function(item) {
                var temp = typeof item.temp === 'number' ? item.temp : '--';
                var precip = typeof item.precip === 'number' ? item.precip : '--';

                return [
                    '<div class="weather-hourly-item">',
                    '<span class="weather-hourly-time">', escapeHtml(item.time || '--:--'), '</span>',
                    '<span class="weather-hourly-icon">', escapeHtml(item.icon || '🌤️'), '</span>',
                    '<span class="weather-hourly-temp">', temp, '°</span>',
                    '<span class="weather-hourly-precip">降水 ', precip, '%</span>',
                    '</div>'
                ].join('');
            }).join('');

            $hourly.html(html);
        }

        function renderClothing(data) {
            var $tagList = $('#modal-clothing');
            var $copy = $('#modal-clothing-copy');
            var tags = data.clothing && data.clothing.tags ? data.clothing.tags : [];

            if (!$tagList.length) {
                return;
            }

            if (!tags.length) {
                $tagList.html('<span class="weather-modal-tag is-muted">待更新</span>');
            } else {
                $tagList.html(tags.map(function(tag) {
                    var extraClass = tag.kind === 'extra' ? ' is-extra' : '';
                    return '<span class="weather-modal-tag' + extraClass + '">' + escapeHtml(tag.label || '') + '</span>';
                }).join(''));
            }

            if ($copy.length) {
                $copy.text((data.clothing && data.clothing.copy) || '今天按舒服的节奏穿就很好。');
            }
        }

        function renderMinuteHint(data) {
            var $box = $('#modal-minute');
            var $text = $('#modal-minute-text');
            var summary = data.minutely && data.minutely.summary ? data.minutely.summary : '';

            if (!$box.length || !$text.length) {
                return;
            }

            if (!summary) {
                $box.prop('hidden', true);
                $text.text('未来 2 小时降雨趋势整理中');
                return;
            }

            $box.prop('hidden', false);
            $text.text(summary);
        }

        function renderAlert(data) {
            var $alert = $('#modal-alert');
            var $badge = $('#modal-alert-badge');
            var $title = $('#modal-alert-title');
            var $meta = $('#modal-alert-meta');
            var warning = data.warning || null;

            if (!$alert.length || !$badge.length || !$title.length || !$meta.length) {
                return;
            }

            if (!warning) {
                $alert.prop('hidden', true).attr('data-tone', 'unknown');
                return;
            }

            var metaParts = [];
            if (warning.pubTime) {
                metaParts.push(formatClock(warning.pubTime) + ' 发布');
            }
            if (warning.expireTime) {
                metaParts.push('至 ' + formatClock(warning.expireTime));
            }
            if (!metaParts.length) {
                metaParts.push('请以官方最新提醒为准');
            }

            $alert.prop('hidden', false).attr('data-tone', warning.tone || 'unknown');
            $badge.text(warning.badge || '天气预警');
            $title.text(warning.headline || warning.text || '天气预警');
            $meta.text(metaParts.join(' · '));
        }

        function renderHealthAdvice(data) {
            $('#modal-health-general').text((data.healthAdvice && data.healthAdvice.general) || '今天的空气和天气提示整理中。');
            $('#modal-health-sensitive').text((data.healthAdvice && data.healthAdvice.sensitive) || '如果你更容易受天气影响，记得给自己多留一点缓冲。');
        }

        function openModal(index) {
            var data = weatherCache[String(index)];

            if (!data || typeof data.temp === 'undefined' || typeof data.tempMin === 'undefined') {
                return;
            }

            activeIndex = String(index);

            $('#modal-city').text(data.name || '城市');
            $('#modal-time').text(formatUpdateText(data.updatedAt, data.stale));
            $('#modal-icon').text(data.icon || '🌤️');
            $('#modal-temp').text((typeof data.temp === 'number' ? data.temp : '--') + '°');
            $('#modal-desc').text(data.desc || '--');
            $('#modal-range').text('今日 ' + (typeof data.tempMin === 'number' ? data.tempMin : '--') + '° ~ ' + (typeof data.tempMax === 'number' ? data.tempMax : '--') + '°');
            $('#modal-sunrise').text(formatClock(data.sunrise));
            $('#modal-sunset').text(formatClock(data.sunset));
            $('#modal-aqi')
                .attr('data-tone', data.aqiTone || 'unknown')
                .find('.weather-modal-aside-text')
                .text((data.aqiLabel || '暂无') + ' · AQI ' + formatAqiValue(data.aqi));
            $('#modal-uv')
                .attr('data-tone', data.uvTone || 'unknown')
                .find('.weather-modal-aside-text')
                .text((data.uvLabel || '暂无') + ' · UV ' + (data.uvMax || '--'));
            $('#modal-primary-pollutant .weather-modal-aside-text').text(formatPrimaryPollutant(data));
            $('#modal-feels').text((typeof data.feels === 'number' ? data.feels : '--') + '°');
            $('#modal-humidity').text((typeof data.humidity === 'number' ? data.humidity : '--') + '%');
            $('#modal-wind').text(formatWindText(data));
            $('#modal-precip').text((typeof data.precipitationMax === 'number' ? data.precipitationMax : '--') + '%');

            renderAlert(data);
            renderClothing(data);
            renderMinuteHint(data);
            renderHealthAdvice(data);
            renderHourlyTrend(data.hourlyTrend || []);

            $modalContent.attr('data-weather', data.weatherType || 'sunny');
            $modal.addClass('active').attr('aria-hidden', 'false');
            $('body').css('overflow', 'hidden');
        }

        function closeModal() {
            activeIndex = null;
            $modal.removeClass('active').attr('aria-hidden', 'true');
            $('body').css('overflow', '');
        }

        function syncWeatherCards(response) {
            var seen = {};

            if (!response || !response.enabled) {
                $weatherCards.each(function() {
                    setCardError($(this), '天气已关闭');
                });
                return;
            }

            if (!response.configured) {
                $weatherCards.each(function() {
                    setCardError($(this), response.message || '天气配置中');
                });
                return;
            }

            (response.cities || []).forEach(function(item) {
                var key = String(item.index);
                var $card = $weatherCards.filter('[data-index="' + key + '"]');

                seen[key] = true;

                if (!$card.length) {
                    return;
                }

                if (item.status === 'error') {
                    setCardError($card, item.message || '同步失败');
                    return;
                }

                weatherCache[key] = item;
                updateCard($card, item);

                if (activeIndex === key) {
                    openModal(key);
                }
            });

            $weatherCards.each(function() {
                var $card = $(this);
                var key = String($card.data('index'));

                if (!seen[key] && !weatherCache[key]) {
                    setCardError($card, '同步失败');
                }
            });
        }

        function fetchWeather() {
            if (!weatherApiUrl) {
                $weatherCards.each(function() {
                    setCardError($(this), '天气接口未就绪');
                });
                return;
            }

            $.ajax({
                url: weatherApiUrl,
                method: 'GET',
                dataType: 'json',
                timeout: 15000,
                success: function(response) {
                    syncWeatherCards(response);
                },
                error: function() {
                    $weatherCards.each(function() {
                        var $card = $(this);
                        var key = String($card.data('index'));

                        if (!weatherCache[key]) {
                            setCardError($card, '天气同步失败');
                        }
                    });
                }
            });
        }

        $weatherCards.on('click', function() {
            var $row = $(this).closest('.weather-scroll');

            if ($row.attr('data-drag-click-block') === '1') {
                return;
            }

            if ($(this).hasClass('is-error')) {
                return;
            }

            openModal($(this).data('index'));
        });

        $modalClose.on('click', closeModal);

        $modal.on('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });

        $(document).on('keydown.weatherModal', function(e) {
            if (e.key === 'Escape' && $modal.hasClass('active')) {
                closeModal();
            }
        });

        fetchWeather();
        setInterval(fetchWeather, refreshDelay);
    }

    // 通用筛选下拉交互
    function initFilterDropdowns() {
        var toggles = document.querySelectorAll('.filter-dropdown-toggle');

        if (!toggles.length) return;

        function closeDropdowns(exceptId) {
            document.querySelectorAll('.filter-dropdown').forEach(function(dropdown) {
                if (dropdown.id !== exceptId) {
                    dropdown.classList.remove('show');
                }
            });

            toggles.forEach(function(toggle) {
                if ((toggle.getAttribute('data-toggle') + '-dropdown') !== exceptId) {
                    toggle.classList.remove('is-open');
                }
            });
        }

        toggles.forEach(function(toggle) {
            toggle.addEventListener('click', function(e) {
                e.stopPropagation();

                var target = this.getAttribute('data-toggle');
                var dropdownId = target ? target + '-dropdown' : '';
                var dropdown = dropdownId ? document.getElementById(dropdownId) : null;

                if (!dropdown) {
                    return;
                }

                var isOpen = dropdown.classList.contains('show');

                closeDropdowns();
                dropdown.classList.toggle('show', !isOpen);
                this.classList.toggle('is-open', !isOpen);
            });
        });

        document.addEventListener('click', function() {
            closeDropdowns();
        });
    }

    // 横向列表在桌面端支持滚轮和拖拽浏览
    function initHorizontalScrollRows() {
        var selectors = ['.weather-scroll', '.anniversary-scroll'];

        selectors.forEach(function(selector) {
            document.querySelectorAll(selector).forEach(function(row) {
                var isPointerDown = false;
                var startX = 0;
                var startScrollLeft = 0;
                var hasDragged = false;

                if (row.scrollWidth > row.clientWidth) {
                    row.classList.add('is-scrollable');
                }

                row.addEventListener('wheel', function(e) {
                    if (row.scrollWidth <= row.clientWidth) {
                        return;
                    }

                    if (Math.abs(e.deltaX) <= 0 && Math.abs(e.deltaY) <= 0) {
                        return;
                    }

                    e.preventDefault();
                    row.scrollLeft += Math.abs(e.deltaX) > Math.abs(e.deltaY) ? e.deltaX : e.deltaY;
                }, { passive: false });

                row.addEventListener('mousedown', function(e) {
                    if (row.scrollWidth <= row.clientWidth) {
                        return;
                    }

                    isPointerDown = true;
                    hasDragged = false;
                    startX = e.pageX;
                    startScrollLeft = row.scrollLeft;
                    row.classList.add('is-dragging');
                });

                window.addEventListener('mousemove', function(e) {
                    if (!isPointerDown) {
                        return;
                    }

                    var delta = e.pageX - startX;
                    if (Math.abs(delta) > 6) {
                        hasDragged = true;
                    }
                    row.scrollLeft = startScrollLeft - delta;
                });

                window.addEventListener('mouseup', function() {
                    if (!isPointerDown) {
                        return;
                    }

                    isPointerDown = false;
                    row.classList.remove('is-dragging');

                    if (hasDragged) {
                        row.setAttribute('data-drag-click-block', '1');
                        window.setTimeout(function() {
                            row.removeAttribute('data-drag-click-block');
                        }, 120);
                    }
                });

                row.addEventListener('mouseleave', function() {
                    if (!isPointerDown) {
                        return;
                    }

                    isPointerDown = false;
                    row.classList.remove('is-dragging');
                });
            });
        });
    }

    // 导航栏滚动效果
    function initNavbar() {
        var $navbar = $('.navbar-brave');
        if (!$navbar.length) return;

        function syncNavbarState() {
            if ($(window).scrollTop() > 50) {
                $navbar.addClass('scrolled');
            } else {
                $navbar.removeClass('scrolled');
            }
        }

        $(window).on('scroll', syncNavbarState);
        syncNavbarState();
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
        $(document).on('click', '.toggle-detail', function() {
            var $button = $(this);
            var $card = $button.closest('.love-list-card');
            var $detail = $card.find('.card-detail').first();
            var $label = $button.find('span').first();

            if (!$card.length || !$detail.length || !$label.length) {
                return;
            }

            var isExpanded = !$card.hasClass('expanded');

            $card.toggleClass('expanded', isExpanded);
            $detail.css('max-height', isExpanded ? $detail.prop('scrollHeight') + 'px' : '0');
            $label.text(isExpanded ? '收起详情' : '查看详情');
            $button.attr('aria-expanded', isExpanded ? 'true' : 'false');
        });

        $(window).on('resize', function() {
            $('.love-list-card.expanded .card-detail').each(function() {
                this.style.maxHeight = this.scrollHeight + 'px';
            });
        });
    }

    // 随笔说说快捷发布交互
    function initQuickNoteForm() {
        var form = document.getElementById('quick-note-form');

        if (!form) {
            return;
        }

        var moodBtns = form.querySelectorAll('.mood-btn');
        var moodInput = document.getElementById('selected-mood');
        var starBtns = form.querySelectorAll('.star-btn');
        var missInput = document.getElementById('selected-miss-level');

        if (moodBtns.length && moodInput) {
            function updateMood(value) {
                moodBtns.forEach(function(btn) {
                    btn.classList.toggle('active', btn.getAttribute('data-mood') === value);
                });
                moodInput.value = value;
            }

            moodBtns.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    updateMood(this.getAttribute('data-mood'));
                });
            });

            updateMood(moodInput.value || moodBtns[0].getAttribute('data-mood'));
        }

        if (starBtns.length && missInput) {
            function updateStars(level) {
                starBtns.forEach(function(btn, index) {
                    btn.classList.toggle('active', index < level);
                });
                missInput.value = String(level);
            }

            starBtns.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var level = parseInt(this.getAttribute('data-level'), 10);
                    updateStars(isNaN(level) ? 3 : Math.max(1, Math.min(5, level)));
                });
            });

            updateStars(Math.max(1, Math.min(5, parseInt(missInput.value, 10) || 3)));
        }
    }

    // 全局主题切换
    function initThemeToggle() {
        var root = document.documentElement;
        var toggle = document.querySelector('[data-theme-toggle]');
        var icon = document.querySelector('[data-theme-toggle-icon]');
        var themeColorMeta = document.getElementById('brave-theme-color');
        var storageKey = 'brave-theme';
        var themeColors = {
            light: '#fff7f8',
            dark: '#18161d'
        };

        function normalizeTheme(value) {
            return value === 'dark' ? 'dark' : 'light';
        }

        function persistTheme(value) {
            try {
                window.localStorage.setItem(storageKey, value);
            } catch (error) {
                // 忽略隐私模式下可能出现的存储异常。
            }
        }

        function updateToggle(theme) {
            if (!toggle) {
                return;
            }

            var isDark = theme === 'dark';
            var nextLabel = isDark ? '切换到浅色模式' : '切换到深色模式';

            toggle.setAttribute('aria-pressed', isDark ? 'true' : 'false');
            toggle.setAttribute('aria-label', nextLabel);
            toggle.setAttribute('title', nextLabel);

            if (icon) {
                icon.textContent = isDark ? '☀️' : '🌙';
            }
        }

        function applyTheme(theme, shouldPersist) {
            var normalized = normalizeTheme(theme);

            root.setAttribute('data-theme', normalized);
            root.style.colorScheme = normalized;

            if (themeColorMeta) {
                themeColorMeta.setAttribute('content', themeColors[normalized]);
            }

            if (shouldPersist) {
                persistTheme(normalized);
            }

            updateToggle(normalized);
        }

        applyTheme(root.getAttribute('data-theme'), false);

        if (!toggle) {
            return;
        }

        toggle.addEventListener('click', function() {
            var nextTheme = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            applyTheme(nextTheme, true);
        });
    }

    // 初始化
    $(document).ready(function() {
        initThemeToggle();
        initLoveTimer();
        initAnniversaryCountdown();
        initWeather();
        initHorizontalScrollRows();
        initFilterDropdowns();
        initNavbar();
        initBackToTop();
        initScrollReveal();
        initListToggle();
        initQuickNoteForm();
    });

})(jQuery);
