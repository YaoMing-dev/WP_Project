# KickZone - WordPress, Backend API, SEO Implementation

File này bổ sung phần còn thiếu ngoài frontend demo `kickzone-frontend.html`: WordPress setup, backend WooCommerce REST API, SEO, plugin, user, nội dung và checklist nộp bài.

## 1. WordPress Stack

Website KickZone dùng WordPress theo loại đề tài số 3: website bán hàng cơ bản / giới thiệu sản phẩm.

| Hạng mục | Cấu hình |
|---|---|
| CMS | WordPress 6.x |
| Theme | Astra Free |
| Page Builder | Elementor Free |
| E-commerce | WooCommerce |
| SEO | Yoast SEO |
| Form | Contact Form 7 |
| Backup | UpdraftPlus |
| Cache | W3 Total Cache |
| Hosting | InfinityFree / Hostinger / localhost |

Thiết lập WordPress bắt buộc:

1. Settings -> General:
   - Site Title: `KickZone`
   - Tagline: `Sneaker chính hãng, giá tốt`
   - Timezone: `Ho Chi Minh`
2. Settings -> Permalinks:
   - Chọn `Post name`
   - URL mẫu: `/%postname%/`
3. Appearance -> Themes:
   - Cài và activate `Astra`
4. Appearance -> Customize:
   - Primary color: `#FF4500`
   - Text color: `#111111`
   - Background: `#FFFFFF`
   - Heading font: `Montserrat`
   - Body font: `Open Sans`
5. Header:
   - Logo trái
   - Menu giữa: Trang chủ, Giới thiệu, Shop, Blog, Liên hệ
   - Cart icon phải
6. Footer:
   - Cột 1: Về KickZone
   - Cột 2: Liên kết nhanh
   - Cột 3: Liên hệ
   - Copyright: `© 2024 KickZone. All rights reserved.`

## 2. Backend

Backend của website là WordPress + WooCommerce. Không cần tự viết Node/PHP API riêng vì WooCommerce đã có REST API chuẩn.

### 2.1 Database

WordPress tự quản lý database MySQL gồm các bảng chính:

| Bảng | Mục đích |
|---|---|
| `wp_posts` | Trang, bài viết, sản phẩm WooCommerce |
| `wp_postmeta` | Giá, SKU, stock, thuộc tính sản phẩm |
| `wp_terms` | Category, tag |
| `wp_term_relationships` | Gán category/tag cho bài viết và sản phẩm |
| `wp_users` | Tài khoản người dùng |
| `wp_usermeta` | Role và thông tin user |
| `wp_options` | Cấu hình site, plugin, theme |
| `wp_woocommerce_order_items` | Dòng sản phẩm trong đơn hàng |

### 2.2 WooCommerce REST API

Bật API:

1. Vào `WooCommerce -> Settings -> Advanced -> REST API`
2. Chọn `Add key`
3. Description: `KickZone API Demo`
4. User: `admin_kickzone`
5. Permissions: `Read`
6. Generate API key
7. Lưu lại:
   - Consumer key: `ck_xxx`
   - Consumer secret: `cs_xxx`

Endpoints dùng trong báo cáo:

| Chức năng | Endpoint |
|---|---|
| Danh sách sản phẩm | `GET /wp-json/wc/v3/products` |
| Chi tiết sản phẩm | `GET /wp-json/wc/v3/products/{id}` |
| Danh mục sản phẩm | `GET /wp-json/wc/v3/products/categories` |
| Danh sách bài viết | `GET /wp-json/wp/v2/posts` |
| Danh sách trang | `GET /wp-json/wp/v2/pages` |

URL test mẫu:

```text
https://yourdomain.com/wp-json/wc/v3/products?consumer_key=ck_xxx&consumer_secret=cs_xxx
```

Response JSON mẫu để đưa vào báo cáo:

