# KickZone — WordPress Sneaker Shop

## Context

Bài tập lớn môn WordPress (10 điểm). Yêu cầu xây dựng website bán hàng cơ bản theo loại 3 (website bán hàng / giới thiệu sản phẩm). Mục tiêu đạt 10/10 bằng cách deploy lên hosting thật (+1.5đ). Nội dung: shop giày thể thao thương hiệu "KickZone".

**Ngày lập kế hoạch**: 2026-05-19  
**File yêu cầu**: `D:\Dev\Thanh\requirement.txt`

---

## Architecture

```
KickZone WordPress Site
├── Hosting: InfinityFree / Hostinger Free (SSL miễn phí)
├── WordPress 6.x
├── Theme: Astra (free)
├── Page Builder: Elementor Free
├── E-commerce: WooCommerce
└── Plugins:
    ├── Yoast SEO          ← SEO (bắt buộc)
    ├── Contact Form 7     ← Form liên hệ (bắt buộc)
    ├── UpdraftPlus        ← Backup (bắt buộc)
    ├── W3 Total Cache     ← Cache / Performance
    └── WooCommerce        ← E-commerce (chứa luôn REST API)
```

**Backend API** (WooCommerce REST API — có sẵn, không cần code):
- `GET /wp-json/wc/v3/products` — danh sách sản phẩm
- `GET /wp-json/wc/v3/products/{id}` — chi tiết sản phẩm
- `GET /wp-json/wc/v3/products/categories` — danh mục
- `GET /wp-json/wp/v2/posts` — bài viết blog
- `GET /wp-json/wp/v2/pages` — trang tĩnh

Để bật API: WooCommerce → Settings → Advanced → REST API → tạo Consumer Key/Secret.

---

## Site Structure (5 trang tĩnh bắt buộc)

| Trang | Slug | Nội dung |
|-------|------|----------|
| Trang chủ | `/` | Hero banner full-width, "Featured Products" grid (4 sản phẩm), category section (Sneaker / Running / Lifestyle), newsletter signup form |
| Giới thiệu | `/gioi-thieu` | Brand story KickZone, mission/vision, "Tại sao chọn chúng tôi" (3 icon cards), ảnh team |
| Shop | `/shop` | WooCommerce catalog, filter sidebar (category/price/brand), product grid 3 cột |
| Blog | `/blog` | Grid bài viết, sidebar (categories/tags/search), 8+ bài |
| Liên hệ | `/lien-he` | Contact Form 7, địa chỉ, số ĐT, email, Google Maps embed |

**Trang WooCommerce tự tạo thêm** (không tính vào 5 trang bắt buộc):
- `/gio-hang` (Cart)
- `/thanh-toan` (Checkout)
- `/tai-khoan` (My Account)

---

## Products (6 sản phẩm tối thiểu)

| # | Tên sản phẩm | Giá | Category | Tags |
|---|-------------|-----|----------|------|
| 1 | Nike Air Force 1 White | 2,800,000₫ | Sneaker | nike, classic, white |
| 2 | Adidas Ultra Boost 22 | 3,200,000₫ | Running | adidas, boost, running |
| 3 | New Balance 574 Grey | 2,500,000₫ | Lifestyle | newbalance, casual |
| 4 | Puma RS-X Reinvention | 2,200,000₫ | Sneaker | puma, chunky, colorful |
| 5 | Converse Chuck Taylor All Star | 1,800,000₫ | Classic | converse, canvas, unisex |
| 6 | Vans Old Skool Black/White | 1,900,000₫ | Skate | vans, skate, classic |
| 7 | Nike Air Max 270 | 3,500,000₫ | Running | nike, airmax, cushion |
| 8 | Adidas Stan Smith White | 2,400,000₫ | Lifestyle | adidas, minimalist |

Mỗi sản phẩm cần: tên, mô tả dài (100+ chữ), giá thường + giá sale, 3-4 ảnh, SKU, stock, category, tags, thuộc tính (size: 38-44, màu sắc).

---

## Blog Posts (8 bài viết tối thiểu)

1. "Top 5 Sneaker Nam Xu Hướng 2024" — category: Xu hướng
2. "Cách Vệ Sinh Giày Trắng Tại Nhà Đúng Cách" — category: Hướng dẫn
3. "Nike vs Adidas: Thương Hiệu Nào Tốt Hơn?" — category: Review
4. "Giày Running vs Giày Lifestyle: Khác Nhau Thế Nào?" — category: Kiến thức
5. "Bộ Sưu Tập Sneaker Mùa Hè 2024 Đáng Mong Chờ" — category: Xu hướng
6. "Cách Bảo Quản Sneaker Bền Đẹp Lâu Dài" — category: Hướng dẫn
7. "Review Nike Air Force 1: Đôi Giày Kinh Điển Mọi Thời Đại" — category: Review
8. "Xu Hướng Màu Giày Hot Nhất Năm 2024" — category: Xu hướng

---

## User Accounts (3 tài khoản tối thiểu)

