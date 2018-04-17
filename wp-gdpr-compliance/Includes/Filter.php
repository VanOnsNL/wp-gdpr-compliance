<?php

namespace WPGDPRC\Includes;

/**
 * Class Filter
 * @package WPGDPRC\Includes
 */
class Filter {
    /** @var null */
    private static $instance = null;

    /**
     * @param string $template
     * @return string
     */
    public function showUserData($template = '') {
        if (isset($_REQUEST['wpgdprc'])) {
            $bla = unserialize(base64_decode($_REQUEST['wpgdprc']));
            $request = Request::getInstance()->getByEmailAddressAndSessionId($bla['email'], $bla['sId']);
            if ($request !== false) {
                if (
                    SessionHelper::checkSession($request->getSessionId()) &&
                    Helpers::checkIpAddress($request->getIpAddress())
                ) {
                    $data = new Data($request->getEmailAddress());
                    echo '<h2>Users</h2>';
                    echo Data::getOutput($data->getUsers(), 'user');
                    echo '<h2>Posts</h2>';
                    echo Data::getOutput($data->getPosts(), 'post');
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
            return '';
        }
        return $template;
    }

    /**
     * @return null|Filter
     */
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}