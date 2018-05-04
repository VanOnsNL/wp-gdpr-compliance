<?php

namespace WPGDPRC\Includes\Extensions;

use WPGDPRC\Includes\Helper;
use WPGDPRC\Includes\Integration;

/**
 * Class WC
 * @package WPGDPRC\Includes\Extensions
 */
class WC {
    const ID = 'woocommerce';
    const SUPPORTED_VERSION = '2.5.0';
    /** @var null */
    private static $instance = null;

    /**
     * Add WP GDPR field before submit button
     */
    public function addField() {
        $args = array(
            'type' => 'checkbox',
            'class' => array('wpgdprc-checkbox'),
            'label' => Integration::getCheckboxText(self::ID),
            'required' => true,
        );
        woocommerce_form_field('wpgdprc', apply_filters('wpgdprc_woocommerce_field_args', $args));
    }

    /**
     * Check if WP GDPR checkbox is checked
     */
    public function checkPost() {
        if (!isset($_POST['wpgdprc'])) {
            wc_add_notice(Integration::getErrorMessage(self::ID), 'error');
        }
    }

    /**
     * @param int $orderId
     */
    public function addAcceptedDateToOrderMeta($orderId = 0) {
        if (isset($_POST['wpgdprc']) && !empty($orderId)) {
            update_post_meta($orderId, '_wpgdprc', time());
        }
    }

    /**
     * @param \WC_Order $order
     */
    public function displayAcceptedDateInOrderData(\WC_Order $order) {
        $label = __('GDPR accepted on:', WP_GDPR_C_SLUG);
        $date = get_post_meta($order->get_id(), '_wpgdprc', true);
        $value = (!empty($date)) ? Helper::localDateFormat(get_option('date_format') . ' ' . get_option('time_format'), $date) : __('Not accepted.', WP_GDPR_C_SLUG);
        echo apply_filters(
            'wpgdprc_woocommerce_accepted_date_in_order_data',
            sprintf('<p class="form-field form-field-wide wpgdprc-accepted-date"><strong>%s</strong><br />%s</p>', $label, $value),
            $label,
            $value,
            $order
        );
    }

    /**
     * @return null|WC
     */
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}
