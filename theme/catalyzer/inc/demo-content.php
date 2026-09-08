<?php
/**
 * داده‌های نمونه — برای اینکه همه‌ی بخش‌های سایت پر و قابل بررسی باشند.
 *
 * همه‌ی موارد ساخته‌شده با متای `_cat_demo` نشانه‌گذاری می‌شوند تا بتوان
 * یکجا حذفشان کرد. محتوای واقعی هرگز پاک نمی‌شود.
 *
 * @package Catalyzer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const CATALYZER_DEMO_FLAG = '_cat_demo';

/* -------------------------------------------------------------------------
 * تصویر نمونه
 * ---------------------------------------------------------------------- */

/**
 * ساخت یک تصویر بندانگشتیِ ساده و افزودن آن به کتابخانه‌ی رسانه.
 *
 * @return int شناسه‌ی پیوست، یا ۰ در صورت شکست.
 */
function catalyzer_demo_thumbnail( $seed, $label ) {
	if ( ! function_exists( 'imagecreatetruecolor' ) ) {
		return 0;
	}

	$w   = 720;
	$h   = 450;
	$img = imagecreatetruecolor( $w, $h );

	// پس‌زمینه‌ی گرادیانی تیره.
	$hue = ( $seed * 37 ) % 60;
	for ( $y = 0; $y < $h; $y++ ) {
		$t = $y / $h;
		$r = (int) ( 18 + $t * ( 34 + $hue / 4 ) );
		$g = (int) ( 17 + $t * 26 );
		$b = (int) ( 16 + $t * 20 );
		$c = imagecolorallocate( $img, $r, $g, $b );
		imageline( $img, 0, $y, $w, $y, $c );
	}

	// حلقه‌های کهربایی.
	$amber = imagecolorallocatealpha( $img, 232, 165, 58, 85 );
	for ( $i = 0; $i < 4; $i++ ) {
		$d = 150 + $i * 90;
		imageellipse( $img, (int) ( $w * 0.72 ), (int) ( $h * 0.42 ), $d, $d, $amber );
	}

	// شش‌ضلعی کوچک به‌عنوان نشانه.
	$points = array();
	$cx     = (int) ( $w * 0.28 );
	$cy     = (int) ( $h * 0.5 );
	for ( $i = 0; $i < 6; $i++ ) {
		$a        = deg2rad( 60 * $i - 30 );
		$points[] = (int) ( $cx + cos( $a ) * 78 );
		$points[] = (int) ( $cy + sin( $a ) * 78 );
	}
	$solid = imagecolorallocate( $img, 232, 165, 58 );
	imagepolygon( $img, $points, $solid );

	$white = imagecolorallocate( $img, 240, 236, 230 );
	imagestring( $img, 5, $cx - 24, $cy - 8, substr( (string) $label, 0, 8 ), $white );

	$upload = wp_upload_dir();
	if ( ! empty( $upload['error'] ) ) {
		imagedestroy( $img );
		return 0;
	}

	$filename = 'catalyzer-demo-' . $seed . '.jpg';
	$path     = trailingslashit( $upload['path'] ) . $filename;
	imagejpeg( $img, $path, 82 );
	imagedestroy( $img );

	$attachment_id = wp_insert_attachment( array(
		'post_mime_type' => 'image/jpeg',
		'post_title'     => 'تصویر نمونه ' . $seed,
		'post_status'    => 'inherit',
	), $path );

	if ( ! $attachment_id || is_wp_error( $attachment_id ) ) {
		return 0;
	}

	require_once ABSPATH . 'wp-admin/includes/image.php';
	wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $path ) );
	update_post_meta( $attachment_id, CATALYZER_DEMO_FLAG, '1' );

	return (int) $attachment_id;
}

/* -------------------------------------------------------------------------
 * تعریف داده‌ها
 * ---------------------------------------------------------------------- */

