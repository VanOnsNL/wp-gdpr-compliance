<?php

namespace WPGDPRC\Includes;

/**
 * Class Action
 * @package WPGDPRC\Includes
 */
class Action {
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
        $page = Helper::getAccessRequestPage();
        $enabled = Helper::isEnabled('enable_access_request', 'settings');
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
        if ($enabled) {
            global $wpdb;
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            $charsetCollate = $wpdb->get_charset_collate();
            $sql = "CREATE TABLE IF NOT EXISTS `" . AccessRequest::getDatabaseTableName() . "` (
            `ID` bigint(20) NOT NULL AUTO_INCREMENT,
            `site_id` bigint(20) NOT NULL,
            `email_address` varchar(100) NOT NULL,
            `session_id` varchar(255) NOT NULL,
            `ip_address` varchar(100) NOT NULL,
            `expired` tinyint(1) DEFAULT '0' NOT NULL,
            `date_created` datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            PRIMARY KEY (`ID`)
            ) $charsetCollate;";
            dbDelta($sql);
            $sql = "CREATE TABLE IF NOT EXISTS `" . DeleteRequest::getDatabaseTableName() . "` (
            `ID` bigint(20) NOT NULL AUTO_INCREMENT,
            `site_id` bigint(20) NOT NULL,
            `access_request_id` bigint(20) NOT NULL,
            `session_id` varchar(255) NOT NULL,
            `ip_address` varchar(100) NOT NULL,
            `data_id` bigint(20) NOT NULL,
            `type` varchar(255) NOT NULL,
            `processed` tinyint(1) DEFAULT '0' NOT NULL,
            `date_created` datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            PRIMARY KEY (`ID`)
            ) $charsetCollate;";
            dbDelta($sql);
        }
    }

    /**
     * @return null|Action
     */
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}