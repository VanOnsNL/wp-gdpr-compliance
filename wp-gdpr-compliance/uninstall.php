<?php

// If uninstall is not called from WordPress, die
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die();
}

global $wpdb;

$options = $wpdb->get_results( "SELECT option_name FROM ".$wpdb->options." WHERE option_name LIKE 'wpgdprc_%'");

foreach( $options as $option ) {
    delete_option( $option->option_name );
}