<?php
/**
 * Creating warehouse database table on plugin activate
 * @package     Woocommerce Purolator Small
 * @author      <https://eniture.com/>
 * @copyright   Copyright (c) 2017, Eniture
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Create warehouse database table
 */

function create_purolator_small_wh_db($network_wide = null)
{
    if ( is_multisite() && $network_wide ) {
        foreach (get_sites(['fields'=>'ids']) as $blog_id) {
            switch_to_blog($blog_id);
            global $wpdb;

            $warehouse_table = $wpdb->prefix . "warehouse";
            if ($wpdb->query("SHOW TABLES LIKE '" . $warehouse_table . "'") === 0) {
                $origin = 'CREATE TABLE ' . $warehouse_table . '(
                            id mediumint(9) NOT NULL AUTO_INCREMENT,
                            city varchar(200) NOT NULL,
                            state varchar(200) NOT NULL,
                            zip varchar(200) NOT NULL,
                            country varchar(200) NOT NULL,
                            location varchar(200) NOT NULL,
                            nickname varchar(200) NOT NULL,
                            enable_store_pickup VARCHAR(255) NOT NULL,
                            miles_store_pickup VARCHAR(255) NOT NULL ,
                            match_postal_store_pickup VARCHAR(255) NOT NULL ,
                            checkout_desc_store_pickup VARCHAR(255) NOT NULL ,
                            enable_local_delivery VARCHAR(255) NOT NULL ,
                            miles_local_delivery VARCHAR(255) NOT NULL ,
                            match_postal_local_delivery VARCHAR(255) NOT NULL ,
                            checkout_desc_local_delivery VARCHAR(255) NOT NULL ,
                            fee_local_delivery VARCHAR(255) NOT NULL ,
                            suppress_local_delivery VARCHAR(255) NOT NULL,  
                            origin_markup VARCHAR(255),
                            PRIMARY KEY  (id) )';
                dbDelta($origin);
            }

            add_option('purolator_small_db_version', '1.0');
            $myCustomer = $wpdb->get_row("SHOW COLUMNS FROM " . $warehouse_table . " LIKE 'enable_store_pickup'");
            if (!(isset($myCustomer->Field) && $myCustomer->Field == 'enable_store_pickup')) {

                $wpdb->query(sprintf("ALTER TABLE %s ADD COLUMN enable_store_pickup VARCHAR(255) NOT NULL , "
                    . "ADD COLUMN miles_store_pickup VARCHAR(255) NOT NULL , "
                    . "ADD COLUMN match_postal_store_pickup VARCHAR(255) NOT NULL , "
                    . "ADD COLUMN checkout_desc_store_pickup VARCHAR(255) NOT NULL , "
                    . "ADD COLUMN enable_local_delivery VARCHAR(255) NOT NULL , "
                    . "ADD COLUMN miles_local_delivery VARCHAR(255) NOT NULL , "
                    . "ADD COLUMN match_postal_local_delivery VARCHAR(255) NOT NULL , "
                    . "ADD COLUMN checkout_desc_local_delivery VARCHAR(255) NOT NULL , "
                    . "ADD COLUMN fee_local_delivery VARCHAR(255) NOT NULL , "
                    . "ADD COLUMN suppress_local_delivery VARCHAR(255) NOT NULL", $warehouse_table));

            }

            $purolator_small_origin_markup = $wpdb->get_row("SHOW COLUMNS FROM " . $warehouse_table . " LIKE 'origin_markup'");
            if (!(isset($purolator_small_origin_markup->Field) && $purolator_small_origin_markup->Field == 'origin_markup')) {
                $wpdb->query(sprintf("ALTER TABLE %s ADD COLUMN origin_markup VARCHAR(255) NOT NULL", $warehouse_table));
            }

            restore_current_blog();
        }

    } else {
        global $wpdb;

        $warehouse_table = $wpdb->prefix . "warehouse";
        if ($wpdb->query("SHOW TABLES LIKE '" . $warehouse_table . "'") === 0) {
            $origin = 'CREATE TABLE ' . $warehouse_table . '(
                            id mediumint(9) NOT NULL AUTO_INCREMENT,
                            city varchar(200) NOT NULL,
                            state varchar(200) NOT NULL,
                            zip varchar(200) NOT NULL,
                            country varchar(200) NOT NULL,
                            location varchar(200) NOT NULL,
                            nickname varchar(200) NOT NULL,
                            enable_store_pickup VARCHAR(255) NOT NULL,
                            miles_store_pickup VARCHAR(255) NOT NULL ,
                            match_postal_store_pickup VARCHAR(255) NOT NULL ,
                            checkout_desc_store_pickup VARCHAR(255) NOT NULL ,
                            enable_local_delivery VARCHAR(255) NOT NULL ,
                            miles_local_delivery VARCHAR(255) NOT NULL ,
                            match_postal_local_delivery VARCHAR(255) NOT NULL ,
                            checkout_desc_local_delivery VARCHAR(255) NOT NULL ,
                            fee_local_delivery VARCHAR(255) NOT NULL ,
                            suppress_local_delivery VARCHAR(255) NOT NULL,                     
                            origin_markup VARCHAR(255),
                            PRIMARY KEY  (id) )';
            dbDelta($origin);
        }

        add_option('purolator_small_db_version', '1.0');
        $myCustomer = $wpdb->get_row("SHOW COLUMNS FROM " . $warehouse_table . " LIKE 'enable_store_pickup'");
        if (!(isset($myCustomer->Field) && $myCustomer->Field == 'enable_store_pickup')) {

            $wpdb->query(sprintf("ALTER TABLE %s ADD COLUMN enable_store_pickup VARCHAR(255) NOT NULL , "
                . "ADD COLUMN miles_store_pickup VARCHAR(255) NOT NULL , "
                . "ADD COLUMN match_postal_store_pickup VARCHAR(255) NOT NULL , "
                . "ADD COLUMN checkout_desc_store_pickup VARCHAR(255) NOT NULL , "
                . "ADD COLUMN enable_local_delivery VARCHAR(255) NOT NULL , "
                . "ADD COLUMN miles_local_delivery VARCHAR(255) NOT NULL , "
                . "ADD COLUMN match_postal_local_delivery VARCHAR(255) NOT NULL , "
                . "ADD COLUMN checkout_desc_local_delivery VARCHAR(255) NOT NULL , "
                . "ADD COLUMN fee_local_delivery VARCHAR(255) NOT NULL , "
                . "ADD COLUMN suppress_local_delivery VARCHAR(255) NOT NULL", $warehouse_table));

        }

        $purolator_small_origin_markup = $wpdb->get_row("SHOW COLUMNS FROM " . $warehouse_table . " LIKE 'origin_markup'");
        if (!(isset($purolator_small_origin_markup->Field) && $purolator_small_origin_markup->Field == 'origin_markup')) {
            $wpdb->query(sprintf("ALTER TABLE %s ADD COLUMN origin_markup VARCHAR(255) NOT NULL", $warehouse_table));
        }
    }
}

/**
 * Create plugin option
 */
function create_purolator_small_option()
{
    $eniture_plugins = get_option('EN_Plugins');
    if (!$eniture_plugins) {
        add_option('EN_Plugins', json_encode(array('purolator_small')));
    } else {
        $plugins_array = json_decode($eniture_plugins, true);
        if (!in_array('purolator_small', $plugins_array)) {
            array_push($plugins_array, 'purolator_small');
            update_option('EN_Plugins', json_encode($plugins_array));
        }
    }
}

/**
 * Remove plugin option
 */
if(!function_exists('en_purolator_small_deactivate_plugin')) {
    function en_purolator_small_deactivate_plugin()
    {
        $eniture_plugins = get_option('EN_Plugins');
        $plugins_array = json_decode($eniture_plugins, true);
        $plugins_array = !empty($plugins_array) && is_array($plugins_array) ? $plugins_array : array();
        $key = array_search('purolator_small', $plugins_array);
        if ($key !== false) {
            unset($plugins_array[$key]);
        }
        update_option('EN_Plugins', json_encode($plugins_array));
    }
}