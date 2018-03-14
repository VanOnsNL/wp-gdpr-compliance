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
     * @param string $submitField
     * @return string
     */
    public function addField($submitField = '') {
        $field = apply_filters('wpgdprc_wordpress_field', '<p class="wpgdprc-checkbox"><label><input type="checkbox" name="wpgdprc" id="wpgdprc" value="1" />' . Integrations::getCheckboxText(self::ID) . ' <abbr class="required" title="required">*</abbr></label></p>', $submitField);
        return $field . $submitField;
    }

    public function checkPost() {
        if (!isset($_POST['wpgdprc'])) {
            wp_die(
                '<p>' . sprintf(__('<strong>ERROR</strong>: %s', WP_GDPR_C_SLUG), Integrations::getErrorMessage(self::ID)) . '</p>',
                __('Comment Submission Failure'),
                array('back_link' => true)
            );
        }
    }

    public function updateMeta( $comment_id ) {
        if (isset($_POST['wpgdprc']) && $comment_id != 0) {
            add_comment_meta($comment_id, '_gdpr-c', date('Y-m-d H:i:s', time()));
        }
    }
}