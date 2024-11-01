<?php

/**
 * Carrier Service
 * @package     Woocommerce Purolator Small
 * @author      <https://eniture.com/>
 * @copyright   Copyright (c) 2017, Eniture
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get Quotes For purolator Small
 */
class purolator_Get_Shipping_Quotes extends EnPurolatorSmallFdo
{
    /** Global Variable */
    public $purolator_sm_errors = array();

    /** Global Variable */
    public $no_services_select = array();
    public $hazardous_status;
    public $en_wd_origin_array;
    public $product_detail = [];
    public $forcefully_residential_delivery = FALSE;

    /**
     * Array For Getting Quotes
     * @param $packages
     * @param $content
     */
    function purolator_Small_shipping_array($packages, $content, $package_plugin = "")
    {
        $shipping_package_obj = new purolator_Small_Shipping_Get_Package();
        $itemTypeVal = "";

        // FDO
        $en_fdo_meta_data = $post_data = array();

        $residential = "";
        $destinationAddressPuroSmall = $this->destinationAddressPuroSmall();

        $origin = reset($packages);

        $this->en_wd_origin_array = (isset($origin['origin'])) ? $origin['origin'] : array();

        $exceedWeight = get_option('en_plugins_return_LTL_quotes');

        (get_option('purolator_small_quote_as_residential_delivery') == 'yes') ? $residential = 'on' : '';
        $Pweight = 0;
        $findLtl = 0;
        if (isset($packages) && !empty($packages)) {
            foreach ($packages as $package) {
                if (!($exceedWeight == 'yes' && $Pweight > 150)) {
                    $lineItem = array();
                    $productIdCount = 0;
                    $orderCutoffTime = "";
                    $shipmentOffsetDays = "";
                    $modifyShipmentDateTime = "";
                    $storeDateTime = "";
                    $shipmentWeekDays = "";
                    $products = $product_name = [];
                    $product_markup_shipment = 0;

                    foreach ($package['items'] as $item) {
                        $Pweight = $item['productWeight'];
                        $lineItem[$productIdCount] = array(
                            'lineItemWeight' => $item['productWeight'],
                            'piecesOfLineItem' => $item['productQty'],
                            'lineItemHeight' => $item['productHeight'],
                            'lineItemWidth' => $item['productWidth'],
                            'lineItemLength' => $item['productLength'],
                        );

                        $product_name[] = $item['product_name'];
                        $products[] = $item['products'];
                        
                        if (!empty($item['markup']) && is_numeric($item['markup'])){
                            $product_markup_shipment += $item['markup'];
                        }

                        $productIdCount++;
                    }

                    if ($destinationAddressPuroSmall['country'] == 'CA') {
                        $serviceId = 'PurolatorGround';
                    } else if ($destinationAddressPuroSmall['country'] == 'US') {
                        $serviceId = 'PurolatorGroundU.S.';
                    } else {
                        $serviceId = 'PurolatorExpressInternational';
                    }

                    $aPluginVersions = $this->purolator_small_wc_version_number();

                    $domain = purolator_small_get_domain();

                    // Start: Cut Off Time & Ship Date Offset
                    $purolator_small_delivery_estimates = get_option('purolator_small_delivery_estimates');

                    // shipment days of a week
                    $shipmentWeekDays = $this->purolator_small_shipment_week_days();
                    if ($purolator_small_delivery_estimates == 'delivery_days'
                        || $purolator_small_delivery_estimates == 'delivery_date') {
                        $orderCutoffTime = get_option('purolator_small_orderCutoffTime');
                        $shipmentOffsetDays = get_option('purolator_small_shipmentOffsetDays');
                        $modifyShipmentDateTime = ($orderCutoffTime != '' || $shipmentOffsetDays != '' || (is_array($shipmentWeekDays) && count($shipmentWeekDays) > 0)) ? 1 : 0;
                        $storeDateTime = date('Y-m-d H:i:s', current_time('timestamp'));
                    }
                    // End: Cut Off Time & Ship Date Offset

                    $package_type = get_option('purolator_small_packaging_method');
                    $per_package_weight = '';
                    if(empty($package_type)){
                        $package_type = 'ship_alone';
                    }elseif('ship_one_package_70' == $package_type){
                        $package_type = 'ship_as_one';
                        $per_package_weight = '70';
                    }elseif('ship_one_package_150' == $package_type){
                        $package_type = 'ship_as_one';
                        $per_package_weight = '150';
                    }

                    // FDO
                    $en_fdo_meta_data = $this->en_cart_package($package);

                    $package_zip = (isset($package['origin']['zip'])) ? $package['origin']['zip'] : '';
                    if (isset($post_data[$package_zip])) {
                        $package_zip .= 'duplicate';
                    }

                    $post_data[$package_zip] = array(
                        'plateform' => 'WordPress',
                        'plugin_version' => $aPluginVersions["purolator_small_plugin_version"],
                        'wordpress_version' => get_bloginfo('version'),
                        'woocommerce_version' => $aPluginVersions["woocommerce_plugin_version"],
                        'licence_key' => get_option('purolator_small_licence_key'),
                        'sever_name' => $this->purolator_small_parse_url($domain),
                        'modifyShipmentDateTime' => $modifyShipmentDateTime,
                        'OrderCutoffTime' => $orderCutoffTime,
                        'shipmentOffsetDays' => $shipmentOffsetDays,
                        'storeDateTime' => $storeDateTime,
                        'shipmentWeekDays' => $shipmentWeekDays,
                        'carrierName' => 'purolator',
                        'carrier_mode' => 'pro',
                        'productionKey' => get_option('purolator_small_pro_key'),
                        'productionPass' => get_option('purolator_small_pro_key_pass'),
                        'registeredAccount' => get_option('purolator_small_registered_account_number'),
                        'billingAccount' => get_option('purolator_small_billing_account_number'),
                        'QuoteType' => 'Domestic',
                        'accessLevel' => 'pro',
                        'senderCity' => $package['origin']['city'],
                        'senderState' => $package['origin']['state'],
                        'senderZip' => preg_replace('/\s+/', '', $package['origin']['zip']),
                        'senderCountryCode' => $package['origin']['country'],
                        'receiverCity' => $destinationAddressPuroSmall['city'],
                        'receiverState' => $destinationAddressPuroSmall['state'],
                        'receiverZip' => preg_replace('/\s+/', '', $destinationAddressPuroSmall['zip']),
                        'receiverCountryCode' => $destinationAddressPuroSmall['country'],
                        'accessorial' => array(),
                        'ServiceID' => $serviceId,
                        'commdityDetails' => array(
                            'handlingUnitDetails' => $lineItem
                        ),
                        // FDO
                        'en_fdo_meta_data' => $en_fdo_meta_data,
                        'sender_origin' => $package['origin']['location'] . ": " . $package['origin']['city'] . ", " . $package['origin']['state'] . " " . $package['origin']['zip'],
                        'product_name' => $product_name,
                        'products' => $products,
                        'packagesType' => $package_type,
                        'perPackageWeight' => $per_package_weight,
                        // Sbs optimization mode
                        'sbsMode' => get_option('box_sizing_optimization_mode'),
                        'origin_markup' => (isset($package['origin']['origin_markup'])) ? $package['origin']['origin_markup'] : 0,
                        'product_level_markup' => $product_markup_shipment
                    );

                    // Hazardous Material
                    $hazardous_material = apply_filters('purolator_small_quotes_plans_suscription_and_features', 'hazardous_material');

                    if (!is_array($hazardous_material)) {
                        $hazardous = reset($packages);
                    // $post_data[$package['origin']['zip']]['hazardousMaterial'] = ($hazardous['hazardousMaterial'] == 'yes') ? 1 : 0;

                        (isset($package['hazardous_material'])) ? $post_data[$package_zip]['hazardous_status'] = TRUE : "";
                        (isset($package['hazardous_material'])) ? $post_data[$package_zip]['hazardous_status'] = 'yes' : "";

                        // FDO
                        $post_data[$package_zip]['en_fdo_meta_data'] = array_merge($post_data[$package_zip]['en_fdo_meta_data'], $this->en_package_hazardous($package, $en_fdo_meta_data));
                    }

                    //Except Ground Transit Restriction
                    $exempt_ground_restriction_plan = apply_filters('purolator_small_quotes_plans_suscription_and_features', 'transit_days');
                    if (!is_array($exempt_ground_restriction_plan)) {
                        (isset($package['exempt_ground_transit_restriction'])) ? $post_data[$package_zip]['exempt_ground_transit_restriction'] = 'yes' : '';
                    }

                    // In-store pickup and local delivery
                    $instore_pickup_local_devlivery_action = apply_filters('purolator_small_quotes_plans_suscription_and_features', 'instore_pickup_local_devlivery');

                    if (!is_array($instore_pickup_local_devlivery_action)) {
                        $post_data[$package['origin']['zip']] = apply_filters('en_wd_standard_plans', $post_data[$package['origin']['zip']], $post_data[$package['origin']['zip']]['receiverZip'], $this->en_wd_origin_array, $package_plugin);
                    }
                }
                $post_data = apply_filters(
                    'enit_box_sizes_post_array_filter', $post_data, $package, $package['origin']['zip']
                );
                // Compatability with OLD SBS Addon
                $zip_code = (isset($package['origin']['zip'])) ? $package['origin']['zip'] : 0;
                if (isset($post_data[$zip_code]['vertical_rotation'], $post_data[$zip_code]['length']) &&
                    count($post_data[$zip_code]['length']) == count($post_data[$zip_code]['vertical_rotation']) &&
                    !empty($post_data[$zip_code]['vertical_rotation'])) {
                    $post_data[$zip_code]['vertical_rotation'] = array_combine(array_keys($post_data[$zip_code]['length']), $post_data[$zip_code]['vertical_rotation']);
                }
                if (isset($post_data[$zip_code]['shipBinAlone'], $post_data[$zip_code]['length']) &&
                    count($post_data[$zip_code]['length']) == count($post_data[$zip_code]['shipBinAlone']) &&
                    !empty($post_data[$zip_code]['shipBinAlone'])) {
                    $post_data[$zip_code]['shipBinAlone'] = array_combine(array_keys($post_data[$zip_code]['length']), $post_data[$zip_code]['shipBinAlone']);
                }
            }
        }
        do_action("eniture_debug_mood", "Plugin Features (purolator-s)", get_option('eniture_plugin_16'));
        do_action("eniture_debug_mood", "Quotes Request (purolator-s)", $post_data);

        return $post_data;
    }

