<?php

namespace WPGDPRC\Includes\Extensions;

use WPGDPRC\Includes\Integrations;

/**
 * Class WC
 * @package WPGDPRC\Includes\Extensions
 */
class WC {
    const ID = 'woocommerce';
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
}
