<?php

namespace WPGDPRC\Includes;

use WPGDPRC\Includes\Extensions\CF7;
use WPGDPRC\Includes\Extensions\GForms;
use WPGDPRC\Includes\Extensions\WC;
use WPGDPRC\Includes\Extensions\WP;

/**
 * Class Integrations
 * @package WPGDPRC\Includes
 */
class Integrations {
    /** @var null */
    private static $instance = null;

    /**
     * @return null|Integrations
     */
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Integrations constructor.
     */
    public function __construct() {
        add_action('admin_init', array($this, 'registerSettings'));
        foreach (Helpers::getEnabledPlugins() as $plugin) {
            switch ($plugin['id']) {
                case WP::ID :
                    add_filter('comment_form_submit_field', array(WP::getInstance(), 'addField'), 999);
                    add_action('pre_comment_on_post', array(WP::getInstance(), 'checkPost'));
                    add_action( 'comment_post', array(WP::getInstance(), 'updateMeta'));
                    break;
                case CF7::ID :
                    add_action('update_option_' . WP_GDPR_C_PREFIX . '_integrations_' . CF7::ID . '_forms', array(CF7::getInstance(), 'processIntegration'));
                    add_action('update_option_' . WP_GDPR_C_PREFIX . '_integrations_' . CF7::ID . '_form_text', array(CF7::getInstance(), 'processIntegration'));
                    add_action('update_option_' . WP_GDPR_C_PREFIX . '_integrations_' . CF7::ID . '_error_message', array(CF7::getInstance(), 'processIntegration'));
                    add_action('wpcf7_init', array(CF7::getInstance(), 'addFormTagSupport'));
                    add_filter('wpcf7_validate_wpgdprc', array(CF7::getInstance(), 'validateField'), 10, 2);
                    break;
                case WC::ID :
                    add_action('woocommerce_checkout_process', array(WC::getInstance(), 'checkPost'));
                    add_action('woocommerce_review_order_before_submit', array(WC::getInstance(), 'addField'), 999);
                    add_action('woocommerce_checkout_update_order_meta', array(WC::getInstance(), 'updateMeta'));
                    add_action('woocommerce_admin_order_data_after_billing_address', array(WC::getInstance(), 'displayMeta'));
                    break;
                case GForms::ID :
                    add_action('update_option_' . WP_GDPR_C_PREFIX . '_integrations_' . GForms::ID . '_forms', array(GForms::getInstance(), 'processIntegration'));
                    add_action('update_option_' . WP_GDPR_C_PREFIX . '_integrations_' . GForms::ID . '_form_text', array(GForms::getInstance(), 'processIntegration'));
                    add_action('update_option_' . WP_GDPR_C_PREFIX . '_integrations_' . GForms::ID . '_error_message', array(GForms::getInstance(), 'processIntegration'));
                    add_filter('gform_entries_field_value', array(GForms::getInstance(), 'changeEntriesFieldValue'), 10, 4);
                    add_filter('gform_get_field_value', array(GForms::getInstance(), 'changeFieldValue'), 10, 2);
                    foreach (GForms::getInstance()->getEnabledForms() as $formId) {
                        add_filter('gform_save_field_value_' . $formId, array(GForms::getInstance(), 'saveFieldValue'), 10, 3);
                        add_filter('gform_entry_list_columns_' . $formId, array(GForms::getInstance(), 'overrideColumn'), 10, 2);
                        add_action('gform_validation_' . $formId, array(GForms::getInstance(), 'customValidation'));
                    }
                    break;
            }
        }
    }

