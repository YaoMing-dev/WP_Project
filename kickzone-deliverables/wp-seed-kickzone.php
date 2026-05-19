<?php
// Seed KickZone demo content for local WordPress.

function kz_upsert_page($title, $slug, $content, $meta_title, $meta_desc) {
    $existing = get_page_by_path($slug, OBJECT, 'page');
    $post = [
        'post_title' => $title,
        'post_name' => $slug,
        'post_content' => $content,
        'post_status' => 'publish',
        'post_type' => 'page',
    ];
    if ($existing) {
        $post['ID'] = $existing->ID;
        $id = wp_update_post($post);
    } else {
        $id = wp_insert_post($post);
    }
    update_post_meta($id, '_yoast_wpseo_title', $meta_title);
    update_post_meta($id, '_yoast_wpseo_metadesc', $meta_desc);
    return $id;
}

function kz_upsert_post($title, $category, $tags, $content) {
    $existing = get_page_by_title($title, OBJECT, 'post');
    $cat_id = wp_create_category($category);
    $post = [
        'post_title' => $title,
        'post_content' => $content,
        'post_status' => 'publish',
        'post_type' => 'post',
        'post_category' => [$cat_id],
    ];
    if ($existing) {
        $post['ID'] = $existing->ID;
        $id = wp_update_post($post);
    } else {
        $id = wp_insert_post($post);
    }
    wp_set_post_tags($id, $tags);
    return $id;
}

function kz_create_product($row) {
    if (!class_exists('WC_Product_Simple')) {
        return;
    }
    $sku = $row['SKU'];
    $existing_id = wc_get_product_id_by_sku($sku);
    $product = $existing_id ? wc_get_product($existing_id) : new WC_Product_Simple();
    $product->set_name($row['Name']);
    $product->set_sku($sku);
    $product->set_status('publish');
    $product->set_catalog_visibility('visible');
    $product->set_featured($row['Is featured?'] === '1');
    $product->set_short_description($row['Short description']);
    $product->set_description($row['Description']);
    $product->set_regular_price($row['Regular price']);
    $product->set_sale_price($row['Sale price']);
    $product->set_manage_stock(true);
    $product->set_stock_quantity((int) $row['Stock']);
    $product->set_stock_status('instock');
    $id = $product->save();

    $categories = array_filter(array_map('trim', explode(',', $row['Categories'])));
    $cat_ids = [];
    foreach ($categories as $cat) {
        $term = term_exists($cat, 'product_cat');
        if (!$term) {
            $term = wp_insert_term($cat, 'product_cat');
        }
        if (!is_wp_error($term)) {
            $cat_ids[] = (int) $term['term_id'];
        }
    }
    if ($cat_ids) {
        wp_set_object_terms($id, $cat_ids, 'product_cat');
    }

    $tags = array_filter(array_map('trim', explode(',', $row['Tags'])));
    if ($tags) {
        wp_set_object_terms($id, $tags, 'product_tag');
    }

    $attributes = [];
    foreach ([1, 2] as $idx) {
        $name = $row["Attribute {$idx} name"] ?? '';
        $values = $row["Attribute {$idx} value(s)"] ?? '';
        if ($name && $values) {
            $attr = new WC_Product_Attribute();
            $attr->set_name($name);
            $attr->set_options(array_map('trim', explode('|', $values)));
            $attr->set_visible(true);
            $attr->set_variation(false);
            $attributes[] = $attr;
        }
    }
    $product = wc_get_product($id);
    $product->set_attributes($attributes);
    $product->save();

    update_post_meta($id, '_yoast_wpseo_metadesc', $row['Short description']);
}

update_option('blogname', 'KickZone');
update_option('blogdescription', 'Sneaker chinh hang, gia tot');
update_option('timezone_string', 'Asia/Ho_Chi_Minh');
update_option('permalink_structure', '/%postname%/');
update_option('woocommerce_currency', 'VND');
update_option('woocommerce_default_country', 'VN:SG');
update_option('woocommerce_store_address', '123 Nguyen Trai');
update_option('woocommerce_store_city', 'TP.HCM');
update_option('woocommerce_store_postcode', '700000');

