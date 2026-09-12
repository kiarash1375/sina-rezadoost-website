<?php
/**
 * نوع‌های محتوای سفارشی و فیلدهای متا.
 *
 * @package Catalyzer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ثبت نوع‌های محتوا: دوره، ویدیوی کلاس، نظر، سرنخ (لید).
 */
function catalyzer_register_post_types() {

	register_post_type( 'course', array(
		'labels'        => array(
			'name'               => 'دوره‌ها و پکیج‌ها',
			'singular_name'      => 'دوره',
			'add_new'            => 'افزودن دوره',
			'add_new_item'       => 'افزودن دوره‌ی جدید',
			'edit_item'          => 'ویرایش دوره',
			'new_item'           => 'دوره‌ی جدید',
			'view_item'          => 'مشاهده‌ی دوره',
			'search_items'       => 'جستجوی دوره',
			'not_found'          => 'دوره‌ای یافت نشد',
			'menu_name'          => 'دوره‌ها',
		),
		'public'        => true,
		'has_archive'   => true,
		'menu_icon'     => 'dashicons-welcome-learn-more',
		'menu_position' => 20,
		'rewrite'       => array( 'slug' => 'courses', 'with_front' => false ),
		'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes' ),
		'show_in_rest'  => true,
	) );

	register_post_type( 'lesson', array(
		'labels'        => array(
			'name'          => 'ویدیوهای کلاس',
			'singular_name' => 'ویدیوی کلاس',
			'add_new'       => 'افزودن ویدیو',
			'add_new_item'  => 'افزودن ویدیوی جدید',
			'edit_item'     => 'ویرایش ویدیو',
			'view_item'     => 'مشاهده‌ی ویدیو',
			'menu_name'     => 'ویدیوهای کلاس',
		),
		'public'        => true,
		'has_archive'   => true,
		'menu_icon'     => 'dashicons-video-alt3',
		'menu_position' => 21,
		'rewrite'       => array( 'slug' => 'lessons', 'with_front' => false ),
		'supports'      => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
		'show_in_rest'  => true,
	) );

	register_post_type( 'testimonial', array(
		'labels'        => array(
			'name'          => 'نظرات دانش‌آموزان',
			'singular_name' => 'نظر',
			'add_new'       => 'افزودن نظر',
			'add_new_item'  => 'افزودن نظر جدید',
			'edit_item'     => 'ویرایش نظر',
			'menu_name'     => 'نظرات',
		),
		'public'        => false,
		'show_ui'       => true,
		'menu_icon'     => 'dashicons-format-quote',
		'menu_position' => 22,
		'supports'      => array( 'title', 'editor', 'page-attributes' ),
		'show_in_rest'  => true,
	) );

	register_post_type( 'success_video', array(
		'labels'        => array(
			'name'          => 'ویدیوهای رتبه‌برترها',
			'singular_name' => 'ویدیوی رتبه‌برتر',
			'add_new'       => 'افزودن ویدیو',
			'add_new_item'  => 'افزودن ویدیوی جدید',
			'edit_item'     => 'ویرایش ویدیو',
			'view_item'     => 'مشاهده‌ی ویدیو',
			'search_items'  => 'جستجوی ویدیو',
			'not_found'     => 'ویدیویی یافت نشد',
			'menu_name'     => 'رتبه‌برترها',
		),
		'public'        => true,
		'has_archive'   => true,
		'menu_icon'     => 'dashicons-awards',
		'menu_position' => 22,
		'rewrite'       => array( 'slug' => 'success-stories', 'with_front' => false ),
		'supports'      => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
		'show_in_rest'  => true,
	) );

	register_post_type( 'catalyzer_lead', array(
		'labels'        => array(
			'name'          => 'درخواست‌های مشاوره',
			'singular_name' => 'درخواست مشاوره',
			'edit_item'     => 'مشاهده‌ی درخواست',
			'menu_name'     => 'درخواست‌های مشاوره',
		),
		'public'          => false,
		'show_ui'         => true,
		'menu_icon'       => 'dashicons-email-alt',
		'menu_position'   => 23,
		'capability_type' => 'post',
		'map_meta_cap'    => true,
		'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
		'supports'        => array( 'title', 'editor' ),
	) );
}
add_action( 'init', 'catalyzer_register_post_types' );

/**
 * برای نوع‌های محتوای این قالب از ویرایشگر کلاسیک استفاده می‌شود.
 *
 * این محتواها فقط «عنوان + فیلدهای مشخصات + تصویر شاخص» هستند و ویرایشگر بلوکی
 * چیزی به آن‌ها اضافه نمی‌کند؛ در عوض کلاسیک سبک‌تر است و به بارگذاری کامل
 * بسته‌های جاوااسکریپت ویرایشگر بلوکی وابسته نیست.
 */