    public function registerSettings() {
        foreach (self::getSupportedIntegrations() as $plugin) {
            register_setting(WP_GDPR_C_SLUG, WP_GDPR_C_PREFIX . '_integrations_' . $plugin['id'], 'intval');
            switch ($plugin['id']) {
                case CF7::ID :
                    add_action('update_option_' . WP_GDPR_C_PREFIX . '_integrations_' . $plugin['id'], array(CF7::getInstance(), 'processIntegration'));
                    register_setting(WP_GDPR_C_SLUG, WP_GDPR_C_PREFIX . '_integrations_' . $plugin['id'] . '_forms');
                    register_setting(WP_GDPR_C_SLUG, WP_GDPR_C_PREFIX . '_integrations_' . $plugin['id'] . '_form_text', array('sanitize_callback' => array(Helpers::getInstance(), 'sanitizeData')));
                    register_setting(WP_GDPR_C_SLUG, WP_GDPR_C_PREFIX . '_integrations_' . $plugin['id'] . '_error_message', array('sanitize_callback' => array(Helpers::getInstance(), 'sanitizeData')));
                    break;
                case GForms::ID :
                    add_action('update_option_' . WP_GDPR_C_PREFIX . '_integrations_' . $plugin['id'], array(GForms::getInstance(), 'processIntegration'));
                    register_setting(WP_GDPR_C_SLUG, WP_GDPR_C_PREFIX . '_integrations_' . $plugin['id'] . '_forms');
                    register_setting(WP_GDPR_C_SLUG, WP_GDPR_C_PREFIX . '_integrations_' . $plugin['id'] . '_form_text');
                    register_setting(WP_GDPR_C_SLUG, WP_GDPR_C_PREFIX . '_integrations_' . $plugin['id'] . '_error_message');
                    break;
                default :
                    register_setting(WP_GDPR_C_SLUG, WP_GDPR_C_PREFIX . '_integrations_' . $plugin['id'] . '_text');
                    register_setting(WP_GDPR_C_SLUG, WP_GDPR_C_PREFIX . '_integrations_' . $plugin['id'] . '_error_message');
                    break;
            }
        }
    }

