<?php
/**
 * کتابخانه‌ی سایت: جزوه‌ها، آزمون‌ها و ویدیوها، قفلشان، و دانلود محافظت‌شده.
 *
 * یک موتور دسترسی مشترک برای هر نوع محتوای قفل‌دار:
 *
 *   ۱. هر جزوه یا آزمون یا ویدیو یا «رایگان» است یا «قفل‌دار».
 *   ۲. قفل با هر چیزی باز می‌شود که catalyzer_grant_access() را صدا بزند —
 *      امروز کدِ دسترسی (inc/access-codes.php)، فردا تأیید رسید یا درگاه.
 *   ۳. فایل جزوه هرگز آدرس عمومی ندارد؛ بیرون از ریشه‌ی وب ذخیره می‌شود و
 *      فقط از راه catalyzer_download_url() سرو می‌شود.
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

/** نام پوشه‌ی فایل‌ها. */
const CATALYZER_LIB_DIR = 'catalyzer-library';

/** کلید کوئری برای دانلود. */
const CATALYZER_DL_QUERY = 'catalyzer_file';

/* -------------------------------------------------------------------------
 * نوع محتوا: جزوه و آزمون
 * ---------------------------------------------------------------------- */

/**
 * ثبت «جزوه» و «آزمون» و دسته‌بندی‌هایشان.
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
			'featured_image'     => 'عکس جزوه',
			'set_featured_image' => 'انتخاب عکس جزوه',
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

	register_post_type( 'exam', array(
		'labels'        => array(
			'name'               => 'آزمون‌ها',
			'singular_name'      => 'آزمون',
			'add_new'            => 'افزودن آزمون',
			'add_new_item'       => 'افزودن آزمون جدید',
			'edit_item'          => 'ویرایش آزمون',
			'new_item'           => 'آزمون جدید',
			'view_item'          => 'مشاهده‌ی آزمون',
			'search_items'       => 'جستجوی آزمون',
			'not_found'          => 'آزمونی یافت نشد',
			'menu_name'          => 'آزمون‌ها',
			'featured_image'     => 'عکس آزمون',
			'set_featured_image' => 'انتخاب عکس آزمون',
		),
		'public'        => true,
		'has_archive'   => true,
		'menu_icon'     => 'dashicons-clipboard',
		'menu_position' => 21,
		'rewrite'       => array( 'slug' => 'exams', 'with_front' => false ),
		'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes' ),
		'show_in_rest'  => true,
	) );

	register_taxonomy( 'exam_cat', array( 'exam' ), array(
		'labels'            => array(
			'name'          => 'دسته‌بندی آزمون‌ها',
			'singular_name' => 'دسته‌بندی',
			'add_new_item'  => 'افزودن دسته‌بندی',
			'edit_item'     => 'ویرایش دسته‌بندی',
			'menu_name'     => 'دسته‌بندی‌ها',
		),
		'public'            => true,
		'hierarchical'      => false,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'exam-category', 'with_front' => false ),
	) );
}
add_action( 'init', 'catalyzer_register_library', 5 );

/**
 * برچسب «تصویر شاخص» را برای هر نوع، به زبان خودش می‌نویسد.
 *
 * ادمین باید بدون فکر کردن بفهمد این جعبه همان عکسِ روی کارت است.
 *
 * @param string $content متن فعلی جعبه.
 * @return string
 */
function catalyzer_featured_image_hint( $content ) {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	$hints  = array(
		'note'   => 'عکس روی کارتِ جزوه — جلد یا تصویر صفحه‌ی اول.',
		'exam'   => 'عکس روی کارتِ آزمون.',
		'course' => 'عکس روی کارتِ دوره.',
		'lesson' => 'بندانگشتی ویدیو. اگر خالی بماند، از آپارات گرفته می‌شود.',
	);
	if ( $screen && isset( $hints[ $screen->post_type ] ) ) {
		$content .= '<p style="margin-top:8px;color:#666">' . esc_html( $hints[ $screen->post_type ] ) . '</p>';
	}
	return $content;
}
add_filter( 'admin_post_thumbnail_html', 'catalyzer_featured_image_hint' );

