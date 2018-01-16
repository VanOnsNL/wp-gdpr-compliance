<?php

namespace WPGDPRC\Includes\Extensions;

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
        $field .= apply_filters('wpgdprc_wordpress_field', '<p class="wpgdprc-checkbox"><label><input type="checkbox" name="wpgdprc" id="wpgdprc" value="1" />' . esc_html(self::getLabelText()) . ' <abbr class="required" title="required">*</abbr></label></p>', $field);
        return $field;
    }

    /**
     * @param array $post
     * @return array
     */
    public function checkPost($post = array()) {
        if (!isset($_POST['wpgdprc'])) {
            wp_die(self::getErrorText());
        }
        return $post;
    }

    /**
     * @return mixed
     */
    public function getErrorText() {
        $default = __('Please accept the privacy checkbox.', WP_GDPR_C_SLUG);
        $option = esc_html(get_option(WP_GDPR_C_PREFIX . '_advanced_error'));
        return !empty($option) ? $option : $default;
    }

    /**
     * @return mixed
     */
    public function getLabelText() {
        $default = __('By using this form you agree with the storage and handling of your data by this website.', WP_GDPR_C_SLUG);
        $option = get_option(WP_GDPR_C_PREFIX . '_integrations_' . self::ID . '_text');
        return !empty($option) ? $option : $default;
    }
}