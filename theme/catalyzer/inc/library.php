<?php
/**
 * کتابخانه‌ی سایت: جزوه‌ها و ویدیوها، قفلشان، و دانلود محافظت‌شده.
 *
 * یک موتور دسترسی مشترک برای هر نوع محتوای قفل‌دار:
 *
 *   ۱. هر جزوه یا ویدیو یا «رایگان» است یا «قفل‌دار».
 *   ۲. قفل با هر چیزی باز می‌شود که catalyzer_grant_access() را صدا بزند —
 *      امروز کدِ دسترسی (inc/access-codes.php)، فردا تأیید رسید یا درگاه.
 *   ۳. فایل جزوه هرگز آدرس عمومی ندارد؛ از uploads/catalyzer-library بیرون
 *      کشیده می‌شود و فقط از راه catalyzer_download_url() سرو می‌شود.
 *
 * دوره‌ها انبارِ خودشان را دارند (inc/enrollment.php) و دست‌نخورده می‌مانند؛
 * catalyzer_user_can_access() بر اساس نوع محتوا تصمیم می‌گیرد کجا را بخواند.
 *
 * @package Catalyzer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** متای کاربر: شناسه‌ی جزوه‌ها و ویدیوهایی که برایش باز شده‌اند. */
const CATALYZER_ACCESS_META = '_cat_access';

/** نام پوشه‌ی خصوصی داخل uploads. */
const CATALYZER_LIB_DIR = 'catalyzer-library';

/** کلید کوئری برای دانلود. */
const CATALYZER_DL_QUERY = 'catalyzer_file';

/* -------------------------------------------------------------------------
 * نوع محتوا: جزوه
 * ---------------------------------------------------------------------- */

/**
 * ثبت «جزوه» و دسته‌بندی‌اش.
 *
 * روی همان init‌ای سوار می‌شود که بقیه‌ی نوع‌های محتوا ثبت می‌شوند، با اولویت
 * پایین‌تر تا ترتیب منوی پیشخوان به‌هم نریزد.
 */
function catalyzer_register_library() {

	register_post_type( 'note', array(
		'labels'        => array(
			'name'          => 'جزوه‌ها',
			'singular_name' => 'جزوه',
			'add_new'       => 'افزودن جزوه',
			'add_new_item'  => 'افزودن جزوه‌ی جدید',
			'edit_item'     => 'ویرایش جزوه',
			'new_item'      => 'جزوه‌ی جدید',
			'view_item'     => 'مشاهده‌ی جزوه',
			'search_items'  => 'جستجوی جزوه',
			'not_found'     => 'جزوه‌ای یافت نشد',
			'menu_name'     => 'جزوه‌ها',
		),
		'public'        => true,
		'has_archive'   => true,
		'menu_icon'     => 'dashicons-media-document',
		'menu_position' => 21,
		'rewrite'       => array( 'slug' => 'notes', 'with_front' => false ),
		'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes' ),
		'show_in_rest'  => true,
	) );

	register_taxonomy( 'note_cat', array( 'note' ), array(
		'labels'            => array(
			'name'          => 'دسته‌بندی جزوه‌ها',
			'singular_name' => 'دسته‌بندی',
			'add_new_item'  => 'افزودن دسته‌بندی',
			'edit_item'     => 'ویرایش دسته‌بندی',
			'menu_name'     => 'دسته‌بندی‌ها',
		),
		'public'            => true,
		'hierarchical'      => false,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'note-category', 'with_front' => false ),
	) );
}
add_action( 'init', 'catalyzer_register_library', 5 );

/**
 * جزوه هم مثل بقیه با ویرایشگر کلاسیک ویرایش می‌شود.
 */
function catalyzer_library_classic_editor( $use_block, $post_type ) {
	return 'note' === $post_type ? false : $use_block;
}
add_filter( 'use_block_editor_for_post_type', 'catalyzer_library_classic_editor', 10, 2 );

/* -------------------------------------------------------------------------
 * قفل و دسترسی
 * ---------------------------------------------------------------------- */

/**
 * نوع‌های محتوایی که این موتور قفلشان را مدیریت می‌کند.
 *
 * @return string[]
 */
function catalyzer_lockable_types() {
	return (array) apply_filters( 'catalyzer_lockable_types', array( 'note', 'lesson' ) );
}

