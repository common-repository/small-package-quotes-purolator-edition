<?php
/*
  Plugin Name: Small Package Quotes - Purolator Edition
  Plugin URI: https://eniture.com/products/
  Description: Dynamically retrieves your negotiated shipping rates from Purolator and displays the results in the WooCommerce shopping cart.
  Version: 3.6.4
  Author: Eniture Technology
  Author URI: https://eniture.com/
  Text Domain: eniture-technology
  License: GPL version 2 or later - http://www.eniture.com/
 * WC requires at least: 6.4
 * WC tested up to: 9.3.1
 */
/**
 * Woo Check Update
 * @package     Woocommerce Purolator Small
 * @author      <https://eniture.com/>
 * @copyright   Copyright (c) 2017, Eniture
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

define('PUROLATOR_DOMAIN_HITTING_URL', 'https://ws041.eniture.com');
define('PUROLATOR_FDO_HITTING_URL', 'https://freightdesk.online/api/updatedWoocomData');
define('PUROLATOR_MAIN_FILE', __FILE__);

add_action('before_woocommerce_init', function () {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
    }
});

if (!function_exists('en_woo_plans_notification_PD')) {

    function en_woo_plans_notification_PD($product_detail_options)
    {
        $eniture_plugins_id = 'eniture_plugin_';

        for ($e = 1; $e <= 25; $e++) {
            $settings = get_option($eniture_plugins_id . $e);
            if (isset($settings) && (!empty($settings)) && (is_array($settings))) {
                $plugin_detail = current($settings);
                $plugin_name = (isset($plugin_detail['plugin_name'])) ? $plugin_detail['plugin_name'] : "";

                foreach ($plugin_detail as $key => $value) {
                    if ($key != 'plugin_name') {
                        $action = $value === 1 ? 'enable_plugins' : 'disable_plugins';
                        $product_detail_options[$key][$action] = (isset($product_detail_options[$key][$action]) && strlen($product_detail_options[$key][$action]) > 0) ? ", $plugin_name" : "$plugin_name";
                    }
                }
            }
        }

        return $product_detail_options;
    }

    add_filter('en_woo_plans_notification_action', 'en_woo_plans_notification_PD', 10, 1);
}

if (!function_exists('en_woo_plans_notification_message')) {

    function en_woo_plans_notification_message($enable_plugins, $disable_plugins)
    {
        $enable_plugins = (strlen($enable_plugins) > 0) ? "$enable_plugins: <b> Enabled</b>. " : "";
        $disable_plugins = (strlen($disable_plugins) > 0) ? " $disable_plugins: Upgrade to <b>Standard Plan to enable</b>." : "";
        return $enable_plugins . "<br>" . $disable_plugins;
    }

    add_filter('en_woo_plans_notification_message_action', 'en_woo_plans_notification_message', 10, 2);
}

/**
 * Load scripts for Unishippers Small json tree view
 */
if (!function_exists('en_purolator_small_jtv_script')) {
    function en_purolator_small_jtv_script()
    {
        wp_register_style('purolator_small_json_tree_view_style', plugin_dir_url(__FILE__) . 'logs/en-json-tree-view/en-jtv-style.css');
        wp_register_script('purolator_small_json_tree_view_script', plugin_dir_url(__FILE__) . 'logs/en-json-tree-view/en-jtv-script.js', ['jquery'], '1.0.0');

        wp_enqueue_style('purolator_small_json_tree_view_style');
        wp_enqueue_script('purolator_small_json_tree_view_script', [
            'en_tree_view_url' => plugins_url(),
        ]);
    }

    add_action('admin_init', 'en_purolator_small_jtv_script');
}

if (!function_exists('is_plugin_active')) {
    require_once(ABSPATH . 'wp-admin/includes/plugin.php');
}

/**
 * Check woocommerce installlation
 */
if (!is_plugin_active('woocommerce/woocommerce.php')) {
    add_action('admin_notices', 'purolator_small_wc_avaibility_err');
}

/**
 * WC availability error
 */
function purolator_small_wc_avaibility_err()
{
    $class = "error";
    $message = "Small Package Quotes - Purolator Edition is enabled but not effective. It requires WooCommerce to work, please <a target='_blank' href='https://wordpress.org/plugins/woocommerce/installation/'>Install</a> WooCommerce Plugin.";
    echo "<div class=\"$class\"> <p>$message</p></div>";
}

