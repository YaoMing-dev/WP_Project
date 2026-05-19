<?php
// Implement KickZone UI/UX branding plan on the local WordPress site.

function kz_page($title, $slug, $content, $seo_title, $seo_desc) {
    $existing = get_page_by_path($slug, OBJECT, 'page');
    $post = [
        'post_title' => $title,
        'post_name' => $slug,
        'post_content' => $content,
        'post_status' => 'publish',
        'post_type' => 'page',
    ];
    $id = $existing ? wp_update_post($post + ['ID' => $existing->ID]) : wp_insert_post($post);
    update_post_meta($id, '_yoast_wpseo_title', $seo_title);
    update_post_meta($id, '_yoast_wpseo_metadesc', $seo_desc);
    return $id;
}

function kz_post($title, $category, $tags, $content, $seo_desc) {
    $existing = get_page_by_title($title, OBJECT, 'post');
    $cat_id = wp_create_category($category);
    $post = [
        'post_title' => $title,
        'post_name' => sanitize_title(remove_accents($title)),
        'post_content' => $content,
        'post_status' => 'publish',
        'post_type' => 'post',
        'post_category' => [$cat_id],
    ];
    $id = $existing ? wp_update_post($post + ['ID' => $existing->ID]) : wp_insert_post($post);
    wp_set_post_tags($id, $tags);
    update_post_meta($id, '_yoast_wpseo_metadesc', $seo_desc);
    return $id;
}

function kz_set_product_image($product_id, $url, $alt) {
    if (has_post_thumbnail($product_id) || !$url) {
        return;
    }
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';
    $attachment_id = media_sideload_image($url, $product_id, $alt, 'id');
    if (!is_wp_error($attachment_id)) {
        set_post_thumbnail($product_id, $attachment_id);
        update_post_meta($attachment_id, '_wp_attachment_image_alt', $alt);
    }
}

function kz_product($sku, $data) {
    if (!function_exists('wc_get_product_id_by_sku')) {
        return;
    }
    $id = wc_get_product_id_by_sku($sku);
    if (!$id) {
        $product = new WC_Product_Simple();
        $product->set_sku($sku);
    } else {
        $product = wc_get_product($id);
    }
    $product->set_name($data['name']);
    $product->set_status('publish');
    $product->set_catalog_visibility('visible');
    $product->set_featured(!empty($data['featured']));
    $product->set_short_description($data['short']);
    $product->set_description($data['description']);
    $product->set_regular_price($data['regular']);
    $product->set_sale_price($data['sale']);
    $product->set_manage_stock(true);
    $product->set_stock_quantity((int) $data['stock']);
    $product->set_stock_status('instock');
    $product_id = $product->save();

    $cat = term_exists($data['category'], 'product_cat');
    if (!$cat) {
        $cat = wp_insert_term($data['category'], 'product_cat');
    }
    if (!is_wp_error($cat)) {
        wp_set_object_terms($product_id, [(int) $cat['term_id']], 'product_cat');
    }
    wp_set_object_terms($product_id, $data['tags'], 'product_tag');

    $size = new WC_Product_Attribute();
    $size->set_name('Size');
    $size->set_options($data['sizes']);
    $size->set_visible(true);

    $color = new WC_Product_Attribute();
    $color->set_name('Màu sắc');
    $color->set_options([$data['color']]);
    $color->set_visible(true);

    $product = wc_get_product($product_id);
    $product->set_attributes([$size, $color]);
    $product->save();
    update_post_meta($product_id, '_yoast_wpseo_metadesc', $data['short']);
    kz_set_product_image($product_id, $data['image'], $data['alt']);
}

update_option('blogname', 'KickZone');
update_option('blogdescription', 'Sneaker chính hãng, giá tốt');
update_option('timezone_string', 'Asia/Ho_Chi_Minh');
update_option('permalink_structure', '/%postname%/');
update_option('woocommerce_currency', 'VND');
update_option('woocommerce_default_country', 'VN:SG');
update_option('woocommerce_store_address', '123 Nguyễn Trãi');
update_option('woocommerce_store_city', 'TP.HCM');
update_option('woocommerce_store_postcode', '700000');

set_theme_mod('astra-settings', array_merge((array) get_theme_mod('astra-settings'), [
    'site-content-width' => 1180,
    'container-layout' => 'full-width-container',
    'link-color' => '#ff3d00',
    'theme-color' => '#ff3d00',
]));