/**
 * آیا این محتوا قفل‌دار است؟
 *
 * پیش‌فرضِ نبودِ فیلد «رایگان» است: محتوایی که هنوز کسی تعیین تکلیفش نکرده
 * نباید ناخواسته قفل شود.
 *
 * @param int|WP_Post $post نوشته.
 * @return bool
 */
function catalyzer_is_locked( $post = null ) {
	$post = get_post( $post );
	if ( ! $post || ! in_array( $post->post_type, catalyzer_lockable_types(), true ) ) {
		return false;
	}
	return 'paid' === get_post_meta( $post->ID, '_cat_access_type', true );
}

/**
 * قیمت نوشته‌ی قفل‌دار، همان‌طور که تایپ شده.
 *
 * @param int|WP_Post $post نوشته.
 * @return string
 */
function catalyzer_price( $post = null ) {
	$post = get_post( $post );
	return $post ? (string) get_post_meta( $post->ID, '_cat_price', true ) : '';
}

/**
 * واحد پول نوشته.
 */
function catalyzer_currency( $post = null ) {
	$post = get_post( $post );
	$unit = $post ? (string) get_post_meta( $post->ID, '_cat_currency', true ) : '';
	return $unit ? $unit : 'تومان';
}

/**
 * شناسه‌ی محتواهایی که برای این کاربر باز شده‌اند.
 *
 * @param int $user_id شناسه‌ی کاربر (پیش‌فرض: کاربر جاری).
 * @return int[]
 */
function catalyzer_user_access_ids( $user_id = 0 ) {
	$user_id = $user_id ? (int) $user_id : get_current_user_id();
	if ( ! $user_id ) {
		return array();
	}

	$stored = get_user_meta( $user_id, CATALYZER_ACCESS_META, true );
	$ids    = is_array( $stored ) ? array_map( 'intval', $stored ) : array();

	$ids = array_values( array_filter( array_unique( $ids ), function ( $id ) {
		return $id > 0
			&& in_array( get_post_type( $id ), catalyzer_lockable_types(), true )
			&& 'publish' === get_post_status( $id );
	} ) );

	/**
	 * فیلتر فهرست دسترسی — نقطه‌ی اتصال برای هر منبع دیگری.
	 *
	 * @param int[] $ids     شناسه‌ها.
	 * @param int   $user_id شناسه‌ی کاربر.
	 */
	return array_values( array_unique( array_map( 'intval', (array) apply_filters( 'catalyzer_user_access', $ids, $user_id ) ) ) );
}

/**
 * آیا کاربر می‌تواند این محتوا را ببیند یا دانلود کند؟
 *
 * @param int|WP_Post $post    نوشته.
 * @param int         $user_id شناسه‌ی کاربر (پیش‌فرض: کاربر جاری).
 * @return bool
 */
function catalyzer_user_can_access( $post = null, $user_id = 0 ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return false;
	}
	if ( ! catalyzer_is_locked( $post ) ) {
		return true;
	}

	$user_id = $user_id ? (int) $user_id : get_current_user_id();
	if ( ! $user_id ) {
		return false;
	}
	if ( user_can( $user_id, 'edit_others_posts' ) ) {
		return true; // مدیر همیشه می‌بیند، وگرنه نمی‌تواند کارش را بررسی کند.
	}

	// جزوه یا ویدیویی که داخل یک دوره‌ی خریداری‌شده است هم باز است.
	$course_id = (int) get_post_meta( $post->ID, '_cat_part_of_course', true );
	if ( $course_id && function_exists( 'catalyzer_user_has_course' ) && catalyzer_user_has_course( $course_id, $user_id ) ) {
		return true;
	}

	return in_array( (int) $post->ID, catalyzer_user_access_ids( $user_id ), true );
}

/**
 * باز کردن یک محتوا برای کاربر.
 *
 * @param int $user_id شناسه‌ی کاربر.
 * @param int $post_id شناسه‌ی نوشته.
 * @param string $source از کجا — برای لاگ و برای قلاب‌ها.
 * @return bool آیا چیزی تغییر کرد.
 */
