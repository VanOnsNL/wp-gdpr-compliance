<?php

namespace WPGDPRC\Includes;

/**
 * Class Actions
 * @package WPGDPRC\Includes
 */
class Actions {
    /** @var null */
    private static $instance = null;

    /**
     * Stop WordPress from sending anything but essential data during the update check
     * @param array $query
     * @return array
     */
    public function onlySendEssentialDataDuringUpdateCheck($query = array()) {
        unset($query['php']);
        unset($query['mysql']);
        unset($query['local_package']);
        unset($query['blogs']);
        unset($query['users']);
        unset($query['multisite_enabled']);
        unset($query['initial_db_version']);
        return $query;
    }

    public function processEnablingRequestUserData() {
        $enabled = Helpers::isEnabled('enable_request_user_data', 'settings');
        $page = get_pages(array(
            'post_type' => 'page',
            'post_status' => 'publish,private,draft',
            'meta_key' => '_wpgdprc_request_user_data',
        ));
        $page = (!empty($page)) ? $page[0] : false;
        if ($enabled) {
            if (empty($page)) {
                $result = wp_insert_post(array(
                    'post_type' => 'page',
                    'post_status' => 'private',
                    'post_title' => __('[WPGDPRC] Request Data', WP_GDPR_C_SLUG),
                    'post_content' => '[wpgdprc_request_form]',
                    'meta_input' => array(
                        '_wpgdprc_request_user_data' => 1,
                    ),
                ), true);
            } else {
                wp_update_post(array(
                    'ID' => $page->ID,
                    'post_status' => 'private'
                ));
            }
        } else {
            if (!empty($page)) {
                wp_update_post(array(
                    'ID' => $page->ID,
                    'post_status' => 'draft'
                ));
            }
        }
    }

    /**
     * @return null|Actions
     */
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}