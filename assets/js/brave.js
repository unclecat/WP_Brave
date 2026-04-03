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

        // WMO 天气代码映射
        var weatherCodes = {
            0: { icon: '☀️', desc: '晴朗' },
            1: { icon: '🌤️', desc: '大致晴朗' },
            2: { icon: '⛅', desc: '局部多云' },
            3: { icon: '☁️', desc: '阴天' },
            45: { icon: '🌫️', desc: '雾' },
            48: { icon: '🌫️', desc: '雾凇' },
            51: { icon: '🌦️', desc: '毛毛雨' },
            53: { icon: '🌦️', desc: '小雨' },
            55: { icon: '🌧️', desc: '中雨' },
            56: { icon: '🌧️', desc: '冻雨' },
            57: { icon: '🌧️', desc: '强冻雨' },
            61: { icon: '🌧️', desc: '阵雨' },
            63: { icon: '🌧️', desc: '中雨' },
            65: { icon: '⛈️', desc: '大雨' },
            66: { icon: '🌧️', desc: '冻雨' },
            67: { icon: '⛈️', desc: '强冻雨' },
            71: { icon: '🌨️', desc: '小雪' },
            73: { icon: '🌨️', desc: '中雪' },
            75: { icon: '❄️', desc: '大雪' },
            77: { icon: '❄️', desc: '冰粒' },
            80: { icon: '🌦️', desc: '阵雨' },
            81: { icon: '🌧️', desc: '较强阵雨' },
            82: { icon: '⛈️', desc: '强阵雨' },
            85: { icon: '🌨️', desc: '阵雪' },
            86: { icon: '❄️', desc: '强阵雪' },
            95: { icon: '⛈️', desc: '雷雨' },
            96: { icon: '⛈️', desc: '雷雨伴冰雹' },
            99: { icon: '⛈️', desc: '强雷雨伴冰雹' }
        };

        function getWeatherInfo(code, isDay) {
            var info = weatherCodes[code] || { icon: '🌡️', desc: '天气未知' };

            if (!isDay && code === 0) {
                return { icon: '🌙', desc: '晴夜' };
            }

            if (!isDay && code === 1) {
                return { icon: '🌙', desc: '夜间少云' };
            }

            return info;
        }

        function getWeatherType(code) {
            if ((code >= 51 && code <= 67) || (code >= 80 && code <= 82)) {
                return 'rainy';
            }

            if ((code >= 71 && code <= 77) || code === 85 || code === 86) {
                return 'snowy';
            }

            if (code >= 95) {
                return 'stormy';
            }

            if ((code >= 1 && code <= 3) || code === 45 || code === 48) {
                return 'cloudy';
            }

            return 'sunny';
        }

        function roundNumber(value, fallback) {
            return typeof value === 'number' && !isNaN(value) ? Math.round(value) : fallback;
        }

        function getArrayValue(list, index, fallback) {
            return list && typeof list[index] !== 'undefined' ? list[index] : fallback;
        }

        function formatClock(value) {
            if (!value || value.indexOf('T') === -1) {
                return '--:--';
            }

            return value.split('T')[1].slice(0, 5);
        }

        function formatUpdateText(value) {
            if (!value || value.indexOf('T') === -1) {
                return '更新时间暂不可用';
            }

            return formatClock(value) + ' 更新';
        }

        function formatUvIndex(value) {
            if (typeof value !== 'number' || isNaN(value)) {
                return '--';
            }

            var rounded = Math.round(value * 10) / 10;
            return rounded % 1 === 0 ? rounded.toFixed(0) : rounded.toFixed(1);
        }

        function buildHourlyTrend(hourlyData, currentTime) {
            var items = [];

            if (!hourlyData || !hourlyData.time || !hourlyData.time.length) {
                return items;
            }

            var currentHour = currentTime && currentTime.indexOf('T') !== -1 ? currentTime.slice(0, 13) + ':00' : hourlyData.time[0];
            var startIndex = 0;

            for (var i = 0; i < hourlyData.time.length; i++) {
                if (hourlyData.time[i] >= currentHour) {
                    startIndex = i;
                    break;
                }
            }

            for (var j = startIndex; j < hourlyData.time.length && items.length < 6; j++) {
                var hourlyCode = getArrayValue(hourlyData.weather_code, j, 0);
                var hourlyInfo = getWeatherInfo(hourlyCode, true);

                items.push({
                    time: formatClock(hourlyData.time[j]),
                    icon: hourlyInfo.icon,
                    temp: roundNumber(getArrayValue(hourlyData.temperature_2m, j, null), '--'),
                    precip: roundNumber(getArrayValue(hourlyData.precipitation_probability, j, 0), 0)
                });
            }

            return items;
        }

        function renderHourlyTrend(items) {
            var $hourly = $('#modal-hourly');

            if (!$hourly.length) {
                return;
            }

            if (!items.length) {
                $hourly.html('<div class="weather-hourly-empty">暂时没有更多短时趋势数据</div>');
                return;
            }

            var html = items.map(function(item) {
                return [
                    '<div class="weather-hourly-item">',
                    '<span class="weather-hourly-time">', item.time, '</span>',
                    '<span class="weather-hourly-icon">', item.icon, '</span>',
                    '<span class="weather-hourly-temp">', item.temp, '°</span>',
                    '<span class="weather-hourly-precip">降水 ', item.precip, '%</span>',
                    '</div>'
                ].join('');
            }).join('');

            $hourly.html(html);
        }

        function getClothingAdvice(temp, weatherCode, precipitation, windSpeed) {
            var baseAdvice = '';
            var extraTips = [];

            if (temp >= 30) {
                baseAdvice = '短袖或轻薄裙装就够了，尽量选透气面料。';
                extraTips.push('记得防晒和补水');
            } else if (temp >= 24) {
                baseAdvice = '短袖加一件薄外套，室内外切换会更舒服。';
            } else if (temp >= 18) {
                baseAdvice = '衬衫、薄针织或卫衣都很合适。';
            } else if (temp >= 12) {
                baseAdvice = '外套最好带上，早晚会明显偏凉。';
            } else if (temp >= 5) {
                baseAdvice = '厚外套或毛衣更稳妥，别只顾好看。';
            } else {
                baseAdvice = '羽绒或加绒衣物更合适，保暖优先。';
            }

            if (precipitation >= 45 || (weatherCode >= 51 && weatherCode <= 82) || weatherCode >= 95) {
                extraTips.push('出门带伞');
            }

            if (windSpeed >= 25) {
                extraTips.push('风偏大，注意防风');
            }

            return baseAdvice + (extraTips.length ? ' ' + extraTips.join('，') + '。' : '');
        }

        function setCardError($card) {
            $card
                .removeClass('is-loading')
                .addClass('is-error')
                .attr('data-weather', 'cloudy');

            $card.find('.weather-card-status').text('暂缺');
            $card.find('.weather-icon').text('🌫️');
            $card.find('.weather-temp').text('--°');
            $card.find('.weather-desc').text('天气暂不可用');
            $card.find('.weather-range').text('--° ~ --°');
            $card.find('.weather-precip').text('降水 --%');
        }

        function updateCard($card, data) {
            $card
                .removeClass('is-loading is-error')
                .attr('data-weather', data.weatherType);

            $card.find('.weather-card-status').text(data.isDay ? '白天' : '夜间');
            $card.find('.weather-icon').text(data.icon);
            $card.find('.weather-temp').text(data.temp + '°');
            $card.find('.weather-desc').text(data.desc);
            $card.find('.weather-range').text(data.tempMin + '° ~ ' + data.tempMax + '°');
            $card.find('.weather-precip').text('降水 ' + data.precipitationMax + '%');
        }

        function fetchCityWeather($card) {
            var lat = $card.data('lat');
            var lon = $card.data('lon');
            var index = String($card.data('index'));
            var cityName = $card.data('name');
            var url = 'https://api.open-meteo.com/v1/forecast?latitude=' + lat + '&longitude=' + lon +
                '&current=temperature_2m,relative_humidity_2m,apparent_temperature,weather_code,wind_speed_10m,is_day' +
                '&hourly=temperature_2m,weather_code,precipitation_probability' +
                '&daily=temperature_2m_max,temperature_2m_min,sunrise,sunset,uv_index_max,precipitation_probability_max' +
                '&forecast_days=2&timezone=auto';

            $.ajax({
                url: url,
                method: 'GET',
                dataType: 'json',
                timeout: 10000,
                success: function(data) {
                    if (!data || !data.current || !data.daily) {
                        setCardError($card);
                        return;
                    }

                    var current = data.current;
                    var daily = data.daily;
                    var code = roundNumber(current.weather_code, 0);
                    var isDay = Number(current.is_day) === 1;
                    var weatherInfo = getWeatherInfo(code, isDay);
                    var weatherType = getWeatherType(code);
                    var temp = roundNumber(current.temperature_2m, '--');
                    var feels = roundNumber(current.apparent_temperature, '--');
                    var humidity = roundNumber(current.relative_humidity_2m, '--');
                    var wind = roundNumber(current.wind_speed_10m, '--');
                    var tempMax = roundNumber(getArrayValue(daily.temperature_2m_max, 0, null), '--');
                    var tempMin = roundNumber(getArrayValue(daily.temperature_2m_min, 0, null), '--');
                    var precipitationMax = roundNumber(getArrayValue(daily.precipitation_probability_max, 0, 0), 0);
                    var uvMax = getArrayValue(daily.uv_index_max, 0, null);
                    var sunrise = getArrayValue(daily.sunrise, 0, '');
                    var sunset = getArrayValue(daily.sunset, 0, '');

                    weatherCache[index] = {
                        name: cityName,
                        temp: temp,
                        feels: feels,
                        humidity: humidity,
                        wind: wind,
                        code: code,
                        icon: weatherInfo.icon,
                        desc: weatherInfo.desc,
                        weatherType: weatherType,
                        isDay: isDay,
                        updatedAt: current.time,
                        tempMax: tempMax,
                        tempMin: tempMin,
                        precipitationMax: precipitationMax,
                        uvMax: formatUvIndex(uvMax),
                        sunrise: sunrise,
                        sunset: sunset,
                        daylight: formatClock(sunrise) + ' / ' + formatClock(sunset),
                        hourlyTrend: buildHourlyTrend(data.hourly, current.time),
                        advice: getClothingAdvice(temp, code, precipitationMax, wind)
                    };

                    updateCard($card, weatherCache[index]);

                    if (activeIndex === index) {
                        openModal(index);
                    }
                },
                error: function() {
                    setCardError($card);
                }
            });
        }

        function openModal(index) {
            var data = weatherCache[String(index)];

            if (!data) {
                return;
            }

            activeIndex = String(index);

            $('#modal-city').text(data.name);
            $('#modal-time').text(formatUpdateText(data.updatedAt));
            $('#modal-icon').text(data.icon);
            $('#modal-temp').text(data.temp + '°');
            $('#modal-desc').text(data.desc);
            $('#modal-range').text('今日 ' + data.tempMin + '° ~ ' + data.tempMax + '°');
            $('#modal-feels').text(data.feels + '°');
            $('#modal-humidity').text(data.humidity + '%');
            $('#modal-wind').text(data.wind + ' km/h');
            $('#modal-precip').text(data.precipitationMax + '%');
            $('#modal-uv').text(data.uvMax);
            $('#modal-daylight').text(data.daylight);
            $('#modal-clothing').text(data.advice);
            renderHourlyTrend(data.hourlyTrend);

            $modalContent.attr('data-weather', data.weatherType);
            $modal.addClass('active').attr('aria-hidden', 'false');
            $('body').css('overflow', 'hidden');
        }

        function closeModal() {
            activeIndex = null;
            $modal.removeClass('active').attr('aria-hidden', 'true');
            $('body').css('overflow', '');
        }

        $weatherCards.each(function() {
            fetchCityWeather($(this));
        });

        $weatherCards.on('click', function() {
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

        setInterval(function() {
            $weatherCards.each(function() {
                fetchCityWeather($(this));
            });
        }, 30 * 60 * 1000);
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
        if (!$('.memory-card').length) return;

        // 检查 PhotoSwipe 是否加载
        if (typeof PhotoSwipe === 'undefined' || typeof PhotoSwipeLightbox === 'undefined') {
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
        initAnniversaryCountdown();
        initWeather();
        initFilterDropdowns();
        initNavbar();
        initBackToTop();
        initPhotoSwipe();
        initScrollReveal();
        initListToggle();
    });

})(jQuery);
