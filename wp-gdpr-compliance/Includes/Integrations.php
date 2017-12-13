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
        add_action('admin_init', array($this, 'init'));
        add_action('wpcf7_init', array(CF7::getInstance(), 'addFormTags'));
        add_filter('wpcf7_validate_wpgdprc', array(CF7::getInstance(), 'validateField'), 10, 2);

        /*
         * This is to add [WPGDPRC] to all forms
         * TODO: ONLY do this AFTER enabling the "Contact Form 7" checkbox
         */
        $forms = CF7::getInstance()->getForms();
        foreach ($forms as $form) {
            $output = get_post_meta($form, '_form', true);
            if (!preg_match('/(\[wpgdprc?.*\])/', $output)) {
                $pattern = '/(\[submit?.*\])/';
                preg_match($pattern, $output, $matches);
                if (!empty($matches)) {
                    $output = preg_replace($pattern, "[wpgdprc]\n\n" . $matches[0], $output);
                } else {
                    $output = $output . "\n\n[wpgdprc]";
                }
                update_post_meta($form, '_form', $output);
            }
        }
    }

    public function init() {
        foreach (Helpers::getActivatedPlugins() as $plugin) {
            register_setting(WP_GDPR_C_SLUG, WP_GDPR_C_SLUG . '_' . $plugin['id']);
            switch ($plugin['id']) {
                //Add you plugin support here
                default:
                    break;
            }
        }
    }
}