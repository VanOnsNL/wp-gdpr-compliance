<?php

namespace WPGDPRC\Includes\Extensions;

use WPGDPRC\Includes\Integrations;

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
     * @return null|WC
     */
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Add WP GDPR field before submit button
     */
    public function addField() {
        woocommerce_form_field(
            'wpgdprc',
            array(
                'type' => 'checkbox',
                'class' => array('wpgdprc-checkbox'),
                'label' => Integrations::getCheckboxText(self::ID),
                'required' => true,
            )
        );
    }

    /**
     * Check if WP GDPR checkbox is checked
     */
    public function checkPost() {
        if (!isset($_POST['wpgdprc'])) {
            wc_add_notice(sprintf(Integrations::getErrorMessage(self::ID)), 'error');
        }
    }

    public function updateMeta($order_id = 0) {
        if (isset($_POST['wpgdprc']) && $order_id != 0) {
            update_post_meta( $order_id, '_gdpr-c',  date('Y-m-d H:i:s', time()));
        }
    }
}
