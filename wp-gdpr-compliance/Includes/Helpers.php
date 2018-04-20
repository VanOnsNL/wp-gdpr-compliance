<?php

namespace WPGDPRC\Includes;

use WPGDPRC\Includes\Extensions\CF7;

/**
 * Class Helpers
 * @package WPGDPRC\Includes
 */
class Helpers {
    /** @var null */
    private static $instance = null;

    /**
     * @return array
     */
    public static function getPluginData() {
        return get_plugin_data(WP_GDPR_C_ROOT_FILE);
    }

    /**
     * @param string $plugin
     * @return mixed
     */
    public static function getAllowedHTMLTags($plugin = '') {
        switch ($plugin) {
            case CF7::ID :
                $output = '';
                break;
            default :
                $output = array(
                    'a' => array(
                        'class' => array(),
                        'href' => array(),
                        'hreflang' => array(),
                        'title' => array(),
                        'target' => array(),
                        'rel' => array(),
                    ),
                    'br' => array(),
                    'em' => array(),
                    'strong' => array(),
                    'u' => array(),
                    'strike' => array(),
                    'span' => array(
                        'class' => array(),
                    ),
                );
                break;
        }
        return apply_filters('wpgdprc_allowed_html_tags', $output, $plugin);
    }

    /**
     * @param string $plugin
     * @return string
     */
    public static function getAllowedHTMLTagsOutput($plugin = '') {
        $allowedTags = self::getAllowedHTMLTags($plugin);
        $output = '<div class="wpgdprc-information">';
        if (!empty($allowedTags)) {
            $tags = '%privacy_policy%';
            foreach ($allowedTags as $tag => $attributes) {
                $tags .= ' <' . $tag;
                if (!empty($attributes)) {
                    foreach ($attributes as $attribute => $data) {
                        $tags .= ' ' . $attribute . '=""';
                    }
                }
                $tags .= '>';
            }
            $output .= sprintf(
                __('You can use: %s', WP_GDPR_C_SLUG),
                sprintf('<pre>%s</pre>', esc_html($tags))
            );
        } else {
            $output .= sprintf(
                '<strong>%s:</strong> %s',
                strtoupper(__('Note', WP_GDPR_C_SLUG)),
                __('No HTML allowed due to plugin limitations.', WP_GDPR_C_SLUG)
            );
        }
        $output .= '</div>';
        return $output;
    }

    /**
     * @param string $plugin
     * @return string
     */
    public static function getNotices($plugin = '') {
        $output = '';
        switch ($plugin) {
            case 'wordpress' :
                if (self::isPluginEnabled('jetpack/jetpack.php')) {
                    $activeModules = (array)get_option('jetpack_active_modules');
                    if (in_array('comments', $activeModules)) {
                        $output .= sprintf(
                            '<strong>%s:</strong> %s',
                            strtoupper(__('Note', WP_GDPR_C_SLUG)),
                            __('Please disable the custom comments form in Jetpack to make your WordPress Comments GDPR compliant.', WP_GDPR_C_SLUG)
                        );
                    }
                }
                break;
        }
        return $output;
    }

