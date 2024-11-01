<?php

/**
 * Package Group
 * @package     Woocommerce Purolator Small
 * @author      <https://eniture.com/>
 * @copyright   Copyright (c) 2017, Eniture
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get Shipping Package Class
 */
class purolator_Small_Shipping_Get_Package
{

    /** Global variable */
    public $hasLTLShipment = 0;

    /** Global variable */
    public $errors = array();
    public $order_details;

    /**
     * Grouping For Shipments
     * @param $package
     * @param $purolator_small_res_inst
     * @return array
     * @global $woocommerce
     */
    function group_purolator_small_shipment($package, $purolator_small_res_inst)
    {

        global $woocommerce;

        if (isset($package['sPackage']) && !empty($package['sPackage'])) {
            return $package['sPackage'];
        }

        $pStatus = (isset($package['itemType'])) ? $package['itemType'] : "";
        $purolator_small_woo_obj = new purolator_Small_Woo_Update_Changes();
        $sm_zipcode = $purolator_small_woo_obj->purolator_small_postcode();

        $wc_settings_wwe_ignore_items = get_option("en_ignore_items_through_freight_classification");
        $en_get_current_classes = strlen($wc_settings_wwe_ignore_items) > 0 ? trim(strtolower($wc_settings_wwe_ignore_items)) : '';
        $en_get_current_classes_arr = strlen($en_get_current_classes) > 0 ? array_map('trim', explode(',', $en_get_current_classes)) : [];
        $flat_rate_shipping_addon = apply_filters('en_add_flat_rate_shipping_addon', false);

        $purolator_small_package = array();
        if (isset($package['contents'])) {
            $pack = $package['contents'];
            foreach ($pack as $item_id => $values) {
                $locationId = 0;
                $_product = $values['data'];

                // Flat rate pricing
                $product_id = (isset($values['variation_id']) && $values['variation_id'] > 0) ? $values['variation_id'] : $_product->get_id();
                $parent_id = $product_id;
                if(isset($values['variation_id']) && $values['variation_id'] > 0){
                    $variation = wc_get_product($values['variation_id']);
                    $parent_id = $variation->get_parent_id();
                }
                $en_flat_rate_price = $this->en_get_flat_rate_price($values, $_product);
                if ($flat_rate_shipping_addon && isset($en_flat_rate_price) && strlen($en_flat_rate_price) > 0) {
                    continue;
                }

                // Get product shipping class
                $en_ship_class = strtolower($values['data']->get_shipping_class());
                if (in_array($en_ship_class, $en_get_current_classes_arr)) {
                    continue;
                }

                // Shippable handling units
                $values = apply_filters('en_shippable_handling_units_request', $values, $values, $_product);
                $shippable = [];
                if (isset($values['shippable']) && !empty($values['shippable'])) {
                    $shippable = $values['shippable'];
                }

                $nestedPercentage = 0;
                $nestedDimension = "";
                $nestedItems = "";
                $StakingProperty = "";

                $dimension_unit = get_option('woocommerce_dimension_unit');
                // Convert product dimensions in feet, centimeter, miles, kilometer into Inches
                if ($dimension_unit == 'ft' || $dimension_unit == 'cm' || $dimension_unit == 'mi' || $dimension_unit == 'km') {
                    $dimensions = $this->dimensions_conversion($_product);
                    $height = $dimensions['height'];
                    $width = $dimensions['width'];
                    $length = $dimensions['length'];
                } else {

                    $height = wc_get_dimension($_product->get_height(), 'in');
                    $width = wc_get_dimension($_product->get_width(), 'in');
                    $length = wc_get_dimension($_product->get_length(), 'in');
                }

                $height = (strlen($height) > 0) ? $height : "0";
                $width = (strlen($width) > 0) ? $width : "0";
                $length = (strlen($length) > 0) ? $length : "0";

                $product_weight = round(wc_get_weight($_product->get_weight(), 'lbs'), 2);
                $dimenssions = $length * $width * $height;
                $exceedWeight = get_option('en_plugins_return_LTL_quotes');
                $weight = ($product_weight * $values['quantity']);
                // Mutiple packages
                $en_multiple_package = $this->en_multiple_package($values, $_product);
                $freight_enable_class = $this->purolator_small_check_freight_class($_product);
                $locations_list = $this->purolator_small_origin_address($values, $_product);
                $origin_address = $purolator_small_res_inst->purolator_Small_multi_warehouse($locations_list, $sm_zipcode);
                $hazardous_material = $this->purolator_small_get_harazdous_material($values, $_product);
                $ptype = $this->purolator_small_check_product_type($freight_enable_class, $exceedWeight, $product_weight, $en_multiple_package);
                $product_level_markup = $this->purolator_small_get_product_level_markup($_product, $values['variation_id'], $product_id, $values['quantity']);

                if (!empty($origin_address) || $en_multiple_package == 'yes') {
                    $locationId = $origin_address['locationId'];
                    $purolator_small_package[$locationId]['origin'] = $origin_address;
                    $purolator_small_package[$locationId]['origin']['ptype'] = $ptype;

                    $insurance = $this->en_insurance_checked($values, $_product);

                    //  Nested Material
                    $nested_material = $this->en_nested_material($values, $_product);
                    if ($nested_material == "yes") {
                        $post_id = (isset($values['variation_id']) && $values['variation_id'] > 0) ? $values['variation_id'] : $_product->get_id();
                        $nestedPercentage = get_post_meta($post_id, '_nestedPercentage', true);
                        $nestedDimension = get_post_meta($post_id, '_nestedDimension', true);
                        $nestedItems = get_post_meta($post_id, '_maxNestedItems', true);
                        $StakingProperty = get_post_meta($post_id, '_nestedStakingProperty', true);
                    }

                    if (!$_product->is_virtual()) {
                        $hm_plan = apply_filters('purolator_small_quotes_plans_suscription_and_features', 'hazardous_material');
                        $hm_status = (!is_array($hm_plan) && $hazardous_material == 'yes') ? TRUE : FALSE;

                        // Shippable handling units
                        $ship_item_alone = '0';
                        extract($shippable);

                        $product_title = str_replace(array("'", '"'), '', $_product->get_title());
//                        $purolator_small_package[$locationId]['items'][] = $this->purolator_small_get_items_data($values, $_product, $product_weight, $length, $width, $height, $hazardous_material, $ptype);
                        $en_items = array(
                            'productId' => $parent_id,
                            'productName' => str_replace(array("'", '"'), '', $_product->get_name()),
                            'productQty' => $values['quantity'],
                            'productPrice' => $_product->get_price(),
                            'productWeight' => $product_weight,
                            'productLength' => $length,
                            'productWidth' => $width,
                            'productHeight' => $height,
                            'hazardousMaterial' => ($hazardous_material == 'yes') ? 'yes' : 'no',
                            'ptype' => $ptype,
                            'product_name' => $values['quantity'] . " x " . $product_title,
                            'products' => $product_title,
                            'nestedMaterial' => $nested_material,
                            'nestedPercentage' => $nestedPercentage,
                            'nestedDimension' => $nestedDimension,
                            'nestedItems' => $nestedItems,
                            'stakingProperty' => $StakingProperty,
                            // FDO
                            'hazardousMaterial' => $hm_status,
                            'productType' => ($_product->get_type() == 'variation') ? 'variant' : 'simple',
                            'productSku' => $_product->get_sku(),
                            'actualProductPrice' => $_product->get_price(),
                            'attributes' => $_product->get_attributes(),
                            'variantId' => ($_product->get_type() == 'variation') ? $_product->get_id() : '',
                            'hazmat' => $hazardous_material,

                            // Shippable handling units
                            'ship_item_alone' => $ship_item_alone,
                            'markup' => $product_level_markup
                        );

                        // Hook for flexibility adding to package
                        $en_items = apply_filters('en_group_package', $en_items, $values, $_product);

                        $purolator_small_package[$locationId]['items'][] = $en_items;

                        // Hazardous Material
                        if ($hazardous_material == "yes" && !isset($purolator_small_package[$locationId]['hazardousMaterial'])) {
                            $purolator_small_package[$locationId]['hazardousMaterial'] = TRUE;
                            $purolator_small_package[$locationId]['hazardous_material'] = TRUE;
                        }

                        // Except Ground Transit
                        $exempt_ground_transit_restriction = $this->exempt_ground_transit_restriction($values, $_product);
                        if($exempt_ground_transit_restriction == 'yes' && !isset($purolator_small_package[$locationId]['exempt_ground_transit_restriction'])){
                            $purolator_small_package[$locationId]['exempt_ground_transit_restriction'] = 1;
                        }

//                        $purolator_small_package[$locationId]['hazardousMaterial'] = $this->purolator_small_get_hazardous_index($purolator_small_package[$locationId]['items']);
                    }
                }
                if ($pStatus == '' && $ptype == 'ltl') {
                    return $purolator_small_package = array();
                }
                if ($dimenssions == 0 && $product_weight == 0) {
                    $purolator_small_package[$locationId]['no_parameter'] = 'NOPARAM';
                }
                /* Order widget details */
                if (isset($purolator_small_package[$locationId]['origin'])) {
                    $this->order_details['details'][$purolator_small_package[$locationId]['origin']['zip']] = $purolator_small_package[$locationId];
                    $this->order_details['details'][$purolator_small_package[$locationId]['origin']['zip']]['location_type'] = $purolator_small_package[$locationId]['origin']['location'];
                }
            }

            do_action("eniture_debug_mood", "Product Details (purolator-s)", $purolator_small_package);
            return $purolator_small_package;
        }
        return false;
    }