function catalyzer_use_classic_editor( $use_block, $post_type ) {
	if ( in_array( $post_type, array( 'course', 'lesson', 'success_video', 'testimonial', 'catalyzer_lead' ), true ) ) {
		return false;
	}
	return $use_block;
}
add_filter( 'use_block_editor_for_post_type', 'catalyzer_use_classic_editor', 10, 2 );

/**
 * تعریف فیلدهای متا.
 *
 * @return array<string,array>
 */
function catalyzer_meta_fields() {
	return array(
		'course' => array(
			'title'  => 'مشخصات دوره',
			'fields' => array(
				'_cat_subtitle'  => array( 'label' => 'زیرعنوان کوتاه', 'type' => 'text' ),
				'_cat_price'     => array( 'label' => 'قیمت (مثلاً ۱٬۸۵۰٬۰۰۰)', 'type' => 'text' ),
				'_cat_old_price' => array( 'label' => 'قیمت پیشین (خط‌خورده، اختیاری)', 'type' => 'text' ),
				'_cat_currency'  => array( 'label' => 'واحد پول', 'type' => 'text', 'default' => 'تومان' ),
				'_cat_features'  => array( 'label' => 'ویژگی‌ها (هر خط یک مورد)', 'type' => 'textarea' ),
				'_cat_badge'     => array( 'label' => 'برچسب گوشه (مثلاً «پرطرفدار»)', 'type' => 'text' ),
				'_cat_featured'  => array( 'label' => 'کارت ویژه (برجسته)', 'type' => 'checkbox' ),
				'_cat_btn_text'  => array( 'label' => 'متن دکمه', 'type' => 'text', 'default' => 'ثبت‌نام' ),
				'_cat_btn_url'   => array( 'label' => 'لینک دکمه (صفحه‌ی محصول ووکامرس یا هر آدرس)', 'type' => 'text' ),
				'_cat_wc_product' => array( 'label' => 'شناسه‌ی محصول ووکامرس (برای باز شدن خودکار دوره پس از خرید)', 'type' => 'text' ),
			),
		),
		'lesson' => array(
			'title'  => 'مشخصات ویدیو',
			'fields' => array(
				'_cat_video_url' => array( 'label' => 'آدرس ویدیو (یوتیوب / اپارات / mp4)', 'type' => 'text' ),
				'_cat_duration'  => array( 'label' => 'مدت زمان (مثلاً ۱۴:۲۰)', 'type' => 'text' ),
				'_cat_category'  => array( 'label' => 'برچسب دسته (مثلاً «دوازدهم · فصل ۳»)', 'type' => 'text' ),
				'_cat_free'      => array( 'label' => 'نمونه‌ی رایگان', 'type' => 'checkbox' ),
			),
		),
		'success_video' => array(
			'title'  => 'مشخصات ویدیوی رتبه‌برتر',
			'note'   => 'عنوانِ نوشته = نام دانش‌آموز. متنِ نوشته = توضیح کوتاه (اختیاری). تصویر شاخص = بندانگشتیِ ویدیو.',
			'fields' => array(
				'_cat_sv_url'      => array( 'label' => 'آدرس ویدیو (یوتیوب / اپارات / mp4)', 'type' => 'text' ),
				'_cat_sv_rank'     => array( 'label' => 'رتبه‌ی کنکور (مثلاً ۳ کشوری)', 'type' => 'text' ),
				'_cat_sv_field'    => array( 'label' => 'رشته (مثلاً تجربی)', 'type' => 'text' ),
				'_cat_sv_year'     => array( 'label' => 'سال کنکور (مثلاً ۱۴۰۳)', 'type' => 'text' ),
				'_cat_sv_duration' => array( 'label' => 'مدت زمان (مثلاً ۰:۵۸)', 'type' => 'text' ),
				'_cat_sv_quote'    => array( 'label' => 'جمله‌ی کوتاه زیر کارت', 'type' => 'textarea' ),
			),
		),
		'testimonial' => array(
			'title'  => 'راهنما',
			'fields' => array(),
			'note'   => 'عنوانِ نوشته = خطِ معرفی (مثلاً «داوطلب تجربی، کنکور ۱۴۰۳»). متنِ نوشته = خودِ نقل‌قول.',
		),
		'catalyzer_lead' => array(
			'title'  => 'اطلاعات درخواست',
			'fields' => array(
				'_cat_lead_phone' => array( 'label' => 'شماره تماس', 'type' => 'text', 'readonly' => true ),
				'_cat_lead_field' => array( 'label' => 'رشته', 'type' => 'text', 'readonly' => true ),
			),
		),
	);
}

