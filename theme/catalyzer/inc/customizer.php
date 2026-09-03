<?php
/**
 * تنظیمات سفارشی‌سازی قالب کاتالیزور.
 *
 * @package Catalyzer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function catalyzer_sanitize_bool( $value ) {
	return ( '1' === (string) $value || 1 === $value || true === $value ) ? true : false;
}

/**
 * افزودن یک فیلد ساده.
 */
function catalyzer_cz_field( $wp, $section, $id, $label, $args = array() ) {
	$type    = isset( $args['type'] ) ? $args['type'] : 'text';
	$default = isset( $args['default'] ) ? $args['default'] : '';

	if ( 'image' === $type ) {
		$wp->add_setting( 'catalyzer_' . $id, array(
			'default'           => 0,
			'sanitize_callback' => 'absint',
			'transport'         => 'refresh',
		) );
		$wp->add_control( new WP_Customize_Media_Control( $wp, 'catalyzer_' . $id, array(
			'label'     => $label,
			'section'   => $section,
			'mime_type' => 'image',
		) ) );
		return;
	}

	$sanitize = 'sanitize_text_field';
	if ( 'checkbox' === $type ) {
		$sanitize = 'catalyzer_sanitize_bool';
	} elseif ( 'url' === $type ) {
		$sanitize = 'esc_url_raw';
	} elseif ( 'email' === $type ) {
		$sanitize = 'sanitize_email';
	} elseif ( 'html' === $type ) {
		$sanitize = 'wp_kses_post';
		$type     = 'textarea';
	} elseif ( 'textarea' === $type ) {
		$sanitize = 'sanitize_textarea_field';
	}

	$wp->add_setting( 'catalyzer_' . $id, array(
		'default'           => $default,
		'sanitize_callback' => $sanitize,
		'transport'         => 'refresh',
	) );

	$control = array(
		'label'   => $label,
		'section' => $section,
		'type'    => $type,
	);
	if ( ! empty( $args['description'] ) ) {
		$control['description'] = $args['description'];
	}
	$wp->add_control( 'catalyzer_' . $id, $control );
}

/**
 * ثبت همه‌ی بخش‌ها و فیلدها.
 */
