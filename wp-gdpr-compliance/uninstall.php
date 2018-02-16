<?php

// If uninstall is not called from WordPress, die
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die();
}

global $wpdb;

// Remove everything related to the WP GDPR Compliance plugin
$options = $wpdb->get_results("SELECT `option_name` FROM `" . $wpdb->options . "` WHERE `option_name` LIKE 'wpgdprc_%'");
if ($options !== null) {
    foreach ($options as $option) {
        delete_option($option->option_name);
    }
}