<?php
/**
 * KickZone WordPress Content Bootstrap
 * Run via: wp eval-file /work/wp-apply-kickzone.php --allow-root
 *
 * Creates: site settings, users, pages, blog posts, WooCommerce products,
 *          contact form, menus, Yoast SEO config.
 */

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

/* ==========================================================
   HELPERS
   ========================================================== */

/**
 * Download an image from URL and attach to WP media library.
 * Accepts a full image URL or a deterministic fallback seed.
 */
function kz_image(string $seed, string $filename, string $title, string $alt, int $w = 800, int $h = 600): int {
    $existing = get_posts([
        'post_type'   => 'attachment',
        'post_status' => 'inherit',
        'title'       => $title,
        'numberposts' => 1,
        'fields'      => 'ids',
    ]);
    if ($existing) return (int) $existing[0];

    $url      = preg_match('/^https?:\/\//', $seed)
        ? $seed
        : "https://picsum.photos/seed/{$seed}/{$w}/{$h}";
    $response = wp_remote_get($url, ['timeout' => 30, 'redirection' => 5]);
    if (is_wp_error($response)) {
        echo "  [WARN] Could not download image: {$seed}\n";
        return 0;
    }

    $body = wp_remote_retrieve_body($response);
    if (empty($body)) return 0;

    $tmp = wp_tempnam($filename);
    file_put_contents($tmp, $body);

    $file = [
        'name'     => $filename,
        'tmp_name' => $tmp,
        'type'     => 'image/jpeg',
        'error'    => 0,
        'size'     => filesize($tmp),
    ];
    $id = media_handle_sideload($file, 0, $title);
    if (is_wp_error($id)) {
        @unlink($tmp);
        echo "  [WARN] Sideload failed for: {$filename}\n";
        return 0;
    }
    update_post_meta($id, '_wp_attachment_image_alt', $alt);
    return (int) $id;
}

/** Upsert a page by slug */
function kz_page(string $slug, string $title, string $content, int $thumb_id = 0): int {
    $existing = get_page_by_path($slug, OBJECT, 'page');
    $data = [
        'post_title'   => $title,
        'post_name'    => $slug,
        'post_content' => $content,
        'post_status'  => 'publish',
        'post_type'    => 'page',
    ];
    $id = $existing
        ? wp_update_post($data + ['ID' => $existing->ID])
        : wp_insert_post($data);
    if ($thumb_id) set_post_thumbnail($id, $thumb_id);
    return (int) $id;
}

/** Upsert a blog post */
function kz_post(
    string $slug, string $title, string $category,
    array $tags, string $excerpt, string $content, int $thumb_id = 0
): int {
    $existing = get_page_by_path($slug, OBJECT, 'post');
    $cat_id   = wp_create_category($category);
    $data = [
        'post_title'    => $title,
        'post_name'     => $slug,
        'post_content'  => $content,
        'post_excerpt'  => $excerpt,
        'post_status'   => 'publish',
        'post_type'     => 'post',
        'post_category' => [$cat_id],
    ];
    $id = $existing
        ? wp_update_post($data + ['ID' => $existing->ID])
        : wp_insert_post($data);
    wp_set_post_tags($id, $tags);
    if ($thumb_id) set_post_thumbnail($id, $thumb_id);
    return (int) $id;
}

/** Create or update a WooCommerce simple product */
function kz_product(array $p): int {
    if (!class_exists('WC_Product_Simple')) {
        echo "  [WARN] WooCommerce not active — skipping product: {$p['name']}\n";
        return 0;
    }

    // Find existing by SKU
    $existing_id = wc_get_product_id_by_sku($p['sku']);
    $product = $existing_id ? wc_get_product($existing_id) : new WC_Product_Simple();

    $product->set_name($p['name']);
    $product->set_sku($p['sku']);
    $product->set_status('publish');
    $product->set_catalog_visibility('visible');
    $product->set_description($p['description']);
    $product->set_short_description($p['short_description']);
    $product->set_regular_price((string) $p['regular_price']);
    if (!empty($p['sale_price'])) {
        $product->set_sale_price((string) $p['sale_price']);
    }
    $product->set_manage_stock(true);
    $product->set_stock_quantity(50);
    $product->set_stock_status('instock');
    $product->set_featured($p['featured'] ?? false);

    // Categories
    $cat_ids = [];
    foreach ($p['categories'] as $cat_name) {
        $term = get_term_by('name', $cat_name, 'product_cat');
        if (!$term) {
            $result = wp_insert_term($cat_name, 'product_cat');
            $cat_ids[] = is_wp_error($result) ? 0 : (int) $result['term_id'];
        } else {
            $cat_ids[] = (int) $term->term_id;
        }
    }
    $product->set_category_ids(array_filter($cat_ids));

    // Tags
    $tag_ids = [];
    foreach ($p['tags'] as $tag_name) {
        $term = get_term_by('name', $tag_name, 'product_tag');
        if (!$term) {
            $result = wp_insert_term($tag_name, 'product_tag');
            $tag_ids[] = is_wp_error($result) ? 0 : (int) $result['term_id'];
        } else {
            $tag_ids[] = (int) $term->term_id;
        }
    }
    $product->set_tag_ids(array_filter($tag_ids));

    // Attributes: Size + Color
    $size_attr = new WC_Product_Attribute();
    $size_attr->set_name('Kích cỡ');
    $size_attr->set_options(['38', '39', '40', '41', '42', '43', '44']);
    $size_attr->set_visible(true);
    $size_attr->set_variation(false);

    $color_attr = new WC_Product_Attribute();
    $color_attr->set_name('Màu sắc');
    $color_attr->set_options($p['colors'] ?? ['Đen', 'Trắng']);
    $color_attr->set_visible(true);
    $color_attr->set_variation(false);

    $product->set_attributes([$size_attr, $color_attr]);

    $product_id = $product->save();

    // Thumbnail image
    if (!empty($p['image_id']) && $p['image_id'] > 0) {
        set_post_thumbnail($product_id, $p['image_id']);
        $product->set_image_id($p['image_id']);
        $product->save();
    }

    // Yoast SEO meta for product
    if (!empty($p['yoast_title'])) {
        update_post_meta($product_id, '_yoast_wpseo_title', $p['yoast_title']);
    }
    if (!empty($p['yoast_desc'])) {
        update_post_meta($product_id, '_yoast_wpseo_metadesc', $p['yoast_desc']);
    }
    if (!empty($p['yoast_kw'])) {
        update_post_meta($product_id, '_yoast_wpseo_focuskw', $p['yoast_kw']);
    }

    return $product_id;
}

/* ==========================================================
   1. SITE SETTINGS
   ========================================================== */
echo "→ Configuring site settings...\n";
update_option('blogname', 'KickZone');
update_option('blogdescription', 'Every Step. A Statement.');
update_option('admin_email', 'admin@kickzone.local');
update_option('timezone_string', 'Asia/Ho_Chi_Minh');
update_option('date_format', 'd/m/Y');
update_option('time_format', 'H:i');
update_option('start_of_week', 1);
update_option('posts_per_page', 10);
update_option('default_comment_status', 'open');
update_option('comment_moderation', 1);

// Register KickZone SVG logo
$logo_path = get_stylesheet_directory() . '/kickzone-logo.svg';
if (file_exists($logo_path)) {
    $existing_logo = get_posts([
        'post_type'   => 'attachment',
        'post_status' => 'inherit',
        'title'       => 'kickzone-logo',
        'numberposts' => 1,
        'fields'      => 'ids',
    ]);
    if ($existing_logo) {
        $logo_id = (int) $existing_logo[0];
    } else {
        $upload_dir = wp_upload_dir();
        $dest_file  = $upload_dir['path'] . '/kickzone-logo.svg';
        copy($logo_path, $dest_file);
        $logo_id = wp_insert_attachment([
            'post_mime_type' => 'image/svg+xml',
            'post_title'     => 'kickzone-logo',
            'post_content'   => '',
            'post_status'    => 'inherit',
            'guid'           => $upload_dir['url'] . '/kickzone-logo.svg',
        ], $dest_file);
        update_post_meta($logo_id, '_wp_attached_file',
            str_replace($upload_dir['basedir'] . '/', '', $dest_file));
    }
    set_theme_mod('custom_logo', $logo_id);
    set_theme_mod('astra-site-logo-width', 160);
    echo "  Logo registered (ID: $logo_id)\n";
}

// WooCommerce basic options
update_option('woocommerce_store_address', '123 Nguyễn Huệ');
update_option('woocommerce_store_city', 'Hồ Chí Minh');
update_option('woocommerce_default_country', 'VN');
update_option('woocommerce_store_postcode', '700000');
update_option('woocommerce_currency', 'VND');
update_option('woocommerce_currency_pos', 'right');
update_option('woocommerce_price_num_decimals', '0');
update_option('woocommerce_price_decimal_sep', ',');
update_option('woocommerce_price_thousand_sep', '.');
update_option('woocommerce_calc_taxes', 'no');
update_option('woocommerce_enable_guest_checkout', 'yes');
update_option('woocommerce_enable_checkout_login_reminder', 'yes');
update_option('woocommerce_enable_signup_and_login_from_checkout', 'yes');

/* ==========================================================
   2. USERS
   ========================================================== */
echo "→ Creating user accounts...\n";

// Editor
if (!username_exists('editor_kickzone')) {
    $editor_id = wp_create_user('editor_kickzone', 'Editor@KZ2024!', 'editor@kickzone.local');
    if (!is_wp_error($editor_id)) {
        $editor = new WP_User($editor_id);
        $editor->set_role('editor');
        wp_update_user(['ID' => $editor_id, 'display_name' => 'KickZone Editor', 'first_name' => 'Editor']);
        echo "  Created: editor_kickzone (Editor)\n";
    }
} else {
    echo "  Exists: editor_kickzone\n";
}

// Customer
if (!username_exists('customer_test')) {
    $customer_id = wp_create_user('customer_test', 'Customer@2024!', 'customer@kickzone.local');
    if (!is_wp_error($customer_id)) {
        $customer = new WP_User($customer_id);
        $customer->set_role('customer');
        wp_update_user(['ID' => $customer_id, 'display_name' => 'Test Customer', 'first_name' => 'Customer']);
        echo "  Created: customer_test (Customer)\n";
    }
} else {
    echo "  Exists: customer_test\n";
}

/* ==========================================================
   3. DOWNLOAD IMAGES
   ========================================================== */
echo "→ Downloading placeholder images...\n";