    /**
     * @return shipment days of a week
     */
    public function purolator_small_shipment_week_days()
    {

        $shipment_days_of_week = array();

        if (get_option('all_shipment_days_purolator_small') == 'yes') {
            return $shipment_days_of_week;
        }

        if (get_option('monday_shipment_day_purolator_small') == 'yes') {
            $shipment_days_of_week[] = 1;
        }
        if (get_option('tuesday_shipment_day_purolator_small') == 'yes') {
            $shipment_days_of_week[] = 2;
        }
        if (get_option('wednesday_shipment_day_purolator_small') == 'yes') {
            $shipment_days_of_week[] = 3;
        }
        if (get_option('thursday_shipment_day_purolator_small') == 'yes') {
            $shipment_days_of_week[] = 4;
        }
        if (get_option('friday_shipment_day_purolator_small') == 'yes') {
            $shipment_days_of_week[] = 5;
        }

        return $shipment_days_of_week;
    }

    /**
     * URL Rewriting
     * @param $domain
     * @return url
     */
    function purolator_small_parse_url($domain)
    {
        $domain = trim($domain);
        $parsed = parse_url($domain);
        if (empty($parsed['scheme'])) {
            $domain = 'http://' . ltrim($domain, '/');
        }
        $parse = parse_url($domain);
        $refinded_domain_name = $parse['host'];
        $domain_array = explode('.', $refinded_domain_name);
        if (in_array('www', $domain_array)) {
            $key = array_search('www', $domain_array);
            unset($domain_array[$key]);
            if(phpversion() < 8) {
                $refinded_domain_name = implode($domain_array, '.'); 
            }else {
                $refinded_domain_name = implode('.', $domain_array);
            }
        }
        return $refinded_domain_name;
    }

