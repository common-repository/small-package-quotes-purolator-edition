<?php

/**
 * Quote Settings
 * @package     Woocommerce Purolator Small
 * @author      <https://eniture.com/>
 * @copyright   Copyright (c) 2017, Eniture
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class For Quote Settings Tab
 */
class purolator_Small_Quote_Settings
{

    /**
     * Quote Setting Fields
     * @return array
     */
    function purolator_small_quote_settings_tab()
    {
        $disable_transit = "";
        $transit_package_required = "";

        $disable_hazardous = "";
        $hazardous_package_required = "";

        $action_transit = apply_filters('purolator_small_quotes_plans_suscription_and_features', 'transit_days');
        if (is_array($action_transit)) {
            $disable_transit = "disabled_me";
            $transit_package_required = apply_filters('purolator_small_plans_notification_link', $action_transit);
        }

        $action_hazardous = apply_filters('purolator_small_quotes_plans_suscription_and_features', 'hazardous_material');
        if (is_array($action_hazardous)) {
            $disable_hazardous = "disabled_me";
            $hazardous_package_required = apply_filters('purolator_small_plans_notification_link', $action_hazardous);
        }

        //**Plan_Validation: Cut Off Time & Ship Date Offset
        $disable_cutOffTime_shipDateOffset = "";
        $cutOffTime_shipDateOffset_package_required = "";
        $action_cutOffTime_shipDateOffset = apply_filters('purolator_small_quotes_plans_suscription_and_features', 'cutOffTime_shipDateOffset');
        if (is_array($action_cutOffTime_shipDateOffset)) {
            $disable_cutOffTime_shipDateOffset = "disabled_me";
            $cutOffTime_shipDateOffset_package_required = apply_filters('purolator_small_plans_notification_link', $action_cutOffTime_shipDateOffset);
        }

        $package_type_options = [
            'ship_alone' => __('Quote each item as shipping as its own package', 'woocommerce-settings-purolator_small_quotes'),
            'ship_combine_and_alone' => __('Combine the weight of all items without dimensions and quote them as one package while quoting each item with dimensions as shipping as its own package', 'woocommerce-settings-purolator_small_quotes'),
            'ship_one_package_70' => __('Quote shipping as if all items ship as one package up to 70 LB each', 'woocommerce-settings-purolator_small_quotes'),
            'ship_one_package_150' => __('Quote shipping as if all items ship as one package up to 150 LB each', 'woocommerce-settings-purolator_small_quotes'),
        ];
        $package_type_default = 'ship_alone';
        $purolator_small_packaging_type = get_option("purolator_small_packaging_type");
        if(!empty($purolator_small_packaging_type) && $purolator_small_packaging_type == 'old'){
            $package_type_default = 'eniture_packaging';
            $package_type_options['eniture_packaging'] = __('Use the default Eniture packaging algorithm', 'woocommerce-settings-purolator_small_quotes');
        }

        echo '<div class="purolator_small_quote_section">';
        $settings = array(
            'purolator_small_services' => array(
                'name' => __('Purolator Services', 'woocommerce-settings-purolator_small_quotes'),
                'type' => 'title',
                'desc' => '<div><p><strong>Note! The service selected will display in the cart if they are available for the origin and destination address, and if the Purolator Rate Estimate API has been enabled for the corresponding shipping zone. </strong></p></div>',
                'id' => 'purolator_small_services'
            ),
            'purolator_small_domastic_services' => array(
                'name' => __('Canada to Canada', 'woocommerce-settings-purolator_small_quotes'),
                'type' => 'checkbox',
                'id' => 'purolator_small_domastic_services',
                'class' => 'dom_int_srvc_hdng'
            ),
            'purolator_small_int_services' => array(
                'name' => __('Canada to U.S', 'woocommerce-settings-purolator_small_quotes'),
                'type' => 'checkbox',
                'id' => 'purolator_small_int_services',
                'class' => 'dom_int_srvc_hdng'
            ),
            'purolator_small_international_services' => array(
                'name' => __('International Services', 'woocommerce-settings-purolator_small_quotes'),
                'type' => 'checkbox',
                'id' => 'purolator_small_international_services',
                'class' => 'dom_int_srvc_hdng'
            ),
            'purolator_small_select_all_services' => array(
                'name' => __('Select All', 'woocommerce-settings-purolator_small_quotes'),
                'type' => 'checkbox',
                'id' => 'purolator_small_ca_to_ca_select_all',
                'class' => 'purolator_small_all_services',
            ),
            'purolator_small_select_all_int_services' => array(
                'name' => __('Select All', 'woocommerce-settings-purolator_small_quotes'),
                'type' => 'checkbox',
                'id' => 'purolator_small_ca_to_us_select_all',
                'class' => 'purolator_small_all_int_services',
            ),
            'purolator_small_select_all_ww_services' => array(
                'name' => __('Select All', 'woocommerce-settings-purolator_small_quotes'),
                'type' => 'checkbox',
                'id' => 'wc_settings_select_ww_all',
                'class' => 'purolator_small_all_ww_services',
            ),
            'purolator_small_express' => array(
                'name' => __('Purolator Express', 'woocommerce-settings-purolator_small_quotes'),
                'type' => 'checkbox',
                'desc' => __('', 'woocommerce-settings-purolator_small_quotes'),
                'id' => 'purolator_small_express',
                'class' => 'purolator_small_quotes_services purolator_small_domestic_quote_service',
            ),
            'purolator_small_ground_us' => array(
                'name' => __('Purolator Ground U.S', 'woocommerce-settings-purolator_small_quotes'),
                'type' => 'checkbox',
                'desc' => __('', 'woocommerce-settings-purolator_small_quotes'),
                'id' => 'purolator_small_ground_us',
                'class' => 'purolator_small_int_quotes_services',
            ),
            'purolator_small_express_inter' => array(
                'name' => __('Purolator Express International', 'woocommerce-settings-purolator_small_quotes'),
                'type' => 'checkbox',
                'desc' => __('', 'woocommerce-settings-purolator_small_quotes'),
                'id' => 'purolator_small_express_inter',
                'class' => 'purolator_small_ww_quotes_services',
            ),
            'purolator_small_express_markup' => array(
                'name' => __('', 'purolator_small_express_markup'),
                'type' => 'text',
                'desc' => __('Markup (e.g. Currency: 1.00 or Percentage: 5.0%)', 'woocommerce-settings-purolator_small_express_markup'),
                'placeholder' => 'Markup',
                'id' => 'purolator_small_express_markup',
                'class' => 'purolator_small_quotes_services_markup',
            ),
            'purolator_small_ground_us_markup' => array(
                'name' => __('', 'purolator_small_ground_us_markup'),
                'type' => 'text',
                'desc' => __('Markup (e.g. Currency: 1.00 or Percentage: 5.0%)', 'woocommerce-settings-purolator_small_ground_us_markup'),
                'placeholder' => 'Markup',
                'id' => 'purolator_small_ground_us_markup',
                'class' => 'purolator_small_quotes_services_markup',
            ),
            'purolator_small_express_inter_markup' => array(
                'name' => __('', 'purolator_small_express_inter_markup'),
                'type' => 'text',
                'desc' => __('Markup (e.g. Currency: 1.00 or Percentage: 5.0%)', 'woocommerce-settings-purolator_small_express_inter_markup'),
                'placeholder' => 'Markup',
                'id' => 'purolator_small_express_inter_markup',
                'class' => 'purolator_small_quotes_services_markup',
            ),
            'purolator_small_express_9' => array(
                'name' => __('Purolator Express 9AM', 'woocommerce-settings-purolator_small_quotes'),
                'type' => 'checkbox',
                'desc' => __('', 'woocommerce-settings-purolator_small_quotes'),
                'id' => 'purolator_small_express_9',
                'class' => 'purolator_small_quotes_services purolator_small_domestic_quote_service',
            ),
            'purolator_small_express_us' => array(
                'name' => __('Purolator Express U.S', 'woocommerce-settings-purolator_small_quotes'),
                'type' => 'checkbox',
                'desc' => __('', 'woocommerce-settings-purolator_small_quotes'),
                'id' => 'purolator_small_express_us',
                'class' => 'purolator_small_int_quotes_services',
            ),

            'purolator_small_express_inter_12' => array(
                'name' => __('Purolator Express International 12:00', 'woocommerce-settings-purolator_small_quotes'),
                'type' => 'checkbox',
                'desc' => __('', 'woocommerce-settings-purolator_small_quotes'),
                'id' => 'purolator_small_express_inter_12',
                'class' => 'purolator_small_ww_quotes_services',
            ),
            'purolator_small_express_9_markup' => array(
                'name' => __('', 'purolator_small_express_9_markup'),
                'type' => 'text',
                'desc' => __('Markup (e.g. Currency: 1.00 or Percentage: 5.0%)', 'woocommerce-settings-purolator_small_express_9_markup'),
                'placeholder' => 'Markup',
                'id' => 'purolator_small_express_9_markup',
                'class' => 'purolator_small_quotes_services_markup',
            ),
            'purolator_small_express_us_markup' => array(
                'name' => __('', 'purolator_small_express_us_markup'),
                'type' => 'text',
                'desc' => __('Markup (e.g. Currency: 1.00 or Percentage: 5.0%)', 'woocommerce-settings-purolator_small_express_us_markup'),
                'placeholder' => 'Markup',
                'id' => 'purolator_small_express_us_markup',
                'class' => 'purolator_small_quotes_services_markup',
            ),
            'purolator_small_express_inter_12_markup' => array(
                'name' => __('', 'purolator_small_express_inter_12_markup'),
                'type' => 'text',
                'desc' => __('Markup (e.g. Currency: 1.00 or Percentage: 5.0%)', 'woocommerce-settings-purolator_small_express_inter_12_markup'),
                'placeholder' => 'Markup',
                'id' => 'purolator_small_express_inter_12_markup',
                'class' => 'purolator_small_quotes_services_markup',
            ),
            'purolator_small_express_10' => array(
                'name' => __('Purolator Express 10:30AM', 'woocommerce-settings-purolator_small_quotes'),
                'type' => 'checkbox',
                'desc' => __('', 'woocommerce-settings-purolator_small_quotes'),
                'id' => 'purolator_small_express_10',
                'class' => 'purolator_small_quotes_services purolator_small_domestic_quote_service',
            ),
            'purolator_small_express_us_9am' => array(
                'name' => __('Purolator Express U.S 9AM', 'woocommerce-settings-purolator_small_quotes'),
                'type' => 'checkbox',
                'desc' => __('', 'woocommerce-settings-purolator_small_quotes'),
                'id' => 'purolator_small_express_us_9am',
                'class' => 'purolator_small_int_quotes_services',
            ),
            'purolator_small_2day_am' => array(
                'name' => __('', 'woocommerce-settings-purolator_small_quotes'),
                'type' => 'checkbox',
                'desc' => __('', 'woocommerce-settings-purolator_small_quotes'),
                'id' => 'purolator_small_2_day_AM',
                'class' => 'purolator_small_quotes_services hide_checkbox',
            ),
            'purolator_small_express_10_markup' => array(
                'name' => __('', 'purolator_small_express_10_markup'),
                'type' => 'text',
                'desc' => __('Markup (e.g. Currency: 1.00 or Percentage: 5.0%)', 'woocommerce-settings-purolator_small_express_10_markup'),
                'placeholder' => 'Markup',
                'id' => 'purolator_small_express_10_markup',
                'class' => 'purolator_small_quotes_services_markup',
            ),
            'purolator_small_express_us_9am_markup' => array(
                'name' => __('', 'purolator_small_express_us_9am_markup'),
                'type' => 'text',
                'desc' => __('Markup (e.g. Currency: 1.00 or Percentage: 5.0%)', 'woocommerce-settings-purolator_small_express_us_9am_markup'),
                'placeholder' => 'Markup',
                'id' => 'purolator_small_express_us_9am_markup',
                'class' => 'purolator_small_quotes_services_markup',
            ),
            'purolator_small_2day_am_markup' => array(
                'name' => __('', 'purolator_small_2day_am_markup'),
                'type' => 'text',
                'desc' => __('Markup (e.g. Currency: 1.00 or Percentage: 5.0%)', 'woocommerce-settings-purolator_small_2day_am_markup'),
                'placeholder' => 'Markup',
                'id' => 'purolator_small_2day_am_markup',
                'class' => 'purolator_small_quotes_services_markup hidden_class',
            ),

            'purolator_small_ground' => array(
                'name' => __('Purolator Ground', 'woocommerce-settings-purolator_small_quotes'),
                'type' => 'checkbox',
                'desc' => __('', 'woocommerce-settings-purolator_small_quotes'),
                'id' => 'purolator_small_ground',
                'class' => 'purolator_small_quotes_services purolator_small_domestic_quote_service',
            ),
            'purolator_small_express_us_10am' => array(
                'name' => __('Purolator Express U.S 10:30AM', 'woocommerce-settings-purolator_small_quotes'),
                'type' => 'checkbox',
                'desc' => __('', 'woocommerce-settings-purolator_small_quotes'),
                'id' => 'purolator_small_express_us_10am',
                'class' => 'purolator_small_int_quotes_services',
            ),
            'purolator_small_priority' => array(
                'name' => __('', 'woocommerce-settings-purolator_small_quotes'),
                'type' => 'checkbox',
                'desc' => __('', 'woocommerce-settings-purolator_small_quotes'),
                'id' => 'purolator_small_int_priority',
                'class' => 'purolator_small_quotes_services hide_checkbox',
            ),
            'purolator_small_ground_markup' => array(
                'name' => __('', 'purolator_small_ground_markup'),
                'type' => 'text',
                'desc' => __('Markup (e.g. Currency: 1.00 or Percentage: 5.0%)', 'woocommerce-settings-purolator_small_ground_markup'),
                'placeholder' => 'Markup',
                'id' => 'purolator_small_ground_markup',
                'class' => 'purolator_small_quotes_services_markup',
            ),
            'purolator_small_express_us_10am_markup' => array(
                'name' => __('', 'purolator_small_express_us_10am_markup'),
                'type' => 'text',
                'desc' => __('Markup (e.g. Currency: 1.00 or Percentage: 5.0%)', 'woocommerce-settings-purolator_small_express_us_10am_markup'),
                'placeholder' => 'Markup',
                'id' => 'purolator_small_express_us_10am_markup',
                'class' => 'purolator_small_quotes_services_markup',
            ),
            'purolator_small_priority_markup' => array(
                'name' => __('', 'purolator_small_priority_markup'),
                'type' => 'text',
                'desc' => __('Markup (e.g. Currency: 1.00 or Percentage: 5.0%)', 'woocommerce-settings-purolator_small_priority_markup'),
                'placeholder' => 'Markup',
                'id' => 'purolator_small_priority_markup',
                'class' => 'purolator_small_quotes_services_markup hidden_class',
            ),

            'purolator_small_ground_90' => array(
                'name' => __('Purolator Ground 9AM', 'woocommerce-settings-purolator_small_quotes'),
                'type' => 'checkbox',
                'desc' => __('', 'woocommerce-settings-purolator_small_quotes'),
                'id' => 'purolator_small_ground_90',
                'class' => 'purolator_small_quotes_services purolator_small_domestic_quote_service',
            ),
            'purolator_small_pr_dist' => array(
                'name' => __('', 'woocommerce-settings-purolator_small_quotes'),
                'type' => 'checkbox',
                'desc' => __('', 'woocommerce-settings-purolator_small_quotes'),
                'id' => 'purolator_small_int_priority_distribution',
                'class' => 'purolator_small_quotes_services hide_checkbox',
            ),
            'purolator_small_fst_overnight' => array(
                'name' => __('', 'woocommerce-settings-purolator_small_quotes'),
                'type' => 'checkbox',
                'desc' => __('', 'woocommerce-settings-purolator_small_quotes'),
                'id' => 'purolator_small_first_overnight',
                'class' => 'purolator_small_quotes_services hide_checkbox',
            ),
            'purolator_small_ground_90_markup' => array(
                'name' => __('', 'purolator_small_ground_90_markup'),
                'type' => 'text',
                'desc' => __('Markup (e.g. Currency: 1.00 or Percentage: 5.0%)', 'woocommerce-settings-purolator_small_ground_90_markup'),
                'placeholder' => 'Markup',
                'id' => 'purolator_small_ground_90_markup',
                'class' => 'purolator_small_quotes_services_markup',
            ),
            'purolator_small_pr_dist_markup' => array(
                'name' => __('', 'purolator_small_pr_dist_markup'),
                'type' => 'text',
                'desc' => __('Markup (e.g. Currency: 1.00 or Percentage: 5.0%)', 'woocommerce-settings-purolator_small_pr_dist_markup'),
                'placeholder' => 'Markup',
                'id' => 'purolator_small_pr_dist_markup',
                'class' => 'purolator_small_quotes_services_markup hidden_class',
            ),
            'purolator_small_fst_overnight_markup' => array(
                'name' => __('', 'purolator_small_fst_overnight_markup'),
                'type' => 'text',
                'desc' => __('Markup (e.g. Currency: 1.00 or Percentage: 5.0%)', 'woocommerce-settings-purolator_small_fst_overnight_markup'),
                'placeholder' => 'Markup',
                'id' => 'purolator_small_fst_overnight_markup',
                'class' => 'purolator_small_quotes_services_markup hidden_class',
            ),
            'purolator_small_ground_100' => array(
                'name' => __('Purolator Ground 10:30AM', 'woocommerce-settings-purolator_small_quotes'),
                'type' => 'checkbox',
                'desc' => __('', 'woocommerce-settings-purolator_small_quotes'),
                'id' => 'purolator_small_ground_100',
                'class' => 'purolator_small_quotes_services purolator_small_domestic_quote_service',
            ),
            'unable_retrieve_shipping_clear_wwe_small_packages' => array(
                'name' => __('', 'woocommerce-settings-purolator_small_quotes'),
                'type' => 'checkbox',
                'desc' => __('', 'woocommerce-settings-purolator_small_quotes'),
                'id' => 'purolator_small_quotes_services_empty',
                'class' => 'purolator_small_quotes_services hide_checkbox',
            ),
            'purolator_small_int_distribution' => array(
                'name' => __('', 'woocommerce-settings-purolator_small_quotes'),
                'type' => 'checkbox',
                'desc' => __('', 'woocommerce-settings-purolator_small_quotes'),
                'id' => 'purolator_small_int_distribution_freight',
                'class' => 'purolator_small_quotes_services hide_checkbox',
            ),
            'purolator_small_ground_100_markup' => array(
                'name' => __('', 'purolator_small_ground_100_markup'),
                'type' => 'text',
                'desc' => __('Markup (e.g. Currency: 1.00 or Percentage: 5.0%)', 'woocommerce-settings-purolator_small_ground_100_markup'),
                'placeholder' => 'Markup',
                'id' => 'purolator_small_ground_100_markup',
                'class' => 'purolator_small_quotes_services_markup',
            ),
            'unable_retrieve_shipping_clear_wwe_small_packages_markup' => array(
                'name' => __('', 'unable_retrieve_shipping_clear_wwe_small_packages_markup'),
                'type' => 'text',
                'desc' => __('Markup (e.g. Currency: 1.00 or Percentage: 5.0%)', 'woocommerce-settings-unable_retrieve_shipping_clear_wwe_small_packages_markup'),
                'placeholder' => 'Markup',
                'id' => 'unable_retrieve_shipping_clear_wwe_small_packages_markup',
                'class' => 'purolator_small_quotes_services_markup hidden_class',
            ),
            'purolator_small_int_distribution_markup' => array(
                'name' => __('', 'purolator_small_int_distribution_markup'),
                'type' => 'text',
                'desc' => __('Markup (e.g. Currency: 1.00 or Percentage: 5.0%)', 'woocommerce-settings-purolator_small_int_distribution_markup'),
                'placeholder' => 'Markup',
                'id' => 'purolator_small_int_distribution_markup',
                'class' => 'purolator_small_quotes_services_markup hidden_class',
            ),
            'price_sort_purolator' => array(
                'name' => __("Don't sort shipping methods by price  ", 'woocommerce-settings-abf_quotes'),
                'type' => 'checkbox',
                'desc' => 'By default, the plugin will sort all shipping methods by price in ascending order.',
                'id' => 'shipping_methods_do_not_sort_by_price'
            ),

            // Package rating method when Standard Box Sizes isn't in use
            'purolator_small_packaging_method_label' => array(
                'name' => __('Package rating method when Standard Box Sizes isn\'t in use', 'woocommerce-settings-purolator_small_quotes'),
                'type' => 'text',
                'id' => 'purolator_small_packaging_method_label'
            ),
            'purolator_small_packaging_method' => array(
                'name' => __('', 'woocommerce-settings-purolator_small_quotes'),
                'type' => 'radio',
                'default' => $package_type_default,
                'options' => $package_type_options,
                'id' => 'purolator_small_packaging_method',
            ),

            'service_purolator_small_estimates_title' => array(
                'name' => __('Delivery Estimate Options ', 'woocommerce-settings-en_woo_addons_packages_quotes'),
                'type' => 'text',
                'desc' => '',
                'id' => 'service_purolator_small_estimates_title'
            ),
            'dont_show_estimates_purolator_small' => array(
                'name' => __('', 'woocommerce-settings-purolator_small_quotes'),
                'type' => 'radio',
                'class' => "",
                'default' => "dont_show_estimates",
                'options' => array(
                    'dont_show_estimates' => __("Don't display delivery estimates.", 'woocommerce'),
                    'delivery_days' => __('Display estimated number of days until delivery.', 'woocommerce'),
                    'delivery_date' => __('Display estimated delivery date.', 'woocommerce'),
                ),
                'id' => 'purolator_small_delivery_estimates',
            ),

            //**Start: Cut Off Time & Ship Date Offset
            'cutOffTime_shipDateOffset_purolator_small' => array(
                'name' => __('Cut Off Time & Ship Date Offset ', 'woocommerce-settings-en_woo_addons_packages_quotes'),
                'type' => 'text',
                'class' => 'hidden',
                'desc' => $cutOffTime_shipDateOffset_package_required,
                'id' => 'purolator_small_cutOffTime_shipDateOffset'
            ),
            'orderCutoffTime_purolator_small' => array(
                'name' => __('Order Cut Off Time ', 'woocommerce-settings-purolator_small_freight_orderCutoffTime'),
                'type' => 'text',
                'placeholder' => '--:-- --',
                'desc' => 'Enter the cut off time (e.g. 2.00) for the orders. Orders placed after this time will be quoted as shipping the next business day.',
                'id' => 'purolator_small_orderCutoffTime',
                'class' => $disable_cutOffTime_shipDateOffset,
            ),
            'shipmentOffsetDays_purolator_small' => array(
                'name' => __('Fulfilment Offset Days ', 'woocommerce-settings-purolator_small_shipmentOffsetDays'),
                'type' => 'text',
                'desc' => 'The number of days the ship date needs to be moved to allow the processing of the order.',
                'placeholder' => 'Fulfilment Offset Days, e.g. 2',
                'id' => 'purolator_small_shipmentOffsetDays',
                'class' => $disable_cutOffTime_shipDateOffset,
            ),
            'all_shipment_days_purolator_small' => array(
                'name' => __("What days do you ship orders?", 'woocommerce-settings-ups_small_quotes'),
                'type' => 'checkbox',
                'desc' => 'Select All',
                'class' => "all_shipment_days_purolator_small $disable_cutOffTime_shipDateOffset",
                'id' => 'all_shipment_days_purolator_small'
            ),
            'monday_shipment_day_purolator_small' => array(
                'name' => __("", 'woocommerce-settings-ups_small_quotes'),
                'type' => 'checkbox',
                'desc' => 'Monday',
                'class' => "purolator_small_shipment_day $disable_cutOffTime_shipDateOffset",
                'id' => 'monday_shipment_day_purolator_small'
            ),
            'tuesday_shipment_day_purolator_small' => array(
                'name' => __("", 'woocommerce-settings-ups_small_quotes'),
                'type' => 'checkbox',
                'desc' => 'Tuesday',
                'class' => "purolator_small_shipment_day $disable_cutOffTime_shipDateOffset",
                'id' => 'tuesday_shipment_day_purolator_small'
            ),
            'wednesday_shipment_day_purolator_small' => array(
                'name' => __("", 'woocommerce-settings-ups_small_quotes'),
                'type' => 'checkbox',
                'desc' => 'Wednesday',
                'class' => "purolator_small_shipment_day $disable_cutOffTime_shipDateOffset",
                'id' => 'wednesday_shipment_day_purolator_small'
            ),
            'thursday_shipment_day_purolator_small' => array(
                'name' => __("", 'woocommerce-settings-ups_small_quotes'),
                'type' => 'checkbox',
                'desc' => 'Thursday',
                'class' => "purolator_small_shipment_day $disable_cutOffTime_shipDateOffset",
                'id' => 'thursday_shipment_day_purolator_small'
            ),
            'friday_shipment_day_purolator_small' => array(
                'name' => __("", 'woocommerce-settings-ups_small_quotes'),
                'type' => 'checkbox',
                'desc' => 'Friday',
                'class' => "purolator_small_shipment_day $disable_cutOffTime_shipDateOffset",
                'id' => 'friday_shipment_day_purolator_small'
            ),
            // Start Transit days            
            'ground_transit_label_title' => array(
                'name' => __('Ground transit time restriction', 'woocommerce-settings-purolator_small'),
                'type' => 'text',
                'class' => 'hidden',
                'desc' => $transit_package_required,
                'id' => 'ground_transit_label'
            ),
            'restrict_days_transit_package_purolator_small' => array(
                'name' => __('Enter the number of transit days to restrict ground service to. Leave blank to disable this feature.', 'ground-transit-settings-ground_transit'),
                'type' => 'text',
                'class' => $disable_transit,
                'id' => 'restrict_days_transit_package_purolator_small'
            ),
            'restrict_transit_purolator_small_packages' => array(
                'name' => __('', 'woocommerce-settings-purolator_small'),
                'type' => 'radio',
                'class' => $disable_transit,
                'options' => array(
                    'BusinessDaysInTransit' => __('Restrict by the carrier\'s in transit days metric.', 'woocommerce'),
                    'CalenderDaysInTransit' => __('Restrict by the calendar days in transit.', 'woocommerce'),
                ),
                'id' => 'restrict_calendar_transit_small_packages_purolator',
            ),
            // End Transit days 
//          Use my standard box sizes notification
            'avaibility_box_sizing' => array(
                'name' => __('Use my standard box sizes', 'woocommerce-settings-wwe_small_packages_quotes'),
                'type' => 'text',
                'class' => 'hidden',
                'desc' => "Click <a target='_blank' href='https://eniture.com/woocommerce-standard-box-sizes/'>here</a> to add the box sizing module (<a target='_blank' href='https://eniture.com/woocommerce-standard-box-sizes/#documentation'>Learn more</a>)",
                'id' => 'avaibility_box_sizing'
            ),
            /*
             * FedEx Residentail Delivery, Handeling Fee And Hazardous Fee
             */
            'purolator_small_hazardous_title' => array(
                'name' => __('Hazardous material settings', 'woocommerce-settings-purolator_small'),
                'type' => 'text',
                'class' => 'hidden',
                'desc' => $hazardous_package_required,
                'id' => 'purolator_small_hazardous_title'
            ),
            'purolator_small_hz_fee' => array(
                'name' => __('Hazardous Material Fee ', 'woocommerce-settings-purolator_small_quotes'),
                'type' => 'text',
                'class' => $disable_hazardous,
                'desc' => '<span class="desc_text_style">Enter an amount, e.g 3.75 or Leave blank to disable.</span>',
                'id' => 'purolator_small_hazardous_fee'
            ),
            'purolator_small_hand_free' => array(
                'name' => __('Handling Fee / Markup ', 'woocommerce-settings-purolator_small_quotes'),
                'type' => 'text',
                'desc' => '<span class="desc_text_style">Amount excluding tax. Enter an amount, e.g 3.75, or a percentage, e.g, 5%. Leave blank to disable.</span>',
                'id' => 'purolator_small_hand_fee_mark_up'
            ),
            'purolator_small_enable_logs' => array(
                'name' => __("Enable Logs  ", 'woocommerce-settings-unishepper_small_quotes'),
                'type' => 'checkbox',
                'desc' => 'When checked, the Logs page will contain up to 25 of the most recent transactions.',
                'id' => 'purolator_small_enable_logs'
            ),
            'allow_other_plugins_purolator_small' => array(
                'name' => __('Allow other plugins to show quotes ', 'woocommerce-settings-purolator_small_quotes'),
                'type' => 'select',
                'default' => '3',
                'desc' => __('', 'woocommerce-settings-purolator_small_quotes'),
                'id' => 'allow_other_plugins_purolator_small',
                'options' => array(
                    'no' => __('NO', 'NO'),
                    'yes' => __('YES', 'YES')
                )
            ),
            'unable_retrieve_shipping_clear_purolator_small' => array(
                'title' => __('', 'woocommerce'),
                'name' => __('', 'woocommerce-settings-purolator-small-quotes'),
                'desc' => '',
                'id' => 'wc_unable_retrieve_shipping_clear_purolator_small',
                'css' => '',
                'default' => '',
                'type' => 'title',
            ),
            'unable_retrieve_shipping_purolator_small' => array(
                'name' => __('Checkout options if the plugin fails to return a rate ', 'woocommerce-settings-purolator-small-quotes'),
                'type' => 'title',
                'desc' => 'When the plugin is unable to retrieve shipping quotes and no other shipping options are provided by an alternative source:',
                'id' => 'wc_settings_unable_retrieve_shipping_purolator_small'
            ),
            'pervent_checkout_proceed_purolator_small' => array(
                'name' => __('', 'woocommerce-settings-purolator-small-quotes'),
                'type' => 'radio',
                'id' => 'pervent_checkout_proceed_purolator_small_packages',
                'options' => array(
                    'allow' => __('', 'woocommerce'),
                    'prevent' => __('', 'woocommerce'),
                ),
                'id' => 'wc_pervent_proceed_checkout_eniture',
            ),

            'section_end_quote' => array(
                'type' => 'sectionend',
                'id' => 'wc_settings_quote_section_end'
            )
        );
        return $settings;
    }

}
