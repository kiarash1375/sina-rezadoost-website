<?php
/**
 * ورود و ثبت‌نام با پیامک (یک‌بار رمز).
 *
 * جریان کار: شماره → ارسال کد → تأیید کد → ورود یا ساخت حساب.
 * درایور پیامک قابل تعویض است؛ در «حالت تست» کدی ارسال نمی‌شود و
 * فقط در پیشخوان و روی فرم نمایش داده می‌شود.
 *
 * @package Catalyzer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const CATALYZER_OTP_TTL       = 120;  // ثانیه — اعتبار کد
const CATALYZER_OTP_RESEND    = 60;   // ثانیه — فاصله‌ی مجاز بین دو ارسال
const CATALYZER_OTP_MAX_TRIES = 5;    // حداکثر تلاش برای هر کد
const CATALYZER_OTP_MAX_SENDS = 5;    // حداکثر ارسال در هر ساعت برای هر شماره

/* -------------------------------------------------------------------------
 * تنظیمات
 * ---------------------------------------------------------------------- */

/**
 * تنظیمات پیامک با مقادیر پیش‌فرض.
 *
 * @return array<string,string>
 */
function catalyzer_sms_settings() {
	$defaults = array(
		'driver'    => 'test',
		'api_key'   => '',
		'sender'    => '',
		'pattern'   => '',
		'signup'    => '1',
	);
	$saved = get_option( 'catalyzer_sms_options', array() );
	if ( ! is_array( $saved ) ) {
		$saved = array();
	}
	return array_merge( $defaults, $saved );
}

function catalyzer_sms_is_test_mode() {
	$s = catalyzer_sms_settings();
	return 'test' === $s['driver'];
}

/* -------------------------------------------------------------------------
 * کمکی‌ها
 * ---------------------------------------------------------------------- */

/**
 * تبدیل ارقام فارسی/عربی به لاتین.
 */
function catalyzer_latin_digits( $text ) {
	$fa = array( '۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹' );
	$ar = array( '٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩' );
	$en = array( '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' );
	return str_replace( $ar, $en, str_replace( $fa, $en, (string) $text ) );
}

/**
 * نرمال‌سازی شماره‌ی موبایل ایران به شکل 09xxxxxxxxx.
 *
 * @return string شماره‌ی نرمال‌شده یا رشته‌ی خالی اگر معتبر نباشد.
 */
function catalyzer_normalize_phone( $raw ) {
	$digits = preg_replace( '/\D+/', '', catalyzer_latin_digits( $raw ) );
	if ( '' === $digits ) {
		return '';
	}
	if ( 0 === strpos( $digits, '0098' ) ) {
		$digits = substr( $digits, 4 );
	} elseif ( 0 === strpos( $digits, '98' ) && 12 === strlen( $digits ) ) {
		$digits = substr( $digits, 2 );
	}
	if ( 10 === strlen( $digits ) && '9' === $digits[0] ) {
		$digits = '0' . $digits;
	}
	return preg_match( '/^09\d{9}$/', $digits ) ? $digits : '';
}

function catalyzer_otp_key( $phone ) {
	return 'cat_otp_' . md5( $phone );
}

function catalyzer_otp_quota_key( $phone ) {
	return 'cat_otp_q_' . md5( $phone );
}

/**
 * کاربر متناظر با یک شماره.
 *
 * @return WP_User|null
 */
function catalyzer_user_by_phone( $phone ) {
	$users = get_users( array(
		'meta_key'   => '_cat_phone',
		'meta_value' => $phone,
		'number'     => 1,
		'fields'     => 'all',
	) );
	if ( $users ) {
		return $users[0];
	}
	$by_login = get_user_by( 'login', $phone );
	return $by_login ? $by_login : null;
}

/**
 * آدرس صفحه‌ی حساب کاربری.
 */
function catalyzer_account_url() {
	$page_id = (int) get_option( 'catalyzer_account_page_id' );
	if ( $page_id && 'publish' === get_post_status( $page_id ) ) {
		return get_permalink( $page_id );
	}
	$page = get_page_by_path( 'account' );
	if ( $page ) {
		update_option( 'catalyzer_account_page_id', $page->ID );
		return get_permalink( $page );
	}
	return home_url( '/account/' );
}