    /**
     * destinationAddressFedexSmall
     * @return array type
     */
    function destinationAddressPuroSmall()
    {
        $en_order_accessories = apply_filters('en_order_accessories', []);
        if (isset($en_order_accessories) && !empty($en_order_accessories)) {
            return $en_order_accessories;
        }

        $purolator_small_woo_obj = new purolator_Small_Woo_Update_Changes();
        $freight_zipcode = (strlen(WC()->customer->get_shipping_postcode()) > 0) ? WC()->customer->get_shipping_postcode() : $purolator_small_woo_obj->purolator_small_postcode();
        $freight_state = (strlen(WC()->customer->get_shipping_state()) > 0) ? WC()->customer->get_shipping_state() : $purolator_small_woo_obj->purolator_small_getState();
        $freight_country = (strlen(WC()->customer->get_shipping_country()) > 0) ? WC()->customer->get_shipping_country() : $purolator_small_woo_obj->purolator_small_getCountry();
        $freight_city = (strlen(WC()->customer->get_shipping_city()) > 0) ? WC()->customer->get_shipping_city() : $purolator_small_woo_obj->purolator_small_getCity();
        return array(
            'city' => $freight_city,
            'state' => $freight_state,
            'zip' => $freight_zipcode,
            'country' => $freight_country
        );
    }

    /**
     * Get Nearest Address If Multiple Warehouses
     * @param $warehous_list
     * @param $receiverZipCode
     * @return array
     */
    function purolator_Small_multi_warehouse($warehous_list, $receiverZipCode)
    {
        if (count($warehous_list) == 1) {
            $warehous_list = reset($warehous_list);
            return $this->purolator_small_origin_array($warehous_list);
        }

        $purolator_Small_distance_request = new Get_purolator_small_distance();
        $accessLevel = "MultiDistance";
        $response_json = $purolator_Small_distance_request->purolator_small_address($warehous_list, $accessLevel, $this->destinationAddressPuroSmall());

        $response_json = json_decode($response_json);
        return $this->purolator_small_origin_array($response_json->origin_with_min_dist);
    }

