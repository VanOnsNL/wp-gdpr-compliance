<?php

namespace WPGDPRC\Includes\Extensions;


class WP {
    private function __construct()  {}

    private static $instance;

    const ID = 'wordpress';

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
        $field .= '
            <label class="checkbox"><br>
                <input class="input-checkbox" name="gdpr_accept" id="gdpr_accept" value="1" type="checkbox">'. esc_html(self::getLabelText()) .'<abbr class="required" title="required">*</abbr>
            </label>';
        return $field;
    }

    /**
     * @param \WP_Post $post
     * @return \WP_Post
     */
    public function checkPost($post) {
        if (!isset($_POST['gdpr_accept'])) {
            wp_die(get_option(WP_GDPR_C_PREFIX . '_advanced_error'));
        }
        return $post;
    }

    /**
     * @return mixed
     */
    public function getLabelText() {
        $default = __('By using this form you agree with the storage and handling of your data by this website.', WP_GDPR_C_SLUG);
        $option = get_option(WP_GDPR_C_PREFIX . '_integrations_' . self::ID .'_text');
        return !empty($option) ? $option : $default;
    }
}