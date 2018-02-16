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
        foreach ($form['fields'] as $field) {
            if (isset($field->wpgdprc) && $field->wpgdprc === true) {
                return;
            }
        }
        $lastField = array_values(array_slice($form['fields'], -1));
        $lastField = (isset($lastField[0])) ? $lastField[0] : false;
        $id = (!empty($lastField)) ? (int)$lastField['id'] + 1 : 1;
        $form['fields'][] = array(
            'type' => 'checkbox',
            'id' => $id,
            'label' => __('GDPR', WP_GDPR_C_SLUG),
            'isRequired' => true,
            'enableChoiceValue' => true,
            'choices' => array(
                array(
                    'text' => self::getCheckboxText($form['id']),
                    'value' => 1,
                    'isSelected' => false
                )
            ),
            'inputs' => array(
                array(
                    'id' => $id . '.1',
                    'label' => self::getCheckboxText($form['id']),
                    'name' => 'wpgdprc'
                )
            ),
            'wpgdprc' => true
        );
        \GFAPI::update_form($form, $form['id']);
    }

    /**
     * @param array $form
     */
    public function removeField($form = array()) {
        foreach ($form['fields'] as $index => $field) {
            if ($field['wpgdprc'] == true) {
                unset($form['fields'][$index]);
            }
        }
        \GFAPI::update_form($form, $form['id']);
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
     * @param int $formId
     * @return string
     */
    public function getCheckboxText($formId = 0) {
        if (!empty($formId)) {
            $texts = $this->getFormTexts();
            if (!empty($texts[$formId])) {
                return esc_html($texts[$formId]);
            }
        }
        return Integrations::getCheckboxText();
    }
}