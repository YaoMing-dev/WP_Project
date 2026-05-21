# KickZone Sneaker Shop - WordPress

KickZone là website bán giày sneaker chạy bằng WordPress + WooCommerce. Repo này chứa source cần thiết để người khác clone về và dựng lại website local giống bản demo hiện tại bằng Docker.

## Yêu cầu

- Docker Desktop đang chạy.
- Port `8080` và `8081` còn trống.
- Không cần cài PHP, MySQL, WordPress hay Composer trên máy host.

## Chạy local lần đầu

```powershell
git clone https://github.com/YaoMing-dev/WP_Project.git
cd WP_Project
docker compose up -d
```

Theo dõi bootstrap:

```powershell
docker logs -f kickzone_wordpress_bootstrap
```

Hoàn tất khi thấy:

```text
KickZone WordPress bootstrap completed.
```

## Truy cập

| URL | Mục đích |
|-----|----------|
| `http://localhost:8080` | Website KickZone |
| `http://localhost:8080/wp-admin` | WordPress Admin |
| `http://localhost:8080/shop` | Trang Shop |
| `http://localhost:8080/gioi-thieu/` | Trang Giới thiệu |
| `http://localhost:8080/lien-he/` | Trang Liên hệ |
| `http://localhost:8081` | phpMyAdmin |

## Tài khoản

Xem đầy đủ trong `accounts.txt`.

| Username | Password | Role |
|----------|----------|------|
| `admin_kickzone` | `Admin@KZ2024!` | Administrator |
| `editor_kickzone` | `Editor@KZ2024!` | Editor |
| `customer_test` | `Customer@2024!` | Customer |

Database phpMyAdmin:

| Field | Value |
|-------|-------|
| Server | `db` hoặc `kickzone_mysql` |
| Username | `root` |
| Password | `root_pass` |
| Database | `kickzone_wp` |

## Không mất dữ liệu local

Lệnh `docker compose up -d` không xóa database volume đang có. Nếu đã nhập thêm sản phẩm/bài viết/order trên local, dữ liệu vẫn nằm trong Docker volume `kickzone_db`.

Chỉ dùng lệnh reset bên dưới khi muốn xóa sạch database local và dựng lại demo từ đầu:

```powershell
docker compose down -v --remove-orphans
docker compose up -d --force-recreate
```

Nếu cần giữ dữ liệu thật trước khi reset, export trong WordPress Admin hoặc phpMyAdmin trước.

## Cấu trúc source chính

```text
.
├── docker-compose.yml
├── bootstrap-wordpress.sh
├── wp-apply-kickzone.php
├── wp-run-with-errors.php
├── wp-run-kickzone.sh
├── accounts.txt
└── wp-content/
    ├── themes/
    │   └── kickzone-child/
    │       ├── style.css
    │       ├── functions.php
    │       └── kickzone-logo.svg
    └── mu-plugins/
        └── kickzone-ui.php
```

## Nội dung bootstrap tạo ra

- Trang: Trang chủ, Shop, Giới thiệu, Liên hệ, Blog, Chính sách bảo mật.
- Sản phẩm WooCommerce: 8 mẫu giày sneaker/running/lifestyle.
- Blog: 8 bài về sneaker, review, vệ sinh giày, xu hướng màu và cách chọn giày.
- Menu chính, footer menu, logo, theme settings, SEO metadata cơ bản.
- Contact Form 7: form liên hệ và newsletter.

## Theme

- Parent theme: Astra, cài tự động bằng bootstrap.
- Child theme: `wp-content/themes/kickzone-child`.
- Font: `Space Grotesk` cho heading/brand, `Manrope` cho body/UI/price.
- Màu chính: cam `#FF4713`, đen `#0A0A0A`, trắng `#FFFFFF`.
- Header được khóa đồng bộ để khi chuyển page logo/menu không đổi layout.

## Plugins tự cài

| Plugin | Mục đích |
|--------|----------|
| WooCommerce | Bán hàng |
| Yoast SEO | SEO metadata |
| Contact Form 7 | Form liên hệ/newsletter |
| UpdraftPlus | Backup |
| W3 Total Cache | Cache |

## Chạy lại script nội dung

Khi cần apply lại pages/products/theme settings vào WordPress đang chạy:

```powershell
docker compose run --rm --entrypoint sh wordpress_bootstrap -c "cd /var/www/html && wp --allow-root eval-file /work/wp-run-with-errors.php"
docker compose run --rm --entrypoint sh wordpress_bootstrap -c "cd /var/www/html && wp --allow-root cache flush"
```

Hoặc dùng helper:

```powershell
docker compose run --rm --entrypoint sh wordpress_bootstrap /work/wp-run-kickzone.sh
```

## Ghi chú

- Repo không commit `wp-content/uploads/` và không commit database dump. Ảnh demo được tải lại khi bootstrap chạy.
- Repo không commit cache/runtime WordPress. Các thư mục đó được tạo trong Docker volume.
- Người clone chỉ cần Docker và tài khoản trong `accounts.txt` để login.
