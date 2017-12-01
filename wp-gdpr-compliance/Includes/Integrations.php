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

    public function init() {
        foreach (Helpers::getActivatedPlugins() as $plugin) {
            register_setting(WP_GDPR_C_SLUG, WP_GDPR_C_SLUG . '_' . $plugin['id']);
            switch ($plugin['id']) {
                case 'contact-form-7' :
                    add_filter('wpcf7_editor_panels', array(CF7::getInstance(), 'addTab'));
                    break;
            }
        }
    }
}