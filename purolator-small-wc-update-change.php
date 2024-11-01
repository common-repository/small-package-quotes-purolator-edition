<?php
/**
 * Woo Check Update
 * @package     Woocommerce Purolator Small
 * @author      <https://eniture.com/>
 * @copyright   Copyright (c) 2017, Eniture
 */
    if ( ! defined( 'ABSPATH' ) ) {
	exit; 
    }
    
/**
 * YRC Woocommerce Class for new and old functions
 */

    class purolator_Small_Woo_Update_Changes 
    {
        /** global */ public $WooVersion;
        /**
         * Constructor
         */
        function __construct() 
        {
            if (!function_exists('get_plugins'))
            require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
            $plugin_folder    = get_plugins('/' . 'woocommerce');
            $plugin_file      = 'woocommerce.php';
            $this->WooVersion = $plugin_folder[$plugin_file]['Version'];
            
        }
        /**
         * get postcode
         */
        function purolator_small_postcode()
        { 
            $sPostCode = "";
            switch ($this->WooVersion) 
            {  
                case ($this->WooVersion <= '2.7'):
                    $sPostCode = WC()->customer->get_postcode();
                    break;
                
                case ($this->WooVersion >= '3.0'):
                    $sPostCode = WC()->customer->get_billing_postcode();
                    break;

                default:
                    break;
            }
            return $sPostCode;
        }
        /**
         * get state
         */
        function purolator_small_getState()
        { 
            $sState = "";
            switch ($this->WooVersion) 
            {  
                case ($this->WooVersion <= '2.7'):
                    $sState = WC()->customer->get_state();
                    break;
                
                case ($this->WooVersion >= '3.0'):
                    $sState = WC()->customer->get_billing_state();
                    break;

                default:
                    break;
            }
            return $sState;
        }
        /**
         * get city
         */
        function purolator_small_getCity()
        { 
            $sCity = "";
            switch ($this->WooVersion) 
            {  
                case ($this->WooVersion <= '2.7'):
                    $sCity = WC()->customer->get_city();
                    break;
                
                case ($this->WooVersion >= '3.0'):
                    $sCity = WC()->customer->get_billing_city();
                    break;

                default:
                    break;
            }
            return $sCity;
        }
        /**
         * get country
         */
        function purolator_small_getCountry()
        { 
            $sCountry = "";
            switch ($this->WooVersion) 
            {  
                case ($this->WooVersion <= '2.7'):
                    $sCountry = WC()->customer->get_country();
                    break;
                
                case ($this->WooVersion >= '3.0'):
                    $sCountry = WC()->customer->get_billing_country();
                    break;

                default:
                    break;
            }
            return $sCountry;
        }
        
    }