    /**
     * Create Origin Array
     * @param $origin
     * @return array
     */
    function purolator_small_origin_array($origin)
    {
//      In-store pickup and local delivery
        if (has_filter("en_wd_origin_array_set")) {
            return apply_filters("en_wd_origin_array_set", $origin);
        }
        $zip = $origin->zip;
        $city = $origin->city;
        $state = $origin->state;
        $country = ($origin->country == "CN") ? "CA" : $origin->country;
        $location = $origin->location;
        $locationId = $origin->id;
        return array('locationId' => $locationId, 'zip' => $zip, 'city' => $city, 'state' => $state, 'location' => $location, 'country' => $country);
    }

    /*
     * 
      @ param : Requeted Data Array
     * 
      @ return Quotes Json
     */

    /**
     * Get purolator Small Web Quotes
     * @param $request_data
     */
    function purolator_small_get_quotes($request_data)
    {


        if (is_array($request_data) && count($request_data) > 0) {

            $purolator_small_curl_obj = new Purolator_Small_Curl_Request();
            $output = $purolator_small_curl_obj->purolator_small_get_curl_response(PUROLATOR_DOMAIN_HITTING_URL . '/index.php', $request_data);

            return json_decode($output);
        }
    }

    /**
     * Get Shipping Array For Single Shipment
     * @param $result
     */
    function parse_purolator_small_output($result)
    {
        $all_services_array = array();
        $estimatedTransitDays = "";
        $serviceID = "";
        $totalPrice = "";
        $hazardous_fee = 0;

        if (isset($result->q)) {

            $quotesArr = $result->q;

            $handling_fee = get_option('purolator_small_hand_fee_mark_up');
            $hazardous_fee = get_option('purolator_small_hazardous_fee');

            $active_services = $this->service_options_array();

            foreach ($quotesArr as $key => $service) {

                $estimatedTransitDays = $service->transitTime;
                $delivery_days = (isset($service->totalTransitTimeInDays)) ? $service->totalTransitTimeInDays : '';
                $transit_time = (isset($service->deliveryTimestamp)) ? $service->deliveryTimestamp : '';

                //**Change: Only If condition by Zeeshan
                if (isset($active_services[$service->serviceType])) {

                    $serviceID = $service->serviceType;
                    $totalPrice = $service->totalNetCharge->Amount;

                    //Start: Adding Service level markup fee
                    $service_markup_fee = $active_services[$service->serviceType]['markup'];
                    $totalPrice = $this->calculate_service_level_markup($totalPrice, $service_markup_fee);
                    //End: Adding Service level markup fee

                    if ($handling_fee != "") {
                        $grand_total = $this->calculate_handeling_fee($handling_fee, $totalPrice);
                    } else {
                        $grand_total = $totalPrice;
                    }

                    if ($result->hazardousMaterial == 1) {
                        $grand_total = (!empty($hazardous_fee)) ? $hazardous_fee + $grand_total : $grand_total;
                    }

                    $service_title = $this->purolatorServiceNames($serviceID);

                    $all_services_array[] = array(
                        'ServiceID' => $serviceID,
                        'TotalPrice' => $grand_total,
                        'transit_time' => $transit_time,
                        'delivery_days' => $delivery_days,
                        'EstimatedTransitDays' => $estimatedTransitDays,
                        'title' => $service_title
                    );
                }
            }

            $price_sorted_key = array();
            foreach ($all_services_array as $key => $cost_carrier) {
                $price_sorted_key[$key] = $cost_carrier['TotalPrice'];
            }
            array_multisort($price_sorted_key, SORT_ASC, $all_services_array);

            return $all_services_array;
        }
    }

