<?php

namespace WPGDPRC\Includes\Extensions;

use WPGDPRC\Includes\Integrations;

/**
 * Class WP
 * @package WPGDPRC\Includes\Extensions
 */
class WP {
    const ID = 'wordpress';
    /** @var null */
    private static $instance = null;

    /**
     * @return null|WP
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
        $field .= apply_filters('wpgdprc_wordpress_field', '<p class="wpgdprc-checkbox"><label><input type="checkbox" name="wpgdprc" id="wpgdprc" value="1" />' . Integrations::getCheckboxText(self::ID) . ' <abbr class="required" title="required">*</abbr></label></p>', $field);
        return $field;
    }

    /**
     * @param array $post
     * @return array
     */
    public function checkPost($post = array()) {
        if (!isset($_POST['wpgdprc'])) {
            wp_die(
                '<p>' . sprintf(__('<strong>ERROR</strong>: %s', WP_GDPR_C_SLUG), Integrations::getErrorMessage(self::ID)) . '</p>',
                __('Comment Submission Failure'),
                array(
                    'response' => $post,
                    'back_link' => true
                )
            );
        }
        return $post;
    }
}