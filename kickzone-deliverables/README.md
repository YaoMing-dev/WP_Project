# Blossom Chic Việt - Deliverables

Thư mục này chứa bộ Docker/WordPress để người clone repo chạy được template Blossom Chic Việt hóa.

## Chạy local

Từ root repo:

```powershell
docker compose -f kickzone-deliverables/docker-compose.wordpress.yml up -d
```

Mở:

```text
Website:    http://localhost:8080
Admin:      http://localhost:8080/wp-admin
phpMyAdmin: http://localhost:8081
```

Admin:

```text
admin_blossom
Admin@Blossom2024
```

## Reset sạch

```powershell
docker compose -f kickzone-deliverables/docker-compose.wordpress.yml down -v
docker compose -f kickzone-deliverables/docker-compose.wordpress.yml up -d
```

## Bootstrap làm gì?

Service `wordpress_bootstrap` sẽ:

- Đợi WordPress và database sẵn sàng.
- Cài parent theme `blossom-feminine`.
- Kích hoạt child theme `blossom-chic` từ repo.
- Cài plugin: Yoast SEO, Contact Form 7, UpdraftPlus, W3 Total Cache, Elementor.
- Kích hoạt tiếng Việt.
- Chạy `wp-apply-blossom-chic.php` để tạo nội dung demo Blossom Chic tiếng Việt và dọn nội dung shop cũ khỏi UI.

## File quan trọng

| File/thư mục | Vai trò |
|---|---|
| `docker-compose.wordpress.yml` | Chạy WordPress, MySQL, phpMyAdmin và bootstrap |
| `kickzone-local-db.sql` | Database WordPress nền để import tự động |
| `bootstrap-wordpress.sh` | Script bootstrap trong container WP-CLI |
| `wp-apply-blossom-chic.php` | Áp nội dung demo Blossom Chic tiếng Việt |
| `wp-content/themes/blossom-chic` | Theme Blossom Chic 1.1.3 |
| `wp-content/mu-plugins/blossom-vietnamese-ui.php` | Việt hóa chuỗi UI thường gặp |
| `wp-content/uploads` | Media/ảnh demo đã export |

## Kiểm tra

```powershell
docker logs kickzone_wordpress_bootstrap
```

Hoàn tất khi thấy:

```text
Blossom Chic Vietnamese WordPress bootstrap completed.
```

Sau đó kiểm tra:

```text
http://localhost:8080
http://localhost:8080/blog
http://localhost:8080/lien-he
http://localhost:8080/sitemap_index.xml
```