add_action('admin_enqueue_scripts', 'en_purolator_small_script');

/**
 * Load Front-end scripts for purolator_small
 */
function en_purolator_small_script()
{
    wp_enqueue_script('jquery');
    wp_enqueue_script('en_purolator_small_script', plugin_dir_url(__FILE__) . 'js/en-purolator-small.js', array(), '1.0.4');
    wp_localize_script('en_purolator_small_script', 'en_purolator_small_admin_script', array(
        'plugins_url' => plugins_url(),
        'allow_proceed_checkout_eniture' => trim(get_option("allow_proceed_checkout_eniture")),
        'prevent_proceed_checkout_eniture' => trim(get_option("prevent_proceed_checkout_eniture")),
        'purolator_small_order_cutoff_time' => get_option("purolator_small_orderCutoffTime"),
        'purolator_small_packaging_type' => get_option("purolator_small_packaging_type")
    ));
}

/**
 * Include Plugin Files
 */
require_once('purolator_small_version_compact.php');
require_once('fdo/en-fdo.php');
require_once('order-details/en-order-widget.php');
require_once('helper/en_helper_class.php');
require_once('db/purolator-small-db.php');
require_once('purolator-small-admin-filter.php');
require_once('purolator-small-shipping-class.php');
require_once('template/connection-settings.php');
require_once('template/quote-settings.php');

require_once 'product/en-product-detail.php';

require_once 'template/csv-export.php';

require_once('warehouse-dropship/wild-delivery.php');
require_once('warehouse-dropship/get-distance-request.php');
require_once('standard-package-addon/standard-package-addon.php');
require_once 'update-plan.php';

require_once('purolator-small-test-connection.php');
require_once('purolator-small-carrier-service.php');
require_once('purolator-small-group-package.php');
require_once('purolator-small-wc-update-change.php');
require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
require_once('purolator-small-curl-class.php');
require_once('order-details/en-order-export.php');


add_action('admin_init', 'purolator_small_check_woo_version');

/**
 * Check woocommerce version compatibility
 */
function purolator_small_check_woo_version()
{
    $wcPluginVersion = new purolator_Get_Shipping_Quotes();
    $woo_version = $wcPluginVersion->purolator_small_wc_version_number();
    $version = '2.6';
    if (!version_compare($woo_version["woocommerce_plugin_version"], $version, ">=")) {
        add_action('admin_notices', 'purolator_small_wc_version_failure');
    }
}

/**
 * WC Version Failure
 */
function purolator_small_wc_version_failure()
{
    ?>
    <div class="notice notice-error">
        <p>
            <?php
            _e('Small Package Quotes - Purolator Edition plugin requires WooCommerce version 2.6 or higher to work. Functionality may not work properly.', 'wwe-woo-version-failure');
            ?>
        </p>
    </div>
    <?php
}

