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
     * Data constructor.
     * @param string $emailAddress
     */
    public function __construct($emailAddress = '') {
        if (empty($emailAddress)) {
            wp_die(
                '<p>' . sprintf(
                    __('<strong>ERROR</strong>: %s', WP_GDPR_C_SLUG),
                    __('Email Address is required.', WP_GDPR_C_SLUG)
                ) . '</p>'
            );
            exit;
        }
        $this->setEmailAddress($emailAddress);
    }

    /**
     * @param string $type
     * @return array
     */
    private static function getOutputColumns($type = '') {
        $output = array();
        switch ($type) {
            case 'user' :
                $output = array(
                    __('Username', WP_GDPR_C_SLUG),
                    __('Display Name', WP_GDPR_C_SLUG),
                    __('Email Address', WP_GDPR_C_SLUG),
                    __('Website', WP_GDPR_C_SLUG),
                    __('Registered on', WP_GDPR_C_SLUG)
                );
                break;
            case 'post' :
                $output = array(
                    __('Title', WP_GDPR_C_SLUG),
                    __('Type', WP_GDPR_C_SLUG),
                    __('Author', WP_GDPR_C_SLUG),
                    __('Date', WP_GDPR_C_SLUG)
                );
                break;
            case 'comment' :
                $output = array(
                    __('Author', WP_GDPR_C_SLUG),
                    __('Content', WP_GDPR_C_SLUG),
                    __('Email Address', WP_GDPR_C_SLUG),
                    __('IP Address', WP_GDPR_C_SLUG)
                );
                break;
        }
        $output[] = __('Action', WP_GDPR_C_SLUG);
        return $output;
    }

    /**
     * @param array $data
     * @param string $type
     * @return array
     */
    private static function getOutputData($data = array(), $type = '') {
        $output = array();
        $action = '<input type="checkbox" name="' . WP_GDPR_C_PREFIX . '_remove[]" id="' . WP_GDPR_C_PREFIX . '_remove" value="1" tabindex="1" />';
        switch ($type) {
            case 'user' :
                /** @var User $user */
                foreach ($data as $user) {
                    $output[] = array(
                        $user->getUsername(),
                        $user->getDisplayName(),
                        $user->getEmailAddress(),
                        $user->getWebsite(),
                        $user->getRegisteredDate(),
                        $action
                    );
                }
                break;
            case 'post' :
                /** @var Post $post */
                foreach ($data as $post) {
                    $author = $post->getAuthor();
                    $output[] = array(
                        (!empty($post->getTitle()) ? $post->getTitle() : __('(no title)', WP_GDPR_C_SLUG)),
                        $post->getType(),
                        sprintf(
                            '%s (%s)',
                            $author->getDisplayName(),
                            $author->getEmailAddress()
                        ),
                        $post->getDate(),
                        $action
                    );
                }
                break;
            case 'comment' :
                /** @var Comment $comment */
                foreach ($data as $comment) {
                    $output[] = array(
                        $comment->getAuthorName(),
                        Helpers::shortenStringByWords(wp_strip_all_tags($comment->getContent(), true), 5),
                        $comment->getEmailAddress(),
                        $comment->getIpAddress(),
                        $action
                    );
                }
                break;
        }
        return $output;
    }

    /**
     * @param array $data
     * @param string $type
     * @return string
     */
    public static function getOutput($data = array(), $type = '') {
        $output = '';
        if (!empty($data)) {
            $output .= '<form>';
            $output .= '<table class="wpgdprc-table">';
            $output .= '<thead>';
            $output .= '<tr>';
            foreach (self::getOutputColumns($type) as $column) {
                $output .= sprintf('<th scope="col">%s</th>', $column);
            }
            $output .= '</tr>';
            $output .= '</thead>';
            $output .= '<tbody>';
            foreach (self::getOutputData($data, $type) as $row) {
                $output .= '<tr>';
                foreach ($row as $value) {
                    $output .= sprintf('<td>%s</td>', $value);
                }
                $output .= '</tr>';
            }
            $output .= '</tbody>';
            $output .= '</table>';
            $output .= '<p><a href="#">Remove selected</a></p>';
            $output .= '</form>';
        }
        return $output;
    }

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
                $object = new User($row->ID);
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