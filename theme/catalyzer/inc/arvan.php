<?php
/**
 * پخش ویدیو از پلتفرم ویدیوی ابر آروان، پشت دیوار دسترسی.
 *
 * ایده‌ی کلی: فایل ویدیو هیچ‌وقت روی هاست خودمان نیست و آدرس مستقیمش هم هرگز
 * داخل HTML صفحه چاپ نمی‌شود. کاربر که دکمه‌ی پخش را می‌زند، مرورگر یک درخواست
 * می‌فرستد؛ سرور تازه آن‌جا بررسی می‌کند که کاربر وارد شده و این ویدیو را
 * خریده، و بعد یک آدرس امضاشده‌ی کوتاه‌عمر و گره‌خورده به آی‌پی همان کاربر
 * می‌سازد و برمی‌گرداند. لینک چند دقیقه بعد می‌میرد و روی دستگاه دیگری هم
 * کار نمی‌کند.
 *
 * صادقانه: این کپی کردن را غیرممکن نمی‌کند — هیچ ویدیویی که در مرورگر پخش شود
 * غیرقابل‌ضبط نیست. کاری که می‌کند این است که هزینه‌ی کپی را بالا می‌برد و با
 * واترمارکِ شماره‌ی خودِ بیننده، کپی‌کننده را قابل‌شناسایی می‌کند.
 *
 * @package Catalyzer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const CATALYZER_ARVAN_OPTION = 'catalyzer_arvan';
const CATALYZER_ARVAN_API    = 'https://napi.arvancloud.ir/vod/2.0';

/**
 * تنظیمات آروان.
 *
 * لینک امن را خود آروان می‌سازد، نه ما: سرور با کلید API از آروان می‌خواهد
 * «برای این آی‌پی، تا این ساعت» یک آدرس معتبر بدهد (پارامترهای secure_ip و
 * secure_expire_time در API پلتفرم ویدیو). فرمول هش آروان عمومی نیست، پس
 * بازسازی‌اش شکننده بود؛ این راه رسمی است و با عوض شدن فرمول هم نمی‌شکند.
 *
 * @return array{apikey:string,ttl:int}
 */
function catalyzer_arvan_settings() {
	$saved = get_option( CATALYZER_ARVAN_OPTION, array() );
	$saved = is_array( $saved ) ? $saved : array();

	return array(
		'apikey' => isset( $saved['apikey'] ) ? (string) $saved['apikey'] : '',
		'ttl'    => isset( $saved['ttl'] ) ? max( 300, min( 86400, (int) $saved['ttl'] ) ) : 1800,
	);
}

/**
 * آدرس HLS ذخیره‌شده‌ی یک ویدیو.
 *
 * @param int $post_id شناسه‌ی نوشته.
 * @return string
 */
function catalyzer_arvan_hls( $post_id = 0 ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();
	return (string) get_post_meta( $post_id, '_cat_arvan_hls', true );
}

/**
 * آیا این ویدیو روی آروان است؟
 */
function catalyzer_is_arvan_video( $post_id = 0 ) {
	return '' !== catalyzer_arvan_hls( $post_id );
}

/**
 * آی‌پی واقعی بیننده.
 *
 * ترتیب مهم است: اگر سایت پشت CDN خود آروان باشد، REMOTE_ADDR آی‌پی لبه است
 * نه کاربر، و لینکی که با آن امضا شود روی دستگاه کاربر ۴۰۳ می‌دهد.
 *
 * @return string
 */
function catalyzer_client_ip() {
	$candidates = array( 'HTTP_AR_REAL_IP', 'HTTP_CF_CONNECTING_IP', 'HTTP_X_REAL_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' );

	foreach ( $candidates as $header ) {
		if ( empty( $_SERVER[ $header ] ) ) {
			continue;
		}
		$raw = sanitize_text_field( wp_unslash( $_SERVER[ $header ] ) );
		// X-Forwarded-For می‌تواند زنجیره باشد؛ اولی کاربر است.
		foreach ( explode( ',', $raw ) as $part ) {
			$ip = filter_var( trim( $part ), FILTER_VALIDATE_IP );
			if ( $ip ) {
				return $ip;
			}
		}
	}

	return '';
}

