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

    public function processEnableAccessRequest() {
        $page = Helpers::getAccessRequestPage();
        $enabled = Helpers::isEnabled('enable_access_request', 'settings');
        $status = ($enabled) ? 'private' : 'draft';
        if ($enabled && $page === false) {
            $result = wp_insert_post(array(
                'post_type' => 'page',
                'post_status' => $status,
                'post_title' => __('[WPGDPRC] Access Request', WP_GDPR_C_SLUG),
                'post_content' => '[wpgdprc_access_request_form]',
                'meta_input' => array(
                    '_wpgdprc_access_request' => 1,
                ),
            ), true);
            if (!is_wp_error($result)) {
                $page = get_post($result);
            }
        }
        if (!empty($page)) {
            wp_update_post(array(
                'ID' => $page->ID,
                'post_status' => $status
            ));
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