    /**
     * Get Shipping Array For Multiple Shipment
     * @param type $quotes
     */
    function purolator_small_quotes_grouping($result, $product_detail, $quote_settings)
    {
        $en_box_fee = $en_count_rates = 0;
        $en_rates = [];
        $en_sorting_rates = [];

        $no_quotes = true;
        $active_services = $this->service_options_array();
        $hazardous_fee = get_option('purolator_small_hazardous_fee');
        if (count($active_services) > 0) {
            $en_always_accessorial = [];
            $multiple_accessorials[] = ['S'];

            $this->forcefully_residential_delivery ? $multiple_accessorials[] = ['R'] : '';
            $ups_small_hazardous_materials_shipments = get_option('ups_small_hazardous_materials_shipments');
            (get_option('ups_small_quote_as_residential_delivery') == 'yes') ? $en_always_accessorial[] = 'R' : '';
            $hazardous_material = isset($product_detail['hazardous_status']) && $product_detail['hazardous_status'] == 'yes' ? TRUE : FALSE;
            $en_auto_residential_status = !in_array('R', $en_always_accessorial) && isset($result->residentialStatus) && $result->residentialStatus == 'r' ? 'r' : '';

            $handling_fee = get_option('purolator_small_hand_fee_mark_up');
            ($hazardous_material) ? $en_always_accessorial[] = 'H' : '';
            $meta_data['accessorials'] = json_encode($en_always_accessorial);
            $meta_data['sender_origin'] = (isset($product_detail['sender_origin'])) ? $product_detail['sender_origin'] : '';
            $meta_data['product_name'] = (isset($product_detail['product_name'])) ? $product_detail['product_name'] : '';
            $meta_data['plugin_name'] = "purolatorSmall";

            // FDO
            $en_fdo_meta_data = (isset($product_detail['en_fdo_meta_data']) && is_array($product_detail['en_fdo_meta_data'])) ? $product_detail['en_fdo_meta_data'] : [];
            $en_fdo_meta_data['quote_settings'] = isset($quote_settings) ? $quote_settings : [];
            $en_auto_residential_status == 'r' ? $en_fdo_meta_data['accessorials']['residential'] = true : '';

            $package_bins = (isset($product_detail['package_bins'])) ? $product_detail['package_bins'] : [];
            $en_box_fee_arr = (isset($product_detail['en_box_fee']) && !empty($product_detail['en_box_fee'])) ? $product_detail['en_box_fee'] : [];
            $en_multi_box_qty = (isset($product_detail['en_multi_box_qty']) && !empty($product_detail['en_multi_box_qty'])) ? $product_detail['en_multi_box_qty'] : [];
            $products = (isset($product_detail['products'])) ? $product_detail['products'] : [];

            if (isset($en_box_fee_arr) && is_array($en_box_fee_arr) && !empty($en_box_fee_arr)) {
                foreach ($en_box_fee_arr as $en_box_fee_key => $en_box_fee_value) {
                    $en_multi_box_quantity = (isset($en_multi_box_qty[$en_box_fee_key])) ? $en_multi_box_qty[$en_box_fee_key] : 0;
                    $en_box_fee += $en_box_fee_value * $en_multi_box_quantity;
                }
            }

            $bin_packaging_filtered = $this->en_bin_packaging_detail($result);
            $bin_packaging_filtered = !empty($bin_packaging_filtered) ? json_decode(json_encode($bin_packaging_filtered), TRUE) : [];

            // Bin Packaging Box Fee|Product Title Start
            $en_box_total_price = 0;
            if (isset($bin_packaging_filtered['bins_packed']) && !empty($bin_packaging_filtered['bins_packed'])) {
                foreach ($bin_packaging_filtered['bins_packed'] as $bins_packed_key => $bins_packed_value) {
                    $bin_data = (isset($bins_packed_value['bin_data'])) ? $bins_packed_value['bin_data'] : [];
                    $bin_items = (isset($bins_packed_value['items'])) ? $bins_packed_value['items'] : [];
                    $bin_id = (isset($bin_data['id'])) ? $bin_data['id'] : '';
                    $bin_type = (isset($bin_data['type'])) ? $bin_data['type'] : '';
                    $bins_detail = (isset($package_bins[$bin_id])) ? $package_bins[$bin_id] : [];
                    $en_box_price = (isset($bins_detail['box_price'])) ? $bins_detail['box_price'] : 0;
                    $en_box_total_price += $en_box_price;

                    foreach ($bin_items as $bin_items_key => $bin_items_value) {
                        $bin_item_id = (isset($bin_items_value['id'])) ? $bin_items_value['id'] : '';
                        $get_product_name = (isset($products[$bin_item_id])) ? $products[$bin_item_id] : '';
                        if ($bin_type == 'item') {
                            $bin_packaging_filtered['bins_packed'][$bins_packed_key]['bin_data']['product_name'] = $get_product_name;
                        }

                        if (isset($bin_packaging_filtered['bins_packed'][$bins_packed_key]['items'][$bin_items_key])) {
                            $bin_packaging_filtered['bins_packed'][$bins_packed_key]['items'][$bin_items_key]['product_name'] = $get_product_name;
                        }
                    }
                }
            }

            $en_box_total_price += $en_box_fee;
            $meta_data['bin_packaging'] = wp_json_encode($bin_packaging_filtered);
            // FDO
            $en_fdo_meta_data['bin_packaging'] = $bin_packaging_filtered;
            $en_fdo_meta_data['bins'] = $package_bins;

            if (isset($result->q)) {
                foreach ($result->q as $val) {
                    if ((isset($val->serviceType))) {
                        $serviceID = $val->serviceType;
                        if (isset($active_services[$val->serviceType])) {
                            $MonetaryValue = (isset($val->totalNetCharge->Amount)) ? $val->totalNetCharge->Amount : 0;
                            
                            // Adding hazardous material fee
                            if (isset($result->hazardousMaterial) && $result->hazardousMaterial == 1) {
                                $MonetaryValue = (!empty($hazardous_fee)) ? $hazardous_fee + $MonetaryValue : $MonetaryValue;
                            }

                            // Adding product level markup
                            if(!empty($product_detail['product_level_markup'])){
                                $MonetaryValue = $this->calculate_service_level_markup($MonetaryValue, $product_detail['product_level_markup']);
                            }
                            
                            // Adding origin level markup
                            if(!empty($product_detail['origin_markup'])){
                                $MonetaryValue = $this->calculate_service_level_markup($MonetaryValue, $product_detail['origin_markup']);
                            }

                            // Adding service level markup
                            if (isset($active_services[$val->serviceType]['markup']) && !empty($active_services[$val->serviceType]['markup']) && isset($MonetaryValue) && !empty($MonetaryValue)) {
                                $service_markup = $active_services[$val->serviceType]['markup'];
                                $MonetaryValue = $this->calculate_service_level_markup($MonetaryValue, $service_markup);
                            }

                            $cost = $MonetaryValue;
                            $service_title = $this->purolatorServiceNames($serviceID);

                            // Adding markup / handling fee
                            if ($handling_fee != "") {
                                $grand_total = $this->calculate_handeling_fee($handling_fee, $cost);
                            } else {
                                $grand_total = $cost;
                            }

                            $surcharges = [];
                            $transit_time = (isset($val->deliveryTimestamp)) ? $val->deliveryTimestamp : '';
                            $delivery_days = (isset($val->totalTransitTimeInDays)) ? $val->totalTransitTimeInDays : '';
                            $service_type = 'ups_small';
                            $service_name = (isset($val->serviceType)) ? $val->serviceType : '';

                            if (($hazardous_material) && ($service_name != 03)) {
                                if ($ups_small_hazardous_materials_shipments == "yes") {
                                    continue;
                                }
                            }

                            $en_service_cost = $grand_total > 0 ? $grand_total + (float)$en_box_total_price : 0;

                            $en_service = array(
                                'id' => $service_type . "_" . $service_name,
                                'service_type' => $service_type . "_" . $service_name,
                                'cost' => $en_service_cost,
                                'rate' => $en_service_cost,
                                'transit_time' => $transit_time,
                                'delivery_days' => $delivery_days,
                                'title' => $service_title,
                                'label' => $service_title,
                                'label_as' => $service_title,
                                'service_name' => $service_name,
                                'meta_data' => $meta_data,
                                'surcharges' => [],
                                'plugin_name' => 'purolator',
                                'plugin_type' => 'small',
                                'owned_by' => 'eniture'
                            );

                            foreach ($multiple_accessorials as $multiple_accessorials_key => $accessorial) {
                                $en_fliped_accessorial = array_flip($accessorial);

                                // When auto-rad detected
                                (!$this->forcefully_residential_delivery && $en_auto_residential_status == 'r') ? $accessorial[] = 'R' : '';

                                $en_extra_charges = array_diff_key((isset($en_service['surcharges']) ? $en_service['surcharges'] : []), $en_fliped_accessorial);

                                $en_accessorial_type = implode('', $accessorial);
                                $en_rates[$en_accessorial_type][$en_count_rates] = $en_service;

                                // Service name changed GROUND HOME DELIVERY to FEDEX GROUND
                                if ((isset($en_service['service_type'], $en_service['title'], $en_service['label']) &&
                                        $service_type == 'GROUND_HOME_DELIVERY') &&
                                    $this->forcefully_residential_delivery &&
                                    !in_array('R', $accessorial)) {
                                    $en_rates[$en_accessorial_type][$en_count_rates]['service_type'] = 'FEDEX_GROUND_home_ground_pricing';
                                    $en_rates[$en_accessorial_type][$en_count_rates]['title'] = 'FedEx Ground';
                                    $en_rates[$en_accessorial_type][$en_count_rates]['label'] = 'FedEx Ground';
                                }

                                // Cost of the rates
                                $en_sorting_rates
                                [$en_accessorial_type]
                                [$en_count_rates]['cost'] = // Used for sorting of rates
                                $en_rates
                                [$en_accessorial_type]
                                [$en_count_rates]['cost'] = (isset($en_service['cost']) ? $en_service['cost'] : 0) - array_sum($en_extra_charges);

                                $en_rates[$en_accessorial_type][$en_count_rates]['meta_data']['label_sufex'] = wp_json_encode($accessorial);
                                $en_rates[$en_accessorial_type][$en_count_rates]['label_sufex'] = $accessorial;
                                $alphabets = 'abcdefghijklmnopqrstuvwxyz';
                                $rand_string = substr(str_shuffle(str_repeat($alphabets, mt_rand(1, 10))), 1, 10);
                                if (isset($en_rates[$en_accessorial_type][$en_count_rates]['service_name']) && strlen($en_accessorial_type) > 0) {
                                    $en_rates[$en_accessorial_type][$en_count_rates]['id'] = $en_rates[$en_accessorial_type][$en_count_rates]['service_name'] . '_' . $en_accessorial_type;
                                } else {
                                    $en_rates[$en_accessorial_type][$en_count_rates]['id'] = $rand_string;
                                }

                                // FDO
                                $en_fdo_meta_data['rate'] = $en_rates[$en_accessorial_type][$en_count_rates];
                                if (isset($en_fdo_meta_data['rate']['meta_data'])) {
                                    unset($en_fdo_meta_data['rate']['meta_data']);
                                }
                                $en_rates[$en_accessorial_type][$en_count_rates]['meta_data']['en_fdo_meta_data'] = $en_fdo_meta_data;
                                $en_count_rates++;
                            }
                        }
                    }
                }
            }
        }

        $en_rates['en_sorting_rates'] = $en_sorting_rates;
//        $en_rates['InstorPickupLocalDelivery'] = $InstorPickupLocalDelivery;

        return $en_rates;
    }