$imgs = [
    // Hero / Pages
    'hero'    => kz_image('https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=1800&q=85', 'kickzone-hero-sneaker-drop.jpg', 'KickZone Sneaker Drop', 'Giày sneaker nổi bật trên nền trẻ trung', 1600, 900),
    'about'   => kz_image('https://images.unsplash.com/photo-1515955656352-a1fa3ffcd111?auto=format&fit=crop&w=1400&q=85', 'kickzone-sneaker-wall.jpg', 'KickZone Sneaker Wall', 'Kệ trưng bày giày sneaker tại KickZone', 1200, 800),
    'contact' => kz_image('https://images.unsplash.com/photo-1460353581641-37baddab0fa2?auto=format&fit=crop&w=1400&q=85', 'kickzone-contact-sneakers.jpg', 'KickZone Contact Sneakers', 'Giày sneaker trắng cho trang liên hệ', 1200, 600),
    // Products
    'af1'     => kz_image('https://images.unsplash.com/photo-1600185365483-26d7a4cc7519?auto=format&fit=crop&w=1000&q=85', 'nike-air-force-1-white.jpg', 'KickZone AF1 White Product', 'Nike Air Force 1 màu trắng nhìn nghiêng', 800, 800),
    'ub22'    => kz_image('https://images.unsplash.com/photo-1491553895911-0055eca6402d?auto=format&fit=crop&w=1000&q=85', 'adidas-ultra-boost-22.jpg', 'KickZone Ultra Boost Product', 'Giày running màu đen cho outfit năng động', 800, 800),
    'nb574'   => kz_image('https://images.unsplash.com/photo-1608231387042-66d1773070a5?auto=format&fit=crop&w=1000&q=85', 'new-balance-574-grey.jpg', 'KickZone NB 574 Product', 'Sneaker xám phong cách retro', 800, 800),
    'rsx'     => kz_image('https://images.unsplash.com/photo-1556906781-9a412961c28c?auto=format&fit=crop&w=1000&q=85', 'puma-rs-x-reinvention.jpg', 'KickZone Chunky Sneaker Product', 'Chunky sneaker nhiều màu cho streetwear', 800, 800),
    'ct'      => kz_image('https://images.unsplash.com/photo-1525966222134-fcfa99b8ae77?auto=format&fit=crop&w=1000&q=85', 'converse-chuck-taylor.jpg', 'KickZone Canvas Sneaker Product', 'Giày canvas cổ điển màu đen', 800, 800),
    'vans'    => kz_image('https://images.unsplash.com/photo-1520256862855-398228c41684?auto=format&fit=crop&w=1000&q=85', 'vans-old-skool-bw.jpg', 'KickZone Skate Sneaker Product', 'Giày skate đen trắng', 800, 800),
    'am270'   => kz_image('https://images.unsplash.com/photo-1543508282-6319a3e2621f?auto=format&fit=crop&w=1000&q=85', 'nike-air-max-270.jpg', 'KickZone Air Max Product', 'Giày lifestyle runner hiện đại', 800, 800),
    'ss'      => kz_image('https://images.unsplash.com/photo-1549298916-b41d501d3772?auto=format&fit=crop&w=1000&q=85', 'adidas-stan-smith-white.jpg', 'KickZone Minimal White Sneaker Product', 'Sneaker trắng tối giản', 800, 800),
    // Blog
    'blog1'   => kz_image('https://images.unsplash.com/photo-1552346154-21d32810aba3?auto=format&fit=crop&w=1400&q=85', 'top-5-sneaker-2024.jpg', 'KickZone Blog Sneaker Trends', 'Bộ sưu tập sneaker xu hướng', 1200, 700),
    'blog2'   => kz_image('https://images.unsplash.com/photo-1511556820780-d912e42b4980?auto=format&fit=crop&w=1400&q=85', 've-sinh-giay-trang.jpg', 'KickZone Blog White Sneaker Care', 'Giày trắng sạch cho outfit hằng ngày', 1200, 700),
    'blog3'   => kz_image('https://images.unsplash.com/photo-1518002171953-a080ee817e1f?auto=format&fit=crop&w=1400&q=85', 'nike-vs-adidas.jpg', 'KickZone Blog Brand Comparison', 'So sánh các dòng sneaker thể thao', 1200, 700),
    'blog4'   => kz_image('https://images.unsplash.com/photo-1539185441755-769473a23570?auto=format&fit=crop&w=1400&q=85', 'giay-running-lifestyle.jpg', 'KickZone Blog Running Lifestyle', 'Giày chạy bộ trên đường phố', 1200, 700),
    'blog5'   => kz_image('https://images.unsplash.com/photo-1551107696-a4b0c5a0d9a2?auto=format&fit=crop&w=1400&q=85', 'sneaker-mua-he-2024.jpg', 'KickZone Blog Summer Sneakers', 'Sneaker sáng màu cho mùa hè', 1200, 700),
    'blog6'   => kz_image('https://images.unsplash.com/photo-1508609349937-5ec4ae374ebf?auto=format&fit=crop&w=1400&q=85', 'bao-quan-sneaker.jpg', 'KickZone Blog Sneaker Storage', 'Bảo quản giày sneaker đúng cách', 1200, 700),
    'blog7'   => kz_image('https://images.unsplash.com/photo-1600185365926-3a2ce3cdb9eb?auto=format&fit=crop&w=1400&q=85', 'review-nike-af1.jpg', 'KickZone Blog White Sneaker Review', 'Review sneaker trắng cổ điển', 1200, 700),
    'blog8'   => kz_image('https://images.unsplash.com/photo-1543508282-5c1f427f023f?auto=format&fit=crop&w=1400&q=85', 'xu-huong-mau-giay.jpg', 'KickZone Blog Sneaker Colors', 'Sneaker màu sắc cho giới trẻ', 1200, 700),
];

/* ==========================================================
   4. PAGES
   ========================================================== */
echo "→ Creating pages...\n";