$home = kz_upsert_page(
    'Trang chu',
    'trang-chu',
    '<h1>STEP INTO THE FUTURE</h1><p>KickZone la cua hang sneaker chinh hang voi cac dong Sneaker, Running, Lifestyle, Classic va Skate.</p><h2>Danh muc noi bat</h2><p>Sneaker, Running, Classic.</p><h2>Featured Products</h2><p>[featured_products limit="4" columns="4"]</p><h2>Sale 20%</h2><p>Giam gia toan bo giay Running trong tuan khai truong.</p>',
    'KickZone - Sneaker Chinh Hang Gia Tot',
    'Mua giay the thao chinh hang Nike, Adidas, New Balance tai KickZone. Giao hang nhanh, doi tra de dang.'
);
$about = kz_upsert_page(
    'Gioi thieu',
    'gioi-thieu',
    '<h1>Ve KickZone</h1><p>KickZone duoc xay dung voi muc tieu tro thanh dia chi mua sneaker dang tin cay cho sinh vien, dan van phong va nguoi yeu streetwear.</p><h2>Tai sao chon chung toi</h2><ul><li>Hang chinh hang</li><li>Doi tra 7 ngay</li><li>Tu van nhanh</li></ul>',
    'Ve Chung Toi | KickZone Sneaker Store',
    'KickZone la cua hang sneaker chuyen biet voi su menh mang den giay the thao chinh hang, de chon va de mua.'
);
$shop = kz_upsert_page('Shop', 'shop', '<h1>Shop giay the thao</h1><p>Danh muc san pham WooCommerce cua KickZone.</p>', 'Shop Giay The Thao | KickZone', 'Kham pha bo suu tap sneaker da dang voi gia tu 1.8 trieu. Hang chinh hang, nhieu size, tu van nhanh.');
$blog = kz_upsert_page('Blog', 'blog', '<h1>Blog sneaker</h1><p>Cac bai viet ve xu huong, review va huong dan cham soc giay.</p>', 'Blog Sneaker, Review Va Huong Dan | KickZone', 'Doc bai viet ve xu huong sneaker, cach ve sinh giay, review Nike, Adidas va meo bao quan giay.');
$contact = kz_upsert_page('Lien he', 'lien-he', '<h1>Lien he KickZone</h1><p>Dia chi: 123 Nguyen Trai, Quan 1, TP.HCM</p><p>Dien thoai: 0909 123 456</p><p>Email: hello@kickzone.vn</p><p>[contact-form-7 title="KickZone Contact Form"]</p><p>Du lieu form chi dung de phan hoi yeu cau tu van.</p>', 'Lien He KickZone | Tu Van Sneaker Chinh Hang', 'Lien he KickZone de duoc tu van chon size, chinh sach doi tra va thong tin san pham sneaker chinh hang.');

update_option('show_on_front', 'page');
update_option('page_on_front', $home);
update_option('page_for_posts', $blog);