/**
 * درخواست به API پلتفرم ویدیوی آروان.
 *
 * @param string $path  مسیر، مثل /videos/{id}.
 * @param array  $query پارامترها.
 * @return array|WP_Error  بدنه‌ی JSON.
 */
function catalyzer_arvan_api( $path, $query = array() ) {
	$s = catalyzer_arvan_settings();
	if ( '' === $s['apikey'] ) {
		return new WP_Error( 'catalyzer_arvan_key', 'کلید API آروان در «ویدیوهای کلاس ← پخش امن (آروان)» ذخیره نشده است.' );
	}

	$auth = preg_match( '/^apikey\s/i', $s['apikey'] ) ? $s['apikey'] : 'Apikey ' . $s['apikey'];
	$url  = CATALYZER_ARVAN_API . $path . ( $query ? '?' . http_build_query( $query ) : '' );
	$res  = wp_remote_get( $url, array(
		'timeout' => 15,
		'headers' => array(
			'Authorization' => $auth,
			'Accept'        => 'application/json',
		),
	) );

	if ( is_wp_error( $res ) ) {
		return new WP_Error( 'catalyzer_arvan_net', 'اتصال به API آروان برقرار نشد: ' . $res->get_error_message() );
	}
	$code = (int) wp_remote_retrieve_response_code( $res );
	$body = json_decode( wp_remote_retrieve_body( $res ), true );

	if ( 401 === $code ) {
		return new WP_Error( 'catalyzer_arvan_401', 'آروان کلید API را نپذیرفت (۴۰۱). کلید را دوباره از پنل آروان کپی کنید.' );
	}
	if ( 403 === $code ) {
		return new WP_Error( 'catalyzer_arvan_403', 'کلید API دسترسی به پلتفرم ویدیو ندارد (۴۰۳). در پنل آروان به این کلید دسترسی «پلتفرم ویدیو» بدهید.' );
	}
	if ( $code < 200 || $code >= 300 || ! is_array( $body ) ) {
		return new WP_Error( 'catalyzer_arvan_' . $code, 'پاسخ غیرمنتظره از API آروان (کد ' . $code . ').' );
	}
	return $body;
}

/**
 * «کانال» و «ویدیو» از آدرس HLS (امضاشده یا نه).
 *
 * @param string $url آدرس.
 * @return array{channel:string,video:string}|null
 */
function catalyzer_arvan_parse_hls( $url ) {
	$path = (string) wp_parse_url( $url, PHP_URL_PATH );
	$seg  = array_values( array_filter( explode( '/', $path ), 'strlen' ) );
	if ( count( $seg ) < 2 ) {
		return null;
	}
	$channel = array_shift( $seg );
	// لینک امضاشده: بعد از کانال یک هش ۳۲ حرفی و یک زمان انقضا می‌آید.
	if ( count( $seg ) >= 3 && preg_match( '/^[a-f0-9]{32}$/i', $seg[0] ) && preg_match( '/^\d{9,11}$/', $seg[1] ) ) {
		$seg = array_slice( $seg, 2 );
	}
	return array( 'channel' => $channel, 'video' => $seg[0] );
}

/**
 * شناسه‌ی API ویدیو (UUID) برای یک آدرس HLS.
 *
 * آدرس HLS فقط شناسه‌های کوتاه دارد و API شناسه‌ی بلند می‌خواهد. پس یک بار
 * فهرست ویدیوهای کانال‌ها را می‌گردیم و نتیجه را کنار نوشته نگه می‌داریم.
 * اگر به‌جای آدرس خود UUID وارد شده باشد، همان استفاده می‌شود.
 *
 * @param string $hls     آدرس یا UUID.
 * @param int    $post_id برای نگه داشتن نتیجه (اختیاری).
 * @param array  $trace   گزارش مراحل، برای صفحه‌ی آزمایش.
 * @return string|WP_Error
 */
