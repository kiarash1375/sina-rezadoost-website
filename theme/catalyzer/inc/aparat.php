<?php
/**
 * درون‌ریزی ویدیوهای رایگان کانال آپارات.
 *
 * کانال آپارات منبع است و سایت آینه‌ی آن. هر ویدیو یک نوشته‌ی «ویدیوی کلاس»
 * می‌شود که با شناسه‌ی آپارات (uid) شناخته می‌شود، و هر پلی‌لیست یک ترم در
 * دسته‌بندی ویدیوها. همگام‌سازی دوباره، نوشته‌ی تازه نمی‌سازد؛ همان نوشته را
 * به‌روز می‌کند.
 *
 * میزبان سایت داخل ایران است و به آپارات می‌رسد؛ فضای کاری ابری نمی‌رسد. برای
 * همین آدرس پایه از فیلتر `catalyzer_aparat_base` می‌گذرد تا در تست بتوان آن را
 * به یک سرور محلی داد.
 *
 * @package Catalyzer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const CATALYZER_APARAT_TAX     = 'lesson_cat';
const CATALYZER_APARAT_USER    = 'catalyzer_aparat_user';
const CATALYZER_APARAT_REPORT  = 'catalyzer_aparat_report';
const CATALYZER_APARAT_DEFAULT = 'Dr_SinaRezadoost';

/* -------------------------------------------------------------------------
 * تماس با آپارات
 * ---------------------------------------------------------------------- */

/**
 * نام کاربری کانال.
 */
function catalyzer_aparat_user() {
	$user = (string) get_option( CATALYZER_APARAT_USER, CATALYZER_APARAT_DEFAULT );
	$user = trim( $user );
	return $user ? $user : CATALYZER_APARAT_DEFAULT;
}

/**
 * آدرس پایه‌ی آپارات — در تست جایگزین می‌شود.
 */
function catalyzer_aparat_base() {
	return untrailingslashit( apply_filters( 'catalyzer_aparat_base', 'https://www.aparat.com' ) );
}

/**
 * یک درخواست GET به آپارات و تبدیل پاسخ به آرایه.
 *
 * @param string $path مسیر، با اسلش ابتدایی.
 * @return array|WP_Error
 */
function catalyzer_aparat_get( $path ) {
	$url = catalyzer_aparat_base() . $path;

	$res = wp_remote_get( $url, array(
		'timeout'    => 20,
		'user-agent' => 'Catalyzer/' . CATALYZER_VERSION . '; ' . home_url( '/' ),
		'headers'    => array( 'Accept' => 'application/json' ),
	) );

	if ( is_wp_error( $res ) ) {
		return $res;
	}

	$code = (int) wp_remote_retrieve_response_code( $res );
	if ( 200 !== $code ) {
		/* translators: 1: HTTP status, 2: request path */
		return new WP_Error( 'catalyzer_aparat_http', sprintf( 'آپارات کد %1$d برگرداند (%2$s).', $code, $path ) );
	}

	$body = json_decode( wp_remote_retrieve_body( $res ), true );
	if ( ! is_array( $body ) ) {
		return new WP_Error( 'catalyzer_aparat_json', sprintf( 'پاسخ آپارات برای %s قابل خواندن نبود.', $path ) );
	}

	return $body;
}

/**
 * همه‌ی ویدیوهای کانال، با دنبال کردن صفحه‌بندی.
 *
 * @return array|WP_Error فهرست ویدیوها.
 */
function catalyzer_aparat_videos() {
	$user  = rawurlencode( catalyzer_aparat_user() );
	$path  = '/etc/api/videoByUser/username/' . $user . '/perpage/50';
	$all   = array();
	$seen  = array();
	$guard = 0;

	while ( $path && $guard++ < 40 ) {
		$body = catalyzer_aparat_get( $path );
		if ( is_wp_error( $body ) ) {
			return $body;
		}

		$page = isset( $body['videobyuser'] ) && is_array( $body['videobyuser'] ) ? $body['videobyuser'] : array();
		if ( ! $page ) {
			break;
		}

		foreach ( $page as $video ) {
			$uid = isset( $video['uid'] ) ? (string) $video['uid'] : '';
			if ( ! $uid || isset( $seen[ $uid ] ) ) {
				continue;
			}
			$seen[ $uid ] = true;
			$all[]        = $video;
		}

		// صفحه‌ی بعد به‌صورت آدرس کامل می‌آید؛ فقط مسیرش لازم است.
		$next = isset( $body['ui']['pagingForward'] ) ? (string) $body['ui']['pagingForward'] : '';
		$path = $next ? '/' . ltrim( (string) wp_parse_url( $next, PHP_URL_PATH ), '/' ) : '';
	}

	return $all;
}