if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins'))) || is_plugin_active_for_network('woocommerce/woocommerce.php')) {

    /**
     * Load scripts for purolator Small
     */
    add_action('admin_enqueue_scripts', 'purolator_small_admin_script');

    /**
     * Admin Script
     */
    function purolator_small_admin_script()
    {
        wp_register_style('purolator_small_style', plugin_dir_url(__FILE__) . '/css/purolator-small-style.css', false, '1.0.5');
        wp_enqueue_style('purolator_small_style');
        wp_register_style('purolator_small_wickedpicker_style', 'https://cdn.jsdelivr.net/npm/wickedpicker@0.4.3/dist/wickedpicker.min.css', false, '1.0.0');
        wp_enqueue_style('purolator_small_style');
        wp_enqueue_style('purolator_small_wickedpicker_style');
        wp_register_script('purolator_small_wickedpicker_style', plugin_dir_url(__FILE__) . '/js/wickedpicker.js', false, '2.0.3');
        wp_enqueue_script('purolator_small_wickedpicker_style');
    }

    add_filter('plugin_action_links', 'purolator_small_add_action_plugin', 10, 5);

    /**
     * purolator Small action links
     * @staticvar $plugin
     * @param $actions
     * @param $plugin_file
     */
    function purolator_small_add_action_plugin($actions, $plugin_file)
    {
        static $plugin;
        if (!isset($plugin))
            $plugin = plugin_basename(__FILE__);
        if ($plugin == $plugin_file) {
            $settings = array('settings' => '<a href="admin.php?page=wc-settings&tab=purolator_small">' . __('Settings', 'General') . '</a>');
            $site_link = array('support' => '<a href="https://support.eniture.com/" target="_blank">Support</a>');
            $actions = array_merge($settings, $actions);
            $actions = array_merge($site_link, $actions);
        }
        return $actions;
    }

    /**
     * purolator Small Activation Hook   (purolator-small-db function are included)
     */
    register_activation_hook(__FILE__, 'create_purolator_small_wh_db');
    register_activation_hook(__FILE__, 'create_purolator_small_option');
    register_activation_hook(__FILE__, 'old_store_purolator_sm_dropship_status');
    register_activation_hook(__FILE__, 'old_store_purolator_sm_hazmat_status');
    register_activation_hook(__FILE__, 'en_purolator_small_activate_hit_to_update_plan');
    register_deactivation_hook(__FILE__, 'en_purolator_small_deactivate_hit_to_update_plan');
    register_deactivation_hook(__FILE__, 'en_purolator_small_deactivate_plugin');

    /**
     * purolator plugin update now
     * @param array type $upgrader_object
     * @param array type $options
     */
    function en_purolator_small_update_now()
    {
        $index = 'small-package-quotes-purolator-edition/small-package-quotes-purolator-edition.php';
        $plugin_info = get_plugins();
        $plugin_version = (isset($plugin_info[$index]['Version'])) ? $plugin_info[$index]['Version'] : '';
        $update_now = get_option('en_purolator_small_update_now');

        if ($update_now != $plugin_version) {
            if (!function_exists('en_purolator_small_activate_hit_to_update_plan')) {
                require_once(__DIR__ . '/update-plan.php');
            }

            en_purolator_small_activate_hit_to_update_plan();
            old_store_purolator_sm_dropship_status();
            old_store_purolator_sm_hazmat_status();
            create_purolator_small_wh_db();
            create_purolator_small_option();

            update_option('en_purolator_small_update_now', $plugin_version);
        }
    }

    add_action('init', 'en_purolator_small_update_now');
    add_action( 'upgrader_process_complete', 'en_purolator_small_update_now', 10, 2);

    /**
     * purolator Small Action And Filters   (purolator-small-admin-filter functions are used as add_filter and add_action)
     *
     * purolator-small-shipping-class main function purolator_small_init included as add action.
     */
    add_filter('woocommerce_shipping_methods', 'add_purolator_small');
    add_filter('woocommerce_get_settings_pages', 'purolator_small_shipping_sections');
    add_action('woocommerce_shipping_init', 'purolator_small_init');
    add_filter('woocommerce_package_rates', 'purolator_small_hide_shipping');
    add_filter('woocommerce_shipping_calculator_enable_city', '__return_true');
    add_filter('woocommerce_cart_no_shipping_available_html', 'purolator_small_default_error_message', 999, 1);
    add_action('init', 'purolator_small_no_method_available');
    add_action('init', 'purolator_default_error_message_selection');


}
/**
 * Update Default custom error message selection
 */

function purolator_default_error_message_selection()
{
    $custom_error_selection = get_option('wc_pervent_proceed_checkout_eniture');
    if (empty($custom_error_selection)) {
        update_option('wc_pervent_proceed_checkout_eniture', 'prevent', true);
        update_option('prevent_proceed_checkout_eniture', 'There are no shipping methods available for the address provided. Please check the address.', true);
    }
}

/**
 * @param $message
 * @return string
 */
