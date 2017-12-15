<?php

namespace WPGDPRC\Includes;

/**
 * Class Helpers
 * @package WPGDPRC\Includes
 */
class Helpers {
    /**
     * @return array
     */
    public static function getPluginData() {
        return get_plugin_data(WP_GDPR_C_ROOT_FILE);
    }

    /**
     * @return array
     */
    public static function getCheckList() {
        return array(
            'contact_form' => array(
                'label' => __('Do you have a contact form?', WP_GDPR_C_SLUG),
                'description' => __('Make sure you add a checkbox specifically asking the user of the form if they consent to you storing and using their personal information to get back in touch with them. The checkbox must be unchecked by default. Also mention if you will send or share the data with any 3rd-parties and which.', WP_GDPR_C_SLUG),
            ),
            'comments' => array(
                'label' => __('Can visitors comment anywhere on your website?', WP_GDPR_C_SLUG),
                'description' => __('Make sure you add a checkbox specifically asking the user of the comment section if they consent to storing their message attached to the e-mail address they\'ve used to comment. The checkbox must be unchecked by default. Also mention if you will send or share the data with any 3rd-parties and which.', WP_GDPR_C_SLUG),
            ),
            'webshop' => array(
                'label' => __('Is there an order form on your website or webshop present?', WP_GDPR_C_SLUG),
                'description' => __('Make sure you add a checkbox specifically asking the user of the form if they consent to you storing and using their personal information to ship the order. This cannot be the same checkbox as the Privacy Policy checkbox you should already have in place. The checkbox must be unchecked by default. Also mention if you will send or share the data with any 3rd-parties and which.', WP_GDPR_C_SLUG),
            ),
            'forum' => array(
                'label' => __('Do you provide a forum or message board environment?', WP_GDPR_C_SLUG),
                'description' => __('Make sure you add a checkbox specifically asking forum / board users if they consent to you storing and using their personal information and messages. The checkbox must be unchecked by default. Also mention if you will send or share the data with any 3rd-parties and which.', WP_GDPR_C_SLUG),
            ),
            'chat' => array(
                'label' => __('Can visitors chat with your company directly?', WP_GDPR_C_SLUG),
                'description' => __('Make sure you add a checkbox specifically asking chat users if they consent to you storing and using their personal information and messages. The checkbox must be unchecked by default. We recommend also mentioning for how long you will store chat messages or deleting them all within 24 hours. Also mention if you will send or share the data with any 3rd-parties and which.', WP_GDPR_C_SLUG),
            ),
        );
    }

    /**
     * @param string $plugin
     * @param string $type
     * @return bool
     */
    public static function isEnabled($plugin = '', $type = 'integrations') {
        return filter_var(get_option(WP_GDPR_C_PREFIX . '_' . $type . '_' . $plugin), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @return array
     */
    public static function getSupportedPlugins() {
        return array(
            array(
                'id' => 'contact-form-7',
                'file' => 'contact-form-7/wp-contact-form-7.php',
                'name' => __('Contact Form 7', WP_GDPR_C_SLUG),
                'description' => 'It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout.',
            ),
            array(
                'id' => 'woocommerce',
                'file' => 'woocommerce/woocommerce.php',
                'name' => __('WooCommerce', WP_GDPR_C_SLUG),
                'description' => 'It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout.',
            )
        );
    }

    /**
     * @param array $output
     * @return array
     */
    public static function getActivatedPlugins($output = array()) {
        $activePlugins = (!empty(get_option('active_plugins'))) ? get_option('active_plugins') : array();
        foreach (self::getSupportedPlugins() as $plugin) {
            if (in_array($plugin['file'], $activePlugins)) {
                $output[] = $plugin;
            }
        }
        return $output;
    }

    /**
     * @param array $output
     * @return array
     */
    public static function getEnabledPlugins($output = array()) {
        foreach (self::getActivatedPlugins() as $plugin) {
            if (self::isEnabled($plugin['id'])) {
                $output[] = $plugin;
            }
        }
        return $output;
    }
}