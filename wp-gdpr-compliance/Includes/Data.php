<?php

namespace WPGDPRC\Includes;

use WPGDPRC\Includes\Data\Comment;
use WPGDPRC\Includes\Data\Post;
use WPGDPRC\Includes\Data\User;

/**
 * Class Data
 * @package WPGDPRC\Includes
 */
class Data {
    /** @var null */
    private static $instance = null;
    /** @var string */
    protected $emailAddress = '';

    /**
     * @return Comment[]
     */
    public function getComments() {
        global $wpdb;
        $output = array();
        $query = "SELECT * FROM " . $wpdb->comments . " WHERE `comment_author_email` = '%s'";
        $results = $wpdb->get_results($wpdb->prepare($query, $this->getEmailAddress()));
        if ($results !== null) {
            foreach ($results as $row) {
                $object = new Comment();
                $object->loadByRow($row);
                $output[] = $object;
            }
        }
        return $output;
    }

    /**
     * @return User[]
     */
    public function getUsers() {
        global $wpdb;
        $output = array();
        $query = "SELECT * FROM `" . $wpdb->users . "` WHERE `user_email` = '%s'";
        $results = $wpdb->get_results($wpdb->prepare($query, $this->getEmailAddress()));
        if ($results !== null) {
            foreach ($results as $row) {
                $object = new User();
                $object->loadByRow($row);
                $output[] = $object;
            }
        }
        return $output;
    }

    /**
     * @return Post[]
     */
    public function getPosts() {
        global $wpdb;
        $output = array();
        $query  = "SELECT `" . $wpdb->posts . "`.* FROM `" . $wpdb->posts . "`";
        $query .= " INNER JOIN `" . $wpdb->users . "` ON `" . $wpdb->users . "`.ID = `" . $wpdb->posts . "`.post_author";
        $query .= " WHERE `" . $wpdb->users . "`.user_email = '%s'";
        $results = $wpdb->get_results($wpdb->prepare($query, $this->getEmailAddress()));
        if ($results !== null) {
            foreach ($results as $row) {
                $object = new Post();
                $object->loadByRow($row);
                $output[] = $object;
            }
        }
        return $output;
    }

    // TODO
    public function getBySupportedIntegrations() {
        $output = array();
        $supportedIntegrations = Integrations::getSupportedIntegrations();
        foreach ($supportedIntegrations as $supportedIntegration) {
            switch ($supportedIntegration['id']) {
                case 'woocommerce' :
                    var_dump($supportedIntegration);
                    break;
            }
        }
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
     * @return null|Data
     */
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}