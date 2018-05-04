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
        ajaxLoading = false,
        ajaxURL = wpgdprcData.ajaxURL,
        ajaxSecurity = wpgdprcData.ajaxSecurity,
        /**
         * @param data
         * @param values
         * @param $form
         * @param delay
         * @private
         */
        _ajax = function (data, values, $form, delay) {
            var $feedback = $form.querySelector('.wpgdprc-feedback'),
                value = values.slice(0, 1);
            if (value.length > 0) {
                var $row = $form.querySelector('tr[data-id="' + value[0] + '"]');
                $row.classList.remove('wpgdprc-status--error');
                $row.classList.add('wpgdprc-status--processing');
                $feedback.setAttribute('style', 'display: none;');
                $feedback.classList.remove('wpgdprc-feedback--error');
                $feedback.innerHTML = '';
                setTimeout(function () {
                    var request = new XMLHttpRequest();
                    data.data.value = value[0];
                    request.open('POST', ajaxURL);
                    request.setRequestHeader('Content-type', 'application/x-www-form-urlencoded; charset=UTF-8');
                    request.send(_objectToParametersString(data));
                    request.addEventListener('load', function () {
                        if (request.response) {
                            var response = JSON.parse(request.response);
                            $row.classList.remove('wpgdprc-status--processing');
                            if (response.error) {
                                $row.classList.add('wpgdprc-status--error');
                                $feedback.innerHTML = response.error;
                                $feedback.classList.add('wpgdprc-feedback--error');
                                $feedback.removeAttribute('style');
                            } else {
                                values.splice(0, 1);
                                $row.querySelector('input[type="checkbox"]').remove();
                                $row.classList.add('wpgdprc-status--removed');
                                _ajax(data, values, $form, 500);
                            }
                        }
                    });
                }, (delay || 0));
            }
        };

    document.addEventListener('DOMContentLoaded', function () {
        var $formAccessRequest = document.querySelector('.wpgdprc-form--access-request'),
            $formDeleteRequest = document.querySelectorAll('.wpgdprc-form--delete-request');

        if ($formAccessRequest !== null) {
            var $feedback = $formAccessRequest.querySelector('.wpgdprc-feedback'),
                $emailAddress = $formAccessRequest.querySelector('#wpgdprc-form__email'),
                $consent = $formAccessRequest.querySelector('#wpgdprc-form__consent');

            $formAccessRequest.addEventListener('submit', function (e) {
                e.preventDefault();
                if (!ajaxLoading) {
                    ajaxLoading = true;
                    $feedback.style.display = 'none';
                    $feedback.classList.remove('wpgdprc-feedback--success', 'wpgdprc-feedback--error');
                    $feedback.innerHTML = '';

                    var data = {
                            action: 'wpgdprc_process_action',
                            security: ajaxSecurity,
                            data: {
                                type: 'access_request',
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
                                $formAccessRequest.reset();
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
                        ajaxLoading = false;
                    });
                }
            });
        }

        if ($formDeleteRequest.length > 0) {
            $formDeleteRequest.forEach(function ($form) {
                var $selectAll = $form.querySelector('.wpgdprc-select-all');

                $form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    var $this = e.target,
                        $checkboxes = $this.querySelectorAll('.wpgdprc-checkbox'),
                        data = {
                            action: 'wpgdprc_process_action',
                            security: ajaxSecurity,
                            data: {
                                type: 'delete_request',
                                session: wpgdprcData.session,
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