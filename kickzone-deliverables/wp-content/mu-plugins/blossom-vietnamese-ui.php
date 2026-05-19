<?php
/**
 * Plugin Name: Blossom Chic Vietnamese UI
 * Description: Vietnamese UI text overrides for Blossom Chic and Blossom Feminine.
 */

add_filter('gettext', function ($translated, $text, $domain) {
    $map = [
        'Read More' => 'Đọc tiếp',
        'Continue Reading' => 'Đọc tiếp',
        'Continue reading' => 'Đọc tiếp',
        'Search' => 'Tìm kiếm',
        'Search...' => 'Tìm kiếm...',
        'Search for:' => 'Tìm kiếm:',
        'Search Results for: %s' => 'Kết quả tìm kiếm cho: %s',
        'Nothing Found' => 'Không tìm thấy nội dung',
        'Sorry, but nothing matched your search terms. Please try again with some different keywords.' => 'Không có nội dung phù hợp. Vui lòng thử lại với từ khóa khác.',
        'Home' => 'Trang chủ',
        'Menu' => 'Menu',
        'Mobile' => 'Di động',
        'Close' => 'Đóng',
        'Previous' => 'Trước',
        'Next' => 'Sau',
        'Older posts' => 'Bài cũ hơn',
        'Newer posts' => 'Bài mới hơn',
        'Related Posts' => 'Bài viết liên quan',
        'You may also like' => 'Có thể bạn cũng thích',
        'Leave a Comment' => 'Để lại bình luận',
        'Leave a comment' => 'Để lại bình luận',
        'Comments' => 'Bình luận',
        'Comment' => 'Bình luận',
        'Post Comment' => 'Gửi bình luận',
        'Name' => 'Họ tên',
        'Email' => 'Email',
        'Website' => 'Website',
        'Categories' => 'Danh mục',
        'Category' => 'Danh mục',
        'Tags' => 'Thẻ',
        'Tag' => 'Thẻ',
        'Archives' => 'Lưu trữ',
        'Recent Posts' => 'Bài viết mới',
        'Popular Posts' => 'Bài viết nổi bật',
        'Shop' => 'Cửa hàng',
        'Cart' => 'Giỏ hàng',
        'Checkout' => 'Thanh toán',
        'My account' => 'Tài khoản',
        'Add to cart' => 'Thêm vào giỏ',
        'View cart' => 'Xem giỏ hàng',
        'Sale!' => 'Giảm giá!',
        'Products' => 'Sản phẩm',
        'Product' => 'Sản phẩm',
        'No products were found matching your selection.' => 'Không có sản phẩm phù hợp.',
        'Showing all %d result' => 'Hiển thị tất cả %d kết quả',
        'Showing all %d results' => 'Hiển thị tất cả %d kết quả',
        'Default sorting' => 'Sắp xếp mặc định',
        'Sort by popularity' => 'Sắp xếp theo độ phổ biến',
        'Sort by average rating' => 'Sắp xếp theo đánh giá',
        'Sort by latest' => 'Sắp xếp mới nhất',
        'Sort by price: low to high' => 'Giá: thấp đến cao',
        'Sort by price: high to low' => 'Giá: cao đến thấp',
        'secondary menu toggle button' => 'nút mở menu phụ',
        'primary menu toggle button' => 'nút mở menu chính',
        'search toggle button' => 'nút mở tìm kiếm',
        ' Blossom Chic | Developed By ' => ' Blossom Chic | Phát triển bởi ',
        ' Powered by %s' => ' Vận hành bởi %s',
        'Demo & Documentation' => 'Demo & Tài liệu',
        'Appearance Settings' => 'Cài đặt giao diện',
        'Typography' => 'Kiểu chữ',
        'Primary Font' => 'Font chính',
        'Secondary Font' => 'Font phụ',
        'Font Size' => 'Cỡ chữ',
        'Primary Color' => 'Màu chính',
        'Secondary Color' => 'Màu phụ',
        'Layout Settings' => 'Cài đặt bố cục',
        'Header Layout' => 'Bố cục header',
        'Home Page Layout' => 'Bố cục trang chủ',
        'Slider Layout' => 'Bố cục slider',
    ];

    if (isset($map[$text])) {
        return $map[$text];
    }

    return $translated;
}, 20, 3);

add_filter('ngettext', function ($translation, $single, $plural, $number, $domain) {
    if ($single === '%s Comment' || $plural === '%s Comments') {
        return sprintf('%s bình luận', number_format_i18n($number));
    }
    return $translation;
}, 20, 5);