    /**
     * Return the array
     * @param object $result
     * @return object
     */
    public function en_bin_packaging_detail($result)
    {
        return isset($result->binPackaging->response) ? $result->binPackaging->response : [];
    }

    /**
     * Get Calculate service level markup
     * @param $total_charge
     * @param $international_markup
     */
    function calculate_service_level_markup($total_charge, $international_markup)
    {
        $grandTotal = 0;
        if (floatval($international_markup)) {
            $pos = strpos($international_markup, '%');
            if ($pos > 0) {
                $rest = substr($international_markup, $pos);
                $exp = explode($rest, $international_markup);
                $get = $exp[0];
                $percnt = $get / 100 * $total_charge;
                $grandTotal += $total_charge + $percnt;
            } else {
                $grandTotal += $total_charge + $international_markup;
            }
        } else {
            $grandTotal += $total_charge;
        }
        return $grandTotal;
    }

    /**
     * Calculate Handling Fee For Each Shipment
     * @param $handeling_fee
     * @param $total
     */
    function calculate_handeling_fee($handeling_fee, $total)
    {
        $grandTotal = 0;
        if (floatval($handeling_fee)) {
            $pos = strpos($handeling_fee, '%');
            if ($pos > 0) {
                $rest = substr($handeling_fee, $pos);
                $exp = explode($rest, $handeling_fee);
                $get = $exp[0];
                $percnt = $get / 100 * $total;
                $grandTotal += $total + $percnt;
            } else {
                $grandTotal += $total + $handeling_fee;
            }
        } else {
            $grandTotal += $total;
        }
        return $grandTotal;
    }

