<?php
/**
 * Plugin Name: KickZone Vietnamese UI
 * Description: Vietnamese UI overrides for KickZone (WordPress core + WooCommerce).
 */

add_filter('gettext', function ($translated, $text, $domain) {
    static $map = null;
    if ($map === null) {
        $map = [
            // Navigation & general
            'Home'           => 'Trang chủ',
            'Menu'           => 'Menu',
            'Close'          => 'Đóng',
            'Previous'       => 'Trước',
            'Next'           => 'Tiếp',
            'Search'         => 'Tìm kiếm',
            'Search...'      => 'Tìm kiếm...',
            'Search for:'    => 'Tìm kiếm:',
            'Nothing Found'  => 'Không tìm thấy kết quả',
            'Sorry, but nothing matched your search terms. Please try again with some different keywords.'
                             => 'Không có nội dung phù hợp. Vui lòng thử từ khóa khác.',
            'Search Results for: %s' => 'Kết quả tìm kiếm: %s',

            // Blog
            'Read More'       => 'Đọc tiếp',
            'Continue Reading'=> 'Đọc tiếp',
            'Older posts'     => 'Bài cũ hơn',
            'Newer posts'     => 'Bài mới hơn',
            'Recent Posts'    => 'Bài viết mới',
            'Popular Posts'   => 'Bài nổi bật',
            'Related Posts'   => 'Bài liên quan',
            'Categories'      => 'Danh mục',
            'Category'        => 'Danh mục',
            'Tags'            => 'Thẻ',
            'Tag'             => 'Thẻ',
            'Archives'        => 'Lưu trữ',
            'Leave a Comment' => 'Để lại bình luận',
            'Post Comment'    => 'Gửi bình luận',
            'Comments'        => 'Bình luận',
            'Name'            => 'Họ tên',
            'Website'         => 'Website',

            // WooCommerce
            'Shop'            => 'Cửa hàng',
            'Cart'            => 'Giỏ hàng',
            'Checkout'        => 'Thanh toán',
            'My account'      => 'Tài khoản',
            'My Account'      => 'Tài khoản',
            'Add to cart'     => 'Thêm vào giỏ',
            'Add to Cart'     => 'Thêm vào giỏ',
            'View cart'       => 'Xem giỏ hàng',
            'View Cart'       => 'Xem giỏ hàng',
            'Sale!'           => 'Giảm giá!',
            'Products'        => 'Sản phẩm',
            'Product'         => 'Sản phẩm',
            'Description'     => 'Mô tả',
            'Reviews'         => 'Đánh giá',
            'Additional information' => 'Thông số kỹ thuật',
            'Related products'=> 'Sản phẩm liên quan',
            'You may also like...' => 'Có thể bạn sẽ thích',
            'Out of stock'    => 'Hết hàng',
            'In stock'        => 'Còn hàng',
            'SKU:'            => 'Mã SP:',
            'Category:'       => 'Danh mục:',
            'Tags:'           => 'Thẻ:',
            'Quantity'        => 'Số lượng',
            'Subtotal'        => 'Tạm tính',
            'Total'           => 'Tổng cộng',
            'Shipping'        => 'Vận chuyển',
            'Coupon:'         => 'Mã giảm giá:',
            'Apply coupon'    => 'Áp dụng',
            'Proceed to checkout' => 'Thanh toán',
            'Update cart'     => 'Cập nhật giỏ',
            'Remove this item'=> 'Xóa',
            'No products were found matching your selection.' => 'Không có sản phẩm phù hợp.',
            'Default sorting' => 'Mặc định',
            'Sort by popularity'    => 'Phổ biến nhất',
            'Sort by average rating'=> 'Đánh giá cao nhất',
            'Sort by latest'        => 'Mới nhất',
            'Sort by price: low to high' => 'Giá: thấp → cao',
            'Sort by price: high to low' => 'Giá: cao → thấp',
            'Showing all %d result'  => 'Hiển thị %d sản phẩm',
            'Showing all %d results' => 'Hiển thị %d sản phẩm',
            'Order'           => 'Đơn hàng',
            'Order #%s'       => 'Đơn hàng #%s',
            'Place order'     => 'Đặt hàng',
            'Billing details' => 'Thông tin thanh toán',
            'First name'      => 'Tên',
            'Last name'       => 'Họ',
            'Company name (optional)' => 'Công ty (tuỳ chọn)',
            'Country / Region'=> 'Quốc gia',
            'Street address'  => 'Địa chỉ',
            'Town / City'     => 'Thành phố',
            'Phone'           => 'Số điện thoại',
            'Order notes (optional)' => 'Ghi chú đơn hàng (tuỳ chọn)',
            'Your order'      => 'Đơn hàng của bạn',
            'Thank you. Your order has been received.' => 'Cảm ơn! Đơn hàng của bạn đã được ghi nhận.',
            'Register'        => 'Đăng ký',
            'Log in'          => 'Đăng nhập',
            'Log out'         => 'Đăng xuất',
            'Username or email address' => 'Tên đăng nhập hoặc email',
            'Password'        => 'Mật khẩu',
            'Remember me'     => 'Ghi nhớ đăng nhập',
            'Lost your password?' => 'Quên mật khẩu?',
            ' Powered by %s'  => ' Vận hành bởi %s',
        ];
    }

    return $map[$text] ?? $translated;
}, 20, 3);

add_filter('ngettext', function ($translation, $single, $plural, $number, $domain) {
    if ($single === '%s Comment' || $plural === '%s Comments') {
        return sprintf('%s bình luận', number_format_i18n($number));
    }
    if ($single === '%s item' || $plural === '%s items') {
        return sprintf('%s sản phẩm', number_format_i18n($number));
    }
    return $translation;
}, 20, 5);
