# Blossom Chic Việt - WordPress Template

Repo này chỉ dùng template WordPress **Blossom Chic 1.1.3**. Không dùng source frontend/backend cũ, không import database cũ.

Người clone về chỉ cần chạy Docker Compose, hệ thống sẽ tự cài WordPress trắng, cài parent theme Blossom Feminine, kích hoạt Blossom Chic và tạo nội dung demo tiếng Việt.

## Chạy local

```powershell
git clone https://github.com/YaoMing-dev/WP_Project.git
cd WP_Project
docker compose up -d
```

Đợi bootstrap hoàn tất:

```powershell
docker logs -f blossom_wordpress_bootstrap
```

Hoàn tất khi thấy:

```text
Blossom Chic Vietnamese WordPress bootstrap completed.
```

Mở website:

```text
http://localhost:8080
```

Admin:

```text
http://localhost:8080/wp-admin
```

```text
Username: admin_blossom
Password: Admin@Blossom2024
```

phpMyAdmin:

```text
http://localhost:8081
```

## Reset sạch, không cache

Nếu muốn xóa sạch container, database volume và dựng lại từ đầu:

```powershell
docker compose down -v --remove-orphans
docker compose pull
docker compose up -d --force-recreate
```

## Cấu trúc chính

```text
.
├── docker-compose.yml
├── bootstrap-wordpress.sh
├── wp-apply-blossom-chic.php
└── wp-content/
    ├── themes/
    │   └── blossom-chic/
    └── mu-plugins/
        └── blossom-vietnamese-ui.php
```

## Nội dung sau khi chạy

- Theme: Blossom Chic 1.1.3.
- Parent theme: Blossom Feminine.
- Giao diện blog/lifestyle theo template Blossom Chic.
- Ảnh dùng từ thư mục ảnh có sẵn của theme.
- Menu tiếng Việt.
- Trang: Trang chủ, Giới thiệu, Blog, Liên hệ.
- Bài viết demo tiếng Việt.
- Form liên hệ tiếng Việt.
- UI thường gặp được Việt hóa: Đọc tiếp, Tìm kiếm, Bình luận, Danh mục, Thẻ, Điều hướng.

## Plugin tự cài

- Yoast SEO
- Contact Form 7
- UpdraftPlus
- W3 Total Cache
- Elementor

## Ghi chú

Repo này không chứa database dump cũ. Mỗi lần reset volume, WordPress sẽ được cài mới và script bootstrap sẽ tạo lại nội dung demo Blossom Chic tiếng Việt.