```json
[
  {
    "id": 101,
    "name": "Nike Air Force 1 White",
    "slug": "nike-air-force-1-white",
    "type": "simple",
    "status": "publish",
    "sku": "KZ-NK-AF1",
    "regular_price": "2800000",
    "sale_price": "2520000",
    "stock_quantity": 18,
    "categories": [
      { "id": 12, "name": "Sneaker", "slug": "sneaker" }
    ],
    "tags": [
      { "id": 21, "name": "nike", "slug": "nike" },
      { "id": 22, "name": "classic", "slug": "classic" }
    ]
  }
]
```

## 3. WooCommerce Products

Tạo tối thiểu 8 sản phẩm:

| Tên | SKU | Regular price | Sale price | Category | Tags | Stock |
|---|---:|---:|---:|---|---|---:|
| Nike Air Force 1 White | KZ-NK-AF1 | 2800000 | 2520000 | Sneaker | nike, classic, white | 18 |
| Adidas Ultra Boost 22 | KZ-AD-UB22 | 3200000 | 2880000 | Running | adidas, boost, running | 12 |
| New Balance 574 Grey | KZ-NB-574 | 2500000 | 2250000 | Lifestyle | newbalance, casual | 20 |
| Puma RS-X Reinvention | KZ-PM-RSX | 2200000 | 1980000 | Sneaker | puma, chunky, colorful | 9 |
| Converse Chuck Taylor All Star | KZ-CV-CTAS | 1800000 | 1620000 | Classic | converse, canvas, unisex | 25 |
| Vans Old Skool Black/White | KZ-VN-OS | 1900000 | 1710000 | Skate | vans, skate, classic | 16 |
| Nike Air Max 270 | KZ-NK-AM270 | 3500000 | 3150000 | Running | nike, airmax, cushion | 11 |
| Adidas Stan Smith White | KZ-AD-SS | 2400000 | 2160000 | Lifestyle | adidas, minimalist | 14 |

Thuộc tính cho mọi sản phẩm:

- Size: `38, 39, 40, 41, 42, 43, 44`
- Color: theo từng sản phẩm
- Product type: `Simple product`
- Tax status: `Taxable`
- Inventory: bật `Manage stock`
- Shipping class: `Standard`

Mô tả dài mẫu cho 1 sản phẩm:

```text
Nike Air Force 1 White là mẫu sneaker cổ điển phù hợp với nhiều phong cách khác nhau, từ đi học, đi làm đến phối đồ streetwear cuối tuần. Phần upper màu trắng dễ phối, form giày ổn định, đế chắc và cảm giác mang quen thuộc. Sản phẩm phù hợp với khách hàng cần một đôi giày bền, dễ vệ sinh, không lỗi mốt và có thể sử dụng thường xuyên. KickZone cung cấp đầy đủ size phổ biến, tư vấn chọn size trước khi mua và hỗ trợ đổi size trong 7 ngày nếu sản phẩm còn nguyên tem hộp.
```

Alt text ảnh sản phẩm:

- `Nike Air Force 1 White size 42`
- `Adidas Ultra Boost 22 running black`
- `New Balance 574 Grey lifestyle sneaker`
- `Puma RS-X Reinvention colorful chunky sneaker`
- `Converse Chuck Taylor All Star canvas black`
- `Vans Old Skool Black White skate sneaker`
- `Nike Air Max 270 white red cushion`
- `Adidas Stan Smith White Green minimalist`

## 4. Pages

Tạo 5 trang tĩnh trong WordPress:

| Trang | Slug | Nội dung |
|---|---|---|
| Trang chủ | `/` | Hero, category, featured products, sale banner, blog preview, newsletter |
| Giới thiệu | `/gioi-thieu` | Brand story, mission, vision, 3 lý do chọn KickZone |
| Shop | `/shop` | WooCommerce catalog, filter category/price/brand |
| Blog | `/blog` | Danh sách bài viết, sidebar search/category/tag |
| Liên hệ | `/lien-he` | Contact Form 7, địa chỉ, hotline, email, Google Maps |

