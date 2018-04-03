<?php

namespace WPGDPRC\Includes\Data;

/**
 * Class Post
 * @package WPGDPRC\Includes\Data
 */
class Post {
    /** @var null */
    private static $instance = null;
    /** @var int */
    protected $id = 0;
    /** @var string */
    protected $title = '';
    /** @var string */
    protected $type = '';
    /** @var string */
    protected $status = '';
    /** @var string */
    protected $date = '';

    /**
     * @param \stdClass $row
     */
    public function loadByRow(\stdClass $row) {
        $this->setId($row->ID);
        $this->setTitle($row->post_title);
        $this->setType($row->post_type);
        $this->setStatus($row->post_status);
        $this->setDate($row->post_date);
    }

    /**
     * @return null|Post
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
    public function getTitle() {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title) {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getType() {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type) {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus($status) {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getDate() {
        return $this->date;
    }

    /**
     * @param string $date
     */
    public function setDate($date) {
        $this->date = $date;
    }
}