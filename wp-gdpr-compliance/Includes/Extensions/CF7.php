<?php

namespace WPGDPRC\Includes\Extensions;

/**
 * Class CF7
 * @package WPGDPRC\Includes\Extensions\CF7
 */
class CF7 {
    /** @var null */
    private static $instance = null;

    /**
     * @return null|CF7
     */
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Add [WPGDPRC] string to all forms
     */
    public function addFormTagToForms() {
        $forms = CF7::getInstance()->getForms();
        foreach ($forms as $form) {
            $output = get_post_meta($form, '_form', true);
            if (!preg_match('/(\[wpgdprc?.*\])/', $output)) {
                $pattern = '/(\[submit?.*\])/';
                preg_match($pattern, $output, $matches);
                if (!empty($matches)) {
                    $output = preg_replace($pattern, "[wpgdprc]\n\n" . $matches[0], $output);
                } else {
                    $output = $output . "\n\n[wpgdprc]";
                }
                update_post_meta($form, '_form', $output);
            }
        }
    }

    public function addFormTags() {
        wpcf7_add_form_tag(
            'wpgdprc',
            array($this, 'addFormTagHandler')
        );
    }

    /**
     * @param \WPCF7_FormTag $tag
     * @return string
     */
    public function addFormTagHandler(\WPCF7_FormTag $tag) {
        $output = '';
        switch ($tag['type']) {
            case 'wpgdprc' :
                $tag->name = 'wpgdprc';
                $label = 'Ja, ik wil';
                $class = wpcf7_form_controls_class($tag->type, 'wpcf7-validates-as-required');
                $validation_error = wpcf7_get_validation_error($tag->name);
                if ($validation_error) {
                    $class .= ' wpcf7-not-valid';
                }

                $label_first = $tag->has_option('label_first');

                $atts = wpcf7_format_atts(array(
                    'class' => $tag->get_class_option($class),
                    'id' => $tag->get_id_option(),
                ));
                $item_atts = wpcf7_format_atts(array(
                    'type' => 'checkbox',
                    'name' => $tag->name,
                    'value' => 1,
                    'tabindex' => $tag->get_option('tabindex', 'signed_int', true),
                    'aria-required' => 'true',
                    'aria-invalid' => ($validation_error) ? 'true' : 'false',
                ));

                if ($label_first) { // put label first, input last
                    $output = sprintf(
                        '<span class="wpcf7-list-item-label">%1$s</span><input %2$s />',
                        esc_html($label), $item_atts);
                } else {
                    $output = sprintf(
                        '<input %2$s /><span class="wpcf7-list-item-label">%1$s</span>',
                        esc_html($label), $item_atts);
                }
                $output = '<span class="wpcf7-list-item"><label>' . $output . '</label></span>';
                $output = sprintf(
                    '<span class="wpcf7-form-control-wrap %1$s"><span %2$s>%3$s</span>%4$s</span>',
                    sanitize_html_class($tag->name), $atts, $output, $validation_error
                );
                break;
        }
        return $output;
    }

    /**
     * @param \WPCF7_Validation $result
     * @param \WPCF7_FormTag $tag
     * @return \WPCF7_Validation
     */
    public function validateField(\WPCF7_Validation $result, \WPCF7_FormTag $tag) {
        switch ($tag['type']) {
            case 'wpgdprc' :
                $tag->name = 'wpgdprc';
                $name = $tag->name;
                $value = (isset($_POST[$name])) ? filter_var($_POST[$name], FILTER_VALIDATE_BOOLEAN) : false;
                if ($value === false) {
                    $result->invalidate($tag, wpcf7_get_message('invalid_required'));
                }
                break;
        }
        return $result;
    }

    /**
     * @return array
     */
    public function getForms() {
        return get_posts(array(
            'post_type' => 'wpcf7_contact_form',
            'posts_per_page' => -1,
            'fields' => 'ids'
        ));
    }
}