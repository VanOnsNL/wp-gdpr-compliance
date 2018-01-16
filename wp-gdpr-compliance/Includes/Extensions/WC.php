<?php

namespace WPGDPRC\Includes\Extensions;

use WPGDPRC\Includes\Helpers;
/**
 * Class WC
 * @package WPGDPRC\Includes\Extensions
 */
class WC {
    const ID = 'woocommerce';
    /** @var null */
    private static $instance = null;

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
     * @param \WC_Checkout $checkout
     */
    public function addField(\WC_Checkout $checkout) {
        woocommerce_form_field(
            'wpgdprc',
            array(
                'type' => 'checkbox',
                'class' => array('wpgdprc-checkbox'),
                'label' => esc_html(self::getLabelText()),
                'required' => true,
            ),
            $checkout->get_value('wpgdprc')
        );
    }

    public function checkPost() {
        if (!isset($_POST['wpgdprc'])) {
            wc_add_notice(sprintf(Helpers::getErrorText()), 'error');
        }
    }

    /**
     * @return mixed
     */
    public function getLabelText() {
        $default = __('By using this form you agree with the storage and handling of your data by this website.', WP_GDPR_C_SLUG);
        $option = get_option(WP_GDPR_C_PREFIX . '_integrations_' . self::ID . '_text');
        return !empty($option) ? $option : $default;
    }
}