/**
 * ثبت رویداد در گزارش پیامک (۳۰ مورد آخر).
 */
function catalyzer_sms_log( $phone, $code, $status, $note = '' ) {
	$log   = get_option( 'catalyzer_sms_log', array() );
	$log   = is_array( $log ) ? $log : array();
	$entry = array(
		'time'   => current_time( 'mysql' ),
		'phone'  => $phone,
		'code'   => $code,
		'status' => $status,
		'note'   => $note,
	);
	array_unshift( $log, $entry );
	update_option( 'catalyzer_sms_log', array_slice( $log, 0, 30 ), false );
}

/* -------------------------------------------------------------------------
 * درایورهای پیامک
 * ---------------------------------------------------------------------- */

/**
 * ارسال کد. در حالت تست چیزی ارسال نمی‌شود.
 *
 * @return true|WP_Error
 */
function catalyzer_sms_send( $phone, $code ) {
	$s      = catalyzer_sms_settings();
	$driver = $s['driver'];

	/**
	 * امکان جایگزینی کامل ارسال پیامک.
	 *
	 * @param null|true|WP_Error $short_circuit
	 */
	$pre = apply_filters( 'catalyzer_pre_sms_send', null, $phone, $code, $s );
	if ( null !== $pre ) {
		return $pre;
	}

	if ( 'test' === $driver ) {
		catalyzer_sms_log( $phone, $code, 'test', 'حالت تست — پیامکی ارسال نشد' );
		return true;
	}

	if ( '' === $s['api_key'] ) {
		return new WP_Error( 'catalyzer_sms_config', 'کلید API سرویس پیامک تنظیم نشده است.' );
	}

	$response = null;

	if ( 'kavenegar' === $driver ) {
		$url = sprintf(
			'https://api.kavenegar.com/v1/%s/verify/lookup.json?receptor=%s&token=%s&template=%s',
			rawurlencode( $s['api_key'] ),
			rawurlencode( $phone ),
			rawurlencode( $code ),
			rawurlencode( $s['pattern'] )
		);
		$response = wp_remote_get( $url, array( 'timeout' => 15 ) );

	} elseif ( 'smsir' === $driver ) {
		$response = wp_remote_post( 'https://api.sms.ir/v1/send/verify', array(
			'timeout' => 15,
			'headers' => array(
				'Content-Type' => 'application/json',
				'Accept'       => 'application/json',
				'x-api-key'    => $s['api_key'],
			),
			'body'    => wp_json_encode( array(
				'mobile'     => $phone,
				'templateId' => (int) $s['pattern'],
				'parameters' => array( array( 'name' => 'CODE', 'value' => (string) $code ) ),
			) ),
		) );

	} elseif ( 'melipayamak' === $driver ) {
		$response = wp_remote_post( 'https://console.melipayamak.com/api/send/otp/' . rawurlencode( $s['api_key'] ), array(
			'timeout' => 15,
			'headers' => array( 'Content-Type' => 'application/json' ),
			'body'    => wp_json_encode( array( 'to' => $phone ) ),
		) );

	} else {
		return new WP_Error( 'catalyzer_sms_driver', 'سرویس پیامک ناشناخته است.' );
	}

	if ( is_wp_error( $response ) ) {
		catalyzer_sms_log( $phone, $code, 'error', $response->get_error_message() );
		return $response;
	}

	$status = (int) wp_remote_retrieve_response_code( $response );
	if ( $status < 200 || $status >= 300 ) {
		$body = wp_remote_retrieve_body( $response );
		catalyzer_sms_log( $phone, $code, 'error', 'HTTP ' . $status . ' — ' . mb_substr( (string) $body, 0, 200 ) );
		return new WP_Error( 'catalyzer_sms_http', 'ارسال پیامک ناموفق بود (کد ' . $status . ').' );
	}

	catalyzer_sms_log( $phone, $code, 'sent', $driver );
	return true;
}

/* -------------------------------------------------------------------------
 * درخواست و تأیید کد
 * ---------------------------------------------------------------------- */

/**
 * ساخت و ارسال کد برای یک شماره.
 *
 * @return array|WP_Error آرایه‌ی نتیجه یا خطا.
 */
