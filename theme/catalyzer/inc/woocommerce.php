<?php
/**
 * یکپارچگی سبک با ووکامرس.
 *
 * ووکامرس برای فروش «پکیج‌های آموزشی» و پرداخت استفاده می‌شود. این فایل
 * فقط وقتی بارگذاری می‌شود که افزونه‌ی ووکامرس فعال باشد.
 *
 * @package Catalyzer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// سایدبار پیش‌فرض فروشگاه را حذف کن (طرح تک‌ستونه).
remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );

// رَپِر ووکامرس را با شل قالب هماهنگ کن.
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );

add_action( 'woocommerce_before_main_content', function () {
	echo '<div class="section woocommerce-wrap"><div class="wrap">';
}, 10 );

add_action( 'woocommerce_after_main_content', function () {
	echo '</div></div>';
}, 10 );

// تعداد ستون‌ها و محصولات در هر صفحه.
add_filter( 'loop_shop_columns', function () { return 3; } );
add_filter( 'loop_shop_per_page', function () { return 12; } );

// پشتیبانی از قالب صفحه‌ی فروشگاه در ابعاد کامل.
add_filter( 'woocommerce_enqueue_styles', function ( $styles ) { return $styles; } );
