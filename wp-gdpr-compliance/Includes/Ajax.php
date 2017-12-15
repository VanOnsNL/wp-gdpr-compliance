<?php

namespace WPGDPRC\Includes;

/**
 * Class Ajax
 * @package WPGDPRC\Includes
 */
class Ajax {
    /**
     * Ajax constructor.
     */
    public function __construct() {
        add_action('wp_ajax_wpgdprc_process_action', array($this, 'processAction'));
    }

    public function processAction() {
        check_ajax_referer('wpgdprc', 'security');

        $output = array(
            'error' => '',
            'redirect' => false
        );
        $data = (isset($_POST['data']) && (is_array($_POST['data']) || is_string($_POST['data']))) ? $_POST['data'] : false;
        if (is_string($data)) {
            $data = json_decode(stripslashes($data), true);
        }
        $type = (isset($data['type']) && is_string($data['type'])) ? esc_html($data['type']) : false;

        if (!$data) {
            $output['error'] = __('Missing data.', WP_GDPR_C_SLUG);
        }

        if (!$type) {
            $output['error'] = __('Missing type.', WP_GDPR_C_SLUG);
        }

        if (empty($output['error'])) {
            switch ($type) {
                case 'save_setting' :
                    $option = (isset($data['option']) && is_string($data['option'])) ? esc_html($data['option']) : false;
                    $value = (isset($data['value'])) ? self::sanitizeValue($data['value']) : false;
                    $append = (isset($data['append'])) ? filter_var($data['append'], FILTER_VALIDATE_BOOLEAN) : false;

                    if (!$option) {
                        $output['error'] = __('Missing option name.', WP_GDPR_C_SLUG);
                    }

                    if (!isset($data['value'])) {
                        $output['error'] = __('Missing value.', WP_GDPR_C_SLUG);
                    }

                    // Let's do this!
                    if (empty($output['error'])) {
                        if ($append) {
                            $values = (array) get_option($option, array());
                            if (isset($values[$value])) {
                                unset($values[$value]);
                            } else {
                                $values[] = $value;
                            }
                            $value = $values;
                        }
                        $callback = update_option($option, $value);
                        if (!$callback) {
                            $output['error'] = __('Something went wrong while saving this setting. Please try again!', WP_GDPR_C_SLUG);
                        } else {
                            /*
                             * TODO: Trigger this to do stuff after enabling/disabling an option, such as adding the Contact Form 7 crap.
                             */
                            do_action($option, $value);
                        }
                    }
                    break;
            }
        }

        header('Content-type: application/json');
        echo json_encode($output);
        die();
    }

    /**
     * @param $value
     * @return mixed
     */
    private static function sanitizeValue($value) {
        if (is_numeric($value)) {
            $value = intval($value);
        }
        if (is_string($value)) {
            $value = esc_html($value);
        }
        return $value;
    }
}