<?php

namespace WPGDPRC\Includes\Extensions;

use WPGDPRC\Includes\Helpers;

/**
 * Class CF7
 * @package WPGDPRC\Includes\Extensions\CF7
 */
class CF7 {
    /** @var null */
    private static $instance = null;

    /**
     * @return null|CF7
     */
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * @param array $panels
     * @return array
     */
    function addTab($panels = array()) {
        $pluginData = Helpers::getPluginData();
        $panels[$pluginData['TextDomain']] = array(
            'title' => __($pluginData['Name'], 'contact-form-7'),
            'callback' => array($this, 'addTabContents')
        );
        return $panels;
    }

    /**
     * @param array $args
     */
    function addTabContents($args = array()) {
        var_dump($args);
        ?>
        HELLO WORLD
        <?php
    }
}