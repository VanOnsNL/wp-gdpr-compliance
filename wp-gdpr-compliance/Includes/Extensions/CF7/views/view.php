<?php

namespace WPGDPRC\Includes\Extensions\CF7;
/**
 * Class view
 */
class view
{
    /** @var null */
    private static $instance = null;

    /**
     * @return null|Pages
     */
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function generatePage() {
        ?>
        <h1>TEST</h1>
        <?php
    }
}