function catalyzer_demo_definitions() {
	$sample_mp4 = array(
		'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerBlazes.mp4',
		'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerEscapes.mp4',
		'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerFun.mp4',
		'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerJoyrides.mp4',
		'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerMeltdowns.mp4',
		'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ElephantsDream.mp4',
	);

	return array(

		'course' => array(
			array(
				'title'   => 'پکیج جامع شیمی کنکور — تجربی',
				'content' => 'کل شیمیِ پایه و دوازدهم، مفهوم تا تست، همراه با جزوه‌ی تایپ‌شده و آزمون مبحثی. مناسب داوطلبی که می‌خواهد شیمی را از صفر تا صد یکجا ببندد.',
				'meta'    => array(
					'_cat_subtitle'  => 'دهم تا دوازدهم · ۱۲۰ ساعت',
					'_cat_price'     => '۴٬۸۵۰٬۰۰۰',
					'_cat_old_price' => '۵٬۹۰۰٬۰۰۰',
					'_cat_features'  => "۱۲۰ ساعت ویدیوی کلاس\nجزوه‌ی تایپ‌شده‌ی هر فصل\n۲۴ آزمون مبحثی با پاسخ تشریحی\nپشتیبانی مستقیم در تلگرام\nدسترسی دائمی",
					'_cat_badge'     => 'پرطرفدار',
					'_cat_featured'  => '1',
					'_cat_btn_text'  => 'ثبت‌نام در پکیج جامع',
				),
			),
			array(
				'title'   => 'شیمی دوازدهم — فصل به فصل',
				'content' => 'تمرکز روی مباحث دوازدهم با بودجه‌بندی کنکور: الکتروشیمی، مولکول‌های حیاتی، سینتیک و تعادل.',
				'meta'    => array(
					'_cat_subtitle' => 'دوازدهم · ۴۵ ساعت',
					'_cat_price'    => '۲٬۳۵۰٬۰۰۰',
					'_cat_features' => "۴۵ ساعت ویدیو\nجزوه‌ی فصل‌های دوازدهم\n۸ آزمون مبحثی\nدسترسی یک‌ساله",
					'_cat_btn_text' => 'ثبت‌نام',
				),
			),
			array(
				'title'   => 'همایش جمع‌بندی نوروز',
				'content' => 'مرور فشرده‌ی کل شیمی در ۱۰ جلسه، با تمرکز روی تیپ سؤالات پرتکرار کنکور سال‌های اخیر.',
				'meta'    => array(
					'_cat_subtitle' => 'جمع‌بندی · ۱۰ جلسه',
					'_cat_price'    => '۹۸۰٬۰۰۰',
					'_cat_features' => "۱۰ جلسه‌ی فشرده\nخلاصه‌ی فرمول‌ها و نکات\nتست‌های پرتکرار ده سال اخیر",
					'_cat_badge'    => 'ویژه‌ی نوروز',
					'_cat_btn_text' => 'رزرو جایگاه',
				),
			),
			array(
				'title'   => 'آزمون‌های مبحثی شیمی',
				'content' => 'بسته‌ی آزمون با پاسخ تشریحی ویدیویی؛ برای سنجش تسلط بعد از هر مبحث.',
				'meta'    => array(
					'_cat_subtitle' => 'آزمون · ۲۴ آزمون',
					'_cat_price'    => '۶۵۰٬۰۰۰',
					'_cat_features' => "۲۴ آزمون مبحثی\nپاسخ تشریحی ویدیویی\nکارنامه و تحلیل عملکرد",
					'_cat_btn_text' => 'تهیه‌ی بسته',
				),
			),
		),

		'lesson' => array(
			array( 'title' => 'استوکیومتری — از مول تا محاسبات ترکیبی', 'meta' => array( '_cat_duration' => '۱۸:۴۰', '_cat_category' => 'دهم · فصل ۳', '_cat_free' => '1' ) ),
			array( 'title' => 'پیوند کووالانسی و ساختار لوویس', 'meta' => array( '_cat_duration' => '۲۲:۱۰', '_cat_category' => 'دهم · فصل ۲' ) ),
			array( 'title' => 'تعادل شیمیایی و اصل لوشاتلیه', 'meta' => array( '_cat_duration' => '۲۶:۰۵', '_cat_category' => 'یازدهم · فصل ۳' ) ),
			array( 'title' => 'اسید و باز — محاسبات pH', 'meta' => array( '_cat_duration' => '۳۱:۵۵', '_cat_category' => 'یازدهم · فصل ۳', '_cat_free' => '1' ) ),
			array( 'title' => 'الکتروشیمی — سلول گالوانی', 'meta' => array( '_cat_duration' => '۲۹:۳۰', '_cat_category' => 'دوازدهم · فصل ۲' ) ),
			array( 'title' => 'مولکول‌های حیاتی — قند و پروتئین', 'meta' => array( '_cat_duration' => '۲۴:۱۵', '_cat_category' => 'دوازدهم · فصل ۳' ) ),
		),

		'success_video' => array(
			array( 'title' => 'نگار محمدی', 'meta' => array( '_cat_sv_rank' => '۳ کشوری', '_cat_sv_field' => 'تجربی', '_cat_sv_year' => '۱۴۰۳', '_cat_sv_duration' => '۰:۵۸', '_cat_sv_quote' => 'شیمی ضعیف‌ترین درسم بود؛ با متد کاتالیزور شد بهترین درصدم.' ) ),
			array( 'title' => 'امیرحسین کریمی', 'meta' => array( '_cat_sv_rank' => '۶ کشوری', '_cat_sv_field' => 'تجربی', '_cat_sv_year' => '۱۴۰۳', '_cat_sv_duration' => '۱:۰۲', '_cat_sv_quote' => 'تفاوتش این بود که به‌جای حفظ کردن، فهمیدم چرا واکنش این‌طور پیش می‌رود.' ) ),
			array( 'title' => 'سارا رحیمی', 'meta' => array( '_cat_sv_rank' => '۹ کشوری', '_cat_sv_field' => 'ریاضی', '_cat_sv_year' => '۱۴۰۲', '_cat_sv_duration' => '۰:۴۹', '_cat_sv_quote' => 'تکنیک‌های زمان سر جلسه‌ی کنکور واقعاً جواب داد.' ) ),
			array( 'title' => 'محمد صادقی', 'meta' => array( '_cat_sv_rank' => '۲ منطقه', '_cat_sv_field' => 'تجربی', '_cat_sv_year' => '۱۴۰۲', '_cat_sv_duration' => '۱:۰۵', '_cat_sv_quote' => 'آزمون‌های مبحثی نگذاشت چیزی روی هوا بماند.' ) ),
			array( 'title' => 'فاطمه نوری', 'meta' => array( '_cat_sv_rank' => '۴ کشوری', '_cat_sv_field' => 'تجربی', '_cat_sv_year' => '۱۴۰۱', '_cat_sv_duration' => '۰:۵۲', '_cat_sv_quote' => 'پشتیبانی هفتگی باعث شد هیچ‌وقت از برنامه عقب نمانم.' ) ),
			array( 'title' => 'علی موسوی', 'meta' => array( '_cat_sv_rank' => '۱۲ کشوری', '_cat_sv_field' => 'ریاضی', '_cat_sv_year' => '۱۴۰۱', '_cat_sv_duration' => '۱:۰۰', '_cat_sv_quote' => 'جزوه‌ها دقیقاً همان چیزی بود که سر جلسه لازم داشتم.' ) ),
		),

		'testimonial' => array(
			array( 'title' => 'داوطلب تجربی، کنکور ۱۴۰۳', 'content' => 'قبل از این کلاس، شیمی برایم یک مشت فرمول پراکنده بود. الان می‌دانم هر فرمول از کجا می‌آید و کجا به کارم می‌آید.' ),
			array( 'title' => 'داوطلب ریاضی، کنکور ۱۴۰۳', 'content' => 'تست‌های ترکیبی همیشه من را زمین می‌زد. تکنیک‌های حل سریع کلاس، زمانم را نصف کرد.' ),
			array( 'title' => 'دانش‌آموز پایه‌ی یازدهم', 'content' => 'از یازدهم شروع کردم و همین باعث شد سال کنکور استرس شیمی نداشته باشم.' ),
			array( 'title' => 'داوطلب تجربی، کنکور ۱۴۰۲', 'content' => 'آزمون‌های مبحثی و کارنامه‌شان دقیقاً نشان می‌داد کجا ضعف دارم.' ),
			array( 'title' => 'مادر یکی از دانش‌آموزان', 'content' => 'پیگیری منظم پشتیبان‌ها خیال ما را راحت کرد؛ می‌دانستیم بچه عقب نمی‌ماند.' ),
			array( 'title' => 'داوطلب تجربی، کنکور ۱۴۰۱', 'content' => 'همایش جمع‌بندی نوروز بهترین تصمیم آن سالم بود.' ),
		),

		'catalyzer_lead' => array(
			array( 'title' => 'زهرا احمدی', 'content' => 'برای پکیج جامع تجربی می‌خواستم مشاوره بگیرم.', 'meta' => array( '_cat_lead_phone' => '09120000001', '_cat_lead_field' => 'تجربی' ) ),
			array( 'title' => 'رضا شریفی', 'content' => 'همایش نوروز هنوز ظرفیت دارد؟', 'meta' => array( '_cat_lead_phone' => '09120000002', '_cat_lead_field' => 'ریاضی' ) ),
			array( 'title' => 'مهسا کاظمی', 'content' => 'آزمون‌های مبحثی جدا هم فروخته می‌شود؟', 'meta' => array( '_cat_lead_phone' => '09120000003', '_cat_lead_field' => 'تجربی' ) ),
		),

		'_videos' => $sample_mp4,
	);
}

