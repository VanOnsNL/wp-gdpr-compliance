(function($, window, document, undefined) {
    'use strict';

    var $wpgdprc = $('.wpgdprc'),
        $wpgdprcCheckbox = $('.wpgdprc-checkbox input[type="checkbox"]', $wpgdprc),
        $wpgdprcTabs = $('.wpgdprc-tabs'),
        initCheckboxes = function () {
            if (!$wpgdprcCheckbox.length) {
                return;
            }
            $wpgdprcCheckbox.on('change', function(e) {
                e.preventDefault();
                var $wpgdprcCheckboxContainer = $(this).closest('.wpgdprc-checkbox'),
                    $wpgdprcChecklistDescription = $wpgdprcCheckboxContainer.next('.wpgdprc-checklist-description');
                if ($(this).is(':checked')) {
                    $wpgdprcChecklistDescription.stop(true, true).slideDown('fast');
                } else {
                    $wpgdprcChecklistDescription.stop(true, true).slideUp('fast');
                }
            });
        },
        initTabs = function () {
            if (!$wpgdprcTabs.length) {
                return;
            }
            var $wpgdprcTabsNavigation = $('.wpgdprc-tabs__navigation', $wpgdprcTabs),
                $wpgdprcTabsNavigationItem = $('a', $wpgdprcTabsNavigation),
                $wpgdprcTabsPanel = $('.wpgdprc-tabs__panel', $wpgdprcTabs);

            $wpgdprcTabsNavigationItem.on('click', function(e) {
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
        initSettingsAjax = function() {

        };

    $(function() {
        if (!$wpgdprc.length) {
            return;
        }
        initCheckboxes();
        initTabs();
        initSettingsAjax();
    });
})(jQuery, window, document);