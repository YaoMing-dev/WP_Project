# Bao cao bai tap lon WordPress - KickZone

## 1. Gioi thieu de tai

KickZone la website ban hang co ban va gioi thieu san pham trong linh vuc sneaker. Website tap trung vao cac dong giay the thao chinh hang nhu Nike, Adidas, New Balance, Puma, Converse va Vans. Doi tuong nguoi dung chinh la sinh vien, dan van phong va nguoi yeu phong cach streetwear.

De tai thuoc nhom website ban hang co ban / gioi thieu san pham. Website duoc xay dung bang WordPress, su dung WooCommerce de quan ly san pham, gio hang va checkout. Ngoai ra website co blog de dang bai SEO, form lien he de tiep nhan yeu cau tu van va cac plugin ho tro backup, cache, SEO.

## 2. Muc tieu website

- Gioi thieu thuong hieu KickZone.
- Hien thi danh muc sneaker theo category va brand.
- Cho phep khach hang xem san pham, them vao gio hang va tien hanh checkout.
- Cung cap blog ve xu huong sneaker, huong dan ve sinh, review san pham.
- Thu thap lien he tu khach hang thong qua form.
- Dam bao website co cau truc SEO co ban, sitemap va meta description.
- Co phuong an backup va cache de ho tro van hanh.

## 3. Cau truc website

Sitemap:

```text
/
/gioi-thieu
/shop
/blog
/lien-he
/gio-hang
/thanh-toan
/tai-khoan
```

Nam trang tinh bat buoc:

| Trang | Noi dung |
|---|---|
| Trang chu | Hero, category, san pham noi bat, sale banner, blog preview, newsletter |
| Gioi thieu | Brand story, mission, vision, ly do chon KickZone |
| Shop | WooCommerce catalog, filter sidebar, product grid |
| Blog | Danh sach bai viet, sidebar search/category/tag |
| Lien he | Contact form, dia chi, hotline, email, ban do |

## 4. Theme va plugin su dung

Theme:

- Astra Free: theme nhe, de tuy bien, phu hop website ban hang.

Plugins:

| Plugin | Vai tro |
|---|---|
| Elementor Free | Xay dung layout cac trang |
| WooCommerce | Quan ly san pham, cart, checkout, REST API |
| Yoast SEO | SEO title, meta description, sitemap |
| Contact Form 7 | Tao form lien he |
| UpdraftPlus | Backup source va database |
| W3 Total Cache | Cache va toi uu toc do |

## 5. Chuc nang chinh

### 5.1 Trang chu

Trang chu co hero banner full width voi thong diep `STEP INTO THE FUTURE`, nut CTA `Shop Now`, khu vuc danh muc Sneaker, Running, Classic, danh sach san pham noi bat, sale banner va preview blog.

### 5.2 Shop WooCommerce

Trang Shop hien thi 8 san pham voi day du thong tin: ten san pham, SKU, gia thuong, gia sale, stock, category, tags, size va mau sac. Khach hang co the vao chi tiet san pham, them vao gio hang va di den checkout.

### 5.3 Blog

Blog gom 8 bai viet thuoc cac category: Xu huong, Huong dan, Review, Kien thuc. Blog giup tang gia tri noi dung va ho tro SEO cho website.

### 5.4 Lien he

Trang lien he su dung Contact Form 7 de khach hang gui yeu cau tu van. Trang co thong tin dia chi, so dien thoai, email, gio mo cua va ghi chu ve thu thap du lieu.

### 5.5 Backend REST API

WooCommerce cung cap REST API de truy van du lieu san pham. Endpoint minh chung:

```text
GET /wp-json/wc/v3/products
GET /wp-json/wc/v3/products/categories
GET /wp-json/wp/v2/posts
GET /wp-json/wp/v2/pages
```

API nay the hien website co backend quan ly du lieu that thong qua WordPress, WooCommerce va MySQL.

## 6. Quy trinh quan tri noi dung

Quan tri vien dang nhap vao WordPress Dashboard de quan ly:

- Pages: tao va cap nhat 5 trang tinh.
- Posts: tao 8 bai blog, gan category va tag.
- Products: tao san pham WooCommerce, cap nhat gia, stock, SKU va thuoc tinh.
- Media: upload anh san pham va dien alt text.
- Users: tao tai khoan admin, editor va customer.
- Plugins: cau hinh SEO, form, backup va cache.

## 7. Quan ly nguoi dung

Website co 3 tai khoan mau:

| Username | Role | Muc dich |
|---|---|---|
| `admin_kickzone` | Administrator | Quan tri toan bo website |
| `editor_kickzone` | Editor | Dang bai va quan ly noi dung |
| `customer_test` | Customer | Tai khoan mua hang mau |

Phan quyen giup dam bao moi nguoi dung chi co quyen phu hop voi nhiem vu.

## 8. SEO

Website su dung Yoast SEO de cau hinh:

- SEO title va meta description cho tung trang.
- XML sitemap.
- Organization schema.
- Social preview image.
- Phan tich SEO va readability.

Technical SEO:

- Permalink dang `Post name`.
- Anh san pham co alt text.
- Sitemap truy cap tai `/sitemap_index.xml`.
- W3 Total Cache bat Page Cache va Browser Cache.
- Neu deploy online, submit sitemap len Google Search Console.

## 9. Ban quyen va du lieu

Anh san pham va anh minh hoa lay tu Unsplash/Pexels hoac nguon mien phi co giay phep phu hop. Noi dung mo ta san pham va blog duoc tu viet, khong copy nguyen van tu website thuong hieu. Footer co copyright `© 2024 KickZone. All rights reserved.`

Trang lien he co ghi chu ve viec thu thap du lieu: thong tin form chi duoc dung de phan hoi yeu cau tu van va khong chia se cho ben thu ba neu chua co su dong y.

## 10. Backup, cache va van hanh

UpdraftPlus duoc cau hinh backup hang tuan cho files va database. Truoc khi nop bai, nhom chay backup thu cong mot lan va chup man hinh minh chung.

W3 Total Cache duoc cau hinh Page Cache va Browser Cache de cai thien toc do tai trang. Neu hosting yeu, cac muc Database Cache va Object Cache co the de tat de tranh loi.

## 11. Trien khai

Website co the trien khai tren InfinityFree, Hostinger hoac localhost. Neu deploy online, nhom nop link website va tai khoan admin test cho giang vien. Neu lam local, nhom nop source WordPress, database export va huong dan chay.

## 12. Phan cong cong viec

| Thanh vien | Cong viec |
|---|---|
| Thanh vien 1 | Cai WordPress, theme, plugin, cau hinh hosting |
| Thanh vien 2 | Xay dung trang chu, gioi thieu, lien he bang Elementor |
| Thanh vien 3 | Nhap san pham WooCommerce, category, tag, anh san pham |
| Thanh vien 4 | Viet blog, SEO title/meta, alt text |
| Thanh vien 5 | Test, backup, API, bao cao, slide demo |

## 13. Ket luan

KickZone dap ung cac yeu cau cua bai tap lon WordPress: co website ban hang, giao dien tuy bien, plugin thuc te, quan ly san pham, blog, user, backend REST API, SEO, backup va checklist demo. Neu website duoc deploy len hosting public, bai co the dat them phan diem trien khai Internet theo yeu cau de bai.
