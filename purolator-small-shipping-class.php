<?php

/**
 * Shipping Class
 * @package     Woocommerce Purolator Small
 * @author      <https://eniture.com/>
 * @copyright   Copyright (c) 2017, Eniture
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Initialization
 */
function purolator_small_init()
{
    if (!class_exists('WC_purolator_small')) {

        /**
         * purolator Small Shipping Calculation Class
         */
        class WC_purolator_small extends WC_Shipping_Method
        {
            /** global variable */
            public $smpkgFoundErr = array();

            /** global variable */
            public $smpkgQuoteErr = array();
            public $order_detail;
            public $is_autoresid;
            public $accessorials;
            public $helper_obj;
            public $instore_pickup_and_local_delivery;
            public $web_service_inst;
            public $VersionCompat;
            public $package_plugin;
            public $InstorPickupLocalDelivery;
            public $woocommerce_package_rates;
            public $quote_settings;
            public $en_not_returned_the_quotes = false;
            public $eniture_rates;
            public $minPrices;
            public $en_fdo_meta_data;

            /**
             * Woocommerce Shipping Field Attributes
             * @param $instance_id
             */
            public function __construct($instance_id = 0)
            {
                $this->id = 'purolator_small';
                $this->helper_obj = new En_Pur_Sml_Helper_Class();
                $this->instance_id = absint($instance_id);
                $this->method_title = __('Purolator');
                $this->method_description = __('shipping rates from Purolator.');
                $this->supports = array(
                    'shipping-zones',
                    'instance-settings',
                    'instance-settings-modal',
                );
                $this->enabled = "yes";
                $this->title = "Small Package Quotes - Purolator Edition";
                $this->init();
            }

            /**
             * Update purolator Small Woocommerce Shipping Settings
             */
            function init()
            {
                $this->init_form_fields();
                $this->init_settings();
                add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
            }

            /**
             * Enable Woocommerce Shipping For purolator Small
             */
            function init_form_fields()
            {
                $this->instance_form_fields = array(
                    'enabled' => array(
                        'title' => __('Enable / Disable', 'purolator_small'),
                        'type' => 'checkbox',
                        'label' => __('Enable This Shipping Service', 'purolator_small'),
                        'default' => 'no',
                        'id' => 'purolator_small_enable_disable_shipping'
                    )
                );
            }

            /**
             * Multi shipment query
             * @param array $en_rates
             * @param string $accessorial
             */
            public function en_multi_shipment($en_rates, $accessorial, $origin)
            {
                $accessorial .= '_purolator_small';
                $en_rates = (isset($en_rates) && (is_array($en_rates))) ? array_slice($en_rates, 0, 1) : [];
                $total_cost = array_sum($this->VersionCompat->enArrayColumn($en_rates, 'cost'));

                !$total_cost > 0 ? $this->en_not_returned_the_quotes = TRUE : '';

                $en_rates = !empty($en_rates) ? reset($en_rates) : [];
                $this->minPrices[$origin] = $en_rates;
                // FDO
                $this->en_fdo_meta_data[$origin] = (isset($en_rates['meta_data']['en_fdo_meta_data'])) ? $en_rates['meta_data']['en_fdo_meta_data'] : [];

                if (isset($this->eniture_rates[$accessorial])) {
                    $this->eniture_rates[$accessorial]['cost'] += $total_cost;
                } else {
                    $this->eniture_rates[$accessorial] = [
                        'id' => $accessorial,
                        'label' => 'Shipping',
                        'cost' => $total_cost,
                        'label_sufex' => str_split($accessorial),
                        'plugin_name' => 'purolator',
                        'plugin_type' => 'small',
                        'owned_by' => 'eniture'
                    ];
                }
            }

            /**
             * Single shipment query
             * @param array $en_rates
             * @param string $accessorial
             */
            public function en_single_shipment($en_rates, $accessorial, $origin)
            {
                $en_rates = isset($en_rates) && is_array($en_rates) ? $en_rates : [];
                $this->eniture_rates = array_merge($this->eniture_rates, $en_rates);
            }

            /**
             * Calculate Shipping Rates For purolator Small
             * @param $package
             * @return boolean|string
             */
            public function calculate_shipping($package = [], $eniture_admin_order_action = false)
            {
                if (is_admin() && !$eniture_admin_order_action) {
                    return [];
                }

                $this->package_plugin = get_option('purolator_small_package');

                $coupn = WC()->cart->get_coupons();
                if (isset($coupn) && !empty($coupn)) {
                    $freeShipping = $this->purolatorSmpkgFreeShipping($coupn);
                    if ($freeShipping == 'y')
                        return FALSE;
                }
                $this->instore_pickup_and_local_delivery = FALSE;
                $purolator_small_woo_obj = new purolator_Small_Woo_Update_Changes();
                $freight_zipcode = (strlen(WC()->customer->get_shipping_postcode()) > 0) ? WC()->customer->get_shipping_postcode() : $purolator_small_woo_obj->purolator_small_postcode();

                if (empty($freight_zipcode)) {
                    return false;
                }

                $get_packg_obj = new purolator_Small_Shipping_Get_Package();
                $purolator_small_res_inst = new purolator_Get_Shipping_Quotes();

                $this->web_service_inst = $purolator_small_res_inst;
                $this->VersionCompat = new VersionCompat();

                $this->get_settings_fields();

                // Free shipping
                if ($this->quote_settings['handling_fee'] == '-100%') {
                    $rates = array(
                        'id' => $this->id . ':' . 'free',
                        'label' => 'Free Shipping',
                        'cost' => 0,
                        'plugin_name' => 'purolator',
                        'plugin_type' => 'small',
                        'owned_by' => 'eniture'
                    );
                    $this->add_rate($rates);
                    
                    return [];
                }

                $rates = array();
                $rateArray = array();
                $quotesArray = array();
                $quotes = array();
                $SmPkgWebServiceArr = array();
                $purolator_small_package = "";
                $purolator_small_package = $get_packg_obj->group_purolator_small_shipment($package, $purolator_small_res_inst);
                $no_param_multi_ship = 0;

                // Suppress small rates when weight threshold is met
                $supress_parcel_rates = apply_filters('en_suppress_parcel_rates_hook', '');
                if (!empty($purolator_small_package) && is_array($purolator_small_package) && $supress_parcel_rates) {
                    foreach ($purolator_small_package as $org_id => $pckg) {
                        $total_shipment_weight = 0;

                        $shipment_items = !empty($pckg['items']) ? $pckg['items'] : []; 
                        foreach ($shipment_items as $item) {
                            $total_shipment_weight += (floatval($item['productWeight']) * $item['productQty']);
                        }

                        $purolator_small_package[$org_id]['shipment_weight'] = $total_shipment_weight;
                        $weight_threshold = get_option('en_weight_threshold_lfq');
                        $weight_threshold = isset($weight_threshold) && $weight_threshold > 0 ? $weight_threshold : 150;
                        
                        if ($total_shipment_weight >= $weight_threshold) {
                            $purolator_small_package[$org_id]['items'] = [];
                        }
                    }
                }
                
                $SmPkgWebServiceArr = $purolator_small_res_inst->purolator_Small_shipping_array($purolator_small_package, $package, $this->package_plugin);

                foreach ($SmPkgWebServiceArr as $locId => $sPackage) {

                    $package_bins = (isset($sPackage['bins'])) ? $sPackage['bins'] : [];
                    $en_box_fee = (isset($sPackage['en_box_fee'])) ? $sPackage['en_box_fee'] : [];
                    $en_multi_box_qty = (isset($sPackage['ups_small_pkg_product_quantity'])) ? $sPackage['ups_small_pkg_product_quantity'] : [];
                    $ups_bins = (isset($sPackage['ups_bins'])) ? $sPackage['ups_bins'] : [];
                    $hazardous_status = (isset($sPackage['hazardous_status'])) ? $sPackage['hazardous_status'] : '';
                    $package_bins = !empty($ups_bins) ? $package_bins + $ups_bins : $package_bins;
                    $speed_ship_senderZip = $locId;
                    if (!strlen($speed_ship_senderZip) > 0) {
                        continue;
                    }
                    $this->web_service_inst->product_detail[$speed_ship_senderZip]['product_name'] = isset($sPackage['product_name']) ? json_encode($sPackage['product_name']) : '';
                    $this->web_service_inst->product_detail[$speed_ship_senderZip]['products'] = isset($sPackage['products']) ? $sPackage['products'] : [];
                    $this->web_service_inst->product_detail[$speed_ship_senderZip]['sender_origin'] = isset($sPackage['sender_origin']) ? $sPackage['sender_origin'] : '';
                    $this->web_service_inst->product_detail[$speed_ship_senderZip]['package_bins'] = $package_bins;
                    $this->web_service_inst->product_detail[$speed_ship_senderZip]['en_box_fee'] = $en_box_fee;
                    $this->web_service_inst->product_detail[$speed_ship_senderZip]['en_multi_box_qty'] = $en_multi_box_qty;
                    $this->web_service_inst->product_detail[$speed_ship_senderZip]['hazardous_status'] = isset($hazardous_status) ? $hazardous_status : '';
                    $this->web_service_inst->product_detail[$speed_ship_senderZip]['exempt_ground_transit_restriction'] = (isset($sPackage['exempt_ground_transit_restriction'])) ? $sPackage['exempt_ground_transit_restriction'] : '';
                    $this->web_service_inst->product_detail[$speed_ship_senderZip]['origin_markup'] = $sPackage['origin_markup'];
                    $this->web_service_inst->product_detail[$speed_ship_senderZip]['product_level_markup'] = $sPackage['product_level_markup'];


                    // FDO
                    $en_fdo_meta_data = (isset($sPackage['en_fdo_meta_data'])) ? $sPackage['en_fdo_meta_data'] : '';
                    $this->web_service_inst->product_detail[$speed_ship_senderZip]['en_fdo_meta_data'] = $en_fdo_meta_data;

                    if ($sPackage != 'ltl' && is_array($sPackage)) {
                        $quotesValue = $purolator_small_res_inst->purolator_small_get_quotes($sPackage);

                        do_action("eniture_debug_mood", "Quotes Response (purolator-s)", $quotesValue);
                        
                        if(!(isset($sPackage['exempt_ground_transit_restriction']) && $sPackage['exempt_ground_transit_restriction'] == 'yes')){
                            $ups_transit_days = new EnPurolatorSmallTransitDays();
                            $quotesValue = $ups_transit_days->purolator_enable_disable_ground_service($quotesValue);
                        }
                            
                        $this->InstorPickupLocalDelivery = isset($quotesValue->InstorPickupLocalDelivery) && !empty($quotesValue->InstorPickupLocalDelivery) ? $quotesValue->InstorPickupLocalDelivery : array();

                        $hazardousIndex = array(
                            "hazardousMaterial" => $hazardous_status == 'yes'
                        );
                        $quotes[$locId] = (object)array_merge((array)$quotesValue, (array)$hazardousIndex);
                    }
                }

                $en_is_shipment = (count($quotes) > 1) ? 'en_multi_shipment' : 'en_single_shipment';

                $this->quote_settings['shipment'] = $en_is_shipment;
                $this->eniture_rates = [];

                $en_rates = $quotes;
                foreach ($en_rates as $origin => $step_for_rates) {
                    $product_detail = (isset($this->web_service_inst->product_detail[$origin])) ? $this->web_service_inst->product_detail[$origin] : array();
                    $filterd_rates = $this->web_service_inst->purolator_small_quotes_grouping($step_for_rates, $product_detail, $this->quote_settings);
                    $en_sorting_rates = (isset($filterd_rates['en_sorting_rates'])) ? $filterd_rates['en_sorting_rates'] : "";

                    // $this->InstorPickupLocalDelivery = (isset($filterd_rates['InstorPickupLocalDelivery'])) ? $filterd_rates['InstorPickupLocalDelivery'] : "";
                    if (isset($filterd_rates['en_sorting_rates']))
                        unset($filterd_rates['en_sorting_rates']);

                    if (isset($filterd_rates['InstorPickupLocalDelivery']))
                        unset($filterd_rates['InstorPickupLocalDelivery']);

                    if (is_array($filterd_rates) && !empty($filterd_rates)) {
                        foreach ($filterd_rates as $accessorial => $service) {
                            (!empty($filterd_rates[$accessorial])) ? array_multisort($en_sorting_rates[$accessorial], SORT_ASC, $filterd_rates[$accessorial]) : $en_sorting_rates[$accessorial] = [];
                            $this->$en_is_shipment($filterd_rates[$accessorial], $accessorial, $origin);
                        }
                    } else {
                        $this->en_not_returned_the_quotes = TRUE;
                    }
                }
                if ($this->en_not_returned_the_quotes) {
                    return [];
                }
                if ($en_is_shipment == 'en_single_shipment') {
                    // In-store pickup and local delivery
                    $instore_pickup_local_devlivery_action = apply_filters('ups_small_quotes_plans_suscription_and_features', 'instore_pickup_local_devlivery');
                    if ((isset($this->web_service_inst->en_wd_origin_array['suppress_local_delivery']) && $this->web_service_inst->en_wd_origin_array['suppress_local_delivery'] == "1") && (!is_array($instore_pickup_local_devlivery_action))) {
                        $this->eniture_rates = apply_filters('suppress_local_delivery', $this->eniture_rates, $this->web_service_inst->en_wd_origin_array, $this->package_plugin, $this->InstorPickupLocalDelivery);
                    }
                }

                $accessorials = [
                    'R' => 'residential delivery',
                ];

                add_filter('woocommerce_package_rates', array($this, 'en_sort_woocommerce_available_shipping_methods'), 10, 2);

                $en_rates = $this->eniture_rates;

                do_action("eniture_debug_mood", "Purolator Small Eniture Quotes Rates", $en_rates);

                foreach ($en_rates as $accessorial => $rate) {

                // show delivery estimates
                    if ($en_is_shipment == 'en_single_shipment') {

                        $ups_small_delivey_estimate = get_option('purolator_small_delivery_estimates');

                        if (isset($ups_small_delivey_estimate) && !empty($ups_small_delivey_estimate) && $ups_small_delivey_estimate != 'dont_show_estimates') {
                            if ($ups_small_delivey_estimate == 'delivery_date' && !empty($rate['transit_time'])) {
                                $rate['label'] .= ' (Expected delivery by ' . date('m-d-Y', strtotime($rate['transit_time'])) . ')';
                            } else if ($ups_small_delivey_estimate == 'delivery_days' && !empty($rate['delivery_days'])) {
                                $correct_word = ($rate['delivery_days'] == 1) ? 'is' : 'are';
                                $rate['label'] .= ' (Intransit days: ' . $rate['delivery_days'] . ')';
                            }
                        }
                    }

                    if (isset($rate['label_sufex']) && !empty($rate['label_sufex'])) {
                        $label_sufex = array_intersect_key($accessorials, array_flip($rate['label_sufex']));
                        $rate['label'] .= (!empty($label_sufex)) ? ' with ' . implode(' and ', $label_sufex) : '';

                        // Order widget detail set
                        // FDO
                        if (isset($this->minPrices) && !empty($this->minPrices)) {
                            $rate['minPrices'] = $this->minPrices;
                            $rate['meta_data']['min_prices'] = wp_json_encode($this->minPrices);
                            $rate['meta_data']['en_fdo_meta_data']['data'] = array_values($this->en_fdo_meta_data);
                            $rate['meta_data']['en_fdo_meta_data']['shipment'] = 'multiple';
                            $rate['meta_data']['en_fdo_meta_data'] = wp_json_encode($rate['meta_data']['en_fdo_meta_data']);
                        } else {
                            $en_set_fdo_meta_data['data'] = [$rate['meta_data']['en_fdo_meta_data']];
                            $en_set_fdo_meta_data['shipment'] = 'sinlge';
                            $rate['meta_data']['en_fdo_meta_data'] = wp_json_encode($en_set_fdo_meta_data);
                        }
                    }

                    if (isset($rate['cost']) && $rate['cost'] > 0) {
                        $rate['id'] = isset($rate['id']) && is_string($rate['id']) ? $this->id . ':' . $rate['id'] : '';
                        $this->add_rate($rate);
                        $en_rates[$accessorial] = array_merge($en_rates[$accessorial], $rate);
                    }
                }

                if ($en_is_shipment == 'en_single_shipment') {
                    (isset($this->InstorPickupLocalDelivery->localDelivery) && ($this->InstorPickupLocalDelivery->localDelivery->status == 1)) ? $this->local_delivery($this->web_service_inst->en_wd_origin_array['fee_local_delivery'], $this->web_service_inst->en_wd_origin_array['checkout_desc_local_delivery']) : "";
                    (isset($this->InstorPickupLocalDelivery->inStorePickup) && ($this->InstorPickupLocalDelivery->inStorePickup->status == 1)) ? $this->pickup_delivery($this->web_service_inst->en_wd_origin_array['checkout_desc_store_pickup']) : "";
                }

                return $en_rates;
            }

            /**
             * Quote settings
             */
            function get_settings_fields()
            {
                $this->quote_settings = [];
                $this->quote_settings['hazardous_fee'] = get_option('purolator_small_hazardous_fee');
                $this->quote_settings['dont_sort'] = get_option('shipping_methods_do_not_sort_by_price');
                $this->quote_settings['handling_fee'] = get_option('purolator_small_hand_fee_mark_up');
                $this->quote_settings['services'] = [
                    'all' => $this->web_service_inst->service_options_array()
                ];
            }

            /**
             * final rates sorting
             * @param array type $rates
             * @param array type $package
             * @return array type
             */
            function en_sort_woocommerce_available_shipping_methods($rates, $package)
            {
//              if there are no rates don't do anything

                if (!$rates) {
                    return array();
                }

//              check the option to sort shipping methods by price on quote settings 
                if (get_option('shipping_methods_do_not_sort_by_price') != 'yes') {

                    $local_delivery = isset($rates['local-delivery']) ? $rates['local-delivery'] : '';
                    $in_store_pick_up = isset($rates['in-store-pick-up']) ? $rates['in-store-pick-up'] : '';
//                  get an array of prices
                    $prices = array();
                    foreach ($rates as $rate) {
                        $prices[] = $rate->cost;
                    }

//                  use the prices to sort the rates
                    array_multisort($prices, $rates);
                }
//              return the rates
                return $rates;
            }

            /**
             * Filter function to update order details.
             * @param array $data
             * @return type
             */
            public function en_update_order_data($data)
            {
                $data['en_shipping_details']['en_puro_small'] = $this->order_detail;
                return $data;
            }

            /**
             * Pickup delivery quote
             * @return array type
             */
            function pickup_delivery($label)
            {
                $this->woocommerce_package_rates = 1;
                $this->instore_pickup_and_local_delivery = TRUE;

                $label = (isset($label) && (strlen($label) > 0)) ? $label : 'In-store pick up';

//              check woocommerce version for displying instore pickup cost $0.00
                $woocommerce_version = get_option('woocommerce_version');
                $label = ($woocommerce_version < '3.5.4') ? $label : $label . ': $0.00';

                $pickup_delivery = array(
                    'id' => $this->id . ':' . 'in-store-pick-up',
                    'cost' => 0,
                    'label' => $label,
                    'plugin_name' => 'purolator',
                    'plugin_type' => 'small',
                    'owned_by' => 'eniture'
                );

                add_filter('woocommerce_package_rates', array($this, 'en_sort_woocommerce_available_shipping_methods'), 10, 2);
                $this->add_rate($pickup_delivery);
            }

            /**
             * Local delivery quote
             * @param string type $cost
             * @return array type
             */
            function local_delivery($cost, $label)
            {

                $this->woocommerce_package_rates = 1;
                $this->instore_pickup_and_local_delivery = TRUE;
                $label = (isset($label) && (strlen($label) > 0)) ? $label : 'Local Delivery';
                if ($cost == 0) {
//              check woocommerce version for displying instore pickup cost $0.00
                    $woocommerce_version = get_option('woocommerce_version');
                    $label = ($woocommerce_version < '3.5.4') ? $label : $label . ': $0.00';
                }

                $local_delivery = array(
                    'id' => $this->id . ':' . 'local-delivery',
                    'cost' => !empty($cost) ? $cost : 0,
                    'label' => $label,
                    'plugin_name' => 'purolator',
                    'plugin_type' => 'small',
                    'owned_by' => 'eniture'
                );

                add_filter('woocommerce_package_rates', array($this, 'en_sort_woocommerce_available_shipping_methods'), 10, 2);
                $this->add_rate($local_delivery);
            }

            /**
             * Function to update the filter data and session with order details.
             * @param object $group_small_shipments
             */
            public function en_order_details_hooks_process($group_small_shipments)
            {
                $order_details = array();
                $this->order_detail = $group_small_shipments->order_details;


                /* Filter the data of order details */
                add_filter('en_fitler_order_data', array($this, 'en_update_order_data'));

                /* Passing empty array because data is updated using class property */
                $session_order_details = apply_filters(
                    'en_fitler_order_data', array()
                );

                /* Set the session */
                WC()->session->set(
                    'en_order_detail', $session_order_details
                );
            }

            /**
             * No Quotes Messages
             * Saved At Quotes Setting Page
             */
            function purolator_small_cart_no_quotes_msg()
            {
                if (get_option('wc_pervent_proceed_checkout_purolator_small') == 'prevent') {
                    remove_action('woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20, 2);
                    return __(get_option('prevent_proceed_checkout_purolator_small'));
                } else {
                    return __(get_option('allow_proceed_checkout_purolator_small'));
                }
            }

            /**
             * Check is free shipping or not
             * @param $coupon
             * @return string
             */
            function purolatorSmpkgFreeShipping($coupon)
            {
                foreach ($coupon as $key => $value) {
                    if ($value->get_free_shipping() == 1) {
                        $rates = array(
                            'id' => 'free',
                            'label' => 'Free Shipping',
                            'cost' => 0,
                            'plugin_name' => 'purolator',
                            'plugin_type' => 'small',
                            'owned_by' => 'eniture'
                        );
                        $this->add_rate($rates);
                        return 'y';
                    }
                }
            }

        }

    }
}