Menu chính:

```text
Trang chủ | Giới thiệu | Shop | Blog | Liên hệ
```

Các trang WooCommerce tự sinh:

```text
/gio-hang
/thanh-toan
/tai-khoan
```

## 5. Blog Posts

Tạo 8 bài viết, mỗi bài tối thiểu 300 chữ khi nhập thật vào WordPress:

| Bài viết | Category | Tags |
|---|---|---|
| Top 5 Sneaker Nam Xu Hướng 2024 | Xu hướng | sneaker, nam, streetwear |
| Cách Vệ Sinh Giày Trắng Tại Nhà Đúng Cách | Hướng dẫn | vệ sinh, giày trắng |
| Nike vs Adidas: Thương Hiệu Nào Tốt Hơn? | Review | nike, adidas |
| Giày Running vs Giày Lifestyle: Khác Nhau Thế Nào? | Kiến thức | running, lifestyle |
| Bộ Sưu Tập Sneaker Mùa Hè 2024 Đáng Mong Chờ | Xu hướng | mùa hè, sneaker |
| Cách Bảo Quản Sneaker Bền Đẹp Lâu Dài | Hướng dẫn | bảo quản, sneaker care |
| Review Nike Air Force 1: Đôi Giày Kinh Điển Mọi Thời Đại | Review | air force 1, nike |
| Xu Hướng Màu Giày Hot Nhất Năm 2024 | Xu hướng | màu sắc, phối đồ |

## 6. SEO

### 6.1 Yoast SEO Settings

1. Yoast SEO -> Settings -> Site basics:
   - Website name: `KickZone`
   - Alternate website name: `KickZone Sneaker Store`
   - Separator: `|`
   - Organization name: `KickZone`
2. Yoast SEO -> Settings -> Site features:
   - XML sitemaps: `On`
   - SEO analysis: `On`
   - Readability analysis: `On`
3. Yoast SEO -> Settings -> Social sharing:
   - Facebook: `https://facebook.com/kickzone`
   - Instagram: `https://instagram.com/kickzone`
   - Default social image: ảnh hero sneaker 1200x630

### 6.2 On-page SEO

| Trang | SEO Title | Meta Description |
|---|---|---|
| Trang chủ | `KickZone - Sneaker Chính Hãng Giá Tốt` | `Mua giày thể thao chính hãng Nike, Adidas, New Balance tại KickZone. Giao hàng nhanh, đổi trả dễ dàng.` |
| Shop | `Shop Giày Thể Thao | KickZone` | `Khám phá bộ sưu tập sneaker đa dạng với giá từ 1.8 triệu. Hàng chính hãng, nhiều size, tư vấn nhanh.` |
| Giới thiệu | `Về Chúng Tôi | KickZone Sneaker Store` | `KickZone là cửa hàng sneaker chuyên biệt với sứ mệnh mang đến giày thể thao chính hãng, dễ chọn và dễ mua.` |
| Blog | `Blog Sneaker, Review Và Hướng Dẫn | KickZone` | `Đọc bài viết về xu hướng sneaker, cách vệ sinh giày, review Nike, Adidas và mẹo bảo quản giày.` |
| Liên hệ | `Liên Hệ KickZone | Tư Vấn Sneaker Chính Hãng` | `Liên hệ KickZone để được tư vấn chọn size, chính sách đổi trả và thông tin sản phẩm sneaker chính hãng.` |

### 6.3 Technical SEO

Checklist:

- Permalink dùng `Post name`
- Mỗi trang chỉ có 1 H1
- Mỗi ảnh có alt text mô tả thật
- Bật XML sitemap
- URL sitemap: `https://yourdomain.com/sitemap_index.xml`
- Submit sitemap lên Google Search Console nếu có domain thật
- Bật Page Cache và Browser Cache trong W3 Total Cache
- Redirect HTTP sang HTTPS trên hosting
- Tối ưu ảnh về WebP/JPG nén trước khi upload
- Product schema do WooCommerce sinh tự động
- Organization/ShoeStore schema có thể bổ sung bằng Yoast hoặc custom schema

