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
     * @param array $form
     */
    public function removeField($form = array()) {
        foreach ($form['fields'] as $index => $field) {
            if (isset($field['wpgdprc']) && $field['wpgdprc'] === true) {
                unset($form['fields'][$index]);
            }
        }
        \GFAPI::update_form($form, $form['id']);
    }

    /**
     * @param string $value
     * @param int $formId
     * @param int $fieldId
     * @param array $entry
     * @return string
     */
    function changeEntriesFieldValue($value = '', $formId = 0, $fieldId = 0, $entry = array()) {
        if (empty($value)) {
            $id = self::getFieldIdByFormId($formId);
            if (!empty($id)) {
                if ($fieldId === $id) {
                    $value = (!empty($entry[$fieldId])) ? $entry[$fieldId] : __('Not accepted.', WP_GDPR_C_SLUG);
                }
            }
        }
        return $value;
    }

    /**
     * @param $value
     * @param array $entry
     * @return string
     */
    public function changeFieldValue($value, $entry = array()) {
        $id = self::getFieldIdByFormId($entry['form_id']);
        if (!empty($id) && isset($value[$id])) {
            if (empty($value[$id])) {
                return __('Not accepted.', WP_GDPR_C_SLUG);
            }
        }
        return $value;
    }

    /**
     * @param string $value
     * @param array $lead
     * @param \GF_Field $field
     * @return string
     */
    public function saveFieldValue($value = '', $lead = array(), \GF_Field $field) {
        if (isset($field['wpgdprc']) && $field['wpgdprc'] === true) {
            $date = date(get_option('date_format') . ' ' . get_option('time_format'), time());
            $date = sprintf(__('Accepted on %s.', WP_GDPR_C_SLUG), $date);
            $value = (empty($value)) ? __('Not accepted.', WP_GDPR_C_SLUG) : $date;
        }
        return $value;
    }

    /**
     * @param array $columns
     * @param int $formId
     * @return array
     */
    public function overrideColumn($columns = array(), $formId = 0) {
        $key = array_search(self::getCheckboxText($formId), $columns);
        if (!empty($key) && isset($columns[$key])) {
            $columns[$key] = __('Privacy', WP_GDPR_C_SLUG);
        }
        return $columns;
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
     * @param bool $insertPrivacyPolicyLink
     * @return string
     */
    public function getCheckboxText($formId = 0, $insertPrivacyPolicyLink = true) {
        if (!empty($formId)) {
            $texts = $this->getFormTexts();
            if (!empty($texts[$formId])) {
                $result = wp_kses($texts[$formId], Helpers::getAllowedHTMLTags());
                return ($insertPrivacyPolicyLink === true) ? Integrations::insertPrivacyPolicyLink($result) : $result;
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

    /**
     * @param int $formId
     * @return int
     */
    private static function getFieldIdByFormId($formId = 0) {
        $form = \GFFormsModel::get_form_meta($formId);
        foreach ($form['fields'] as $field) {
            if (isset($field['wpgdprc']) && $field['wpgdprc'] === true) {
                if (isset($field['inputs'][0]['id'])) {
                    return $field['inputs'][0]['id'];
                }
            }
        }
        return 0;
    }
}