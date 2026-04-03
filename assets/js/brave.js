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

        function formatAqi(value) {
            if (typeof value !== 'number' || isNaN(value)) {
                return '--';
            }

            return String(Math.round(value));
        }

        function getAqiTone(value) {
            if (typeof value !== 'number' || isNaN(value)) {
                return 'unknown';
            }

            if (value <= 50) {
                return 'good';
            }

            if (value <= 100) {
                return 'moderate';
            }

            if (value <= 150) {
                return 'sensitive';
            }

            if (value <= 200) {
                return 'unhealthy';
            }

            return 'hazardous';
        }

        function getAqiLabel(value) {
            if (typeof value !== 'number' || isNaN(value)) {
                return '暂无';
            }

            if (value <= 50) {
                return '优';
            }

            if (value <= 100) {
                return '良';
            }

            if (value <= 150) {
                return '敏感';
            }

            if (value <= 200) {
                return '较差';
            }

            return '很差';
        }

        function getUvLabel(value) {
            if (typeof value !== 'number' || isNaN(value)) {
                return '暂无';
            }

            if (value < 3) {
                return '低';
            }

            if (value < 6) {
                return '中';
            }

            if (value < 8) {
                return '较高';
            }

            if (value < 11) {
                return '高';
            }

            return '很高';
        }

        function getUvTone(value) {
            if (typeof value !== 'number' || isNaN(value)) {
                return 'unknown';
            }

            if (value < 3) {
                return 'good';
            }

            if (value < 6) {
                return 'moderate';
            }

            if (value < 8) {
                return 'sensitive';
            }

            if (value < 11) {
                return 'unhealthy';
            }

            return 'hazardous';
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

        function addClothingTag(tags, label, kind) {
            if (!label) {
                return;
            }

            var exists = tags.some(function(item) {
                return item.label === label;
            });

            if (!exists) {
                tags.push({
                    label: label,
                    kind: kind || 'wear'
                });
            }
        }

        function getClothingTags(feelsLike, weatherCode, precipitation, windSpeed, uvValue, aqiValue) {
            var tags = [];
            var hasRain = precipitation >= 45 || (weatherCode >= 51 && weatherCode <= 82) || weatherCode >= 95;

            if (typeof feelsLike !== 'number' || isNaN(feelsLike)) {
                return tags;
            }

            if (feelsLike >= 30) {
                addClothingTag(tags, '短袖');
                addClothingTag(tags, '短裤');
            } else if (feelsLike >= 24) {
                addClothingTag(tags, '短袖');
                addClothingTag(tags, '薄裤');
            } else if (feelsLike >= 18) {
                addClothingTag(tags, '长袖');
                addClothingTag(tags, '长裤');
            } else if (feelsLike >= 12) {
                addClothingTag(tags, '长袖');
                addClothingTag(tags, '薄外套');
                addClothingTag(tags, '长裤');
            } else if (feelsLike >= 6) {
                addClothingTag(tags, '卫衣');
                addClothingTag(tags, '外套');
                addClothingTag(tags, '长裤');
            } else {
                addClothingTag(tags, '羽绒');
                addClothingTag(tags, '保暖裤');
            }

            if (hasRain) {
                addClothingTag(tags, '带伞', 'extra');
            }

            if (typeof windSpeed === 'number' && !isNaN(windSpeed) && windSpeed >= 25) {
                addClothingTag(tags, '防风', 'extra');
            }

            if (typeof uvValue === 'number' && !isNaN(uvValue) && uvValue >= 6) {
                addClothingTag(tags, '防晒', 'extra');
            }

            if (typeof aqiValue === 'number' && !isNaN(aqiValue) && aqiValue > 100) {
                addClothingTag(tags, '口罩', 'extra');
            }

            return tags;
        }

        function renderClothingTags(tags) {
            var $container = $('#modal-clothing');

            if (!$container.length) {
                return;
            }

            if (!tags.length) {
                $container.html('<span class="weather-modal-tag is-muted">待更新</span>');
                return;
            }

            var html = tags.map(function(item) {
                return '<span class="weather-modal-tag' + (item.kind === 'extra' ? ' is-extra' : '') + '">' + item.label + '</span>';
            }).join('');

            $container.html(html);
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
                    var previousData = weatherCache[index] || {};

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
                        uvValue: uvMax,
                        uvMax: formatUvIndex(uvMax),
                        uvLabel: getUvLabel(uvMax),
                        uvTone: getUvTone(uvMax),
                        sunrise: sunrise,
                        sunset: sunset,
                        hourlyTrend: buildHourlyTrend(data.hourly, current.time),
                        aqi: typeof previousData.aqi === 'number' ? previousData.aqi : null,
                        aqiDisplay: previousData.aqiDisplay || '--',
                        aqiTone: previousData.aqiTone || 'unknown',
                        aqiLabel: previousData.aqiLabel || '暂无'
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

            $.ajax({
                url: 'https://air-quality-api.open-meteo.com/v1/air-quality?latitude=' + lat + '&longitude=' + lon + '&current=us_aqi&timezone=auto',
                method: 'GET',
                dataType: 'json',
                timeout: 10000,
                success: function(data) {
                    var current = data && data.current ? data.current : {};
                    var aqiValue = typeof current.us_aqi === 'number' ? current.us_aqi : null;
                    var existingData = weatherCache[index] || { name: cityName };

                    existingData.aqi = aqiValue;
                    existingData.aqiDisplay = formatAqi(aqiValue);
                    existingData.aqiTone = getAqiTone(aqiValue);
                    existingData.aqiLabel = getAqiLabel(aqiValue);

                    weatherCache[index] = existingData;

                    if (activeIndex === index) {
                        openModal(index);
                    }
                }
            });
        }

        function openModal(index) {
            var data = weatherCache[String(index)];

            if (!data || typeof data.temp === 'undefined' || typeof data.tempMin === 'undefined') {
                return;
            }

            activeIndex = String(index);

            $('#modal-city').text(data.name);
            $('#modal-time').text(formatUpdateText(data.updatedAt));
            $('#modal-icon').text(data.icon);
            $('#modal-temp').text(data.temp + '°');
            $('#modal-desc').text(data.desc);
            $('#modal-range').text('今日 ' + data.tempMin + '° ~ ' + data.tempMax + '°');
            $('#modal-sunrise').text(formatClock(data.sunrise));
            $('#modal-sunset').text(formatClock(data.sunset));
            $('#modal-aqi')
                .attr('data-tone', data.aqiTone || 'unknown')
                .find('.weather-modal-aside-text')
                .text((data.aqiLabel || '暂无') + ' · AQI ' + (data.aqiDisplay || '--'));
            $('#modal-uv')
                .attr('data-tone', data.uvTone || 'unknown')
                .find('.weather-modal-aside-text')
                .text((data.uvLabel || '暂无') + ' · UV ' + (data.uvMax || '--'));
            $('#modal-feels').text(data.feels + '°');
            $('#modal-humidity').text(data.humidity + '%');
            $('#modal-wind').text(data.wind + ' km/h');
            $('#modal-precip').text(data.precipitationMax + '%');
            renderClothingTags(getClothingTags(data.feels, data.code, data.precipitationMax, data.wind, data.uvValue, data.aqi));
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
