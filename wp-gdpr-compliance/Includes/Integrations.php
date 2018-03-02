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
                    break;
                case CF7::ID :
                    add_action('update_option_' . WP_GDPR_C_PREFIX . '_integrations_' . CF7::ID . '_forms', array(CF7::getInstance(), 'processIntegration'));
                    add_action('update_option_' . WP_GDPR_C_PREFIX . '_integrations_' . CF7::ID . '_form_text', array(CF7::getInstance(), 'processIntegration'));
                    add_action('wpcf7_init', array(CF7::getInstance(), 'addFormTagSupport'));
                    add_filter('wpcf7_validate_wpgdprc', array(CF7::getInstance(), 'validateField'), 10, 2);
                    break;
                case WC::ID :
                    add_action('woocommerce_checkout_process', array(WC::getInstance(), 'checkPost'));
                    add_action('woocommerce_review_order_before_submit', array(WC::getInstance(), 'addField'), 999);
                    break;
                case GForms::ID :
                    add_action('update_option_' . WP_GDPR_C_PREFIX . '_integrations_' . GForms::ID . '_forms', array(GForms::getInstance(), 'processIntegration'));
                    add_action('update_option_' . WP_GDPR_C_PREFIX . '_integrations_' . GForms::ID . '_form_text', array(GForms::getInstance(), 'processIntegration'));
                    break;
            }
        }
    }

    public function registerSettings() {
        foreach (self::getSupportedIntegrations() as $plugin) {
            register_setting(WP_GDPR_C_SLUG, WP_GDPR_C_PREFIX . '_integrations_' . $plugin['id'], 'intval');
            switch ($plugin['id']) {
                case CF7::ID :
                case GForms::ID :
                    $class = ($plugin['id'] === CF7::ID) ? CF7::getInstance() : GForms::getInstance();
                    add_action('update_option_' . WP_GDPR_C_PREFIX . '_integrations_' . $plugin['id'], array($class, 'processIntegration'));
                    register_setting(WP_GDPR_C_SLUG, WP_GDPR_C_PREFIX . '_integrations_' . $plugin['id'] . '_forms');
                    register_setting(WP_GDPR_C_SLUG, WP_GDPR_C_PREFIX . '_integrations_' . $plugin['id'] . '_form_text');
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
                    $enabledForms = CF7::getInstance()->getEnabledForms();
                    $output .= '<ul class="wpgdprc-checklist-options">';
                    foreach ($forms as $form) {
                        $formSettingId = WP_GDPR_C_PREFIX . '_integrations_' . $plugin . '_form_' . $form;
                        $textSettingId = WP_GDPR_C_PREFIX . '_integrations_' . $plugin . '_form_text_' . $form;
                        $enabled = in_array($form, $enabledForms);
                        $text = CF7::getInstance()->getCheckboxText($form);
                        $output .= '<li>';
                        $output .= '<div class="wpgdprc-checkbox">';
                        $output .= '<input type="checkbox" name="' . $optionNameForms . '[]" id="' . $formSettingId . '" value="' . $form . '" tabindex="1" data-type="save_setting" data-option="' . $optionNameForms . '" data-append="1" ' . checked(true, $enabled, false) . ' />';
                        $output .= '<label for="' . $formSettingId . '"><strong>' . sprintf(__('Form: %s', WP_GDPR_C_SLUG), get_the_title($form)) . '</strong></label>';
                        $output .= '<span class="wpgdprc-instructions">' . __('Activate for this form:', WP_GDPR_C_SLUG) . '</span>';
                        $output .= '</div>';
                        $output .= '<p class="wpgdprc-setting">';
                        $output .= '<label for="' . $textSettingId . '">' . __('Checkbox text', WP_GDPR_C_SLUG) . '</label>';
                        $output .= '<input type="text" name="' . $optionNameFormText . '[' . $form . ']' . '" class="regular-text" id="' . $textSettingId . '" placeholder="' . $text . '" value="' . $text . '" />';
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
                    $enabledForms = GForms::getInstance()->getEnabledForms();
                    $output .= '<ul class="wpgdprc-checklist-options">';
                    foreach ($forms as $form) {
                        $formSettingId = WP_GDPR_C_PREFIX . '_integrations_' . $plugin . '_form_' . $form['id'];
                        $textSettingId = WP_GDPR_C_PREFIX . '_integrations_' . $plugin . '_form_text_' . $form['id'];
                        $enabled = in_array($form['id'], $enabledForms);
                        $text = esc_html(GForms::getInstance()->getCheckboxText($form['id']));
                        $output .= '<li>';
                        $output .= '<div class="wpgdprc-checkbox">';
                        $output .= '<input type="checkbox" name="' . $optionNameForms . '[]" id="' . $formSettingId . '" value="' . $form['id'] . '" tabindex="1" data-type="save_setting" data-option="' . $optionNameForms . '" data-append="1" ' . checked(true, $enabled, false) . ' />';
                        $output .= '<label for="' . $formSettingId . '"><strong>' . sprintf(__('Form: %s', WP_GDPR_C_SLUG), $form['title']) . '</strong></label>';
                        $output .= '<span class="wpgdprc-instructions">' . __('Activate for this form:', WP_GDPR_C_SLUG) . '</span>';
                        $output .= '</div>';
                        $output .= '<p class="wpgdprc-setting">';
                        $output .= '<label for="' . $textSettingId . '">' . __('Checkbox text', WP_GDPR_C_SLUG) . '</label>';
                        $output .= '<input type="text" name="' . $optionNameFormText . '[' . $form['id'] . ']' . '" class="regular-text" id="' . $textSettingId . '" placeholder="' . $text . '" value="' . $text . '" />';
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
                $text = esc_html(self::getCheckboxText($plugin));
                $errorMessage = esc_html(self::getErrorMessage($plugin));
                $output .= '<ul class="wpgdprc-checklist-options">';
                $output .= '<li>';
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
     * @return string
     */
    public static function getCheckboxText($plugin = '') {
        $option = (!empty($plugin)) ? get_option(WP_GDPR_C_PREFIX . '_integrations_' . $plugin . '_text') : '';
        if (empty($option)) {
            return __('By using this form you agree with the storage and handling of your data by this website.', WP_GDPR_C_SLUG);
        }
        return wp_kses($option, Helpers::getAllowedHTMLTags());
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