function catalyzer_grant_access( $user_id, $post_id, $source = '' ) {
	$user_id = (int) $user_id;
	$post_id = (int) $post_id;
	if ( ! $user_id || ! $post_id || ! in_array( get_post_type( $post_id ), catalyzer_lockable_types(), true ) ) {
		return false;
	}

	$stored = get_user_meta( $user_id, CATALYZER_ACCESS_META, true );
	$ids    = is_array( $stored ) ? array_map( 'intval', $stored ) : array();
	if ( in_array( $post_id, $ids, true ) ) {
		return false;
	}

	$ids[] = $post_id;
	update_user_meta( $user_id, CATALYZER_ACCESS_META, array_values( array_unique( $ids ) ) );

	/**
	 * پس از باز شدن یک محتوا برای کاربر.
	 *
	 * @param int    $user_id شناسه‌ی کاربر.
	 * @param int    $post_id شناسه‌ی نوشته.
	 * @param string $source  منبع (code، receipt، gateway، admin …).
	 */
	do_action( 'catalyzer_access_granted', $user_id, $post_id, $source );
	return true;
}

/**
 * بستن یک محتوا برای کاربر.
 */
function catalyzer_revoke_access( $user_id, $post_id ) {
	$stored = get_user_meta( (int) $user_id, CATALYZER_ACCESS_META, true );
	$ids    = is_array( $stored ) ? array_map( 'intval', $stored ) : array();
	$next   = array_values( array_diff( $ids, array( (int) $post_id ) ) );
	if ( count( $next ) === count( $ids ) ) {
		return false;
	}
	update_user_meta( (int) $user_id, CATALYZER_ACCESS_META, $next );
	do_action( 'catalyzer_access_revoked', (int) $user_id, (int) $post_id );
	return true;
}

/* -------------------------------------------------------------------------
 * پوشه‌ی خصوصیِ فایل‌ها
 * ---------------------------------------------------------------------- */

/**
 * مسیر و آدرس پوشه‌ی خصوصی.
 *
 * @return array{dir:string,exists:bool}
 */
function catalyzer_library_dir() {
	$uploads = wp_get_upload_dir();
	$dir     = trailingslashit( $uploads['basedir'] ) . CATALYZER_LIB_DIR;
	return array( 'dir' => $dir, 'exists' => is_dir( $dir ) );
}

/**
 * ساخت پوشه‌ی خصوصی و بستنش به روی وب.
 *
 * دو لایه: .htaccess برای آپاچی و لایت‌اسپید، و index.php خالی تا فهرست
 * پوشه لو نرود اگر روزی .htaccess نادیده گرفته شد.
 *
 * @return bool آیا پوشه آماده است.
 */
