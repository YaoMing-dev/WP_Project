<?php
function bc_demo_image($relative_path, $title, $alt) {
    $source = get_stylesheet_directory() . '/' . ltrim($relative_path, '/');
    if (!file_exists($source)) return 0;

    $existing = get_posts([
        'post_type' => 'attachment',
        'post_status' => 'inherit',
        'title' => $title,
        'numberposts' => 1,
        'fields' => 'ids',
    ]);
    if ($existing) return (int) $existing[0];

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
    $id = media_handle_sideload($file, 0, $title);
    if (is_wp_error($id)) {
        @unlink($tmp);
        return 0;
    }
    update_post_meta($id, '_wp_attachment_image_alt', $alt);
    return (int) $id;
}

function bc_page($slug, $title, $content, $image_id = 0) {
    $page = get_page_by_path($slug, OBJECT, 'page');
    $payload = [
        'post_title' => $title,
        'post_name' => $slug,
        'post_content' => $content,
        'post_status' => 'publish',
        'post_type' => 'page',
    ];
    $id = $page ? wp_update_post($payload + ['ID' => $page->ID]) : wp_insert_post($payload);
    if ($image_id) set_post_thumbnail($id, $image_id);
    return $id;
}

function bc_post($slug, $title, $category, $tags, $excerpt, $image_id) {
    $existing = get_page_by_path($slug, OBJECT, 'post');
    $cat_id = wp_create_category($category);
    $content = '<p>' . esc_html($excerpt) . '</p><h2>Gợi ý</h2><p>Nội dung được viết bằng tiếng Việt rõ ràng, phù hợp giao diện blog Blossom Chic và hiển thị tốt trên máy tính lẫn điện thoại.</p>';
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
    if ($image_id) set_post_thumbnail($id, $image_id);
    return $id;
}

update_option('blogname', 'Blossom Chic Việt');
update_option('blogdescription', 'Blog phong cách sống, làm đẹp và cảm hứng mỗi ngày');

