<?php
/**
 * قالب کاتالیزور — بوت‌استرپ
 *
 * @package Catalyzer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CATALYZER_VERSION', '1.3.0' );
define( 'CATALYZER_DIR', get_template_directory() );
define( 'CATALYZER_URI', get_template_directory_uri() );

/**
 * راه‌اندازی امکانات قالب.
 */
function catalyzer_setup() {
	load_theme_textdomain( 'catalyzer', CATALYZER_DIR . '/languages' );

	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
	add_theme_support( 'custom-logo', array(
		'height'      => 76,
		'width'       => 260,
		'flex-height' => true,
		'flex-width'  => true,
	) );
	add_theme_support( 'align-wide' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'editor-styles' );
	add_editor_style( 'assets/css/editor.css' );
	add_theme_support( 'woocommerce' );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );

	register_nav_menus( array(
		'primary' => __( 'منوی اصلی (هدر)', 'catalyzer' ),
		'footer'  => __( 'منوی فوتر', 'catalyzer' ),
	) );

	add_image_size( 'catalyzer-card', 720, 450, true );

	// اپارات را به‌عنوان ارائه‌دهنده‌ی oEmbed اضافه می‌کنیم تا آدرس ویدیوها خودکار جاسازی شود.
	wp_oembed_add_provider( '#https?://(www\.)?aparat\.com/v/.*#i', 'https://www.aparat.com/oembed', true );
}
add_action( 'after_setup_theme', 'catalyzer_setup' );

/**
 * عرض محتوای اصلی.
 */
function catalyzer_content_width() {
	$GLOBALS['content_width'] = 1140;
}
add_action( 'after_setup_theme', 'catalyzer_content_width', 0 );

/**
 * بارگذاری استایل و اسکریپت‌ها.
 */
function catalyzer_assets() {
	wp_enqueue_style(
		'catalyzer-fonts',
		'https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800;900&family=IBM+Plex+Mono:wght@400;500&display=swap',
		array(),
		null
	);

	$css_path = CATALYZER_DIR . '/assets/css/theme.css';
	wp_enqueue_style(
		'catalyzer',
		CATALYZER_URI . '/assets/css/theme.css',
		array( 'catalyzer-fonts' ),
		file_exists( $css_path ) ? filemtime( $css_path ) : CATALYZER_VERSION
	);

	$bg_path = CATALYZER_DIR . '/assets/js/atomic-bg.js';
	wp_enqueue_script(
		'catalyzer-bg',
		CATALYZER_URI . '/assets/js/atomic-bg.js',
		array(),
		file_exists( $bg_path ) ? filemtime( $bg_path ) : CATALYZER_VERSION,
		true
	);

	$site_path = CATALYZER_DIR . '/assets/js/site.js';
	wp_enqueue_script(
		'catalyzer-site',
		CATALYZER_URI . '/assets/js/site.js',
		array(),
		file_exists( $site_path ) ? filemtime( $site_path ) : CATALYZER_VERSION,
		true
	);

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'catalyzer_assets' );

/**
 * فونت‌ها را در ویرایشگر بلوک هم بارگذاری کن.
 */
function catalyzer_editor_assets() {
	wp_enqueue_style(
		'catalyzer-editor-fonts',
		'https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800;900&family=IBM+Plex+Mono:wght@400;500&display=swap',
		array(),
		null
	);
}
add_action( 'enqueue_block_editor_assets', 'catalyzer_editor_assets' );

/**
 * کلاس‌های بدنه.
 */
function catalyzer_body_classes( $classes ) {
	if ( ! is_singular() ) {
		$classes[] = 'hfeed';
	}
	if ( is_front_page() ) {
		$classes[] = 'catalyzer-landing';
	}
	return $classes;
}
add_filter( 'body_class', 'catalyzer_body_classes' );

/**
 * طول خلاصه و ادامه‌ی مطلب.
 */
add_filter( 'excerpt_length', function () { return 24; } );
add_filter( 'excerpt_more', function () { return '&hellip;'; } );

/**
 * کارهای یک‌بار پس از به‌روزرسانی قالب: ثبت مسیرها و ساخت صفحه‌ی حساب کاربری.
 */
function catalyzer_maybe_upgrade() {
	if ( get_option( 'catalyzer_theme_version' ) === CATALYZER_VERSION ) {
		return;
	}
	if ( function_exists( 'catalyzer_register_post_types' ) ) {
		catalyzer_register_post_types();
	}
	flush_rewrite_rules();
	if ( function_exists( 'catalyzer_ensure_account_page' ) ) {
		catalyzer_ensure_account_page();
	}
	catalyzer_retire_contact_links();
	update_option( 'catalyzer_theme_version', CATALYZER_VERSION );
}
add_action( 'admin_init', 'catalyzer_maybe_upgrade', 5 );

/**
 * بخش «تماس و مشاوره» در نسخه‌ی ۱.۳.۰ حذف شد. هر لینکی که هنوز به #contact
 * اشاره می‌کند به صفحه‌ی حساب کاربری منتقل می‌شود و آیتم «تماس» از منوها
 * برداشته می‌شود، وگرنه کاربر روی لنگرِ ناموجود می‌ماند.
 */
function catalyzer_retire_contact_links() {
	if ( ! function_exists( 'catalyzer_account_url' ) ) {
		return;
	}
	$account = catalyzer_account_url();

	$opts = get_option( 'catalyzer_options', array() );
	if ( is_array( $opts ) ) {
		$dirty = false;
		foreach ( array( 'cta_btn1_url', 'cta_btn2_url', 'nav_cta_url' ) as $key ) {
			if ( isset( $opts[ $key ] ) && false !== strpos( (string) $opts[ $key ], '#contact' ) ) {
				$opts[ $key ] = $account;
				$dirty        = true;
			}
		}
		unset( $opts['nav_cta_text'], $opts['nav_cta_url'] );
		if ( $dirty ) {
			update_option( 'catalyzer_options', $opts );
		}
	}

	foreach ( array( 'primary', 'footer' ) as $location ) {
		$menu = wp_get_nav_menu_object( get_nav_menu_locations()[ $location ] ?? 0 );
		if ( ! $menu ) {
			continue;
		}
		foreach ( (array) wp_get_nav_menu_items( $menu->term_id ) as $item ) {
			if ( isset( $item->url ) && false !== strpos( $item->url, '#contact' ) ) {
				wp_delete_post( $item->ID, true );
			}
		}
	}
}

require CATALYZER_DIR . '/inc/template-helpers.php';
require CATALYZER_DIR . '/inc/post-types.php';
require CATALYZER_DIR . '/inc/customizer.php';
require CATALYZER_DIR . '/inc/contact.php';
require CATALYZER_DIR . '/inc/auth.php';
require CATALYZER_DIR . '/inc/enrollment.php';
require CATALYZER_DIR . '/inc/demo-content.php';

if ( class_exists( 'WooCommerce' ) ) {
	require CATALYZER_DIR . '/inc/woocommerce.php';
}