/**
 * جزوه هم مثل بقیه با ویرایشگر کلاسیک ویرایش می‌شود.
 */
function catalyzer_library_classic_editor( $use_block, $post_type ) {
	return in_array( $post_type, array( 'note', 'exam' ), true ) ? false : $use_block;
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
	return (array) apply_filters( 'catalyzer_lockable_types', array( 'note', 'exam', 'lesson' ) );
}

/**
 * نوع‌هایی که فایل PDF دارند: جزوه و آزمون.
 *
 * @return string[]
 */
function catalyzer_filed_types() {
	return (array) apply_filters( 'catalyzer_filed_types', array( 'note', 'exam' ) );
}

/**
 * بخش‌های کتابخانه، در یک جا — منوی پنل کاربری و میان‌برها از همین می‌خوانند.
 *
 * @return array<string,array{label:string,single:string,icon:string}>
 */
function catalyzer_library_sections() {
	return array(
		'note'   => array( 'label' => 'جزوه‌ها',        'single' => 'جزوه',        'icon' => 'pdf' ),
		'exam'   => array( 'label' => 'آزمون‌ها',       'single' => 'آزمون',       'icon' => 'clipboard' ),
		'lesson' => array( 'label' => 'ویدیوهای کلاس', 'single' => 'ویدیوی کلاس', 'icon' => 'play-o' ),
		'course' => array( 'label' => 'دوره‌ها',         'single' => 'دوره',        'icon' => 'hexagon' ),
	);
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
 * مسیر پوشه‌ای که فایل‌ها در آن می‌نشینند.
 *
 * ترتیب انتخاب، از امن به ناامن:
 *
 *   ۱. مسیری که در wp-config یا با فیلتر تعیین شده باشد.
 *   ۲. یک پله بیرونِ ریشه‌ی وب — آنجا هیچ آدرسی به فایل نمی‌رسد، نقطه.
 *   ۳. آخرین راه: داخل uploads با .htaccess.
 *
 * راه سوم روی این هاست کافی نبود: لایت‌اسپید فایل ایستا را پیش از خواندن
 * .htaccess تحویل می‌داد و PDFِ «خریدنی» با آدرس مستقیم دانلود می‌شد. برای
 * همین پیش‌فرض بیرون از ریشه‌ی وب است و اگر مجبور شدیم به uploads برگردیم،
 * قالب در پیشخوان هشدار می‌دهد.
 *
 * @return array{dir:string,exposed:bool}
 */
function catalyzer_library_dir() {

	$custom = defined( 'CATALYZER_LIB_PATH' ) ? CATALYZER_LIB_PATH : '';
	/**
	 * مسیر دلخواه برای فایل‌های کتابخانه.
	 *
	 * @param string $custom مسیر مطلق، یا رشته‌ی خالی برای رفتار پیش‌فرض.
	 */
	$custom = (string) apply_filters( 'catalyzer_library_path', $custom );
	if ( $custom ) {
		$custom = untrailingslashit( $custom );
		return array( 'dir' => $custom, 'exposed' => catalyzer_path_is_public( $custom ) );
	}

	$outside = dirname( untrailingslashit( ABSPATH ) ) . '/' . CATALYZER_LIB_DIR;
	if ( is_dir( $outside ) || ( is_writable( dirname( $outside ) ) && wp_mkdir_p( $outside ) ) ) {
		return array( 'dir' => $outside, 'exposed' => false );
	}

	$uploads = wp_get_upload_dir();
	return array(
		'dir'     => trailingslashit( $uploads['basedir'] ) . CATALYZER_LIB_DIR,
		'exposed' => true,
	);
}

/**
 * آیا این مسیر از راه وب قابل دسترسی است؟
 *
 * فقط یک مقایسه‌ی مسیر است: هر چیزی داخل ریشه‌ی وب، آدرس دارد.
 *
 * @param string $path مسیر مطلق.
 * @return bool
 */
function catalyzer_path_is_public( $path ) {
	$root = wp_normalize_path( untrailingslashit( ABSPATH ) );
	$path = wp_normalize_path( untrailingslashit( $path ) );
	return 0 === strpos( $path . '/', $root . '/' );
}

/**
 * ساخت پوشه و بستنش، به‌اضافه‌ی کوچ دادن فایل‌های نسخه‌ی قبل.
 *
 * .htaccess و index.php هنوز نوشته می‌شوند — روی سرورهایی که رعایتشان می‌کنند
 * یک لایه‌ی اضافه‌اند و ضرری ندارند — ولی دیگر تنها خط دفاع نیستند.
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

	catalyzer_migrate_library_files( $dir );

	return is_dir( $dir );
}

/**
 * فایل‌هایی که نسخه‌ی قبل داخل uploads گذاشته بود را به پوشه‌ی امن ببر.
 *
 * @param string $dir پوشه‌ی مقصد.
 */
function catalyzer_migrate_library_files( $dir ) {
	$uploads = wp_get_upload_dir();
	$old     = trailingslashit( $uploads['basedir'] ) . CATALYZER_LIB_DIR;

	if ( wp_normalize_path( $old ) === wp_normalize_path( $dir ) || ! is_dir( $old ) ) {
		return;
	}

	foreach ( (array) glob( $old . '/*.pdf' ) as $file ) {
		$target = $dir . '/' . basename( $file );
		if ( ! file_exists( $target ) ) {
			rename( $file, $target ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		} else {
			wp_delete_file( $file );
		}
	}
}

/**
 * اگر فایل‌ها ناچاراً جایی نشسته‌اند که آدرس عمومی دارد، مدیر باید بداند.
 */
function catalyzer_library_exposure_notice() {
	if ( ! current_user_can( 'edit_others_posts' ) ) {
		return;
	}
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || ( false === strpos( (string) $screen->id, 'note' ) && false === strpos( (string) $screen->id, 'exam' ) ) ) {
		return;
	}
	$lib = catalyzer_library_dir();
	if ( empty( $lib['exposed'] ) ) {
		return;
	}
	echo '<div class="notice notice-error"><p><strong>هشدار:</strong> فایل‌های جزوه‌ها داخل پوشه‌ی uploads ذخیره می‌شوند و ممکن است با آدرس مستقیم قابل دانلود باشند. برای رفعش یک خط به wp-config.php اضافه کن: <code>define( \'CATALYZER_LIB_PATH\', \'/مسیر/بیرون/از/public_html/catalyzer-library\' );</code></p></div>';
}
add_action( 'admin_notices', 'catalyzer_library_exposure_notice' );

/**
 * جزوه‌ها دو فایل دارند: برگه‌ی خالی و همان برگه با پاسخ‌ها.
 *
 * هر دو زیر یک قفل‌اند — کسی که جزوه را خریده هر دو را می‌گیرد.
 *
 * @return array<string,array{label:string,meta:string}>
 */
function catalyzer_note_slots( $post_type = 'note' ) {
	$first = 'exam' === $post_type ? 'برگه‌ی سؤال' : 'فایل سفید';
	return array(
		'blank'   => array( 'label' => $first,     'meta' => '_cat_file_blank' ),
		'answers' => array( 'label' => 'پاسخنامه', 'meta' => '_cat_file_answers' ),
	);
}

/**
 * خانه‌های فایلِ یک نوشته، با برچسب درستِ نوع خودش.
 *
 * @param int|WP_Post $post نوشته.
 */
function catalyzer_post_slots( $post = null ) {
	$post = get_post( $post );
	return catalyzer_note_slots( $post ? $post->post_type : 'note' );
}

/**
 * نام فایلِ ذخیره‌شده‌ی یک بخش از جزوه (بدون مسیر).
 *
 * @param int|WP_Post $post نوشته.
 * @param string      $slot blank یا answers.
 */
function catalyzer_note_filename( $post = null, $slot = 'blank' ) {
	$post  = get_post( $post );
	$slots = catalyzer_post_slots( $post );
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
	foreach ( catalyzer_post_slots( $post ) as $slot => $info ) {
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

	$slot = (string) get_query_var( 'part' );
	if ( ! in_array( $slot, array( 'blank', 'answers' ), true ) ) {
		$slot = 'blank';
	}

	$nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'catalyzer_download_' . $post_id . '_' . $slot ) ) {
		wp_die( esc_html__( 'این لینک دانلود منقضی شده است. صفحه‌ی جزوه را دوباره باز کن.', 'catalyzer' ), 403 );
	}

	$post = get_post( $post_id );
	if ( ! $post || ! in_array( $post->post_type, catalyzer_filed_types(), true ) || 'publish' !== $post->post_status ) {
		wp_die( esc_html__( 'این فایل در دسترس نیست.', 'catalyzer' ), 404 );
	}

	if ( ! catalyzer_user_can_access( $post ) ) {
		wp_die( esc_html__( 'این مورد برای حساب تو باز نشده است.', 'catalyzer' ), 403 );
	}

	$slots = catalyzer_post_slots( $post );
	$path  = catalyzer_note_path( $post, $slot );
	if ( ! $path ) {
		wp_die( esc_html__( 'فایل این مورد هنوز بارگذاری نشده است.', 'catalyzer' ), 404 );
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
	if ( $post && in_array( $post->post_type, catalyzer_filed_types(), true ) ) {
		echo ' enctype="multipart/form-data"';
	}
}
add_action( 'post_edit_form_tag', 'catalyzer_library_form_enctype' );

/**
 * جعبه‌ی «فایل‌های جزوه».
 */
function catalyzer_library_file_box() {
	$titles = array( 'note' => 'فایل‌های جزوه', 'exam' => 'فایل‌های آزمون' );
	foreach ( catalyzer_filed_types() as $type ) {
		$title = isset( $titles[ $type ] ) ? $titles[ $type ] : 'فایل‌ها';
		add_meta_box( 'catalyzer_note_files', $title, 'catalyzer_render_file_box', $type, 'normal', 'high' );
	}
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
	$lib = catalyzer_library_dir();
	echo '<p style="margin:0 0 12px;color:#666">فایل‌ها ' . ( empty( $lib['exposed'] ) ? 'بیرون از ریشه‌ی وب' : 'داخل uploads' ) . ' ذخیره می‌شوند و فقط از راه صفحه‌ی جزوه دانلود می‌شوند. بیشترین حجم مجاز این سرور: ' . esc_html( $max ) . '</p>';

	echo '<div style="display:grid;gap:18px">';
	foreach ( catalyzer_post_slots( $post ) as $slot => $info ) {
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
	if ( ! current_user_can( 'edit_post', $post_id ) || ! in_array( get_post_type( $post_id ), catalyzer_filed_types(), true ) ) {
		return;
	}
	if ( ! catalyzer_prepare_library_dir() ) {
		return;
	}

	$lib     = catalyzer_library_dir();
	$deletes = isset( $_POST['cat_file_delete'] ) ? array_map( 'sanitize_key', (array) wp_unslash( $_POST['cat_file_delete'] ) ) : array();

	foreach ( catalyzer_post_slots( $post_id ) as $slot => $info ) {

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

		$name = sprintf( '%s-%d-%s-%s.pdf', get_post_type( $post_id ), (int) $post_id, $slot, wp_generate_password( 8, false, false ) );
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
	if ( ! in_array( get_post_type( $post_id ), catalyzer_filed_types(), true ) ) {
		return;
	}
	foreach ( array( 'blank', 'answers' ) as $slot ) {
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
add_filter( 'manage_exam_posts_columns', 'catalyzer_note_columns' );

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
		$slots  = catalyzer_post_slots( $post_id );
		$labels = array_map( function ( $s ) use ( $slots ) {
			return $slots[ $s ]['label'];
		}, $have );
		echo esc_html( implode( '، ', $labels ) );
	}
}
add_action( 'manage_note_posts_custom_column', 'catalyzer_note_column_content', 10, 2 );
add_action( 'manage_exam_posts_custom_column', 'catalyzer_note_column_content', 10, 2 );

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