/**
 * پلی‌لیست‌های کانال به‌همراه شناسه‌ی ویدیوهای داخل هرکدام.
 *
 * @return array|WP_Error آرایه‌ای از array( 'id', 'title', 'uids' ).
 */
function catalyzer_aparat_playlists() {
	$user = rawurlencode( catalyzer_aparat_user() );
	$body = catalyzer_aparat_get( '/api/fa/v1/video/playlist/list/username/' . $user );
	if ( is_wp_error( $body ) ) {
		return $body;
	}

	$out = array();
	foreach ( (array) ( isset( $body['data'] ) ? $body['data'] : array() ) as $row ) {
		$attr = isset( $row['attributes'] ) ? $row['attributes'] : array();
		$id   = isset( $attr['id'] ) ? (string) $attr['id'] : '';
		if ( ! $id ) {
			continue;
		}

		$one  = catalyzer_aparat_get( '/api/fa/v1/video/playlist/one/playlist_id/' . rawurlencode( $id ) );
		$uids = array();
		if ( ! is_wp_error( $one ) ) {
			foreach ( (array) ( isset( $one['included'] ) ? $one['included'] : array() ) as $inc ) {
				if ( isset( $inc['type'], $inc['attributes']['uid'] ) && 'Video' === $inc['type'] ) {
					$uids[] = (string) $inc['attributes']['uid'];
				}
			}
		}

		$out[] = array(
			'id'    => $id,
			'title' => isset( $attr['title'] ) ? (string) $attr['title'] : '',
			'desc'  => isset( $attr['description'] ) ? (string) $attr['description'] : '',
			'uids'  => $uids,
		);
	}

	return $out;
}

/* -------------------------------------------------------------------------
 * کمک‌کارها
 * ---------------------------------------------------------------------- */

/**
 * ثانیه → «۱:۰۲:۳۳» یا «۱۸:۴۵»، با رقم فارسی مثل بقیه‌ی سایت.
 */
function catalyzer_aparat_duration( $seconds ) {
	$seconds = max( 0, (int) $seconds );
	$h       = (int) floor( $seconds / 3600 );
	$m       = (int) floor( ( $seconds % 3600 ) / 60 );
	$s       = $seconds % 60;

	$text = $h
		? sprintf( '%d:%02d:%02d', $h, $m, $s )
		: sprintf( '%d:%02d', $m, $s );

	return catalyzer_fa_digits( $text );
}

/**
 * رقم‌های لاتین → فارسی.
 */
function catalyzer_fa_digits( $text ) {
	return str_replace(
		array( '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' ),
		array( '۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹' ),
		(string) $text
	);
}

/**
 * آدرس جاسازی (iframe) یک ویدیوی آپارات.
 */
function catalyzer_aparat_embed_url( $uid ) {
	$uid = preg_replace( '/[^A-Za-z0-9]/', '', (string) $uid );
	return $uid ? 'https://www.aparat.com/video/video/embed/videohash/' . $uid . '/vt/frame' : '';
}

/**
 * آدرس تماشای ویدیو روی خود آپارات.
 */
function catalyzer_aparat_watch_url( $uid ) {
	$uid = preg_replace( '/[^A-Za-z0-9]/', '', (string) $uid );
	return $uid ? 'https://www.aparat.com/v/' . $uid : '';
}

/**
 * قاب جاسازی آماده برای لایت‌باکس.
 *
 * @param int $post_id شناسه‌ی نوشته‌ی ویدیو.
 */
function catalyzer_lesson_embed( $post_id = 0 ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();
	$uid     = get_post_meta( $post_id, '_cat_aparat_uid', true );

	if ( $uid ) {
		return sprintf(
			'<iframe src="%s" title="%s" allowfullscreen webkitallowfullscreen mozallowfullscreen loading="lazy" referrerpolicy="origin"></iframe>',
			esc_url( catalyzer_aparat_embed_url( $uid ) ),
			esc_attr( get_the_title( $post_id ) )
		);
	}

	$url = get_post_meta( $post_id, '_cat_video_url', true );
	return $url ? catalyzer_video_embed( $url ) : '';
}

/**
 * بندانگشتیِ ویدیو: تصویر شاخص، وگرنه پوستر آپارات.
 *
 * @param int $post_id شناسه‌ی نوشته.
 */
