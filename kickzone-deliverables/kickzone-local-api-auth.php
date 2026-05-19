<?php
/**
 * Plugin Name: KickZone Local API Auth
 * Description: Allows the fixed KickZone local WooCommerce API key on localhost Docker.
 */

add_filter('determine_current_user', function ($user_id) {
    if ($user_id) {
        return $user_id;
    }
    $key = $_GET['consumer_key'] ?? '';
    $secret = $_GET['consumer_secret'] ?? '';
    if (
        $key === 'ck_kickzone_local_1234567890abcdef1234567890abcdef' &&
        $secret === 'cs_kickzone_local_1234567890abcdef1234567890abcdef' &&
        isset($_SERVER['REQUEST_URI']) &&
        strpos($_SERVER['REQUEST_URI'], '/wp-json/wc/v3/') !== false
    ) {
        $user = get_user_by('login', 'admin_kickzone');
        return $user ? $user->ID : $user_id;
    }
    return $user_id;
}, 20);

add_filter('rest_authentication_errors', function ($result) {
    if (!empty($result)) {
        return $result;
    }
    $key = $_GET['consumer_key'] ?? '';
    $secret = $_GET['consumer_secret'] ?? '';
    if (
        $key === 'ck_kickzone_local_1234567890abcdef1234567890abcdef' &&
        $secret === 'cs_kickzone_local_1234567890abcdef1234567890abcdef' &&
        isset($_SERVER['REQUEST_URI']) &&
        strpos($_SERVER['REQUEST_URI'], '/wp-json/wc/v3/') !== false
    ) {
        return true;
    }
    return $result;
});
