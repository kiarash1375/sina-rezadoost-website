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
	catalyzer_cz_field( $wp, 'catalyzer_general', 'footer_logo_id', 'لوگوی فوتر (تصویر)', array( 'type' => 'image' ) );
	catalyzer_cz_field( $wp, 'catalyzer_general', 'footer_copy', 'متن کپی‌رایت فوتر', array( 'default' => '© ۱۴۰۴ کاتالیزور — دکتر سینا رضادوست. تمام حقوق محفوظ است.' ) );
	catalyzer_cz_field( $wp, 'catalyzer_general', 'footer_tagline', 'شعار فوتر', array( 'default' => 'شتاب‌دهنده‌ی رشد شیمیِ کنکور' ) );

	foreach ( array(
		'stats'        => 'نوار آمار',
		'about'        => 'درباره و رزومه',
		'method'       => 'متد کاتالیزور',
		'courses'      => 'دوره‌ها',
		'notes'        => 'جزوه‌ها',
		'exams'        => 'آزمون‌ها',
		'videos'       => 'ویدیوهای کلاس',
		'success'      => 'ویدیوهای رتبه‌برترها',
		'testimonials' => 'نظرات دانش‌آموزان',
		'cta'          => 'بنر فراخوان',
		'contact'      => 'تماس با ما',
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
		'default' => catalyzer_default_bio(),
	) );
	catalyzer_cz_field( $wp, 'catalyzer_about', 'about_sign', 'امضا', array( 'default' => 'دکتر سینا رضادوست' ) );
	catalyzer_cz_field( $wp, 'catalyzer_about', 'about_sign_sub', 'زیر امضا', array( 'default' => 'Dr. Sina Rezadoost — Chemistry, Mashhad' ) );
	catalyzer_cz_field( $wp, 'catalyzer_about', 'about_creds', 'رزومه (هر خط یک مورد؛ قالب: «بخش پررنگ | ادامه»)', array(
		'type'    => 'textarea',
		'default' => catalyzer_default_credentials(),
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

	/* ---------- جزوه‌ها ---------- */
	$add_section( 'catalyzer_notes', 'جزوه‌ها (سربرگ بخش)', 65 );
	catalyzer_cz_field( $wp, 'catalyzer_notes', 'notes_eyebrow', 'برچسب کوچک', array( 'default' => catalyzer_notes_default( 'notes_eyebrow' ) ) );
	catalyzer_cz_field( $wp, 'catalyzer_notes', 'notes_heading', 'تیتر', array( 'default' => catalyzer_notes_default( 'notes_heading' ) ) );
	catalyzer_cz_field( $wp, 'catalyzer_notes', 'notes_intro', 'توضیح کوتاه', array( 'default' => catalyzer_notes_default( 'notes_intro' ) ) );
	catalyzer_cz_field( $wp, 'catalyzer_notes', 'notes_count', 'تعداد جزوه در صفحه‌ی اصلی', array( 'default' => catalyzer_notes_default( 'notes_count' ) ) );
	catalyzer_cz_field( $wp, 'catalyzer_notes', 'notes_more_text', 'دکمه‌ی «همه‌ی جزوه‌ها» — متن', array( 'default' => catalyzer_notes_default( 'notes_more_text' ) ) );
	catalyzer_cz_field( $wp, 'catalyzer_notes', 'notes_more_url', 'دکمه‌ی «همه‌ی جزوه‌ها» — لینک', array( 'default' => '' ) );
	catalyzer_cz_field( $wp, 'catalyzer_notes', 'buy_note', 'متن راهنمای خرید (روی جعبه‌ی قفل)', array(
		'type'        => 'textarea',
		'default'     => catalyzer_notes_default( 'buy_note' ),
		'description' => 'همین متن روی هر جزوه و ویدیوی خریدنی دیده می‌شود.',
	) );

	/* ---------- آزمون‌ها ---------- */
	$add_section( 'catalyzer_exams', 'آزمون‌ها (سربرگ بخش)', 67 );
	catalyzer_cz_field( $wp, 'catalyzer_exams', 'exams_eyebrow', 'برچسب کوچک', array( 'default' => catalyzer_exams_default( 'exams_eyebrow' ) ) );
	catalyzer_cz_field( $wp, 'catalyzer_exams', 'exams_heading', 'تیتر', array( 'default' => catalyzer_exams_default( 'exams_heading' ) ) );
	catalyzer_cz_field( $wp, 'catalyzer_exams', 'exams_intro', 'توضیح کوتاه', array( 'default' => catalyzer_exams_default( 'exams_intro' ) ) );
	catalyzer_cz_field( $wp, 'catalyzer_exams', 'exams_count', 'تعداد آزمون در صفحه‌ی اصلی', array( 'default' => catalyzer_exams_default( 'exams_count' ) ) );
	catalyzer_cz_field( $wp, 'catalyzer_exams', 'exams_more_text', 'دکمه‌ی «همه‌ی آزمون‌ها» — متن', array( 'default' => catalyzer_exams_default( 'exams_more_text' ) ) );
	catalyzer_cz_field( $wp, 'catalyzer_exams', 'exams_more_url', 'دکمه‌ی «همه‌ی آزمون‌ها» — لینک', array( 'default' => '' ) );

	/* ---------- ویدیوها ---------- */
	$add_section( 'catalyzer_videos', 'ویدیوهای کلاس (سربرگ بخش)', 70 );
	catalyzer_cz_field( $wp, 'catalyzer_videos', 'videos_eyebrow', 'برچسب کوچک', array( 'default' => 'ویدیوهای کلاس' ) );
	catalyzer_cz_field( $wp, 'catalyzer_videos', 'videos_heading', 'تیتر', array( 'default' => 'نمونه‌ی تدریس و بخشی از جلسات ضبط‌شده' ) );
	catalyzer_cz_field( $wp, 'catalyzer_videos', 'videos_intro', 'توضیح کوتاه', array( 'default' => 'چند دقیقه از کلاس را ببین تا با فضای تدریس آشنا شوی.' ) );
	catalyzer_cz_field( $wp, 'catalyzer_videos', 'videos_count', 'تعداد ویدیو در صفحه‌ی اصلی', array( 'default' => '6' ) );
	catalyzer_cz_field( $wp, 'catalyzer_videos', 'videos_more_text', 'دکمه‌ی «همه‌ی ویدیوها» — متن', array( 'default' => 'همه‌ی ویدیوها' ) );
	catalyzer_cz_field( $wp, 'catalyzer_videos', 'videos_more_url', 'دکمه‌ی «همه‌ی ویدیوها» — لینک', array( 'default' => '' ) );

	/* ---------- رتبه‌برترها ---------- */
	$add_section( 'catalyzer_success', 'ویدیوهای رتبه‌برترها (سربرگ بخش)', 75 );
	catalyzer_cz_field( $wp, 'catalyzer_success', 'success_eyebrow', 'برچسب کوچک', array( 'default' => 'رتبه‌های برتر' ) );
	catalyzer_cz_field( $wp, 'catalyzer_success', 'success_heading', 'تیتر', array( 'default' => 'یک دقیقه با رتبه‌برترهایی که شاگرد کاتالیزور بودند' ) );
	catalyzer_cz_field( $wp, 'catalyzer_success', 'success_intro', 'توضیح کوتاه', array( 'default' => 'ویدیوهای کوتاه و بدون تعارف از داوطلب‌هایی که با همین متد به رتبه‌های برتر کشوری رسیدند.' ) );
	catalyzer_cz_field( $wp, 'catalyzer_success', 'success_count', 'تعداد ویدیو در صفحه‌ی اصلی', array( 'default' => '6' ) );
	catalyzer_cz_field( $wp, 'catalyzer_success', 'success_more_text', 'دکمه‌ی «همه‌ی ویدیوها» — متن', array( 'default' => 'همه‌ی رتبه‌برترها' ) );
	catalyzer_cz_field( $wp, 'catalyzer_success', 'success_more_url', 'دکمه‌ی «همه‌ی ویدیوها» — لینک', array( 'default' => '' ) );

	/* ---------- حساب کاربری ---------- */
	$add_section( 'catalyzer_account', 'ورود و حساب کاربری', 78 );
	catalyzer_cz_field( $wp, 'catalyzer_account', 'show_account_btn', 'نمایش دکمه‌ی ورود در هدر', array( 'type' => 'checkbox', 'default' => true ) );
	catalyzer_cz_field( $wp, 'catalyzer_account', 'login_btn_text', 'متن دکمه (کاربر واردنشده)', array( 'default' => 'ورود / ثبت‌نام' ) );
	catalyzer_cz_field( $wp, 'catalyzer_account', 'account_btn_text', 'متن دکمه (کاربر واردشده)', array( 'default' => 'پنل کاربری' ) );

	/* ---------- نظرات ---------- */
	$add_section( 'catalyzer_testimonials', 'نظرات (سربرگ بخش)', 80 );
	catalyzer_cz_field( $wp, 'catalyzer_testimonials', 'quotes_eyebrow', 'برچسب کوچک', array( 'default' => 'نظر دانش‌آموزان' ) );
	catalyzer_cz_field( $wp, 'catalyzer_testimonials', 'quotes_heading', 'تیتر', array( 'default' => 'حرف‌هایی از داوطلب‌هایی که شیمی را جدی گرفتند' ) );

	/* ---------- فراخوان ---------- */
	$add_section( 'catalyzer_cta', 'بنر فراخوان', 90 );
	catalyzer_cz_field( $wp, 'catalyzer_cta', 'cta_heading', 'تیتر', array( 'default' => 'همین امروز شیمی‌ات را از نقطه‌ضعف به نقطه‌قوت تبدیل کن' ) );
	catalyzer_cz_field( $wp, 'catalyzer_cta', 'cta_text', 'توضیح', array( 'default' => 'برای مشاوره‌ی انتخاب دوره، پیام بده.' ) );
	catalyzer_cz_field( $wp, 'catalyzer_cta', 'cta_btn1_text', 'دکمه‌ی اول — متن', array( 'default' => 'ثبت‌نام و شروع' ) );
	catalyzer_cz_field( $wp, 'catalyzer_cta', 'cta_btn1_url', 'دکمه‌ی اول — لینک', array( 'default' => '' ) );
	catalyzer_cz_field( $wp, 'catalyzer_cta', 'cta_btn2_text', 'دکمه‌ی دوم — متن', array( 'default' => 'دیدن دوره‌ها' ) );
	catalyzer_cz_field( $wp, 'catalyzer_cta', 'cta_btn2_url', 'دکمه‌ی دوم — لینک', array( 'default' => '#courses' ) );

	/* ---------- تماس با ما ---------- */
	$add_section( 'catalyzer_contact', 'تماس با ما', 100 );
	catalyzer_cz_field( $wp, 'catalyzer_contact', 'contact_eyebrow', 'برچسب کوچک', array( 'default' => catalyzer_contact_default( 'contact_eyebrow' ) ) );
	catalyzer_cz_field( $wp, 'catalyzer_contact', 'contact_heading', 'تیتر', array( 'default' => catalyzer_contact_default( 'contact_heading' ) ) );
	catalyzer_cz_field( $wp, 'catalyzer_contact', 'contact_intro', 'توضیح کوتاه', array( 'type' => 'textarea', 'default' => catalyzer_contact_default( 'contact_intro' ) ) );
	catalyzer_cz_field( $wp, 'catalyzer_contact', 'social_telegram', 'کانال تلگرام (لینک کامل)', array(
		'type'        => 'url',
		'default'     => catalyzer_contact_default( 'social_telegram' ),
		'description' => 'خالی بگذارید تا کارتش نمایش داده نشود.',
	) );
	catalyzer_cz_field( $wp, 'catalyzer_contact', 'support_telegram', 'آیدی تلگرام پشتیبانی', array(
		'default'     => catalyzer_contact_default( 'support_telegram' ),
		'description' => 'با @ یا بدون آن. همین آیدی روی جعبه‌ی «تهیه‌ی کد دسترسی» هم دیده می‌شود.',
	) );
	catalyzer_cz_field( $wp, 'catalyzer_contact', 'contact_phone', 'شماره تماس برای مشاوره', array(
		'default'     => catalyzer_contact_default( 'contact_phone' ),
		'description' => 'تا وقتی خالی است، کارت تماس تلفنی ساخته نمی‌شود.',
	) );
	catalyzer_cz_field( $wp, 'catalyzer_contact', 'social_instagram', 'آیدی یا لینک اینستاگرام', array(
		'default'     => catalyzer_contact_default( 'social_instagram' ),
		'description' => 'تا وقتی خالی است، کارت اینستاگرام ساخته نمی‌شود.',
	) );
	catalyzer_cz_field( $wp, 'catalyzer_contact', 'contact_email', 'ایمیل دریافت پیام‌ها (خالی = ایمیل مدیر سایت)', array( 'type' => 'email', 'default' => '' ) );
}
add_action( 'customize_register', 'catalyzer_customize_register' );
