<?php

/**
 * Includes Ajax Request class
 */
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists("EnWooWdAddonsAjaxReqIncludes")) {

    class EnWooWdAddonsAjaxReqIncludes
    {

        public $plugin_standards;
        public $selected_plan;
        public $EnWooAddonAutoResidDetectionTemplate;

        /**
         * Get Address ajax request
         */
        public function __construct()
        {
            add_action('wp_ajax_nopriv_en_wd_get_address', array($this, 'get_address_api_ajax'));
            add_action('wp_ajax_en_wd_get_address', array($this, 'get_address_api_ajax'));

            add_action('wp_ajax_nopriv_en_wd_delete_dropship', array($this, 'delete_dropship_ajax'));
            add_action('wp_ajax_en_wd_delete_dropship', array($this, 'delete_dropship_ajax'));

            add_action('wp_ajax_nopriv_en_wd_save_warehouse', array($this, 'save_warehouse_ajax'));
            add_action('wp_ajax_en_wd_save_warehouse', array($this, 'save_warehouse_ajax'));

            add_action('wp_ajax_nopriv_en_wd_save_dropship', array($this, 'save_dropship_ajax'));
            add_action('wp_ajax_en_wd_save_dropship', array($this, 'save_dropship_ajax'));


            add_action('wp_ajax_nopriv_en_wd_edit_dropship', array($this, 'edit_dropship_ajax'));
            add_action('wp_ajax_en_wd_edit_dropship', array($this, 'edit_dropship_ajax'));

            add_action('wp_ajax_nopriv_en_wd_delete_warehouse', array($this, 'delete_warehouse_ajax'));
            add_action('wp_ajax_en_wd_delete_warehouse', array($this, 'delete_warehouse_ajax'));

            add_action('wp_ajax_nopriv_en_wd_edit_warehouse', array($this, 'edit_warehouse_ajax'));
            add_action('wp_ajax_en_wd_edit_warehouse', array($this, 'edit_warehouse_ajax'));
        }

        /**
         * Get Address From ZipCode Using API
         */
        function get_address_api_ajax()
        {
            if (isset($_POST['origin_zip'])) {
                $map_address = (isset($_POST['origin_zip'])) ? sanitize_text_field($_POST['origin_zip']) : "";
                $zipCode = str_replace(' ', '', $map_address);
                $accessLevel = 'address';
                $Get_sm_distance = new Get_purolator_small_distance();
                $resp_json = $Get_sm_distance->purolator_small_address($zipCode, $accessLevel);
                $map_result = json_decode($resp_json, true);
                $city = "";
                $state = "";
                $country = "";
                $postcode_localities = 0;
                $address_type = $city_name = $city_option = '';
                if (isset($map_result['error']) && !empty($map_result['error'])) {
                    echo json_encode(array('apiResp' => 'apiErr'));
                    exit;
                }
                if (isset($map_result['results'], $map_result['status']) && (empty($map_result['results'])) && ($map_result['status'] == "ZERO_RESULTS")) {
                    echo json_encode(array('result' => 'ZERO_RESULTS'));
                    exit;
                }
                if (count($map_result['results']) == 0) {
                    echo json_encode(array('result' => 'false'));
                    exit;
                }
                $first_city = '';
                if (count($map_result['results']) > 0) {
                    $arrComponents = $map_result['results'][0]['address_components'];
                    if (isset($map_result['results'][0]['postcode_localities']) && $map_result['results'][0]['postcode_localities']) {
                        foreach ($map_result['results'][0]['postcode_localities'] as $index => $component) {
                            $first_city = ($index == 0) ? $component : $first_city;
                            $city_option .= '<option value="' . trim($component) . ' "> ' . $component . ' </option>';
                        }
                        $city = '<select id="' . $address_type . '_city" class="city-multiselect select en_wd_multi_state city_select_css" name="' . $address_type . '_city" aria-required="true" aria-invalid="false">
                                    ' . $city_option . '</select>';
                        $postcode_localities = 1;
                    } elseif ($arrComponents) {
                        foreach ($arrComponents as $index => $component) {
                            $type = $component['types'][0];
                            if ($city == "" && ($type == "sublocality_level_1" || $type == "locality")) {
                                $city_name = trim($component['long_name']);
                            }
                        }
                    }
                    if ($arrComponents) {
                        foreach ($arrComponents as $index => $state_app) {
                            $type = $state_app['types'][0];
                            if ($state == "" && ($type == "administrative_area_level_1")) {
                                $state_name = trim($state_app['short_name']);
                                $state = $state_name;
                            }
                            if ($country == "" && ($type == "country")) {
                                $country_name = trim($state_app['short_name']);
                                $country = $country_name;
                            }
                        }
                    }
                    echo json_encode(array('first_city' => $first_city, 'city' => $city_name, 'city_option' => $city, 'state' => $state, 'country' => $country, 'postcode_localities' => $postcode_localities));
                    exit;
                }
            }
        }

        /**
         * Validate Input Fields
         * @param type $sPostData
         * @return string
         */
        function pkg_validate_post_data($sPostData)
        {
            foreach ($sPostData as $key => &$tag) {
                $check_characters = $key == "city" ? preg_match('/[#$%@^&!_*()+=\[\]\';,\/{}|":<>?~\\\\]/', $tag) : preg_match('/[#$%@^&!_*()+=\-\[\]\';,\/{}|":<>?~\\\\]/', $tag);
                if ($check_characters != 1 ||
                    $key == "match_postal_local_delivery" ||
                    $key == "match_postal_store_pickup" ||
                    $key == "checkout_desc_local_delivery" ||
                    $key == "checkout_desc_store_pickup" ||
                    $key == "nickname" || 
                    $key == "origin_markup") {
                    $data[$key] = sanitize_text_field($tag);
                } else {
                    $data[$key] = 'Error';
                }
            }

            return $data;
        }

        /**
         * Filtered Data Array
         * @param $validateData
         * @return array
         */
        function filtered_data($validateData)
        {
            return array(
                'city' => $validateData["city"],
                'state' => $validateData["state"],
                'zip' => preg_replace('/\s+/', '', $validateData["zip"]),
                'country' => $validateData["country"],
                'location' => $validateData["location"],
                'nickname' => (isset($validateData["nickname"])) ? $validateData["nickname"] : "",
            );
        }

        /**
         * Save Warehouse Function
         * @global $wpdb
         */
        function save_warehouse_ajax()
        {
            global $wpdb;
            $inputData = $_POST;
            $html = "";
            if (isset($inputData['origin_country']) && $inputData['origin_country'] != '') {

                $countrycode = strtolower($inputData['origin_country']);

                $inputData['origin_country'] = ($countrycode == 'un') ? 'US' : $inputData['origin_country'];
            }
            $input_data_arr = array(
                'city' => $inputData['origin_city'],
                'state' => $inputData['origin_state'],
                'zip' => $inputData['origin_zip'],
                'country' => $inputData['origin_country'],
                'location' => $inputData['location'],
                'enable_store_pickup' => ($inputData['enable_instore'] === 'true') ? 1 : 0,
                'miles_store_pickup' => $inputData['address_miles_instore'],
                'match_postal_store_pickup' => $inputData['zipmatch_instore'],
                'checkout_desc_store_pickup' => $inputData['desc_instore'],
                'enable_local_delivery' => ($inputData['enable_delivery'] === 'true') ? 1 : 0,
                'miles_local_delivery' => $inputData['address_miles_delivery'],
                'match_postal_local_delivery' => $inputData['zipmatch_delivery'],
                'checkout_desc_local_delivery' => $inputData['desc_delivery'],
                'fee_local_delivery' => $inputData['fee_delivery'],
                'suppress_local_delivery' => ($inputData['supppress_delivery'] === 'true') ? 1 : 0,
                'origin_markup'=> $inputData['origin_markup']
            );

            $validateData = $this->pkg_validate_post_data($input_data_arr);
            $get_warehouse = $wpdb->get_results(
                "SELECT * FROM " . $wpdb->prefix . "warehouse WHERE city = '" . $validateData["city"] . "' && state = '" . $validateData["state"] . "' && zip = '" . $validateData["zip"] . "'");

            $insert_qry = $update_qry = '';
            if ($validateData["city"] != 'Error') {
                $data = $validateData;

                if (isset($validateData["city"])) {
                    $get_warehouse_id = (isset($_POST['origin_id']) && intval($_POST['origin_id'])) ? $_POST['origin_id'] : "";
                    if ($get_warehouse_id && (empty($get_warehouse) || (!empty($get_warehouse) && reset($get_warehouse)->id == $get_warehouse_id))) {
                        $update_qry = $wpdb->update(
                            $wpdb->prefix . 'warehouse', $data, array('id' => $get_warehouse_id)
                        );

                        $update_qry = (!empty($get_warehouse) && reset($get_warehouse)->id == $get_warehouse_id) ? 1 : $update_qry;
                    } else {
                        if (empty($get_warehouse)) {
                            $insert_qry = $wpdb->insert(
                                $wpdb->prefix . 'warehouse', $data
                            );

                            $html = warehouse_template(TRUE);
                        }
                    }
                }
                $lastid = $wpdb->insert_id;
                if ($lastid == 0) {
                    $lastid = $get_warehouse_id;
                }
                $warehous_list = array('origin_city' => $data["city"], 'origin_state' => $data["state"], 'origin_zip' => $data["zip"], 'origin_country' => $data["country"], 'insert_qry' => $insert_qry, 'update_qry' => $update_qry, 'id' => $lastid, 'html' => $html);
                echo json_encode($warehous_list);
                exit;
            } else {
                echo "false";
                exit;
            }
        }


        /**
         * Edit Warehouse Function
         * @global $wpdb
         */
        function edit_warehouse_ajax()
        {
            global $wpdb;
            $get_warehouse_id = (isset($_POST['edit_id']) && intval($_POST['edit_id'])) ? $_POST['edit_id'] : "";
            $warehous_list = $wpdb->get_results(
                "SELECT id, city, state, zip, country , enable_store_pickup , fee_local_delivery , suppress_local_delivery , miles_store_pickup , match_postal_store_pickup , checkout_desc_store_pickup , enable_local_delivery , miles_local_delivery , match_postal_local_delivery , checkout_desc_local_delivery, origin_markup FROM " . $wpdb->prefix . "warehouse WHERE id=$get_warehouse_id"
            );
            echo json_encode($warehous_list);
            exit;
        }


        /**
         * Delete Warehouse Function
         * @global $wpdb
         */
        function delete_warehouse_ajax()
        {
            global $wpdb;
            $get_warehouse_id = (isset($_POST['delete_id']) && intval($_POST['delete_id'])) ? $_POST['delete_id'] : "";
            $qry = $wpdb->delete($wpdb->prefix . 'warehouse', array('id' => $get_warehouse_id, 'location' => 'warehouse'));

            $html = warehouse_template(TRUE);
            echo json_encode($html);
            exit;
        }


        /**
         * Save Dropship Function
         * @global $wpdb
         */
        function save_dropship_ajax()
        {
            global $wpdb;
            $inputData = $_POST;
            $html = "";

            if (isset($inputData['dropship_country']) && $inputData['dropship_country'] != '') {

                $countrycode = strtolower($inputData['dropship_country']);

                $inputData['dropship_country'] = ($countrycode == 'un') ? 'US' : $inputData['dropship_country'];
            }

            $input_data_arr = array(
                'city' => $inputData['dropship_city'],
                'state' => $inputData['dropship_state'],
                'zip' => $inputData['dropship_zip'],
                'country' => $inputData['dropship_country'],
                'location' => $inputData['location'],
                'nickname' => $inputData['nickname'],

                'enable_store_pickup' => ($inputData['enable_instore'] === 'true') ? 1 : 0,
                'miles_store_pickup' => $inputData['address_miles_instore'],
                'match_postal_store_pickup' => $inputData['zipmatch_instore'],
                'checkout_desc_store_pickup' => $inputData['desc_instore'],
                'enable_local_delivery' => ($inputData['enable_delivery'] === 'true') ? 1 : 0,
                'miles_local_delivery' => $inputData['address_miles_delivery'],
                'match_postal_local_delivery' => $inputData['zipmatch_delivery'],
                'checkout_desc_local_delivery' => $inputData['desc_delivery'],
                'fee_local_delivery' => $inputData['fee_delivery'],
                'suppress_local_delivery' => ($inputData['supppress_delivery'] === 'true') ? 1 : 0,
                'origin_markup'=> $inputData['origin_markup']
            );
            $validateData = $this->pkg_validate_post_data($input_data_arr);
            $get_dropship = $wpdb->get_results(
                "SELECT * FROM " . $wpdb->prefix . "warehouse WHERE city = '" . $validateData["city"] . "' && state = '" . $validateData["state"] . "' && zip = '" . $validateData["zip"] . "' && nickname = '" . $validateData["nickname"] . "'");
            $insert_qry = $update_qry = '';
            if ($validateData["city"] != 'Error' && $validateData["nickname"] != 'Error') {
                $data = $validateData;

                if (isset($validateData["city"])) {
                    $get_dropship_id = (isset($_POST['dropship_id']) && intval($_POST['dropship_id'])) ? $_POST['dropship_id'] : "";

                    if ($get_dropship_id != '' && (empty($get_dropship) || (!empty($get_dropship) && reset($get_dropship)->id == $get_dropship_id))) {
                        $update_qry = $wpdb->update(
                            $wpdb->prefix . 'warehouse', $data, array('id' => $get_dropship_id)
                        );

                        $update_qry = (!empty($get_dropship) && reset($get_dropship)->id == $get_dropship_id) ? 1 : $update_qry;
                    } else {
                        if (empty($get_dropship)) {
                            $insert_qry = $wpdb->insert(
                                $wpdb->prefix . 'warehouse', $data
                            );

                            $html = dropship_template(TRUE);
                        }
                    }
                }
                $lastid = $wpdb->insert_id;
                if ($lastid == 0) {
                    $lastid = $get_dropship_id;
                }
                $warehous_list = array('nickname' => $data["nickname"], 'origin_city' => $data["city"], 'origin_state' => $data["state"], 'origin_zip' => $data["zip"], 'origin_country' => $data["country"], 'insert_qry' => $insert_qry, 'update_qry' => $update_qry, 'id' => $lastid, 'html' => $html);
                echo json_encode($warehous_list);
                exit;
            } else {
                echo "false";
                exit;
            }
        }


        /**
         * Edit Dropship Function
         * @global $wpdb
         */
        function edit_dropship_ajax()
        {
            global $wpdb;
            $get_dropship_id = (isset($_POST['dropship_edit_id']) && intval($_POST['dropship_edit_id'])) ? $_POST['dropship_edit_id'] : "";
            $warehous_list = $wpdb->get_results(
                "SELECT id, city, state, zip, country, nickname , enable_store_pickup , fee_local_delivery , suppress_local_delivery , miles_store_pickup , match_postal_store_pickup , checkout_desc_store_pickup , enable_local_delivery , miles_local_delivery , match_postal_local_delivery , checkout_desc_local_delivery, origin_markup FROM " . $wpdb->prefix . "warehouse WHERE id=$get_dropship_id"
            );
            echo json_encode($warehous_list);
            exit;
        }


        /**
         * Delete Dropship Function
         * @global $wpdb
         */
        function delete_dropship_ajax()
        {

            global $wpdb;

            $get_dropship_id = (isset($_POST['dropship_delete_id']) && intval($_POST['dropship_delete_id'])) ? $_POST['dropship_delete_id'] : "";
            $get_dropship_array = array($_POST['dropship_delete_id']);
            $get_dropship_val = array_map('intval', $get_dropship_array);
            $ser = maybe_serialize($get_dropship_val);
            $get_post_id = $wpdb->get_results("SELECT group_concat(post_id) as post_ids_list FROM `" . $wpdb->prefix . "postmeta` WHERE `meta_key` = '_dropship_location' AND (`meta_value` LIKE '%" . $ser . "%' OR `meta_value` = '" . $_POST['dropship_delete_id'] . "')");
            $post_id = reset($get_post_id)->post_ids_list;

            if (isset($post_id)) {
                $wpdb->query("UPDATE `" . $wpdb->prefix . "postmeta` SET `meta_value` = '' WHERE `meta_key` IN('_enable_dropship','_dropship_location') AND `post_id` IN ($post_id)");
            }

            $qry = $wpdb->delete($wpdb->prefix . "warehouse", array('id' => $get_dropship_id, 'location' => 'dropship'));

            $html = dropship_template(TRUE);
            echo json_encode($html);
            exit;
        }

    }
}

new EnWooWdAddonsAjaxReqIncludes();
