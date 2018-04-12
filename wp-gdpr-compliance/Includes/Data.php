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
     * @param array $data
     * @param string $type
     * @return string
     */
    public static function getOutput($data = array(), $type = '') {
        $output = '';
        if (!empty($data)) {
            $output .= '<table>';
            $output .= '<thead>';
            $output .= '<tr>';
            switch ($type) {
                case 'user' :
                    $output .= sprintf('<th scope="col">%s</th>', __('ID', WP_GDPR_C_SLUG));
                    $output .= sprintf('<th scope="col">%s</th>', __('Username', WP_GDPR_C_SLUG));
                    $output .= sprintf('<th scope="col">%s</th>', __('Nice Name', WP_GDPR_C_SLUG));
                    $output .= sprintf('<th scope="col">%s</th>', __('Display Name', WP_GDPR_C_SLUG));
                    $output .= sprintf('<th scope="col">%s</th>', __('Email Address', WP_GDPR_C_SLUG));
                    $output .= sprintf('<th scope="col">%s</th>', __('Website', WP_GDPR_C_SLUG));
                    $output .= sprintf('<th scope="col">%s</th>', __('Registered on', WP_GDPR_C_SLUG));
                    break;
                case 'post' :
                    $output .= sprintf('<th scope="col">%s</th>', __('ID', WP_GDPR_C_SLUG));
                    $output .= sprintf('<th scope="col">%s</th>', __('Title', WP_GDPR_C_SLUG));
                    $output .= sprintf('<th scope="col">%s</th>', __('Type', WP_GDPR_C_SLUG));
                    $output .= sprintf('<th scope="col">%s</th>', __('Status', WP_GDPR_C_SLUG));
                    $output .= sprintf('<th scope="col">%s</th>', __('Date', WP_GDPR_C_SLUG));
                    break;
            }
            $output .= '</tr>';
            $output .= '</thead>';
            $output .= '<tbody>';
            switch ($type) {
                case 'user' :
                    /** @var User $user */
                    foreach ($data as $user) {
                        $output .= '<tr>';
                        $output .= sprintf('<td>%d</td>', $user->getId());
                        $output .= sprintf('<td>%s</td>', $user->getUsername());
                        $output .= sprintf('<td>%s</td>', $user->getNiceName());
                        $output .= sprintf('<td>%s</td>', $user->getDisplayName());
                        $output .= sprintf('<td>%s</td>', $user->getEmailAddress());
                        $output .= sprintf('<td>%s</td>', $user->getWebsite());
                        $output .= sprintf('<td>%s</td>', $user->getRegisteredDate());
                        $output .= '</tr>';
                    }
                    break;
                case 'post' :
                    /** @var Post $post */
                    foreach ($data as $post) {
                        $output .= '<tr>';
                        $output .= sprintf('<td>%d</td>', $post->getId());
                        $output .= sprintf('<td>%s</td>', $post->getTitle());
                        $output .= sprintf('<td>%s</td>', $post->getType());
                        $output .= sprintf('<td>%s</td>', $post->getStatus());
                        $output .= sprintf('<td>%s</td>', $post->getDate());
                        $output .= '</tr>';
                    }
                    break;
            }
            $output .= '</tbody>';
            $output .= '</table>';
        }
        return $output;
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