function catalyzer_request_otp( $raw_phone ) {
	$phone = catalyzer_normalize_phone( $raw_phone );
	if ( '' === $phone ) {
		return new WP_Error( 'catalyzer_phone', 'شماره‌ی موبایل معتبر نیست. نمونه‌ی درست: ۰۹۱۲۳۴۵۶۷۸۹' );
	}

	$existing = get_transient( catalyzer_otp_key( $phone ) );
	if ( is_array( $existing ) && ( time() - (int) $existing['created'] ) < CATALYZER_OTP_RESEND ) {
		$wait = CATALYZER_OTP_RESEND - ( time() - (int) $existing['created'] );
		return new WP_Error( 'catalyzer_otp_wait', sprintf( 'برای ارسال دوباره %d ثانیه صبر کنید.', $wait ) );
	}

	$quota = (int) get_transient( catalyzer_otp_quota_key( $phone ) );
	if ( $quota >= CATALYZER_OTP_MAX_SENDS ) {
		return new WP_Error( 'catalyzer_otp_quota', 'تعداد درخواست‌ها زیاد بود. یک ساعت دیگر دوباره تلاش کنید.' );
	}

	$code = (string) wp_rand( 10000, 99999 );

	$sent = catalyzer_sms_send( $phone, $code );
	if ( is_wp_error( $sent ) ) {
		return $sent;
	}

	set_transient( catalyzer_otp_key( $phone ), array(
		'code'    => $code,
		'tries'   => 0,
		'created' => time(),
	), CATALYZER_OTP_TTL );

	set_transient( catalyzer_otp_quota_key( $phone ), $quota + 1, HOUR_IN_SECONDS );

	$result = array(
		'phone'      => $phone,
		'expires_in' => CATALYZER_OTP_TTL,
		'is_new'     => null === catalyzer_user_by_phone( $phone ),
	);

	if ( catalyzer_sms_is_test_mode() ) {
		$result['test_code'] = $code;
	}

	return $result;
}

/**
 * تأیید کد و ورود (یا ساخت حساب).
 *
 * @return array|WP_Error
 */
function catalyzer_verify_otp( $raw_phone, $raw_code, $name = '', $field = '' ) {
	$phone = catalyzer_normalize_phone( $raw_phone );
	if ( '' === $phone ) {
		return new WP_Error( 'catalyzer_phone', 'شماره‌ی موبایل معتبر نیست.' );
	}

	$key  = catalyzer_otp_key( $phone );
	$data = get_transient( $key );
	if ( ! is_array( $data ) ) {
		return new WP_Error( 'catalyzer_otp_expired', 'کد منقضی شده است. دوباره درخواست کد بدهید.' );
	}

	if ( (int) $data['tries'] >= CATALYZER_OTP_MAX_TRIES ) {
		delete_transient( $key );
		return new WP_Error( 'catalyzer_otp_tries', 'تعداد تلاش‌های نادرست زیاد بود. دوباره درخواست کد بدهید.' );
	}

	$code = preg_replace( '/\D+/', '', catalyzer_latin_digits( $raw_code ) );
	if ( ! hash_equals( (string) $data['code'], (string) $code ) ) {
		$data['tries'] = (int) $data['tries'] + 1;
		$remaining     = max( 0, (int) $data['created'] + CATALYZER_OTP_TTL - time() );
		set_transient( $key, $data, $remaining > 0 ? $remaining : 1 );
		return new WP_Error( 'catalyzer_otp_wrong', 'کد وارد‌شده درست نیست.' );
	}

	delete_transient( $key );
	delete_transient( catalyzer_otp_quota_key( $phone ) );

	$user    = catalyzer_user_by_phone( $phone );
	$created = false;

	if ( ! $user ) {
		$settings = catalyzer_sms_settings();
		if ( '1' !== (string) $settings['signup'] ) {
			return new WP_Error( 'catalyzer_signup_off', 'ثبت‌نام کاربر جدید در حال حاضر بسته است.' );
		}

		$user_id = wp_insert_user( array(
			'user_login'   => $phone,
			'user_pass'    => wp_generate_password( 24, true, true ),
			'user_email'   => $phone . '@sms.invalid',
			'display_name' => '' !== $name ? $name : $phone,
			'first_name'   => $name,
			'role'         => 'subscriber',
		) );

		if ( is_wp_error( $user_id ) ) {
			return $user_id;
		}

		update_user_meta( $user_id, '_cat_phone', $phone );
		if ( '' !== $field ) {
			update_user_meta( $user_id, '_cat_field', $field );
		}
		$user    = get_user_by( 'id', $user_id );
		$created = true;
	} else {
		if ( '' !== $name && $user->display_name === $user->user_login ) {
			wp_update_user( array( 'ID' => $user->ID, 'display_name' => $name, 'first_name' => $name ) );
		}
		if ( '' !== $field ) {
			update_user_meta( $user->ID, '_cat_field', $field );
		}
		if ( ! get_user_meta( $user->ID, '_cat_phone', true ) ) {
			update_user_meta( $user->ID, '_cat_phone', $phone );
		}
	}

	wp_set_current_user( $user->ID );
	wp_set_auth_cookie( $user->ID, true );
	do_action( 'wp_login', $user->user_login, $user );

	catalyzer_sms_log( $phone, '—', $created ? 'signup' : 'login', 'کاربر #' . $user->ID );

	return array(
		'user_id'  => $user->ID,
		'created'  => $created,
		'redirect' => catalyzer_account_url(),
	);
}

