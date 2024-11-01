<?php
/**
 * Test connection
 * @package     Woocommerce Purolator Small
 * @author      <https://eniture.com/>
 * @copyright   Copyright (c) 2017, Eniture
 */
if (!defined('ABSPATH')) {
    exit;
}


add_action('wp_ajax_nopriv_purolator_small_test_connection', 'purolator_test_submit');
add_action('wp_ajax_purolator_small_test_connection', 'purolator_test_submit');

/**
 * purolator Small Test connection AJAX Request
 */
function purolator_test_submit()
{

    if (isset($_POST)) {
        foreach ($_POST as $key => $post) {
            $data[$key] = sanitize_text_field($post);
        }

        $billAccNumber = $data['purolator_small_billing_acc_number'];
        $regAccNumber = $data['purolator_small_registered_acc_number'];
        $regCity = $data['purolator_small_registered_city'];
        $regState = $data['purolator_small_registered_state'];
        $regZip = $data['purolator_small_registered_zip'];
        $proKey = $data['purolator_small_pro_key'];
        $proKeyPass = $data['purolator_small_pro_key_pass'];
        $lcns = $data['purolator_small_license'];
    }
    $data = array(
        'licence_key' => $lcns,
        'sever_name' => purolator_small_get_domain(),
        'carrierName' => 'purolator',
        'plateform' => 'WordPress',
        'carrier_mode' => 'test',
        'productionKey' => $proKey,
        'productionPass' => $proKeyPass,
        'registeredAccount' => $regAccNumber,
        'billingAccount' => $billAccNumber,
        'senderCity' => $regCity,
        'senderState' => $regState,
        'senderZip' => $regZip,
        'accessLevel' => 'pro'
    );


    $purolator_small_curl_obj = new Purolator_Small_Curl_Request();
    $output = $purolator_small_curl_obj->purolator_small_get_curl_response(PUROLATOR_DOMAIN_HITTING_URL . '/index.php', $data);
    $result = json_decode($output);


    if (isset($result->severity) && $result->severity == 'ERROR' && $result->Message == 'Unauthorized') {
        $response = array('Error' => "Please verify credentials and try again.");
    } else if (isset($result->severity) && $result->severity == 'SUCCESS') {
        $response = array('Success' => $result->Message);
    } else {
        if (isset($result->error)) {
            $response = array('Error' => $result->error);
        } else {
            $response = array('Error' => "Please verify credentials and try again.");
        }

    }
    echo json_encode($response);
    exit();
}