/* -------------------------------------------------------------------------
 * ورود و حذف داده‌های نمونه
 * ---------------------------------------------------------------------- */

/**
 * ساخت همه‌ی محتوای نمونه.
 *
 * @return array<string,int> شمارش موارد ساخته‌شده.
 */
function catalyzer_import_demo_content() {
	$defs   = catalyzer_demo_definitions();
	$videos = $defs['_videos'];
	unset( $defs['_videos'] );

	$made = array();
	$seed = 0;

	foreach ( $defs as $post_type => $items ) {
		$made[ $post_type ] = 0;

		foreach ( $items as $index => $item ) {
			$seed++;

			// اگر نوشته‌ای با همین عنوان و همین نوع هست، دوباره نساز.
			$dupe = get_posts( array(
				'post_type'      => $post_type,
				'title'          => $item['title'],
				'posts_per_page' => 1,
				'post_status'    => 'any',
				'fields'         => 'ids',
			) );
			if ( $dupe ) {
				continue;
			}

			$post_id = wp_insert_post( array(
				'post_type'    => $post_type,
				'post_title'   => $item['title'],
				'post_content' => isset( $item['content'] ) ? $item['content'] : '',
				'post_status'  => 'publish',
				'menu_order'   => $index,
			) );

			if ( is_wp_error( $post_id ) || ! $post_id ) {
				continue;
			}

			update_post_meta( $post_id, CATALYZER_DEMO_FLAG, '1' );

			$meta = isset( $item['meta'] ) ? $item['meta'] : array();

			if ( 'lesson' === $post_type ) {
				$meta['_cat_video_url'] = $videos[ $index % count( $videos ) ];
			} elseif ( 'success_video' === $post_type ) {
				$meta['_cat_sv_url'] = $videos[ $index % count( $videos ) ];
			}

			foreach ( $meta as $key => $value ) {
				update_post_meta( $post_id, $key, $value );
			}

			if ( in_array( $post_type, array( 'course', 'lesson', 'success_video' ), true ) ) {
				$thumb = catalyzer_demo_thumbnail( $seed, strtoupper( substr( $post_type, 0, 3 ) ) . ( $index + 1 ) );
				if ( $thumb ) {
					set_post_thumbnail( $post_id, $thumb );
				}
			}

			$made[ $post_type ]++;
		}
	}

	catalyzer_ensure_account_page();
	catalyzer_ensure_demo_menu();

	update_option( 'catalyzer_demo_imported', current_time( 'mysql' ), false );

	return $made;
}

