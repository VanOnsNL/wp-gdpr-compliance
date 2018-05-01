<?php

namespace WPGDPRC\Includes;

/**
 * Class Cron
 * @package WPGDPRC\Includes
 */
class Cron {
    /** @var null */
    private static $instance = null;

    /**
     * Deactivate requests after 24 hours
     */
    public function deactivateAccessRequests() {
        $date = Helpers::localDateTime(time());
        $date->modify('-24 hours');
        $requests = AccessRequest::getInstance()->getList(array(
            'active' => array(
                'value' => 1
            ),
            'date_created' => array(
                'value' => $date->format('Y-m-d H:i:s'),
                'compare' => '<='
            ),
        ));
        if (!empty($requests)) {
            foreach ($requests as $request) {
                $request->setActive(0);
                $request->save();
            }
        }
    }

    /**
     * @return null|Cron
     */
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}