    /**
     * purolator Selected Services From Admin Configuration
     * @return array
     */
    function service_options_array()
    {
        $active_services = array();

        if (get_option('purolator_small_express_9') == 'yes') {
            $active_services['PurolatorExpress9AM'] = ['name' => 'PurolatorExpress9AM', 'markup' => get_option('purolator_small_express_9_markup')];
        }

        if (get_option('purolator_small_express_10') == 'yes') {
            $active_services['PurolatorExpress10:30AM'] = ['name' => 'PurolatorExpress10:30AM', 'markup' => get_option('purolator_small_express_10_markup')];
        }

        if (get_option('purolator_small_express') == 'yes') {
            $active_services['PurolatorExpress'] = ['name' => 'PurolatorExpress', 'markup' => get_option('purolator_small_express_markup')];
        }

        if (get_option('purolator_small_ground') == 'yes') {
            $active_services['PurolatorGround'] = ['name' => 'PurolatorGround', 'markup' => get_option('purolator_small_ground_markup')];
        }

        if (get_option('purolator_small_ground_us') == 'yes') {
            $active_services['PurolatorGroundU.S.'] = ['name' => 'PurolatorGroundU.S.', 'markup' => get_option('purolator_small_ground_us_markup')];
        }

        if (get_option('purolator_small_express_us') == 'yes') {
            $active_services['PurolatorExpressU.S.'] = ['name' => 'PurolatorExpressU.S.', 'markup' => get_option('purolator_small_express_us_markup')];
        }
        if (get_option('purolator_small_express_us_10am') == 'yes') {
            $active_services['PurolatorExpressU.S.10:30AM'] = ['name' => 'PurolatorExpressU.S.10:30AM', 'markup' => get_option('purolator_small_express_us_10am_markup')];
        }

        if (get_option('purolator_small_express_us_9am') == 'yes') {
            $active_services['PurolatorExpressU.S.9AM'] = ['name' => 'PurolatorExpressU.S.9AM', 'markup' => get_option('purolator_small_express_us_9am_markup')];
        }

        if (get_option('purolator_small_express_inter') == 'yes') {
            $active_services['PurolatorExpressInternational'] = ['name' => 'PurolatorExpressInternational', 'markup' => get_option('purolator_small_express_inter_markup')];
        }
        if (get_option('purolator_small_ground_90') == 'yes') {
            $active_services['PurolatorGround9AM'] = ['name' => 'PurolatorGround9AM', 'markup' => get_option('purolator_small_ground_90_markup')];
        }
        if (get_option('purolator_small_ground_100') == 'yes') {
            $active_services['PurolatorGround10:30AM'] = ['name' => 'PurolatorGround10:30AM', 'markup' => get_option('purolator_small_ground_100_markup')];
        }

        return $active_services;
    }