function catalyzer_lesson_thumb( $post_id = 0 ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();

	// بندانگشتی تزئینی است: عنوان ویدیو همان کنارش در کارت نوشته شده و کارت خودش
	// aria-label دارد. alt خالی هم تکرار را برمی‌دارد و هم وقتی پوستر آپارات بالا
	// نیامد، متن جایگزین روی کارت پخش نمی‌شود.
	if ( has_post_thumbnail( $post_id ) ) {
		return get_the_post_thumbnail( $post_id, 'catalyzer-card', array(
			'loading' => 'lazy',
			'alt'     => '',
		) );
	}

	$poster = get_post_meta( $post_id, '_cat_aparat_poster', true );
	if ( $poster ) {
		return sprintf(
			'<img src="%s" alt="" loading="lazy" decoding="async" width="640" height="360">',
			esc_url( $poster )
		);
	}

	return catalyzer_icon( 'rings' );
}

/**
 * دسته‌بندی‌هایی که دست‌کم یک ویدیو دارند.
 *
 * @return WP_Term[]
 */
function catalyzer_lesson_categories() {
	$terms = get_terms( array(
		'taxonomy'   => CATALYZER_APARAT_TAX,
		'hide_empty' => true,
		'orderby'    => 'count',
		'order'      => 'DESC',
	) );
	return is_wp_error( $terms ) ? array() : $terms;
}

/**
 * نام دسته‌ی یک ویدیو، برای نمایش روی کارت.
 */
function catalyzer_lesson_category_label( $post_id = 0 ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();
	$terms   = get_the_terms( $post_id, CATALYZER_APARAT_TAX );
	if ( $terms && ! is_wp_error( $terms ) ) {
		return $terms[0]->name;
	}
	return (string) get_post_meta( $post_id, '_cat_category', true );
}

/* -------------------------------------------------------------------------
 * همگام‌سازی
 * ---------------------------------------------------------------------- */

/**
 * نوشته‌ی متناظر یک شناسه‌ی آپارات.
 *
 * @param string $uid شناسه‌ی ویدیو در آپارات.
 * @return int شناسه‌ی نوشته یا صفر.
 */
function catalyzer_aparat_find_post( $uid ) {
	$found = get_posts( array(
		'post_type'        => 'lesson',
		'post_status'      => 'any',
		'posts_per_page'   => 1,
		'fields'           => 'ids',
		'suppress_filters' => false,
		'meta_key'         => '_cat_aparat_uid',   // phpcs:ignore WordPress.DB.SlowDBQuery
		'meta_value'       => (string) $uid,        // phpcs:ignore WordPress.DB.SlowDBQuery
	) );
	return $found ? (int) $found[0] : 0;
}

/**
 * کشیدن کل کانال داخل سایت.
 *
 * @return array|WP_Error گزارش همگام‌سازی.
 */
