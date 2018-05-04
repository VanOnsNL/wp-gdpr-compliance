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
                    if (Helper::isEnabled('enable_access_request', 'settings')) {
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
                                $request->setIpAddress(Helper::getClientIpAddress());
                                $request->setExpired(0);
                                $id = $request->save();
                                if ($id !== false) {
                                    $page = Helper::getAccessRequestPage();
                                    if (is_multisite()) {
                                        $siteName = get_blog_option($request->getSiteId(), 'blogname');
                                        $siteEmail = get_blog_option($request->getSiteId(), 'admin_email');
                                    } else {
                                        $siteName = get_option('blogname');
                                        $siteEmail = get_option('admin_email');
                                    }
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
                                            // TODO: Better message.
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
                    if (Helper::isEnabled('enable_access_request', 'settings')) {
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
                                    Helper::checkIpAddress($accessRequest->getIpAddress())
                                ) {
                                    $request = new DeleteRequest();
                                    $request->setSiteId(get_current_blog_id());
                                    $request->setAccessRequestId($accessRequest->getId());
                                    $request->setSessionId($accessRequest->getSessionId());
                                    $request->setIpAddress($accessRequest->getIpAddress());
                                    $request->setDataId($value);
                                    $request->setType($type);
                                    $id = $request->save();
                                    if ($id === false) {
                                        $output['error'] = __('Something went wrong while saving this request. Please try again.', WP_GDPR_C_SLUG);
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

        if (!Helper::isEnabled('enable_access_request', 'settings')) {
            $output['error'] = __('The access request functionality is not enabled.', WP_GDPR_C_SLUG);
        }

        $data = (isset($_POST['data']) && (is_array($_POST['data']) || is_string($_POST['data']))) ? $_POST['data'] : false;
        if (is_string($data)) {
            $data = json_decode(stripslashes($data), true);
        }
        $id = (isset($data['id']) && is_numeric($data['id'])) ? absint($data['id']) : 0;

        if (!$data) {
            $output['error'] = __('Missing data.', WP_GDPR_C_SLUG);
        }

        if ($id === 0 || !DeleteRequest::getInstance()->exists($id)) {
            $output['error'] = __('This delete request doesn\'t exist.', WP_GDPR_C_SLUG);
        }

        // Let's do this!
        if (empty($output['error'])) {
            $request = new DeleteRequest($id);
            if (!$request->getProcessed()) {
                switch ($request->getType()) {
                    case 'user' :
                        if (current_user_can('edit_users')) {
                            $date = Helper::localDateTime(time());
                            $result = wp_update_user(array(
                                'ID' => $request->getDataId(),
                                'display_name' => 'DISPLAY_NAME',
                                'nickname' => 'NICKNAME',
                                'first_name' => 'FIRST_NAME',
                                'last_name' => 'LAST_NAME',
                                'user_email' => $request->getDataId() . '.' . $date->format('Ymd') . '.' . $date->format('His') . '@example.org'
                            ));
                            if (is_wp_error($result)) {
                                $output['error'] = __('This user doesn\'t exist.', WP_GDPR_C_SLUG);
                            } else {
                                $request->setProcessed(1);
                                $request->save();
                            }
                        } else {
                            $output['error'] = __('You\'re not allowed to edit users.', WP_GDPR_C_SLUG);
                        }
                        break;
                    case 'comment' :
                        if (current_user_can('edit_posts')) {
                            $date = Helper::localDateTime(time());
                            $result = wp_update_comment(array(
                                'comment_ID' => $request->getDataId(),
                                'comment_author' => 'NAME',
                                'comment_author_email' => $request->getDataId() . '.' . $date->format('Ymd') . '.' . $date->format('His') . '@example.org',
                                'comment_author_IP' => '127.0.0.1'
                            ));
                            if ($result === 0) {
                                $output['error'] = __('This comment doesn\'t exist.', WP_GDPR_C_SLUG);
                            } else {
                                $request->setProcessed(1);
                                $request->save();
                            }
                        } else {
                            $output['error'] = __('You\'re not allowed to edit comments.', WP_GDPR_C_SLUG);
                        }
                        break;
                }
            } else {
                $output['error'] = __('This delete request has already been processed.', WP_GDPR_C_SLUG);
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