function catalyzer_arvan_video_id( $hls, $post_id = 0, &$trace = array() ) {
	$hls = trim( (string) $hls );
	if ( preg_match( '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $hls ) ) {
		return strtolower( $hls );
	}

	$ids = catalyzer_arvan_parse_hls( $hls );
	if ( ! $ids ) {
		return new WP_Error( 'catalyzer_arvan_url', 'آدرس HLS قابل خواندن نیست.' );
	}

	if ( $post_id ) {
		$cached = get_post_meta( $post_id, '_cat_arvan_vid', true );
		if ( is_array( $cached ) && isset( $cached['for'], $cached['id'] ) && $cached['for'] === $ids['channel'] . '/' . $ids['video'] ) {
			return $cached['id'];
		}
	}

	$needle_c = '/' . $ids['channel'] . '/';
	$needle_v = '/' . $ids['video'] . '/';

	$channels = catalyzer_arvan_api( '/channels', array( 'per_page' => 100 ) );
	if ( is_wp_error( $channels ) ) {
		return $channels;
	}
	$channels = isset( $channels['data'] ) && is_array( $channels['data'] ) ? $channels['data'] : array();
	$trace[]  = 'کانال‌های پیدا شده: ' . count( $channels );

	foreach ( $channels as $channel ) {
		if ( empty( $channel['id'] ) ) {
			continue;
		}
		for ( $page = 1; $page <= 20; $page++ ) {
			$list = catalyzer_arvan_api( '/channels/' . rawurlencode( $channel['id'] ) . '/videos', array( 'per_page' => 100, 'page' => $page ) );
			if ( is_wp_error( $list ) ) {
				return $list;
			}
			$videos = isset( $list['data'] ) && is_array( $list['data'] ) ? $list['data'] : array();
			foreach ( $videos as $video ) {
				$blob = wp_json_encode( $video, JSON_UNESCAPED_SLASHES );
				if ( ! empty( $video['id'] ) && false !== strpos( $blob, $needle_c ) && false !== strpos( $blob, $needle_v ) ) {
					$trace[] = 'ویدیو در کانال «' . ( isset( $channel['title'] ) ? $channel['title'] : $channel['id'] ) . '» پیدا شد.';
					if ( $post_id ) {
						update_post_meta( $post_id, '_cat_arvan_vid', array( 'for' => $ids['channel'] . '/' . $ids['video'], 'id' => $video['id'] ) );
					}
					return (string) $video['id'];
				}
			}
			if ( count( $videos ) < 100 ) {
				break;
			}
		}
	}

	return new WP_Error( 'catalyzer_arvan_notfound', 'این ویدیو در هیچ‌کدام از کانال‌های این حساب آروان پیدا نشد. آیا کلید API از همان حسابی است که ویدیو در آن است؟' );
}

/**
 * آدرس HLS امضاشده برای یک آی‌پی، ساخته‌ی خود آروان.
 *
 * @param string $video_id شناسه‌ی API ویدیو.
 * @param string $ip       آی‌پی بیننده؛ خالی یعنی آی‌پی درخواست‌دهنده (سرور).
 * @param int    $ttl      عمر به ثانیه.
 * @return string|WP_Error
 */
function catalyzer_arvan_signed_hls( $video_id, $ip = '', $ttl = 0 ) {
	$s     = catalyzer_arvan_settings();
	$query = array( 'secure_expire_time' => time() + ( $ttl > 0 ? (int) $ttl : $s['ttl'] ) );
	if ( '' !== $ip ) {
		$query['secure_ip'] = $ip;
	}

	$res = catalyzer_arvan_api( '/videos/' . rawurlencode( $video_id ), $query );
	if ( is_wp_error( $res ) ) {
		return $res;
	}
	$video = isset( $res['data'] ) && is_array( $res['data'] ) ? $res['data'] : $res;

	if ( ! empty( $video['hls_playlist'] ) && is_string( $video['hls_playlist'] ) ) {
		return $video['hls_playlist'];
	}
	// اگر نام فیلد عوض شده باشد: اولین آدرس m3u8 در پاسخ.
	$found = '';
	array_walk_recursive( $video, function ( $v ) use ( &$found ) {
		if ( '' === $found && is_string( $v ) && preg_match( '#^https://\S+\.m3u8#', $v ) ) {
			$found = $v;
		}
	} );
	if ( $found ) {
		return $found;
	}

	$status = isset( $video['status'] ) ? (string) $video['status'] : '';
	return new WP_Error( 'catalyzer_arvan_nohls', 'آروان برای این ویدیو آدرس HLS برنگرداند' . ( $status ? ' (وضعیت: ' . $status . ')' : '' ) . '. شاید هنوز در حال تبدیل است.' );
}

/* ------------------------------------------------------------------
   نقطه‌ی پایانی: مرورگر لینک را از اینجا می‌گیرد، نه از داخل HTML صفحه.
   ------------------------------------------------------------------ */

/**
 * پاسخ به درخواست پخش.
 */
function catalyzer_arvan_ajax() {
	check_ajax_referer( 'catalyzer_play', 'nonce' );

	$post_id = isset( $_POST['post'] ) ? (int) $_POST['post'] : 0;
	if ( ! $post_id || 'lesson' !== get_post_type( $post_id ) ) {
		wp_send_json_error( array( 'message' => 'ویدیو پیدا نشد.' ), 404 );
	}

	if ( '' === catalyzer_arvan_hls( $post_id ) ) {
		wp_send_json_error( array( 'message' => 'این ویدیو روی آروان نیست.' ), 400 );
	}
	if ( ! catalyzer_user_can_access( $post_id ) ) {
		wp_send_json_error( array( 'message' => 'این ویدیو برای شما باز نشده است.' ), 403 );
	}

	$video_id = catalyzer_arvan_video_id( catalyzer_arvan_hls( $post_id ), $post_id );
	if ( is_wp_error( $video_id ) ) {
		wp_send_json_error( array( 'message' => $video_id->get_error_message() ), 500 );
	}

	// امضای آروان «پیشوند» مسیر را پوشش می‌دهد: فهرست‌های فرزند و قطعه‌ها با همان
	// امضا باز می‌شوند و مستقیم از شبکه‌ی آروان می‌آیند، نه از هاست ما.
	$s   = catalyzer_arvan_settings();
	$url = catalyzer_arvan_signed_hls( $video_id, catalyzer_client_ip(), $s['ttl'] );
	if ( is_wp_error( $url ) ) {
		wp_send_json_error( array( 'message' => $url->get_error_message() ), 500 );
	}

	nocache_headers();
	wp_send_json_success( array(
		'src'       => $url,
		'expires'   => time() + $s['ttl'],
		// واترمارک: شماره‌ی خود بیننده روی تصویر. کپی را نمی‌گیرد، کپی‌کننده را
		// قابل‌شناسایی می‌کند.
		'watermark' => catalyzer_arvan_watermark( wp_get_current_user() ),
	) );
}
add_action( 'wp_ajax_catalyzer_play', 'catalyzer_arvan_ajax' );
add_action( 'wp_ajax_nopriv_catalyzer_play', 'catalyzer_arvan_ajax' );

/**
 * متن واترمارک.
 *
 * @param WP_User $user کاربر.
 * @return string
 */
function catalyzer_arvan_watermark( $user ) {
	if ( ! $user || ! $user->ID ) {
		return '';
	}
	$phone = (string) get_user_meta( $user->ID, '_cat_phone', true );
	return $phone ? $phone : $user->display_name;
}

/* ------------------------------------------------------------------
   تنظیمات
   ------------------------------------------------------------------ */

/**
 * صفحه‌ی تنظیمات زیر «ویدیوهای کلاس».
 */
function catalyzer_arvan_menu() {
	add_submenu_page(
		'edit.php?post_type=lesson',
		'پخش امن (ابر آروان)',
		'پخش امن (آروان)',
		'manage_options',
		'catalyzer-arvan',
		'catalyzer_arvan_page'
	);
}
add_action( 'admin_menu', 'catalyzer_arvan_menu' );

/**
 * ذخیره‌ی تنظیمات.
 */
function catalyzer_arvan_save() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'اجازه‌ی دسترسی ندارید.' );
	}
	check_admin_referer( 'catalyzer_arvan_save' );

	$old = catalyzer_arvan_settings();

	// فیلد کلید که خالی بماند یعنی «دست نزن» — تا با ذخیره‌ی دوباره پاک نشود.
	$key = isset( $_POST['apikey'] ) ? trim( (string) wp_unslash( $_POST['apikey'] ) ) : '';
	$key = '' === $key ? $old['apikey'] : sanitize_text_field( $key );
	$ttl = isset( $_POST['ttl'] ) ? (int) $_POST['ttl'] : $old['ttl'];

	update_option( CATALYZER_ARVAN_OPTION, array(
		'apikey' => $key,
		'ttl'    => max( 300, min( 86400, $ttl ) ),
	) );

	$check = catalyzer_arvan_settings();
	wp_safe_redirect( add_query_arg( 'saved', $check['apikey'] ? '1' : '0', catalyzer_arvan_page_url() ) );
	exit;
}
add_action( 'admin_post_catalyzer_arvan_save', 'catalyzer_arvan_save' );

