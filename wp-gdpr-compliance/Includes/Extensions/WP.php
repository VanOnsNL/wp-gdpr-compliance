<?php

namespace WPGDPRC\Includes\Extensions;


class WP {
    private static $instance;

    private function __construct()  {}

    private function __clone() {}

    const ID = 'wordpress';

    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function addField($field) {
        $field .= "
            <label class=\"checkbox \"><br>
                <input class=\"input-checkbox \" name=\"gdpr_accept\" id=\"gdpr_accept\" value=\"1\" type=\"checkbox\">". self::getLabelText() ."<abbr class=\"required\" title=\"required\">*</abbr>
            </label>";
        return $field;
    }

    public function checkPost($post) {
        if (!isset($_POST['gdpr_accept'])) {
            wp_die( __( 'The field is required' ) );
        }
        return $post;
    }

    public function getLabelText() {
        $default = __('By using this form you agree with the storage and handling of your data by this website.', WP_GDPR_C_SLUG);
        $option = get_option(WP_GDPR_C_PREFIX . '_integrations_' . self::ID .'_text');
        return !empty($option) ? $option : $default;
    }
}