| Username | Mật khẩu | Role | Mục đích |
|----------|----------|------|----------|
| `admin_kickzone` | `Admin@KickZone2024` | Administrator | Quản trị toàn bộ |
| `editor_kickzone` | `Editor@KickZone2024` | Editor | Đăng bài, quản lý nội dung |
| `customer_test` | `Customer@2024` | Customer | Tài khoản mua hàng mẫu |

---

## SEO Implementation

### Plugin: Yoast SEO
1. **General Settings**: Website name "KickZone", separator " | ", organization schema
2. **Sitemap**: Enable XML sitemap → submit lên Google Search Console
3. **Social**: Điền Facebook/Instagram URL, upload social image mặc định

### On-page SEO cho từng trang

| Trang | Title Tag | Meta Description |
|-------|-----------|-----------------|
| Trang chủ | `KickZone - Sneaker Chính Hãng Giá Tốt` | `Mua giày thể thao chính hãng Nike, Adidas, New Balance tại KickZone. Giao hàng nhanh, đổi trả dễ dàng.` |
| Shop | `Shop Giày Thể Thao \| KickZone` | `Khám phá bộ sưu tập sneaker đa dạng với giá từ 1.8 triệu. Hàng chính hãng, nhiều size.` |
| Giới thiệu | `Về Chúng Tôi \| KickZone Sneaker Store` | `KickZone - cửa hàng sneaker chuyên biệt với sứ mệnh mang đến giày thể thao chất lượng cao.` |

### Technical SEO
- Permalink: Cài đặt → Permalink → **Post name** (`/%postname%/`)
- Tất cả ảnh sản phẩm: điền Alt Text mô tả (VD: "Nike Air Force 1 White size 42")
- W3 Total Cache: bật Page Cache + Browser Cache + Minify CSS/JS
- Hosting Hostinger/InfinityFree: SSL bật sẵn → redirect HTTP → HTTPS

---

## Thiết kế giao diện (Astra + Elementor)

### Color Palette
- Primary: `#FF4500` (cam đỏ — Nike vibe)
- Secondary: `#111111` (đen)
- Background: `#FFFFFF` (trắng)
- Accent: `#F5F5F5` (xám nhạt)

### Typography
- Heading: **Montserrat** Bold
- Body: **Open Sans** Regular

### Astra Global Settings
- Header: Logo trái + Navigation + Cart icon phải
- Footer: 3 cột (Về KickZone | Liên kết nhanh | Liên hệ) + copyright bar

### Trang chủ Layout (Elementor)
```
[HERO SECTION]
- Full-width banner: "STEP INTO THE FUTURE" + CTA "Shop Now"
- Background: ảnh sneaker đẹp (Unsplash free)

[CATEGORY SECTION]
- 3 cột: Sneaker | Running | Classic
- Mỗi ô: ảnh + tên category + link

[FEATURED PRODUCTS]
- WooCommerce shortcode: [featured_products limit="4" columns="4"]

[SALE BANNER]
- Full-width: "SALE 20% - Giảm giá toàn bộ giày Running"

[BLOG PREVIEW]
- 3 bài viết mới nhất dạng card

[NEWSLETTER FORM]
- Contact Form 7 embedded
```

---

## Triển khai (Deployment)

### Hosting: InfinityFree (free, phù hợp bài tập)
1. Đăng ký tại infinityfree.net
2. Tạo account → Create Hosting Account → chọn subdomain `.rf.gd` hoặc `.epizy.com`
3. Vào Control Panel → Softaculous → WordPress → Install
4. Điền: Site Name "KickZone", Admin Email, Username/Password admin

### Hoặc Hostinger (100 ngày free trial với student email)
1. Đăng ký hostinger.com
2. Chọn plan "Free" hoặc shared hosting trial
3. Cài WordPress qua hPanel → WordPress → Install

### Sau khi cài WordPress:
- Vào `yoursite.com/wp-admin`
- Settings → General: đặt Site Title, Tagline, upload Logo
- Settings → Permalink: chọn "Post name"
- Appearance → Themes: Install & activate Astra
- Plugins → Add New: cài 5 plugins theo danh sách

---

## Thứ tự thực hiện (Implementation Order)

### Phase 1 — Setup (1-2 giờ)
- [ ] Đăng ký hosting, cài WordPress
- [ ] Cài Astra theme, activate
- [ ] Cài Elementor Free
- [ ] Cài WooCommerce, chạy Setup Wizard
- [ ] Cài Yoast SEO, Contact Form 7, UpdraftPlus, W3 Total Cache

### Phase 2 — Cấu hình cơ bản (1 giờ)
- [ ] Upload logo + favicon (dùng Canva tạo)
- [ ] Thiết lập Astra: màu sắc, font, header/footer layout
- [ ] Cấu hình Yoast SEO: general settings, sitemap on
- [ ] Cấu hình WooCommerce: tiền tệ VND, địa chỉ cửa hàng

### Phase 3 — Xây dựng trang (2-3 giờ)
- [ ] Trang chủ bằng Elementor (theo layout ở trên)
- [ ] Trang Giới thiệu
- [ ] Trang Liên hệ (Contact Form 7 embedded)
- [ ] Trang Blog (dùng layout mặc định của WordPress)
- [ ] Trang Shop (WooCommerce tự tạo, customize sidebar)