/**
 * آدرس صفحه‌ی تنظیمات.
 */
function catalyzer_arvan_page_url() {
	return admin_url( 'edit.php?post_type=lesson&page=catalyzer-arvan' );
}

/**
 * آزمایش اتصال: ویدیو را در API پیدا می‌کند، یک لینک امن برای آی‌پی خود سرور
 * می‌گیرد و همان را از سرور باز می‌کند. ۲۰۰ یعنی زنجیره کامل است.
 */
function catalyzer_arvan_test() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'اجازه‌ی دسترسی ندارید.' );
	}
	check_admin_referer( 'catalyzer_arvan_test' );

	$input  = isset( $_POST['test_url'] ) ? trim( (string) wp_unslash( $_POST['test_url'] ) ) : '';
	$report = array( 'steps' => array(), 'ok' => false, 'error' => '' );

	$video_id = catalyzer_arvan_video_id( $input, 0, $report['steps'] );
	if ( is_wp_error( $video_id ) ) {
		$report['error'] = $video_id->get_error_message();
	} else {
		$report['steps'][] = 'شناسه‌ی ویدیو در API: ' . $video_id;
		// بدون secure_ip: آروان لینک را برای آی‌پی خود درخواست‌دهنده، یعنی همین
		// سرور، می‌سازد — پس سرور باید بتواند بازش کند.
		$signed = catalyzer_arvan_signed_hls( $video_id, '', 600 );
		if ( is_wp_error( $signed ) ) {
			$report['error'] = $signed->get_error_message();
		} else {
			$report['steps'][] = 'آروان لینک امن ساخت.';
			$res  = wp_remote_get( $signed, array( 'timeout' => 15 ) );
			$code = is_wp_error( $res ) ? $res->get_error_message() : (int) wp_remote_retrieve_response_code( $res );
			$report['steps'][] = 'باز کردن همان لینک از سرور: ' . $code;
			$report['ok']      = ( 200 === $code );
			if ( ! $report['ok'] ) {
				$report['error'] = 'لینک ساخته شد ولی باز نشد (' . $code . ').';
			}
		}
	}

	set_transient( 'catalyzer_arvan_test', $report, 300 );
	wp_safe_redirect( catalyzer_arvan_page_url() );
	exit;
}
add_action( 'admin_post_catalyzer_arvan_test', 'catalyzer_arvan_test' );

