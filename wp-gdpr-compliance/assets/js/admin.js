(function($, window, document, undefined) {
    'use strict';

    var $wpgdprc = $('.wpgdprc'),
        $wpgdprcCheckbox = $('.wpgdprc-checkbox input[type="checkbox"]', $wpgdprc);

    $(function() {
        if (!$wpgdprc.length) {
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
    });
})(jQuery, window, document);