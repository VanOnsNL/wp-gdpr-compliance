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
        wp_enqueue_style('wpgdprc.css');
        wp_enqueue_script('wpgdprc.js');
        $output = '<div class="wpgdprc">';
        if (isset($_REQUEST['wpgdprc'])) {
            $request = unserialize(base64_decode($_REQUEST['wpgdprc']));
            $request = Request::getInstance()->getByEmailAddressAndSessionId($request['email'], $request['sId']);
            if ($request !== false) {
                if (
                    SessionHelper::checkSession($request->getSessionId()) &&
                    Helpers::checkIpAddress($request->getIpAddress())
                ) {
                    $data = new Data($request->getEmailAddress());
                    $output .= '<h2>Users</h2>';
                    $output .= Data::getOutput($data->getUsers(), 'user');
                    $output .= '<h2>Posts</h2>';
                    $output .= Data::getOutput($data->getPosts(), 'post');
                    $output .= '<h2>Comments</h2>';
                    $output .= Data::getOutput($data->getComments(), 'comment');
                } else {
                    wp_die(
                        '<p>' . sprintf(
                            __('<strong>ERROR</strong>: %s', WP_GDPR_C_SLUG),
                            __('What are you trying to do?', WP_GDPR_C_SLUG)
                        ) . '</p>'
                    );
                    exit;
                }
            }
        } else {
            $attributes = shortcode_atts(
                array(),
                $attributes,
                'wpgdprc_request_form'
            );
            $output .= '<div class="wpgdprc-feedback" style="display: none;"></div>';
            $output .= '<form id="wpgdprc-form" name="wpgdprc_form" method="POST">';
            $output .= '<p>It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout.</p>';
            $output .= apply_filters('wpgdprc_request_form_email_field', '<p><input type="email" name="wpgdprc_email" id="wpgdprc-form__email" placeholder="' . esc_attr__(apply_filters('wpgdprc_request_form_email_placeholder', __('Your Email Address', WP_GDPR_C_SLUG))) . '" required /></p>');
            $output .= apply_filters(
                'wpgdprc_request_form_consent_field',
                sprintf(
                    '<p><input type="checkbox" name="wpgdprc_consent" id="wpgdprc-form__consent" value="1" required /> %s</p>',
                    __('Blabla ik ga akkoord en shit.', WP_GDPR_C_SLUG)
                )
            );
            $output .= apply_filters('wpgdprc_request_form_submit_field', '<p><input type="submit" name="wpgdprc_submit" value="' . esc_attr__(apply_filters('wpgdprc_request_form_submit_label', __('Send', WP_GDPR_C_SLUG))) . '" /></p>');
            $output .= '</form>';
        }
        $output .= '</div>';
        return apply_filters('wpgdprc_request_form', $output);
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