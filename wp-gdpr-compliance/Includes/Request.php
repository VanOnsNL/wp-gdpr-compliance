<?php

namespace WPGDPRC\Includes;

/**
 * Class Requests
 * @package WPGDPRC\Includes
 */
class Request {
    /** @var null */
    private static $instance = null;
    /** @var int */
    private $id = 0;
    /** @var int */
    private $siteId = 0;
    /** @var string */
    private $emailAddress = '';
    /** @var string */
    private $sessionId = '';
    /** @var string */
    private $ipAddress = '';
    /** @var int */
    private $active = 0;
    /** @var string */
    private $dateCreated = '';

    /**
     * Request constructor.
     * @param int $id
     */
    public function __construct($id = 0) {
        if ((int)$id > 0) {
            $this->setId($id);
            $this->load();
        }
    }

    /**
     * @param string $emailAddress
     * @param string $sessionId
     * @return bool|Request
     */
    public function getByEmailAddressAndSessionId($emailAddress = '', $sessionId = '') {
        global $wpdb;
        $query = "SELECT * FROM `" . self::getDatabaseTableName() . "`
        WHERE `email_address` = '%s'
        AND `session_id` = '%s'
        AND `active` = '1'";
        $row = $wpdb->get_row($wpdb->prepare($query, $emailAddress, $sessionId));
        if ($row !== null) {
            return new self($row->ID);
        }
        return false;
    }

    public function getList() {
        global $wpdb;
        $output = array();
        $query  = "SELECT * FROM `" . self::getDatabaseTableName() . "`";
        $results = $wpdb->get_results($query);
        if ($results !== null) {
            foreach ($results as $row) {
                $object = new self;
                $object->loadByRow($row);
                $output[] = $object;
            }
        }
        return $output;
    }

    /**
     * @param $row
     */
    private function loadByRow($row) {
        $this->setId($row->ID);
        $this->setSiteId($row->site_id);
        $this->setEmailAddress($row->email_address);
        $this->setSessionId($row->session_id);
        $this->setIpAddress($row->ip_address);
        $this->setActive($row->active);
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
     * @param string $emailAddress
     * @return bool
     */
    public function exists($emailAddress = '') {
        global $wpdb;
        $query = "SELECT * FROM `" . self::getDatabaseTableName() . "` WHERE `email_address` = '%s'";
        $row = $wpdb->get_row($wpdb->prepare($query, $emailAddress));
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
                'email_address' => $this->getEmailAddress(),
                'session_id' => $this->getSessionId(),
                'ip_address' => $this->getIpAddress(),
                'active' => $this->getActive(),
                'date_created' => date_i18n('Y-m-d H:i:s'),
            ),
            array('%d', '%s', '%s', '%d', '%s')
        );
        if ($result !== false) {
            $this->setId($wpdb->insert_id);
            return $this->getId();
        }
        return false;
    }

    /**
     * @return null|Request
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
    public function getEmailAddress() {
        return $this->emailAddress;
    }

    /**
     * @param string $emailAddress
     */
    public function setEmailAddress($emailAddress) {
        $this->emailAddress = $emailAddress;
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
     * @return int
     */
    public function getActive() {
        return $this->active;
    }

    /**
     * @param int $active
     */
    public function setActive($active) {
        $this->active = $active;
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