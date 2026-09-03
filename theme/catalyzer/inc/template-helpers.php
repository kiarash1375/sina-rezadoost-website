<?php
/**
 * توابع کمکی قالب.
 *
 * @package Catalyzer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * گرفتن یک تنظیم سفارشی‌سازی با پیشوند catalyzer_.
 *
 * @param string $key     کلید بدون پیشوند.
 * @param mixed  $default مقدار پیش‌فرض.
 * @return mixed
 */
function catalyzer_opt( $key, $default = '' ) {
	return get_theme_mod( 'catalyzer_' . $key, $default );
}

/**
 * آیا یک بخش از صفحه‌ی فرود فعال است؟
 */
function catalyzer_section_enabled( $key ) {
	return (bool) catalyzer_opt( 'show_' . $key, true );
}

/**
 * تبدیل متن چندخطی به آرایه‌ای از خطوط غیرخالی.
 */
function catalyzer_lines( $text ) {
	$text  = (string) $text;
	$lines = preg_split( '/\r\n|\r|\n/', $text );
	$lines = array_map( 'trim', $lines );
	return array_values( array_filter( $lines, 'strlen' ) );
}

/**
 * جایگزینی [...] با <span class="hl">...</span> در تیتر هیرو.
 * ابتدا امن‌سازی، سپس تبدیل کروشه‌ها.
 */
function catalyzer_highlight( $text ) {
	$safe = esc_html( $text );
	$safe = str_replace( array( '[', ']' ), array( '<span class="hl">', '</span>' ), $safe );
	return $safe;
}

/**
 * تجزیه‌ی یک خط رزومه به بخش پررنگ و ادامه.
 * قالب هر خط:  «بخش پررنگ | ادامه‌ی توضیح»  (اگر | نبود، کل خط پررنگ است).
 *
 * @return array{strong:string,rest:string}
 */
function catalyzer_split_credential( $line ) {
	$parts  = explode( '|', $line, 2 );
	$strong = trim( $parts[0] );
	$rest   = isset( $parts[1] ) ? trim( $parts[1] ) : '';
	return array( 'strong' => $strong, 'rest' => $rest );
}

/**
 * اگر لینک با # شروع شود و روی صفحه‌ی اصلی نباشیم، آدرس خانه را جلوی آن می‌گذارد
 * تا لنگرها از صفحات داخلی هم درست کار کنند.
 */
function catalyzer_anchor_url( $url ) {
	$url = trim( (string) $url );
	if ( '' === $url ) {
		return '#';
	}
	if ( '#' === $url[0] && ! is_front_page() ) {
		return home_url( '/' ) . $url;
	}
	return $url;
}

/**
 * آدرس تصویر پرتره‌ی هیرو (تنظیم سفارشی‌سازی، وگرنه تصویر پیش‌فرض قالب).
 */
function catalyzer_hero_portrait_url() {
	$id = catalyzer_opt( 'hero_portrait_id' );
	if ( $id ) {
		$src = wp_get_attachment_image_url( (int) $id, 'large' );
		if ( $src ) {
			return $src;
		}
	}
	return CATALYZER_URI . '/assets/img/sina-rezadoost.jpg';
}

/**
 * آدرس لوگوی فوتر.
 */
function catalyzer_footer_logo_url() {
	$id = catalyzer_opt( 'footer_logo_id' );
	if ( $id ) {
		$src = wp_get_attachment_image_url( (int) $id, 'medium' );
		if ( $src ) {
			return $src;
		}
	}
	return CATALYZER_URI . '/assets/img/catalyzer-logo.jpg';
}

/**
 * نشان SVG برند (سازگار با تم روشن/تیره).
 */
function catalyzer_brand_mark() {
	return '<svg class="mark" viewBox="0 0 52 52" fill="none" aria-hidden="true">'
		. '<g stroke-linecap="round" stroke-linejoin="round">'
		. '<path class="c-stroke" d="M40.7 17.5 L26 9 L11.3 17.5 L11.3 34.5 L26 43 L40.7 34.5" stroke-width="4.2"/>'
		. '<path class="c-stroke" d="M35.1 20.9 L26 15.6 L16.9 20.9 L16.9 31.1 L26 36.4" stroke-width="3.3" opacity="0.85"/>'
		. '<path class="c-arrow" d="M30 33 H44 M39 27.5 L44.5 33 L39 38.5" stroke-width="4.2"/>'
		. '</g></svg>';
}

