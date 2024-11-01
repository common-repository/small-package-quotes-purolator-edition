<?php
/**
 * Purolator Small curl response class | Curl response from api
 * @package     Woocommerce Purolator Small
 * @author      <https://eniture.com/>
 * @copyright   Copyright (c) 2017, Eniture
 */
    if ( ! defined( 'ABSPATH' ) ) {
        exit; 
    }
        
/**
 * Curl Response Class
 */ 
    class Purolator_Small_Curl_Request 
    {
        /**
         * Get Curl Response 
         * @param $url
         * @param $postData
         * @return json
         */
        
            function purolator_small_get_curl_response($url, $postData) 
            {
                if ( !empty( $url ) && !empty( $postData ) )
                {
                    $field_string = http_build_query($postData);
                    
                    $response = wp_remote_post($url,
                                array(
                                    'method' => 'POST',
                                     'timeout' => 60,
                                    'redirection' => 5,
                                    'blocking' => true,
                                    'body' => $field_string,
                                )
                            );
                    $output = wp_remote_retrieve_body($response);                    
                    
                    return $output;
                }    
            }
    }