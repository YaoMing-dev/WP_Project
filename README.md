# KickZone - WordPress Sneaker Shop

KickZone là website WordPress bán giày thể thao/sneaker dành cho giới trẻ, phong cách năng động lấy cảm hứng từ các website sneaker hiện đại. Dự án dùng WordPress local bằng Docker, WooCommerce làm backend bán hàng, Yoast SEO cho SEO, Contact Form 7 cho form liên hệ và Astra/Elementor cho giao diện.

## Demo Local

Sau khi chạy Docker:

```text
Website:    http://localhost:8080
Admin:      http://localhost:8080/wp-admin
phpMyAdmin: http://localhost:8081
```

Tài khoản admin:

```text
Username: admin_kickzone
Password: Admin@KickZone2024
```

Tài khoản mẫu:

```text
editor_kickzone / Editor@KickZone2024
customer_test / Customer@2024
```

## Tính Năng Đã Hoàn Thành

- WordPress local chạy bằng Docker Compose.
- Theme Astra, Elementor Free.
- WooCommerce với 8 sản phẩm sneaker.
- 5 trang chính: Trang chủ, Giới thiệu, Shop, Blog, Liên hệ.
- 8 bài blog tiếng Việt.
- Giao diện UI/UX style sneaker trẻ trung, có hero full-width, product card, sale banner, contact section.
- Nội dung tiếng Việt rõ ràng, có dấu.
- Yoast SEO: title/meta description, sitemap.
- Contact Form 7: form liên hệ.
- UpdraftPlus: plugin backup.
- W3 Total Cache: plugin cache.
- REST API WooCommerce trả JSON sản phẩm.
- 3 user mẫu với role khác nhau.

## Công Nghệ

| Hạng mục | Công nghệ |
|---|---|
| CMS | WordPress |
| Database | MySQL 8 |
| E-commerce | WooCommerce |
| Theme | Astra |
| Page Builder | Elementor Free |
| SEO | Yoast SEO |
| Form | Contact Form 7 |
| Backup | UpdraftPlus |
| Cache | W3 Total Cache |
| Local runtime | Docker Compose |

## Cách Chạy Local

Yêu cầu:

- Docker Desktop đã cài và đang chạy.
- PowerShell tại thư mục project.

Chạy WordPress:

```powershell
docker compose -f kickzone-deliverables/docker-compose.wordpress.yml up -d
```

Mở website:

```text
http://localhost:8080
```

Dừng container:

```powershell
docker compose -f kickzone-deliverables/docker-compose.wordpress.yml down
```

## Khôi Phục Database

File database đã export:

```text
kickzone-deliverables/kickzone-local-db.sql
```

Nếu cần import lại database:

```powershell
Get-Content kickzone-deliverables/kickzone-local-db.sql | docker exec -i kickzone_mysql mysql -uroot -proot_pass kickzone_wp
```

Sau khi import, mở:

```text
http://localhost:8080
```

## REST API Backend

WooCommerce REST API local:

```text
http://localhost:8080/wp-json/wc/v3/products?consumer_key=ck_kickzone_local_1234567890abcdef1234567890abcdef&consumer_secret=cs_kickzone_local_1234567890abcdef1234567890abcdef
```

WordPress Posts API:

```text
http://localhost:8080/wp-json/wp/v2/posts
```

Sitemap SEO:

```text
http://localhost:8080/sitemap_index.xml
```

Ghi chú: API key trên chỉ dùng cho demo local. Khi deploy hosting thật, cần tạo Consumer Key/Secret mới trong WooCommerce và không public secret thật.

## Cấu Trúc Project

```text
.
├── README.md
├── kickzone-frontend.html
├── kickzone-plan.md
├── kickzone-wordpress-backend-seo.md
├── requirement.txt
└── kickzone-deliverables/
    ├── README.md
    ├── docker-compose.wordpress.yml
    ├── kickzone-local-db.sql
    ├── woocommerce-products.csv
    ├── pages-content.md
    ├── blog-posts.md
    ├── report.md
    ├── demo-checklist.md
    ├── api-test.http
    ├── wp-seed-kickzone.php
    ├── wp-implement-design.php
    ├── wp-import-product-images.php
    ├── wp-create-api-key.php
    └── kickzone-local-api-auth.php
```

## Nội Dung Website

### Trang Chính

- Trang chủ: hero, danh mục nổi bật, sản phẩm bán chạy, sale banner, lý do chọn KickZone, newsletter.
- Giới thiệu: câu chuyện thương hiệu, sứ mệnh, tầm nhìn, cam kết.
- Shop: danh sách sản phẩm WooCommerce.
- Blog: 8 bài viết về sneaker, review và hướng dẫn chăm sóc giày.
- Liên hệ: form, địa chỉ, hotline, email, ghi chú dữ liệu.

### Sản Phẩm

Website có 8 sản phẩm:

- Nike Air Force 1 White
- Adidas Ultra Boost 22
- New Balance 574 Grey
- Puma RS-X Reinvention
- Converse Chuck Taylor All Star
- Vans Old Skool Black/White
- Nike Air Max 270
- Adidas Stan Smith White

Mỗi sản phẩm có SKU, giá thường, giá sale, stock, category, tag, size, màu sắc, thumbnail và alt text.

## SEO

Đã cấu hình:

- Yoast SEO active.
- Title/meta description cho 5 trang chính.
- XML sitemap.
- Slug không dấu.
- Ảnh sản phẩm có alt text.
- WordPress language: Vietnamese.
- `html lang="vi"`.

Các title SEO chính:

| Trang | SEO Title |
|---|---|
| Trang chủ | KickZone - Sneaker Chính Hãng Giá Tốt |
| Shop | Shop Giày Thể Thao \| KickZone |
| Giới thiệu | Về Chúng Tôi \| KickZone Sneaker Store |
| Blog | Blog Sneaker, Review Và Hướng Dẫn \| KickZone |
| Liên hệ | Liên Hệ KickZone \| Tư Vấn Sneaker Chính Hãng |

## Ghi Chú Bản Quyền

- Ảnh dùng từ Unsplash cho mục đích học tập/demo.
- Nội dung mô tả sản phẩm và blog được viết riêng cho KickZone.
- Không sử dụng logo, slogan hoặc tài sản thương hiệu chính thức của Nike/Adidas.
- Footer có copyright và ghi chú thu thập dữ liệu form.

## Trạng Thái Kiểm Tra

Đã kiểm tra local:

- `http://localhost:8080` trả `200`.
- `http://localhost:8080/shop` trả `200`, có sản phẩm và ảnh.
- `http://localhost:8080/lien-he` trả `200`, form tiếng Việt OK.
- `http://localhost:8080/sitemap_index.xml` trả `200`.
- WooCommerce REST API products trả `200`.
- Có đủ 8 sản phẩm, 8 bài blog, 8 product thumbnails.