/* -------------------------------------------------------------------------
 * نقاط پایانی AJAX
 * ---------------------------------------------------------------------- */

function catalyzer_ajax_fail( $error ) {
	wp_send_json_error( array(
		'code'    => is_wp_error( $error ) ? $error->get_error_code() : 'error',
		'message' => is_wp_error( $error ) ? $error->get_error_message() : (string) $error,
	) );
}

function catalyzer_ajax_send_otp() {
	check_ajax_referer( 'catalyzer_auth', 'nonce' );

	$phone  = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
	$result = catalyzer_request_otp( $phone );

	if ( is_wp_error( $result ) ) {
		catalyzer_ajax_fail( $result );
	}
	wp_send_json_success( $result );
}
add_action( 'wp_ajax_nopriv_catalyzer_send_otp', 'catalyzer_ajax_send_otp' );
add_action( 'wp_ajax_catalyzer_send_otp', 'catalyzer_ajax_send_otp' );

function catalyzer_ajax_verify_otp() {
	check_ajax_referer( 'catalyzer_auth', 'nonce' );

	$phone = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
	$code  = isset( $_POST['code'] ) ? sanitize_text_field( wp_unslash( $_POST['code'] ) ) : '';
	$name  = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
	$field = isset( $_POST['field'] ) ? sanitize_text_field( wp_unslash( $_POST['field'] ) ) : '';

	$result = catalyzer_verify_otp( $phone, $code, $name, $field );
	if ( is_wp_error( $result ) ) {
		catalyzer_ajax_fail( $result );
	}
	wp_send_json_success( $result );
}
add_action( 'wp_ajax_nopriv_catalyzer_verify_otp', 'catalyzer_ajax_verify_otp' );
add_action( 'wp_ajax_catalyzer_verify_otp', 'catalyzer_ajax_verify_otp' );

/**
 * به‌روزرسانی پروفایل از پنل کاربر.
 */
function catalyzer_handle_profile_update() {
	if ( ! is_user_logged_in() ) {
		wp_safe_redirect( catalyzer_account_url() );
		exit;
	}
	check_admin_referer( 'catalyzer_profile' );

	$user_id = get_current_user_id();
	$name    = isset( $_POST['cat_name'] ) ? sanitize_text_field( wp_unslash( $_POST['cat_name'] ) ) : '';
	$field   = isset( $_POST['cat_field'] ) ? sanitize_text_field( wp_unslash( $_POST['cat_field'] ) ) : '';
	$email   = isset( $_POST['cat_email'] ) ? sanitize_email( wp_unslash( $_POST['cat_email'] ) ) : '';

	$update = array( 'ID' => $user_id );
	if ( '' !== $name ) {
		$update['display_name'] = $name;
		$update['first_name']   = $name;
	}
	if ( '' !== $email && is_email( $email ) ) {
		$update['user_email'] = $email;
	}
	wp_update_user( $update );
	update_user_meta( $user_id, '_cat_field', $field );

	wp_safe_redirect( add_query_arg( 'updated', '1', catalyzer_account_url() ) );
	exit;
}
add_action( 'admin_post_catalyzer_profile', 'catalyzer_handle_profile_update' );

