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

    /**
     * @param \stdClass $row
     */
    public function loadByRow(\stdClass $row) {
        $this->setId($row->ID);
        $this->setTitle($row->post_title);
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
}