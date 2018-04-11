<?php

namespace WPGDPRC\Includes;

/**
 * Class Shortcodes
 * @package WPGDPRC\Includes
 */
class Shortcodes {
    /** @var null */
    private static $instance = null;

    /**
     * @param array $attributes
     * @return string
     */
    public function requestForm($attributes = array()) {
        $output = '';
        $attributes = shortcode_atts(
            array(),
            $attributes,
            'wpgdprc_request_form'
        );
        $output  = '<form id="wpgdprc-form" name="wpgdprc_form" method="POST">';
        $output .= '<p>It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout.</p>';
        $output .= apply_filters('wpgpdprc_request_form_email_field', '<p><input type="email" name="wpgpdprc_email" id="wpgpdrc-form__email" placeholder="' . esc_attr__(apply_filters('wpgpdprc_request_form_email_placeholder', __('Your Email Address', WP_GDPR_C_SLUG))) . '" required value="donny@van-ons.nl" /></p>');
        $output .= apply_filters('wpgpdprc_request_form_submit_field', '<p><input type="submit" name="wpgdprc_submit" value="' . esc_attr__(apply_filters('wpgpdprc_request_form_submit_label', __('Send', WP_GDPR_C_SLUG))) . '" /></p>');
        $output .= '</form>';
        return apply_filters('wpgpdprc_request_form', $output);
    }

    /**
     * @return null|Shortcodes
     */
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}