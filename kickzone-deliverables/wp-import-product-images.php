<?php
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$images = [
    'KZ-NK-AF1' => ['https://images.unsplash.com/photo-1600185365926-3a2ce3cdb9eb?auto=format&fit=crop&w=900&q=80', 'nike-air-force-1-white.jpg', 'Nike Air Force 1 White size 42'],
    'KZ-AD-UB22' => ['https://images.unsplash.com/photo-1587563871167-1ee9c731aefb?auto=format&fit=crop&w=900&q=80', 'adidas-ultra-boost-22.jpg', 'Adidas Ultra Boost 22 running black'],
    'KZ-NB-574' => ['https://images.unsplash.com/photo-1539185441755-769473a23570?auto=format&fit=crop&w=900&q=80', 'new-balance-574-grey.jpg', 'New Balance 574 Grey lifestyle sneaker'],
    'KZ-PM-RSX' => ['https://images.unsplash.com/photo-1556048219-bb6978360b84?auto=format&fit=crop&w=900&q=80', 'puma-rsx-reinvention.jpg', 'Puma RS-X Reinvention colorful chunky sneaker'],
    'KZ-CV-CTAS' => ['https://images.unsplash.com/photo-1491553895911-0055eca6402d?auto=format&fit=crop&w=900&q=80', 'converse-chuck-taylor.jpg', 'Converse Chuck Taylor All Star canvas black'],
    'KZ-VN-OS' => ['https://images.unsplash.com/photo-1525966222134-fcfa99b8ae77?auto=format&fit=crop&w=900&q=80', 'vans-old-skool-black-white.jpg', 'Vans Old Skool Black White skate sneaker'],
    'KZ-NK-AM270' => ['https://images.unsplash.com/photo-1605408499391-6368c628ef42?auto=format&fit=crop&w=900&q=80', 'nike-air-max-270.jpg', 'Nike Air Max 270 white red cushion'],
    'KZ-AD-SS' => ['https://images.unsplash.com/photo-1608231387042-66d1773070a5?auto=format&fit=crop&w=900&q=80', 'adidas-stan-smith-white.jpg', 'Adidas Stan Smith White Green minimalist'],
];

foreach ($images as $sku => [$url, $filename, $alt]) {
    $product_id = wc_get_product_id_by_sku($sku);
    if (!$product_id || has_post_thumbnail($product_id)) {
        continue;
    }
    $tmp = download_url($url, 30);
    if (is_wp_error($tmp)) {
        echo "download failed {$sku}: " . $tmp->get_error_message() . "\n";
        continue;
    }
    $file = [
        'name' => $filename,
        'type' => 'image/jpeg',
        'tmp_name' => $tmp,
        'error' => 0,
        'size' => filesize($tmp),
    ];
    $attachment_id = media_handle_sideload($file, $product_id, $alt);
    if (is_wp_error($attachment_id)) {
        @unlink($tmp);
        echo "sideload failed {$sku}: " . $attachment_id->get_error_message() . "\n";
        continue;
    }
    set_post_thumbnail($product_id, $attachment_id);
    update_post_meta($attachment_id, '_wp_attachment_image_alt', $alt);
    echo "image set {$sku}\n";
}
