<?php
/**
 * Connection Settings
 * @package     Woocommerce Purolator Small
 * @author      <https://eniture.com/>
 * @copyright   Copyright (c) 2017, Eniture
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * purolator Small Connection Settings Tab Class
 */
class purolator_Small_Connection_Settings
{

    /**
     * Connection Settings Fields
     * @return array
     */

    public function purolator_small_con_setting()
    {
        echo '<div class="purolator_small_connection_section">';
        $settings = array(
            'section_title_purolator_small' => array(
                'name' => __('', 'woocommerce-settings-purolator_small'),
                'type' => 'title',
                'desc' => '<br> ',
                'id' => 'purolator_small_connection_title',
            ),

            'bill_acc_number_purolator_small' => array(
                'name' => __('Billing Account Number ', 'woocommerce-settings-purolator_small'),
                'type' => 'text',
                'placeholder' => 'Billing Account Number',
                'desc' => __('', 'woocommerce-settings-purolator_small'),
                'id' => 'purolator_small_billing_account_number'
            ),

            'reg_acc_number_purolator_small' => array(
                'name' => __('Registered Account Number ', 'woocommerce-settings-purolator_small'),
                'type' => 'text',
                'placeholder' => 'Registered Account Number',
                'desc' => __('', 'woocommerce-settings-purolator_small'),
                'id' => 'purolator_small_registered_account_number'
            ),

            'reg_address city_purolator_small' => array(
                'name' => __('Registered Address ', 'woocommerce-settings-purolator_small'),
                'type' => 'text',
                'placeholder' => 'City',
                'desc' => __('', 'woocommerce-settings-purolator_small'),
                'id' => 'purolator_small_registered_city'
            ),
            'reg_address_state_purolator_small' => array(
                'class' => 'reg_address_purolator_small',
                'name' => __('', 'woocommerce-settings-purolator_small'),
                'type' => 'text',
                'placeholder' => 'State',
                'desc' => __('', 'woocommerce-settings-purolator_small'),
                'id' => 'purolator_small_registered_state'
            ),
            'reg_address_zip_purolator_small' => array(
                'class' => 'reg_address_purolator_small',
                'name' => __('', 'woocommerce-settings-purolator_small'),
                'type' => 'text',
                'placeholder' => 'Zip',
                'desc' => __('', 'woocommerce-settings-purolator_small'),
                'id' => 'purolator_small_registered_zip'
            ),

            'pro_key_purolator_small' => array(
                'name' => __('Production Key ', 'woocommerce-settings-purolator_small'),
                'type' => 'text',
                'placeholder' => 'Production Key',
                'desc' => __('', 'woocommerce-settings-purolator_small'),
                'id' => 'purolator_small_pro_key'
            ),
            'pro_key_pass_purolator_small' => array(
                'name' => __('Production Key Password ', 'woocommerce-settings-purolator_small'),
                'type' => 'text',
                'placeholder' => 'Production Key Password',
                'desc' => __('', 'woocommerce-settings-purolator_small'),
                'id' => 'purolator_small_pro_key_pass'
            ),
            'licence_key_purolator_small' => array(
                'name' => __('Eniture API Key ', 'woocommerce-settings-purolator_small'),
                'type' => 'text',
                'placeholder' => 'Eniture API Key',
                'desc' => __('Obtain a Eniture API Key from <a href="https://eniture.com/products/" target="_blank" >eniture.com </a>', 'woocommerce-settings-purolator_small'),
                'id' => 'purolator_small_licence_key'
            ),

            'section_end_purolator_small' => array(
                'type' => 'sectionend',
                'id' => 'purolator_small_licence_key'
            ),

        );

        return $settings;

    }
}