/**
 * ساخت منوی اصلی نمونه، اگر هیچ منویی به جایگاه هدر متصل نیست.
 */
function catalyzer_ensure_demo_menu() {
	$locations = get_theme_mod( 'nav_menu_locations', array() );
	if ( ! empty( $locations['primary'] ) && wp_get_nav_menu_object( $locations['primary'] ) ) {
		return;
	}

	$menu_name = 'منوی اصلی کاتالیزور';
	$menu      = wp_get_nav_menu_object( $menu_name );
	$menu_id   = $menu ? (int) $menu->term_id : (int) wp_create_nav_menu( $menu_name );

	if ( ! $menu_id || is_wp_error( $menu_id ) ) {
		return;
	}

	if ( ! wp_get_nav_menu_items( $menu_id ) ) {
		$items = array(
			array( 'درباره‌ی مدرس', home_url( '/#about' ) ),
			array( 'متد کاتالیزور', home_url( '/#method' ) ),
			array( 'دوره‌ها', home_url( '/#courses' ) ),
			array( 'ویدیوهای کلاس', home_url( '/#videos' ) ),
			array( 'رتبه‌برترها', home_url( '/#success' ) ),
			array( 'تماس', home_url( '/#contact' ) ),
		);
		foreach ( $items as $order => $item ) {
			wp_update_nav_menu_item( $menu_id, 0, array(
				'menu-item-title'     => $item[0],
				'menu-item-url'       => $item[1],
				'menu-item-status'    => 'publish',
				'menu-item-position'  => $order + 1,
			) );
		}
	}

	$locations['primary'] = $menu_id;
	set_theme_mod( 'nav_menu_locations', $locations );
}