function catalyzer_aparat_sync() {
	$videos = catalyzer_aparat_videos();
	if ( is_wp_error( $videos ) ) {
		return $videos;
	}
	if ( ! $videos ) {
		return new WP_Error( 'catalyzer_aparat_empty', 'آپارات هیچ ویدیویی برای این کانال برنگرداند.' );
	}

	$playlists = catalyzer_aparat_playlists();
	if ( is_wp_error( $playlists ) ) {
		$playlists = array(); // پلی‌لیست‌ها اختیاری‌اند؛ نبودشان نباید کل کار را بخواباند.
	}

	// uid → عنوان پلی‌لیست.
	$by_uid = array();
	foreach ( $playlists as $pl ) {
		foreach ( $pl['uids'] as $uid ) {
			$by_uid[ $uid ] = $pl['title'];
		}
	}

	$report = array(
		'time'      => time(),
		'user'      => catalyzer_aparat_user(),
		'total'     => count( $videos ),
		'created'   => 0,
		'updated'   => 0,
		'playlists' => count( $playlists ),
		'retired'   => 0,
		'demo'      => 0,
		'errors'    => array(),
	);

	$live = array();

	foreach ( $videos as $video ) {
		$uid = isset( $video['uid'] ) ? (string) $video['uid'] : '';
		if ( ! $uid ) {
			continue;
		}
		$live[] = $uid;

		$title    = isset( $video['title'] ) ? wp_strip_all_tags( (string) $video['title'] ) : $uid;
		$existing = catalyzer_aparat_find_post( $uid );

		$postarr = array(
			'post_type'    => 'lesson',
			'post_title'   => $title,
			'post_status'  => 'publish',
			'post_content' => '',
		);

		if ( ! empty( $video['create_date'] ) ) {
			$postarr['post_date'] = get_date_from_gmt( (string) $video['create_date'] . '', 'Y-m-d H:i:s' );
		}

		if ( $existing ) {
			$postarr['ID'] = $existing;
			unset( $postarr['post_content'] ); // متنی که دستی نوشته شده نباید پاک شود.
			$post_id = wp_update_post( $postarr, true );
		} else {
			$post_id = wp_insert_post( $postarr, true );
		}

		if ( is_wp_error( $post_id ) ) {
			$report['errors'][] = $title . ': ' . $post_id->get_error_message();
			continue;
		}

		$existing ? $report['updated']++ : $report['created']++;

		$category = isset( $by_uid[ $uid ] ) ? $by_uid[ $uid ] : 'دسته‌بندی‌نشده';

		update_post_meta( $post_id, '_cat_aparat_uid', $uid );
		update_post_meta( $post_id, '_cat_aparat_id', isset( $video['id'] ) ? (string) $video['id'] : '' );
		update_post_meta( $post_id, '_cat_aparat_poster', isset( $video['big_poster'] ) ? esc_url_raw( (string) $video['big_poster'] ) : '' );
		update_post_meta( $post_id, '_cat_aparat_visits', isset( $video['visit_cnt'] ) ? (int) $video['visit_cnt'] : 0 );
		update_post_meta( $post_id, '_cat_aparat_seconds', isset( $video['duration'] ) ? (int) $video['duration'] : 0 );
		update_post_meta( $post_id, '_cat_video_url', catalyzer_aparat_watch_url( $uid ) );
		update_post_meta( $post_id, '_cat_duration', catalyzer_aparat_duration( isset( $video['duration'] ) ? $video['duration'] : 0 ) );
		update_post_meta( $post_id, '_cat_category', $category );
		update_post_meta( $post_id, '_cat_free', 1 );

		wp_set_object_terms( $post_id, array( $category ), CATALYZER_APARAT_TAX, false );
	}

	// ویدیویی که دیگر روی کانال نیست پاک نمی‌شود؛ پیش‌نویس می‌شود تا از سایت برود
	// ولی متن و تنظیمات دستی‌اش بماند.
	$stale = get_posts( array(
		'post_type'      => 'lesson',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery
			array(
				'key'     => '_cat_aparat_uid',
				'value'   => $live,
				'compare' => 'NOT IN',
			),
		),
	) );
	foreach ( $stale as $id ) {
		wp_update_post( array( 'ID' => $id, 'post_status' => 'draft' ) );
		$report['retired']++;
	}

	// ویدیوهای نمونه‌ی قالب تاریخ تازه‌تری دارند و روی ویدیوهای واقعی می‌نشینند.
	// پس از اولین درون‌ریزی، پیش‌نویس می‌شوند تا سایت محتوای واقعی را نشان دهد؛
	// پاک نمی‌شوند و دکمه‌ی «حذف محتوای نمونه» هم سر جایش است.
	$demo = get_posts( array(
		'post_type'      => 'lesson',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery
			'relation' => 'AND',
			array( 'key' => CATALYZER_DEMO_FLAG, 'compare' => 'EXISTS' ),
			array( 'key' => '_cat_aparat_uid', 'compare' => 'NOT EXISTS' ),
		),
	) );
	foreach ( $demo as $id ) {
		wp_update_post( array( 'ID' => $id, 'post_status' => 'draft' ) );
		$report['demo']++;
	}

	update_option( CATALYZER_APARAT_REPORT, $report, false );

	return $report;
}

/* -------------------------------------------------------------------------
 * پیشخوان
 * ---------------------------------------------------------------------- */

/**
 * صفحه‌ی «درون‌ریزی از آپارات» زیر ویدیوهای کلاس.
 */
function catalyzer_aparat_menu() {
	add_submenu_page(
		'edit.php?post_type=lesson',
		'درون‌ریزی از آپارات',
		'درون‌ریزی از آپارات',
		'manage_options',
		'catalyzer-aparat',
		'catalyzer_aparat_screen'
	);
}
add_action( 'admin_menu', 'catalyzer_aparat_menu' );

/**
 * خود صفحه.
 */