$css = <<<'CSS'
:root {
  --kz-orange: #ff3d00;
  --kz-black: #0b0b0b;
  --kz-muted: #666;
  --kz-light: #f4f4f4;
  --kz-yellow: #ffe500;
  --kz-line: #e8e8e8;
}
body { font-family: "Open Sans", Arial, sans-serif; color: var(--kz-black); }
h1, h2, h3, .kz-brand, .kz-btn, .woocommerce-loop-product__title, .price { font-family: Montserrat, Arial, sans-serif; letter-spacing: 0; }
.site-content .ast-container { max-width: 100%; padding: 0; }
.entry-content > * { max-width: none; }
.kz-wrap { width: min(1180px, calc(100% - 32px)); margin: 0 auto; }
.kz-hero {
  min-height: 680px; display: grid; align-items: end; color: #fff;
  background: linear-gradient(90deg, rgba(0,0,0,.86), rgba(0,0,0,.42), rgba(0,0,0,.08)), url("https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=1900&q=84") center/cover;
}
.kz-hero .kz-wrap { padding: 96px 0 58px; }
.kz-eyebrow { color: #ffd3c4; font-weight: 800; text-transform: uppercase; font-size: 13px; margin: 0 0 16px; }
.kz-hero h1 { font-size: clamp(44px, 8vw, 92px); line-height: .95; margin: 0 0 18px; color: #fff; }
.kz-hero p { max-width: 640px; font-size: 18px; color: rgba(255,255,255,.88); margin-bottom: 28px; }
.kz-actions, .kz-newsletter-form { display: flex; gap: 12px; flex-wrap: wrap; }
.kz-btn, .wp-block-button__link, .button, .woocommerce a.button, .woocommerce button.button, .woocommerce input.button {
  border-radius: 8px !important; background: var(--kz-orange) !important; color: #fff !important;
  font-weight: 800 !important; padding: 13px 18px !important; border: 0 !important; min-height: 46px;
}
.kz-btn.secondary { background: #fff !important; color: var(--kz-black) !important; }
.kz-section { padding: 78px 0; }
.kz-section.light { background: var(--kz-light); }
.kz-head { display: flex; align-items: end; justify-content: space-between; gap: 24px; margin-bottom: 28px; }
.kz-head h2 { font-size: clamp(30px, 4vw, 46px); line-height: 1.06; margin: 0; color: var(--kz-black); }
.kz-head p { max-width: 560px; color: var(--kz-muted); margin: 8px 0 0; }
.kz-grid-3, .kz-values, .kz-blog-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 18px; }
.kz-cat { min-height: 310px; border-radius: 8px; overflow: hidden; position: relative; color: #fff; background: #111; display: flex; align-items: flex-end; padding: 24px; }
.kz-cat:before { content: ""; position: absolute; inset: 0; background: var(--img) center/cover; transition: transform .35s ease; }
.kz-cat:after { content: ""; position: absolute; inset: 0; background: linear-gradient(180deg, transparent, rgba(0,0,0,.78)); }
.kz-cat:hover:before { transform: scale(1.06); }
.kz-cat > div { position: relative; z-index: 2; }
.kz-cat h3 { color: #fff; margin: 0; font-size: 26px; }
.kz-card { background: #fff; border: 1px solid var(--kz-line); border-radius: 8px; padding: 22px; }
.kz-icon { width: 48px; height: 48px; border-radius: 8px; display: grid; place-items: center; background: var(--kz-black); color: #fff; font-size: 22px; margin-bottom: 14px; }
.kz-sale { background: var(--kz-black); color: #fff; padding: 52px 0; }
.kz-sale .kz-wrap { display: flex; justify-content: space-between; align-items: center; gap: 22px; }
.kz-sale h2 { color: #fff; font-size: clamp(32px, 5vw, 58px); margin: 0; }
.kz-sale strong { color: var(--kz-yellow); }
.kz-about { display: grid; grid-template-columns: 1fr 1fr; gap: 34px; align-items: center; }
.kz-about-img { min-height: 500px; border-radius: 8px; background: url("https://images.unsplash.com/photo-1528701800489-20be3c4ea627?auto=format&fit=crop&w=1200&q=82") center/cover; }
.kz-contact { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
.kz-panel { background: #fff; border: 1px solid var(--kz-line); border-radius: 8px; padding: 24px; }
.kz-map { min-height: 290px; border-radius: 8px; background: linear-gradient(45deg, rgba(255,61,0,.18), rgba(0,0,0,.08)), url("https://images.unsplash.com/photo-1524661135-423995f22d0b?auto=format&fit=crop&w=1000&q=80") center/cover; margin-top: 14px; }
.kz-newsletter { background: var(--kz-orange); color: #fff; padding: 54px 0; }
.kz-newsletter .kz-wrap { display: grid; grid-template-columns: 1fr minmax(280px, 460px); gap: 24px; align-items: center; }
.kz-newsletter h2 { color: #fff; margin: 0 0 8px; }
.kz-newsletter input { min-height: 48px; border: 0; border-radius: 8px; padding: 0 14px; flex: 1; min-width: 0; }
.woocommerce ul.products li.product { border: 1px solid var(--kz-line); border-radius: 8px; padding: 0 0 18px !important; overflow: hidden; background: #fff; }
.woocommerce ul.products li.product img { aspect-ratio: 4 / 3; object-fit: cover; margin: 0 0 14px !important; background: var(--kz-light); }
.woocommerce ul.products li.product .woocommerce-loop-product__title, .woocommerce ul.products li.product .price, .woocommerce ul.products li.product .button { margin-left: 16px !important; margin-right: 16px !important; }
.woocommerce span.onsale { background: var(--kz-orange); border-radius: 8px; }
.kz-post-body { width: min(840px, calc(100% - 32px)); margin: 0 auto; padding: 60px 0; }
.kz-post-body h1 { font-size: clamp(34px, 5vw, 56px); line-height: 1.05; }
@media (max-width: 920px) {
  .kz-grid-3, .kz-values, .kz-blog-grid, .kz-about, .kz-contact, .kz-newsletter .kz-wrap { grid-template-columns: 1fr; }
  .kz-sale .kz-wrap, .kz-head { align-items: flex-start; flex-direction: column; }
  .kz-hero { min-height: 620px; }
}
CSS;
wp_update_custom_css_post($css);

$home_content = <<<'HTML'
<section class="kz-hero">
  <div class="kz-wrap">
    <p class="kz-eyebrow">Sneaker chính hãng | Đổi size 7 ngày</p>
    <h1>KickZone Sneaker Store</h1>
    <p>Giày thể thao chính hãng, dễ phối, giá rõ ràng. Chọn sneaker đúng phong cách cho đi học, đi làm, chạy nhẹ và streetwear hằng ngày.</p>
    <div class="kz-actions"><a class="kz-btn" href="/shop">Mua ngay</a><a class="kz-btn secondary" href="/shop">Xem bộ sưu tập</a></div>
  </div>
</section>
<section class="kz-section">
  <div class="kz-wrap">
    <div class="kz-head"><div><h2>Danh mục nổi bật</h2><p>Chọn nhanh theo nhu cầu thật: phối đồ, chạy nhẹ, đi học hoặc streetwear cuối tuần.</p></div></div>
    <div class="kz-grid-3">
      <a class="kz-cat" href="/product-category/sneaker/" style="--img:url('https://images.unsplash.com/photo-1600185365483-26d7a4cc7519?auto=format&fit=crop&w=900&q=80')"><div><h3>Sneaker</h3><p>Năng động, dễ phối, hợp giới trẻ.</p></div></a>
      <a class="kz-cat" href="/product-category/running/" style="--img:url('https://images.unsplash.com/photo-1460353581641-37baddab0fa2?auto=format&fit=crop&w=900&q=80')"><div><h3>Running</h3><p>Êm chân cho di chuyển cả ngày.</p></div></a>
      <a class="kz-cat" href="/product-category/lifestyle/" style="--img:url('https://images.unsplash.com/photo-1514989940723-e8e51635b782?auto=format&fit=crop&w=900&q=80')"><div><h3>Lifestyle</h3><p>Tối giản, sạch, dễ mặc.</p></div></a>
    </div>
  </div>
</section>
<section class="kz-section light">
  <div class="kz-wrap">
    <div class="kz-head"><div><h2>Sản phẩm bán chạy</h2><p>8 sản phẩm WooCommerce đã có SKU, giá sale, stock, category, tag, size và màu sắc.</p></div><a class="kz-btn" href="/shop">Xem shop</a></div>
    [featured_products limit="4" columns="4"]
  </div>
</section>
<section class="kz-sale"><div class="kz-wrap"><div><h2>SALE <strong>20%</strong> RUNNING</h2><p>Áp dụng cho Adidas Ultra Boost 22 và Nike Air Max 270 trong tuần khai trương.</p></div><a class="kz-btn secondary" href="/shop">Mua ngay</a></div></section>
<section class="kz-section">
  <div class="kz-wrap">
    <div class="kz-head"><div><h2>Vì sao chọn KickZone?</h2><p>Trải nghiệm mua sneaker rõ ràng, nhanh, phù hợp bài WordPress bán hàng.</p></div></div>
    <div class="kz-values">
      <div class="kz-card"><div class="kz-icon">✓</div><h3>Hàng chính hãng</h3><p>Thông tin sản phẩm minh bạch: SKU, giá, stock, size, màu sắc và tag.</p></div>
      <div class="kz-card"><div class="kz-icon">↻</div><h3>Đổi size 7 ngày</h3><p>Hỗ trợ đổi size khi sản phẩm còn nguyên tem, hộp và chưa qua sử dụng.</p></div>
      <div class="kz-card"><div class="kz-icon">⚡</div><h3>Tư vấn nhanh</h3><p>Gợi ý mẫu giày theo nhu cầu: đi học, đi làm, running hoặc streetwear.</p></div>
    </div>
  </div>
</section>
<section class="kz-newsletter"><div class="kz-wrap"><div><h2>Nhận ưu đãi mới</h2><p>Đăng ký để nhận mã giảm giá và bài viết chăm sóc sneaker.</p></div><form class="kz-newsletter-form"><input type="email" placeholder="Email của bạn"><button class="kz-btn secondary" type="submit">Đăng ký</button></form></div></section>
HTML;

$about_content = <<<'HTML'
<section class="kz-section light"><div class="kz-wrap kz-about"><div><p class="kz-eyebrow" style="color:#ff3d00">Về KickZone</p><h1>Shop sneaker cho giới trẻ yêu streetwear</h1><p>KickZone được xây dựng để giúp khách hàng chọn đúng đôi giày cho nhu cầu thật: đi học, đi làm, đi chơi, chạy nhẹ hoặc phối đồ hằng ngày.</p><p>Website dùng WordPress, WooCommerce, Yoast SEO và Contact Form 7 để mô phỏng một shop sneaker có thể vận hành thực tế.</p><div class="kz-values"><div class="kz-card"><h3>Sứ mệnh</h3><p>Thông tin giày rõ ràng, dễ hiểu, dễ mua.</p></div><div class="kz-card"><h3>Tầm nhìn</h3><p>Trở thành shop sneaker online trẻ trung và đáng tin cậy.</p></div><div class="kz-card"><h3>Cam kết</h3><p>Chính hãng, minh bạch giá, hỗ trợ đổi size.</p></div></div></div><div class="kz-about-img"></div></div></section>
HTML;

$shop_content = <<<'HTML'
<section class="kz-section light"><div class="kz-wrap"><div class="kz-head"><div><h1>Shop giày thể thao</h1><p>Catalog WooCommerce với sản phẩm sneaker chính hãng, giá sale, SKU, stock, size và màu sắc.</p></div></div>[products limit="12" columns="3" orderby="date" order="DESC"]</div></section>
HTML;

$blog_content = <<<'HTML'
<section class="kz-section light"><div class="kz-wrap"><div class="kz-head"><div><h1>Blog sneaker</h1><p>Xu hướng, review và hướng dẫn chăm sóc giày cho người mới bắt đầu chơi sneaker.</p></div></div>[display-posts posts_per_page="8" include_date="true"]<p>Nếu shortcode blog không hiển thị trên bản local, vào Posts trong WordPress để xem đủ 8 bài viết đã tạo.</p></div></section>
HTML;

$contact_content = <<<'HTML'
<section class="kz-section light"><div class="kz-wrap"><div class="kz-head"><div><h1>Liên hệ KickZone</h1><p>Cần tư vấn size, chính sách đổi trả hoặc mẫu giày phù hợp? Gửi thông tin cho KickZone.</p></div></div><div class="kz-contact"><div class="kz-panel">[contact-form-7 title="KickZone Contact Form"]</div><div class="kz-panel"><h2>Thông tin cửa hàng</h2><p><strong>Địa chỉ:</strong> 123 Nguyễn Trãi, Quận 1, TP.HCM</p><p><strong>Điện thoại:</strong> 0909 123 456</p><p><strong>Email:</strong> hello@kickzone.vn</p><p><strong>Giờ mở cửa:</strong> 9:00 - 21:00, Thứ 2 - Chủ nhật</p><div class="kz-map"></div><p><small>Dữ liệu form chỉ dùng để phản hồi yêu cầu tư vấn, không chia sẻ cho bên thứ ba nếu chưa có sự đồng ý.</small></p></div></div></div></section>
HTML;

$home = kz_page('Trang chủ', 'trang-chu', $home_content, 'KickZone - Sneaker Chính Hãng Giá Tốt', 'Mua giày thể thao chính hãng Nike, Adidas, New Balance tại KickZone. Giao hàng nhanh, đổi trả dễ dàng.');
$about = kz_page('Giới thiệu', 'gioi-thieu', $about_content, 'Về Chúng Tôi | KickZone Sneaker Store', 'KickZone là cửa hàng sneaker chuyên biệt với sứ mệnh mang đến giày thể thao chính hãng, dễ chọn và dễ mua.');
$shop = kz_page('Shop', 'shop', $shop_content, 'Shop Giày Thể Thao | KickZone', 'Khám phá bộ sưu tập sneaker đa dạng với giá từ 1.8 triệu. Hàng chính hãng, nhiều size, tư vấn nhanh.');
$blog = kz_page('Blog', 'blog', $blog_content, 'Blog Sneaker, Review Và Hướng Dẫn | KickZone', 'Đọc bài viết về xu hướng sneaker, cách vệ sinh giày, review Nike, Adidas và mẹo bảo quản giày.');
$contact = kz_page('Liên hệ', 'lien-he', $contact_content, 'Liên Hệ KickZone | Tư Vấn Sneaker Chính Hãng', 'Liên hệ KickZone để được tư vấn chọn size, chính sách đổi trả và thông tin sản phẩm sneaker chính hãng.');

$cart = kz_page('Giỏ hàng', 'gio-hang', '<section class="kz-section light"><div class="kz-wrap"><h1>Giỏ hàng</h1>[woocommerce_cart]</div></section>', 'Giỏ Hàng | KickZone', 'Xem sản phẩm sneaker đã thêm vào giỏ hàng tại KickZone.');
$checkout = kz_page('Thanh toán', 'thanh-toan', '<section class="kz-section light"><div class="kz-wrap"><h1>Thanh toán</h1>[woocommerce_checkout]</div></section>', 'Thanh Toán | KickZone', 'Hoàn tất đơn hàng sneaker tại KickZone.');
$account = kz_page('Tài khoản', 'tai-khoan', '<section class="kz-section light"><div class="kz-wrap"><h1>Tài khoản</h1>[woocommerce_my_account]</div></section>', 'Tài Khoản | KickZone', 'Quản lý tài khoản mua hàng KickZone.');
update_option('woocommerce_cart_page_id', $cart);
update_option('woocommerce_checkout_page_id', $checkout);
update_option('woocommerce_myaccount_page_id', $account);

update_option('show_on_front', 'page');
update_option('page_on_front', $home);
update_option('page_for_posts', $blog);

$products = [
    'KZ-NK-AF1' => ['name' => 'Nike Air Force 1 White', 'short' => 'Sneaker trắng cổ điển, dễ phối đồ và phù hợp sử dụng hằng ngày.', 'description' => 'Nike Air Force 1 White là mẫu sneaker cổ điển phù hợp với nhiều phong cách khác nhau, từ đi học, đi làm đến phối đồ streetwear cuối tuần. Phần upper màu trắng dễ phối, form giày ổn định, đế chắc và cảm giác mang quen thuộc. Sản phẩm phù hợp với khách hàng cần một đôi giày bền, dễ vệ sinh, không lỗi mốt và có thể sử dụng thường xuyên.', 'regular' => '2800000', 'sale' => '2520000', 'stock' => 18, 'category' => 'Sneaker', 'tags' => ['nike', 'classic', 'white'], 'sizes' => ['38','39','40','41','42','43','44'], 'color' => 'White', 'featured' => true, 'image' => 'https://images.unsplash.com/photo-1600185365926-3a2ce3cdb9eb?auto=format&fit=crop&w=900&q=80', 'alt' => 'Nike Air Force 1 White size 42'],
    'KZ-AD-UB22' => ['name' => 'Adidas Ultra Boost 22', 'short' => 'Giày running êm, thoáng khí, phù hợp chạy nhẹ và di chuyển cả ngày.', 'description' => 'Adidas Ultra Boost 22 phù hợp với khách hàng cần một đôi giày running có độ êm tốt và vẫn dễ phối với trang phục hằng ngày. Phần đệm Boost mang lại cảm giác đàn hồi, upper thoáng khí giúp bàn chân thoải mái trong thời gian dài.', 'regular' => '3200000', 'sale' => '2880000', 'stock' => 12, 'category' => 'Running', 'tags' => ['adidas', 'boost', 'running'], 'sizes' => ['39','40','41','42','43','44'], 'color' => 'Black', 'featured' => true, 'image' => 'https://images.unsplash.com/photo-1587563871167-1ee9c731aefb?auto=format&fit=crop&w=900&q=80', 'alt' => 'Adidas Ultra Boost 22 running black'],
    'KZ-NB-574' => ['name' => 'New Balance 574 Grey', 'short' => 'Lifestyle sneaker màu xám trung tính, dáng retro dễ mặc.', 'description' => 'New Balance 574 Grey là lựa chọn an toàn cho người thích phong cách retro, đơn giản và lịch sự. Màu xám trung tính giúp đôi giày phối được với quần jeans, kaki, short hoặc outfit công sở thoải mái.', 'regular' => '2500000', 'sale' => '2250000', 'stock' => 20, 'category' => 'Lifestyle', 'tags' => ['newbalance', 'casual'], 'sizes' => ['38','39','40','41','42','43'], 'color' => 'Grey', 'image' => 'https://images.unsplash.com/photo-1539185441755-769473a23570?auto=format&fit=crop&w=900&q=80', 'alt' => 'New Balance 574 Grey lifestyle sneaker'],
    'KZ-PM-RSX' => ['name' => 'Puma RS-X Reinvention', 'short' => 'Sneaker chunky phối màu trẻ trung, tạo điểm nhấn streetwear.', 'description' => 'Puma RS-X Reinvention phù hợp với người thích dáng giày chunky và màu sắc nổi bật. Sản phẩm có form dày dặn, phần đế tạo cảm giác chắc chắn và thiết kế nhiều lớp giúp outfit basic trở nên cá tính hơn.', 'regular' => '2200000', 'sale' => '1980000', 'stock' => 9, 'category' => 'Sneaker', 'tags' => ['puma', 'chunky', 'colorful'], 'sizes' => ['39','40','41','42','43','44'], 'color' => 'Multi', 'image' => 'https://images.unsplash.com/photo-1556048219-bb6978360b84?auto=format&fit=crop&w=900&q=80', 'alt' => 'Puma RS-X Reinvention colorful chunky sneaker'],
    'KZ-CV-CTAS' => ['name' => 'Converse Chuck Taylor All Star', 'short' => 'Giày canvas unisex, nhẹ, dễ phối với phong cách học đường.', 'description' => 'Converse Chuck Taylor All Star là mẫu giày canvas quen thuộc với thiết kế tối giản, dễ phối và phù hợp cả nam lẫn nữ. Form giày gọn, trọng lượng nhẹ, thích hợp cho đi học, đi chơi và phong cách vintage.', 'regular' => '1800000', 'sale' => '1620000', 'stock' => 25, 'category' => 'Classic', 'tags' => ['converse', 'canvas', 'unisex'], 'sizes' => ['36','37','38','39','40','41','42','43','44'], 'color' => 'Black', 'image' => 'https://images.unsplash.com/photo-1622760807800-66cf1460fc4c?auto=format&fit=crop&w=900&q=80', 'alt' => 'Converse Chuck Taylor All Star canvas black'],
    'KZ-VN-OS' => ['name' => 'Vans Old Skool Black/White', 'short' => 'Mẫu skate classic với side stripe đặc trưng và đế bám tốt.', 'description' => 'Vans Old Skool Black White là mẫu giày skate classic có khả năng phối đồ rất linh hoạt. Phần thân giày đen trắng dễ mặc với nhiều phong cách, từ casual, streetwear đến outfit đi học.', 'regular' => '1900000', 'sale' => '1710000', 'stock' => 16, 'category' => 'Skate', 'tags' => ['vans', 'skate', 'classic'], 'sizes' => ['38','39','40','41','42','43','44'], 'color' => 'Black/White', 'image' => 'https://images.unsplash.com/photo-1525966222134-fcfa99b8ae77?auto=format&fit=crop&w=900&q=80', 'alt' => 'Vans Old Skool Black White skate sneaker'],
    'KZ-NK-AM270' => ['name' => 'Nike Air Max 270', 'short' => 'Giày đệm Air lớn ở gót, êm và hiện đại cho daily wear.', 'description' => 'Nike Air Max 270 có thiết kế hiện đại với túi Air lớn ở gót, tạo cảm giác êm rõ khi di chuyển. Mẫu giày phù hợp với người thích sneaker năng động, có thể dùng cho đi bộ, tập nhẹ và phối đồ hằng ngày.', 'regular' => '3500000', 'sale' => '3150000', 'stock' => 11, 'category' => 'Running', 'tags' => ['nike', 'airmax', 'cushion'], 'sizes' => ['39','40','41','42','43','44'], 'color' => 'White/Red', 'featured' => true, 'image' => 'https://images.unsplash.com/photo-1605408499391-6368c628ef42?auto=format&fit=crop&w=900&q=80', 'alt' => 'Nike Air Max 270 white red cushion'],
    'KZ-AD-SS' => ['name' => 'Adidas Stan Smith White', 'short' => 'Lifestyle sneaker tối giản, trắng xanh, phù hợp đi học và công sở.', 'description' => 'Adidas Stan Smith White là mẫu lifestyle sneaker tối giản lấy cảm hứng từ giày tennis. Tông trắng xanh dễ phối, phù hợp với quần jeans, chinos và trang phục lịch sự hằng ngày.', 'regular' => '2400000', 'sale' => '2160000', 'stock' => 14, 'category' => 'Lifestyle', 'tags' => ['adidas', 'minimalist'], 'sizes' => ['37','38','39','40','41','42','43','44'], 'color' => 'White/Green', 'image' => 'https://images.unsplash.com/photo-1608231387042-66d1773070a5?auto=format&fit=crop&w=900&q=80', 'alt' => 'Adidas Stan Smith White Green minimalist'],
];
foreach ($products as $sku => $data) {
    kz_product($sku, $data);
}

$blog_posts = [
    ['Top 5 Sneaker Nam Xu Hướng 2024', 'Xu hướng', 'sneaker, nam, streetwear', 'Năm 2024, xu hướng sneaker nam tập trung vào tính ứng dụng, khả năng phối đồ và cảm giác mang thoải mái. Thay vì chỉ chọn những đôi giày quá nổi bật, nhiều người ưu tiên các mẫu có thể dùng trong nhiều hoàn cảnh: đi học, đi làm, đi chơi và gặp bạn bè. Nike Air Force 1 White vẫn là lựa chọn an toàn vì form cổ điển, màu trắng dễ phối và ít bị lỗi mốt. Adidas Stan Smith phù hợp với người thích phong cách tối giản, sạch sẽ và lịch sự. New Balance 574 Grey lại mang cảm giác retro, hợp với quần jeans, kaki và áo thun basic. Khi chọn sneaker nam, bạn nên cân nhắc nhu cầu sử dụng, màu sắc trong tủ đồ và độ vừa chân.'],
    ['Cách Vệ Sinh Giày Trắng Tại Nhà Đúng Cách', 'Hướng dẫn', 'vệ sinh, giày trắng, sneaker care', 'Giày trắng dễ phối đồ nhưng cũng rất dễ bám bụi, vì vậy việc vệ sinh đúng cách giúp giày bền và giữ màu tốt hơn. Trước khi làm sạch, bạn nên tháo dây giày và lót giày ra riêng. Dùng bàn chải mềm phủ bụi trên bề mặt, tránh chà quá mạnh khi giày còn khô vì có thể làm xước chất liệu da hoặc vải. Không nên ngâm cả đôi giày trong nước, không dùng chất tẩy mạnh và không phơi trực tiếp dưới nắng gắt. Sau khi vệ sinh, nhét giày bằng giấy trắng hoặc khăn khô để giữ form, rồi phơi nơi mát.'],
    ['Nike vs Adidas: Thương Hiệu Nào Tốt Hơn?', 'Review', 'nike, adidas, review', 'Nike và Adidas đều là hai thương hiệu giày thể thao lớn, nhưng mỗi hãng có điểm mạnh riêng. Nike thường nổi bật ở thiết kế trẻ trung, nhận diện mạnh và nhiều dòng sneaker mang tính biểu tượng như Air Force 1, Air Max và Jordan. Adidas lại có lợi thế ở những mẫu giày tối giản và công nghệ đệm như Boost. Không có câu trả lời tuyệt đối cho việc thương hiệu nào tốt hơn. Điều quan trọng nhất là chọn đúng mẫu giày cho nhu cầu thật, đúng size và đúng ngân sách.'],
    ['Giày Running vs Giày Lifestyle: Khác Nhau Thế Nào?', 'Kiến thức', 'running, lifestyle, chọn giày', 'Giày running và giày lifestyle có thể nhìn giống nhau ở một số mẫu, nhưng mục đích thiết kế khác rõ. Giày running ưu tiên trọng lượng nhẹ, đệm êm, độ thoáng khí và khả năng hỗ trợ chuyển động. Giày lifestyle tập trung vào tính thời trang và khả năng phối đồ. Nếu bạn mua giày để tập luyện, hãy ưu tiên running shoes đúng mục đích. Nếu bạn cần giày đi học, đi làm, đi chơi và chụp ảnh outfit, lifestyle shoes sẽ phù hợp hơn.'],
    ['Bộ Sưu Tập Sneaker Mùa Hè 2024 Đáng Mong Chờ', 'Xu hướng', 'mùa hè, sneaker, phối đồ', 'Mùa hè 2024 ưu tiên những đôi sneaker có màu sáng, chất liệu thoáng và form không quá nặng. Các tông trắng, xám nhạt, xanh lá nhẹ và cam đỏ là những lựa chọn tạo cảm giác trẻ trung. Khi mua sneaker cho mùa hè, bạn nên chú ý chất liệu và khả năng thoáng khí. Những đôi giày quá dày hoặc khó thoát ẩm có thể gây khó chịu khi đi lâu.'],
    ['Cách Bảo Quản Sneaker Bền Đẹp Lâu Dài', 'Hướng dẫn', 'bảo quản, sneaker care', 'Bảo quản sneaker đúng cách giúp giày giữ form, hạn chế mùi và kéo dài tuổi thọ sử dụng. Sau khi mang, bạn nên để giày ở nơi khô thoáng trong vài giờ trước khi cất vào hộp. Nếu giày bị ướt, hãy nhét giày bằng giấy trắng để hút ẩm và giữ form, sau đó phơi nơi mát. Không dùng máy sấy nóng vì nhiệt có thể làm biến dạng đế và ảnh hưởng keo dán.'],
    ['Review Nike Air Force 1: Đôi Giày Kinh Điển Mọi Thời Đại', 'Review', 'nike, air force 1, classic', 'Nike Air Force 1 là một trong những mẫu sneaker phổ biến nhất vì thiết kế đơn giản, dễ phối và có tính biểu tượng cao. Phiên bản màu trắng đặc biệt được ưa chuộng vì có thể kết hợp với gần như mọi loại trang phục: jeans, kaki, jogger, short hay đồ streetwear. Điểm mạnh của Air Force 1 là độ bền, nhận diện thương hiệu và khả năng sử dụng lâu dài mà không lỗi mốt.'],
    ['Xu Hướng Màu Giày Hot Nhất Năm 2024', 'Xu hướng', 'màu sắc, sneaker, style', 'Xu hướng màu giày năm 2024 không chỉ xoay quanh màu trắng cổ điển mà còn mở rộng sang xám, xanh lá, đen trắng và các điểm nhấn cam đỏ. Màu trắng vẫn giữ vị trí quan trọng vì dễ phối và tạo cảm giác sạch sẽ. Màu xám được ưa chuộng vì trung tính nhưng ít bám bẩn hơn màu trắng. Khi chọn màu giày, hãy nhìn vào tủ đồ hiện có để chọn màu có thể mang thường xuyên.'],
];
foreach ($blog_posts as $bp) {
    kz_post($bp[0], $bp[1], $bp[2], '<div class="kz-post-body"><h1>' . esc_html($bp[0]) . '</h1><p>' . esc_html($bp[3]) . '</p><h2>Gợi ý từ KickZone</h2><p>Hãy ưu tiên đôi giày vừa chân, phù hợp nhu cầu sử dụng và dễ phối với trang phục hằng ngày. Một đôi sneaker tốt là đôi bạn có thể mang thường xuyên, chăm sóc dễ dàng và tự tin khi sử dụng.</p></div>', wp_trim_words($bp[3], 25));
}

if (class_exists('WPCF7_ContactForm')) {
    $forms = WPCF7_ContactForm::find(['title' => 'KickZone Contact Form']);
    $form = $forms ? $forms[0] : WPCF7_ContactForm::get_template();
    $form->set_title('KickZone Contact Form');
    $form->set_properties([
        'form' => '[text* your-name placeholder "Họ tên"]' . "\n" . '[tel* your-phone placeholder "Số điện thoại"]' . "\n" . '[email* your-email placeholder "Email"]' . "\n" . '[textarea* your-message placeholder "Bạn cần tư vấn mẫu giày, size hoặc chính sách đổi trả?"]' . "\n" . '[submit "Gửi liên hệ"]',
        'mail' => [
            'active' => true,
            'subject' => 'KickZone contact form',
            'sender' => '[_site_title] <wordpress@localhost>',
            'recipient' => 'hello@kickzone.vn',
            'body' => "From: [your-name] [your-email]\nPhone: [your-phone]\n\n[your-message]",
            'additional_headers' => 'Reply-To: [your-email]',
            'attachments' => '',
            'use_html' => false,
            'exclude_blank' => false,
        ],
    ]);
    $form->save();
}

$menu_obj = wp_get_nav_menu_object('KickZone Main Menu');
$menu_id = $menu_obj ? $menu_obj->term_id : wp_create_nav_menu('KickZone Main Menu');
foreach (wp_get_nav_menu_items($menu_id) ?: [] as $item) {
    wp_delete_post($item->ID, true);
}
foreach ([$home, $about, $shop, $blog, $contact] as $page_id) {
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
$locations['menu-1'] = $menu_id;
set_theme_mod('nav_menu_locations', $locations);

flush_rewrite_rules();
echo "KickZone UI/UX design implementation completed\n";