/**
 * حذف همه‌ی محتوای نشانه‌گذاری‌شده به‌عنوان نمونه.
 *
 * @return int تعداد موارد حذف‌شده.
 */
function catalyzer_remove_demo_content() {
	$ids = get_posts( array(
		'post_type'      => array( 'course', 'lesson', 'success_video', 'testimonial', 'catalyzer_lead', 'attachment' ),
		'post_status'    => 'any',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'meta_key'       => CATALYZER_DEMO_FLAG,
		'meta_value'     => '1',
	) );

	foreach ( $ids as $id ) {
		wp_delete_post( $id, true );
	}

	delete_option( 'catalyzer_demo_imported' );

	return count( $ids );
}

/* -------------------------------------------------------------------------
 * صفحه‌ی پیشخوان
 * ---------------------------------------------------------------------- */

function catalyzer_demo_menu() {
	add_management_page(
		'داده‌های نمونه‌ی کاتالیزور',
		'داده‌های نمونه',
		'manage_options',
		'catalyzer-demo',
		'catalyzer_render_demo_page'
	);
}
add_action( 'admin_menu', 'catalyzer_demo_menu' );

function catalyzer_handle_demo_actions() {
	if ( ! isset( $_POST['catalyzer_demo_action'] ) ) {
		return;
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'دسترسی مجاز نیست.' );
	}
	check_admin_referer( 'catalyzer_demo' );

	$action = sanitize_key( wp_unslash( $_POST['catalyzer_demo_action'] ) );

	if ( 'import' === $action ) {
		$made  = catalyzer_import_demo_content();
		$total = array_sum( $made );
		$notice = $total
			? sprintf( '%d مورد محتوای نمونه ساخته شد.', $total )
			: 'همه‌ی موارد نمونه از قبل وجود داشتند؛ چیزی اضافه نشد.';
		set_transient( 'catalyzer_demo_notice', $notice, 30 );
	} elseif ( 'remove' === $action ) {
		$count = catalyzer_remove_demo_content();
		set_transient( 'catalyzer_demo_notice', sprintf( '%d مورد نمونه حذف شد.', $count ), 30 );
	}

	wp_safe_redirect( admin_url( 'tools.php?page=catalyzer-demo' ) );
	exit;
}
add_action( 'admin_init', 'catalyzer_handle_demo_actions' );