    /**
     * @param string $plugin
     * @return string
     */
    public static function getSupportedPluginOptions($plugin = '') {
        $output = '';
        switch ($plugin) {
            case CF7::ID :
                $forms = CF7::getInstance()->getForms();
                if (!empty($forms)) {
                    $optionNameForms = WP_GDPR_C_PREFIX . '_integrations_' . $plugin . '_forms';
                    $optionNameFormText = WP_GDPR_C_PREFIX . '_integrations_' . $plugin . '_form_text';
                    $optionNameErrorMessage = WP_GDPR_C_PREFIX . '_integrations_' . $plugin . '_error_message';
                    $enabledForms = CF7::getInstance()->getEnabledForms();
                    $output .= '<ul class="wpgdprc-checklist-options">';
                    foreach ($forms as $form) {
                        $formSettingId = WP_GDPR_C_PREFIX . '_integrations_' . $plugin . '_form_' . $form;
                        $textSettingId = WP_GDPR_C_PREFIX . '_integrations_' . $plugin . '_form_text_' . $form;
                        $errorSettingId = WP_GDPR_C_PREFIX . '_integrations_' . $plugin . '_error_message_' . $form;
                        $enabled = in_array($form, $enabledForms);
                        $text = CF7::getInstance()->getCheckboxText($form, false);
                        $errorMessage = CF7::getInstance()->getErrorMessage($form);
                        $output .= '<li class="wpgdprc-clearfix">';
                        $output .= '<div class="wpgdprc-checkbox">';
                        $output .= '<input type="checkbox" name="' . $optionNameForms . '[]" id="' . $formSettingId . '" value="' . $form . '" tabindex="1" data-type="save_setting" data-option="' . $optionNameForms . '" data-append="1" ' . checked(true, $enabled, false) . ' />';
                        $output .= '<label for="' . $formSettingId . '"><strong>' . sprintf(__('Form: %s', WP_GDPR_C_SLUG), get_the_title($form)) . '</strong></label>';
                        $output .= '<span class="wpgdprc-instructions">' . __('Activate for this form:', WP_GDPR_C_SLUG) . '</span>';
                        $output .= '</div>';
                        $output .= '<p class="wpgdprc-setting">';
                        $output .= '<label for="' . $textSettingId . '">' . __('Checkbox text', WP_GDPR_C_SLUG) . '</label>';
                        $output .= '<input type="text" name="' . $optionNameFormText . '[' . $form . ']' . '" class="regular-text" id="' . $textSettingId . '" placeholder="' . $text . '" value="' . $text . '" />';
                        $output .= '</p>';
                        $output .= '<p class="wpgdprc-setting">';
                        $output .= '<label for="' . $errorSettingId . '">' . __('Error message', WP_GDPR_C_SLUG) . '</label>';
                        $output .= '<input type="text" name="' . $optionNameErrorMessage . '[' . $form . ']' . '" class="regular-text" id="' . $errorSettingId . '" placeholder="' . $errorMessage . '" value="' . $errorMessage . '" />';
                        $output .= '</p>';
                        $output .= '</li>';
                    }
                    $output .= '</ul>';
                } else {
                    $output = '<p>' . __('No forms found.', WP_GDPR_C_SLUG) . '</p>';
                }
                break;
            case GForms::ID :
                $forms = GForms::getInstance()->getForms();
                if (!empty($forms)) {
                    $optionNameForms = WP_GDPR_C_PREFIX . '_integrations_' . $plugin . '_forms';
                    $optionNameFormText = WP_GDPR_C_PREFIX . '_integrations_' . $plugin . '_form_text';
                    $optionNameErrorMessage = WP_GDPR_C_PREFIX . '_integrations_' . $plugin . '_error_message';
                    $enabledForms = GForms::getInstance()->getEnabledForms();
                    $output .= '<ul class="wpgdprc-checklist-options">';
                    foreach ($forms as $form) {
                        $formSettingId = WP_GDPR_C_PREFIX . '_integrations_' . $plugin . '_form_' . $form['id'];
                        $textSettingId = WP_GDPR_C_PREFIX . '_integrations_' . $plugin . '_form_text_' . $form['id'];
                        $errorSettingId = WP_GDPR_C_PREFIX . '_integrations_' . $plugin . '_error_message_' . $form['id'];
                        $enabled = in_array($form['id'], $enabledForms);
                        $text = esc_html(GForms::getInstance()->getCheckboxText($form['id'], false));
                        $errorMessage = esc_html(GForms::getInstance()->getErrorMessage($form['id']));
                        $output .= '<li class="wpgdprc-clearfix">';
                        $output .= '<div class="wpgdprc-checkbox">';
                        $output .= '<input type="checkbox" name="' . $optionNameForms . '[]" id="' . $formSettingId . '" value="' . $form['id'] . '" tabindex="1" data-type="save_setting" data-option="' . $optionNameForms . '" data-append="1" ' . checked(true, $enabled, false) . ' />';
                        $output .= '<label for="' . $formSettingId . '"><strong>' . sprintf(__('Form: %s', WP_GDPR_C_SLUG), $form['title']) . '</strong></label>';
                        $output .= '<span class="wpgdprc-instructions">' . __('Activate for this form:', WP_GDPR_C_SLUG) . '</span>';
                        $output .= '</div>';
                        $output .= '<p class="wpgdprc-setting">';
                        $output .= '<label for="' . $textSettingId . '">' . __('Checkbox text', WP_GDPR_C_SLUG) . '</label>';
                        $output .= '<input type="text" name="' . $optionNameFormText . '[' . $form['id'] . ']' . '" class="regular-text" id="' . $textSettingId . '" placeholder="' . $text . '" value="' . $text . '" />';
                        $output .= '</p>';
                        $output .= '<p class="wpgdprc-setting">';
                        $output .= '<label for="' . $errorSettingId . '">' . __('Error message', WP_GDPR_C_SLUG) . '</label>';
                        $output .= '<input type="text" name="' . $optionNameErrorMessage . '[' . $form['id'] . ']' . '" class="regular-text" id="' . $errorSettingId . '" placeholder="' . $errorMessage . '" value="' . $errorMessage . '" />';
                        $output .= '</p>';
                        $output .= Helpers::getAllowedHTMLTagsOutput();
                        $output .= '</li>';
                    }
                    $output .= '</ul>';
                } else {
                    $output = '<p>' . __('No forms found.', WP_GDPR_C_SLUG) . '</p>';
                }
                break;
            default :
                $optionNameText = WP_GDPR_C_PREFIX . '_integrations_' . $plugin . '_text';
                $optionNameErrorMessage = WP_GDPR_C_PREFIX . '_integrations_' . $plugin . '_error_message';
                $text = esc_html(self::getCheckboxText($plugin, false));
                $errorMessage = esc_html(self::getErrorMessage($plugin));
                $output .= '<ul class="wpgdprc-checklist-options">';
                $output .= '<li class="wpgdprc-clearfix">';
                $output .= '<p class="wpgdprc-setting">';
                $output .= '<label for="' . $optionNameText . '">' . __('Checkbox text', WP_GDPR_C_SLUG) . '</label>';
                $output .= '<input type="text" name="' . $optionNameText . '" class="regular-text" id="' . $optionNameText . '" placeholder="' . $text . '" value="' . $text . '" />';
                $output .= '</p>';
                $output .= '<p class="wpgdprc-setting">';
                $output .= '<label for="' . $optionNameErrorMessage . '">' . __('Error message', WP_GDPR_C_SLUG) . '</label>';
                $output .= '<input type="text" name="' . $optionNameErrorMessage . '" class="regular-text" id="' . $optionNameErrorMessage . '" placeholder="' . $errorMessage . '" value="' . $errorMessage . '" />';
                $output .= '</p>';
                $output .= Helpers::getAllowedHTMLTagsOutput();
                $output .= '</li>';
                $output .= '</ul>';
                break;
        }
        return $output;
    }

