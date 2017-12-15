<?php

namespace WPGDPRC\Includes;

//add use namespace for your extension
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
        add_action('init', array($this, 'init'));
    }

    public function init() {
        foreach (Helpers::getActivatedPlugins() as $plugin) {
            register_setting(WP_GDPR_C_SLUG, WP_GDPR_C_SLUG . '_' . $plugin['id']);

            switch ($plugin['id']) {
                //Add your plugin support here
                default:
                    break;
            }
        }
    }
}