    /**
     *  Get the product multiple package checkbox value.
     */
    public function en_multiple_package($product_object, $product_detail)
    {
        $post_id = (isset($product_object['variation_id']) && $product_object['variation_id'] > 0) ? $product_object['variation_id'] : $product_detail->get_id();
        return get_post_meta($post_id, '_en_multiple_packages', true);
    }

    /**
     *
     * @param array $values
     * @param array $_product
     * @return string
     */
    function en_insurance_checked($values, $_product)
    {
        $post_id = (isset($values['variation_id']) && $values['variation_id'] > 0) ? $values['variation_id'] : $_product->get_id();
        return get_post_meta($post_id, '_en_insurance_fee', true);
    }

    /**
     * Nested Material
     * @param array type $values
     * @param array type $_product
     * @return string type
     */
    function en_nested_material($values, $_product)
    {
        $post_id = (isset($values['variation_id']) && $values['variation_id'] > 0) ? $values['variation_id'] : $_product->get_id();
        return get_post_meta($post_id, '_nestedMaterials', true);
    }

    /**
     * products items array
     * @param $values
     * @param $_product
     * @param $product_weight
     * @param $length
     * @param $width
     * @param $height
     * @param $hazardous_material
     * @param $ptype
     * @return array
     */
    function purolator_small_get_items_data($values, $_product, $product_weight, $length, $width, $height, $hazardous_material, $ptype)
    {
        $items_data_arr = array(
            'productId' => $_product->get_id(),
            'productName' => $_product->get_title(),
            'productQty' => $values['quantity'],
            'productPrice' => $_product->get_price(),
            'productWeight' => $product_weight,
            'productLength' => $length,
            'productWidth' => $width,
            'productHeight' => $height,
            'hazardousMaterial' => ($hazardous_material == 'yes') ? 'yes' : 'no',
            'ptype' => $ptype
        );

        return $items_data_arr;
    }

