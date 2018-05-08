<?php

/*
 Plugin Name: WP GDPR Compliance
 Plugin URI:  https://www.wpgdprc.com/
 Description: This plugin assists website and webshop owners to comply with European privacy regulations known as GDPR. By May 24th, 2018 your website or shop has to comply to avoid large fines.
 Version:     1.3.1
 Author:      Van Ons
 Author URI:  https://www.van-ons.nl/
 License:     GPL2
 License URI: https://www.gnu.org/licenses/gpl-2.0.html
 Text Domain: wp-gdpr-compliance
 Domain Path: /languages
*/

/*
This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see http://www.gnu.org/licenses.
*/

namespace WPGDPRC;

use WPGDPRC\Includes\Action;
use WPGDPRC\Includes\Ajax;
use WPGDPRC\Includes\Cron;
use WPGDPRC\Includes\Filter;
use WPGDPRC\Includes\Helper;
use WPGDPRC\Includes\Integration;
use WPGDPRC\Includes\Page;
use WPGDPRC\Includes\Shortcode;

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die();
}

define('WP_GDPR_C_SLUG', 'wp-gdpr-compliance');
define('WP_GDPR_C_PREFIX', 'wpgdprc');
define('WP_GDPR_C_ROOT_FILE', __FILE__);
define('WP_GDPR_C_DIR', plugin_dir_path(WP_GDPR_C_ROOT_FILE));
define('WP_GDPR_C_DIR_JS', WP_GDPR_C_DIR . 'assets/js');
define('WP_GDPR_C_DIR_CSS', WP_GDPR_C_DIR . 'assets/css');
define('WP_GDPR_C_DIR_SVG', WP_GDPR_C_DIR . 'assets/svg');
define('WP_GDPR_C_URI', plugin_dir_url(WP_GDPR_C_ROOT_FILE));
define('WP_GDPR_C_URI_JS', WP_GDPR_C_URI . 'assets/js');
define('WP_GDPR_C_URI_CSS', WP_GDPR_C_URI . 'assets/css');
define('WP_GDPR_C_URI_SVG', WP_GDPR_C_URI . 'assets/svg');

// Let's do this!
spl_autoload_register(__NAMESPACE__ . '\\autoload');
add_action('plugins_loaded', array(WPGDPRC::getInstance(), 'init'));

/**
 * Class WPGDPRC
 * @package WPGDPRC
 */
class WPGDPRC {
    /** @var null */
    private static $instance = null;

    public function init() {
        if (is_admin() && !function_exists('get_plugin_data')) {
            require_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }
        load_plugin_textdomain(WP_GDPR_C_SLUG, false, basename(dirname(__FILE__)) . '/languages/');
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'addActionLinksToPluginPage'));
        add_action('admin_init', array(Page::getInstance(), 'registerSettings'));
        add_action('admin_menu', array(Page::getInstance(), 'addAdminMenu'));
        add_action('wp_enqueue_scripts', array($this, 'loadAssets'), 999);
        add_action('admin_enqueue_scripts', array($this, 'loadAdminAssets'), 999);
        add_action('core_version_check_query_args', array(Action::getInstance(), 'onlySendEssentialDataDuringUpdateCheck'));
        add_action('wp_ajax_nopriv_wpgdprc_process_action', array(Ajax::getInstance(), 'processAction'));
        add_action('wp_ajax_wpgdprc_process_action', array(Ajax::getInstance(), 'processAction'));
        add_action('update_option_wpgdprc_settings_enable_access_request', array(Action::getInstance(), 'processEnableAccessRequest'));
        add_filter('pre_update_option_wpgdprc_settings_access_request_page', array(Filter::getInstance(), 'processEnableAccessRequest'));
        Integration::getInstance();
        if (Helper::isEnabled('enable_access_request', 'settings')) {
            add_action('admin_notices', array(Action::getInstance(), 'showNoticesRequestUserData'));
            add_action('wpgdprc_deactivate_access_requests', array(Cron::getInstance(), 'deactivateAccessRequests'));
            add_action('wp_ajax_wpgdprc_process_delete_request', array(Ajax::getInstance(), 'processDeleteRequest'));
            add_shortcode('wpgdprc_access_request_form', array(Shortcode::getInstance(), 'accessRequestForm'));
            if (!wp_next_scheduled('wpgdprc_deactivate_access_requests')) {
                wp_schedule_event(time(), 'hourly', 'wpgdprc_deactivate_access_requests');
            }
        } else {
            if (wp_next_scheduled('wpgdprc_deactivate_access_requests')) {
                wp_clear_scheduled_hook('wpgdprc_deactivate_access_requests');
            }
        }
    }

    /**
     * @param array $links
     * @return array
     */
    public function addActionLinksToPluginPage($links = array()) {
        $actionLinks = array(
            'settings' => '<a href="' . add_query_arg(array('page' => str_replace('-', '_', WP_GDPR_C_SLUG)), admin_url('tools.php')) . '" aria-label="' . esc_attr__('View WP GDPR Compliance settings', WP_GDPR_C_SLUG) . '">' . esc_html__('Settings', WP_GDPR_C_SLUG) . '</a>',
        );
        return array_merge($actionLinks, $links);
    }

    public function loadAssets() {
        wp_register_style('wpgdprc.css', WP_GDPR_C_URI_CSS . '/front.css', array(), filemtime(WP_GDPR_C_DIR_CSS . '/front.css'));
        wp_register_script('wpgdprc.js', WP_GDPR_C_URI_JS . '/front.js', array(), filemtime(WP_GDPR_C_DIR_JS . '/front.js'), true);
        $data = array(
            'ajaxURL' => admin_url('admin-ajax.php'),
            'ajaxSecurity' => wp_create_nonce('wpgdprc'),
        );
        if (!empty($_REQUEST['wpgdprc'])) {
            $data['session'] = esc_html($_REQUEST['wpgdprc']);
        }
        wp_localize_script('wpgdprc.js', 'wpgdprcData', $data);
    }

    public function loadAdminAssets() {
        wp_enqueue_style('wpgdprc.admin.css', WP_GDPR_C_URI_CSS . '/admin.css', array(), filemtime(WP_GDPR_C_DIR_CSS . '/admin.css'));
        wp_enqueue_script('wpgdprc.admin.js', WP_GDPR_C_URI_JS . '/admin.js', array(), filemtime(WP_GDPR_C_DIR_JS . '/admin.js'), true);
        wp_localize_script('wpgdprc.admin.js', 'wpgdprcData', array(
            'ajaxURL' => admin_url('admin-ajax.php'),
            'ajaxSecurity' => wp_create_nonce('wpgdprc'),
        ));
    }

    /**
     * @return null|WPGDPRC
     */
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}

/**
 * @param string $class
 */
function autoload($class = '') {
    if (!strstr($class, 'WPGDPRC')) {
        return;
    }
    $result = str_replace('WPGDPRC\\', '', $class);
    $result = str_replace('\\', '/', $result);
    require $result . '.php';
}