    /**
     * @return float|int
     */
    public static function getDaysLeftToComply() {
        $date = mktime(0, 0, 0, 5, 25, 2018);
        $difference = $date - time();
        if ($difference < 0) {
            return 0;
        }
        return floor($difference / 60 / 60 / 24);
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
     * @param string $plugin
     * @return bool
     */
    public static function isPluginEnabled($plugin = '') {
        $activatePlugins = (array)self::getActivePlugins();
        return (in_array($plugin, $activatePlugins));
    }

    /**
     * @param string $option
     * @param string $type
     * @return bool
     */
    public static function isEnabled($option = '', $type = 'integrations') {
        return filter_var(get_option(WP_GDPR_C_PREFIX . '_' . $type . '_' . $option), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @return array
     */
    public static function getActivePlugins() {
        $activePlugins = (array)get_option('active_plugins', array());
        // Catch network activated plugins
        $activeNetworkPlugins = (array)get_site_option('active_sitewide_plugins', array());
        if (!empty($activeNetworkPlugins)) {
            foreach ($activeNetworkPlugins as $file => $timestamp) {
                if (!in_array($file, $activePlugins)) {
                    $activePlugins[] = $file;
                }
            }
        }
        return $activePlugins;
    }

    /**
     * @return array
     */
    public static function getActivatedPlugins() {
        $output = array();
        $activePlugins = self::getActivePlugins();
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

    /**
     * @param $data
     * @return string
     */
    public function sanitizeData($data) {
        if (is_array($data)) {
            foreach ($data as &$value) {
                $value = sanitize_text_field($value);
            }
        } else {
            $data = sanitize_text_field($data);
        }
        return $data;
    }

    /**
     * @param string $format
     * @param int $timestamp
     * @return string
     */
    public static function localDateFormat($format = '', $timestamp = 0) {
        $gmtOffset = get_option('gmt_offset', '');
        if ($gmtOffset !== '') {
            $negative = ($gmtOffset < 0);
            $gmtOffset = str_replace('-', '', $gmtOffset);
            $hour = floor($gmtOffset);
            $minutes = ($gmtOffset - $hour) * 60;
            if ($negative) {
                $hour = '-' . $hour;
                $minutes = '-' . $minutes;
            }
            $date = new \DateTime(null, new \DateTimeZone('UTC'));
            $date->setTimestamp($timestamp);
            $date->modify($hour . ' hour');
            $date->modify($minutes . ' minutes');
        } else {
            $date = new \DateTime(null, new \DateTimeZone(get_option('timezone_string', 'UTC')));
            $date->setTimestamp($timestamp);
        }
        $date = new \DateTime($date->format('Y-m-d H:i:s'), new \DateTimeZone('UTC'));
        return date_i18n($format, $date->getTimestamp(), true);
    }

    /**
     * @param string $string
     * @param int $length
     * @param string $more
     * @return string
     */
    public static function shortenStringByWords($string = '', $length = 20, $more = '...') {
        $words = preg_split("/[\n\r\t ]+/", $string, $length + 1, PREG_SPLIT_NO_EMPTY);
        if (count($words) > $length) {
            array_pop($words);
            $output = implode(' ', $words) . $more;
        } else {
            $output = implode(' ', $words);
        }
        return $output;
    }

    /**
     * Ensures an ip address is both a valid IP and does not fall within
     * a private network range.
     * 
     * @param string $ipAddress
     * @return bool
     */
    public static function validateIpAddress($ipAddress = '') {
        if (strtolower($ipAddress) === 'unknown') {
            return false;
        }
        // Generate ipv4 network address
        $ipAddress = ip2long($ipAddress);
        // If the ip is set and not equivalent to 255.255.255.255
        if ($ipAddress !== false && $ipAddress !== -1) {
            /**
             * Make sure to get unsigned long representation of ip
             * due to discrepancies between 32 and 64 bit OSes and
             * signed numbers (ints default to signed in PHP)
             */
            $ipAddress = sprintf('%u', $ipAddress);
            // Do private network range checking
            if ($ipAddress >= 0 && $ipAddress <= 50331647) return false;
            if ($ipAddress >= 167772160 && $ipAddress <= 184549375) return false;
            if ($ipAddress >= 2130706432 && $ipAddress <= 2147483647) return false;
            if ($ipAddress >= 2851995648 && $ipAddress <= 2852061183) return false;
            if ($ipAddress >= 2886729728 && $ipAddress <= 2887778303) return false;
            if ($ipAddress >= 3221225984 && $ipAddress <= 3221226239) return false;
            if ($ipAddress >= 3232235520 && $ipAddress <= 3232301055) return false;
            if ($ipAddress >= 4294967040) return false;
        }
        return true;
    }

    /**
     * @return string
     */
    public static function getClientIpAddress() {
        // Check for shared internet/ISP IP
        if (!empty($_SERVER['HTTP_CLIENT_IP']) && self::validateIpAddress($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        }
        // Check for IPs passing through proxies
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // Check if multiple ips exist in var
            if (strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',') !== false) {
                $listOfIpAddresses = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                foreach ($listOfIpAddresses as $ipAddress) {
                    $ipAddress = trim($ipAddress);
                    if (self::validateIpAddress($ipAddress)) {
                        return $ipAddress;
                    }
                }
            } else {
                if (self::validateIpAddress($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                    return $_SERVER['HTTP_X_FORWARDED_FOR'];
                }
            }
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED']) && self::validateIpAddress($_SERVER['HTTP_X_FORWARDED'])) {
            return $_SERVER['HTTP_X_FORWARDED'];
        }
        if (!empty($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']) && self::validateIpAddress($_SERVER['HTTP_X_CLUSTER_CLIENT_IP'])) {
            return $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
        }
        if (!empty($_SERVER['HTTP_FORWARDED_FOR']) && self::validateIpAddress($_SERVER['HTTP_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_FORWARDED_FOR'];
        }
        if (!empty($_SERVER['HTTP_FORWARDED']) && self::validateIpAddress($_SERVER['HTTP_FORWARDED'])) {
            return $_SERVER['HTTP_FORWARDED'];
        }
        // Return unreliable ip since all else failed
        return $_SERVER['REMOTE_ADDR'];
    }

    /**
     * @param string $ipAddress
     * @return bool
     */
    public static function checkIpAddress($ipAddress = '') {
        return self::getClientIpAddress() === $ipAddress;
    }

    /**
     * @return bool|\WP_Post
     */
    public static function getRequestDataPage() {
        $output = false;
        $page = get_pages(array(
            'post_type' => 'page',
            'post_status' => 'publish,private,draft',
            'number' => 1,
            'meta_key' => '_wpgdprc_request_user_data',
            'meta_value' => '1'
        ));
        if (!empty($page)) {
            /** @var \WP_Post $output */
            $output = $page[0];
        }
        return $output;
    }

    /**
     * @return null|Helpers
     */
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}