    /**
     * hazadous material is on or off
     * @param $values
     * @param $_product
     */
    function purolator_small_get_harazdous_material($values, $_product)
    {

        if ($_product->get_type() == 'variation') {
            $hazardous_material = get_post_meta($values['variation_id'], '_hazardousmaterials', true);
        } else {
            $hazardous_material = get_post_meta($_product->get_id(), '_hazardousmaterials', true);
        }

        return $hazardous_material;
    }

    /**
     * Get Enabled Shipping Class Of Product
     * @param $_product
     */
    function purolator_small_check_freight_class($_product)
    {
        if ($_product->get_type() == 'variation') {

            $ship_class_id = $_product->get_shipping_class_id();

            if ($ship_class_id == 0) {
                $parent_data = $_product->get_parent_data();
                $get_parent_term = get_term_by('id', $parent_data['shipping_class_id'], 'product_shipping_class');
                $freight_enable_class = (isset($get_parent_term->slug)) ? $get_parent_term->slug : "";
            } else {
                $freight_enable_class = $_product->get_shipping_class();
            }
        } else {
            $freight_enable_class = $_product->get_shipping_class();
        }

        return $freight_enable_class;
    }

    /**
     * Check Product Type
     * @param $freight_enable_class
     * @param $exceedWeight
     * @param $weight
     * @return string
     */
    function purolator_small_check_product_type($freight_enable_class, $exceedWeight, $weight, $en_multiple_package)
    {
        if ($freight_enable_class == 'ltl_freight') {
            $ptype = 'ltl';
        } elseif ($exceedWeight == 'yes' && ($weight > 150 && $en_multiple_package != 'yes')) {
            $ptype = 'ltl';
        } else {
            $ptype = 'small';
        }

        return $ptype;
    }

