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
            'message' => '',
            'error' => '',
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
                case 'access_request' :
                    if (Helpers::isEnabled('enable_access_request', 'settings')) {
                        $emailAddress = (isset($data['email']) && is_email($data['email'])) ? $data['email'] : false;
                        $consent = (isset($data['consent'])) ? filter_var($data['consent'], FILTER_VALIDATE_BOOLEAN) : false;

                        if (!$emailAddress) {
                            $output['error'] = __('Missing or incorrect email address.', WP_GDPR_C_SLUG);
                        }

                        if (!$consent) {
                            $output['error'] = __('You need to accept the privacy checkbox.', WP_GDPR_C_SLUG);
                        }

                        // Let's do this!
                        if (empty($output['error'])) {
                            if (!AccessRequest::getInstance()->existsByEmailAddress($emailAddress, true)) {
                                $request = new AccessRequest();
                                $request->setSiteId(get_current_blog_id());
                                $request->setEmailAddress($emailAddress);
                                $request->setSessionId(SessionHelper::getSessionId());
                                $request->setIpAddress(Helpers::getClientIpAddress());
                                $request->setActive(1);
                                $id = $request->save();
                                if ($id !== false) {
                                    $page = Helpers::getAccessRequestPage();
                                    $siteName = get_blog_option($request->getSiteId(), 'blogname');
                                    $siteEmail = get_blog_option($request->getSiteId(), 'admin_email');
                                    $subject = __('[WPGDPRC] Your request', WP_GDPR_C_SLUG);
                                    $message = '';
                                    if (!empty($page)) {
                                        $message = sprintf(
                                            '<a target="_blank" href="%s">%s</a>',
                                            add_query_arg(
                                                array(
                                                    'wpgdprc' => base64_encode(serialize(array(
                                                        'email' => $request->getEmailAddress(),
                                                        'sId' => $request->getSessionId()
                                                    )))
                                                ),
                                                get_permalink($page)
                                            ),
                                            __('Let\'s go!', WP_GDPR_C_SLUG)
                                        );
                                    }
                                    $headers = array(
                                        'Content-Type: text/html; charset=UTF-8',
                                        "From: $siteName <$siteEmail>"
                                    );
                                    $response = wp_mail($emailAddress, $subject, $message, $headers);
                                    if ($response !== false) {
                                        $output['message'] = __('Wooooo!', WP_GDPR_C_SLUG);
                                    }
                                } else {
                                    $output['error'] = __('Something went wrong while saving the request.', WP_GDPR_C_SLUG);
                                }
                            } else {
                                $output['error'] = __('Already requested.', WP_GDPR_C_SLUG);
                            }
                        }
                    }
                    break;
                case 'delete_request' :
                    if (Helpers::isEnabled('enable_access_request', 'settings')) {
                        $session = (isset($data['session'])) ? esc_html($data['session']) : '';
                        $settings = (isset($data['settings']) && is_array($data['settings'])) ? $data['settings'] : array();
                        $type = (isset($settings['type']) && in_array($settings['type'], Data::getPossibleDataTypes())) ? $settings['type'] : '';
                        $value = (isset($data['value']) && is_numeric($data['value'])) ? (int)$data['value'] : 0;

                        if (empty($session)) {
                            $output['error'] = __('Missing session.', WP_GDPR_C_SLUG);
                        }

                        if (empty($type)) {
                            $output['error'] = __('Missing type.', WP_GDPR_C_SLUG);
                        }

                        if ($value === 0) {
                            $output['error'] = __('No value selected.', WP_GDPR_C_SLUG);
                        }

                        // Let's do this!
                        if (empty($output['error'])) {
                            $accessRequest = unserialize(base64_decode($session));
                            $accessRequest = (!empty($accessRequest)) ? AccessRequest::getInstance()->getByEmailAddressAndSessionId($accessRequest['email'], $accessRequest['sId']) : false;
                            if ($accessRequest !== false) {
                                if (
                                    SessionHelper::checkSession($accessRequest->getSessionId()) &&
                                    Helpers::checkIpAddress($accessRequest->getIpAddress())
                                ) {
                                    $request = new DeleteRequest();
                                    $request->setSiteId(get_current_blog_id());
                                    $request->setAccessRequestId($accessRequest->getId());
                                    $request->setSessionId($accessRequest->getSessionId());
                                    $request->setIpAddress($accessRequest->getIpAddress());
                                    $request->setDataId($value);
                                    $request->setType($type);
                                    $id = $request->save();
                                    if ($id !== false) {
                                    } else {
                                        $output['error'] = __('Something went wrong while saving the request.', WP_GDPR_C_SLUG);
                                    }
                                } else {
                                    $output['error'] = __('Session doesn\'t match.', WP_GDPR_C_SLUG);
                                }
                            } else {
                                $output['error'] = __('No session found.', WP_GDPR_C_SLUG);
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

    public function processDeleteRequest() {
        check_ajax_referer('wpgdprc', 'security');

        $output = array(
            'message' => '',
            'error' => '',
        );

        if (!Helpers::isEnabled('enable_access_request', 'settings')) {
            $output['error'] = __('The access request functionality is not enabled.', WP_GDPR_C_SLUG);
        }

        if (!current_user_can('manage_posts')) {
            $output['error'] = __('You\'re not allowed to do this.', WP_GDPR_C_SLUG);
        }

        $data = (isset($_POST['data']) && (is_array($_POST['data']) || is_string($_POST['data']))) ? $_POST['data'] : false;
        if (is_string($data)) {
            $data = json_decode(stripslashes($data), true);
        }
        $type = (isset($data['type']) && is_string($data['type'])) ? esc_html($data['type']) : false;
        $value = (isset($data['value']) && is_numeric($data['value'])) ? absint($data['value']) : 0;

        if (!$data) {
            $output['error'] = __('Missing data.', WP_GDPR_C_SLUG);
        }

        if (!$type) {
            $output['error'] = __('Missing type.', WP_GDPR_C_SLUG);
        }

        if ($value === 0) {
            $output['error'] = __('Missing value.', WP_GDPR_C_SLUG);
        }

        // Let's do this!
        if (empty($output['error'])) {
            switch ($type) {
                case 'post' :

                    break;
                case 'user' :
                    $result = wp_update_user(array(
                        'ID' => $value,
                        'user_email' => 'test@test.nl'
                    ));
                    if (is_wp_error($result)) {
                        $output['error'] = __('This user does not exist.', WP_GDPR_C_SLUG);
                    }
                    break;
                case 'comment' :

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