<?php

/*
 Plugin Name: WP GDPR Compliance
 Plugin URI:  https://www.wpgdprc.com/
 Description: This plugin assists website and webshop owners to comply with European privacy regulations (known as GDPR). By May 24th, 2018 your website or shop has to comply to avoid large fines.
 Version:     1.0
 Author:      Donny Oexman, Van Ons
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

// If this file is called directly, abort.
use WPGDPRC\Includes\Pages;

if (!defined('WPINC')) {
    die();
}

define('WP_GDPR_C_SLUG', 'wp-gdpr-compliance');
define('WP_GDPR_C_DIR', plugin_dir_path(__FILE__));
define('WP_GDPR_C_DIR_JS', WP_GDPR_C_DIR . 'assets/js');
define('WP_GDPR_C_DIR_CSS', WP_GDPR_C_DIR . 'assets/css');
define('WP_GDPR_C_DIR_SVG', WP_GDPR_C_DIR . 'assets/svg');
define('WP_GDPR_C_URI', plugin_dir_url(__FILE__));
define('WP_GDPR_C_URI_JS', WP_GDPR_C_URI . 'assets/js');
define('WP_GDPR_C_URI_CSS', WP_GDPR_C_URI . 'assets/css');
define('WP_GDPR_C_URI_SVG', WP_GDPR_C_URI . 'assets/svg');

// Let's do this!
add_action('plugins_loaded', array(WPGDPRC::getInstance(), 'init'));

/**
 * Class WPGDPRC
 * @package WPGDPRC
 */
class WPGDPRC {
    /** @var null */
    private static $instance = null;

    /**
     * @return null|WPGDPRC
     */
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function init() {
        load_plugin_textdomain(WP_GDPR_C_SLUG, false, basename(dirname(__FILE__)) . '/languages/');
        add_action('admin_menu', array(Pages::getInstance(), 'addAdminMenu'));
        add_action('admin_enqueue_scripts', array($this, 'loadAssets'), 999);
        add_action('admin_head', array($this, 'addToAdminHead'));
    }

    public function loadAssets() {
        wp_enqueue_style('wpgdprc.css', WP_GDPR_C_URI_CSS . '/admin.css', array(), filemtime(WP_GDPR_C_DIR_CSS . '/admin.css'));
        wp_enqueue_style('wpgdprc.fontawesome', WP_GDPR_C_URI_CSS . '/font-awesome.min.css', array(), false);
        wp_enqueue_script('wpgdprc.js', WP_GDPR_C_URI_JS . '/admin.js', array(), filemtime(WP_GDPR_C_DIR_JS . '/admin.js'), true);
    }

    public function addToAdminHead() {
        ?>
        <script src="//use.typekit.net/ais6lnh.js"></script>
        <script>try{Typekit.load({ async: true });}catch(e){}</script>
        <?php
    }
}

spl_autoload_register(__NAMESPACE__ . '\\autoload');

/**
 * @param $class
 */
function autoload($class) {
    if (!strstr($class, 'WPGDPRC')) {
        return;
    }
    $result = str_replace('WPGDPRC\\', '', $class);
    $result = str_replace('\\', '/', $result);
    require $result . '.php';
}