<?php
// Apply the official Blossom Chic template with Vietnamese user-facing content.

function bc_demo_image($relative_path, $title, $alt) {
    $source = get_stylesheet_directory() . '/' . ltrim($relative_path, '/');
    if (!file_exists($source)) {
        return 0;
    }

    $existing = get_posts([
        'post_type' => 'attachment',
        'post_status' => 'inherit',
        'title' => $title,
        'numberposts' => 1,
        'fields' => 'ids',
    ]);
    if ($existing) {
        return (int) $existing[0];
    }

    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $tmp = wp_tempnam(basename($source));
    copy($source, $tmp);
    $file = [
        'name' => basename($source),
        'tmp_name' => $tmp,
        'type' => wp_check_filetype(basename($source))['type'],
        'error' => 0,
        'size' => filesize($tmp),
    ];

    $attachment_id = media_handle_sideload($file, 0, $title);
    if (is_wp_error($attachment_id)) {
        @unlink($tmp);
        return 0;
    }
    update_post_meta($attachment_id, '_wp_attachment_image_alt', $alt);
    return (int) $attachment_id;
}

function bc_upsert_page($slug, $title, $content, $seo_title, $seo_desc, $image_id = 0) {
    $page = get_page_by_path($slug, OBJECT, 'page');
    $payload = [
        'post_title' => $title,
        'post_name' => $slug,
        'post_content' => $content,
        'post_status' => 'publish',
        'post_type' => 'page',
    ];
    $id = $page ? wp_update_post($payload + ['ID' => $page->ID]) : wp_insert_post($payload);
    if ($image_id) {
        set_post_thumbnail($id, $image_id);
    }
    update_post_meta($id, '_yoast_wpseo_title', $seo_title);
    update_post_meta($id, '_yoast_wpseo_metadesc', $seo_desc);
    return $id;
}

function bc_upsert_post($slug, $title, $category, $tags, $content, $excerpt, $image_id) {
    $existing = get_page_by_path($slug, OBJECT, 'post');
    $cat_id = wp_create_category($category);
    $payload = [
        'post_title' => $title,
        'post_name' => $slug,
        'post_content' => $content,
        'post_excerpt' => $excerpt,
        'post_status' => 'publish',
        'post_type' => 'post',
        'post_category' => [$cat_id],
    ];
    $id = $existing ? wp_update_post($payload + ['ID' => $existing->ID]) : wp_insert_post($payload);
    wp_set_post_tags($id, $tags);
    if ($image_id) {
        set_post_thumbnail($id, $image_id);
    }
    update_post_meta($id, '_yoast_wpseo_metadesc', $excerpt);
    return $id;
}

update_option('blogname', 'Blossom Chic Việt');
update_option('blogdescription', 'Blog phong cách sống, làm đẹp và cảm hứng mỗi ngày');
update_option('WPLANG', 'vi');
update_option('woocommerce_coming_soon', 'no');

if (!username_exists('admin_blossom')) {
    $admin_id = wp_create_user('admin_blossom', 'Admin@Blossom2024', 'admin@blossom.local');
    if (!is_wp_error($admin_id)) {
        (new WP_User($admin_id))->set_role('administrator');
    }
}