    /**
     * Get Origin Address
     * @param $values
     * @param $_product
     * @global $wpdb
     */
    function purolator_small_origin_address($values, $_product)
    {
        global $wpdb;

        //      UPDATE QUERY In-store pick up                           
        $en_wd_update_query_string = apply_filters("en_wd_update_query_string", "");
        $locations_list = [];
        (isset($values['variation_id']) && $values['variation_id'] > 0) ? $post_id = $values['variation_id'] : $post_id = $_product->get_id();
        $enable_dropship = get_post_meta($post_id, '_enable_dropship', true);
        if ($enable_dropship == 'yes') {
            $get_loc = get_post_meta($post_id, '_dropship_location', true);
            if ($get_loc == '') {
                return array('error' => 'purolator small dp location not found!');
            }

            //          Multi Dropship
            $multi_dropship = apply_filters('purolator_small_quotes_plans_suscription_and_features', 'multi_dropship');

            if (is_array($multi_dropship)) {
                $locations_list = $wpdb->get_results(
                    "SELECT id, city, state, zip, country, location, origin_markup " . $en_wd_update_query_string . "FROM " . $wpdb->prefix . "warehouse WHERE location = 'dropship' LIMIT 1"
                );
            } else {
                $get_loc = ($get_loc !== '') ? maybe_unserialize($get_loc) : $get_loc;
                $get_loc = is_array($get_loc) ? implode(" ', '", $get_loc) : $get_loc;
                $locations_list = $wpdb->get_results(
                    "SELECT id, city, state, zip, country, location, nickname, origin_markup " . $en_wd_update_query_string . "FROM " . $wpdb->prefix . "warehouse WHERE id IN ('" . $get_loc . "')"
                );
            }

            $eniture_debug_name = "Dropships";
        }

        if (empty($locations_list)) {
            // Multi Warehouse
            $multi_warehouse = apply_filters('purolator_small_quotes_plans_suscription_and_features', 'multi_warehouse');
            if (is_array($multi_warehouse)) {
                $locations_list = $wpdb->get_results(
                    "SELECT id, city, state, zip, country, location, origin_markup " . $en_wd_update_query_string . "FROM " . $wpdb->prefix . "warehouse WHERE location = 'warehouse' LIMIT 1"
                );
            } else {
                $locations_list = $wpdb->get_results(
                    "SELECT id, city, state, zip, country, location, origin_markup " . $en_wd_update_query_string . "FROM " . $wpdb->prefix . "warehouse WHERE location = 'warehouse'"
                );
            }

            $eniture_debug_name = "Warehouses";
        }

        do_action("eniture_debug_mood", "Quotes $eniture_debug_name (purolator-s)", $locations_list);
        return $locations_list;
    }