// Trang chủ
$home_id = kz_page('trang-chu', 'Trang chủ', '

<!-- HERO -->
<div class="kz-hp-hero">
  <div class="kz-hp-hero__bg">
    ' . ($imgs['hero'] ? wp_get_attachment_image($imgs['hero'], 'full', false, ['class' => 'kz-hp-hero__img', 'alt' => 'KickZone Sneaker Collection 2024']) : '<div style="position:absolute;inset:0;background:#111;"></div>') . '
  </div>
  <div class="kz-hp-hero__overlay">
    <span class="kz-section-label">KickZone New Drops</span>
    <h1 class="kz-hp-hero__title">STEP<br>INTO<br>YOUR<br><em>ZONE.</em></h1>
    <p class="kz-hp-hero__sub">Sneaker chính hãng cho nhịp sống trẻ: đi học, đi làm, chạy phố, lên outfit cuối tuần.</p>
    <div class="kz-hp-hero__ctas">
      <a href="/shop" class="kz-btn-primary">Mua giày mới</a>
      <a href="/product-category/sneaker" class="kz-btn-ghost">Xem sneaker</a>
    </div>
  </div>
  <div class="kz-hp-hero__scroll">
    <div class="kz-hp-hero__scroll-line"></div>
    <span>Xem thêm</span>
  </div>
</div>

<!-- FEATURED DROP -->
<div class="kz-hp-drop">
  <div class="kz-hp-drop__image">
    ' . ($imgs['af1'] ? wp_get_attachment_image($imgs['af1'], 'large', false, ['class' => 'kz-hp-drop__img', 'alt' => 'Nike Air Force 1 White']) : '') . '
  </div>
  <div class="kz-hp-drop__info">
    <span class="kz-section-label">Drop nổi bật</span>
    <h2 class="kz-hp-drop__name">Nike Air<br>Force 1<br>White</h2>
    <p class="kz-hp-drop__price">2.800.000₫</p>
    <p class="kz-hp-drop__desc">Form giày trắng dễ phối, đủ sạch cho outfit tối giản và đủ nổi để đi cùng denim, cargo hoặc shorts. Một đôi sneaker nền tảng cho tủ giày hằng ngày.</p>
    <a href="/product/nike-air-force-1-white" class="kz-btn-primary">Mua ngay</a>
    <a href="/shop" class="kz-hp-drop__more">Xem tất cả sản phẩm</a>
  </div>
</div>

<!-- CATEGORIES -->
<div class="kz-hp-cats">
  <a href="/product-category/sneaker" class="kz-hp-cat">
    ' . ($imgs['af1'] ? wp_get_attachment_image($imgs['af1'], 'large', false, ['class' => 'kz-hp-cat__img', 'alt' => 'Sneaker']) : '') . '
    <div class="kz-hp-cat__label"><h3>SNEAKER</h3><span>Everyday rotation</span></div>
  </a>
  <a href="/product-category/running" class="kz-hp-cat">
    ' . ($imgs['ub22'] ? wp_get_attachment_image($imgs['ub22'], 'large', false, ['class' => 'kz-hp-cat__img', 'alt' => 'Running']) : '') . '
    <div class="kz-hp-cat__label"><h3>RUNNING</h3><span>Êm cho từng bước</span></div>
  </a>
  <a href="/product-category/lifestyle" class="kz-hp-cat">
    ' . ($imgs['ss'] ? wp_get_attachment_image($imgs['ss'], 'large', false, ['class' => 'kz-hp-cat__img', 'alt' => 'Lifestyle']) : '') . '
    <div class="kz-hp-cat__label"><h3>LIFESTYLE</h3><span>Phối đồ mỗi ngày</span></div>
  </a>
</div>

<!-- NEW ARRIVALS -->
<div class="kz-hp-section">
  <div class="kz-hp-section__head">
    <div>
      <span class="kz-section-label">Vừa lên kệ</span>
      <h2 class="kz-hp-section__title">New in KickZone</h2>
    </div>
    <a href="/shop" class="kz-hp-section__more">Xem tất cả</a>
  </div>
  [products limit="4" columns="4" orderby="date" order="DESC"]
</div>

<!-- SALE BANNER -->
<div class="kz-hp-sale">
  <div class="kz-hp-sale__inner">
    <span class="kz-section-label" style="color:rgba(255,255,255,0.72);">Weekend rotation</span>
    <h2>Running shoes<br>giảm đến 20%</h2>
    <p>Chọn đôi êm cho lịch chạy, tập gym và những ngày phải di chuyển nhiều.</p>
    <a href="/product-category/running" class="kz-btn-dark">Xem running</a>
  </div>
</div>

<!-- BRAND STORY -->
<div class="kz-hp-story">
  <div class="kz-hp-story__image">
    ' . ($imgs['about'] ? wp_get_attachment_image($imgs['about'], 'large', false, ['class' => 'kz-hp-story__img', 'alt' => 'KickZone Store']) : '') . '
  </div>
  <div class="kz-hp-story__text">
    <span class="kz-section-label">Về KickZone</span>
    <h2>Giày đúng gu.<br>Đi đúng chất.</h2>
    <p>KickZone chọn sneaker theo cách người trẻ thật sự mang: dễ phối, bền, thoải mái và có câu chuyện riêng.</p>
    <p>Mỗi sản phẩm đều được kiểm tra nguồn gốc, hình ảnh và size trước khi giao để bạn yên tâm mở hộp.</p>
    <a href="/gioi-thieu" class="kz-btn-outline-dark">Tìm hiểu KickZone</a>
  </div>
</div>

<!-- EDITORIAL / BLOG -->
<div class="kz-hp-section kz-hp-section--dark">
  <div class="kz-hp-section__head">
    <div>
      <span class="kz-section-label">Blog giày</span>
      <h2 class="kz-hp-section__title">Sneaker notes</h2>
    </div>
    <a href="/blog" class="kz-hp-section__more">Xem tất cả bài viết</a>
  </div>
  [kz_recent_posts limit="3" columns="3"]
</div>

<!-- TRUST STRIP -->
<div class="kz-trust-strip">
  <div class="kz-trust-item">
    <span class="kz-trust-title">Giao nhanh</span>
    <span class="kz-trust-sub">Nhận giày trong 2-4 ngày</span>
  </div>
  <div class="kz-trust-item">
    <span class="kz-trust-title">Đổi size</span>
    <span class="kz-trust-sub">Hỗ trợ đổi trong 7 ngày</span>
  </div>
  <div class="kz-trust-item">
    <span class="kz-trust-title">Chính hãng</span>
    <span class="kz-trust-sub">Kiểm tra nguồn gốc rõ ràng</span>
  </div>
  <div class="kz-trust-item">
    <span class="kz-trust-title">Tư vấn fit</span>
    <span class="kz-trust-sub">Chọn size và phối đồ</span>
  </div>
</div>

<!-- NEWSLETTER -->
<div class="kz-hp-newsletter">
  <span class="kz-section-label">Newsletter</span>
  <h2>Nhận lịch drop mới</h2>
  <p>Đăng ký để biết trước mẫu giày mới, restock size hot và ưu đãi dành cho thành viên KickZone.</p>
  [contact-form-7 id="kickzone-newsletter" title="KickZone Newsletter"]
  <p class="kz-hp-newsletter__note">Email chỉ dùng để gửi tin giày mới và ưu đãi KickZone.</p>
</div>

', $imgs['hero']);

// Set homepage to full-width layout (Astra meta)
update_post_meta($home_id, 'site-sidebar-layout', 'no-sidebar');
update_post_meta($home_id, 'site-content-layout', 'full-width');
update_post_meta($home_id, 'ast-featured-img', 'disabled');
update_post_meta($home_id, 'footer-sml-layout', 'disabled');

// Giới thiệu
$about_id = kz_page('gioi-thieu', 'Giới thiệu', '
<section class="kz-about-hero">
  <div style="padding:60px 24px;">
    <span class="kz-section-label">KickZone story</span>
    <h1 class="kz-display" style="font-size:80px;color:#0A0A0A;margin:8px 0;">Về KickZone</h1>
  </div>
</section>

<section class="kz-section kz-about-story">
  <div class="kz-container" style="max-width:900px;margin:0 auto;">
    <div>
      <span class="kz-section-label">Brand Story</span>
      <h2 style="font-family:Bebas Neue,sans-serif;font-size:48px;letter-spacing:0.05em;margin:8px 0 24px;">Sinh ra từ văn hóa sneaker</h2>
      <p>KickZone chọn giày cho người trẻ cần một đôi sneaker đẹp, dễ mang và đúng chất cá nhân. Không chạy theo quá nhiều trang trí, chúng tôi tập trung vào form giày, chất liệu, độ êm và khả năng phối đồ mỗi ngày.</p>
      <p>Từ những mẫu trắng tối giản đến running shoes đậm tính hiệu năng, mỗi sản phẩm đều được kiểm tra nguồn gốc, hình ảnh và size trước khi giao.</p>
      <p>KickZone là nơi bạn tìm được đôi giày để đi học, đi làm, đi chơi và bắt đầu một outfit tự tin hơn.</p>
    </div>
  </div>
</section>

<section class="kz-about-values">
  <div class="kz-container" style="max-width:1100px;margin:0 auto;">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:32px;margin-bottom:64px;">
      <div style="background:#FFFFFF;border:1px solid #D8D8D8;padding:40px;">
        <h3 style="font-family:Bebas Neue,sans-serif;font-size:32px;color:#0A0A0A;letter-spacing:0.05em;margin:0 0 16px;">Mission</h3>
        <p>Mang đến sneaker chính hãng, dễ chọn, dễ phối và phù hợp nhịp sống thành thị của giới trẻ.</p>
      </div>
      <div style="background:#FFFFFF;border:1px solid #D8D8D8;padding:40px;">
        <h3 style="font-family:Bebas Neue,sans-serif;font-size:32px;color:#0A0A0A;letter-spacing:0.05em;margin:0 0 16px;">Vision</h3>
        <p>Trở thành điểm đến sneaker đáng tin cậy, nơi mỗi đôi giày được chọn vì trải nghiệm mang thật.</p>
      </div>
    </div>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:24px;">
      <div style="background:#FFFFFF;border:1px solid #D8D8D8;padding:32px;text-align:center;">
        <h4 style="color:#0A0A0A;font-weight:700;margin-bottom:8px;">Hàng chính hãng 100%</h4>
        <p style="color:#111;font-size:14px;margin:0;">Cam kết hoàn tiền nếu hàng không đúng nguồn gốc.</p>
      </div>
      <div style="background:#FFFFFF;border:1px solid #D8D8D8;padding:32px;text-align:center;">
        <h4 style="color:#0A0A0A;font-weight:700;margin-bottom:8px;">Giao hàng nhanh</h4>
        <p style="color:#111;font-size:14px;margin:0;">Đóng gói kỹ, giao toàn quốc trong 2-4 ngày làm việc.</p>
      </div>
      <div style="background:#FFFFFF;border:1px solid #D8D8D8;padding:32px;text-align:center;">
        <h4 style="color:#0A0A0A;font-weight:700;margin-bottom:8px;">Hỗ trợ tận tâm</h4>
        <p style="color:#111;font-size:14px;margin:0;">Tư vấn size, fit chân và cách phối theo nhu cầu.</p>
      </div>
    </div>
  </div>
</section>

<section style="background:#FF4713;padding:60px 24px;text-align:center;">
  <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:40px;max-width:900px;margin:0 auto;">
    <div><p style="font-family:Bebas Neue,sans-serif;font-size:60px;color:#F5F4F0;margin:0;line-height:1;">500+</p><p style="color:rgba(255,255,255,0.8);font-size:13px;font-weight:600;text-transform:uppercase;letter-spacing:0.1em;margin:4px 0 0;">Sản phẩm</p></div>
    <div><p style="font-family:Bebas Neue,sans-serif;font-size:60px;color:#F5F4F0;margin:0;line-height:1;">2K+</p><p style="color:rgba(255,255,255,0.8);font-size:13px;font-weight:600;text-transform:uppercase;letter-spacing:0.1em;margin:4px 0 0;">Khách hàng</p></div>
    <div><p style="font-family:Bebas Neue,sans-serif;font-size:60px;color:#F5F4F0;margin:0;line-height:1;">4</p><p style="color:rgba(255,255,255,0.8);font-size:13px;font-weight:600;text-transform:uppercase;letter-spacing:0.1em;margin:4px 0 0;">Năm kinh nghiệm</p></div>
    <div><p style="font-family:Bebas Neue,sans-serif;font-size:60px;color:#F5F4F0;margin:0;line-height:1;">★4.9</p><p style="color:rgba(255,255,255,0.8);font-size:13px;font-weight:600;text-transform:uppercase;letter-spacing:0.1em;margin:4px 0 0;">Đánh giá TB</p></div>
  </div>
</section>
', 0);

update_post_meta($about_id, 'ast-featured-img', 'disabled');
delete_post_thumbnail($about_id);

// Liên hệ
$contact_id = kz_page('lien-he', 'Liên hệ', '
<section style="background:#1A1A1A;padding:60px 24px;text-align:center;">
  <h1 class="kz-display" style="font-size:72px;color:#F5F4F0;margin:0;">LIÊN HỆ</h1>
  <p style="color:#FFFFFF;font-size:18px;font-weight:600;margin-top:14px;">Chúng tôi phản hồi trong vòng 2 giờ — T2–T7: 9h–21h</p>
</section>

<section class="kz-section" style="background:#0A0A0A;">
  <div class="kz-container" style="max-width:1100px;margin:0 auto;display:grid;grid-template-columns:1fr 1fr;gap:80px;">
    <div>
      <h2 style="font-family:Bebas Neue,sans-serif;font-size:40px;letter-spacing:0.05em;margin:0 0 32px;">GỬI TIN NHẮN</h2>
      [contact-form-7 id="kickzone-contact" title="KickZone Contact Form"]
      <p style="color:#111;font-size:12px;margin-top:16px;">Thông tin bạn nhập chỉ dùng để phản hồi liên hệ. Không chia sẻ với bên thứ ba.</p>
    </div>
    <div>
      <h2 style="font-family:Bebas Neue,sans-serif;font-size:40px;letter-spacing:0.05em;margin:0 0 32px;">THÔNG TIN</h2>
      <div style="margin-bottom:24px;padding-bottom:24px;border-bottom:1px solid #2E2E2E;">
        <p style="color:#111;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.15em;margin:0 0 6px;">Địa chỉ</p>
        <p style="color:#F5F4F0;margin:0;">123 Nguyễn Huệ, Quận 1, TP. Hồ Chí Minh</p>
      </div>
      <div style="margin-bottom:24px;padding-bottom:24px;border-bottom:1px solid #2E2E2E;">
        <p style="color:#111;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.15em;margin:0 0 6px;">Điện thoại</p>
        <p style="color:#F5F4F0;margin:0;"><a href="tel:0901234567" style="color:#FF4713;">0901 234 567</a></p>
      </div>
      <div style="margin-bottom:24px;padding-bottom:24px;border-bottom:1px solid #2E2E2E;">
        <p style="color:#111;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.15em;margin:0 0 6px;">Email</p>
        <p style="color:#F5F4F0;margin:0;"><a href="mailto:hello@kickzone.vn" style="color:#FF4713;">hello@kickzone.vn</a></p>
      </div>
      <div>
        <p style="color:#111;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.15em;margin:0 0 6px;">Giờ mở cửa</p>
        <p style="color:#F5F4F0;margin:0;">Thứ 2 – Thứ 7: 9:00 – 21:00<br>Chủ nhật: 10:00 – 18:00</p>
      </div>
    </div>
  </div>
</section>
', $imgs['contact']);

// Chính sách bảo mật
$privacy_id = kz_page('chinh-sach-bao-mat', 'Chính sách bảo mật', '
<section style="background:#0A0A0A;padding:80px 24px;">
  <div class="kz-container" style="max-width:800px;margin:0 auto;">
    <h1 class="kz-display" style="font-size:64px;margin:0 0 40px;">CHÍNH SÁCH BẢO MẬT</h1>

    <h2>1. Thu thập thông tin</h2>
    <p style="color:#C0BFB8;">KickZone thu thập các thông tin bạn chủ động cung cấp qua form liên hệ, form đặt hàng và tài khoản. Bao gồm: họ tên, email, số điện thoại, địa chỉ giao hàng.</p>

    <h2>2. Sử dụng thông tin</h2>
    <p style="color:#C0BFB8;">Thông tin được sử dụng để: xử lý đơn hàng, liên hệ phản hồi, gửi thông báo khuyến mãi (nếu bạn đồng ý).</p>

    <h2>3. Không chia sẻ bên thứ ba</h2>
    <p style="color:#C0BFB8;">KickZone <strong>không bán, trao đổi, hoặc chia sẻ</strong> thông tin cá nhân của bạn với bên thứ ba, ngoại trừ đối tác vận chuyển cần thiết để giao hàng.</p>

    <h2>4. Bảo mật dữ liệu</h2>
    <p style="color:#C0BFB8;">Website sử dụng SSL/HTTPS. Mật khẩu được mã hóa. Chúng tôi áp dụng các biện pháp bảo mật tiêu chuẩn ngành.</p>

    <h2>5. Cookie</h2>
    <p style="color:#C0BFB8;">Website sử dụng cookie để cải thiện trải nghiệm (giỏ hàng, đăng nhập). Bạn có thể tắt cookie trong cài đặt trình duyệt.</p>

    <h2>6. Quyền của bạn</h2>
    <p style="color:#C0BFB8;">Bạn có quyền yêu cầu xem, sửa, hoặc xóa dữ liệu cá nhân. Liên hệ: <a href="mailto:hello@kickzone.vn" style="color:#FF4713;">hello@kickzone.vn</a></p>

    <p style="color:#111;font-size:13px;margin-top:48px;">Cập nhật lần cuối: ' . date('d/m/Y') . '</p>
  </div>
</section>
');

// Blog archive page
$blog_id = kz_page('blog', 'Blog', '<section class="kz-blog-intro"><h1 class="kz-display">Sneaker notes</h1><p>Tin tức, review và hướng dẫn chọn giày từ đội ngũ KickZone.</p></section>');

/* ==========================================================
   5. BLOG POSTS
   ========================================================== */
echo "→ Creating blog posts...\n";

$posts_data = [
  [
    'slug' => 'top-5-sneaker-nam-xu-huong-2024',
    'title' => 'Top 5 Sneaker Nam Xu Hướng 2024',
    'category' => 'Xu hướng',
    'tags' => ['sneaker', '2024', 'xu-huong', 'nam'],
    'excerpt' => 'Năm 2024 chứng kiến sự trở lại mạnh mẽ của các mẫu sneaker retro cùng sự xuất hiện của nhiều thiết kế đột phá. Đây là 5 đôi không thể thiếu.',
    'img' => $imgs['blog1'],
    'yoast_kw' => 'sneaker nam xu hướng 2024',
    'yoast_desc' => 'Top 5 sneaker nam xu hướng 2024 được yêu thích nhất. Nike, Adidas, New Balance — đôi nào đáng mua nhất năm nay?',
  ],
  [
    'slug' => 'cach-ve-sinh-giay-trang-tai-nha',
    'title' => 'Cách Vệ Sinh Giày Trắng Tại Nhà Đúng Cách',
    'category' => 'Hướng dẫn',
    'tags' => ['ve-sinh', 'giay-trang', 'tips', 'bao-quan'],
    'excerpt' => 'Giày trắng đẹp nhưng dễ bẩn. Với vài bước đơn giản bằng nguyên liệu có sẵn tại nhà, bạn có thể giữ đôi giày trắng như mới.',
    'img' => $imgs['blog2'],
    'yoast_kw' => 'cách vệ sinh giày trắng',
    'yoast_desc' => 'Hướng dẫn vệ sinh giày trắng tại nhà đúng cách với nguyên liệu đơn giản. Giữ sneaker trắng sáng bền màu.',
  ],
  [
    'slug' => 'nike-vs-adidas-thuong-hieu-nao-tot-hon',
    'title' => 'Nike vs Adidas: Thương Hiệu Nào Tốt Hơn?',
    'category' => 'Review',
    'tags' => ['nike', 'adidas', 'review', 'so-sanh'],
    'excerpt' => 'Hai ông lớn ngành giày thể thao thế giới — Nike và Adidas — mỗi người có điểm mạnh riêng. Bài viết phân tích khách quan để giúp bạn chọn đúng.',
    'img' => $imgs['blog3'],
    'yoast_kw' => 'nike vs adidas',
    'yoast_desc' => 'So sánh Nike và Adidas: chất lượng, thiết kế, giá cả, và độ bền. Thương hiệu nào phù hợp với bạn hơn?',
  ],
  [
    'slug' => 'giay-running-vs-giay-lifestyle-khac-nhau-the-nao',
    'title' => 'Giày Running vs Giày Lifestyle: Khác Nhau Thế Nào?',
    'category' => 'Kiến thức',
    'tags' => ['running', 'lifestyle', 'kien-thuc', 'chon-giay'],
    'excerpt' => 'Nhiều người mua nhầm giày running để đi chơi, hoặc ngược lại. Bài viết giúp bạn hiểu rõ sự khác biệt và chọn đúng loại.',
    'img' => $imgs['blog4'],
    'yoast_kw' => 'giày running vs lifestyle',
    'yoast_desc' => 'Phân biệt giày running và giày lifestyle: cấu trúc, công dụng, và cách chọn đôi phù hợp nhất.',
  ],
  [
    'slug' => 'bo-suu-tap-sneaker-mua-he-2024',
    'title' => 'Bộ Sưu Tập Sneaker Mùa Hè 2024 Đáng Mong Chờ',
    'category' => 'Xu hướng',
    'tags' => ['mua-he', 'sneaker', '2024', 'bo-suu-tap'],
    'excerpt' => 'Mùa hè 2024 mang đến hàng loạt bộ sưu tập sneaker đầy màu sắc. Từ Nike đến Puma, đây là những đôi nổi bật nhất.',
    'img' => $imgs['blog5'],
    'yoast_kw' => 'sneaker mùa hè 2024',
    'yoast_desc' => 'Khám phá bộ sưu tập sneaker mùa hè 2024: thiết kế mới nhất từ Nike, Adidas, Puma và nhiều thương hiệu nổi tiếng.',
  ],
  [
    'slug' => 'cach-bao-quan-sneaker-ben-dep-lau-dai',
    'title' => 'Cách Bảo Quản Sneaker Bền Đẹp Lâu Dài',
    'category' => 'Hướng dẫn',
    'tags' => ['bao-quan', 'sneaker', 'tips', 've-sinh'],
    'excerpt' => 'Một đôi sneaker tốt có thể đi được nhiều năm nếu được chăm sóc đúng cách. Đây là những bí quyết bảo quản từ chuyên gia.',
    'img' => $imgs['blog6'],
    'yoast_kw' => 'cách bảo quản sneaker',
    'yoast_desc' => 'Bí quyết bảo quản sneaker bền đẹp lâu dài: làm sạch đúng cách, bảo quản đúng nơi, xử lý các vết bẩn thường gặp.',
  ],
  [
    'slug' => 'review-nike-air-force-1-doi-giay-kinh-dien',
    'title' => 'Review Nike Air Force 1: Đôi Giày Kinh Điển Mọi Thời Đại',
    'category' => 'Review',
    'tags' => ['nike', 'air-force-1', 'review', 'classic'],
    'excerpt' => 'Nike Air Force 1 ra đời năm 1982 và vẫn hot đến tận ngày nay. Điều gì làm nên sức sống bất diệt của đôi giày huyền thoại này?',
    'img' => $imgs['blog7'],
    'yoast_kw' => 'review nike air force 1',
    'yoast_desc' => 'Review chi tiết Nike Air Force 1: thiết kế, chất lượng, độ thoải mái, và lý do đây là đôi giày kinh điển nhất mọi thời đại.',
  ],
  [
    'slug' => 'xu-huong-mau-giay-hot-nhat-2024',
    'title' => 'Xu Hướng Màu Giày Hot Nhất Năm 2024',
    'category' => 'Xu hướng',
    'tags' => ['mau-sac', 'xu-huong', '2024', 'phoi-do'],
    'excerpt' => '2024 là năm của sắc trắng tinh khôi, xanh cobalt táo bạo và tone đất ấm áp. Những màu giày nào đang thống trị streetwear?',
    'img' => $imgs['blog8'],
    'yoast_kw' => 'xu hướng màu giày 2024',
    'yoast_desc' => 'Xu hướng màu giày hot nhất 2024: từ trắng tinh, xanh cobalt đến tone đất — màu nào phù hợp với phong cách của bạn?',
  ],
];

$post_contents = [
  'top-5-sneaker-nam-xu-huong-2024' => '
<p>2024 là năm của sự giao thoa giữa retro cổ điển và hiệu suất công nghệ cao. Dưới đây là 5 đôi sneaker nam mà bất kỳ ai yêu sneaker cũng phải có trong tủ giày năm nay.</p>

<h2>1. Nike Air Force 1 — Biểu tượng không bao giờ lỗi mốt</h2>
<p>Ra đời năm 1982, Nike Air Force 1 là đôi sneaker bán chạy nhất lịch sử Nike. Thiết kế clean với upper da trắng tinh, đường may cẩn thận, đế Air-Sole đệm êm — đây là "tờ giấy trắng" để phối với jogger, jeans hay thậm chí âu phục. Không có gì controversial khi nói AF1 là đôi giày thiết yếu nhất 2024.</p>

<h2>2. Adidas Ultra Boost 22 — Performance đỉnh cao</h2>
<p>Công nghệ Boost trả lại 80% năng lượng mỗi bước chạy, upper Primeknit+ ôm sát như tất thứ hai. Ultra Boost 22 không chỉ là giày chạy — nó đã trở thành statement piece của athleisure culture. Màu đen all-black hay trắng sáng đều hot cả năm nay.</p>

<h2>3. New Balance 574 — Retro tối giản chinh phục mọi outfit</h2>
<p>Trong thời đại dad-shoe đang thống trị, New Balance 574 dẫn đầu nhờ sự cân bằng hoàn hảo: không quá flashy, không quá nhàm. Logo N đặc trưng, phối màu xám trung tính, đế ENCAP êm ái — đây là đôi giày của những người không cần gắng sức mà vẫn tự nhiên stylish.</p>

<h2>4. Vans Old Skool — Skate soul bất diệt</h2>
<p>Từ 1977 đến nay, Sidestripe iconic của Vans Old Skool chưa bao giờ mất đi sức hút. Canvas và suede kết hợp chắc chắn, Waffle outsole bám tốt mọi địa hình, cổ thấp linh hoạt — đây là lựa chọn anti-hype số 1 của giới sneaker am hiểu.</p>

<h2>5. Nike Air Max 270 — Streetwear elevated</h2>
<p>Túi Air 270 lớn nhất lịch sử dòng Air Max, cao 32mm ở gót, tạo silhouette độc đáo không thể nhầm lẫn. Màu đen-xám đang rất được ưa chuộng trong cộng đồng streetwear Việt Nam năm 2024. Đây là đôi giày lifestyle runner dành cho những ai muốn nổi bật mà không cần cringe.</p>

<h2>Kết luận</h2>
<p>5 đôi trên đều có mặt tại KickZone với đầy đủ size 38-46, cam kết 100% chính hãng. <a href="/shop" style="color:#FF4713;">Xem shop ngay →</a></p>
',
  'cach-ve-sinh-giay-trang-tai-nha' => '
<p>Giày trắng đẹp nhưng dễ bẩn là nỗi ám ảnh của nhiều sneakerhead. Tin tốt: bạn không cần dịch vụ vệ sinh đắt tiền — chỉ cần vài nguyên liệu đơn giản và đúng kỹ thuật là có thể giữ giày trắng như mới.</p>

<h2>Chuẩn bị dụng cụ</h2>
<p>Bạn cần: bàn chải mềm (hoặc bàn chải đánh răng cũ), khăn microfiber, nước ấm, baking soda, nước rửa chén nhẹ, và kem đánh răng trắng (không gel). Với giày da, thêm dầu dưỡng da chuyên dụng.</p>

<h2>Bước 1 — Làm sạch sơ bộ</h2>
<p>Tháo dây giày và ngâm riêng trong nước ấm pha chút baking soda 15 phút. Dùng tay hoặc bàn chải mềm chà nhẹ. Phủi bụi bẩn khô trên thân giày bằng khăn khô trước khi bắt đầu rửa ướt — đây là bước nhiều người hay bỏ qua khiến bùn đất loang ra.</p>

<h2>Bước 2 — Làm sạch phần đế</h2>
<p>Pha baking soda với nước rửa chén theo tỉ lệ 2:1 thành hỗn hợp sệt. Dùng bàn chải cứng chà mạnh vào phần đế rubber. Với vết ố vàng cứng đầu, kem đánh răng trắng chà trực tiếp rồi để 5 phút trước khi rửa sạch.</p>

<h2>Bước 3 — Làm sạch thân giày</h2>
<p>Pha loãng nước rửa chén với nước ấm. Nhúng khăn microfiber hoặc bàn chải mềm, chà theo chuyển động tròn nhẹ nhàng. Với giày canvas: có thể thấm ướt nhiều hơn. Với giày da: chỉ lau ẩm, tránh thấm nước sâu vào da. Lau lại bằng khăn sạch ướt để rửa sạch bọt.</p>

<h2>Bước 4 — Làm khô đúng cách</h2>
<p>Nhét giấy báo bên trong giày để giữ form và hút ẩm. Phơi nơi thoáng mát, tránh ánh nắng trực tiếp — UV làm vàng giày và cứng đế cao su. Không dùng máy sấy hay lò vi sóng. Thời gian khô tự nhiên: 8-12 tiếng.</p>

<h2>Mẹo bảo vệ lâu dài</h2>
<p>Sau khi giày hoàn toàn khô, xịt một lớp water repellent lên toàn bộ bề mặt để tạo lá chắn chống nước và bụi. Sản phẩm Crep Protect hoặc Jason Markk hiện có tại KickZone. Vệ sinh định kỳ 2 tuần/lần thay vì đợi đến khi quá bẩn sẽ cho kết quả tốt hơn nhiều.</p>
',
  'nike-vs-adidas-thuong-hieu-nao-tot-hon' => '
<p>Nike hay Adidas? Câu hỏi này đã chia rẽ cộng đồng sneaker suốt hơn 5 thập kỷ. Cả hai đều là những gã khổng lồ thể thao toàn cầu với di sản khổng lồ. Nhưng nếu buộc phải chọn một, bạn nên dựa trên tiêu chí gì?</p>

<h2>Công nghệ đế — Boost vs Air</h2>
<p>Đây là cuộc chiến lớn nhất. <strong>Nike Air</strong> (túi khí ép) mang lại cảm giác đệm nhẹ nhàng, responsive và đã tồn tại từ 1978. <strong>Adidas Boost</strong> (bọt TPU nén) ra đời 2013 và ngay lập tức thay đổi ngành công nghiệp với độ trả lực 80% — vượt trội Air trong các bài kiểm tra lâm sàng. Nếu chạy bộ là ưu tiên: Adidas thắng điểm này rõ ràng.</p>

<h2>Thiết kế & Văn hóa</h2>
<p>Nike đã master việc kết hợp thể thao với pop culture — hợp tác với Jordan, Travis Scott, Off-White. Mỗi drop là một sự kiện văn hóa. Adidas theo đuổi hướng minimalist-European với Stan Smith, Superstar — đồng thời đột phá với Yeezy (dù Kanye đã chia tay). Về độ hype: Nike vẫn đang dẫn trước ở thị trường Việt Nam.</p>

<h2>Độ bền & Chất liệu</h2>
<p>Nike thường dùng da tổng hợp và mesh kỹ thuật — nhẹ và đẹp nhưng đôi khi kém bền hơn. Adidas với Primeknit và các lớp gia cố overlay thường cho độ bền tốt hơn ở phân khúc tầm trung. Tuy nhiên, cả hai đều đáng tin cậy nếu bạn mua đúng hàng chính hãng.</p>

<h2>Giá cả tại thị trường Việt Nam</h2>
<p>Ở phân khúc 2-3.5 triệu, cả hai đều có nhiều lựa chọn tốt. Nike Air Force 1, Dunk Low thường hot hơn và đôi khi khó mua ở giá gốc. Adidas Stan Smith, Gazelle dễ tìm hơn và giá ổn định hơn. Nếu budget dưới 2 triệu: Adidas có nhiều lựa chọn hơn.</p>

<h2>Kết luận — Không có kẻ thua</h2>
<p>Thực tế: người yêu sneaker thực thụ không chọn một bên. Tủ giày cân bằng có cả Air Force 1 lẫn Stan Smith, cả Air Max lẫn Ultra Boost. KickZone luôn có đầy đủ cả hai thương hiệu — hãy để <a href="/lien-he" style="color:#FF4713;">đội tư vấn</a> giúp bạn chọn đúng đôi phù hợp nhất.</p>
',
  'giay-running-vs-giay-lifestyle-khac-nhau-the-nao' => '
<p>Nhìn qua, giày running và lifestyle có thể trông khá giống nhau. Nhưng mua nhầm loại không chỉ ảnh hưởng đến phong cách — nó còn có thể gây chấn thương nếu bạn dùng sai mục đích.</p>

<h2>Giày Running — Kỹ sư thiết kế để chạy</h2>
<p>Giày chạy bộ được tối ưu theo từng bước chân: <strong>Drop (chênh lệch gót-mũi)</strong> thường 8-12mm để hỗ trợ cơ chế chạy tự nhiên. <strong>Midsole</strong> làm từ foam tiên tiến (Boost, React, Gel) tập trung vào shock absorption. <strong>Outsole</strong> có pattern phức tạp để bám và chuyển lực. <strong>Upper</strong> mesh mỏng để thoát nhiệt. Giày chạy thường nặng hơn lifestyle ở phần đế nhưng toàn bộ giày thường nhẹ hơn.</p>

<h2>Giày Lifestyle — Thiết kế để mặc</h2>
<p>Lifestyle sneaker ưu tiên hình thức hơn chức năng. <strong>Drop thấp hơn</strong> (0-6mm) phù hợp đi bộ thông thường. <strong>Midsole cứng hơn</strong> để giữ form và không bị biến dạng theo thời gian. <strong>Upper đa dạng</strong>: da, canvas, suede — không nhất thiết phải thoáng khí tối đa. <strong>Thiết kế</strong> được chú trọng từng chi tiết, thường có câu chuyện lịch sử hoặc văn hóa đằng sau.</p>

<h2>Dùng sai mục đích — Hậu quả thế nào?</h2>
<p>Chạy bộ bằng giày lifestyle: thiếu support, đế không đủ đệm → đau gối, viêm cân gan chân sau 2-3 tuần chạy thường xuyên. Mặc giày chạy đi chơi hàng ngày: không sao cả, chỉ hao mòn đế nhanh hơn và trông... thiếu style.</p>

<h2>Giày đa dụng — Có tồn tại không?</h2>
<p>Có — và đó thường là dòng Trail Light hay Hybrid như Nike Air Max, Adidas Ultra Boost. Những đôi này đủ tốt để chạy nhẹ 5-10km đồng thời đủ đẹp để đi chơi. Không phải best-in-class ở cả hai, nhưng là lựa chọn thực tế cho 90% người dùng.</p>

<h2>Tóm tắt — Chọn thế nào?</h2>
<p>Chạy bộ > 3 buổi/tuần → đầu tư vào giày chạy chuyên dụng như Ultra Boost 22. Style hàng ngày → AF1, Stan Smith, Old Skool. Vừa chạy vừa đi chơi → Air Max 270 là lựa chọn hoàn hảo. Cần tư vấn thêm? <a href="/lien-he" style="color:#FF4713;">Liên hệ KickZone</a> để được hỗ trợ.</p>
',
  'bo-suu-tap-sneaker-mua-he-2024' => '
<p>Mùa hè 2024 mang đến làn sóng sneaker mới: nhẹ hơn, sáng màu hơn, và táo bạo hơn bao giờ hết. Các thương hiệu lớn đồng loạt release những thiết kế được làm riêng cho khí hậu nắng nóng và phong cách beach-to-street.</p>

<h2>Xu hướng chủ đạo mùa hè năm nay</h2>
<p><strong>Mesh upper thống trị:</strong> Thoáng khí là ưu tiên số 1 khi nhiệt độ lên tới 38-40°C. Các thương hiệu đang đầu tư mạnh vào công nghệ dệt mesh cao cấp vừa thoáng vừa giữ form tốt. <strong>Colorway sáng bold:</strong> Pastel mint, butter yellow, cobalt blue — những màu sắc mà trước đây chỉ thấy trên runway fashion nay đã xuống đường phố. <strong>Silhouette gọn nhẹ:</strong> Low-top và slip-on đang chiếm ưu thế hoàn toàn trước mid-top và high-top trong mùa hè.</p>

<h2>Nike Summer 2024 — Highlights</h2>
<p>Nike Air Max 270 trong colorway xám-cam là siêu phẩm mùa hè của Nike năm nay. Túi Air 270 tạo chiều cao vừa đủ, mesh upper thoáng khí tối đa. Bộ sưu tập Air Force 1 "Beach" với tông kem-nâu cũng đang cháy hàng toàn cầu.</p>

<h2>Adidas Summer 2024 — Highlights</h2>
<p>Stan Smith "Linen" với chất liệu linen tự nhiên và màu be trung tính đang là đôi giày lifestyle được săn đón nhất. Ultra Boost 22 "Mint" cho runner yêu màu sắc tươi sáng. Đặc biệt dòng Adilette 22 sandal-sneaker hybrid đang được giới trẻ Việt ưa chuộng dịp hè.</p>

<h2>New Balance mùa hè — Surprise of the season</h2>
<p>574 "Summer Fog" với tông pastel lilac bất ngờ được cộng đồng sneaker đón nhận nhiệt tình. New Balance đang ngày càng được tín đồ thời trang cao cấp để ý nhiều hơn — một phần nhờ sự hợp tác với các NDs (Neighbourhood Dealers) châu Âu.</p>

<h2>Mua gì cho mùa hè này?</h2>
<p>Nếu chỉ mua một đôi: Nike Air Max 270 là lựa chọn đa năng nhất — đẹp, êm, phù hợp mọi dịp. Budget chặt hơn: Vans Old Skool all-white là lựa chọn timeless không bao giờ sai. Tất cả đang có tại <a href="/shop" style="color:#FF4713;">KickZone với ưu đãi mùa hè</a>.</p>
',
  'cach-bao-quan-sneaker-ben-dep-lau-dai' => '
<p>Một đôi sneaker chất lượng như Nike Air Force 1 hay Adidas Stan Smith có thể đi tốt 3-5 năm nếu được chăm sóc đúng cách. Ngược lại, chỉ cần vài tháng không đúng cách là bạn đã phá hỏng một đôi giày đắt tiền.</p>

<h2>Bảo quản khi không đi</h2>
<p><strong>Shoe trees là người bạn tốt nhất.</strong> Nhét shoe tree bằng gỗ cedar (tuyết tùng) vào trong giày sau mỗi lần đi giúp giữ form, hút ẩm và khử mùi tự nhiên. Tránh dùng shoe tree nhựa vì không hút ẩm được. Nếu không có shoe tree, nhét giấy báo cuộn cũng tạm ổn.</p>

<h2>Nơi lưu trữ lý tưởng</h2>
<p><strong>Kẻ thù số 1 của sneaker: ánh sáng UV và độ ẩm cao.</strong> Không để giày nơi có ánh nắng trực tiếp — UV làm vàng midsole trắng chỉ sau vài tuần. Không để trong phòng tắm hay khu vực ẩm ướt. Lý tưởng nhất là hộp giày gốc với gói silica gel (hút ẩm) trong ngăn tủ mát, tối. Sneaker collector thường đầu tư vào tủ trưng bày có UV-filter và kiểm soát độ ẩm.</p>

<h2>Vệ sinh định kỳ</h2>
<p>Đừng đợi đến khi giày bẩn mới vệ sinh. Lau nhẹ bằng khăn microfiber sau mỗi lần đi để loại bụi bám mới — dễ sạch hơn nhiều so với bụi đã khô cứng lại. Định kỳ 2-4 tuần: vệ sinh toàn diện với brush và cleaning solution chuyên dụng.</p>

<h2>Rotation — Bí quyết ít ai biết</h2>
<p>Đi cùng một đôi giày mỗi ngày là cách nhanh nhất phá giày. Midsole foam cần ít nhất 24 tiếng để "hồi phục" sau khi bị nén. Nếu có thể, hãy rotate tối thiểu 3 đôi trong tuần — giày sẽ bền gấp đôi, đế sẽ êm hơn lâu hơn.</p>

<h2>Xử lý khi giày bị ướt</h2>
<p>Không phơi nắng, không sấy máy sấy tóc. Nhét giấy báo (thay mới mỗi 2 tiếng), để nơi thoáng mát. Với giày da bị ướt: sau khi khô, dưỡng da ngay bằng conditioner để tránh nứt. Thời gian khô tự nhiên: 8-24 tiếng tùy vật liệu. Kiên nhẫn là đức tính quan trọng nhất khi chăm giày.</p>
',
  'review-nike-air-force-1-doi-giay-kinh-dien' => '
<p><strong>Điểm đánh giá: 9.2/10</strong> — Ít đôi giày nào trong lịch sử sneaker có thể duy trì được sức hút qua hơn 4 thập kỷ. Nike Air Force 1 là một ngoại lệ đặc biệt.</p>

<h2>Lịch sử — Từ sân bóng rổ đến đường phố</h2>
<p>Ra đời năm 1982 do designer Bruce Kilgore, Air Force 1 là đôi giày bóng rổ đầu tiên sử dụng công nghệ Nike Air. Tên gọi lấy cảm hứng từ chuyên cơ tổng thống Mỹ Air Force One — một lựa chọn đặt tên đầy tham vọng. Bị ngừng sản xuất năm 1984 rồi được hồi sinh năm 1986 nhờ áp lực từ 3 cửa hàng giày ở Baltimore — đây là đôi giày được hồi sinh bởi chính người dùng, một câu chuyện chưa từng có trong lịch sử ngành.</p>

<h2>Thiết kế — Đơn giản đến mức hoàn hảo</h2>
<p>Điều làm AF1 bất tử chính là sự tối giản có chủ đích. Upper da một màu (thường trắng), đường may nổi bật, Swoosh ở hai bên, vòng băng mắt cá chân đặc trưng. Không có quá nhiều chi tiết — nhưng mỗi chi tiết đều đúng chỗ. Đế dày gợi cảm giác premium và tạo chiều cao vừa phải. Phiên bản Low, Mid và High phục vụ mọi sở thích.</p>

<h2>Comfort — Thực tế như thế nào?</h2>
<p>Soleful hơn nhiều so với vẻ ngoài cứng. Lót trong êm, đế Air-Sole ở gót hấp thụ shock tốt. Tuy nhiên, vì được thiết kế cho bóng rổ chứ không phải chạy bộ, AF1 không phải lựa chọn tốt cho đi bộ dài > 8km liên tục. Khi đi vừa size, cổ chân được ôm ổn định. Break-in period khoảng 1-2 tuần với da thật.</p>

<h2>Độ bền — Đáng đồng tiền</h2>
<p>Da thật trên AF1 chính hãng cực kỳ bền bỉ. Outsole rubber có thể đi 2-3 năm chạy hàng ngày trước khi cần thay. Điểm yếu duy nhất: midsole trắng dễ vàng theo thời gian nếu để nơi có UV. Dùng chất tẩy midsole định kỳ giải quyết được vấn đề này hoàn toàn.</p>

<h2>Kết luận — Có nên mua không?</h2>
<p>Câu trả lời là có, không cần do dự. Nike Air Force 1 là đôi giày duy nhất mà ngay cả người không quan tâm đến sneaker cũng nên sở hữu. Hiện tại KickZone có size 38-44, giá <strong>2,800,000₫</strong>. <a href="/product/nike-air-force-1-white" style="color:#FF4713;">Mua ngay →</a></p>
',
  'xu-huong-mau-giay-hot-nhat-2024' => '
<p>Màu sắc không chỉ là thẩm mỹ — đó là cách bạn giao tiếp với thế giới trước khi mở miệng. Năm 2024, ngành sneaker đang thấy sự dịch chuyển rõ rệt từ safe neutrals sang những tone màu có ý kiến mạnh mẽ hơn.</p>

<h2>Trắng tinh — Không bao giờ là cũ</h2>
<p>All-white vẫn là vua của tủ giày 2024. Nike Air Force 1 Triple White, Adidas Stan Smith White, Converse Chuck Taylor All White — những đôi này bán chạy quanh năm không bị ảnh hưởng bởi trend. Lý do: trắng là màu duy nhất phối được với 100% outfit mà không cần suy nghĩ. Nếu chỉ mua được một đôi giày trắng, đó phải là AF1.</p>

<h2>Cobalt Blue & Electric Blue — Màu của sự tự tin</h2>
<p>Xanh cobalt đang bùng nổ trên đường phố Việt Nam năm 2024. Từ New Balance 550 "Blue Agate" đến Nike Dunk Low "Royal Blue" — những đôi giày có statement color này đang tạo ra khoảnh khắc thị giác mạnh mẽ khi phối với outfit monochrome navy hay all-black. Không dành cho người thích an toàn.</p>

<h2>Earth Tones — Warm & Grounded</h2>
<p>Tone đất (terracotta, rust, camel, khaki) đang trở thành ngôn ngữ của "quiet luxury" trong sneaker culture 2024. New Balance 574 Sandstone, Adidas Campus "Mesa" — những màu này phối cực tốt với outfit tối giản, vải linen hay workwear aesthetic. Đây là xu hướng bền vững, không bị "expire" nhanh như neon hay pastels.</p>

<h2>Neon & Hi-Vis — Dành cho người dũng cảm</h2>
<p>Neon yellow và safety orange đang được các runner yêu thích vì lý do thực tế (visibility ban đêm) và aesthetic. Nhưng trên street? Cần rất nhiều sự tự tin và outfit phối chuẩn. Puma RS-X với colorway đa sắc là ví dụ điển hình — đôi giày tuyên ngôn cá tính mà không phải ai cũng dám mặc.</p>

<h2>Lời khuyên chọn màu giày</h2>
<p>Rule của người mặc đẹp: 2/3 tủ giày nên là neutrals (trắng, đen, xám, nâu) — chúng phối được mọi thứ. 1/3 còn lại dành cho statement colors theo sở thích cá nhân. Đừng chạy theo trend màu sắc nếu nó không phù hợp da của bạn hay wardrobe hiện tại. <a href="/shop" style="color:#FF4713;">Xem toàn bộ bộ sưu tập →</a></p>
',
];

foreach ($posts_data as $p) {
    $content = $post_contents[$p['slug']] ?? "<p>{$p['excerpt']}</p>";

    $post_id = kz_post($p['slug'], $p['title'], $p['category'], $p['tags'], $p['excerpt'], $content, $p['img']);

    // Yoast SEO for post
    update_post_meta($post_id, '_yoast_wpseo_focuskw', $p['yoast_kw']);
    update_post_meta($post_id, '_yoast_wpseo_metadesc', $p['yoast_desc']);
    update_post_meta($post_id, '_yoast_wpseo_title', $p['title'] . ' · KickZone');

    echo "  Post: {$p['title']}\n";
}

/* ==========================================================
   6. WOOCOMMERCE PRODUCTS
   ========================================================== */
echo "→ Creating WooCommerce products...\n";

$products_data = [
  [
    'name'              => 'Nike Air Force 1 White',
    'sku'               => 'NK-AF1-WHT',
    'regular_price'     => 3200000,
    'sale_price'        => 2800000,
    'featured'          => true,
    'categories'        => ['Sneaker'],
    'tags'              => ['nike', 'classic', 'white', 'af1'],
    'colors'            => ['Trắng', 'Đen'],
    'image_id'          => $imgs['af1'],
    'short_description' => 'Biểu tượng sneaker kinh điển — Nike Air Force 1 màu trắng tinh. Đế Air đệm êm, upper da thật bền bỉ.',
    'description'       => '<p>Nike Air Force 1 White là một trong những đôi giày iconic nhất lịch sử sneaker. Ra đời năm 1982, thiết kế này đã vượt qua thử thách của thời gian để trở thành biểu tượng văn hóa toàn cầu.</p><h2>Thiết kế</h2><p>Upper da thật trắng tinh, đường may cẩn thận từng chi tiết. Logo Swoosh nổi bật ở hai bên, tạo điểm nhấn cổ điển không thể nhầm lẫn. Lưỡi gà cao giúp bảo vệ mắt cá chân.</p><h2>Công nghệ đế</h2><p>Đế Air-Sole ở gót chân cung cấp độ đệm vượt trội. Đế ngoài cao su bền chắc, họa tiết tăng cường độ bám trên nhiều bề mặt.</p><h2>Phù hợp với</h2><p>Phong cách casual, streetwear, thậm chí semi-formal. Dễ phối đồ với jeans, jogger, hay thậm chí quần âu.</p>',
    'yoast_kw'          => 'nike air force 1 white',
    'yoast_title'       => 'Nike Air Force 1 White · KickZone',
    'yoast_desc'        => 'Mua Nike Air Force 1 White chính hãng tại KickZone. Size 38-44, giao hàng toàn quốc. Giá ₫2,800,000 — đổi trả 7 ngày.',
  ],
  [
    'name'              => 'Adidas Ultra Boost 22',
    'sku'               => 'AD-UB22-BLK',
    'regular_price'     => 3200000,
    'sale_price'        => null,
    'featured'          => true,
    'categories'        => ['Running'],
    'tags'              => ['adidas', 'running', 'boost', 'performance'],
    'colors'            => ['Đen', 'Trắng', 'Xám'],
    'image_id'          => $imgs['ub22'],
    'short_description' => 'Công nghệ Boost đỉnh cao cho runner nghiêm túc. Êm, bền, ôm chân — Ultra Boost 22 là người bạn đồng hành lý tưởng.',
    'description'       => '<p>Adidas Ultra Boost 22 là đỉnh cao công nghệ giày chạy bộ của Adidas. Được thiết kế dựa trên dữ liệu từ hàng triệu bước chạy, đây là đôi giày running được yêu thích nhất năm 2022.</p><h2>Công nghệ Boost</h2><p>Đế giữa Boost trả lại 80% năng lượng mỗi bước chạy, giúp bạn chạy lâu hơn mà không mệt. Độ đàn hồi không thay đổi dù trời lạnh hay nóng.</p><h2>Upper Primeknit+</h2><p>Vải dệt Primeknit+ ôm sát chân như một đôi tất, cho phép cử động tự nhiên trong khi vẫn hỗ trợ tốt. Thoáng khí xuất sắc trong thời tiết nóng.</p><h2>Phù hợp với</h2><p>Chạy bộ ngắn và dài, gym, hoặc đi bộ hàng ngày. Kiểu dáng thể thao thanh lịch cũng rất phù hợp phối đồ casual.</p>',
    'yoast_kw'          => 'adidas ultra boost 22',
    'yoast_title'       => 'Adidas Ultra Boost 22 · KickZone',
    'yoast_desc'        => 'Mua Adidas Ultra Boost 22 chính hãng tại KickZone. Công nghệ Boost đệm êm tối ưu. Size 38-44, giao hàng toàn quốc.',
  ],
  [
    'name'              => 'New Balance 574 Grey',
    'sku'               => 'NB-574-GRY',
    'regular_price'     => 2900000,
    'sale_price'        => 2500000,
    'featured'          => false,
    'categories'        => ['Lifestyle'],
    'tags'              => ['newbalance', 'lifestyle', 'casual', 'retro'],
    'colors'            => ['Xám', 'Navy', 'Đen'],
    'image_id'          => $imgs['nb574'],
    'short_description' => 'Thiết kế retro đơn giản mà tinh tế. New Balance 574 là lựa chọn hoàn hảo cho phong cách lifestyle hàng ngày.',
    'description'       => '<p>New Balance 574 là một trong những mẫu retro lifestyle bán chạy nhất của New Balance. Ra đời năm 1988, mẫu giày này vẫn giữ nguyên vẻ đẹp cổ điển và ngày càng được yêu thích hơn.</p><h2>Thiết kế cổ điển</h2><p>Phối màu xám trung tính dễ phối đồ. Upper da lộn + lưới thoáng khí, logo NB đặc trưng ở hai bên. Không quá flashy, không quá nhạt nhẽo — đúng điểm cân bằng hoàn hảo.</p><h2>Đế ENCAP</h2><p>Công nghệ đế ENCAP của New Balance kết hợp urethane cứng ở ngoài và EVA mềm ở trong, cho độ đệm tốt và ổn định cần thiết khi đi lâu.</p><h2>Phù hợp với</h2><p>Đi chơi, cafe, mua sắm, đi học. Phong cách old-school, retro, hay minimalist đều có thể khai thác tốt đôi giày này.</p>',
    'yoast_kw'          => 'new balance 574 grey',
    'yoast_title'       => 'New Balance 574 Grey · KickZone',
    'yoast_desc'        => 'New Balance 574 Grey chính hãng tại KickZone. Thiết kế retro cổ điển, đế ENCAP êm ái. Size 38-44.',
  ],
  [
    'name'              => 'Puma RS-X Reinvention',
    'sku'               => 'PM-RSX-MUL',
    'regular_price'     => 2200000,
    'sale_price'        => null,
    'featured'          => false,
    'categories'        => ['Sneaker'],
    'tags'              => ['puma', 'chunky', 'colorful', 'rsx'],
    'colors'            => ['Nhiều màu', 'Trắng/Xanh', 'Đen/Đỏ'],
    'image_id'          => $imgs['rsx'],
    'short_description' => 'Chunky sneaker táo bạo với đế dày ấn tượng. Puma RS-X là sự lựa chọn cho ai muốn nổi bật giữa đám đông.',
    'description'       => '<p>Puma RS-X Reinvention là phiên bản tái sinh của dòng RS (Running System) huyền thoại từ những năm 80. Được thiết kế lại với aesthetic chunky đặc trưng, đây là đôi giày được giới sneakerhead yêu thích.</p><h2>Thiết kế Bold</h2><p>Phối màu táo bạo với nhiều lớp vật liệu. Đế dày chunky tạo chiều cao ấn tượng. Upper mesh và da tổng hợp kết hợp hài hòa.</p><h2>Công nghệ RS</h2><p>Đế RS (Running System) với các ô khí thị giác cho cảm giác đệm tuyệt vời. Nhẹ hơn vẻ ngoài cồng kềnh rất nhiều.</p><h2>Phù hợp với</h2><p>Streetwear, outfit nổi bật, hay những ngày muốn thể hiện cá tính mạnh. Không dành cho người thích kín đáo!</p>',
    'yoast_kw'          => 'puma rs-x reinvention',
    'yoast_title'       => 'Puma RS-X Reinvention · KickZone',
    'yoast_desc'        => 'Puma RS-X Reinvention chính hãng tại KickZone. Chunky sneaker táo bạo, đế dày ấn tượng. Size 38-44.',
  ],
  [
    'name'              => 'Converse Chuck Taylor All Star',
    'sku'               => 'CV-CT-BLK',
    'regular_price'     => 1800000,
    'sale_price'        => null,
    'featured'          => false,
    'categories'        => ['Classic'],
    'tags'              => ['converse', 'canvas', 'classic', 'unisex'],
    'colors'            => ['Đen', 'Trắng', 'Đỏ', 'Navy'],
    'image_id'          => $imgs['ct'],
    'short_description' => 'Đôi giày canvas kinh điển của mọi thế hệ. Chuck Taylor All Star — không bao giờ lỗi mốt.',
    'description'       => '<p>Converse Chuck Taylor All Star là đôi giày bán chạy nhất lịch sử nhân loại. Từ 1917 đến nay, thiết kế này đã đồng hành cùng hàng tỷ người trên khắp thế giới.</p><h2>Canvas vải thoáng</h2><p>Upper vải canvas thoáng khí, nhẹ nhàng. Dễ vệ sinh, dễ bảo quản. Mũi giày bo tròn với miếng cao su bảo vệ đặc trưng.</p><h2>Đế cao su vulcanized</h2><p>Đế cao su lưu hóa siêu bền, được làm theo phương pháp thủ công truyền thống. Đường viền trắng iconic chạy quanh đế.</p><h2>Phù hợp với</h2><p>Mọi phong cách, mọi lứa tuổi. Casual, vintage, grunge, pop art — Chuck Taylor là tờ giấy trắng cho phong cách của bạn.</p>',
    'yoast_kw'          => 'converse chuck taylor all star',
    'yoast_title'       => 'Converse Chuck Taylor All Star · KickZone',
    'yoast_desc'        => 'Converse Chuck Taylor All Star chính hãng tại KickZone. Đôi giày kinh điển mọi thế hệ. Nhiều màu, size 36-45.',
  ],
  [
    'name'              => 'Vans Old Skool Black/White',
    'sku'               => 'VN-OS-BW',
    'regular_price'     => 2200000,
    'sale_price'        => 1900000,
    'featured'          => false,
    'categories'        => ['Skate'],
    'tags'              => ['vans', 'skate', 'classic', 'oldskool'],
    'colors'            => ['Đen/Trắng', 'All Black', 'Trắng'],
    'image_id'          => $imgs['vans'],
    'short_description' => 'Giày skate huyền thoại với sọc bên Sidestripe iconic. Vans Old Skool — đơn giản, bền, không bao giờ out of style.',
    'description'       => '<p>Vans Old Skool ra mắt năm 1977 và ngay lập tức trở thành biểu tượng của văn hóa skate California. Cho đến ngày nay, đây vẫn là một trong những đôi giày được yêu thích nhất toàn cầu.</p><h2>Sidestripe đặc trưng</h2><p>Sọc Vans Sidestripe màu trắng trên nền đen tạo nên nhận diện thương hiệu cực kỳ mạnh. Đây là chi tiết không thể thiếu trên mọi đôi Old Skool.</p><h2>Upper vải + da lộn</h2><p>Kết hợp vải canvas và da lộn tạo nên sự bền bỉ vượt trội so với các giày vải thông thường. Lớp gia cố ở mũi và gót bảo vệ tốt khi trượt ván.</p><h2>Đế Waffle</h2><p>Đế Waffle rubber iconic của Vans cho độ bám tuyệt vời trên ván trượt lẫn mặt đường thông thường.</p>',
    'yoast_kw'          => 'vans old skool đen trắng',
    'yoast_title'       => 'Vans Old Skool Black/White · KickZone',
    'yoast_desc'        => 'Vans Old Skool Black/White chính hãng tại KickZone. Giày skate kinh điển, đế Waffle bám tốt. Size 36-45.',
  ],
  [
    'name'              => 'Nike Air Max 270',
    'sku'               => 'NK-AM270-BLK',
    'regular_price'     => 3500000,
    'sale_price'        => null,
    'featured'          => true,
    'categories'        => ['Running'],
    'tags'              => ['nike', 'airmax', 'cushion', 'running'],
    'colors'            => ['Đen', 'Trắng', 'Xám/Cam'],
    'image_id'          => $imgs['am270'],
    'short_description' => 'Túi Air Max 270 lớn nhất từ trước đến nay — êm ái tối đa cho mọi ngày. Lifestyle runner perfect.',
    'description'       => '<p>Nike Air Max 270 sở hữu túi Air lớn nhất trong lịch sử dòng Air Max, cao 32mm ở gót chân. Kết hợp giữa hiệu suất running và aesthetics lifestyle hiện đại.</p><h2>Air Unit 270</h2><p>Túi Air 270 độc đáo nhìn thấy rõ từ hai bên, cung cấp độ đệm tối đa và trả lại năng lượng tốt. Không chỉ đẹp mắt mà còn cực kỳ thoải mái.</p><h2>Upper mesh thoáng</h2><p>Upper mesh kỹ thuật với phần overlay tổng hợp tạo độ thoáng và hỗ trợ cấu trúc chân. Lót trong mềm mại, phù hợp đi cả ngày dài.</p><h2>Phù hợp với</h2><p>Đi bộ, chạy nhẹ, gym, đi chơi. Thiết kế lifestyle-forward phù hợp với nhiều outfit từ casual đến sporty.</p>',
    'yoast_kw'          => 'nike air max 270',
    'yoast_title'       => 'Nike Air Max 270 · KickZone',
    'yoast_desc'        => 'Nike Air Max 270 chính hãng tại KickZone. Túi Air 270 đệm tối đa, thiết kế lifestyle hiện đại. Size 38-46.',
  ],
  [
    'name'              => 'Adidas Stan Smith White',
    'sku'               => 'AD-SS-WHT',
    'regular_price'     => 2800000,
    'sale_price'        => 2400000,
    'featured'          => true,
    'categories'        => ['Lifestyle'],
    'tags'              => ['adidas', 'stan-smith', 'minimalist', 'white'],
    'colors'            => ['Trắng/Xanh lá', 'Trắng/Đỏ', 'Trắng/Navy'],
    'image_id'          => $imgs['ss'],
    'short_description' => 'Tối giản, thanh lịch và không bao giờ lỗi mốt. Adidas Stan Smith là chuẩn mực của giày lifestyle minimalist.',
    'description'       => '<p>Adidas Stan Smith ra đời năm 1965 như một đôi giày tennis chuyên dụng, nhưng đã nhanh chóng trở thành biểu tượng lifestyle toàn cầu với thiết kế tối giản đặc trưng.</p><h2>Minimalist tối giản</h2><p>Upper da trắng tinh không có nhiều chi tiết rườm rà. Ba sọc Adidas được đục lỗ thay vì in trực tiếp, tạo nên sự khác biệt tinh tế. Lưỡi gà phẳng với ảnh Stan Smith kinh điển.</p><h2>Chất liệu da bền</h2><p>Upper da thật hoặc da tổng hợp cao cấp, dễ vệ sinh và bền với thời gian. Đường may tỉ mỉ, chi tiết hoàn thiện cẩn thận.</p><h2>Phù hợp với</h2><p>Mọi outfit từ casual đến semi-formal. Phong cách minimalist, preppy, Scandinavian hay Korean — Stan Smith đều phù hợp.</p>',
    'yoast_kw'          => 'adidas stan smith trắng',
    'yoast_title'       => 'Adidas Stan Smith White · KickZone',
    'yoast_desc'        => 'Adidas Stan Smith White chính hãng tại KickZone. Minimalist kinh điển, da thật bền. Size 36-45.',
  ],
];

foreach ($products_data as $p) {
    $product_id = kz_product($p);
    if ($product_id) echo "  Product: {$p['name']} (ID: {$product_id})\n";
}

/* ==========================================================
   7. CONTACT FORMS (CF7)
   ========================================================== */
echo "→ Creating Contact Form 7 forms...\n";

if (class_exists('WPCF7_ContactForm')) {
    // Main contact form
    $forms = WPCF7_ContactForm::find(['title' => 'KickZone Contact Form']);
    $form = $forms ? $forms[0] : WPCF7_ContactForm::get_template();
    $form->set_title('KickZone Contact Form');
    $form->set_properties([
        'form' => '[text* your-name placeholder "Họ và tên *"]' . "\n"
                . '[email* your-email placeholder "Email *"]' . "\n"
                . '[tel your-phone placeholder "Số điện thoại"]' . "\n"
                . '[text your-subject placeholder "Chủ đề"]' . "\n"
                . '[textarea* your-message placeholder "Nội dung tin nhắn *"]' . "\n"
                . '[submit "GỬI TIN NHẮN →"]',
        'mail' => [
            'subject'    => 'KickZone - Liên hệ mới từ [your-name]',
            'sender'     => 'KickZone <hello@kickzone.vn>',
            'body'       => "Họ tên: [your-name]\nEmail: [your-email]\nSĐT: [your-phone]\nChủ đề: [your-subject]\n\nNội dung:\n[your-message]",
            'recipient'  => 'hello@kickzone.vn',
            'additional_headers' => 'Reply-To: [your-email]',
            'attachments'=> '',
            'use_html'   => false,
            'exclude_blank' => false,
        ],
    ]);
    $cf_id = $form->save();

    // Newsletter form
    $nl_forms = WPCF7_ContactForm::find(['title' => 'KickZone Newsletter']);
    $nl_form = $nl_forms ? $nl_forms[0] : WPCF7_ContactForm::get_template();
    $nl_form->set_title('KickZone Newsletter');
    $nl_form->set_properties([
        'form' => '<div style="display:flex;gap:12px;max-width:480px;margin:0 auto;">'
                . '[email* newsletter-email placeholder "Nhập email của bạn"]'
                . '[submit "ĐĂNG KÝ"]'
                . '</div>',
        'mail' => [
            'subject'    => 'KickZone - Đăng ký nhận tin mới',
            'sender'     => 'KickZone <hello@kickzone.vn>',
            'body'       => "Email đăng ký: [newsletter-email]",
            'recipient'  => 'hello@kickzone.vn',
            'additional_headers' => '',
            'attachments'=> '',
            'use_html'   => false,
            'exclude_blank' => false,
        ],
    ]);
    $nl_form->save();
    echo "  Created: Contact Form + Newsletter Form\n";
} else {
    echo "  [WARN] Contact Form 7 not active — skipping forms.\n";
}

/* ==========================================================
   8. MENUS
   ========================================================== */
echo "→ Setting up navigation menus...\n";

// Primary menu
$menu_name = 'Menu chính';
$menu_obj  = wp_get_nav_menu_object($menu_name);
$menu_id   = $menu_obj ? $menu_obj->term_id : wp_create_nav_menu($menu_name);

// Clear existing items
foreach (wp_get_nav_menu_items($menu_id) ?: [] as $item) {
    wp_delete_post($item->ID, true);
}

// Add items
$shop_page = get_option('woocommerce_shop_page_id');
$menu_pages = [
    $home_id    => 'Trang chủ',
    $shop_page  => 'Shop',
    $about_id   => 'Giới thiệu',
    $blog_id    => 'Blog',
    $contact_id => 'Liên hệ',
];
foreach ($menu_pages as $page_id => $label) {
    if (!$page_id) continue;
    wp_update_nav_menu_item($menu_id, 0, [
        'menu-item-title'     => $label,
        'menu-item-object'    => 'page',
        'menu-item-object-id' => $page_id,
        'menu-item-type'      => 'post_type',
        'menu-item-status'    => 'publish',
    ]);
}

// Assign menu to locations
$locations = get_theme_mod('nav_menu_locations') ?: [];
$locations['primary'] = $menu_id;
$locations['secondary'] = $menu_id;
set_theme_mod('nav_menu_locations', $locations);

// Footer menu
$footer_menu_name = 'Footer Menu';
$footer_obj  = wp_get_nav_menu_object($footer_menu_name);
$footer_id   = $footer_obj ? $footer_obj->term_id : wp_create_nav_menu($footer_menu_name);
foreach (wp_get_nav_menu_items($footer_id) ?: [] as $item) wp_delete_post($item->ID, true);

$footer_pages = [$shop_page => 'Shop', $about_id => 'Giới thiệu', $blog_id => 'Blog', $contact_id => 'Liên hệ', $privacy_id => 'Chính sách bảo mật'];
foreach ($footer_pages as $page_id => $label) {
    if (!$page_id) continue;
    wp_update_nav_menu_item($footer_id, 0, [
        'menu-item-title'     => $label,
        'menu-item-object'    => 'page',
        'menu-item-object-id' => $page_id,
        'menu-item-type'      => 'post_type',
        'menu-item-status'    => 'publish',
    ]);
}
$locations['footer'] = $footer_id;
set_theme_mod('nav_menu_locations', $locations);

/* ==========================================================
   9. READING / HOMEPAGE SETTINGS
   ========================================================== */
echo "→ Configuring homepage and blog page...\n";
update_option('show_on_front', 'page');
update_option('page_on_front', $home_id);
update_option('page_for_posts', $blog_id);

/* ==========================================================
   10. YOAST SEO SETTINGS
   ========================================================== */
echo "→ Configuring Yoast SEO...\n";
if (defined('WPSEO_VERSION') || class_exists('WPSEO_Options')) {
    // Basic Yoast options
    $yoast_opts = get_option('wpseo', []);
    $yoast_opts['website_name']     = 'KickZone';
    $yoast_opts['company_or_person']= 'company';
    $yoast_opts['company_name']     = 'KickZone';
    $yoast_opts['enable_xml_sitemap']= true;
    update_option('wpseo', $yoast_opts);

    // Titles & metas
    $yoast_titles = get_option('wpseo_titles', []);
    $yoast_titles['title-home-wpseo']   = 'KickZone – Sneaker Chính Hãng Giá Tốt';
    $yoast_titles['metadesc-home-wpseo']= 'Mua giày thể thao Nike, Adidas, New Balance chính hãng tại KickZone. Giao hàng toàn quốc, đổi trả 7 ngày.';
    $yoast_titles['sep']                = '·';
    $yoast_titles['title-product']      = '%%title%% · KickZone';
    $yoast_titles['metadesc-product']   = '%%excerpt%%';
    $yoast_titles['title-post']         = '%%title%% · KickZone Blog';
    $yoast_titles['metadesc-post']      = '%%excerpt%%';
    update_option('wpseo_titles', $yoast_titles);

    // Enable sitemap
    update_option('wpseo_xml', ['enablexmlsitemap' => true]);
    echo "  Yoast SEO configured\n";
} else {
    echo "  [WARN] Yoast SEO not active — skipping\n";
}

/* ==========================================================
   11. ASTRA THEME SETTINGS
   ========================================================== */
echo "→ Configuring Astra theme...\n";
set_theme_mod('astra-color-global-palette', [
    'palette' => [
        ['color' => '#FF4713'],
        ['color' => '#0A0A0A'],
        ['color' => '#F5F4F0'],
        ['color' => '#1A1A1A'],
        ['color' => '#2E2E2E'],
        ['color' => '#111111'],
        ['color' => '#F0EDE6'],
        ['color' => '#FF4713'],
        ['color' => '#0A0A0A'],
    ],
]);

set_theme_mod('header-bg-color', '#FFFFFF');
set_theme_mod('footer-bg-color', '#FFFFFF');
set_theme_mod('link-color', '#0A0A0A');
set_theme_mod('button-bg-color', '#0A0A0A');
set_theme_mod('button-color', '#FFFFFF');
set_theme_mod('transparent-header-enable', false);
set_theme_mod('transparent-header-logo', 0);
set_theme_mod('different-transparent-logo', false);
set_theme_mod('different-retina-logo', false);
set_theme_mod('astra-site-logo-width', 160);
set_theme_mod('astra-ext-blog-layout', 'blog-layout-3');

/* ==========================================================
   12. FINAL FLUSH
   ========================================================== */
flush_rewrite_rules(true);

// Regenerate WooCommerce pages if missing
if (function_exists('wc_get_page_id') && wc_get_page_id('shop') <= 0) {
    WC_Install::create_pages();
}

echo "\n✅ KickZone bootstrap completed!\n";
echo "   → " . get_option('blogname') . " — " . get_option('blogdescription') . "\n";
echo "   → Site URL: " . get_option('siteurl') . "\n";
echo "   → Products: " . wp_count_posts('product')->publish . "\n";
echo "   → Posts: " . wp_count_posts('post')->publish . "\n";
echo "   → Pages: " . wp_count_posts('page')->publish . "\n";
