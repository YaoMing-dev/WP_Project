<?php
$consumer_key = 'ck_kickzone_local_1234567890abcdef1234567890abcdef';
$consumer_secret = 'cs_kickzone_local_1234567890abcdef1234567890abcdef';
$user = get_user_by('login', 'admin_kickzone');
if (!$user) {
    echo "admin_kickzone not found\n";
    return;
}
global $wpdb;
$table = $wpdb->prefix . 'woocommerce_api_keys';
$existing = $wpdb->get_var($wpdb->prepare("SELECT key_id FROM {$table} WHERE description = %s", 'KickZone Local API'));
if ($existing) {
    $wpdb->update($table, [
        'user_id' => $user->ID,
        'permissions' => 'read',
        'consumer_key' => wc_api_hash($consumer_key),
        'consumer_secret' => $consumer_secret,
        'truncated_key' => substr($consumer_key, -7),
    ], ['key_id' => $existing]);
} else {
    $wpdb->insert($table, [
        'user_id' => $user->ID,
        'description' => 'KickZone Local API',
        'permissions' => 'read',
        'consumer_key' => wc_api_hash($consumer_key),
        'consumer_secret' => $consumer_secret,
        'nonces' => '',
        'truncated_key' => substr($consumer_key, -7),
    ]);
}
echo "consumer_key={$consumer_key}\n";
echo "consumer_secret={$consumer_secret}\n";
