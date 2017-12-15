<?php

namespace WPGDPRC\Includes;

use WPGDPRC\Includes\Extensions\CF7;

/**
 * Class Integrations
 * @package WPGDPRC\Includes
 */
class Integrations {
    /** @var null */
    private static $instance = null;

    /**
     * @return null|Integrations
     */
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Integrations constructor.
     */
    public function __construct() {
        foreach (Helpers::getEnabledPlugins() as $plugin) {
            register_setting(WP_GDPR_C_SLUG, WP_GDPR_C_SLUG . '_' . $plugin['id']);
            switch ($plugin['id']) {
                case 'contact-form-7' :
                    CF7::getInstance()->addFormTagToForms();
                    add_action('wpcf7_init', array(CF7::getInstance(), 'addFormTags'));
                    add_filter('wpcf7_validate_wpgdprc', array(CF7::getInstance(), 'validateField'), 10, 2);
                    break;
            }
        }
    }
}