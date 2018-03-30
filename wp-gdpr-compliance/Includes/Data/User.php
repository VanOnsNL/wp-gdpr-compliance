<?php

namespace WPGDPRC\Includes\Data;

/**
 * Class User
 * @package WPGDPRC\Includes\Data
 */
class User {
    /** @var null */
    private static $instance = null;
    /** @var int */
    protected $id = 0;
    /** @var string */
    protected $username = '';
    /** @var string */
    protected $niceName = '';
    /** @var string */
    protected $displayName = '';
    /** @var string */
    protected $emailAddress = '';
    /** @var string */
    protected $website = '';
    /** @var string */
    protected $registeredDate = '';

    /**
     * @param \stdClass $row
     */
    public function loadByRow(\stdClass $row) {
        $this->setId($row->ID);
        $this->setUsername($row->user_login);
        $this->setNiceName($row->user_nicename);
        $this->setDisplayName($row->display_name);
        $this->setEmailAddress($row->user_email);
        $this->setWebsite($row->user_url);
        $this->setRegisteredDate($row->user_registered);
    }

    /**
     * @return null|User
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
    public function getUsername() {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername($username) {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getNiceName() {
        return $this->niceName;
    }

    /**
     * @param string $niceName
     */
    public function setNiceName($niceName) {
        $this->niceName = $niceName;
    }

    /**
     * @return string
     */
    public function getDisplayName() {
        return $this->displayName;
    }

    /**
     * @param string $displayName
     */
    public function setDisplayName($displayName) {
        $this->displayName = $displayName;
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
    public function getWebsite() {
        return $this->website;
    }

    /**
     * @param string $website
     */
    public function setWebsite($website) {
        $this->website = $website;
    }

    /**
     * @return string
     */
    public function getRegisteredDate() {
        return $this->registeredDate;
    }

    /**
     * @param string $registeredDate
     */
    public function setRegisteredDate($registeredDate) {
        $this->registeredDate = $registeredDate;
    }
}