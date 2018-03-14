<?php

namespace WPGDPRC\Includes\Extensions;

use WPGDPRC\Includes\Helpers;
use WPGDPRC\Includes\Integrations;

/**
 * Class GForms
 * @package WPGDPRC\Includes\Extensions
 */
class GForms {
    const ID = 'gravity-forms';
    const SUPPORTED_VERSION = '1.9';
    /** @var null */
    private static $instance = null;

    /**
     * @return null|GForms
     */
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function processIntegration() {
        if (!class_exists('\GFAPI')) {
            return;
        }
        foreach (self::getForms() as $form) {
            if (in_array($form['id'], self::getEnabledForms()) && Helpers::isEnabled(self::ID)) {
                self::addField($form);
            } else {
                self::removeField($form);
            }
        }
    }

    /**
     * @param array $form
     */
    public function addField($form = array()) {
        $isUpdated = false;
        $choices = array(
            array(
                'text' => self::getCheckboxText($form['id']),
                'value' => 'true',
                'isSelected' => false
            )
        );
        foreach ($form['fields'] as &$field) {
            if (isset($field->wpgdprc) && $field->wpgdprc === true) {
                $field['choices'] = $choices;
                $isUpdated = true;
            }
        }
        if (!$isUpdated) {
            $lastField = array_values(array_slice($form['fields'], -1));
            $lastField = (isset($lastField[0])) ? $lastField[0] : false;
            $id = (!empty($lastField)) ? (int)$lastField['id'] + 1 : 1;
            $form['fields'][] = array(
                'id' => $id,
                'type' => 'checkbox',
                'label' => __('Privacy', WP_GDPR_C_SLUG),
                'labelPlacement' => 'hidden_label',
                'isRequired' => true,
                'enableChoiceValue' => true,
                'choices' => $choices,
                'inputs' => array(
                    array(
                        'id' => $id . '.1',
                        'label' => self::getCheckboxText($form['id']),
                        'name' => 'wpgdprc'
                    )
                ),
                'wpgdprc' => true
            );
        }
        \GFAPI::update_form($form, $form['id']);
    }

    /**
     * @param array $validation_result
     * @return array
     */
    public function customValidation($validation_result = array()) {
        $form = $validation_result['form'];
        foreach ($form['fields'] as &$field) {
            if (isset($field['wpgdprc']) && $field['wpgdprc'] === true) {
                if (isset($field['failed_validation']) && $field['failed_validation'] === true) {
                    $field['validation_message'] = sprintf(self::getErrorMessage($form['id']));
                }
            }
        }
        $validation_result['form'] = $form;
        return $validation_result;
    }

    /**
     * @param array $form
     */
    public function removeField($form = array()) {
        foreach ($form['fields'] as $index => $field) {
            if (isset($field->wpgdprc) && $field->wpgdprc === true) {
                unset($form['fields'][$index]);
            }
        }
        \GFAPI::update_form($form, $form['id']);
    }

    function hookProcess($value, $entry, $field) {
        if (!isset($field['wpgdprc'])) {
            return $value;
        }

        return date('Y-m-d H:i:s', time());
    }

    /**
     * @return array
     */
    public function getForms() {
        $output = array();
        if (class_exists('\GFAPI')) {
            $forms = \GFAPI::get_forms();
            foreach ($forms as $form) {
                $output[] = $form;
            }
        }
        return $output;
    }

    /**
     * @return array
     */
    public function getEnabledForms() {
        return (array)get_option(WP_GDPR_C_PREFIX . '_integrations_' . self::ID . '_forms', array());
    }

    /**
     * @return array
     */
    public function getFormTexts() {
        return (array)get_option(WP_GDPR_C_PREFIX . '_integrations_' . self::ID . '_form_text', array());
    }

    /**
     * @return array
     */
    public function getFormErrorMessages() {
        return (array)get_option(WP_GDPR_C_PREFIX . '_integrations_' . self::ID . '_error_message', array());
    }

    /**
     * @param int $formId
     * @return string
     */
    public function getCheckboxText($formId = 0) {
        if (!empty($formId)) {
            $texts = $this->getFormTexts();
            if (!empty($texts[$formId])) {
                return wp_kses($texts[$formId], Helpers::getAllowedHTMLTags());
            }
        }
        return Integrations::getCheckboxText();
    }

    /**
     * @param int $formId
     * @return string
     */
    public function getErrorMessage($formId = 0) {
        if (!empty($formId)) {
            $errors = $this->getFormErrorMessages();
            if (!empty($errors[$formId])) {
                return wp_kses($errors[$formId], Helpers::getAllowedHTMLTags());
            }
        }
        return Integrations::getErrorMessage();
    }
}