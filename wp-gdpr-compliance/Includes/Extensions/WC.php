<?php
namespace WPGDPRC\Includes\Extensions;


class WC {
    private static $instance = null;

    private function __construct() //Full static
    {
    }

    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }


    function my_custom_checkout_field( $checkout ) {

        echo '<div id="my-new-field"><h3>'.__('My Checkbox: ').'</h3>';

        woocommerce_form_field( 'gdpr_checkbox', array(
            'type'          => 'checkbox',
            'class'         => array('input-checkbox'),
            'label'         => __('I have read and agreed.'),
            'required'  => true,
        ), $checkout->get_value( 'my_checkbox' ));

        echo '</div>';
    }

    function my_custom_checkout_field_process() {
        global $woocommerce;

        // Check if set, if its not set add an error.
        if (!$_POST['gdpr_checkbox'])
            $woocommerce->add_error( __('Please agree to my checkbox.') );
    }

    function my_custom_checkout_field_update_order_meta( $order_id ) {
        if ($_POST['gdpr_checkbox']) update_post_meta( $order_id, 'My Checkbox', esc_attr($_POST['gdpr_checkbox']));
    }

}