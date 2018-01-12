<?php

namespace WPGDPRC\Includes\Extensions;


class WC {
    private function __construct() {}

    private static $instance;

    const ID = 'woocommerce';

    /**
     * @return WC
     */
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * @param $checkout
     */
    public function addField($checkout) {
        woocommerce_form_field('gdpr_accept', array(
            'type'          => 'checkbox',
            'class'         => array('input-checkbox'),
            'label'         => esc_html(self::getLabelText()),
            'required'  => true,
        ), $checkout->get_value('gdpr_accept'));
    }

    /**
     *
     */
    public function checkPost() {
        if (!isset($_POST['gdpr_accept'])) {
            wc_add_notice(sprintf(esc_html(get_option(WP_GDPR_C_PREFIX . '_advanced_error'))) ,'error');
        }
    }

    /**
     * @return mixed
     */
    public function getLabelText() {
        $default = __('By using this form you agree with the storage and handling of your data by this website.', WP_GDPR_C_SLUG);
        $option = get_option(WP_GDPR_C_PREFIX . '_integrations_' . self::ID .'_text');
        return !empty($option) ? $option : $default;
    }
}
