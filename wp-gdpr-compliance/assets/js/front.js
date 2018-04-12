(function (window, document, undefined) {
    'use strict';

    var _objectToParametersString = function (data) {
            return Object.keys(data).map(function (key) {
                var value = data[key];
                if (typeof value === 'object') {
                    value = JSON.stringify(value);
                }
                return key + '=' + value;
            }).join('&');
        },
        ajaxLoading = false,
        ajaxURL = wpgdprcData.ajaxURL,
        ajaxSecurity = wpgdprcData.ajaxSecurity,
        $wpgdprc = document.querySelector('.wpgdprc'),
        $wpgdprcFeedback = document.querySelector('.wpgdprc-feedback'),
        $wpgdprcForm = document.getElementById('wpgdprc-form'),
        $wpgdprcFormEmailField = document.getElementById('wpgpdrc-form__email');

    if ($wpgdprc && $wpgdprcForm && $wpgdprcFormEmailField) {
        $wpgdprcForm.addEventListener('submit', function (e) {
            e.preventDefault();
            if (!ajaxLoading) {
                ajaxLoading = true;
                $wpgdprcFeedback.style.display = 'none';
                $wpgdprcFeedback.classList.remove('wpgdprc-feedback--success', 'wpgdprc-feedback--error');
                $wpgdprcFeedback.innerHTML = '';

                var data = {
                        action: 'wpgdprc_process_action',
                        security: ajaxSecurity,
                        data: {
                            type: 'request_data',
                            email: $wpgdprcFormEmailField.value
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
                            $wpgdprcFormEmailField.value = '';
                            $wpgdprcFormEmailField.blur();
                            $wpgdprcFeedback.innerHTML = response.message;
                            $wpgdprcFeedback.classList.add('wpgdprc-feedback--success');
                            $wpgdprcFeedback.removeAttribute('style');
                        }
                        if (response.error) {
                            $wpgdprcFormEmailField.focus();
                            $wpgdprcFeedback.innerHTML = response.error;
                            $wpgdprcFeedback.classList.add('wpgdprc-feedback--error');
                            $wpgdprcFeedback.removeAttribute('style');
                        }
                    }
                    ajaxLoading = false;
                });
            }
        });
    }
})(window, document);