// Remove old project/demo content from the visible site.
foreach (get_posts(['post_type' => 'product', 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids']) as $product_id) {
    wp_delete_post($product_id, true);
}
foreach (get_posts(['post_type' => 'post', 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids']) as $post_id) {
    wp_delete_post($post_id, true);
}
foreach (['trang-chu', 'gioi-thieu', 'shop', 'lien-he', 'gio-hang', 'thanh-toan', 'tai-khoan'] as $slug) {
    $page = get_page_by_path($slug, OBJECT, 'page');
    if ($page && in_array($slug, ['shop', 'gio-hang', 'thanh-toan', 'tai-khoan'], true)) {
        wp_delete_post($page->ID, true);
    }
}

// Clear old custom CSS so Blossom Chic renders as the official theme.
foreach (get_posts(['post_type' => 'custom_css', 'post_status' => 'any', 'numberposts' => -1]) as $post) {
    wp_delete_post($post->ID, true);
}

$img_slider_one = bc_demo_image('images/slider/one.jpg', 'Blossom Chic Slider 1', 'Ảnh minh họa phong cách Blossom Chic');
$img_slider_two = bc_demo_image('images/slider/two.jpg', 'Blossom Chic Slider 2', 'Ảnh minh họa bài viết phong cách sống');
$img_home_one = bc_demo_image('images/home/one-right.jpg', 'Blossom Chic Home 1', 'Ảnh trang chủ Blossom Chic');
$img_home_two = bc_demo_image('images/home/two-right.jpg', 'Blossom Chic Home 2', 'Ảnh chuyên mục Blossom Chic');
$img_header = bc_demo_image('images/header/header-two.png', 'Blossom Chic Header', 'Ảnh header Blossom Chic');

$home_id = bc_upsert_page(
    'trang-chu',
    'Trang chủ',
    '<h1>Blossom Chic Việt</h1><p>Không gian blog nhẹ nhàng dành cho phong cách sống, làm đẹp, cảm hứng cá nhân và những câu chuyện thường ngày.</p><p>Giao diện sử dụng template Blossom Chic chính thức, giữ phong cách thanh lịch của theme và Việt hóa các phần hiển thị trên UI.</p><p><a class="button" href="/blog">Đọc bài mới</a></p>',
    'Blossom Chic Việt - Blog Phong Cách Sống',
    'Blog Blossom Chic tiếng Việt về phong cách sống, làm đẹp, cảm hứng cá nhân và những câu chuyện thường ngày.',
    $img_home_one
);

$about_id = bc_upsert_page(
    'gioi-thieu',
    'Giới thiệu',
    '<h1>Giới thiệu</h1><p>Blossom Chic Việt là bản demo WordPress dùng template Blossom Chic chính thức. Website tập trung vào giao diện blog/lifestyle gọn gàng, dễ đọc và phù hợp trình bày bài viết tiếng Việt.</p><p>Các phần chữ hiển thị trên giao diện người dùng như menu, tìm kiếm, bình luận, bài viết, danh mục và nút điều hướng đã được Việt hóa để người xem dễ sử dụng.</p>',
    'Giới Thiệu | Blossom Chic Việt',
    'Giới thiệu website demo dùng template Blossom Chic chính thức và nội dung tiếng Việt rõ ràng.',
    $img_home_two
);

$contact_id = bc_upsert_page(
    'lien-he',
    'Liên hệ',
    '<h1>Liên hệ</h1><p>Bạn có thể gửi góp ý hoặc yêu cầu liên hệ qua biểu mẫu bên dưới.</p>[contact-form-7 title="Blossom Chic Contact Form"]<p><small>Thông tin form chỉ dùng để phản hồi liên hệ, không chia sẻ cho bên thứ ba nếu chưa có sự đồng ý.</small></p>',
    'Liên Hệ | Blossom Chic Việt',
    'Gửi liên hệ hoặc góp ý cho website Blossom Chic Việt.',
    $img_header
);

$blog_id = bc_upsert_page(
    'blog',
    'Blog',
    '<h1>Blog</h1><p>Danh sách bài viết mới nhất về phong cách sống, làm đẹp, du lịch và cảm hứng cá nhân.</p>',
    'Blog | Blossom Chic Việt',
    'Đọc các bài viết tiếng Việt mới nhất trên Blossom Chic Việt.',
    $img_slider_two
);

$posts = [
    [
        'slug' => '5-thoi-quen-buoi-sang-giup-ngay-moi-nhe-nhang',
        'title' => '5 thói quen buổi sáng giúp ngày mới nhẹ nhàng hơn',
        'category' => 'Phong cách sống',
        'tags' => 'thói quen, sống chậm, cảm hứng',
        'image' => $img_slider_one,
        'excerpt' => 'Một buổi sáng gọn gàng bắt đầu từ vài thói quen nhỏ: dậy sớm hơn một chút, uống nước, ghi nhanh việc cần làm và dành thời gian cho bản thân.',
    ],
    [
        'slug' => 'cach-sap-xep-goc-lam-viec-nho-ma-van-dep',
        'title' => 'Cách sắp xếp góc làm việc nhỏ mà vẫn đẹp',
        'category' => 'Trang trí',
        'tags' => 'góc làm việc, trang trí, tối giản',
        'image' => $img_home_one,
        'excerpt' => 'Không cần quá nhiều đồ, một góc làm việc đẹp nên có ánh sáng tốt, mặt bàn thoáng, vài vật dụng cần thiết và một điểm nhấn cá nhân.',
    ],
    [
        'slug' => 'goi-y-cham-soc-ban-than-sau-mot-ngay-ban-ron',
        'title' => 'Gợi ý chăm sóc bản thân sau một ngày bận rộn',
        'category' => 'Làm đẹp',
        'tags' => 'chăm sóc bản thân, làm đẹp, thư giãn',
        'image' => $img_home_two,
        'excerpt' => 'Sau một ngày dài, hãy dành vài phút để làm sạch, dưỡng ẩm, nghe nhạc nhẹ và tạm rời xa màn hình trước khi nghỉ ngơi.',
    ],
    [
        'slug' => 'nhung-mon-do-nho-giup-chuyen-di-cuoi-tuan-de-chiu-hon',
        'title' => 'Những món đồ nhỏ giúp chuyến đi cuối tuần dễ chịu hơn',
        'category' => 'Du lịch',
        'tags' => 'du lịch, cuối tuần, chuẩn bị',
        'image' => $img_slider_two,
        'excerpt' => 'Một chiếc túi gọn, sổ tay nhỏ, kem chống nắng, tai nghe và chai nước cá nhân có thể làm chuyến đi ngắn trở nên thoải mái hơn.',
    ],
    [
        'slug' => 'phoi-mau-trang-phuc-don-gian-cho-ngay-di-choi',
        'title' => 'Phối màu trang phục đơn giản cho ngày đi chơi',
        'category' => 'Thời trang',
        'tags' => 'thời trang, phối đồ, màu sắc',
        'image' => $img_header,
        'excerpt' => 'Các tông trắng, be, đen, xanh nhạt và hồng đất dễ phối với nhau, giúp tổng thể nhẹ nhàng nhưng vẫn có điểm nhấn.',
    ],
];

foreach ($posts as $post) {
    bc_upsert_post(
        $post['slug'],
        $post['title'],
        $post['category'],
        $post['tags'],
        '<p>' . esc_html($post['excerpt']) . '</p><h2>Gợi ý thực hiện</h2><p>Hãy bắt đầu từ một thay đổi nhỏ, phù hợp với lịch sinh hoạt và sở thích cá nhân. Nội dung trên website được viết bằng tiếng Việt rõ ràng để người đọc dễ theo dõi trên cả máy tính và điện thoại.</p>',
        $post['excerpt'],
        $post['image']
    );
}

if (class_exists('WPCF7_ContactForm')) {
    $forms = WPCF7_ContactForm::find(['title' => 'Blossom Chic Contact Form']);
    $form = $forms ? $forms[0] : WPCF7_ContactForm::get_template();
    $form->set_title('Blossom Chic Contact Form');
    $form->set_properties([
        'form' => '[text* your-name placeholder "Họ tên"]' . "\n" . '[email* your-email placeholder "Email"]' . "\n" . '[textarea* your-message placeholder "Nội dung liên hệ"]' . "\n" . '[submit "Gửi liên hệ"]',
        'mail' => [
            'active' => true,
            'subject' => 'Blossom Chic contact form',
            'sender' => '[_site_title] <wordpress@localhost>',
            'recipient' => 'hello@example.com',
            'body' => "From: [your-name] [your-email]\n\n[your-message]",
            'additional_headers' => 'Reply-To: [your-email]',
            'attachments' => '',
            'use_html' => false,
            'exclude_blank' => false,
        ],
    ]);
    $form->save();
}

update_option('show_on_front', 'page');
update_option('page_on_front', $home_id);
update_option('page_for_posts', $blog_id);

$menu_obj = wp_get_nav_menu_object('Menu chính');
$menu_id = $menu_obj ? $menu_obj->term_id : wp_create_nav_menu('Menu chính');
foreach (wp_get_nav_menu_items($menu_id) ?: [] as $item) {
    wp_delete_post($item->ID, true);
}
foreach ([$home_id, $about_id, $blog_id, $contact_id] as $page_id) {
    wp_update_nav_menu_item($menu_id, 0, [
        'menu-item-title' => get_the_title($page_id),
        'menu-item-object' => 'page',
        'menu-item-object-id' => $page_id,
        'menu-item-type' => 'post_type',
        'menu-item-status' => 'publish',
    ]);
}

$locations = get_theme_mod('nav_menu_locations') ?: [];
$locations['primary'] = $menu_id;
$locations['secondary'] = $menu_id;
$locations['menu-1'] = $menu_id;
set_theme_mod('nav_menu_locations', $locations);

set_theme_mod('custom_header', $img_header);
set_theme_mod('header_image', get_stylesheet_directory_uri() . '/images/header/header-two.png');
set_theme_mod('header_layout', 'two');
set_theme_mod('blog_layout_option', 'home-two');
set_theme_mod('slider_layout', 'two');

flush_rewrite_rules();
echo "Official Blossom Chic Vietnamese demo applied\n";