/**
 * مجموعه‌ی آیکون‌های خطی درون‌خطی.
 */
function catalyzer_icon( $name ) {
	$icons = array(
		'check'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M5 13l4 4L19 7"/></svg>',
		'hexagon'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2 21 7v10l-9 5-9-5V7z"/></svg>',
		'play'      => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>',
		'play-o'    => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>',
		'check-c'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M8 12l3 3 5-6"/></svg>',
		'alert-c'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v5M12 16h.01"/></svg>',
		'telegram'  => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M21.9 4.3 18.6 19.8c-.2 1-.9 1.3-1.8.8l-4.9-3.6-2.4 2.3c-.3.3-.5.5-1 .5l.3-4.9L17 6.3c.4-.3-.1-.5-.6-.2L6.6 12.4l-4.8-1.5C.8 10.6.8 10 2 9.5l18.3-7c.9-.3 1.7.2 1.6 1.8Z"/></svg>',
		'instagram' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1.2" fill="currentColor" stroke="none"/></svg>',
		'youtube'   => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M23 12s0-3.2-.4-4.7c-.2-.8-.9-1.5-1.7-1.7C19.4 5.2 12 5.2 12 5.2s-7.4 0-8.9.4c-.8.2-1.5.9-1.7 1.7C1 8.8 1 12 1 12s0 3.2.4 4.7c.2.8.9 1.5 1.7 1.7 1.5.4 8.9.4 8.9.4s7.4 0 8.9-.4c.8-.2 1.5-.9 1.7-1.7C23 15.2 23 12 23 12ZM9.8 15.3V8.7l5.7 3.3-5.7 3.3Z"/></svg>',
		'aparat'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="3.4"/><path d="M12 3v3M12 18v3M3 12h3M18 12h3"/></svg>',
		'phone'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 3h4l2 5-3 2a12 12 0 0 0 6 6l2-3 5 2v4a2 2 0 0 1-2 2A17 17 0 0 1 4 5a2 2 0 0 1 2-2Z"/></svg>',
		'sun'       => '<svg class="sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="4.2"/><path d="M12 3v2M12 19v2M3 12h2M19 12h2M5.6 5.6l1.4 1.4M17 17l1.4 1.4M18.4 5.6L17 7M7 17l-1.4 1.4"/></svg>',
		'moon'      => '<svg class="moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 14.5A8 8 0 0 1 9.5 4a8 8 0 1 0 10.5 10.5Z"/></svg>',
		'menu'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 7h16M4 12h16M4 17h16"/></svg>',
		'rings'     => '<svg class="rings" viewBox="0 0 120 75" fill="none" stroke="var(--accent)" stroke-width="1" opacity="0.35" aria-hidden="true"><path d="M60 20 74 28 74 47 60 55 46 47 46 28Z"/><circle cx="60" cy="37" r="3"/></svg>',
	);
	return isset( $icons[ $name ] ) ? $icons[ $name ] : '';
}

/**
 * لینک‌های ناوبری: از منوی «primary» اگر تعیین شده باشد، وگرنه لینک‌های پیش‌فرضِ لنگرِ صفحه.
 *
 * @param string $class کلاس عنصر لیست.
 */