/**
 * صفحه‌ی تنظیمات.
 */
function catalyzer_arvan_page() {
	$s      = catalyzer_arvan_settings();
	$report = get_transient( 'catalyzer_arvan_test' );
	delete_transient( 'catalyzer_arvan_test' );
	?>
	<div class="wrap">
		<h1>پخش امن ویدیو — ابر آروان</h1>

		<?php if ( isset( $_GET['saved'] ) && '1' === $_GET['saved'] ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
			<div class="notice notice-success is-dismissible"><p>ذخیره شد.</p></div>
		<?php elseif ( isset( $_GET['saved'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
			<div class="notice notice-error"><p>کلید ذخیره نشد. اگر نشست ورود منقضی شده، دوباره وارد شوید و ذخیره کنید.</p></div>
		<?php endif; ?>

		<?php if ( is_array( $report ) ) : ?>
			<div class="notice <?php echo $report['ok'] ? 'notice-success' : 'notice-error'; ?>">
				<p><strong><?php echo $report['ok'] ? 'همه‌چیز درست است — پخش امن آماده است.' : esc_html( $report['error'] ); ?></strong></p>
				<?php if ( $report['steps'] ) : ?>
					<ul style="list-style:disc;padding-inline-start:20px">
						<?php foreach ( $report['steps'] as $step ) : ?>
							<li><?php echo esc_html( $step ); ?></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<p style="max-width:70ch">
			ویدیو روی آروان می‌ماند و آدرسش داخل HTML صفحه چاپ نمی‌شود. وقتی دانش‌آموزی که ویدیو را
			خریده دکمه‌ی پخش را می‌زند، سرور دسترسی‌اش را بررسی می‌کند و از خود آروان یک لینک امن
			گره‌خورده به آی‌پی همان دانش‌آموز می‌گیرد. لینک پس از <?php echo esc_html( (string) round( $s['ttl'] / 60 ) ); ?> دقیقه
			می‌میرد و روی دستگاهی با آی‌پی دیگر کار نمی‌کند.
		</p>

		<table class="widefat striped" style="max-width:520px;margin-block:12px 20px">
			<tbody>
				<tr><td>کلید API</td><td><?php echo $s['apikey'] ? '<span style="color:#1a7f37">ذخیره شده</span>' : '<strong style="color:#b32d2e">ذخیره نشده</strong>'; ?></td></tr>
				<tr><td>عمر لینک</td><td><?php echo esc_html( (string) $s['ttl'] ); ?> ثانیه</td></tr>
			</tbody>
		</table>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="catalyzer_arvan_save">
			<?php wp_nonce_field( 'catalyzer_arvan_save' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="cat_arvan_apikey">کلید API آروان</label></th>
					<td>
						<input name="apikey" id="cat_arvan_apikey" type="password" class="regular-text" dir="ltr" autocomplete="off"
							placeholder="<?php echo $s['apikey'] ? 'ذخیره شده — برای تغییر، کلید تازه را بنویسید' : 'Apikey xxxxxxxx-xxxx-…'; ?>">
						<p class="description">
							پنل آروان ← پروفایل ← «کلیدهای API» ← ساخت کلید. بهتر است کلید را فقط به «پلتفرم ویدیو» محدود کنید.
							این با «کلید لینک امن» کانال فرق دارد. خالی بگذارید تا کلید فعلی دست‌نخورده بماند.
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="cat_arvan_ttl">عمر لینک (ثانیه)</label></th>
					<td>
						<input name="ttl" id="cat_arvan_ttl" type="number" min="300" max="86400" value="<?php echo esc_attr( (string) $s['ttl'] ); ?>" class="small-text">
						<p class="description">پیشنهاد: ۱۸۰۰. اگر لینک وسط تماشا منقضی شود، پلیر خودش لینک تازه می‌گیرد و از همان ثانیه ادامه می‌دهد.</p>
					</td>
				</tr>
			</table>
			<?php submit_button( 'ذخیره' ); ?>
		</form>

		<hr>

		<h2>آزمایش اتصال</h2>
		<p style="max-width:70ch">آدرس HLS یک ویدیو (امضاشده یا نه) یا شناسه‌ی UUID آن را بگذارید.</p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="catalyzer_arvan_test">
			<?php wp_nonce_field( 'catalyzer_arvan_test' ); ?>
			<input name="test_url" type="text" class="large-text" dir="ltr" placeholder="https://…/master.m3u8" required>
			<?php submit_button( 'آزمایش', 'secondary' ); ?>
		</form>
	</div>
	<?php
}
