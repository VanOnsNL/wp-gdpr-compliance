<?php

namespace WPGDPRC\Includes\Data;

/**
 * Class WooCommerce
 * @package WPGDPRC\Includes\Data
 */
class WooCommerce {
    /** @var null */
    private static $instance = null;
    /** @var int */
    protected $id = 0;
    /** @var string */
    protected $billingEmailAddress = '';
    /** @var string */
    protected $billingFirstName = '';
    /** @var string */
    protected $billingLastName = '';
    /** @var string */
    protected $billingCompany = '';
    /** @var string */
    protected $billingAddressOne = '';
    /** @var string */
    protected $billingAddressTwo = '';
    /** @var string */
    protected $billingCity = '';
    /** @var string */
    protected $billingState = '';
    /** @var string */
    protected $billingPostCode = '';
    /** @var string */
    protected $billingCountry = '';
    /** @var string */
    protected $billingPhone = '';

    /**
     * User constructor.
     * @param int $id
     */
    public function __construct($id = 0) {
        if ((int)$id > 0) {
            $this->setId($id);
            $this->load();
        }
    }

    public function load() {
        global $wpdb;
        $query = "SELECT * FROM `" . $wpdb->users . "` WHERE `ID` = '%d'";
        $row = $wpdb->get_row($wpdb->prepare($query, $this->getId()));
        if ($row !== null) {
            $this->loadByRow($row);
        }
    }

    /**
     * @param \stdClass $row
     */
    public function loadByRow(\stdClass $row) {
        $this->setId($row->ID);
        $this->setBillingEmailAddress(get_post_meta($row->post_id, '_billing_email', true));
        $this->setBillingFirstName(get_post_meta($row->post_id, '_billing_first_name', true));
        $this->setBillingLastName(get_post_meta($row->post_id, '_billing_last_name', true));
        $this->setBillingCompany(get_post_meta($row->post_id, '_billing_company', true));
        $this->setBillingAddressOne(get_post_meta($row->post_id, '_billing_address_1', true));
        $this->setBillingAddressTwo(get_post_meta($row->post_id, '_billing_address_2', true));
        $this->setBillingCity(get_post_meta($row->post_id, '_billing_city', true));
        $this->setBillingState(get_post_meta($row->post_id, '_billing_state', true));
        $this->setBillingPostCode(get_post_meta($row->post_id, '_billing_postcode', true));
        $this->setBillingCountry(get_post_meta($row->post_id, '_billing_country', true));
        $this->setBillingPhone(get_post_meta($row->post_id, '_billing_phone', true));
    }

    /**
     * @return null|WooCommerce
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
    public function getBillingEmailAddress() {
        return $this->billingEmailAddress;
    }

    /**
     * @param string $billingEmailAddress
     */
    public function setBillingEmailAddress($billingEmailAddress) {
        $this->billingEmailAddress = $billingEmailAddress;
    }

    /**
     * @return string
     */
    public function getBillingFirstName() {
        return $this->billingFirstName;
    }

    /**
     * @param string $billingFirstName
     */
    public function setBillingFirstName($billingFirstName) {
        $this->billingFirstName = $billingFirstName;
    }

    /**
     * @return string
     */
    public function getBillingLastName() {
        return $this->billingLastName;
    }

    /**
     * @param string $billingLastName
     */
    public function setBillingLastName($billingLastName) {
        $this->billingLastName = $billingLastName;
    }

    /**
     * @return string
     */
    public function getBillingCompany() {
        return $this->billingCompany;
    }

    /**
     * @param string $billingCompany
     */
    public function setBillingCompany($billingCompany) {
        $this->billingCompany = $billingCompany;
    }

    /**
     * @return string
     */
    public function getBillingAddressOne() {
        return $this->billingAddressOne;
    }

    /**
     * @param string $billingAddressOne
     */
    public function setBillingAddressOne($billingAddressOne) {
        $this->billingAddressOne = $billingAddressOne;
    }

    /**
     * @return string
     */
    public function getBillingAddressTwo() {
        return $this->billingAddressTwo;
    }

    /**
     * @param string $billingAddressTwo
     */
    public function setBillingAddressTwo($billingAddressTwo) {
        $this->billingAddressTwo = $billingAddressTwo;
    }

    /**
     * @return string
     */
    public function getBillingCity() {
        return $this->billingCity;
    }

    /**
     * @param string $billingCity
     */
    public function setBillingCity($billingCity) {
        $this->billingCity = $billingCity;
    }

    /**
     * @return string
     */
    public function getBillingState() {
        return $this->billingState;
    }

    /**
     * @param string $billingState
     */
    public function setBillingState($billingState) {
        $this->billingState = $billingState;
    }

    /**
     * @return string
     */
    public function getBillingPostCode() {
        return $this->billingPostCode;
    }

    /**
     * @param string $billingPostCode
     */
    public function setBillingPostCode($billingPostCode) {
        $this->billingPostCode = $billingPostCode;
    }

    /**
     * @return string
     */
    public function getBillingCountry() {
        return $this->billingCountry;
    }

    /**
     * @param string $billingCountry
     */
    public function setBillingCountry($billingCountry) {
        $this->billingCountry = $billingCountry;
    }

    /**
     * @return string
     */
    public function getBillingPhone() {
        return $this->billingPhone;
    }

    /**
     * @param string $billingPhone
     */
    public function setBillingPhone($billingPhone) {
        $this->billingPhone = $billingPhone;
    }
}