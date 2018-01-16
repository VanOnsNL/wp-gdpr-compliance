<?php

namespace WPGDPRC\Includes;

use WPGDPRC\Includes\Extensions\CF7;
use WPGDPRC\Includes\Extensions\WC;
use WPGDPRC\Includes\Extensions\WP;

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
            switch ($plugin['id']) {
                case CF7::ID :
                    add_action('update_option_' . WP_GDPR_C_PREFIX . '_integrations_' . CF7::ID . '_forms', array(CF7::getInstance(), 'processIntegration'));
                    add_action('update_option_' . WP_GDPR_C_PREFIX . '_integrations_' . CF7::ID . '_form_text', array(CF7::getInstance(), 'processIntegration'));
                    add_action('wpcf7_init', array(CF7::getInstance(), 'addFormTagSupport'));
                    add_filter('wpcf7_validate_wpgdprc', array(CF7::getInstance(), 'validateField'), 10, 2);
                    break;

                case WC::ID :
                    add_action('woocommerce_checkout_process', array(WC::getInstance(), 'checkPost'));
                    add_action('woocommerce_after_order_notes', array(WC::getInstance(), 'addField'));
                    break;

                case WP::ID :
                    add_action('comment_form_field_comment', array(WP::getInstance(), 'addField'));
                    add_filter('preprocess_comment', array(WP::getInstance(), 'checkPost'));
                    break;
            }
        }
        register_setting(WP_GDPR_C_SLUG, WP_GDPR_C_PREFIX . '_advanced_error');
    }

    public function registerSettings() {
        foreach (Helpers::getSupported() as $plugin) {
            register_setting(WP_GDPR_C_SLUG, WP_GDPR_C_PREFIX . '_integrations_' . $plugin['id'], 'intval');
            if(!empty($plugin['text_field'])) {
                register_setting(WP_GDPR_C_SLUG, WP_GDPR_C_PREFIX . '_integrations_' . $plugin['id'] . '_text');
            }
            if (!empty($plugin['fields'])) {
                foreach ($plugin['fields'] as $field) {
                    $field = str_replace('%id%', $plugin['id'], $field);
                    register_setting(WP_GDPR_C_SLUG, WP_GDPR_C_PREFIX . '_' . $field);
                }
            }
        }
    }
}