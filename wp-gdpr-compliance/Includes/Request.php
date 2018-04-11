<?php

namespace WPGDPRC\Includes;

/**
 * Class Requests
 * @package WPGDPRC\Includes
 */
class Request {
    /** @var int */
    private $id = 0;
    /** @var int */
    private $siteId = 0;
    /** @var string */
    private $sessionId = '';
    /** @var string */
    private $ipAddress = '';
    /** @var string */
    private $dateCreated = '';

    /**
     * @param $row
     */
    private function loadByRow($row) {
        $this->setId($row->ID);
        $this->setSiteId($row->site_id);
        $this->setSessionId($row->session_id);
        $this->setIpAddress($row->ip_address);
        $this->setDateCreated($row->date_created);
    }

    public function load() {
        global $wpdb;
        $query = "SELECT * FROM `" . self::getDatabaseTableName() . "` WHERE `ID` = '%d'";
        $row = $wpdb->get_row($wpdb->prepare($query, $this->getId()));
        if ($row !== null) {
            $this->loadByRow($row);
        }
    }

    /**
     * @param int $id
     * @return bool
     */
    public function exists($id = 0) {
        global $wpdb;
        $query = "SELECT * FROM `" . self::getDatabaseTableName() . "` WHERE `ID` = '%d'";
        $row = $wpdb->get_row($wpdb->prepare($query, $id));
        return ($row !== null);
    }

    /**
     * @return bool|int
     */
    public function save() {
        global $wpdb;
        $result = $wpdb->insert(
            self::getDatabaseTableName(),
            array(
                'site_id' => $this->getSiteId(),
                'session_id' => $this->getSessionId(),
                'ip_address' => $this->getIpAddress(),
                'date_created' => date_i18n('Y-m-d H:i:s'),
            ),
            array('%d', '%s', '%s', '%s')
        );
        if ($result !== false) {
            $this->setId($wpdb->insert_id);
            return $this->getId();
        }
        return false;
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
     * @return int
     */
    public function getSiteId() {
        return $this->siteId;
    }

    /**
     * @param int $siteId
     */
    public function setSiteId($siteId) {
        $this->siteId = $siteId;
    }

    /**
     * @return string
     */
    public function getSessionId() {
        return $this->sessionId;
    }

    /**
     * @param string $sessionId
     */
    public function setSessionId($sessionId) {
        $this->sessionId = $sessionId;
    }

    /**
     * @return string
     */
    public function getIpAddress() {
        return $this->ipAddress;
    }

    /**
     * @param string $ipAddress
     */
    public function setIpAddress($ipAddress) {
        $this->ipAddress = $ipAddress;
    }

    /**
     * @return string
     */
    public function getDateCreated() {
        return $this->dateCreated;
    }

    /**
     * @param string $dateCreated
     */
    public function setDateCreated($dateCreated) {
        $this->dateCreated = $dateCreated;
    }

    /**
     * @return string
     */
    public static function getDatabaseTableName() {
        global $wpdb;
        return $wpdb->base_prefix . 'wpgpdrc_requests';
    }
}