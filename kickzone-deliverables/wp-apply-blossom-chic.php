<?php
// Apply Blossom Chic theme and keep user-facing content Vietnamese.

update_option('blogname', 'KickZone');
update_option('blogdescription', 'Sneaker chính hãng, phong cách trẻ');
update_option('WPLANG', 'vi');
update_option('woocommerce_coming_soon', 'no');

// Clear old Astra-specific custom CSS so Blossom Chic renders as the official template.
$custom_css_posts = get_posts([
    'post_type' => 'custom_css',
    'post_status' => 'any',
    'numberposts' => -1,
]);
foreach ($custom_css_posts as $post) {
    wp_delete_post($post->ID, true);
}

$pages = [
    'trang-chu' => [
        'title' => 'Trang chủ',
        'content' => '<h1>KickZone Sneaker Store</h1><p>Chào mừng bạn đến với KickZone - cửa hàng sneaker dành cho giới trẻ yêu phong cách năng động, tối giản và dễ phối đồ.</p><p>Khám phá sản phẩm mới, đọc blog chăm sóc giày và liên hệ để được tư vấn size phù hợp.</p><p><a class="button" href="/shop">Mua sắm ngay</a></p>',
        'seo_title' => 'KickZone - Sneaker Chính Hãng Cho Giới Trẻ',
        'seo_desc' => 'KickZone là shop sneaker chính hãng, phong cách trẻ trung, có sản phẩm, blog hướng dẫn và form tư vấn tiếng Việt.',
    ],
    'gioi-thieu' => [
        'title' => 'Giới thiệu',
        'content' => '<h1>Về KickZone</h1><p>KickZone được xây dựng như một website WordPress bán giày thể thao cơ bản, tập trung vào trải nghiệm xem sản phẩm rõ ràng, nội dung tiếng Việt dễ hiểu và giao diện blog/shop thanh lịch.</p><h2>Cam kết</h2><ul><li>Sản phẩm có thông tin giá, SKU, stock, size và màu sắc.</li><li>Nội dung blog tự viết, không sao chép nguyên văn từ thương hiệu khác.</li><li>Form liên hệ chỉ dùng để phản hồi yêu cầu tư vấn.</li></ul>',
        'seo_title' => 'Về KickZone | Sneaker Store',
        'seo_desc' => 'Tìm hiểu về KickZone, website WordPress bán sneaker với giao diện Blossom Chic và nội dung tiếng Việt rõ ràng.',
    ],
    'lien-he' => [
        'title' => 'Liên hệ',
        'content' => '<h1>Liên hệ KickZone</h1><p>Bạn cần tư vấn size, mẫu giày hoặc chính sách đổi trả? Gửi thông tin cho KickZone.</p>[contact-form-7 title="KickZone Contact Form"]<h2>Thông tin cửa hàng</h2><p><strong>Địa chỉ:</strong> 123 Nguyễn Trãi, Quận 1, TP.HCM</p><p><strong>Điện thoại:</strong> 0909 123 456</p><p><strong>Email:</strong> hello@kickzone.vn</p><p><small>Dữ liệu form chỉ dùng để phản hồi yêu cầu tư vấn, không chia sẻ cho bên thứ ba nếu chưa có sự đồng ý.</small></p>',
        'seo_title' => 'Liên Hệ KickZone | Tư Vấn Sneaker',
        'seo_desc' => 'Liên hệ KickZone để được tư vấn chọn size, sản phẩm sneaker và chính sách đổi trả.',
    ],
];

foreach ($pages as $slug => $data) {
    $page = get_page_by_path($slug, OBJECT, 'page');
    if ($page) {
        $id = wp_update_post([
            'ID' => $page->ID,
            'post_title' => $data['title'],
            'post_content' => $data['content'],
            'post_status' => 'publish',
        ]);
    } else {
        $id = wp_insert_post([
            'post_title' => $data['title'],
            'post_name' => $slug,
            'post_content' => $data['content'],
            'post_status' => 'publish',
            'post_type' => 'page',
        ]);
    }
    update_post_meta($id, '_yoast_wpseo_title', $data['seo_title']);
    update_post_meta($id, '_yoast_wpseo_metadesc', $data['seo_desc']);
}

$home = get_page_by_path('trang-chu');
$blog = get_page_by_path('blog');
if ($home) {
    update_option('show_on_front', 'page');
    update_option('page_on_front', $home->ID);
}
if ($blog) {
    update_option('page_for_posts', $blog->ID);
}

// Menu labels in Vietnamese.
$menu_obj = wp_get_nav_menu_object('KickZone Main Menu');
$menu_id = $menu_obj ? $menu_obj->term_id : wp_create_nav_menu('KickZone Main Menu');
foreach (wp_get_nav_menu_items($menu_id) ?: [] as $item) {
    wp_delete_post($item->ID, true);
}
foreach (['trang-chu', 'gioi-thieu', 'shop', 'blog', 'lien-he'] as $slug) {
    $page = get_page_by_path($slug);
    if ($page) {
        wp_update_nav_menu_item($menu_id, 0, [
            'menu-item-title' => get_the_title($page),
            'menu-item-object' => 'page',
            'menu-item-object-id' => $page->ID,
            'menu-item-type' => 'post_type',
            'menu-item-status' => 'publish',
        ]);
    }
}

$locations = get_theme_mod('nav_menu_locations') ?: [];
$locations['primary'] = $menu_id;
$locations['secondary'] = $menu_id;
$locations['menu-1'] = $menu_id;
set_theme_mod('nav_menu_locations', $locations);

// Blossom Chic layout choices.
set_theme_mod('header_layout', 'two');
set_theme_mod('blog_layout_option', 'home-two');
set_theme_mod('slider_layout', 'two');

flush_rewrite_rules();
echo "Blossom Chic Vietnamese UI applied\n";
