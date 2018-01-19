<?php

namespace WPGDPRC\Includes\Extensions;

use WPGDPRC\Includes\Helpers;

/**
 * Class WP
 * @package WPGDPRC\Includes\Extensions
 */
class WP {
    const ID = 'wordpress';
    /** @var null */
    private static $instance = null;

    /**
     * @return WP
     */
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * @param string $field
     * @return string
     */
    public function addField($field = '') {
        $field .= apply_filters('wpgdprc_wordpress_field', '<p class="wpgdprc-checkbox"><label><input type="checkbox" name="wpgdprc" id="wpgdprc" value="1" />' . self::getLabelText() . ' <abbr class="required" title="required">*</abbr></label></p>', $field);
        return $field;
    }

    /**
     * @param array $post
     * @return array
     */
    public function checkPost($post = array()) {
        if (!isset($_POST['wpgdprc'])) {
            wp_die(Helpers::getErrorText());
        }
        return $post;
    }

    /**
     * @return mixed
     */
    public function getLabelText() {
        $option = get_option(WP_GDPR_C_PREFIX . '_integrations_' . self::ID . '_text');
        return (!empty($option)) ? esc_html($option) : __('By using this form you agree with the storage and handling of your data by this website.', WP_GDPR_C_SLUG);
    }
}