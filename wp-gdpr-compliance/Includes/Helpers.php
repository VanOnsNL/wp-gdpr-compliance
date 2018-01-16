<?php

namespace WPGDPRC\Includes;

use WPGDPRC\Includes\Extensions\CF7;
use WPGDPRC\Includes\Extensions\WC;

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
     * @param string $plugin
     * @param string $type
     * @return bool
     */
    public static function isEnabled($plugin = '', $type = 'integrations') {
        return filter_var(get_option(WP_GDPR_C_PREFIX . '_' . $type . '_' . $plugin), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @param string $plugin
     * @return string
     */
    public static function getSupportedPluginOptions($plugin = '') {
        $output = '';
        switch ($plugin) {
            case CF7::ID :
                $optionNameForms = WP_GDPR_C_PREFIX . '_integrations_' . $plugin . '_forms';
                $optionNameFormText = WP_GDPR_C_PREFIX . '_integrations_' . $plugin . '_form_text';
                $valueForms = CF7::getInstance()->getEnabledForms();
                $output .= '<ul class="wpgdprc-checklist-options">';
                foreach (CF7::getInstance()->getForms() as $form) {
                    $formSettingId = WP_GDPR_C_PREFIX . '_integrations_' . $plugin . '_form_' . $form;
                    $textSettingId = WP_GDPR_C_PREFIX . '_integrations_' . $plugin . '_form_text_' . $form;
                    $text = CF7::getInstance()->getLabelText($form);
                    $output .= '<li>';
                    $output .= '<div class="wpgdprc-checkbox">';
                    $output .= '<input type="checkbox" name="' . $optionNameForms . '[]" id="' . $formSettingId . '" value="' . $form . '" tabindex="1" data-type="save_setting" data-option="' . $optionNameForms . '" data-append="1" ' . checked(true, (in_array($form, $valueForms)), false) . ' />';
                    $output .= '<label for="' . $formSettingId . '"><strong>Form: ' . get_the_title($form) . '</strong></label>';
                    $output .= '<span class="wpgdprc-instructions">' . __('Activate for this form:', WP_GDPR_C_SLUG) . '</span>';
                    $output .= '<div class="wpgdprc-switch" aria-hidden="true">';
                    $output .= '<div class="wpgdprc-switch-label">';
                    $output .= '<div class="wpgdprc-switch-inner"></div>';
                    $output .= '<div class="wpgdprc-switch-switch"></div>';
                    $output .= '</div>';
                    $output .= '</div>';
                    $output .= '</div>';
                    $output .= '<p class="wpgdprc-setting">';
                    $output .= '<label for="' . $textSettingId . '">' . __('Checkbox text', WP_GDPR_C_SLUG) . '</label>';
                    $output .= '<input type="text" name="' . $optionNameFormText . '[' . $form . ']' . '" class="regular-text" id="' . $textSettingId . '" placeholder="' . $text . '" value="' . $text . '" />';
                    $output .= '</p>';
                    $output .= '</li>';
                }
                $output .= '</ul>';
                break;
            default :
                $optionName = WP_GDPR_C_PREFIX . '_integrations_' . $plugin . '_text';
                $text = get_option($optionName);
                $text = empty($text) ? __('By using this form you agree with the storage and handling of your data by this website.', WP_GDPR_C_SLUG) : $text;
                $output .= '<ul class="wpgdprc-checklist-options">';
                $output .= '<li>';
                $output .= '<p class="wpgdprc-setting">';
                $output .= '<label for="' . $optionName . '">' . __('Checkbox text', WP_GDPR_C_SLUG) . '</label>';
                $output .= '<input type="text" name="' . $optionName . '" class="regular-text" id="' . $optionName . '" placeholder="' . $text . '" value="' . $text . '" />';
                $output .= '</p>';
                $output .= '</li>';
                $output .= '</ul>';
                break;
        }
        return $output;
    }

    /**
     * @return array
     */
    public static function getSupportedWordpress() {
        return array(
            array(
                'id' => 'wordpress',
                'name' => __('WordPress Comments', WP_GDPR_C_SLUG),
                'description' => 'When activated the GDPR checkbox will be added automatically just above the submit button.',
                'text_field' => true,
                'fields' => false
            )
        );
    }

    /**
     * @return array
     */
    public static function getSupportedPlugins() {
        return array(
            array(
                'id' => CF7::ID,
                'file' => 'contact-form-7/wp-contact-form-7.php',
                'name' => __('Contact Form 7', WP_GDPR_C_SLUG),
                'description' => __('A GDPR form tag will be automatically added to every form you activate it for.', WP_GDPR_C_SLUG),
                'text_field' => false,
                'fields' => array(
                    'integrations_%id%_forms',
                    'integrations_%id%_form_text'
                )
            ),
            array(
                'id' => WC::ID,
                'file' => 'woocommerce/woocommerce.php',
                'name' => __('WooCommerce', WP_GDPR_C_SLUG),
                'description' => __('The GDPR checkbox will be added automatically at the end of your checkout page.', WP_GDPR_C_SLUG),
                'text_field' => true,
                'fields' => false
            )
        );
    }

    /**
     * @return array
     */
    public static function getSupported() {
        return array_merge(self::getSupportedPlugins(), self::getSupportedWordpress());
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
        foreach (self::getSupportedWordpress() as $wp) {
            $output[] = $wp;
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

    /**
     * @param string $option
     * @return mixed
     */
    public static function getAdvancedOption($option = '') {
        $option = get_option(WP_GDPR_C_PREFIX . '_advanced_' . $option);
        $default = __('Please accept the privacy checkbox.', WP_GDPR_C_SLUG);
        return empty($option) ? $default : $option;
    }
}