foreach (get_posts(['post_type' => 'post', 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids']) as $id) {
    wp_delete_post($id, true);
}

$img1 = bc_demo_image('images/slider/one.jpg', 'Blossom Chic Slider 1', 'Ảnh minh họa Blossom Chic');
$img2 = bc_demo_image('images/slider/two.jpg', 'Blossom Chic Slider 2', 'Ảnh minh họa blog');
$img3 = bc_demo_image('images/home/one-right.jpg', 'Blossom Chic Home 1', 'Ảnh trang chủ Blossom Chic');
$img4 = bc_demo_image('images/home/two-right.jpg', 'Blossom Chic Home 2', 'Ảnh chuyên mục Blossom Chic');
$img5 = bc_demo_image('images/header/header-two.png', 'Blossom Chic Header', 'Ảnh header Blossom Chic');

$home = bc_page('trang-chu', 'Trang chủ', '<h1>Blossom Chic Việt</h1><p>Không gian blog nhẹ nhàng dành cho phong cách sống, làm đẹp, cảm hứng cá nhân và những câu chuyện thường ngày.</p><p>Website dùng template Blossom Chic chính thức và Việt hóa các phần hiển thị trên UI.</p><p><a class="button" href="/blog">Đọc bài mới</a></p>', $img3);
$about = bc_page('gioi-thieu', 'Giới thiệu', '<h1>Giới thiệu</h1><p>Blossom Chic Việt là bản demo WordPress dùng template Blossom Chic chính thức. Website tập trung vào giao diện blog/lifestyle gọn gàng, dễ đọc và phù hợp trình bày bài viết tiếng Việt.</p>', $img4);
$blog = bc_page('blog', 'Blog', '<h1>Blog</h1><p>Danh sách bài viết mới nhất về phong cách sống, làm đẹp, du lịch và cảm hứng cá nhân.</p>', $img2);
$contact = bc_page('lien-he', 'Liên hệ', '<h1>Liên hệ</h1><p>Bạn có thể gửi góp ý hoặc yêu cầu liên hệ qua biểu mẫu bên dưới.</p>[contact-form-7 title="Blossom Chic Contact Form"]<p><small>Thông tin form chỉ dùng để phản hồi liên hệ.</small></p>', $img5);

$posts = [
    ['5-thoi-quen-buoi-sang', '5 thói quen buổi sáng giúp ngày mới nhẹ nhàng hơn', 'Phong cách sống', 'thói quen, sống chậm', 'Một buổi sáng gọn gàng bắt đầu từ vài thói quen nhỏ: dậy sớm hơn một chút, uống nước, ghi nhanh việc cần làm và dành thời gian cho bản thân.', $img1],
    ['sap-xep-goc-lam-viec', 'Cách sắp xếp góc làm việc nhỏ mà vẫn đẹp', 'Trang trí', 'góc làm việc, tối giản', 'Không cần quá nhiều đồ, một góc làm việc đẹp nên có ánh sáng tốt, mặt bàn thoáng, vài vật dụng cần thiết và một điểm nhấn cá nhân.', $img3],
    ['cham-soc-ban-than', 'Gợi ý chăm sóc bản thân sau một ngày bận rộn', 'Làm đẹp', 'chăm sóc bản thân, thư giãn', 'Sau một ngày dài, hãy dành vài phút để làm sạch, dưỡng ẩm, nghe nhạc nhẹ và tạm rời xa màn hình trước khi nghỉ ngơi.', $img4],
    ['chuyen-di-cuoi-tuan', 'Những món đồ nhỏ giúp chuyến đi cuối tuần dễ chịu hơn', 'Du lịch', 'du lịch, cuối tuần', 'Một chiếc túi gọn, sổ tay nhỏ, kem chống nắng, tai nghe và chai nước cá nhân có thể làm chuyến đi ngắn trở nên thoải mái hơn.', $img2],
    ['phoi-mau-trang-phuc', 'Phối màu trang phục đơn giản cho ngày đi chơi', 'Thời trang', 'thời trang, phối đồ', 'Các tông trắng, be, đen, xanh nhạt và hồng đất dễ phối với nhau, giúp tổng thể nhẹ nhàng nhưng vẫn có điểm nhấn.', $img5],
];
foreach ($posts as $p) bc_post($p[0], $p[1], $p[2], $p[3], $p[4], $p[5]);

if (class_exists('WPCF7_ContactForm')) {
    $forms = WPCF7_ContactForm::find(['title' => 'Blossom Chic Contact Form']);
    $form = $forms ? $forms[0] : WPCF7_ContactForm::get_template();
    $form->set_title('Blossom Chic Contact Form');
    $form->set_properties([
        'form' => '[text* your-name placeholder "Họ tên"]' . "\n" . '[email* your-email placeholder "Email"]' . "\n" . '[textarea* your-message placeholder "Nội dung liên hệ"]' . "\n" . '[submit "Gửi liên hệ"]',
    ]);
    $form->save();
}

update_option('show_on_front', 'page');
update_option('page_on_front', $home);
update_option('page_for_posts', $blog);

$menu = wp_get_nav_menu_object('Menu chính');
$menu_id = $menu ? $menu->term_id : wp_create_nav_menu('Menu chính');
foreach (wp_get_nav_menu_items($menu_id) ?: [] as $item) wp_delete_post($item->ID, true);
foreach ([$home, $about, $blog, $contact] as $page_id) {
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
set_theme_mod('nav_menu_locations', $locations);

set_theme_mod('header_layout', 'two');
set_theme_mod('blog_layout_option', 'home-two');
set_theme_mod('slider_layout', 'two');
set_theme_mod('header_image', get_stylesheet_directory_uri() . '/images/header/header-two.png');

flush_rewrite_rules();
echo "Official Blossom Chic Vietnamese demo applied\n";