/* -------------------------------------------------------------------------
 * صفحه‌ی حساب کاربری (شورت‌کد)
 * ---------------------------------------------------------------------- */

function catalyzer_auth_assets() {
	wp_localize_script( 'catalyzer-site', 'CatalyzerAuth', array(
		'ajaxUrl' => admin_url( 'admin-ajax.php' ),
		'nonce'   => wp_create_nonce( 'catalyzer_auth' ),
		'test'    => catalyzer_sms_is_test_mode(),
	) );
}
add_action( 'wp_enqueue_scripts', 'catalyzer_auth_assets', 20 );

function catalyzer_account_shortcode() {
	ob_start();
	if ( is_user_logged_in() ) {
		catalyzer_render_account_panel();
	} else {
		catalyzer_render_auth_form();
	}
	return ob_get_clean();
}
add_shortcode( 'catalyzer_account', 'catalyzer_account_shortcode' );

function catalyzer_render_auth_form() {
	$test = catalyzer_sms_is_test_mode();
	?>
	<div class="auth-card" id="catAuth">
		<div class="auth-head">
			<p class="eyebrow"><?php esc_html_e( 'ورود و ثبت‌نام', 'catalyzer' ); ?></p>
			<h2><?php esc_html_e( 'با شماره‌ی موبایل وارد شو', 'catalyzer' ); ?></h2>
			<p class="auth-lede"><?php esc_html_e( 'یک کد پنج‌رقمی برایت پیامک می‌شود. اگر تا به حال ثبت‌نام نکرده‌ای، همین‌جا حسابت ساخته می‌شود.', 'catalyzer' ); ?></p>
		</div>

		<?php if ( $test ) : ?>
			<p class="auth-note auth-note-test">
				<?php esc_html_e( 'حالت تست فعال است: پیامکی ارسال نمی‌شود و کد همین‌جا نمایش داده می‌شود.', 'catalyzer' ); ?>
			</p>
		<?php endif; ?>

		<div class="auth-step" data-step="phone">
			<label for="catPhone"><?php esc_html_e( 'شماره‌ی موبایل', 'catalyzer' ); ?></label>
			<input type="tel" id="catPhone" inputmode="numeric" autocomplete="tel" placeholder="۰۹۱۲۳۴۵۶۷۸۹" dir="ltr">
			<button type="button" class="btn btn-primary" id="catSendOtp"><?php esc_html_e( 'ارسال کد', 'catalyzer' ); ?></button>
		</div>

		<div class="auth-step" data-step="code" hidden>
			<p class="auth-phone-line">
				<span id="catPhoneEcho" dir="ltr"></span>
				<button type="button" class="linklike" id="catEditPhone"><?php esc_html_e( 'ویرایش شماره', 'catalyzer' ); ?></button>
			</p>

			<div class="auth-newuser" id="catNewUser" hidden>
				<label for="catName"><?php esc_html_e( 'نام و نام خانوادگی', 'catalyzer' ); ?></label>
				<input type="text" id="catName" autocomplete="name">
				<label for="catField"><?php esc_html_e( 'رشته', 'catalyzer' ); ?></label>
				<select id="catField">
					<option value="تجربی"><?php esc_html_e( 'تجربی', 'catalyzer' ); ?></option>
					<option value="ریاضی"><?php esc_html_e( 'ریاضی', 'catalyzer' ); ?></option>
					<option value="سایر"><?php esc_html_e( 'سایر', 'catalyzer' ); ?></option>
				</select>
			</div>

			<label for="catCode"><?php esc_html_e( 'کد پنج‌رقمی', 'catalyzer' ); ?></label>
			<input type="text" id="catCode" inputmode="numeric" autocomplete="one-time-code" maxlength="5" dir="ltr">
			<button type="button" class="btn btn-primary" id="catVerifyOtp"><?php esc_html_e( 'تأیید و ورود', 'catalyzer' ); ?></button>
			<p class="auth-resend">
				<button type="button" class="linklike" id="catResend" disabled><?php esc_html_e( 'ارسال دوباره‌ی کد', 'catalyzer' ); ?></button>
				<span id="catTimer"></span>
			</p>
		</div>

		<p class="auth-msg" id="catAuthMsg" role="status" aria-live="polite"></p>
	</div>
	<?php
}