/**
 * افزودن متاباکس‌ها.
 */
function catalyzer_add_meta_boxes() {
	foreach ( catalyzer_meta_fields() as $screen => $box ) {
		add_meta_box(
			'catalyzer_' . $screen,
			$box['title'],
			'catalyzer_render_meta_box',
			$screen,
			'normal',
			'high',
			$box
		);
	}
}
add_action( 'add_meta_boxes', 'catalyzer_add_meta_boxes' );

/**
 * نمایش متاباکس.
 */
function catalyzer_render_meta_box( $post, $meta ) {
	$box = $meta['args'];
	wp_nonce_field( 'catalyzer_meta', 'catalyzer_meta_nonce' );

	if ( ! empty( $box['note'] ) ) {
		echo '<p style="margin:0 0 12px;color:#666">' . esc_html( $box['note'] ) . '</p>';
	}

	echo '<div style="display:grid;gap:14px">';
	foreach ( $box['fields'] as $key => $field ) {
		$value    = get_post_meta( $post->ID, $key, true );
		$default  = isset( $field['default'] ) ? $field['default'] : '';
		$value    = ( '' === $value && '' !== $default ) ? $default : $value;
		$readonly = ! empty( $field['readonly'] ) ? ' readonly' : '';
		$id       = esc_attr( $key );

		echo '<p style="margin:0"><label for="' . $id . '" style="display:block;font-weight:600;margin-bottom:4px">' . esc_html( $field['label'] ) . '</label>';

		if ( 'textarea' === $field['type'] ) {
			echo '<textarea id="' . $id . '" name="' . $id . '" rows="5" class="widefat"' . $readonly . '>' . esc_textarea( $value ) . '</textarea>';
		} elseif ( 'checkbox' === $field['type'] ) {
			echo '<label><input type="checkbox" id="' . $id . '" name="' . $id . '" value="1"' . checked( $value, '1', false ) . '> بله</label>';
		} else {
			echo '<input type="text" id="' . $id . '" name="' . $id . '" value="' . esc_attr( $value ) . '" class="widefat"' . $readonly . '>';
		}
		echo '</p>';
	}
	echo '</div>';
}

/**
 * ذخیره‌ی متاباکس.
 */
function catalyzer_save_meta( $post_id ) {
	if ( ! isset( $_POST['catalyzer_meta_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['catalyzer_meta_nonce'] ), 'catalyzer_meta' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$all = catalyzer_meta_fields();
	$type = get_post_type( $post_id );
	if ( ! isset( $all[ $type ] ) ) {
		return;
	}

	foreach ( $all[ $type ]['fields'] as $key => $field ) {
		if ( ! empty( $field['readonly'] ) ) {
			continue;
		}
		if ( 'checkbox' === $field['type'] ) {
			update_post_meta( $post_id, $key, isset( $_POST[ $key ] ) ? '1' : '' );
			continue;
		}
		if ( ! isset( $_POST[ $key ] ) ) {
			continue;
		}
		$raw = wp_unslash( $_POST[ $key ] );
		if ( 'textarea' === $field['type'] ) {
			update_post_meta( $post_id, $key, sanitize_textarea_field( $raw ) );
		} elseif ( '_cat_btn_url' === $key || '_cat_video_url' === $key || '_cat_sv_url' === $key ) {
			update_post_meta( $post_id, $key, esc_url_raw( trim( $raw ) ) );
		} else {
			update_post_meta( $post_id, $key, sanitize_text_field( $raw ) );
		}
	}
}
add_action( 'save_post', 'catalyzer_save_meta' );

/**
 * ستون‌های ادمین برای درخواست‌های مشاوره.
 */
function catalyzer_lead_columns( $cols ) {
	return array(
		'cb'        => $cols['cb'],
		'title'     => 'نام',
		'cat_phone' => 'تلفن',
		'cat_field' => 'رشته',
		'date'      => 'تاریخ',
	);
}
add_filter( 'manage_catalyzer_lead_posts_columns', 'catalyzer_lead_columns' );

function catalyzer_lead_column_content( $col, $post_id ) {
	if ( 'cat_phone' === $col ) {
		echo esc_html( get_post_meta( $post_id, '_cat_lead_phone', true ) );
	} elseif ( 'cat_field' === $col ) {
		echo esc_html( get_post_meta( $post_id, '_cat_lead_field', true ) );
	}
}
add_action( 'manage_catalyzer_lead_posts_custom_column', 'catalyzer_lead_column_content', 10, 2 );

/**
 * فلاش کردن rewriteها هنگام فعال‌سازی قالب.
 */
function catalyzer_rewrite_flush() {
	catalyzer_register_post_types();
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'catalyzer_rewrite_flush' );