function catalyzer_prepare_library_dir() {
	$lib = catalyzer_library_dir();
	$dir = $lib['dir'];

	if ( ! is_dir( $dir ) && ! wp_mkdir_p( $dir ) ) {
		return false;
	}

	$htaccess = $dir . '/.htaccess';
	$rules    = "# فایل‌های این پوشه فقط از راه قالب سرو می‌شوند.\nRequire all denied\n<IfModule !mod_authz_core.c>\n\tOrder allow,deny\n\tDeny from all\n</IfModule>\n";
	if ( ! file_exists( $htaccess ) || md5_file( $htaccess ) !== md5( $rules ) ) {
		file_put_contents( $htaccess, $rules ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	}

	$index = $dir . '/index.php';
	if ( ! file_exists( $index ) ) {
		file_put_contents( $index, "<?php\n// Silence is golden.\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	}

	return is_dir( $dir );
}

/**
 * جزوه‌ها دو فایل دارند: برگه‌ی خالی و همان برگه با پاسخ‌ها.
 *
 * هر دو زیر یک قفل‌اند — کسی که جزوه را خریده هر دو را می‌گیرد.
 *
 * @return array<string,array{label:string,meta:string}>
 */
function catalyzer_note_slots() {
	return array(
		'blank'   => array( 'label' => 'فایل سفید', 'meta' => '_cat_file_blank' ),
		'answers' => array( 'label' => 'پاسخنامه', 'meta' => '_cat_file_answers' ),
	);
}

/**
 * نام فایلِ ذخیره‌شده‌ی یک بخش از جزوه (بدون مسیر).
 *
 * @param int|WP_Post $post نوشته.
 * @param string      $slot blank یا answers.
 */
function catalyzer_note_filename( $post = null, $slot = 'blank' ) {
	$post  = get_post( $post );
	$slots = catalyzer_note_slots();
	if ( ! $post || ! isset( $slots[ $slot ] ) ) {
		return '';
	}
	return (string) get_post_meta( $post->ID, $slots[ $slot ]['meta'], true );
}

/**
 * مسیر کامل فایل روی دیسک، یا رشته‌ی خالی اگر نبود.
 */
function catalyzer_note_path( $post = null, $slot = 'blank' ) {
	$name = catalyzer_note_filename( $post, $slot );
	if ( ! $name ) {
		return '';
	}
	$lib  = catalyzer_library_dir();
	$path = $lib['dir'] . '/' . $name;
	return file_exists( $path ) ? $path : '';
}

/**
 * حجم فایل، خوانا و با رقم فارسی.
 */
function catalyzer_note_filesize( $post = null, $slot = 'blank' ) {
	$path = catalyzer_note_path( $post, $slot );
	if ( ! $path ) {
		return '';
	}
	$bytes = (int) filesize( $path );
	if ( $bytes <= 0 ) {
		return '';
	}
	$mb = $bytes / ( 1024 * 1024 );
	$n  = $mb >= 1 ? number_format_i18n( $mb, 1 ) . ' مگابایت' : number_format_i18n( max( 1, round( $bytes / 1024 ) ) ) . ' کیلوبایت';
	return function_exists( 'catalyzer_fa_digits' ) ? catalyzer_fa_digits( $n ) : $n;
}

/**
 * بخش‌هایی از این جزوه که واقعاً فایل دارند.
 *
 * @return array<string,array{label:string,size:string,url:string}>
 */
function catalyzer_note_available_files( $post = null ) {
	$post = get_post( $post );
	$out  = array();
	if ( ! $post ) {
		return $out;
	}
	foreach ( catalyzer_note_slots() as $slot => $info ) {
		if ( ! catalyzer_note_path( $post, $slot ) ) {
			continue;
		}
		$out[ $slot ] = array(
			'label' => $info['label'],
			'size'  => catalyzer_note_filesize( $post, $slot ),
			'url'   => catalyzer_download_url( $post, $slot ),
		);
	}
	return $out;
}

/**
 * آدرس دانلودِ محافظت‌شده.
 *
 * nonce به کاربر گره می‌خورد، پس آدرسِ کپی‌شده در دست دیگری کار نمی‌کند.
 */
function catalyzer_download_url( $post = null, $slot = 'blank' ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return '';
	}
	$url = add_query_arg(
		array(
			CATALYZER_DL_QUERY => $post->ID,
			'part'             => $slot,
		),
		home_url( '/' )
	);
	return wp_nonce_url( $url, 'catalyzer_download_' . $post->ID . '_' . $slot );
}

/* -------------------------------------------------------------------------
 * سرو کردن فایل
 * ---------------------------------------------------------------------- */

/**
 * ثبت کلید کوئری دانلود.
 */
function catalyzer_library_query_vars( $vars ) {
	$vars[] = CATALYZER_DL_QUERY;
	$vars[] = 'part';
	return $vars;
}
add_filter( 'query_vars', 'catalyzer_library_query_vars' );

/**
 * تحویل فایل، بعد از بررسی قفل.
 *
 * پیش از هر خروجیِ دیگری روی template_redirect می‌نشیند تا هدرها دست‌نخورده
 * بمانند.
 */
function catalyzer_serve_download() {
	$post_id = (int) get_query_var( CATALYZER_DL_QUERY );
	if ( ! $post_id ) {
		return;
	}

	$slot  = (string) get_query_var( 'part' );
	$slots = catalyzer_note_slots();
	if ( ! isset( $slots[ $slot ] ) ) {
		$slot = 'blank';
	}

	$nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'catalyzer_download_' . $post_id . '_' . $slot ) ) {
		wp_die( esc_html__( 'این لینک دانلود منقضی شده است. صفحه‌ی جزوه را دوباره باز کن.', 'catalyzer' ), 403 );
	}

	$post = get_post( $post_id );
	if ( ! $post || 'note' !== $post->post_type || 'publish' !== $post->post_status ) {
		wp_die( esc_html__( 'این جزوه در دسترس نیست.', 'catalyzer' ), 404 );
	}

	if ( ! catalyzer_user_can_access( $post ) ) {
		wp_die( esc_html__( 'این جزوه برای حساب تو باز نشده است.', 'catalyzer' ), 403 );
	}

	$path = catalyzer_note_path( $post, $slot );
	if ( ! $path ) {
		wp_die( esc_html__( 'فایل این جزوه هنوز بارگذاری نشده است.', 'catalyzer' ), 404 );
	}

	$name = sanitize_file_name( get_the_title( $post ) . ' — ' . $slots[ $slot ]['label'] );
	$name = $name ? $name . '.pdf' : basename( $path );

	/**
	 * پیش از تحویل فایل — شمارنده و هر لاگ دیگری اینجا سوار می‌شود.
	 *
	 * @param int    $post_id شناسه‌ی جزوه.
	 * @param string $slot    کدام فایل.
	 */
	do_action( 'catalyzer_downloaded', $post->ID, $slot );

	nocache_headers();
	header( 'Content-Type: application/pdf' );
	header( 'Content-Disposition: attachment; filename="' . rawurlencode( $name ) . '"; filename*=UTF-8\'\'' . rawurlencode( $name ) );
	header( 'Content-Length: ' . filesize( $path ) );
	header( 'X-Content-Type-Options: nosniff' );

	if ( function_exists( 'ob_get_level' ) ) {
		while ( ob_get_level() ) {
			ob_end_clean();
		}
	}

	readfile( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	exit;
}
add_action( 'template_redirect', 'catalyzer_serve_download', 1 );

/* -------------------------------------------------------------------------
 * بارگذاری فایل در پیشخوان
 * ---------------------------------------------------------------------- */

/**
 * فرم ویرایش نوشته باید multipart باشد وگرنه فایل نمی‌رسد.
 */
function catalyzer_library_form_enctype() {
	global $post;
	if ( $post && 'note' === $post->post_type ) {
		echo ' enctype="multipart/form-data"';
	}
}
add_action( 'post_edit_form_tag', 'catalyzer_library_form_enctype' );

/**
 * جعبه‌ی «فایل‌های جزوه».
 */
function catalyzer_library_file_box() {
	add_meta_box( 'catalyzer_note_files', 'فایل‌های جزوه', 'catalyzer_render_file_box', 'note', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'catalyzer_library_file_box' );

/**
 * نمایش جعبه‌ی فایل‌ها.
 *
 * @param WP_Post $post نوشته.
 */
function catalyzer_render_file_box( $post ) {
	wp_nonce_field( 'catalyzer_note_files', 'catalyzer_note_files_nonce' );

	$max = size_format( wp_max_upload_size() );
	echo '<p style="margin:0 0 12px;color:#666">فایل‌ها بیرون از دسترس عمومی ذخیره می‌شوند و فقط از راه صفحه‌ی جزوه دانلود می‌شوند. بیشترین حجم مجاز این سرور: ' . esc_html( $max ) . '</p>';

	echo '<div style="display:grid;gap:18px">';
	foreach ( catalyzer_note_slots() as $slot => $info ) {
		$current = catalyzer_note_filename( $post, $slot );
		$size    = catalyzer_note_filesize( $post, $slot );
		$id      = 'cat_file_' . $slot;

		echo '<div style="border:1px solid #dcdcde;border-radius:6px;padding:12px">';
		echo '<p style="margin:0 0 8px;font-weight:600">' . esc_html( $info['label'] ) . '</p>';

		if ( $current ) {
			echo '<p style="margin:0 0 8px">بارگذاری‌شده: <code>' . esc_html( $current ) . '</code>';
			if ( $size ) {
				echo ' <span style="color:#666">(' . esc_html( $size ) . ')</span>';
			}
			echo '</p>';
			echo '<p style="margin:0 0 8px"><label><input type="checkbox" name="cat_file_delete[]" value="' . esc_attr( $slot ) . '"> حذف این فایل</label></p>';
		} else {
			echo '<p style="margin:0 0 8px;color:#666">هنوز فایلی ندارد.</p>';
		}

		echo '<input type="file" id="' . esc_attr( $id ) . '" name="' . esc_attr( $id ) . '" accept="application/pdf">';
		if ( $current ) {
			echo '<p style="margin:6px 0 0;color:#666">فایل تازه جای فایل فعلی را می‌گیرد.</p>';
		}
		echo '</div>';
	}
	echo '</div>';
}

/**
 * ذخیره‌ی فایل‌های جزوه.
 *
 * فایل مستقیم داخل پوشه‌ی خصوصی می‌نشیند و هرگز وارد کتابخانه‌ی رسانه نمی‌شود،
 * چون هر چیزی که در کتابخانه‌ی رسانه باشد یک آدرس عمومی هم دارد.
 *
 * @param int $post_id شناسه‌ی نوشته.
 */
function catalyzer_save_note_files( $post_id ) {
	if ( ! isset( $_POST['catalyzer_note_files_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['catalyzer_note_files_nonce'] ), 'catalyzer_note_files' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) || 'note' !== get_post_type( $post_id ) ) {
		return;
	}
	if ( ! catalyzer_prepare_library_dir() ) {
		return;
	}

	$lib     = catalyzer_library_dir();
	$deletes = isset( $_POST['cat_file_delete'] ) ? array_map( 'sanitize_key', (array) wp_unslash( $_POST['cat_file_delete'] ) ) : array();

	foreach ( catalyzer_note_slots() as $slot => $info ) {

		// حذف خواسته‌شده.
		if ( in_array( $slot, $deletes, true ) ) {
			$old = catalyzer_note_path( $post_id, $slot );
			if ( $old ) {
				wp_delete_file( $old );
			}
			delete_post_meta( $post_id, $info['meta'] );
		}

		$field = 'cat_file_' . $slot;
		if ( empty( $_FILES[ $field ]['name'] ) || ! empty( $_FILES[ $field ]['error'] ) ) {
			continue;
		}

		$check = wp_check_filetype( sanitize_file_name( $_FILES[ $field ]['name'] ), array( 'pdf' => 'application/pdf' ) );
		if ( 'pdf' !== $check['ext'] ) {
			continue; // فقط PDF.
		}

		$name = sprintf( 'note-%d-%s-%s.pdf', (int) $post_id, $slot, wp_generate_password( 8, false, false ) );
		$dest = $lib['dir'] . '/' . $name;

		if ( ! move_uploaded_file( $_FILES[ $field ]['tmp_name'], $dest ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions
			continue;
		}
		chmod( $dest, 0644 ); // phpcs:ignore WordPress.WP.AlternativeFunctions

		$old = catalyzer_note_path( $post_id, $slot );
		if ( $old && $old !== $dest ) {
			wp_delete_file( $old );
		}
		update_post_meta( $post_id, $info['meta'], $name );
	}
}
add_action( 'save_post', 'catalyzer_save_note_files' );

/**
 * وقتی جزوه برای همیشه حذف شد، فایل‌هایش هم بروند.
 *
 * @param int $post_id شناسه‌ی نوشته.
 */
function catalyzer_delete_note_files( $post_id ) {
	if ( 'note' !== get_post_type( $post_id ) ) {
		return;
	}
	foreach ( array_keys( catalyzer_note_slots() ) as $slot ) {
		$path = catalyzer_note_path( $post_id, $slot );
		if ( $path ) {
			wp_delete_file( $path );
		}
	}
}
add_action( 'before_delete_post', 'catalyzer_delete_note_files' );

/**
 * ستون‌های فهرست جزوه‌ها.
 */
function catalyzer_note_columns( $cols ) {
	$out = array();
	foreach ( $cols as $key => $label ) {
		$out[ $key ] = $label;
		if ( 'title' === $key ) {
			$out['cat_access'] = 'دسترسی';
			$out['cat_files']  = 'فایل‌ها';
		}
	}
	return $out;
}
add_filter( 'manage_note_posts_columns', 'catalyzer_note_columns' );

function catalyzer_note_column_content( $col, $post_id ) {
	if ( 'cat_access' === $col ) {
		if ( catalyzer_is_locked( $post_id ) ) {
			$price = catalyzer_price( $post_id );
			echo '<span style="color:#b26a00">خریدنی</span>';
			if ( $price ) {
				echo '<br><small>' . esc_html( $price . ' ' . catalyzer_currency( $post_id ) ) . '</small>';
			}
		} else {
			echo '<span style="color:#2a7d2e">رایگان</span>';
		}
		return;
	}
	if ( 'cat_files' === $col ) {
		$have = array_keys( catalyzer_note_available_files( $post_id ) );
		if ( ! $have ) {
			echo '—';
			return;
		}
		$slots  = catalyzer_note_slots();
		$labels = array_map( function ( $s ) use ( $slots ) {
			return $slots[ $s ]['label'];
		}, $have );
		echo esc_html( implode( '، ', $labels ) );
	}
}
add_action( 'manage_note_posts_custom_column', 'catalyzer_note_column_content', 10, 2 );

/* -------------------------------------------------------------------------
 * شمارش دانلود
 * ---------------------------------------------------------------------- */

/**
 * هر دانلود موفق یک واحد به شمارنده‌ی جزوه اضافه می‌کند.
 *
 * @param int $post_id شناسه‌ی جزوه.
 */
function catalyzer_count_download( $post_id ) {
	$n = (int) get_post_meta( $post_id, '_cat_downloads', true );
	update_post_meta( $post_id, '_cat_downloads', $n + 1 );
}
add_action( 'catalyzer_downloaded', 'catalyzer_count_download' );