function catalyzer_aparat_screen() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$report = get_option( CATALYZER_APARAT_REPORT, array() );
	$notice = isset( $_GET['catalyzer_aparat'] ) ? sanitize_text_field( wp_unslash( $_GET['catalyzer_aparat'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	$error  = isset( $_GET['catalyzer_error'] ) ? sanitize_text_field( wp_unslash( $_GET['catalyzer_error'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	?>
	<div class="wrap">
		<h1>درون‌ریزی ویدیوهای آپارات</h1>

		<?php if ( 'done' === $notice ) : ?>
			<div class="notice notice-success is-dismissible"><p>همگام‌سازی انجام شد.</p></div>
		<?php elseif ( $error ) : ?>
			<div class="notice notice-error"><p><?php echo esc_html( $error ); ?></p></div>
		<?php endif; ?>

		<p class="description" style="max-width:46em">
			هر ویدیوی کانال یک «ویدیوی کلاس» می‌شود و هر پلی‌لیست یک دسته‌بندی. ویدیویی که
			پیش‌تر درون‌ریزی شده دوباره ساخته نمی‌شود؛ عنوان، مدت، پوستر و دسته‌اش به‌روز می‌شود.
			ویدیویی که از کانال حذف شده باشد پیش‌نویس می‌شود، نه پاک.
		</p>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="catalyzer_aparat_sync">
			<?php wp_nonce_field( 'catalyzer_aparat_sync' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th><label for="catalyzer_aparat_user">نام کاربری کانال</label></th>
					<td>
						<input type="text" dir="ltr" class="regular-text" id="catalyzer_aparat_user"
							name="catalyzer_aparat_user" value="<?php echo esc_attr( catalyzer_aparat_user() ); ?>">
						<p class="description">
							بخش پایانی آدرس کانال — برای
							<code dir="ltr">https://www.aparat.com/<?php echo esc_html( catalyzer_aparat_user() ); ?></code>
							همین مقدار درست است.
						</p>
					</td>
				</tr>
			</table>
			<?php submit_button( 'همگام‌سازی با آپارات' ); ?>
		</form>

		<?php if ( ! empty( $report['time'] ) ) : ?>
			<h2>آخرین همگام‌سازی</h2>
			<table class="widefat striped" style="max-width:40em">
				<tbody>
					<tr><td>زمان</td><td><?php echo esc_html( wp_date( 'Y/m/d H:i', (int) $report['time'] ) ); ?></td></tr>
					<tr><td>کانال</td><td dir="ltr"><?php echo esc_html( $report['user'] ); ?></td></tr>
					<tr><td>ویدیوهای کانال</td><td><?php echo esc_html( number_format_i18n( (int) $report['total'] ) ); ?></td></tr>
					<tr><td>تازه اضافه‌شده</td><td><?php echo esc_html( number_format_i18n( (int) $report['created'] ) ); ?></td></tr>
					<tr><td>به‌روزشده</td><td><?php echo esc_html( number_format_i18n( (int) $report['updated'] ) ); ?></td></tr>
					<tr><td>پلی‌لیست‌ها</td><td><?php echo esc_html( number_format_i18n( (int) $report['playlists'] ) ); ?></td></tr>
					<tr><td>پیش‌نویس‌شده (از کانال حذف شده)</td><td><?php echo esc_html( number_format_i18n( (int) $report['retired'] ) ); ?></td></tr>
					<tr><td>ویدیوی نمونه‌ی کنارگذاشته‌شده</td><td><?php echo esc_html( number_format_i18n( (int) ( isset( $report['demo'] ) ? $report['demo'] : 0 ) ) ); ?></td></tr>
				</tbody>
			</table>
			<?php if ( ! empty( $report['errors'] ) ) : ?>
				<h3>خطاها</h3>
				<ul class="ul-disc">
					<?php foreach ( (array) $report['errors'] as $line ) : ?>
						<li><?php echo esc_html( $line ); ?></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * اجرای همگام‌سازی از دکمه‌ی پیشخوان.
 */
function catalyzer_aparat_handle_sync() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'اجازه‌ی این کار را ندارید.' );
	}
	check_admin_referer( 'catalyzer_aparat_sync' );

	if ( isset( $_POST['catalyzer_aparat_user'] ) ) {
		$user = sanitize_text_field( wp_unslash( $_POST['catalyzer_aparat_user'] ) );
		$user = preg_replace( '/[^A-Za-z0-9_.-]/', '', $user );
		if ( $user ) {
			update_option( CATALYZER_APARAT_USER, $user, false );
		}
	}

	$result = catalyzer_aparat_sync();
	$back   = admin_url( 'edit.php?post_type=lesson&page=catalyzer-aparat' );

	if ( is_wp_error( $result ) ) {
		wp_safe_redirect( add_query_arg( 'catalyzer_error', rawurlencode( $result->get_error_message() ), $back ) );
		exit;
	}

	wp_safe_redirect( add_query_arg( 'catalyzer_aparat', 'done', $back ) );
	exit;
}
add_action( 'admin_post_catalyzer_aparat_sync', 'catalyzer_aparat_handle_sync' );
