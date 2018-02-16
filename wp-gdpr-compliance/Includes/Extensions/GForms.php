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
            if ($field['WP-GDPR_field'] == true) {
                return;
            }
        }
        $new_field = array(
            'type' => 'checkbox',
            'id' => (isset(end($form['fields'])['id']) ? ((int)end($form['fields'])['id']+1) : 1),
            'label' => 'GDPR',
            'WP-GDPR_field' => true,
            'isRequired' => true,
            'size' => 'medium',
            'choices' => array(
                array(
                    'text' => self::getCheckboxText($form['id']),
                    'value' => self::getCheckboxText($form['id']),
                    'isSelected' => false
                )
            ),
            'inputs' => array(
                array(
                    'id' => (isset(end($form['fields'])['id']) ? ((int)end($form['fields'])['id']+1) : 1).'.1',
                    'label' => self::getCheckboxText($form['id']),
                    'name' => ''
                )
            )
        );
        $form['fields'][] = $new_field;
        \GFAPI::update_form($form, $form['id']);
    }

    /**
     * @param array $form
     */
    public function removeField($form = array()) {
        foreach ($form['fields'] as $index => $field) {
            if ($field['WP-GDPR_field'] == true) {
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