function catalyzer_render_demo_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$notice   = get_transient( 'catalyzer_demo_notice' );
	$imported = get_option( 'catalyzer_demo_imported' );

	$counts = array();
	foreach ( array(
		'course'         => 'دوره',
		'lesson'         => 'ویدیوی کلاس',
		'success_video'  => 'ویدیوی رتبه‌برتر',
		'testimonial'    => 'نظر',
		'catalyzer_lead' => 'درخواست مشاوره',
	) as $type => $label ) {
		$counts[ $label ] = count( get_posts( array(
			'post_type'      => $type,
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		) ) );
	}
	?>
	<div class="wrap">
		<h1>داده‌های نمونه‌ی کاتالیزور</h1>

		<?php if ( $notice ) : ?>
			<div class="notice notice-success"><p><?php echo esc_html( $notice ); ?></p></div>
			<?php delete_transient( 'catalyzer_demo_notice' ); ?>
		<?php endif; ?>

		<p>این ابزار همه‌ی بخش‌های سایت را با محتوای نمونه پر می‌کند تا ساختار کامل سایت قابل بررسی باشد.
		محتوای نمونه نشانه‌گذاری می‌شود و با یک کلیک قابل حذف است؛ محتوای واقعی شما هرگز حذف نمی‌شود.</p>

		<?php if ( $imported ) : ?>
			<p><strong>آخرین ورود داده‌ی نمونه:</strong> <?php echo esc_html( $imported ); ?></p>
		<?php endif; ?>

		<table class="widefat striped" style="max-width:520px">
			<thead><tr><th>نوع محتوا</th><th>تعداد موجود</th></tr></thead>
			<tbody>
			<?php foreach ( $counts as $label => $n ) : ?>
				<tr><td><?php echo esc_html( $label ); ?></td><td><?php echo esc_html( (string) $n ); ?></td></tr>
			<?php endforeach; ?>
			</tbody>
		</table>

		<p style="margin-top:20px;display:flex;gap:12px">
			<form method="post">
				<?php wp_nonce_field( 'catalyzer_demo' ); ?>
				<input type="hidden" name="catalyzer_demo_action" value="import">
				<?php submit_button( 'ساخت محتوای نمونه', 'primary', 'submit', false ); ?>
			</form>
			<form method="post" onsubmit="return confirm('همه‌ی محتوای نمونه حذف شود؟');">
				<?php wp_nonce_field( 'catalyzer_demo' ); ?>
				<input type="hidden" name="catalyzer_demo_action" value="remove">
				<?php submit_button( 'حذف محتوای نمونه', 'delete', 'submit', false ); ?>
			</form>
		</p>

		<h2>یادداشت</h2>
		<ul class="ul-disc">
			<li>آدرس ویدیوهای نمونه به فایل‌های نمایشی عمومی اشاره می‌کند و باید با ویدیوهای واقعی جایگزین شود.</li>
			<li>تصاویر بندانگشتی به‌صورت خودکار ساخته می‌شوند و جای عکس واقعی را می‌گیرند.</li>
			<li>قیمت‌ها، رتبه‌ها و نام‌ها ساختگی‌اند.</li>
		</ul>
	</div>
	<?php
}