function catalyzer_render_account_panel() {
	$user  = wp_get_current_user();
	$phone = get_user_meta( $user->ID, '_cat_phone', true );
	$field = get_user_meta( $user->ID, '_cat_field', true );
	$done  = isset( $_GET['updated'] ); // phpcs:ignore WordPress.Security.NonceVerification
	?>
	<div class="account-panel">
		<div class="account-head">
			<p class="eyebrow"><?php esc_html_e( 'پنل کاربری', 'catalyzer' ); ?></p>
			<h2><?php echo esc_html( sprintf( 'سلام %s', $user->display_name ) ); ?></h2>
		</div>

		<?php if ( $done ) : ?>
			<p class="auth-note auth-note-ok"><?php esc_html_e( 'تغییرات ذخیره شد.', 'catalyzer' ); ?></p>
		<?php endif; ?>

		<form class="account-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="catalyzer_profile">
			<?php wp_nonce_field( 'catalyzer_profile' ); ?>

			<label for="accName"><?php esc_html_e( 'نام و نام خانوادگی', 'catalyzer' ); ?></label>
			<input type="text" id="accName" name="cat_name" value="<?php echo esc_attr( $user->display_name ); ?>">

			<label for="accPhone"><?php esc_html_e( 'شماره‌ی موبایل', 'catalyzer' ); ?></label>
			<input type="text" id="accPhone" value="<?php echo esc_attr( $phone ); ?>" dir="ltr" readonly>
			<span class="field-note"><?php esc_html_e( 'شماره‌ی موبایل قابل تغییر نیست.', 'catalyzer' ); ?></span>

			<label for="accField"><?php esc_html_e( 'رشته', 'catalyzer' ); ?></label>
			<select id="accField" name="cat_field">
				<?php foreach ( array( 'تجربی', 'ریاضی', 'سایر' ) as $opt ) : ?>
					<option value="<?php echo esc_attr( $opt ); ?>" <?php selected( $field, $opt ); ?>><?php echo esc_html( $opt ); ?></option>
				<?php endforeach; ?>
			</select>

			<label for="accEmail"><?php esc_html_e( 'ایمیل (اختیاری)', 'catalyzer' ); ?></label>
			<input type="email" id="accEmail" name="cat_email" value="<?php echo esc_attr( false !== strpos( $user->user_email, '@sms.invalid' ) ? '' : $user->user_email ); ?>" dir="ltr">

			<button type="submit" class="btn btn-primary"><?php esc_html_e( 'ذخیره‌ی تغییرات', 'catalyzer' ); ?></button>
		</form>

		<div class="account-courses">
			<h3><?php esc_html_e( 'دوره‌های من', 'catalyzer' ); ?></h3>
			<?php
			$purchased = apply_filters( 'catalyzer_user_courses', array(), $user->ID );
			if ( empty( $purchased ) ) :
				?>
				<p class="form-note"><?php esc_html_e( 'هنوز دوره‌ای تهیه نکرده‌ای. پس از خرید، دوره‌ها اینجا نمایش داده می‌شوند.', 'catalyzer' ); ?></p>
				<a class="btn btn-ghost" href="<?php echo esc_url( home_url( '/#courses' ) ); ?>"><?php esc_html_e( 'دیدن دوره‌ها', 'catalyzer' ); ?></a>
			<?php else : ?>
				<ul class="account-course-list">
					<?php foreach ( $purchased as $course_id ) : ?>
						<li><a href="<?php echo esc_url( get_permalink( $course_id ) ); ?>"><?php echo esc_html( get_the_title( $course_id ) ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>

		<p class="account-logout">
			<a class="linklike" href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>"><?php esc_html_e( 'خروج از حساب', 'catalyzer' ); ?></a>
		</p>
	</div>
	<?php
}

/* -------------------------------------------------------------------------
 * ساخت خودکار صفحه‌ی حساب کاربری
 * ---------------------------------------------------------------------- */

function catalyzer_ensure_account_page() {
	$page_id = (int) get_option( 'catalyzer_account_page_id' );
	if ( $page_id && 'publish' === get_post_status( $page_id ) ) {
		return $page_id;
	}

	$existing = get_page_by_path( 'account' );
	if ( $existing ) {
		update_option( 'catalyzer_account_page_id', $existing->ID );
		return $existing->ID;
	}

	$new_id = wp_insert_post( array(
		'post_title'   => 'حساب کاربری',
		'post_name'    => 'account',
		'post_content' => '[catalyzer_account]',
		'post_status'  => 'publish',
		'post_type'    => 'page',
	) );

	if ( ! is_wp_error( $new_id ) ) {
		update_option( 'catalyzer_account_page_id', $new_id );
		return $new_id;
	}
	return 0;
}
add_action( 'after_switch_theme', 'catalyzer_ensure_account_page' );

/**
 * کاربران عادی نباید به پیشخوان وردپرس دسترسی داشته باشند.
 */
function catalyzer_block_admin_for_subscribers() {
	if ( wp_doing_ajax() || wp_doing_cron() ) {
		return;
	}
	// admin-post.php هم زیر is_admin() قرار می‌گیرد؛ فرم‌های خود سایت نباید مسدود شوند.
	$script = isset( $GLOBALS['pagenow'] ) ? $GLOBALS['pagenow'] : '';
	if ( in_array( $script, array( 'admin-post.php', 'admin-ajax.php' ), true ) ) {
		return;
	}
	if ( is_admin() && ! current_user_can( 'edit_posts' ) ) {
		wp_safe_redirect( catalyzer_account_url() );
		exit;
	}
}
add_action( 'admin_init', 'catalyzer_block_admin_for_subscribers' );

function catalyzer_hide_admin_bar( $show ) {
	return current_user_can( 'edit_posts' ) ? $show : false;
}
add_filter( 'show_admin_bar', 'catalyzer_hide_admin_bar' );

/* -------------------------------------------------------------------------
 * صفحه‌ی تنظیمات در پیشخوان
 * ---------------------------------------------------------------------- */

function catalyzer_sms_settings_menu() {
	add_options_page(
		'ورود پیامکی کاتالیزور',
		'ورود پیامکی',
		'manage_options',
		'catalyzer-sms',
		'catalyzer_render_sms_settings'
	);
}
add_action( 'admin_menu', 'catalyzer_sms_settings_menu' );

function catalyzer_register_sms_settings() {
	register_setting( 'catalyzer_sms', 'catalyzer_sms_options', array(
		'sanitize_callback' => 'catalyzer_sanitize_sms_options',
		'default'           => array(),
	) );
}
add_action( 'admin_init', 'catalyzer_register_sms_settings' );

function catalyzer_sanitize_sms_options( $input ) {
	$allowed = array( 'test', 'kavenegar', 'smsir', 'melipayamak' );
	$driver  = isset( $input['driver'] ) && in_array( $input['driver'], $allowed, true ) ? $input['driver'] : 'test';

	return array(
		'driver'  => $driver,
		'api_key' => isset( $input['api_key'] ) ? sanitize_text_field( $input['api_key'] ) : '',
		'sender'  => isset( $input['sender'] ) ? sanitize_text_field( $input['sender'] ) : '',
		'pattern' => isset( $input['pattern'] ) ? sanitize_text_field( $input['pattern'] ) : '',
		'signup'  => ! empty( $input['signup'] ) ? '1' : '0',
	);
}

function catalyzer_render_sms_settings() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$s   = catalyzer_sms_settings();
	$log = get_option( 'catalyzer_sms_log', array() );
	$log = is_array( $log ) ? $log : array();
	?>
	<div class="wrap">
		<h1>ورود پیامکی کاتالیزور</h1>

		<form method="post" action="options.php">
			<?php settings_fields( 'catalyzer_sms' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="cat_driver">سرویس پیامک</label></th>
					<td>
						<select name="catalyzer_sms_options[driver]" id="cat_driver">
							<option value="test" <?php selected( $s['driver'], 'test' ); ?>>حالت تست (بدون ارسال واقعی)</option>
							<option value="kavenegar" <?php selected( $s['driver'], 'kavenegar' ); ?>>کاوه‌نگار</option>
							<option value="smsir" <?php selected( $s['driver'], 'smsir' ); ?>>SMS.ir</option>
							<option value="melipayamak" <?php selected( $s['driver'], 'melipayamak' ); ?>>ملی‌پیامک</option>
						</select>
						<p class="description">در حالت تست پیامکی ارسال نمی‌شود؛ کد روی فرم و در جدول پایین همین صفحه نمایش داده می‌شود. <strong>پیش از انتشار سایت حتماً یک سرویس واقعی انتخاب کنید.</strong></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="cat_api">کلید API</label></th>
					<td>
						<input type="text" class="regular-text" id="cat_api" name="catalyzer_sms_options[api_key]" value="<?php echo esc_attr( $s['api_key'] ); ?>" dir="ltr" autocomplete="off">
						<p class="description">کاوه‌نگار: API Key · SMS.ir: x-api-key · ملی‌پیامک: شناسه‌ی OTP</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="cat_pattern">شناسه‌ی الگو</label></th>
					<td>
						<input type="text" class="regular-text" id="cat_pattern" name="catalyzer_sms_options[pattern]" value="<?php echo esc_attr( $s['pattern'] ); ?>" dir="ltr" autocomplete="off">
						<p class="description">کاوه‌نگار: نام template · SMS.ir: templateId عددی · ملی‌پیامک: لازم نیست</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="cat_sender">شماره‌ی فرستنده</label></th>
					<td><input type="text" class="regular-text" id="cat_sender" name="catalyzer_sms_options[sender]" value="<?php echo esc_attr( $s['sender'] ); ?>" dir="ltr" autocomplete="off"></td>
				</tr>
				<tr>
					<th scope="row">ثبت‌نام کاربر جدید</th>
					<td>
						<label><input type="checkbox" name="catalyzer_sms_options[signup]" value="1" <?php checked( $s['signup'], '1' ); ?>> کاربران جدید بتوانند ثبت‌نام کنند</label>
					</td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>

		<h2>آخرین رویدادها</h2>
		<p class="description">صفحه‌ی حساب کاربری: <a href="<?php echo esc_url( catalyzer_account_url() ); ?>" target="_blank"><?php echo esc_html( catalyzer_account_url() ); ?></a></p>
		<table class="widefat striped">
			<thead><tr><th>زمان</th><th>شماره</th><th>کد</th><th>وضعیت</th><th>توضیح</th></tr></thead>
			<tbody>
			<?php if ( ! $log ) : ?>
				<tr><td colspan="5">هنوز رویدادی ثبت نشده است.</td></tr>
			<?php else : ?>
				<?php foreach ( $log as $row ) : ?>
					<tr>
						<td><?php echo esc_html( $row['time'] ); ?></td>
						<td dir="ltr"><?php echo esc_html( $row['phone'] ); ?></td>
						<td dir="ltr"><strong><?php echo esc_html( $row['code'] ); ?></strong></td>
						<td><?php echo esc_html( $row['status'] ); ?></td>
						<td><?php echo esc_html( $row['note'] ); ?></td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
			</tbody>
		</table>
	</div>
	<?php
}

/**
 * ستون شماره‌ی موبایل در فهرست کاربران.
 */
function catalyzer_user_columns( $cols ) {
	$cols['cat_phone'] = 'موبایل';
	$cols['cat_field'] = 'رشته';
	return $cols;
}
add_filter( 'manage_users_columns', 'catalyzer_user_columns' );

function catalyzer_user_column_content( $out, $col, $user_id ) {
	if ( 'cat_phone' === $col ) {
		return esc_html( get_user_meta( $user_id, '_cat_phone', true ) );
	}
	if ( 'cat_field' === $col ) {
		return esc_html( get_user_meta( $user_id, '_cat_field', true ) );
	}
	return $out;
}
add_filter( 'manage_users_custom_column', 'catalyzer_user_column_content', 10, 3 );
