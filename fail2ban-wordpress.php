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
function get_data($ip) {
    return json_encode(array(
        'source' => $ip,
        'service' => 'wordpress',
        'timestamp' => time()
    ));
}

function get_client_ip() {
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if(isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}

add_action('wp_login_failed', function() {
    $ip = get_client_ip();
    $data = get_data($ip);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://192.168.42.78:8080/api/entries/add/' . $ip);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Content-Length: ' . strlen($data)));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    curl_exec($ch);
    curl_close($ch);    
});

add_action('wp_authenticate', function() {
    $ip = get_client_ip();
    $json = file_get_contents('http://192.168.42.78:8080/api/blocked/'. $ip);
    $data = json_decode($json);
    
    if ($data->{'blocked'} == 'true') {
        wp_die('Your IP has been temporarily blocked due to too many failed login attempts.');
    }
});
