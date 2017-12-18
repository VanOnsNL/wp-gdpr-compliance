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
        add_action('admin_init', array($this, 'registerSettings'));

        foreach (Helpers::getEnabledPlugins() as $plugin) {
            switch ($plugin['id']) {
                case CF7::ID :
                    register_setting(WP_GDPR_C_SLUG, WP_GDPR_C_PREFIX . '_integrations_' . $plugin['id'] . '_forms', 'intval');
                    register_setting(WP_GDPR_C_SLUG, WP_GDPR_C_PREFIX . '_integrations_' . $plugin['id'] . '_form_text');
                    add_action('wpgdprc_integrations_' . CF7::ID . '_forms', array(CF7::getInstance(), 'addFormTagToForms'));
                    add_action('wpgdprc_integrations_' . CF7::ID . '_forms', array(CF7::getInstance(), 'removeFormTagFromForms'));
                    add_action('wpcf7_init', array(CF7::getInstance(), 'addFormTagSupport'));
                    add_filter('wpcf7_validate_wpgdprc', array(CF7::getInstance(), 'validateField'), 10, 2);
                    break;
            }
        }
    }

    public function registerSettings() {
        foreach (Helpers::getSupportedPlugins() as $plugin) {
            register_setting(WP_GDPR_C_SLUG, WP_GDPR_C_PREFIX . '_integrations_' . $plugin['id'], 'intval');
        }
    }

    /**
     * @param string $plugin
     * @return string
     */
    public static function getText($plugin = '') {
        $output = '';
        switch ($plugin) {
            case CF7::ID :
                $output = __('Yes, you may use my personal information.', WP_GDPR_C_SLUG);
                break;
        }
        return apply_filters('wpgdprc_integration_text', $output, $plugin);
    }
}