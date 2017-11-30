<?php

namespace WPGDPRC\Includes\Extensions;

/**
 * Class CF7
 * @package WPGDPRC\Includes\Extensions
 */
class CF7 {
    /** @var null */
    private static $instance = null;

    /**
     * @return null|Pages
     */
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    function add_tab ( $panels ) {

        $new_page = array(
            'GDPR-Extension' => array(
                'title' => __( 'GDPR', 'contact-form-7' ),
                'callback' => 'add_tab_contents'
            )
        );

        $panels = array_merge($panels, $new_page);

        return $panels;

    }

    function add_tab_contents ( $args ) {

    }
}