$posts = [
    ['Top 5 Sneaker Nam Xu Huong 2024', 'Xu huong', 'sneaker,nam,streetwear'],
    ['Cach Ve Sinh Giay Trang Tai Nha Dung Cach', 'Huong dan', 've sinh,giay trang'],
    ['Nike vs Adidas: Thuong Hieu Nao Tot Hon?', 'Review', 'nike,adidas'],
    ['Giay Running vs Giay Lifestyle: Khac Nhau The Nao?', 'Kien thuc', 'running,lifestyle'],
    ['Bo Suu Tap Sneaker Mua He 2024 Dang Mong Cho', 'Xu huong', 'mua he,sneaker'],
    ['Cach Bao Quan Sneaker Ben Dep Lau Dai', 'Huong dan', 'bao quan,sneaker care'],
    ['Review Nike Air Force 1: Doi Giay Kinh Dien Moi Thoi Dai', 'Review', 'air force 1,nike'],
    ['Xu Huong Mau Giay Hot Nhat Nam 2024', 'Xu huong', 'mau sac,phoi do'],
];
foreach ($posts as $post) {
    kz_upsert_post($post[0], $post[1], $post[2], '<p>' . $post[0] . ' la bai viet mau cho blog KickZone. Noi dung tap trung vao sneaker, cach chon giay, bao quan va review san pham. Khi nop bai that, nhom co the dung file blog-posts.md de thay bang ban day du tren 300 chu cho moi bai.</p>');
}

$csv = '/work/kickzone-deliverables/woocommerce-products.csv';
if (file_exists($csv) && class_exists('WooCommerce')) {
    $handle = fopen($csv, 'r');
    $headers = fgetcsv($handle);
    while (($data = fgetcsv($handle)) !== false) {
        $row = array_combine($headers, $data);
        kz_create_product($row);
    }
    fclose($handle);
}

$users = [
    ['editor_kickzone', 'Editor@KickZone2024', 'editor', 'editor@kickzone.local'],
    ['customer_test', 'Customer@2024', 'customer', 'customer@kickzone.local'],
];
foreach ($users as $u) {
    if (!username_exists($u[0])) {
        $uid = wp_create_user($u[0], $u[1], $u[3]);
        if (!is_wp_error($uid)) {
            (new WP_User($uid))->set_role($u[2]);
        }
    }
}

$menu_id = wp_get_nav_menu_object('KickZone Main Menu');
if (!$menu_id) {
    $menu_id = wp_create_nav_menu('KickZone Main Menu');
} else {
    $menu_id = $menu_id->term_id;
}
$menu_pages = [$home, $about, $shop, $blog, $contact];
foreach ($menu_pages as $page_id) {
    $title = get_the_title($page_id);
    $exists = false;
    foreach (wp_get_nav_menu_items($menu_id) ?: [] as $item) {
        if ((int) $item->object_id === (int) $page_id) {
            $exists = true;
            break;
        }
    }
    if (!$exists) {
        wp_update_nav_menu_item($menu_id, 0, [
            'menu-item-title' => $title,
            'menu-item-object' => 'page',
            'menu-item-object-id' => $page_id,
            'menu-item-type' => 'post_type',
            'menu-item-status' => 'publish',
        ]);
    }
}
$locations = get_theme_mod('nav_menu_locations');
$locations['primary'] = $menu_id;
set_theme_mod('nav_menu_locations', $locations);

if (class_exists('WPCF7_ContactForm')) {
    $forms = WPCF7_ContactForm::find(['title' => 'KickZone Contact Form']);
    if (!$forms) {
        $form = WPCF7_ContactForm::get_template();
        $form->set_title('KickZone Contact Form');
        $form->set_properties([
            'form' => '[text* your-name placeholder "Ho ten"]' . "\n" . '[tel* your-phone placeholder "So dien thoai"]' . "\n" . '[email* your-email placeholder "Email"]' . "\n" . '[textarea* your-message placeholder "Noi dung"]' . "\n" . '[submit "Gui lien he"]',
            'mail' => [
                'active' => true,
                'subject' => 'KickZone contact form',
                'sender' => '[_site_title] <wordpress@localhost>',
                'recipient' => 'hello@kickzone.vn',
                'body' => 'From: [your-name] [your-email]' . "\n" . 'Phone: [your-phone]' . "\n\n" . '[your-message]',
                'additional_headers' => 'Reply-To: [your-email]',
                'attachments' => '',
                'use_html' => false,
                'exclude_blank' => false,
            ],
        ]);
        $form->save();
    }
}

flush_rewrite_rules();
echo "KickZone seed completed\n";
