# Blossom Chic Việt - WordPress Template Demo

Đây là bộ WordPress local dùng template chính thức **Blossom Chic 1.1.3**. Người clone repo chỉ cần chạy Docker Compose là có website WordPress với theme Blossom Chic, ảnh demo của theme và nội dung UI tiếng Việt.

## Clone Và Chạy

Yêu cầu:

- Docker Desktop đã cài và đang chạy.
- PowerShell hoặc terminal tại thư mục muốn lưu project.

```powershell
git clone https://github.com/YaoMing-dev/WP_Project.git
cd WP_Project
docker compose -f kickzone-deliverables/docker-compose.wordpress.yml up -d
```

Lần đầu chạy, đợi khoảng 1-3 phút để service `wordpress_bootstrap`:

- Import database WordPress.
- Cài parent theme `Blossom Feminine`.
- Kích hoạt child theme `Blossom Chic`.
- Cài plugin cần thiết.
- Áp nội dung demo tiếng Việt.
- Mount ảnh demo và must-use plugin Việt hóa UI.

Mở website:

```text
http://localhost:8080
```

Admin:

```text
http://localhost:8080/wp-admin
```

Tài khoản:

```text
Username: admin_blossom
Password: Admin@Blossom2024
```

phpMyAdmin:

```text
http://localhost:8081
```

## Reset Chạy Lại Từ Đầu

Nếu đã chạy trước đó và muốn dựng lại sạch:

```powershell
docker compose -f kickzone-deliverables/docker-compose.wordpress.yml down -v
docker compose -f kickzone-deliverables/docker-compose.wordpress.yml up -d
```

Xem log bootstrap:

```powershell
docker logs kickzone_wordpress_bootstrap
```

Khi thấy dòng dưới là hoàn tất:

```text
Blossom Chic Vietnamese WordPress bootstrap completed.
```

## Nội Dung Website Sau Khi Chạy

Website sẽ hiển thị theo template Blossom Chic:

- Trang chủ: giới thiệu blog phong cách sống.
- Giới thiệu: mô tả website demo Blossom Chic Việt.
- Blog: các bài viết demo tiếng Việt.
- Liên hệ: form Contact Form 7 tiếng Việt.
- Menu chính tiếng Việt.
- Các chuỗi UI thường gặp như tìm kiếm, bình luận, đọc tiếp, danh mục, thẻ, điều hướng được Việt hóa.

Nội dung cũ về shop/sneaker được dọn khỏi UI trong quá trình bootstrap. Giao diện chính dùng Blossom Chic dạng blog/lifestyle.

## Công Nghệ

| Hạng mục | Công nghệ |
|---|---|
| CMS | WordPress |
| Runtime | Docker Compose |
| Database | MySQL 8 |
| Theme chính | Blossom Chic 1.1.3 |
| Parent theme | Blossom Feminine |
| Form | Contact Form 7 |
| SEO | Yoast SEO |
| Backup | UpdraftPlus |
| Cache | W3 Total Cache |
| Việt hóa UI | Must-use plugin `blossom-vietnamese-ui.php` |

## Cấu Trúc Quan Trọng

```text
kickzone-deliverables/
├── docker-compose.wordpress.yml
├── bootstrap-wordpress.sh
├── kickzone-local-db.sql
├── wp-apply-blossom-chic.php
└── wp-content/
    ├── themes/
    │   └── blossom-chic/
    ├── mu-plugins/
    │   └── blossom-vietnamese-ui.php
    └── uploads/
```

## SEO Và Ngôn Ngữ

- WordPress language: Vietnamese.
- `html lang="vi"`.
- Yoast SEO active.
- Sitemap: `http://localhost:8080/sitemap_index.xml`.
- Slug trang dùng tiếng Việt không dấu.
- Nội dung hiển thị trên UI dùng tiếng Việt có dấu.

## Ghi Chú

- Theme Blossom Chic nằm trong repo tại `kickzone-deliverables/wp-content/themes/blossom-chic`.
- Parent theme Blossom Feminine được tải tự động khi chạy Docker.
- Ảnh demo sử dụng ảnh có sẵn trong theme Blossom Chic.
- Đây là bản local phục vụ demo/nộp bài WordPress, không dùng cho production trực tiếp.