    /**
     * Get Service Name
     * @param $rawService
     * @return string
     */
    function purolatorServiceNames($rawService)
    {
        switch ($rawService) {
            case 'PurolatorExpress':
                $newName = 'Purolator Express';
                break;
            case 'PurolatorGround':
                $newName = 'Purolator Ground';
                break;
            case 'PurolatorGroundU.S.':
                $newName = 'Purolator Ground U.S';
                break;
            case 'PurolatorExpressU.S.':
                $newName = 'Purolator Express U.S';
                break;
            case 'PurolatorExpressU.S.10:30AM':
                $newName = 'Purolator Express U.S 10:30AM';
                break;
            case 'PurolatorExpress10:30AM':
                $newName = 'Purolator Express 10:30AM';
                break;
            case 'PurolatorExpress9AM':
                $newName = 'Purolator Express 9AM';
                break;
            case 'PuroloatorExpressU.S.9AM':
                $newName = 'Purolator Express U.S 9AM';
                break;
            default:
                $newName = preg_replace('/([A-Z])/', ' $1', $rawService);
                break;
        }
        return $newName;
    }

    /**
     * Get Names Of purolator Selected Services (DOMESTIC) From Admin Configuration
     * @param $rawService
     */
    function purolatorSmallDomesticServiceNames($rawService)
    {
        switch ($rawService) {
            case 'PurolatorExpress9AM':
                $newName = 'purolator express 9AM';
                break;
            case 'purolator_small_express_10':
                $newName = 'purolator express 10:30AM';
                break;
            case 'PurolatorExpress':
                $newName = 'purolator express';
                break;
            case 'purolator_small_ground':
                $newName = 'purolator ground';
                break;

            default:
                $newName = preg_replace('/([A-Z])/', ' $1', $rawService);
                break;
        }

        return $newName;
    }

    /**
     * purolator Get Shipment Rated Array
     * @param $locationGroups
     */
    function RatedShipmentDetails($locationGroups)
    {
        $rates_option = get_option('wc_pulish_negotiate_purolator_small');
        ($rates_option == 'negotiated') ? $searchword = 'PAYOR_ACCOUNT' : $searchword = 'PAYOR_LIST';
        $allLocations = array_filter($locationGroups, function ($var) use ($searchword) {
            return preg_match("/^$searchword/", $var->ShipmentRateDetail->RateType);
        });
        return $allLocations;
    }

    /**
     * woocomerce version
     */
    function purolator_small_wc_version_number()
    {
        if (!function_exists('get_plugins'))
            require_once(ABSPATH . 'wp-admin/includes/plugin.php');

        $plugin_folder = get_plugins('/' . 'woocommerce');
        $plugin_file = 'woocommerce.php';
        $purolator_small_plugin_folders = get_plugins('/' . 'small-package-quotes-purolator-edition');
        $purolator_small_plugin_files = 'small-package-quotes-purolator-edition.php';
        $wc_plugin = (isset($plugin_folder[$plugin_file]['Version'])) ? $plugin_folder[$plugin_file]['Version'] : "";
        $purolator_small_plugin = (isset($purolator_small_plugin_folders[$purolator_small_plugin_files]['Version'])) ? $purolator_small_plugin_folders[$purolator_small_plugin_files]['Version'] : "";

        $pluginVersions = array(
            "woocommerce_plugin_version" => $wc_plugin,
            "purolator_small_plugin_version" => $purolator_small_plugin
        );

        return $pluginVersions;
    }

}
