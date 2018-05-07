<?php

namespace WPGDPRC\Includes;

/**
 * Class Filter
 * @package WPGDPRC\Includes
 */
class Filter {
    /** @var null */
    private static $instance = null;

    public function processEnableAccessRequest($value) {
        $enabled = Helper::isEnabled('enable_access_request', 'settings');
        if (empty($value) && $enabled) {
            $page = Helper::getAccessRequestPage();
            if (!empty($page)) {
                $value = $page->ID;
            }
        }
        return $value;
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