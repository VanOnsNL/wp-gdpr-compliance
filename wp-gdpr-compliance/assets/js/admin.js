(function ($, window, document, undefined) {
    'use strict';

    var ajaxURL = wpgdprcData.ajaxURL,
        ajaxSecurity = wpgdprcData.ajaxSecurity,
        delay = (function () {
            var timer = 0;
            return function (callback, ms) {
                clearTimeout(timer);
                timer = setTimeout(callback, ms);
            };
        })(),
        $wpgdprc = $('.wpgdprc'),
        $checkbox = $('input[type="checkbox"]', $('.wpgdprc-checkbox, .wpgdprc-setting', $wpgdprc)),
        $selectAll = $('.wpgdprc-select-all', $wpgdprc),
        $formProcessDeleteRequests = $('.wpgdprc-form--process-delete-requests'),
        /**
         * @param $checkboxes
         * @returns {Array}
         * @private
         */
        _getValuesByCheckedBoxes = function ($checkboxes) {
            var output = [];
            if ($checkboxes.length) {
                $checkboxes.each(function () {
                    var $this = $(this),
                        type = $this.data('type'),
                        value = parseInt($this.val());
                    if ($this.is(':checked') && value > 0) {
                        output.push({
                            type: type,
                            value: value
                        });
                    }
                });
            }
            return output;
        },
        /**
         * @param $element
         * @returns {*}
         * @private
         */
        _getElementAjaxData = function ($element) {
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
        /**
         * @param $element
         * @private
         */
        _doProcessAction = function ($element) {
            $element.addClass('processing');
            var $checkboxContainer = $element.closest('.wpgdprc-checkbox'),
                $checkboxData = ($checkboxContainer.length) ? $checkboxContainer.next('.wpgdprc-checkbox-data') : false;
            $.ajax({
                url: ajaxURL,
                type: 'POST',
                dataType: 'JSON',
                data: {
                    action: 'wpgdprc_process_action',
                    security: ajaxSecurity,
                    data: _getElementAjaxData($element)
                },
                success: function (response) {
                    if (response) {
                        if ($checkboxData.length) {
                            if ($element.is(':checked')) {
                                $checkboxData.stop(true, true).slideDown('fast');
                            } else {
                                $checkboxData.stop(true, true).slideUp('fast');
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
        },
        _ajax = function (values, $form, delay) {
            var value = values.slice(0, 1);
            if (value.length > 0) {
                var $row = $('tr[data-id="' + value[0].type + '-' + value[0].value + '"]', $form);
                $row.addClass('wpgdprc-processing');
                setTimeout(function () {
                    $.ajax({
                        url: ajaxURL,
                        type: 'POST',
                        dataType: 'JSON',
                        data: {
                            action: 'wpgdprc_process_delete_request',
                            security: ajaxSecurity,
                            data: value[0]
                        },
                        success: function (response) {
                            if (response) {
                                values.splice(0, 1);
                                $row.removeClass('wpgdprc-processing');
                                $('input[type="checkbox"]', $row).remove();
                                $row.addClass('wpgdprc-removed');
                                _ajax(values, $form, 500);
                            }
                        }
                    });
                }, (delay || 0));
            }
        },
        initCheckboxes = function () {
            if (!$checkbox.length) {
                return;
            }
            $checkbox.on('change', function (e) {
                e.preventDefault();
                _doProcessAction($(this));
            });
        },
        initSelectAll = function () {
            if (!$selectAll.length) {
                return;
            }
            $selectAll.on('change', function () {
                var $this = $(this),
                    checked = $this.is(':checked'),
                    $checkboxes = $('tbody input[type="checkbox"]', $this.closest('table'));
                $checkboxes.prop('checked', checked);
            });
        },
        initProcessDeleteRequests = function () {
            if (!$formProcessDeleteRequests.length) {
                return;
            }
            $formProcessDeleteRequests.on('submit', function (e) {
                e.preventDefault();
                var $this = $(this),
                    $checkboxes = $('.wpgdprc-checkbox', $this);
                $selectAll.prop('checked', false);
                _ajax(_getValuesByCheckedBoxes($checkboxes), $this);
            });
        };

    $(function () {
        if (!$wpgdprc.length) {
            return;
        }
        initCheckboxes();
        initSelectAll();
        initProcessDeleteRequests();
    });
})(jQuery, window, document);