    /**
     * @param string $plugin
     * @param bool $insertPrivacyPolicyLink
     * @return string
     */
    public static function getCheckboxText($plugin = '', $insertPrivacyPolicyLink = true) {
        $option = (!empty($plugin)) ? get_option(WP_GDPR_C_PREFIX . '_integrations_' . $plugin . '_text') : '';
        if (empty($option)) {
            return __('By using this form you agree with the storage and handling of your data by this website.', WP_GDPR_C_SLUG);
        }
        $option = wp_kses($option, Helpers::getAllowedHTMLTags());
        return ($insertPrivacyPolicyLink === true) ? self::insertPrivacyPolicyLink($option) : $option;
    }

    /**
     * @param string $plugin
     * @return mixed
     */
    public static function getErrorMessage($plugin = '') {
        $option = (!empty($plugin)) ? get_option(WP_GDPR_C_PREFIX . '_integrations_' . $plugin . '_error_message') : '';
        if (empty($option)) {
            return __('Please accept the privacy checkbox.', WP_GDPR_C_SLUG);
        }
        return wp_kses($option, Helpers::getAllowedHTMLTags());
    }

    /**
     * @param string $content
     * @return mixed|string
     */
    public static function insertPrivacyPolicyLink($content = '') {
        $page = get_option(WP_GDPR_C_PREFIX . '_settings_privacy_policy_page');
        $label = get_option(WP_GDPR_C_PREFIX . '_settings_privacy_policy_text');
        if (!empty($page) && !empty($label)) {
            $content = str_replace('%privacy_policy%', sprintf('<a target="_blank" href="%s">%s</a>', get_page_link($page), esc_html($label)), $content);
        }
        return $content;
    }

    /**
     * @return array
     */
    public static function getSupportedWordPressFunctionality() {
        return array(
            array(
                'id' => 'wordpress',
                'name' => __('WordPress Comments', WP_GDPR_C_SLUG),
                'description' => __('When activated the GDPR checkbox will be added automatically just above the submit button.', WP_GDPR_C_SLUG),
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
                'supported_version' => CF7::SUPPORTED_VERSION,
                'file' => 'contact-form-7/wp-contact-form-7.php',
                'name' => __('Contact Form 7', WP_GDPR_C_SLUG),
                'description' => __('A GDPR form tag will be automatically added to every form you activate.', WP_GDPR_C_SLUG),
            ),
            array(
                'id' => GForms::ID,
                'supported_version' => GForms::SUPPORTED_VERSION,
                'file' => 'gravityforms/gravityforms.php',
                'name' => __('Gravity Forms', WP_GDPR_C_SLUG),
                'description' => __('A GDPR form tag will be automatically added to every form you activate.', WP_GDPR_C_SLUG),
            ),
            array(
                'id' => WC::ID,
                'supported_version' => WC::SUPPORTED_VERSION,
                'file' => 'woocommerce/woocommerce.php',
                'name' => __('WooCommerce', WP_GDPR_C_SLUG),
                'description' => __('The GDPR checkbox will be added automatically at the end of your checkout page.', WP_GDPR_C_SLUG),
            )
        );
    }

    /**
     * @return array
     */
    public static function getSupportedIntegrations() {
        return array_merge(self::getSupportedPlugins(), self::getSupportedWordPressFunctionality());
    }

    /**
     * @return array
     */
    public static function getSupportedIntegrationsLabels() {
        $output = array();
        $supportedIntegrations = self::getSupportedIntegrations();
        foreach ($supportedIntegrations as $supportedIntegration) {
            $output[] = $supportedIntegration['name'];
        }
        return $output;
    }
}