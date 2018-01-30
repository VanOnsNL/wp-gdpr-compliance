(function ($, window, document, undefined) {
    'use strict';

    var ajaxLoading = false,
        ajaxURL = wpgdprcData.ajaxURL,
        ajaxSecurity = wpgdprcData.ajaxSecurity,
        delay = (function () {
            var timer = 0;
            return function (callback, ms) {
                clearTimeout(timer);
                timer = setTimeout(callback, ms);
            };
        })(),
        $wpgdprc = $('.wpgdprc'),
        $wpgdprcCheckbox = $('.wpgdprc-checkbox input[type="checkbox"]', $wpgdprc),
        $wpgdprcTabs = $('.wpgdprc-tabs'),
        initCheckboxes = function () {
            if (!$wpgdprcCheckbox.length) {
                return;
            }
            $wpgdprcCheckbox.on('change', function (e) {
                e.preventDefault();
                doProcessAction($(this));
            });
        },
        initTabs = function () {
            if (!$wpgdprcTabs.length) {
                return;
            }
            var $wpgdprcTabsNavigation = $('.wpgdprc-tabs__navigation', $wpgdprcTabs),
                $wpgdprcTabsNavigationItem = $('a', $wpgdprcTabsNavigation),
                $wpgdprcTabsPanel = $('.wpgdprc-tabs__panel', $wpgdprcTabs);

            $wpgdprcTabsNavigationItem.on('click', function (e) {
                e.preventDefault();
                var target = $(this).attr('href'),
                    $target = $(target);
                if (!$target.length) {
                    return;
                }
                $wpgdprcTabsNavigationItem.removeClass('active').attr('aria-selected', false).attr('tabindex', '-1');
                $wpgdprcTabsPanel.removeClass('active').attr('aria-hidden', true);
                $(this).addClass('active').attr('aria-selected', true).attr('tabindex', 0);
                $target.addClass('active').attr('aria-hidden', false);
            });
        },
        getElementAjaxData = function ($element) {
            var data = $element.data();
            if (!data.option) {
                data.option = $element.attr('name');
            }
            if ($element.is('input')) {
                data.value = $element.val();
                if ($element.is('input[type="checkbox"]')) {
                    data.enabled = ($element.is(':checked'));
                }
            }
            return data;
        },
        doProcessAction = function ($element) {
            $element.addClass('processing');

            var $wpgdprcCheckboxContainer = $element.closest('.wpgdprc-checkbox'),
                $wpgdprcCheckboxData = ($wpgdprcCheckboxContainer.length) ? $wpgdprcCheckboxContainer.next('.wpgdprc-checkbox-data') : false;

            $.ajax({
                url: ajaxURL,
                type: 'POST',
                dataType: 'JSON',
                data: {
                    action: 'wpgdprc_process_action',
                    security: ajaxSecurity,
                    data: getElementAjaxData($element)
                },
                success: function (response) {
                    if (response) {
                        if ($wpgdprcCheckboxData.length) {
                            if ($element.is(':checked')) {
                                $wpgdprcCheckboxData.stop(true, true).slideDown('fast');
                            } else {
                                $wpgdprcCheckboxData.stop(true, true).slideUp('fast');
                            }
                        }

                        if (response.error) {
                            $element.addClass('alert');
                        }

                        if (response.redirect) {
                            document.location.href = currentPage;
                        }
                    }
                },
                complete: function () {
                    $element.removeClass('processing');
                    delay(function () {
                        $element.removeClass('alert');
                    }, 2000);
                }
            });
        };

    $(function () {
        if (!$wpgdprc.length) {
            return;
        }
        initCheckboxes();
        initTabs();
    });
})(jQuery, window, document);