function catalyzer_nav_menu( $list_class = 'nav-links', $panel = false ) {
	$id = $panel ? 'navPanel' : 'navLinks';

	$cta_li = '';
	if ( $panel ) {
		$cta_li = sprintf(
			'<li class="menu-item nav-panel-cta"><a class="btn btn-primary" href="%s">%s</a></li>',
			esc_url( catalyzer_anchor_url( catalyzer_opt( 'nav_cta_url', '#contact' ) ) ),
			esc_html( catalyzer_opt( 'nav_cta_text', 'مشاوره و ثبت‌نام' ) )
		);
	}

	if ( has_nav_menu( 'primary' ) ) {
		wp_nav_menu( array(
			'theme_location' => 'primary',
			'container'      => false,
			'menu_id'        => $id,
			'menu_class'     => $list_class,
			'depth'          => 1,
			'fallback_cb'    => false,
			'items_wrap'     => '<ul id="%1$s" class="%2$s">%3$s' . str_replace( '%', '%%', $cta_li ) . '</ul>',
		) );
		return;
	}

	$default = array(
		'#about'   => 'درباره',
		'#method'  => 'متد کاتالیزور',
		'#courses' => 'دوره‌ها',
		'#videos'  => 'ویدیوها',
		'#contact' => 'تماس',
	);
	/**
	 * فیلتر لینک‌های پیش‌فرض ناوبری.
	 */
	$links = apply_filters( 'catalyzer_default_nav', $default );

	echo '<ul id="' . esc_attr( $id ) . '" class="' . esc_attr( $list_class ) . '">';
	foreach ( $links as $hash => $label ) {
		printf(
			'<li class="menu-item"><a class="js-navlink" href="%s">%s</a></li>',
			esc_url( catalyzer_anchor_url( $hash ) ),
			esc_html( $label )
		);
	}
	echo $cta_li; // phpcs:ignore WordPress.Security.EscapeOutput
	echo '</ul>';
}

/**
 * کلاس js-navlink را به لینک‌های منوی اصلی اضافه می‌کند (برای اسکرول‌اسپای).
 */
function catalyzer_nav_link_attributes( $atts, $item, $args ) {
	if ( isset( $args->theme_location ) && $args->theme_location === 'primary' ) {
		$existing      = isset( $atts['class'] ) ? $atts['class'] : '';
		$atts['class'] = trim( $existing . ' js-navlink' );
	}
	return $atts;
}
add_filter( 'nav_menu_link_attributes', 'catalyzer_nav_link_attributes', 10, 3 );

/**
 * منوی فوتر (ساده).
 */
function catalyzer_footer_menu() {
	if ( has_nav_menu( 'footer' ) ) {
		wp_nav_menu( array(
			'theme_location' => 'footer',
			'container'      => 'nav',
			'menu_class'     => 'footer-menu',
			'depth'          => 1,
			'fallback_cb'    => false,
		) );
		return;
	}
	$links = apply_filters( 'catalyzer_default_nav', array(
		'#about'   => 'درباره',
		'#method'  => 'متد کاتالیزور',
		'#courses' => 'دوره‌ها',
		'#videos'  => 'ویدیوها',
		'#contact' => 'تماس',
	) );
	echo '<nav>';
	foreach ( $links as $hash => $label ) {
		printf( '<a href="%s">%s</a>', esc_url( catalyzer_anchor_url( $hash ) ), esc_html( $label ) );
	}
	echo '</nav>';
}

/**
 * جاسازی ویدیو از روی یک آدرس (یوتیوب/اپارات/mp4).
 */
function catalyzer_video_embed( $url ) {
	$url = trim( $url );
	if ( ! $url ) {
		return '';
	}
	if ( preg_match( '/\.(mp4|webm|ogg)(\?.*)?$/i', $url ) ) {
		return '<video controls preload="metadata" src="' . esc_url( $url ) . '"></video>';
	}
	$oembed = wp_oembed_get( $url, array( 'width' => 940 ) );
	if ( $oembed ) {
		return $oembed;
	}
	return '<p><a class="btn btn-primary" href="' . esc_url( $url ) . '" target="_blank" rel="noopener">تماشای ویدیو</a></p>';
}

/**
 * صفحه‌بندی استاندارد با کلاس‌های قالب.
 */
function catalyzer_pagination() {
	$links = paginate_links( array(
		'type'      => 'plain',
		'mid_size'  => 1,
		'prev_text' => '«',
		'next_text' => '»',
	) );
	if ( $links ) {
		echo '<nav class="pagination" aria-label="' . esc_attr__( 'صفحه‌بندی', 'catalyzer' ) . '">' . $links . '</nav>'; // phpcs:ignore WordPress.Security.EscapeOutput
	}
}
