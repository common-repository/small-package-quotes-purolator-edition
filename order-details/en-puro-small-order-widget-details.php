<?php
/**
 * WWE Small Group Packaging
 *
 * @package     WWE Small Quotes
 * @author      Eniture-Technology
 */
if (!defined('ABSPATH')) {
    exit;
}
if (!class_exists("En_Puro_Small_Order_Widget_Details")) {
    class En_Puro_Small_Order_Widget_Details
    {

        /**
         * Handling fee status
         * @var string
         */
        public $handling_fee;

        /**
         * Selected shipping status.
         * @var string/int
         */
        public $ship_status;

        /**
         *  current curreny symbol.
         * @var string
         */
        public $currency_symbol;

        /**
         *  Response of order from our custom table.
         * @var array
         */
        public $result_details;

        /**
         * Order key.
         * @var string
         */
        public $order_key;

        /**
         * Selected shippping title.
         * @var type
         */
        public $shipping_method_title;

        /**
         * Selected shippping ID.
         * @var string
         */
        public $shipping_method_id;

        /**
         * Selected shippping price.
         * @var int/float/string
         */
        public $shipping_method_total;

        /**
         * Set 1 if any eniture service selected.
         * @var string
         */
        public $shipment_status;

        /**
         * Set 1 if any eniture service selected.
         * @var string
         */
        public $hazardous_material;

        /**
         * Helper object.
         * @var object
         */
        public $helper_obj;

        /**
         * Multishipment id.
         * @var string
         */
        public $multi_ship_id;

        /**
         * Constructor.
         */
        public function __construct()
        {
            $this->multi_ship_id = 'multi_purolator_small';
            $this->helper_obj = new En_Pur_Sml_Helper_Class();
            $this->en_call_hooks();
        }

        /**
         * Call needed hooks.
         */
        public function en_call_hooks()
        {

            /* Woocommerce order action hook */
            add_filter(
                'woocommerce_thankyou',
                array($this, 'en_woocommerce_save_order_detail_puro_small'), 10,
                1
            );

            /* Woocommerce order action hook */
            add_action(
                'woocommerce_order_actions',
                array($this, 'en_assign_order_details_pruo_smll'), 10
            );
        }

        /**
         * Adding Meta container admin shop_order pages
         * @param $actions
         */
        function en_create_meta_box_order_details()
        {
            $this->en_assign_order_details();
        }

        /**
         * Assign order details.
         */
        function en_assign_order_details_pruo_smll($actions)
        {
            global $wpdb;
            $this->shipment_status = 'single';
            $order_id = get_the_ID();
            $order = new WC_Order($order_id);
            $this->order_key = $order->get_order_key();
            $shipping_details = $order->get_items('shipping');

            foreach ($shipping_details as $item_id => $shipping_item_obj) {
                $this->shipping_method_title = $shipping_item_obj->get_method_title();
                $this->shipping_method_id = $shipping_item_obj->get_method_id();
                $this->shipping_method_total = $shipping_item_obj->get_total();
            }

            $this->result_details = [];
            $enit_order_details_table = $wpdb->prefix . "enit_order_details";
            $query = $wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($enit_order_details_table));
            if ($wpdb->get_var($query) == $enit_order_details_table) {
                $this->result_details = $wpdb->get_results(
                    "SELECT * FROM `" . $wpdb->prefix . "enit_order_details` WHERE order_id = '" . $this->order_key . "'", ARRAY_A
                );
            }

            /* Add metabox if user selected our service */
            if (!empty($this->result_details) && count($this->result_details) > 0) {
                /* Add metabox for 3dbin visual details */
                add_meta_box(
                    'en_additional_order_details',
                    __('Additional Order Details', 'woocommerce'),
                    array($this, 'en_add_meta_box_order_widget'), 'shop_order',
                    'side', 'low', 'core');
            }

            return $actions;
        }

        /**
         * Add order details in metabox.
         */
        public function en_add_meta_box_order_widget()
        {
            /* In case of single shipment remove index 0 */
            if (count($this->result_details) == 1) {
                $order_details = reset($this->result_details);
            }

            /* In case of multishipment */
            if (count($this->result_details) > 1) {
                $order_details = $this->en_return_multiship_row($this->result_details);
            }


            /* Check multi-shipment or single-shipment */
            if (!is_array(json_decode($order_details['data'], true))) {
                $this->shipment_status = 'multishipment';
                $this->en_multi_shipment_order($order_details,
                    $this->shipment_status, $this->order_key);
            } elseif (is_array(json_decode($order_details['data'], true))) {

                $this->shipment_status = 'single';
                $single_price_details['ship_details'] = array(
                    'title' => $this->shipping_method_title,
                    'id' => $this->shipping_method_id,
                    'rate' => $this->shipping_method_total,
                );
                $this->en_single_shipment_order($order_details,
                    $this->shipment_status, $single_price_details);
            }
        }

        /**
         * Return the multiship row.
         */
        public function en_return_multiship_row($details)
        {
            foreach ($details as $key => $value) {
                $data = json_decode($value['data']);

                if (is_string($data)) {
                    return $value;
                }
            }
            return false;
        }

        /**
         * Single shipment order details.
         */
        function en_single_shipment_order($order_details, $shipment_status, $single_price_details)
        {

            $ship_count = 1;
            $service_details = reset($order_details);
            $this->en_origin_services_details($order_details, $shipment_status,
                $ship_count, $single_price_details);
        }

        /**
         * Multi shipment order details.
         */
        function en_multi_shipment_order($order_details, $shipment_status, $order_key)
        {
            global $wpdb;
            $cheapest_ids = explode(", ", $order_details['data']);
            $ship_count = 1;

            foreach ($cheapest_ids as $key => $value) {

                $service_id = str_replace('"', "", $value);
                $service_details = $this->en_get_service_details_by_id($service_id,
                    $order_key);
                $this->en_origin_services_details($service_details[0],
                    $shipment_status, $ship_count);
                $ship_count++;
                /* Horizontal line */
                echo "<hr>";
            }
        }

        /**
         * Get service details from id
         */
        function en_get_service_details_by_id($id, $order_key)
        {
            global $wpdb;
            $result_details = $wpdb->get_results(
                "SELECT * FROM `" . $wpdb->prefix . "enit_order_details` WHERE `service_id` = '" . $id . "' AND order_id = '" . $order_key . "'",
                ARRAY_A
            );
            return $result_details;
        }

        /**
         * Origin & Services details.
         */
        function en_origin_services_details($order_data, $shipment_status, $ship_count, $single_price_details = array())
        {

            $this->currency_symbol = get_woocommerce_currency_symbol(get_option('woocommerce_currency'));
            $code = $order_data['service_id'];
            $service_order_data = json_decode($order_data['data']);
            /* In case of single shipment reset the array */
            if ($shipment_status == 'single') {
                $service_order_data = reset($service_order_data);
            }

            echo '<h4 style="text-decoration: underline;margin: 4px 0px 4px 0px;">Shipment ' . $ship_count . " > Origin & Services </h4>";
            echo '<ul class="en-list" style="list-style: disc;    list-style-position: inside;">';
            echo '<li>';
            echo ucwords($service_order_data->origin->location) . ', ';
            echo $service_order_data->origin->zip . ', ';
            echo $service_order_data->origin->city . ', ';
            echo $service_order_data->origin->state . ', ';
            echo $service_order_data->origin->country . "<br />";
            echo '</li>';

            /* Run in case of multishipment only */
            if ($shipment_status != 'single') {

                if (
                    isset($service_order_data->accessorials->R) &&
                    $service_order_data->accessorials->R == 'R'
                ) {

                    if (isset($service_order_data->cheapest_services->title) && $service_order_data->cheapest_services->title != '') {
                        $resd = '(R) ';
                        $title = $service_order_data->cheapest_services->title . ' : ';
                    } else {
                        $resd = '';
                        $title = '';
                    }
                    /* Run in case of single shipment only */
                    if (isset($service_order_data->cheapest_services->totalPrice)) {
                        echo '<li>';
                        echo $title . ' ' . $resd . ' ' . $this->en_format_price($service_order_data->cheapest_services->totalPrice);
                        echo '</li>';
                    } else {
                        echo '<li>';
                        echo $title . ' ' . $resd . ' ' . $this->currency_symbol . '0.00';
                        echo '</li>';
                    }
                } else {
                    if (isset($service_order_data->cheapest_services->title) && $service_order_data->cheapest_services->title != '') {
                        $resd = $service_order_data->cheapest_services->title . ' : ';
                    } else {
                        $resd = '';
                    }
                    /* Run in case of single shipment only */
                    if (isset($service_order_data->cheapest_services->totalPrice)) {
                        echo '<li>';
                        echo $resd . '  ' . $this->en_format_price($service_order_data->cheapest_services->totalPrice);
                        echo '</li>';
                    } else {

                        echo '<li>';
                        echo $resd . '  ' . $this->currency_symbol . '0.00';
                        echo '</li>';
                    }
                }
            } else {
                if (isset($single_price_details['ship_details']['rate'])) {
                    /* Run in case of single shipment only */
                    echo '<li>' . $single_price_details['ship_details']['title'] . ' : ' . $this->en_format_price($single_price_details['ship_details']['rate']) . '</li>';
                }
            }

            /* Show accessorials */
            $this->en_show_accessorials($service_order_data, $shipment_status);
            echo "</ul>";

            echo "<br />";
            echo '<h4 style="    text-decoration: underline;margin: 4px 0px 4px 0px;">Shipment ' . $ship_count . " > items </h4>";
            echo '<ul id="product-details-order" class="en-list" style="list-style: disc;    list-style-position: inside;">';
            foreach ($service_order_data->items as $value) {
                /* Check for variations */
                $product_name = wc_get_product($value->productId);
                echo '<li>' . $value->productQty . ' x ' . $product_name->get_name() . '</li>';
            }
            echo '</ul>';
            echo "<br /><br />";
        }

        /**
         * Price format.
         * @param int/double/string $dollars
         * @return string
         */
        function en_format_price($dollars)
        {
            return $this->currency_symbol . number_format(sprintf('%0.2f',
                    preg_replace("/[^0-9.]/", "", $dollars)), 2);
        }

        /**
         * Show accessorial.
         */
        public function en_show_accessorials($service_order_data, $shipment_status)
        {

            /* Show accessorials code here */
            /* Hazardous check */
            if (isset($service_order_data->hazardousMaterial) &&
                $service_order_data->hazardousMaterial == "yes"
            ) {
                echo '<li>Hazardous Material</li>';
            }
            $residential_del = get_option('ups_small_quote_as_residential_delivery');
            /* Residential feature */
            if (
                (isset($residential_del) &&
                    $residential_del == 'yes') || (isset($service_order_data->accessorials->R) &&
                    $service_order_data->accessorials->R == 'R')
            ) {

                echo '<li>Residential Delivery</li>';
            }
        }

        /**
         * Check accessorial.
         * @param array $service_order_data
         * @param string $shipment_status
         */
        public function en_check_accessorials($service_order_data, $shipment_status)
        {
            /* In case of singleshipment */
            if ($shipment_status == 'single') {
                $service_order_data = reset($service_order_data);
            }

            if (isset($service_order_data->handling_fee) && $service_order_data->handling_fee == 1) {
                $this->handling_fee = 1;
            }
        }

        /**
         * Items details.
         * @param array $order_details
         * @param string $shipment_status
         */
        function en_order_items_details($order_details, $shipment_status)
        {
            foreach ($order_details->items as $items) {
                echo $items->productQty . ' x ' . $items->productName;
            }
        }
        //  Front Order Hook Function

        /**
         * Save order details in options table.
         * @param int $order_id
         */
        function en_woocommerce_save_order_detail_puro_small($order_id)
        {

            $this->ship_status = 0;
            $order = new WC_Order($order_id);
            $order_key = $order->get_order_key();
            /* Check if data is already saved */
            global $wpdb;
            $result = 0;
            $sql = "SELECT order_id FROM `" . $wpdb->prefix . "enit_order_details` WHERE `order_id` = '" . $order_key . "'";
            $result = $wpdb->query($sql);
            /* This order details are already updated please return false */
            if ($result != 0) {
                return false;
            }

            $order_item_name = '';
            $order_item_type = '';
            $shipping_method_title = '';
            $shipping_method_id = '';
            $shipping_method_total = '';
            $shipping_method_total_tax = '';
            $shipping_method_taxes = '';
            $app_id = 'no_id';
            $details = '';
            $shipment_type = 'single';

            /* Get order details */
            $order_details = WC()->session->get('en_order_detail');
            $order_details = $order_details['en_shipping_details'];

            /* Get shipping details */
            $shipping_details = $order->get_items('shipping');
            /* Update shipping details */
            foreach ($shipping_details as $item_id => $shipping_item_obj) {
                $order_item_name = $shipping_item_obj->get_name();
                $order_item_type = $shipping_item_obj->get_type();
                $shipping_method_title = $shipping_item_obj->get_method_title();
                $shipping_method_id = $shipping_item_obj->get_method_id();
                $shipping_method_total = $shipping_item_obj->get_total();
                $shipping_method_total_tax = $shipping_item_obj->get_total_tax();
                $shipping_method_taxes = $shipping_item_obj->get_taxes();
            }
            $multiple_methods = array();

            /* Set status to 1 if service is from current plugin */
            $this->en_find_selected_shipping_status(
                $shipping_method_id, $order_details['en_puro_small']['services']
            );

            /* If service is from current plugin */
            if ($this->ship_status == 1) {
                if (count($order_details['en_puro_small']['details']) > 1) {
                    $access = array();  /* Get accessorials */
                    if (isset($order_details['en_puro_small']['accessorials'])) {
                        $access = $order_details['en_puro_small']['accessorials'];
                    }

                    /* No need of accessorials any more */
                    unset($order_details['en_puro_small']['accessorials']);

                    $shipping_method_id = $this->multi_ship_id;

                    /* Update the cheapeast services in each shipments */
                    foreach ($order_details['en_puro_small']['services'][$shipping_method_id]['minPrices'] as $zipcode => $value) {
                        $order_details['en_puro_small']['details'][$zipcode]['cheapest_services'] = $order_details['en_puro_small']['services'][$shipping_method_id]['minPrices'][$zipcode];
                        if (!empty($access)) {
                            $order_details['en_puro_small']['details'][$zipcode]['accessorials'] = $access;
                        }
                    }
                }


                /* Loop order details */
                foreach ($order_details as $app_id => $value) { /* Number of apps */

                    if ($app_id == 'en_puro_small') {
                        if (count($value['details']) == 1) { /* If single shipment */

                            $this->en_update_order_details($shipping_method_id,
                                $order_key, $app_id, $value['details'],
                                $shipment_type);
                        } elseif (count($value['details']) > 1) { /* If multi-shipment */
                            $shipment_type = 'multiple';

                            foreach ($value['details'] as $zip => $zip_details) {
                                /* set cheapest array here in multishipment */
                                $keys = $zip_details['cheapest_services'];

                                $cheapest_service = $keys['serivceID'];
                                $multiple_methods[] = $cheapest_service . '_' . $zip_details['origin']['zip'];
                                $this->en_update_order_details($cheapest_service . '_' . $zip_details['origin']['zip'],
                                    $order_key, 'en_puro_small', $zip_details,
                                    $shipment_type);
                            }
                            $multiple_methods_implode = implode(", ",
                                $multiple_methods);
                            $this->en_update_order_details('multi_purolator_small',
                                $order_key, 'en_puro_small',
                                $multiple_methods_implode, $shipment_type);
                        }
                    }
                }
            }
        }

        /**
         * Find the selected shipping in order details.
         */
        function en_find_selected_shipping_status($shipping_method_id, $services)
        {
            $services = isset($services) && is_array($services) && !empty($services) ? $services : [];
            if (!function_exists('array_column')) {
                $service_ids = $this->helper_obj->array_column($services, 'id');
            } else {
                $service_ids = array_column($services, 'id');
            }
            if (in_array($shipping_method_id, $service_ids) || $shipping_method_id == 'purolator_small') {
                $this->ship_status = 1;
            }
        }

        /**
         * Update the order details data.
         * @param string $shipping_method_id
         * @param string $order_key
         * @param string $app_id
         * @param array $order_details
         * @param string $shipment_type
         */
        function en_update_order_details($shipping_method_id, $order_key, $app_id, $order_details, $shipment_type)
        {
            global $wpdb;
            $result = 0;
            $order_details = json_encode($order_details);

            if ($shipment_type == 'single') {
                $sql = "SELECT order_id FROM `" . $wpdb->prefix . "enit_order_details` WHERE `order_id` = '" . $order_key . "' AND `plugin_name` = 'en_puro_small'";
                $result = $wpdb->query($sql);
            }

            if ($shipment_type == 'single') {
                $shipping_method_id = $shipping_method_id . '_single';
            }

            if ($result == 0) {
                $sql = $wpdb->prepare("INSERT INTO `" . $wpdb->prefix . "enit_order_details` (`service_id`, `app_id`, `order_id`, `data`, `plugin_name`) values (%s, %s, %s, %s, %s) ",
                    $shipping_method_id, $app_id, $order_key, $order_details,
                    'en_puro_small');
                $wpdb->query($sql);
            }
        }
    }

    /* Initialize class object */
    new En_Puro_Small_Order_Widget_Details();
}
    