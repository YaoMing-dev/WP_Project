# KickZone Deliverables

Bo file nay dung de hoan thien bai tap lon WordPress cho shop sneaker KickZone.

## File chinh

| File | Muc dich |
|---|---|
| `../kickzone-frontend.html` | Frontend demo tinh, mo truc tiep bang trinh duyet |
| `../kickzone-wordpress-backend-seo.md` | Huong dan WordPress, backend WooCommerce REST API va SEO |
| `woocommerce-products.csv` | Du lieu san pham de import vao WooCommerce |
| `pages-content.md` | Noi dung 5 trang tinh |
| `blog-posts.md` | Noi dung 8 bai blog |
| `report.md` | Bao cao bai tap lon |
| `demo-checklist.md` | Checklist chup minh chung demo |
| `api-test.http` | Mau test WooCommerce REST API |

## Thu tu lam tren WordPress

1. Cai WordPress tren hosting hoac localhost.
2. Cai theme Astra va plugin Elementor, WooCommerce, Yoast SEO, Contact Form 7, UpdraftPlus, W3 Total Cache.
3. Cai permalink `Post name`.
4. Tao 5 trang: Trang chu, Gioi thieu, Shop, Blog, Lien he.
5. Import `woocommerce-products.csv` vao WooCommerce.
6. Tao 8 bai blog tu `blog-posts.md`.
7. Dien SEO title/meta description theo `kickzone-wordpress-backend-seo.md`.
8. Tao 3 user: admin, editor, customer.
9. Bat WooCommerce REST API va test bang `api-test.http`.
10. Chup minh chung theo `demo-checklist.md`.

## Chay local bang Docker neu khong co hosting

Can cai Docker Desktop truoc.

Mo Docker Desktop va doi den khi trang thai hien `Docker Desktop is running`, sau do chay lenh trong PowerShell tai thu muc `D:\Dev\Thanh`:

```powershell
docker compose -f kickzone-deliverables/docker-compose.wordpress.yml up -d
```

Sau do mo:

- WordPress: `http://localhost:8080`
- phpMyAdmin: `http://localhost:8081`

Cau hinh WordPress lan dau:

- Site Title: `KickZone`
- Username: `admin_kickzone`
- Password: `Admin@KickZone2024`
- Email: email cua nhom

Sau khi cai xong, vao Dashboard va lam tiep cac buoc plugin, theme, import san pham, SEO.

Neu gap loi:

```text
failed to connect to the docker API
```

Nghia la Docker Desktop chua chay. Hay mo Docker Desktop truoc roi chay lai lenh.

Neu gap canh bao:

```text
Error loading config file: C:\Users\...\ .docker\config.json: Access is denied
```

Hay mo Docker Desktop mot lan bang quyen user hien tai, hoac xoa/doi quyen file config Docker trong thu muc user. Canh bao nay khong phai loi WordPress, nhung co the lam Docker CLI khong doc duoc cau hinh.

## Luu y khi nop

- Neu deploy online: nop link website, tai khoan admin test, bao cao va slide.
- Neu lam local: nop source WordPress, database `.sql`, huong dan chay va tai khoan admin.
- Khong public Consumer Secret WooCommerce neu website con hoat dong that.

## Trang thai local da cai

WordPress local Docker da duoc khoi tao tai:

- Website: `http://localhost:8080`
- Admin: `http://localhost:8080/wp-admin`
- phpMyAdmin: `http://localhost:8081`

Tai khoan admin:

```text
Username: admin_kickzone
Password: Admin@KickZone2024
```

Tai khoan mau:

```text
editor_kickzone / Editor@KickZone2024
customer_test / Customer@2024
```

Da cai:

- Theme: Astra
- Plugins: WooCommerce, Yoast SEO, Contact Form 7, UpdraftPlus, W3 Total Cache, Elementor
- Noi dung: 5 trang chinh, 8 blog posts, 8 WooCommerce products, menu, user mau
- UI/UX: da ap dung branding KickZone phong cach sneaker tre trung, hero full-width, product cards, sale banner, contact section va CSS responsive
- Media: 8 san pham da co thumbnail va alt text
- Ngon ngu: WordPress da kich hoat tieng Viet, giao dien nguoi dung dung chu tieng Viet co dau
- SEO: sitemap Yoast tai `http://localhost:8080/sitemap_index.xml`

Local WooCommerce REST API:

```text
http://localhost:8080/wp-json/wc/v3/products?consumer_key=ck_kickzone_local_1234567890abcdef1234567890abcdef&consumer_secret=cs_kickzone_local_1234567890abcdef1234567890abcdef
```

Ghi chu: local Docker dang chay HTTP, nen da them mu-plugin `kickzone-local-api-auth.php` chi phuc vu demo local REST API. Khi deploy hosting that co HTTPS, hay tao key that trong WooCommerce va khong dung key demo nay.
