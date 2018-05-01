(function (window, document, undefined) {
    'use strict';

    /**
     * @param data
     * @returns {string}
     * @private
     */
    var _objectToParametersString = function (data) {
            return Object.keys(data).map(function (key) {
                var value = data[key];
                if (typeof value === 'object') {
                    value = JSON.stringify(value);
                }
                return key + '=' + value;
            }).join('&');
        },
        /**
         * @param $checkboxes
         * @returns {Array}
         * @private
         */
        _getValuesByCheckedBoxes = function ($checkboxes) {
            var output = [];
            if ($checkboxes.length) {
                $checkboxes.forEach(function (e) {
                    var value = parseInt(e.value);
                    if (e.checked && value > 0) {
                        output.push(value);
                    }
                });
            }
            return output;
        },
        loading = false,
        ajaxURL = wpgdprcData.ajaxURL,
        _ajax = function (data, values, $form, delay) {
            var value = values.slice(0, 1);
            if (value.length > 0 && !loading) {
                loading = true;
                var $row = $form.querySelector('tr[data-id="' + value[0] + '"]');
                $row.classList.add('wpgdprc-processing');
                setTimeout(function () {
                    var request = new XMLHttpRequest();
                    data.data.value = value[0];
                    request.open('POST', ajaxURL);
                    request.setRequestHeader('Content-type', 'application/x-www-form-urlencoded; charset=UTF-8');
                    request.send(_objectToParametersString(data));
                    request.addEventListener('load', function () {
                        if (request.response) {
                            var response = JSON.parse(request.response);
                            values.splice(0, 1);
                            loading = false;
                            $row.classList.remove('wpgdprc-processing');
                            $row.querySelector('input').remove();
                            $row.classList.add('wpgdprc-removed');
                            _ajax(data, values, $form, 500);
                        }
                    });
                }, (delay || 0));
            }
        };

    document.addEventListener('DOMContentLoaded', function () {
        var ajaxSecurity = wpgdprcData.ajaxSecurity,
            $formRequestData = document.querySelector('#wpgdprc-form'),
            $formDeleteData = document.querySelectorAll('.wpgdprc-form');

        if ($formRequestData !== null) {
            var $feedback = document.querySelector('.wpgdprc-feedback'),
                $emailAddress = document.querySelector('#wpgdprc-form__email'),
                $consent = document.querySelector('#wpgdprc-form__consent');

            $formRequestData.addEventListener('submit', function (e) {
                e.preventDefault();
                if (!loading) {
                    loading = true;
                    $feedback.style.display = 'none';
                    $feedback.classList.remove('wpgdprc-feedback--success', 'wpgdprc-feedback--error');
                    $feedback.innerHTML = '';

                    var data = {
                            action: 'wpgdprc_process_action',
                            security: ajaxSecurity,
                            data: {
                                type: 'request_data',
                                email: $emailAddress.value,
                                consent: $consent.checked
                            }
                        },
                        request = new XMLHttpRequest();

                    data = _objectToParametersString(data);
                    request.open('POST', ajaxURL, true);
                    request.setRequestHeader('Content-type', 'application/x-www-form-urlencoded; charset=UTF-8');
                    request.send(data);
                    request.addEventListener('load', function () {
                        if (request.response) {
                            var response = JSON.parse(request.response);
                            if (response.message) {
                                $formRequestData.reset();
                                $emailAddress.blur();
                                $feedback.innerHTML = response.message;
                                $feedback.classList.add('wpgdprc-feedback--success');
                                $feedback.removeAttribute('style');
                            }
                            if (response.error) {
                                $emailAddress.focus();
                                $feedback.innerHTML = response.error;
                                $feedback.classList.add('wpgdprc-feedback--error');
                                $feedback.removeAttribute('style');
                            }
                        }
                        loading = false;
                    });
                }
            });
        }

        if ($formDeleteData.length > 0) {
            $formDeleteData.forEach(function ($form) {
                var $selectAll = $form.querySelector('.wpgdprc-select-all');

                $form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    var $this = e.target,
                        $checkboxes = $this.querySelectorAll('.wpgdprc-checkbox'),
                        data = {
                            action: 'wpgdprc_process_action',
                            security: ajaxSecurity,
                            data: {
                                type: 'delete_data',
                                settings: JSON.parse($this.dataset.wpgdprc)
                            }
                        };
                    $selectAll.checked = false;
                    _ajax(data, _getValuesByCheckedBoxes($checkboxes), $this);
                });

                if ($selectAll !== null) {
                    $selectAll.addEventListener('change', function (e) {
                        var $this = e.target,
                            checked = $this.checked,
                            $checkboxes = $form.querySelectorAll('.wpgdprc-checkbox');
                        $checkboxes.forEach(function (e) {
                            e.checked = checked;
                        });
                    });
                }
            });
        }
    });
})(window, document);