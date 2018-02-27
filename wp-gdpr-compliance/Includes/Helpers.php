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
                'label' => __('Do you provide a forum or message board?', WP_GDPR_C_SLUG),
                'description' => __('Make sure you add a checkbox specifically asking forum / board users if they consent to you storing and using their personal information and messages. The checkbox must be unchecked by default. Also mention if you will send or share the data with any 3rd-parties and which.', WP_GDPR_C_SLUG),
            ),
            'chat' => array(
                'label' => __('Can visitors chat with your company directly?', WP_GDPR_C_SLUG),
                'description' => __('Make sure you add a checkbox specifically asking chat users if they consent to you storing and using their personal information and messages. The checkbox must be unchecked by default. We recommend also mentioning for how long you will store chat messages or deleting them all within 24 hours. Also mention if you will send or share the data with any 3rd-parties and which.', WP_GDPR_C_SLUG),
            ),
        );
    }

    /**
     * @param bool $return_default
     * @return mixed
     */
    public static function getErrorMessage($return_default = true) {
        $option = get_option(WP_GDPR_C_PREFIX . '_advanced_error');
        if (empty($option) && $return_default === true) {
            return __('Please accept the privacy checkbox.', WP_GDPR_C_SLUG);
        }
        return esc_html($option);
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
    public static function getActivatedPlugins() {
        $output = array();

        $activePlugins = (array) get_option('active_plugins', array());
        $activeNetworkPlugins = (array) get_site_option('active_sitewide_plugins', array());
        if (!empty($activeNetworkPlugins)) {
            foreach ($activeNetworkPlugins as $file => $timestamp) {
                if (!in_array($file, $activePlugins)) {
                    $activePlugins[] = $file;
                }
            }
        }

        // Loop through supported plugins
        foreach (Integrations::getSupportedPlugins() as $plugin) {
            if (in_array($plugin['file'], $activePlugins)) {
                if (is_admin()) {
                    $plugin['supported'] = true;
                    if (isset($plugin['supported_version'])) {
                        $pluginData = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin['file']);
                        if (!empty($pluginData['Version']) && $pluginData['Version'] < $plugin['supported_version']) {
                            $plugin['supported'] = false;
                        }
                    }
                }
                $output[] = $plugin;
            }
        }

        // Loop through supported WordPress functionality
        foreach (Integrations::getSupportedWordPressFunctionality() as $wp) {
            $wp['supported'] = true;
            $output[] = $wp;
        }

        return $output;
    }

    /**
     * @return array
     */
    public static function getEnabledPlugins() {
        $output = array();
        foreach (self::getActivatedPlugins() as $plugin) {
            if (self::isEnabled($plugin['id'])) {
                $output[] = $plugin;
            }
        }
        return $output;
    }

    public static function getDaysLeft() {
        $date = mktime(0, 0, 0, 5, 25, 2018, 0);
        $difference = $date - time();

        if ($difference < 0) {
            return 0;
        }

        return floor($difference/60/60/24);
    }
}