    /**
     * parameters $_product
     * return $dimensions in inches
     */
    function dimensions_conversion($_product)
    {

        $dimension_unit = get_option('woocommerce_dimension_unit');
        $dimensions = array();
        $height = is_numeric($_product->get_height()) ? $_product->get_height() : 0;
        $width = is_numeric($_product->get_width()) ? $_product->get_width() : 0;
        $length = is_numeric($_product->get_length()) ? $_product->get_length() : 0;
        switch ($dimension_unit) {

            case 'ft':
                $dimensions['height'] = round($height * 12, 2);
                $dimensions['width'] = round($width * 12, 2);
                $dimensions['length'] = round($length * 12, 2);
                break;

            case 'cm':
                $dimensions['height'] = round($height * 0.3937007874, 2);
                $dimensions['width'] = round($width * 0.3937007874, 2);
                $dimensions['length'] = round($length * 0.3937007874, 2);
                break;

            case 'mi':
                $dimensions['height'] = round($height * 63360, 2);
                $dimensions['width'] = round($width * 63360, 2);
                $dimensions['length'] = round($length * 63360, 2);
                break;

            case 'km':
                $dimensions['height'] = round($height * 39370.1, 2);
                $dimensions['width'] = round($width * 39370.1, 2);
                $dimensions['length'] = round($length * 39370.1, 2);
                break;
        }

        return $dimensions;
    }

    /**
     * Check except transit time restriction
     * @param array $values
     * @param array $_product
     * @return string
     */
    function exempt_ground_transit_restriction($values, $_product)
    {
        $post_id = (isset($values['variation_id']) && $values['variation_id'] > 0) ? $values['variation_id'] : $_product->get_id();
        return get_post_meta($post_id, '_en_exempt_ground_transit_restriction', true);
    }

    /**
     * Returns flat rate price and quantity
     */
    function en_get_flat_rate_price($values, $_product)
    {
        if ($_product->get_type() == 'variation') {
            $flat_rate_price = get_post_meta($values['variation_id'], 'en_flat_rate_price', true);
            if (strlen($flat_rate_price) < 1) {
                $flat_rate_price = get_post_meta($values['product_id'], 'en_flat_rate_price', true);
            }
        } else {
            $flat_rate_price = get_post_meta($_product->get_id(), 'en_flat_rate_price', true);
        }

        return $flat_rate_price;
    }

    /**
     * Returns product level markup
     */
    function purolator_small_get_product_level_markup($_product, $variation_id, $product_id, $quantity)
    {
        $product_level_markup = 0;
        if ($_product->get_type() == 'variation') {
            $product_level_markup = get_post_meta($variation_id, '_en_product_markup_variation', true);
            if(empty($product_level_markup) || $product_level_markup == 'get_parent'){
                $product_level_markup = get_post_meta($_product->get_id(), '_en_product_markup', true);
            }
        } else {
            $product_level_markup = get_post_meta($_product->get_id(), '_en_product_markup', true);
        }

        if(empty($product_level_markup)) {
            $product_level_markup = get_post_meta($product_id, '_en_product_markup', true);
        }

        if(!empty($product_level_markup) && strpos($product_level_markup, '%') === false 
        && is_numeric($product_level_markup) && is_numeric($quantity))
        {
            $product_level_markup *= $quantity;
            
        }else if(!empty($product_level_markup) && strpos($product_level_markup, '%') > 0 && is_numeric($quantity)){
            $position = strpos($product_level_markup, '%');
            $first_str = substr($product_level_markup, $position);
            $arr = explode($first_str, $product_level_markup);
            $percentage_value = $arr[0];
            $product_price = $_product->get_price();
            if(!empty($product_price)){
                $product_level_markup = $percentage_value / 100 * ($product_price * $quantity);
            }else{
                $product_level_markup = 0;
            }
        }

        return $product_level_markup;
    }

}