## 7. Contact Form 7

Tạo form tên `KickZone Contact Form`.

Shortcode:

```text
[contact-form-7 id="123" title="KickZone Contact Form"]
```

Form template:

```text
[text* your-name placeholder "Họ tên"]
[tel* your-phone placeholder "Số điện thoại"]
[email* your-email placeholder "Email"]
[textarea* your-message placeholder "Bạn cần tư vấn mẫu giày, size hoặc chính sách đổi trả?"]
[submit "Gửi liên hệ"]
```

Mail recipient:

```text
hello@kickzone.vn
```

Privacy note trên trang Liên hệ:

```text
Dữ liệu form chỉ dùng để phản hồi yêu cầu tư vấn, không chia sẻ cho bên thứ ba nếu chưa có sự đồng ý.
```

## 8. Users

Tạo 3 tài khoản:

| Username | Password | Role | Mục đích |
|---|---|---|---|
| `admin_kickzone` | `Admin@KickZone2024` | Administrator | Quản trị toàn bộ |
| `editor_kickzone` | `Editor@KickZone2024` | Editor | Đăng bài, quản lý nội dung |
| `customer_test` | `Customer@2024` | Customer | Tài khoản mua hàng mẫu |

Lưu ý khi nộp bài: chỉ đưa tài khoản admin test cho giảng viên nếu site đã deploy.

## 9. Backup, Cache, Security

UpdraftPlus:

- Schedule: Weekly
- Files backup: Weekly
- Database backup: Weekly
- Remote storage: Google Drive nếu có
- Chạy backup thủ công 1 lần và chụp màn hình minh chứng

W3 Total Cache:

- Page Cache: Enable
- Browser Cache: Enable
- Minify CSS/JS: Enable nếu không lỗi giao diện
- Database Cache/Object Cache: có thể để Off trên hosting yếu

Bảo mật cơ bản:

- Không dùng username `admin`
- Mật khẩu admin mạnh
- Chỉ cấp quyền Editor cho người viết bài
- Cập nhật plugin/theme trước khi nộp
- Không public Consumer Secret trong báo cáo nếu website còn hoạt động thật

## 10. Demo Checklist

Trước khi nộp, cần chụp màn hình:

1. Trang chủ KickZone hiển thị đúng.
2. Shop có đủ 8 sản phẩm.
3. Chi tiết 1 sản phẩm có ảnh, giá, mô tả, SKU, stock, Add to cart.
4. Cart và checkout chạy được.
5. Blog có đủ 8 bài.
6. Contact Form 7 submit thành công hoặc hiện notification.
7. Yoast SEO panel có title/meta description.
8. UpdraftPlus có bản backup.
9. W3 Total Cache đã bật cache.
10. WordPress Users có 3 tài khoản với role khác nhau.
11. WooCommerce REST API trả JSON ở endpoint products.
12. Mobile responsive bằng Chrome DevTools.

## 11. Phân biệt Frontend Và Backend Trong Bài Này

| Phần | File/Công cụ | Vai trò |
|---|---|---|
| Frontend demo | `kickzone-frontend.html` | Giao diện mẫu để trình bày layout và nội dung |
| WordPress | Dashboard WordPress thật | CMS quản lý trang, bài viết, media, menu, user |
| Backend | WooCommerce + MySQL + REST API | Lưu sản phẩm, đơn hàng, user và trả JSON API |
| SEO | Yoast SEO + technical SEO | Title, meta, sitemap, schema, alt text, cache |

Khi làm website thật để chấm điểm, frontend HTML chỉ là bản mẫu. Bản nộp chính nên là link WordPress đã deploy hoặc source WordPress + database nếu làm local.
