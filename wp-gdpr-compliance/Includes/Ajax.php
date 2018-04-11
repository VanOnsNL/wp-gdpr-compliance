<?php

namespace WPGDPRC\Includes;

/**
 * Class Ajax
 * @package WPGDPRC\Includes
 */
class Ajax {
    /** @var null */
    private static $instance = null;

    public function processAction() {
        check_ajax_referer('wpgdprc', 'security');

        $output = array(
            'error' => '',
            'redirect' => false
        );
        $data = (isset($_POST['data']) && (is_array($_POST['data']) || is_string($_POST['data']))) ? $_POST['data'] : false;
        if (is_string($data)) {
            $data = json_decode(stripslashes($data), true);
        }
        $type = (isset($data['type']) && is_string($data['type'])) ? esc_html($data['type']) : false;

        if (!$data) {
            $output['error'] = __('Missing data.', WP_GDPR_C_SLUG);
        }

        if (!$type) {
            $output['error'] = __('Missing type.', WP_GDPR_C_SLUG);
        }

        if (empty($output['error'])) {
            switch ($type) {
                case 'save_setting' :
                    $option = (isset($data['option']) && is_string($data['option'])) ? esc_html($data['option']) : false;
                    $value = (isset($data['value'])) ? self::sanitizeValue($data['value']) : false;
                    $enabled = (isset($data['enabled'])) ? filter_var($data['enabled'], FILTER_VALIDATE_BOOLEAN) : false;
                    $append = (isset($data['append'])) ? filter_var($data['append'], FILTER_VALIDATE_BOOLEAN) : false;

                    if (!$option) {
                        $output['error'] = __('Missing option name.', WP_GDPR_C_SLUG);
                    }

                    if (!isset($data['value'])) {
                        $output['error'] = __('Missing value.', WP_GDPR_C_SLUG);
                    }

                    // Let's do this!
                    if (empty($output['error'])) {
                        if ($append) {
                            $values = (array)get_option($option, array());
                            if ($enabled) {
                                if (!in_array($value, $values)) {
                                    $values[] = $value;
                                }
                            } else {
                                $index = array_search($value, $values);
                                if ($index !== false) {
                                    unset($values[$index]);
                                }
                            }
                            $value = $values;
                        } else {
                            if (isset($data['enabled'])) {
                                $value = $enabled;
                            }
                        }
                        update_option($option, $value);
                        do_action($option, $value);
                    }
                    break;
                case 'request_data' :
                    $emailAddress = (isset($data['email']) && is_email($data['email'])) ? $data['email'] : false;

                    if (!$emailAddress) {
                        $output['error'] = __('Missing or incorrect email address.', WP_GDPR_C_SLUG);
                    }

                    // Let's do this!
                    if (empty($output['error'])) {
                        $request = new Request();
                        $request->setSiteId(get_current_blog_id());
                        $request->setSessionId(SessionHelper::getSessionId());
                        $request->setIpAddress(Helpers::getUserIpAddress());
                        $id = $request->save();
                        if ($id !== false) {
                            $siteName = get_blog_option($request->getSiteId(), 'blogname');
                            $siteEmail = get_blog_option($request->getSiteId(), 'admin_email');
                            $subject = __('[WPGDPRC] Your request', WP_GDPR_C_SLUG);
                            $message = sprintf(
                                '<a target="_blank" href="%s">%s</a>',
                                add_query_arg(
                                    array(
                                        'sId' => $request->getSessionId()
                                    ),
                                    get_site_url($request->getSiteId())
                                ),
                                __('Let\'s go!', WP_GDPR_C_SLUG)
                            );
                            $headers = array(
                                'Content-Type: text/html; charset=UTF-8',
                                "From: $siteName <$siteEmail>"
                            );
                            $response = wp_mail($emailAddress, $subject, $message, $headers);
                            if ($response !== false) {
                            }
                        }
                    }

                    break;
            }
        }

        header('Content-type: application/json');
        echo json_encode($output);
        die();
    }

    /**
     * @param $value
     * @return mixed
     */
    private static function sanitizeValue($value) {
        if (is_numeric($value)) {
            $value = intval($value);
        }
        if (is_string($value)) {
            $value = esc_html($value);
        }
        return $value;
    }

    /**
     * @return null|Ajax
     */
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}