<?php

namespace WPGDPRC\Includes;

class DeleteRequest {
    /** @var null */
    private static $instance = null;
    /** @var int */
    private $id = 0;

    /**
     * @return null|DeleteRequest
     */
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public static function getDatabaseTableName() {
        global $wpdb;
        return $wpdb->base_prefix . 'wpgpdrc_delete_requests';
    }
}