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
        ajaxURL = wpgdprcData.ajaxURL,
        ajaxSecurity = wpgdprcData.ajaxSecurity,
        $wpgdprcForm = document.getElementById('wpgdprc-form'),
        $wpgdprcFormEmailField = document.getElementById('wpgpdrc-form__email');

    if ($wpgdprcForm && $wpgdprcFormEmailField) {
        $wpgdprcForm.addEventListener('submit', function (e) {
            e.preventDefault();
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
                console.log('Ja');
            });
        });
    }
})(window, document);