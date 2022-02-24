<?php
/**
* Plugin Name: Fail2Ban Wordpress
* Plugin URI: https://github.com/timanema/fail2ban-service-wordpress
* Description: Adds fail2ban functionality to wordpress.
* Version: 1.0.0
* Author: Tim Anema
* Author URI: https://timanema.net
* License: MIT
*/

function get_data() {
    return json_encode(array(
        'source' => '10.42.42.42',
        'service' => 'wordpress',
        'timestamp' => time()
    ));
}

add_action( 'wp_login_failed', function( $username ) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://10.8.0.48:8080/api/entries/add/10.42.42.42");
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Content-Length: ' . strlen($data)));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    curl_exec($ch);
    $statusCode = curl_getInfo($channel, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);
    
    wp_die('code:' . $statusCode . ', err: ' . $err);
} );
