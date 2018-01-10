<?php

namespace WPGDPRC\Includes\Extensions;

use WPGDPRC\Includes\Integrations;

class WC {
    private static $instance;

    private function __construct() {}

    private function __clone() {}

    const ID = 'woocommerce';

    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function addField( $checkout ) {
        woocommerce_form_field('gdpr_accept', array(
            'type'          => 'checkbox',
            'class'         => array('input-checkbox'),
            'label'         => self::getLabelText(),
            'required'  => true,
        ), $checkout->get_value('gdpr_accept'));
    }

    public function checkPost() {
        if (!isset($_POST['gdpr_accept'])) {
            wc_add_notice(sprintf(__('The field is required', 'themename')) ,'error');
        }
    }

    public function getLabelText() {
        $default = __('By using this form you agree with the storage and handling of your data by this website.', WP_GDPR_C_SLUG);
        $option = get_option(WP_GDPR_C_PREFIX . '_integrations_' . self::ID .'_text');
        return !empty($option) ? $option : $default;
    }
}