if (!function_exists("purolator_small_default_error_message")) {

    function purolator_small_default_error_message($message)
    {

        if (get_option('wc_pervent_proceed_checkout_eniture') == 'prevent') {
            remove_action('woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20, 2);
            return __(get_option('prevent_proceed_checkout_eniture'));
        } else if (get_option('wc_pervent_proceed_checkout_eniture') == 'allow') {
            add_action('woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20, 2);
            return __(get_option('allow_proceed_checkout_eniture'));
        }
    }

}

define("en_woo_plugin_purolator_small", "purolator_small");

add_action('wp_enqueue_scripts', 'en_puro_small_frontend_checkout_script');

/**
 * Load Frontend scripts for ODFL
 */
function en_puro_small_frontend_checkout_script()
{
    wp_enqueue_script('jquery');
    wp_enqueue_script('en_puro_small_frontend_checkout_script', plugin_dir_url(__FILE__) . 'front/js/en-purolator-small-checkout.js', array(), '1.0.1');
    wp_localize_script('en_puro_small_frontend_checkout_script', 'frontend_script', array(
        'pluginsUrl' => plugins_url(),
    ));
}

if (!function_exists('getHost')) {

    function getHost($url)
    {
        $parseUrl = parse_url(trim($url));
        if (isset($parseUrl['host'])) {
            $host = $parseUrl['host'];
        } else {
            $path = explode('/', $parseUrl['path']);
            $host = $path[0];
        }
        return trim($host);
    }

}
/**
 * Get Domain Name
 */
if (!function_exists('purolator_small_get_domain')) {

    function purolator_small_get_domain()
    {
        global $wp;
        $url = home_url($wp->request);
        return getHost($url);
    }

}
/**
 * Plans Common Hooks
 */
add_filter('purolator_small_quotes_plans_suscription_and_features', 'purolator_small_quotes_plans_suscription_and_features', 1);

function purolator_small_quotes_plans_suscription_and_features($feature)
{


    $package = get_option('purolator_small_package');

    $features = array
    (
        'instore_pickup_local_devlivery' => array('3'),
        'transit_days' => array('3'),
        'cutOffTime_shipDateOffset' => array('2', '3')
    );

    if (get_option('purolator_small_quotes_store_type') == "1") {
        $features['multi_warehouse'] = array('2', '3');
        $features['multi_dropship'] = array('', '0', '1', '2', '3');
        $features['hazardous_material'] = array('2', '3');
    }

    if (get_option('en_old_user_dropship_status') == "0" && get_option('purolator_small_quotes_store_type') == "0") {
        $features['multi_dropship'] = array('', '0', '1', '2', '3');
    }
    if (get_option('en_old_user_warehouse_status') === "0" && get_option('purolator_small_quotes_store_type') == "0") {
        $features['multi_warehouse'] = array('2', '3');
    }
    if (get_option('en_old_user_hazmat_status') == "1" && get_option('purolator_small_quotes_store_type') == "0") {
        $features['hazardous_material'] = array('2', '3');
    }

    return (isset($features[$feature]) && (in_array($package, $features[$feature]))) ? TRUE : ((isset($features[$feature])) ? $features[$feature] : '');
}

add_filter('purolator_small_plans_notification_link', 'purolator_small_plans_notification_link', 1);

function purolator_small_plans_notification_link($plans)
{
    $plan = current($plans);
    $plan_to_upgrade = "";
    switch ($plan) {
        case 1:
            $plan_to_upgrade = "<a class='plan_color' href='https://eniture.com/woocommerce-purolator-small-package-plugin/' target='_blank'>Basic Plan required.</a>";
            break;
        case 2:
            $plan_to_upgrade = "<a class='plan_color' href='https://eniture.com/woocommerce-purolator-small-package-plugin/' target='_blank'>Standard Plan required.</a>";
            break;
        case 3:
            $plan_to_upgrade = "<a class='plan_color' href='https://eniture.com/woocommerce-purolator-small-package-plugin/' target='_blank'>Advanced Plan required.</a>";
            break;
    }

    return $plan_to_upgrade;
}

/**
 *
 * old customer check dropship / warehouse status on plugin update
 */
function old_store_purolator_sm_dropship_status()
{
    global $wpdb;

//  Check total no. of dropships on plugin updation
    $table_name = $wpdb->prefix . 'warehouse';
    $count_query = "select count(*) from $table_name where location = 'dropship' ";
    $num = $wpdb->get_var($count_query);

    if (get_option('en_old_user_dropship_status') == "0" && get_option('purolator_small_quotes_store_type') == "0") {

        $dropship_status = ($num > 1) ? 1 : 0;

        update_option('en_old_user_dropship_status', "$dropship_status");
    } elseif (get_option('en_old_user_dropship_status') == "" && get_option('purolator_small_quotes_store_type') == "0") {
        $dropship_status = ($num == 1) ? 0 : 1;

        update_option('en_old_user_dropship_status', "$dropship_status");
    }

//  Check total no. of warehouses on plugin updation
    $table_name = $wpdb->prefix . 'warehouse';
    $warehouse_count_query = "select count(*) from $table_name where location = 'warehouse' ";
    $warehouse_num = $wpdb->get_var($warehouse_count_query);

    if (get_option('en_old_user_warehouse_status') == "0" && get_option('purolator_small_quotes_store_type') == "0") {

        $warehouse_status = ($warehouse_num > 1) ? 1 : 0;

        update_option('en_old_user_warehouse_status', "$warehouse_status");
    } elseif (get_option('en_old_user_warehouse_status') == "" && get_option('purolator_small_quotes_store_type') == "0") {
        $warehouse_status = ($warehouse_num == 1) ? 0 : 1;

        update_option('en_old_user_warehouse_status', "$warehouse_status");
    }
}

/**
 *
 * old customer check hazmat status on plugin update
 */
function old_store_purolator_sm_hazmat_status()
{
    global $wpdb;
    $results = $wpdb->get_results("SELECT meta_key FROM {$wpdb->prefix}postmeta WHERE meta_key LIKE '_hazardousmaterials%' AND meta_value = 'yes'
            "
    );

    if (get_option('en_old_user_hazmat_status') == "0" && get_option('purolator_small_quotes_store_type') == "0") {
        $hazmat_status = (count($results) > 0) ? 0 : 1;
        update_option('en_old_user_hazmat_status', "$hazmat_status");
    } elseif (get_option('en_old_user_hazmat_status') == "" && get_option('purolator_small_quotes_store_type') == "0") {
        $hazmat_status = (count($results) == 0) ? 1 : 0;

        update_option('en_old_user_hazmat_status', "$hazmat_status");
    }
}
// fdo va
add_action('wp_ajax_nopriv_purolator_s_fd', 'purolator_s_fd_api');
add_action('wp_ajax_purolator_s_fd', 'purolator_s_fd_api');
/**
 * UPS AJAX Request
 */
function purolator_s_fd_api()
{
    $store_name = purolator_small_get_domain();
    $company_id = $_POST['company_id'];
    $data = [
        'plateform'  => 'wp',
        'store_name' => $store_name,
        'company_id' => $company_id,
        'fd_section' => 'tab=purolator_small&section=section-4',
    ];
    if (is_array($data) && count($data) > 0) {
        if($_POST['disconnect'] != 'disconnect') {
            $url =  'https://freightdesk.online/validate-company';
        }else {
            $url = 'https://freightdesk.online/disconnect-woo-connection';
        }
        $response = wp_remote_post($url, [
                'method' => 'POST',
                'timeout' => 60,
                'redirection' => 5,
                'blocking' => true,
                'body' => $data,
            ]
        );
        $response = wp_remote_retrieve_body($response);
    }
    if($_POST['disconnect'] == 'disconnect') {
        $result = json_decode($response);
        if ($result->status == 'SUCCESS') {
            update_option('en_fdo_company_id_status', 0);
        }
    }
    echo $response;
    exit();
}
add_action('rest_api_init', 'en_rest_api_init_status_purolator_s');
function en_rest_api_init_status_purolator_s()
{
    register_rest_route('fdo-company-id', '/update-status', array(
        'methods' => 'POST',
        'callback' => 'en_purolator_s_fdo_data_status',
        'permission_callback' => '__return_true'
    ));
}

/**
 * Update FDO coupon data
 * @param array $request
 * @return array|void
 */
function en_purolator_s_fdo_data_status(WP_REST_Request $request)
{
    $status_data = $request->get_body();
    $status_data_decoded = json_decode($status_data);
    if (isset($status_data_decoded->connection_status)) {
        update_option('en_fdo_company_id_status', $status_data_decoded->connection_status);
        update_option('en_fdo_company_id', $status_data_decoded->fdo_company_id);
    }
    return true;
}

if (!function_exists('en_purolator_check_ground_transit_restrict_status')) {

    function en_purolator_check_ground_transit_restrict_status($ground_transit_statuses)
    {
        $ground_transit_restrict_plan = apply_filters('purolator_small_quotes_plans_suscription_and_features', 'transit_days');
        $ground_restrict_value = (false !== get_option('restrict_days_transit_package_purolator_small')) ? get_option('restrict_days_transit_package_purolator_small') : '';
        if ('' !== $ground_restrict_value && strlen(trim($ground_restrict_value)) && !is_array($ground_transit_restrict_plan)) {
            $ground_transit_statuses['purolator'] = '1';
        }

        return $ground_transit_statuses;
    }

    add_filter('en_check_ground_transit_restrict_status', 'en_purolator_check_ground_transit_restrict_status', 9, 1);
}