function catalyzer_customize_register( $wp ) {

	$panel = 'catalyzer_panel';
	$wp->add_panel( $panel, array(
		'title'       => 'کاتالیزور — محتوای صفحه‌ی فرود',
		'priority'    => 20,
		'description' => 'متن و تصاویر همه‌ی بخش‌های صفحه‌ی اصلی از اینجا ویرایش می‌شود. «دوره‌ها»، «ویدیوهای کلاس» و «نظرات» از منوی پیشخوان اضافه می‌شوند.',
	) );

	$add_section = function ( $id, $title, $priority ) use ( $wp, $panel ) {
		$wp->add_section( $id, array( 'title' => $title, 'panel' => $panel, 'priority' => $priority ) );
	};

	/* ---------- عمومی ---------- */
	$add_section( 'catalyzer_general', 'عمومی و برند', 10 );
	catalyzer_cz_field( $wp, 'catalyzer_general', 'brand_name', 'نام برند (کنار لوگو در هدر)', array( 'default' => 'کاتالیزور' ) );
	catalyzer_cz_field( $wp, 'catalyzer_general', 'brand_sub', 'زیرنویس لاتین برند', array( 'default' => 'SINA REZADOOST' ) );
	catalyzer_cz_field( $wp, 'catalyzer_general', 'nav_cta_text', 'متن دکمه‌ی هدر', array( 'default' => 'مشاوره و ثبت‌نام' ) );
	catalyzer_cz_field( $wp, 'catalyzer_general', 'nav_cta_url', 'لینک دکمه‌ی هدر', array( 'default' => '#contact' ) );
	catalyzer_cz_field( $wp, 'catalyzer_general', 'footer_logo_id', 'لوگوی فوتر (تصویر)', array( 'type' => 'image' ) );
	catalyzer_cz_field( $wp, 'catalyzer_general', 'footer_copy', 'متن کپی‌رایت فوتر', array( 'default' => '© ۱۴۰۴ کاتالیزور — دکتر سینا رضادوست. تمام حقوق محفوظ است.' ) );
	catalyzer_cz_field( $wp, 'catalyzer_general', 'footer_tagline', 'شعار فوتر', array( 'default' => 'شتاب‌دهنده‌ی رشد شیمیِ کنکور' ) );

	foreach ( array(
		'stats'        => 'نوار آمار',
		'about'        => 'درباره و رزومه',
		'method'       => 'متد کاتالیزور',
		'courses'      => 'دوره‌ها',
		'videos'       => 'ویدیوهای کلاس',
		'testimonials' => 'نظرات دانش‌آموزان',
		'cta'          => 'بنر فراخوان',
		'contact'      => 'تماس',
	) as $key => $label ) {
		catalyzer_cz_field( $wp, 'catalyzer_general', 'show_' . $key, 'نمایش بخش: ' . $label, array( 'type' => 'checkbox', 'default' => true ) );
	}

	/* ---------- هیرو ---------- */
	$add_section( 'catalyzer_hero', 'هیرو (بالای صفحه)', 20 );
	catalyzer_cz_field( $wp, 'catalyzer_hero', 'hero_eyebrow', 'برچسب کوچک بالای تیتر', array( 'default' => 'CATALYZER · شیمی کنکور' ) );
	catalyzer_cz_field( $wp, 'catalyzer_hero', 'hero_heading', 'تیتر اصلی', array(
		'type'        => 'textarea',
		'default'     => 'شیمیِ کنکور را [مثل یک کاتالیزور] جلو ببر.',
		'description' => 'هر عبارتی داخل [ ] با رنگ کهربایی برجسته می‌شود.',
	) );
	catalyzer_cz_field( $wp, 'catalyzer_hero', 'hero_lede', 'متن توضیح', array(
		'type'    => 'textarea',
		'default' => 'دکتر سینا رضادوست — دکتر داروساز، مدرس شیمی کنکور مدارس سمپاد و طراح آزمون‌های کشوری. با متد «کاتالیزور»، مفهوم و تست را کنار هم پیش می‌بری و از پراکندگی مطالب شیمی عبور می‌کنی.',
	) );
	catalyzer_cz_field( $wp, 'catalyzer_hero', 'hero_btn1_text', 'دکمه‌ی اول — متن', array( 'default' => 'مشاهده‌ی دوره‌ها' ) );
	catalyzer_cz_field( $wp, 'catalyzer_hero', 'hero_btn1_url', 'دکمه‌ی اول — لینک', array( 'default' => '#courses' ) );
	catalyzer_cz_field( $wp, 'catalyzer_hero', 'hero_btn2_text', 'دکمه‌ی دوم — متن', array( 'default' => 'تماشای نمونه‌ی تدریس' ) );
	catalyzer_cz_field( $wp, 'catalyzer_hero', 'hero_btn2_url', 'دکمه‌ی دوم — لینک', array( 'default' => '#videos' ) );
	catalyzer_cz_field( $wp, 'catalyzer_hero', 'hero_trust', 'خط اعتماد (زیر دکمه‌ها)', array( 'default' => 'بیش از ۱۲ سال تدریس تخصصیِ کنکور در مشهد' ) );
	catalyzer_cz_field( $wp, 'catalyzer_hero', 'hero_portrait_id', 'عکس مدرس', array( 'type' => 'image' ) );
	catalyzer_cz_field( $wp, 'catalyzer_hero', 'hero_tile_sym', 'کاشی — نماد', array( 'default' => 'C' ) );
	catalyzer_cz_field( $wp, 'catalyzer_hero', 'hero_tile_num', 'کاشی — عدد', array( 'default' => '۶' ) );
	catalyzer_cz_field( $wp, 'catalyzer_hero', 'hero_tile_name', 'کاشی — نام', array( 'default' => 'کربن · کاتالیزور' ) );

	/* ---------- آمار ---------- */
	$add_section( 'catalyzer_stats', 'نوار آمار', 30 );
	$stat_defaults = array(
		array( '+۱۲', 'سال تدریس تخصصی کنکور' ),
		array( '۲،۳،۴،۶،۹', 'رتبه‌های برتر کشوری زیر تدریس' ),
		array( 'سمپاد', 'تدریس در مدارس تیزهوشان' ),
		array( 'قلم‌چی · خیلی‌سبز', 'طراح آزمون‌های کشوری' ),
	);
	for ( $i = 1; $i <= 4; $i++ ) {
		catalyzer_cz_field( $wp, 'catalyzer_stats', "stat{$i}_value", "آمار {$i} — عدد/عبارت", array( 'default' => $stat_defaults[ $i - 1 ][0] ) );
		catalyzer_cz_field( $wp, 'catalyzer_stats', "stat{$i}_label", "آمار {$i} — توضیح", array( 'default' => $stat_defaults[ $i - 1 ][1] ) );
	}

	/* ---------- درباره ---------- */
	$add_section( 'catalyzer_about', 'درباره و رزومه', 40 );
	catalyzer_cz_field( $wp, 'catalyzer_about', 'about_eyebrow', 'برچسب کوچک', array( 'default' => 'درباره‌ی مدرس' ) );
	catalyzer_cz_field( $wp, 'catalyzer_about', 'about_heading', 'تیتر', array( 'default' => 'سینا رضادوست، دبیر شیمی کنکور' ) );
	catalyzer_cz_field( $wp, 'catalyzer_about', 'about_body', 'متن معرفی (هر پاراگراف یک خط خالی فاصله)', array(
		'type'    => 'html',
		'default' => "دکتر سینا رضادوست، دکتر داروساز و دانش‌آموخته‌ی دبیرستان شهید هاشمی‌نژاد ۱ مشهد است. سال‌هاست شیمیِ کنکور را برای داوطلبان تجربی و ریاضی تدریس می‌کند و نامش با رتبه‌های تک‌رقمی کنکور گره خورده است.\n\nتدریس در مدارس سمپاد و طراحی آزمون‌های کشوریِ قلم‌چی و خیلی‌سبز، نگاه او را به بودجه‌بندی و تیپ سؤالات کنکور دقیق کرده است؛ همان چیزی که در کلاس‌ها به دانش‌آموز منتقل می‌شود.",
	) );
	catalyzer_cz_field( $wp, 'catalyzer_about', 'about_sign', 'امضا', array( 'default' => 'دکتر سینا رضادوست' ) );
	catalyzer_cz_field( $wp, 'catalyzer_about', 'about_sign_sub', 'زیر امضا', array( 'default' => 'Dr. Sina Rezadoost — Chemistry, Mashhad' ) );
	catalyzer_cz_field( $wp, 'catalyzer_about', 'about_creds', 'رزومه (هر خط یک مورد؛ قالب: «بخش پررنگ | ادامه»)', array(
		'type'    => 'textarea',
		'default' => "دکتر داروساز | تحصیلات دانشگاهی در رشته‌ی داروسازی\nمدرس کنکور مدارس سمپاد | تدریس شیمی در مدارس تیزهوشان\nمدرس رتبه‌های ۲، ۳، ۴، ۶، ۹ و ... کنکور | همراهیِ داوطلبان برتر کشوری\nطراح آزمون‌های کشوریِ قلم‌چی و خیلی‌سبز\nدانش‌آموخته‌ی هاشمی‌نژاد ۱ مشهد",
	) );

	/* ---------- متد ---------- */
	$add_section( 'catalyzer_method', 'متد کاتالیزور', 50 );
	catalyzer_cz_field( $wp, 'catalyzer_method', 'method_eyebrow', 'برچسب کوچک', array( 'default' => 'چرا کاتالیزور؟' ) );
	catalyzer_cz_field( $wp, 'catalyzer_method', 'method_heading', 'تیتر', array( 'default' => 'کاتالیزور سرعت واکنش را بالا می‌برد — اینجا هم همین کار را می‌کنیم' ) );
	catalyzer_cz_field( $wp, 'catalyzer_method', 'method_intro', 'توضیح کوتاه', array( 'default' => 'سه ستونی که مسیر شیمیِ کنکور را کوتاه‌تر و مطمئن‌تر می‌کند.' ) );
	$pillar_defaults = array(
		array( 'مفهوم‌محور، نه حفظِ خشک', 'هر مبحث از ریشه‌ی مفهومی باز می‌شود تا سؤال‌های ترکیبی و نفس‌گیرِ کنکور برایت قابل حل باشد.' ),
		array( 'تست و تکنیکِ زمان', 'تکنیک‌های حل سریع، تله‌های رایج و مدیریت زمان روی سؤالات واقعیِ کنکور و آزمون‌های کشوری.' ),
		array( 'پشتیبانی و آزمونِ منظم', 'آزمون‌های مبحثی، بازخورد و پیگیری مستمر تا مطمئن شوی مطالب واقعاً تثبیت شده است.' ),
	);
	for ( $i = 1; $i <= 3; $i++ ) {
		catalyzer_cz_field( $wp, 'catalyzer_method', "pillar{$i}_title", "ستون {$i} — تیتر", array( 'default' => $pillar_defaults[ $i - 1 ][0] ) );
		catalyzer_cz_field( $wp, 'catalyzer_method', "pillar{$i}_text", "ستون {$i} — متن", array( 'type' => 'textarea', 'default' => $pillar_defaults[ $i - 1 ][1] ) );
	}

	/* ---------- دوره‌ها ---------- */
	$add_section( 'catalyzer_courses', 'دوره‌ها (سربرگ بخش)', 60 );
	catalyzer_cz_field( $wp, 'catalyzer_courses', 'courses_eyebrow', 'برچسب کوچک', array( 'default' => 'دوره‌ها و پکیج‌ها' ) );
	catalyzer_cz_field( $wp, 'catalyzer_courses', 'courses_heading', 'تیتر', array( 'default' => 'پکیج آموزشیِ متناسب با مسیر خودت را انتخاب کن' ) );
	catalyzer_cz_field( $wp, 'catalyzer_courses', 'courses_intro', 'توضیح کوتاه', array( 'default' => 'ویدیوی کامل کلاس، جزوه و آزمون. پرداخت امن و دسترسی دائمی پس از خرید.' ) );
	catalyzer_cz_field( $wp, 'catalyzer_courses', 'courses_note', 'یادداشت زیر کارت‌ها', array( 'default' => '' ) );

	/* ---------- ویدیوها ---------- */
	$add_section( 'catalyzer_videos', 'ویدیوهای کلاس (سربرگ بخش)', 70 );
	catalyzer_cz_field( $wp, 'catalyzer_videos', 'videos_eyebrow', 'برچسب کوچک', array( 'default' => 'ویدیوهای کلاس' ) );
	catalyzer_cz_field( $wp, 'catalyzer_videos', 'videos_heading', 'تیتر', array( 'default' => 'نمونه‌ی تدریس و بخشی از جلسات ضبط‌شده' ) );
	catalyzer_cz_field( $wp, 'catalyzer_videos', 'videos_intro', 'توضیح کوتاه', array( 'default' => 'چند دقیقه از کلاس را ببین تا با فضای تدریس آشنا شوی.' ) );
	catalyzer_cz_field( $wp, 'catalyzer_videos', 'videos_count', 'تعداد ویدیو در صفحه‌ی اصلی', array( 'default' => '6' ) );
	catalyzer_cz_field( $wp, 'catalyzer_videos', 'videos_more_text', 'دکمه‌ی «همه‌ی ویدیوها» — متن', array( 'default' => 'همه‌ی ویدیوها' ) );
	catalyzer_cz_field( $wp, 'catalyzer_videos', 'videos_more_url', 'دکمه‌ی «همه‌ی ویدیوها» — لینک', array( 'default' => '' ) );

	/* ---------- نظرات ---------- */
	$add_section( 'catalyzer_testimonials', 'نظرات (سربرگ بخش)', 80 );
	catalyzer_cz_field( $wp, 'catalyzer_testimonials', 'quotes_eyebrow', 'برچسب کوچک', array( 'default' => 'نظر دانش‌آموزان' ) );
	catalyzer_cz_field( $wp, 'catalyzer_testimonials', 'quotes_heading', 'تیتر', array( 'default' => 'حرف‌هایی از داوطلب‌هایی که شیمی را جدی گرفتند' ) );

	/* ---------- فراخوان ---------- */
	$add_section( 'catalyzer_cta', 'بنر فراخوان', 90 );
	catalyzer_cz_field( $wp, 'catalyzer_cta', 'cta_heading', 'تیتر', array( 'default' => 'همین امروز شیمی‌ات را از نقطه‌ضعف به نقطه‌قوت تبدیل کن' ) );
	catalyzer_cz_field( $wp, 'catalyzer_cta', 'cta_text', 'توضیح', array( 'default' => 'برای مشاوره‌ی انتخاب دوره، پیام بده.' ) );
	catalyzer_cz_field( $wp, 'catalyzer_cta', 'cta_btn1_text', 'دکمه‌ی اول — متن', array( 'default' => 'درخواست مشاوره' ) );
	catalyzer_cz_field( $wp, 'catalyzer_cta', 'cta_btn1_url', 'دکمه‌ی اول — لینک', array( 'default' => '#contact' ) );
	catalyzer_cz_field( $wp, 'catalyzer_cta', 'cta_btn2_text', 'دکمه‌ی دوم — متن', array( 'default' => 'دیدن دوره‌ها' ) );
	catalyzer_cz_field( $wp, 'catalyzer_cta', 'cta_btn2_url', 'دکمه‌ی دوم — لینک', array( 'default' => '#courses' ) );

	/* ---------- تماس ---------- */
	$add_section( 'catalyzer_contact', 'تماس', 100 );
	catalyzer_cz_field( $wp, 'catalyzer_contact', 'contact_eyebrow', 'برچسب کوچک', array( 'default' => 'تماس و مشاوره' ) );
	catalyzer_cz_field( $wp, 'catalyzer_contact', 'contact_heading', 'تیتر', array( 'default' => 'سؤالی داری؟ راه‌های ارتباط با کاتالیزور' ) );
	catalyzer_cz_field( $wp, 'catalyzer_contact', 'contact_telegram_label', 'برچسب تلگرام', array( 'default' => 'تلگرام پشتیبانی' ) );
	catalyzer_cz_field( $wp, 'catalyzer_contact', 'contact_telegram_value', 'آیدی/متن تلگرام', array( 'default' => '@catalyzer_support' ) );
	catalyzer_cz_field( $wp, 'catalyzer_contact', 'contact_phone', 'شماره تماس (نمایشی)', array( 'default' => '۰۹۱۵ ۰۰۰ ۰۰۰۰' ) );
	catalyzer_cz_field( $wp, 'catalyzer_contact', 'contact_location', 'محل تدریس', array( 'default' => 'مشهد' ) );
	catalyzer_cz_field( $wp, 'catalyzer_contact', 'contact_email', 'ایمیل دریافت درخواست‌ها (خالی = ایمیل مدیر سایت)', array( 'type' => 'email', 'default' => '' ) );
	catalyzer_cz_field( $wp, 'catalyzer_contact', 'social_telegram', 'لینک تلگرام', array( 'type' => 'url', 'default' => '' ) );
	catalyzer_cz_field( $wp, 'catalyzer_contact', 'social_instagram', 'لینک اینستاگرام', array( 'type' => 'url', 'default' => '' ) );
	catalyzer_cz_field( $wp, 'catalyzer_contact', 'social_youtube', 'لینک یوتیوب', array( 'type' => 'url', 'default' => '' ) );
	catalyzer_cz_field( $wp, 'catalyzer_contact', 'social_aparat', 'لینک اپارات', array( 'type' => 'url', 'default' => '' ) );
	catalyzer_cz_field( $wp, 'catalyzer_contact', 'contact_cf7', 'شورت‌کد فرم (مثلاً Contact Form 7). اگر پر شود جایگزین فرم داخلی می‌شود.', array( 'default' => '' ) );
	catalyzer_cz_field( $wp, 'catalyzer_contact', 'contact_form_note', 'یادداشت زیر فرم', array( 'default' => 'در کوتاه‌ترین زمان با شما تماس می‌گیریم.' ) );
}
add_action( 'customize_register', 'catalyzer_customize_register' );
