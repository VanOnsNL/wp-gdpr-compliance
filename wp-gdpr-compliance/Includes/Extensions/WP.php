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
                <input class=\"input-checkbox \" name=\"gdpr_accept\" id=\"gdpr_accept\" value=\"1\" type=\"checkbox\"> I accept that we can use your information <abbr class=\"required\" title=\"required\">*</abbr>
            </label>";
        return $field;
    }

    public function checkPost($post) {
        if (!isset($_POST['gdpr_accept'])) {
            wp_die( __( 'You have to agree to giving us your information' ) );
        }
        return $post;
    }
}