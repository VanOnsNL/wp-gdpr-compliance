<?php

namespace WPGDPRC\Includes;

/**
 * Class Shortcode
 * @package WPGDPRC\Includes
 */
class Shortcode {
    /** @var null */
    private static $instance = null;

    /**
     * @return string
     */
    private static function getAccessRequestData() {
        $output = '';
        $request = (isset($_REQUEST['wpgdprc'])) ? unserialize(base64_decode(esc_html($_REQUEST['wpgdprc']))) : false;
        $request = (!empty($request)) ? AccessRequest::getInstance()->getByEmailAddressAndSessionId($request['email'], $request['sId']) : false;
        if ($request !== false) {
            if (
                SessionHelper::checkSession($request->getSessionId()) &&
                Helper::checkIpAddress($request->getIpAddress())
            ) {
                $data = new Data($request->getEmailAddress());
                $users = Data::getOutput($data->getUsers(), 'user', $request->getId());
                $comments = Data::getOutput($data->getComments(), 'comment', $request->getId());
                $woocommerceOrders = Data::getOutput($data->getWooCommerceOrders(), 'woocommerce_order', $request->getId());
                $output .= sprintf(
                    '<div class="wpgdprc-feedback wpgdprc-feedback--notice">%s</div>',
                    Integration::getDeleteRequestFormExplanationText()
                );
                $output .= sprintf('<h2 class="wpgdprc-title">%s</h2>', __('Users', WP_GDPR_C_SLUG));
                if (!empty($users)) {
                    $output .= $users;
                } else {
                    $output .= sprintf(
                        '<div class="wpgdprc-feedback wpgdprc-feedback--notice">%s</div>',
                        sprintf(
                            __('No users found with email address %s.', WP_GDPR_C_SLUG),
                            sprintf('<strong>%s</strong>', $request->getEmailAddress())
                        )
                    );
                }
                $output .= sprintf('<h2 class="wpgdprc-title">%s</h2>', __('Comments', WP_GDPR_C_SLUG));
                if (!empty($comments)) {
                    $output .= $comments;
                } else {
                    $output .= sprintf(
                        '<div class="wpgdprc-feedback wpgdprc-feedback--notice">%s</div>',
                        sprintf(
                            __('No comments found with email address %s.', WP_GDPR_C_SLUG),
                            sprintf('<strong>%s</strong>', $request->getEmailAddress())
                        )
                    );
                }
                $output .= sprintf('<h2 class="wpgdprc-title">%s</h2>', __('WooCommerce Orders', WP_GDPR_C_SLUG));
                if (!empty($woocommerceOrders)) {
                    $output .= $woocommerceOrders;
                } else {
                    $output .= sprintf(
                        '<div class="wpgdprc-feedback wpgdprc-feedback--notice">%s</div>',
                        sprintf(
                            __('No WooCommerce orders found with email address %s.', WP_GDPR_C_SLUG),
                            sprintf('<strong>%s</strong>', $request->getEmailAddress())
                        )
                    );
                }
            } else {
                wp_die(
                    '<p>' . sprintf(
                        __('<strong>ERROR</strong>: %s', WP_GDPR_C_SLUG),
                        __('What are you trying to do?', WP_GDPR_C_SLUG)
                    ) . '</p>'
                );
                exit;
            }
        } else {
            $output .= __('This request is expired or doesn\'t exist.', WP_GDPR_C_SLUG);
        }
        return $output;
    }

    /**
     * @return string
     */
    public function accessRequestForm() {
        wp_enqueue_style('wpgdprc.css');
        wp_enqueue_script('wpgdprc.js');
        $output = '<div class="wpgdprc">';
        if (isset($_REQUEST['wpgdprc'])) {
            $output .= self::getAccessRequestData();
        } else {
            $output .= '<form class="wpgdprc-form wpgdprc-form--access-request" name="wpgdprc_form" method="POST">';
            $output .= apply_filters(
                'wpgdprc_request_form_email_field',
                sprintf(
                    '<p><input type="email" name="wpgdprc_email" id="wpgdprc-form__email" placeholder="%s" required /></p>',
                    esc_attr__(apply_filters('wpgdprc_request_form_email_placeholder', __('Your Email Address', WP_GDPR_C_SLUG)))
                )
            );
            $output .= apply_filters(
                'wpgdprc_request_form_consent_field',
                sprintf(
                    '<p><label><input type="checkbox" name="wpgdprc_consent" id="wpgdprc-form__consent" value="1" required /> %s</label></p>',
                    Integration::getAccessRequestFormCheckboxText()
                )
            );
            $output .= apply_filters(
                'wpgdprc_request_form_submit_field',
                sprintf(
                    '<p><input type="submit" name="wpgdprc_submit" value="%s" /></p>',
                    esc_attr__(apply_filters('wpgdprc_request_form_submit_label', __('Send', WP_GDPR_C_SLUG)))
                )
            );
            $output .= '<div class="wpgdprc-feedback" style="display: none;"></div>';
            $output .= '</form>';
        }
        $output .= '</div>';
        return apply_filters('wpgdprc_request_form', $output);
    }

    /**
     * @return null|Shortcode
     */
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}