### Phase 4 — Thêm sản phẩm và nội dung (2-3 giờ)
- [ ] Thêm 8 sản phẩm với đầy đủ thông tin, ảnh, category, tags
- [ ] Viết 8 bài blog (có thể dùng ChatGPT hỗ trợ nội dung, chỉnh lại)
- [ ] Phân loại: tạo categories và tags rõ ràng

### Phase 5 — SEO On-page (1 giờ)
- [ ] Điền Yoast meta title + description cho mỗi trang
- [ ] Điền alt text cho tất cả ảnh sản phẩm
- [ ] Kích hoạt XML sitemap
- [ ] Submit sitemap lên Google Search Console (nếu có domain thật)

### Phase 6 — Users, Backup & Test (1 giờ)
- [ ] Tạo 3 tài khoản user với roles khác nhau
- [ ] Cấu hình UpdraftPlus: lịch backup hàng tuần, lưu về Google Drive
- [ ] W3 Total Cache: bật Page Cache
- [ ] Kiểm tra mobile responsiveness (Chrome DevTools)
- [ ] Kiểm tra tất cả links, forms hoạt động

### Phase 7 — WooCommerce REST API test (30 phút)
- [ ] Tạo Consumer Key/Secret trong WooCommerce
- [ ] Test endpoint: `GET /wp-json/wc/v3/products` trả về JSON
- [ ] Chụp màn hình API response để đưa vào báo cáo

### Phase 8 — Báo cáo & Demo (2 giờ)
- [ ] Viết báo cáo bài tập lớn (theo mục III trong requirement.txt)
- [ ] Tạo slide demo (Google Slides / PowerPoint)
- [ ] Bảng phân công công việc nhóm
- [ ] Chụp màn hình minh chứng các thao tác admin

---

## Verification / Testing

### Checklist trước khi nộp

**WordPress Core:**
- [ ] Truy cập `yoursite.com` → trang chủ hiển thị đúng
- [ ] `yoursite.com/wp-admin` → đăng nhập được bằng 3 tài khoản khác nhau
- [ ] Mobile view: Chrome → F12 → chọn iPhone → giao diện responsive

**WooCommerce:**
- [ ] `yoursite.com/shop` → hiển thị 8+ sản phẩm
- [ ] Click 1 sản phẩm → trang chi tiết có ảnh, mô tả, giá, nút "Add to cart"
- [ ] Add to cart → xem giỏ hàng → checkout flow hoạt động

**REST API:**
- [ ] Truy cập `yoursite.com/wp-json/wc/v3/products?consumer_key=ck_xxx&consumer_secret=cs_xxx`
- [ ] Response là JSON array chứa products

**SEO:**
- [ ] `yoursite.com/sitemap_index.xml` → sitemap hiển thị
- [ ] View source trang chủ → tìm `<meta name="description"` → có nội dung
- [ ] Google PageSpeed Insights → nhập URL → score ≥ 70 (mobile)

**Plugins (minh chứng cấu hình):**
- [ ] Contact Form 7: submit form → email về inbox (hoặc chụp màn hình notification)
- [ ] UpdraftPlus: chạy backup 1 lần → file backup xuất hiện
- [ ] Yoast SEO: mở 1 trang → thấy SEO panel phía dưới editor
- [ ] W3 Total Cache: Settings → Status → Cache types enabled

**Nội dung:**
- [ ] Đếm: ≥5 pages, ≥8 products, ≥8 blog posts
- [ ] Mỗi blog post: ≥300 chữ, có ảnh featured
- [ ] Footer có: copyright, địa chỉ liên hệ, lưu ý thu thập dữ liệu (GDPR note)

---

## Điểm số ước tính

| Tiêu chí | Mô tả | Điểm tối đa | Ước tính |
|----------|-------|-------------|---------|
| 1 | Phân tích đề tài, cấu trúc hợp lý | 1.0 | 1.0 |
| 2 | Cài đặt WordPress đúng | 1.0 | 1.0 |
| 3 | Tùy chỉnh giao diện, menu, header/footer | 1.5 | 1.5 |
| 4 | Plugin cài đặt và sử dụng hiệu quả | 1.0 | 1.0 |
| 5 | Xây dựng và quản trị nội dung | 1.5 | 1.5 |
| 6 | Quản lý user và thao tác admin | 1.0 | 1.0 |
| 7 | Bản quyền, bảo mật cơ bản | 0.5 | 0.5 |
| 8 | Báo cáo, demo, phân công nhóm | 1.0 | 1.0 |
| 9 | Deploy lên hosting/Internet | 1.5 | 1.5 |
| **Tổng** | | **10.0** | **10.0** |

---

## Lưu ý bản quyền (tiêu chí 7)

- Ảnh: dùng từ **Unsplash.com** (free, no attribution required) hoặc **Pexels.com**
- Mô tả sản phẩm: tự viết, không copy nguyên văn từ Nike/Adidas
- Footer phải có: `© 2024 KickZone. All rights reserved.`
- Thêm trang `/chinh-sach-bao-mat` hoặc note nhỏ trong Contact page về thu thập dữ liệu form
