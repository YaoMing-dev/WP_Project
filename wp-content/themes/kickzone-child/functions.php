<?php
/**
 * KickZone Child Theme — functions.php
 * Astra child theme for KickZone Sneaker Shop
 */

/* -------------------------------------------------------
   Enqueue parent + child styles
------------------------------------------------------- */
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style(
        'astra-parent',
        get_template_directory_uri() . '/style.css',
        [],
        wp_get_theme('astra')->get('Version')
    );
    wp_enqueue_style(
        'kickzone-child',
        get_stylesheet_uri(),
        ['astra-parent'],
        wp_get_theme()->get('Version')
    );
});

/* -------------------------------------------------------
   Theme support
------------------------------------------------------- */
add_action('after_setup_theme', function () {
    add_theme_support('woocommerce');
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');
    add_theme_support('custom-logo', [
        'height'      => 44,
        'width'       => 220,
        'flex-height' => true,
        'flex-width'  => true,
    ]);
});

/* -------------------------------------------------------
   Announcement ticker above header
------------------------------------------------------- */
add_action('wp_body_open', function () {
    echo '<div class="kz-ticker" aria-label="Thông báo">'
       . '<div class="kz-ticker-track">'
       . kz_ticker_items()
       . kz_ticker_items()   // duplicate for seamless loop
       . '</div></div>';
});

function kz_ticker_items() {
    $items = [
        'New drops mỗi tuần',
        'Đổi size trong 7 ngày',
        'Sneaker chính hãng',
        'Running shoes giảm đến 20%',
        'Tư vấn fit theo chân',
        'Hotline: 0901 234 567',
    ];
    $out = '';
    foreach ($items as $item) {
        $out .= '<span>' . esc_html($item) . '</span><span class="kz-dot"></span>';
    }
    return $out;
}

/* -------------------------------------------------------
   Footer — copyright + GDPR note
------------------------------------------------------- */
add_filter('astra_footer_copyright_text', function () {
    return '&copy; ' . date('Y') . ' KickZone. All rights reserved. &nbsp;|&nbsp; '
         . '<a href="/chinh-sach-bao-mat">Chính sách bảo mật</a> &nbsp;|&nbsp; '
         . '<small>Dữ liệu form chỉ dùng để phản hồi, không chia sẻ bên thứ ba.</small>';
});

/* -------------------------------------------------------
   WooCommerce: currency symbol + position
------------------------------------------------------- */
add_filter('woocommerce_currency_symbol', function ($symbol, $currency) {
    return $currency === 'VND' ? '₫' : $symbol;
}, 10, 2);

add_filter('woocommerce_price_format', function ($format, $currency_pos) {
    return '%1$s%2$s';  // price then symbol
}, 10, 2);

/* -------------------------------------------------------
   WooCommerce: show # products per page
------------------------------------------------------- */
add_filter('loop_shop_per_page', function () { return 12; });

/* -------------------------------------------------------
   WooCommerce: related products count
------------------------------------------------------- */
add_filter('woocommerce_output_related_products_args', function ($args) {
    $args['posts_per_page'] = 4;
    $args['columns']        = 4;
    return $args;
});

/* -------------------------------------------------------
   Remove Astra sidebar on WooCommerce pages
------------------------------------------------------- */
add_filter('astra_page_layout', function ($layout) {
    if (is_shop() || is_product_category() || is_product()) {
        return 'no-sidebar';
    }
    return $layout;
});

/* -------------------------------------------------------
   Customize Astra colors via theme mods (runs on init)
------------------------------------------------------- */
add_action('after_setup_theme', function () {
    set_theme_mod('global-color-palette', [
        'palette' => [
            ['color' => '#FF4713', 'slug' => 'theme-color', 'name' => 'KickZone Orange'],
            ['color' => '#0A0A0A', 'slug' => 'theme-color-2', 'name' => 'KickZone Black'],
            ['color' => '#F5F4F0', 'slug' => 'theme-color-3', 'name' => 'KickZone White'],
            ['color' => '#1A1A1A', 'slug' => 'theme-color-4', 'name' => 'KickZone Dark'],
            ['color' => '#2E2E2E', 'slug' => 'theme-color-5', 'name' => 'KickZone Mid'],
            ['color' => '#111111', 'slug' => 'theme-color-6', 'name' => 'KickZone Text'],
            ['color' => '#F0EDE6', 'slug' => 'theme-color-7', 'name' => 'KickZone Cream'],
        ],
    ]);
}, 20);

/* -------------------------------------------------------
   Custom page title for shop archive
------------------------------------------------------- */
add_filter('woocommerce_page_title', function ($title) {
    if (is_shop()) return 'Shop';
    return $title;
});

/* -------------------------------------------------------
   Add product SKU to cart & order items (optional)
------------------------------------------------------- */
add_filter('woocommerce_get_item_data', function ($data, $cart_item) {
    if ($sku = $cart_item['data']->get_sku()) {
        $data[] = ['name' => 'SKU', 'value' => $sku];
    }
    return $data;
}, 10, 2);

/* -------------------------------------------------------
   [kz_recent_posts] shortcode — blog preview grid
------------------------------------------------------- */
add_shortcode('kz_recent_posts', function ($atts) {
    $atts  = shortcode_atts(['limit' => 3, 'columns' => 3], $atts);
    $posts = get_posts([
        'numberposts' => max(1, (int) $atts['limit']),
        'post_status' => 'publish',
        'post_type'   => 'post',
    ]);
    if (empty($posts)) {
        return '<p style="color:#111;text-align:center;padding:40px 0;">Chưa có bài viết.</p>';
    }
    $cols = max(1, min(4, (int) $atts['columns']));
    $out  = '<div class="kz-posts-grid kz-posts-cols-' . $cols . '">';
    foreach ($posts as $post) {
        $url     = get_permalink($post->ID);
        $title   = esc_html($post->post_title);
        $thumb   = get_the_post_thumbnail($post->ID, 'medium_large', ['class' => 'kz-post-card__img']);
        $cats    = get_the_category($post->ID);
        $cat     = $cats ? esc_html($cats[0]->name) : '';
        $date    = get_the_date('d/m/Y', $post);
        $excerpt = wp_trim_words($post->post_excerpt ?: wp_strip_all_tags($post->post_content), 18, '…');

        $out .= '<article class="kz-post-card">';
        $out .= '<a href="' . esc_url($url) . '" class="kz-post-card__thumb" tabindex="-1">';
        $out .= $thumb ?: '<div class="kz-post-card__thumb-placeholder"></div>';
        $out .= '</a>';
        $out .= '<div class="kz-post-card__body">';
        if ($cat) $out .= '<span class="kz-post-card__cat">' . $cat . '</span>';
        $out .= '<p class="kz-post-card__date">' . $date . '</p>';
        $out .= '<h3 class="kz-post-card__title"><a href="' . esc_url($url) . '">' . $title . '</a></h3>';
        $out .= '<p class="kz-post-card__excerpt">' . esc_html($excerpt) . '</p>';
        $out .= '<a href="' . esc_url($url) . '" class="kz-post-card__link">ĐỌC TIẾP →</a>';
        $out .= '</div></article>';
    }
    $out .= '</div>';
    return $out;
});

/* -------------------------------------------------------
   Allow SVG uploads (for logo)
------------------------------------------------------- */
add_filter('upload_mimes', function ($mimes) {
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
});
add_filter('wp_check_filetype_and_ext', function ($data, $file, $filename) {
    if (substr($filename, -4) === '.svg') {
        $data['ext']  = 'svg';
        $data['type'] = 'image/svg+xml';
    }
    return $data;
}, 10, 3);
