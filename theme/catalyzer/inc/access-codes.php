<?php
/**
 * کدهای دسترسی.
 *
 * تا وقتی درگاه پرداخت نداریم، خرید بیرون از سایت انجام می‌شود و چیزی که قفل
 * را باز می‌کند یک کد است: مدیر در پیشخوان کد می‌سازد و می‌دهد، کاربر کد را در
 * حسابش وارد می‌کند و محتوا برایش باز می‌شود.
 *
 * هر کد یک نوشته از نوع catalyzer_code است:
 *
 *   post_title            خودِ کد
 *   _cat_code_targets     شناسه‌ی محتواهایی که باز می‌کند
 *   _cat_code_all         «۱» یعنی همه‌ی جزوه‌ها
 *   _cat_code_max         چند بار قابل استفاده است
 *   _cat_code_used        [ [user_id, timestamp], … ]
 *   _cat_code_expires     Y-m-d یا خالی
 *   _cat_code_note        یادداشت مدیر (نام خریدار، شماره‌ی رسید و …)
 *
 * وقتی درگاه یا تأیید رسید اضافه شد، همان catalyzer_grant_access() را صدا
 * می‌زند و این فایل دست‌نخورده می‌ماند.
 *
 * @package Catalyzer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** حروف و ارقامی که در کد به کار می‌روند — بدون O/0 و I/1 و امثالش. */
const CATALYZER_CODE_ALPHABET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

/** پیشوند کدها. */
const CATALYZER_CODE_PREFIX = 'SR';

/* -------------------------------------------------------------------------
 * ذخیره‌سازی
 * ---------------------------------------------------------------------- */

/**
 * ثبت نوع محتوای کد.
 *
 * رابط کاربری‌اش خاموش است؛ صفحه‌ی اختصاصی خودش را دارد.
 */
function catalyzer_register_code_type() {
	register_post_type( 'catalyzer_code', array(
		'labels'          => array( 'name' => 'کدهای دسترسی', 'singular_name' => 'کد دسترسی' ),
		'public'          => false,
		'show_ui'         => false,
		'show_in_rest'    => false,
		'supports'        => array( 'title' ),
		'capability_type' => 'post',
		'map_meta_cap'    => true,
	) );
}
add_action( 'init', 'catalyzer_register_code_type', 5 );

/**
 * ساخت یک رشته‌ی کد تصادفی و خوانا.
 *
 * @return string مثل SR-7F3K-2QD9
 */
function catalyzer_new_code_string() {
	$alphabet = CATALYZER_CODE_ALPHABET;
	$len      = strlen( $alphabet );
	$block    = function () use ( $alphabet, $len ) {
		$out = '';
		for ( $i = 0; $i < 4; $i++ ) {
			$out .= $alphabet[ random_int( 0, $len - 1 ) ];
		}
		return $out;
	};
	return CATALYZER_CODE_PREFIX . '-' . $block() . '-' . $block();
}

/**
 * یکسان‌سازی شکل کدِ واردشده: بزرگ، بدون فاصله، با ارقام لاتین.
 *
 * @param string $raw ورودی کاربر.
 * @return string
 */
function catalyzer_normalize_code( $raw ) {
	$raw = function_exists( 'catalyzer_latin_digits' ) ? catalyzer_latin_digits( (string) $raw ) : (string) $raw;
	$raw = strtoupper( trim( $raw ) );
	$raw = preg_replace( '/[\s\x{200c}]+/u', '', $raw );
	return preg_replace( '/[^A-Z0-9\-]/', '', $raw );
}

/**
 * پیدا کردن نوشته‌ی یک کد.
 *
 * @param string $code کدِ یکسان‌سازی‌شده.
 * @return WP_Post|null
 */
function catalyzer_find_code( $code ) {
	$found = get_posts( array(
		'post_type'        => 'catalyzer_code',
		'post_status'      => 'publish',
		'posts_per_page'   => 1,
		'title'            => $code,
		'suppress_filters' => false,
	) );
	return $found ? $found[0] : null;
}

/**
 * شناسه‌ی محتواهایی که یک کد باز می‌کند.
 *
 * @param int|WP_Post $code نوشته‌ی کد.
 * @return int[]
 */
function catalyzer_code_targets( $code ) {
	$code = get_post( $code );
	if ( ! $code ) {
		return array();
	}

	if ( '1' === get_post_meta( $code->ID, '_cat_code_all', true ) ) {
		return get_posts( array(
			'post_type'      => 'note',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_key'       => '_cat_access_type',   // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'     => 'paid',               // phpcs:ignore WordPress.DB.SlowDBQuery
		) );
	}

	$ids = get_post_meta( $code->ID, '_cat_code_targets', true );
	return is_array( $ids ) ? array_values( array_filter( array_map( 'intval', $ids ) ) ) : array();
}

/**
 * سابقه‌ی مصرف یک کد.
 *
 * @return array<int,array{user:int,time:int}>
 */
function catalyzer_code_uses( $code ) {
	$code = get_post( $code );
	$used = $code ? get_post_meta( $code->ID, '_cat_code_used', true ) : array();
	return is_array( $used ) ? $used : array();
}

/**
 * آیا کد هنوز قابل استفاده است؟ اگر نه، چرا.
 *
 * @param WP_Post $code    نوشته‌ی کد.
 * @param int     $user_id کاربری که می‌خواهد مصرفش کند.
 * @return true|WP_Error
 */
function catalyzer_code_is_usable( $code, $user_id ) {
	if ( ! $code ) {
		return new WP_Error( 'not_found', 'این کد معتبر نیست.' );
	}

	$expires = (string) get_post_meta( $code->ID, '_cat_code_expires', true );
	if ( $expires && strtotime( $expires . ' 23:59:59' ) < current_time( 'timestamp' ) ) {
		return new WP_Error( 'expired', 'اعتبار این کد تمام شده است.' );
	}

	$uses = catalyzer_code_uses( $code );
	foreach ( $uses as $use ) {
		if ( (int) $use['user'] === (int) $user_id ) {
			return new WP_Error( 'already', 'این کد قبلاً روی همین حساب استفاده شده است.' );
		}
	}

	$max = max( 1, (int) get_post_meta( $code->ID, '_cat_code_max', true ) );
	if ( count( $uses ) >= $max ) {
		return new WP_Error( 'spent', 'این کد قبلاً استفاده شده است.' );
	}

	if ( ! catalyzer_code_targets( $code ) ) {
		return new WP_Error( 'empty', 'این کد به محتوایی وصل نیست. با پشتیبانی تماس بگیر.' );
	}

	return true;
}

/**
 * مصرف کد و باز کردن محتواها.
 *
 * @param string $raw     کدی که کاربر تایپ کرده.
 * @param int    $user_id شناسه‌ی کاربر.
 * @return array{opened:int[],code:string}|WP_Error
 */
function catalyzer_redeem_code( $raw, $user_id ) {
	$user_id = (int) $user_id;
	if ( ! $user_id ) {
		return new WP_Error( 'no_user', 'برای استفاده از کد باید وارد حسابت شوی.' );
	}

	$clean = catalyzer_normalize_code( $raw );
	if ( ! $clean ) {
		return new WP_Error( 'empty_input', 'کد را وارد کن.' );
	}

	$code = catalyzer_find_code( $clean );
	$ok   = catalyzer_code_is_usable( $code, $user_id );
	if ( is_wp_error( $ok ) ) {
		return $ok;
	}

	$opened = array();
	foreach ( catalyzer_code_targets( $code ) as $post_id ) {
		if ( catalyzer_grant_access( $user_id, $post_id, 'code' ) ) {
			$opened[] = (int) $post_id;
		}
	}

	$uses   = catalyzer_code_uses( $code );
	$uses[] = array( 'user' => $user_id, 'time' => time() );
	update_post_meta( $code->ID, '_cat_code_used', $uses );

	/**
	 * پس از مصرف موفق یک کد.
	 *
	 * @param int   $user_id شناسه‌ی کاربر.
	 * @param int   $code_id شناسه‌ی کد.
	 * @param int[] $opened  محتواهایی که تازه باز شدند.
	 */
	do_action( 'catalyzer_code_redeemed', $user_id, $code->ID, $opened );

	return array( 'opened' => $opened, 'code' => $clean );
}

/* -------------------------------------------------------------------------
 * فرم کاربر
 * ---------------------------------------------------------------------- */

/**
 * دریافت فرم «کد دسترسی» از پنل کاربری یا از صفحه‌ی جزوه.
 */
function catalyzer_handle_redeem() {
	check_admin_referer( 'catalyzer_redeem' );

	$back = isset( $_POST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_POST['redirect_to'] ) ) : '';
	$back = $back ? $back : catalyzer_account_url();

	if ( ! is_user_logged_in() ) {
		wp_safe_redirect( add_query_arg( 'code_error', rawurlencode( 'برای استفاده از کد باید وارد حسابت شوی.' ), $back ) );
		exit;
	}

	$raw    = isset( $_POST['cat_code'] ) ? wp_unslash( $_POST['cat_code'] ) : '';
	$result = catalyzer_redeem_code( $raw, get_current_user_id() );

	if ( is_wp_error( $result ) ) {
		wp_safe_redirect( add_query_arg( 'code_error', rawurlencode( $result->get_error_message() ), $back ) );
		exit;
	}

	$n = count( $result['opened'] );
	wp_safe_redirect( add_query_arg( 'code_ok', $n, $back ) );
	exit;
}
add_action( 'admin_post_catalyzer_redeem', 'catalyzer_handle_redeem' );
add_action( 'admin_post_nopriv_catalyzer_redeem', 'catalyzer_handle_redeem' );

/**
 * فرم وارد کردن کد.
 *
 * @param string $redirect_to آدرسی که بعد از ثبت به آن برگردیم.
 */
function catalyzer_redeem_form( $redirect_to = '' ) {
	$redirect_to = $redirect_to ? $redirect_to : ( is_singular() ? get_permalink() : catalyzer_account_url() );
	?>
	<form class="code-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="catalyzer_redeem">
		<input type="hidden" name="redirect_to" value="<?php echo esc_url( $redirect_to ); ?>">
		<?php wp_nonce_field( 'catalyzer_redeem' ); ?>
		<label for="catCode"><?php esc_html_e( 'کد دسترسی', 'catalyzer' ); ?></label>
		<div class="code-row">
			<input type="text" id="catCode" name="cat_code" dir="ltr" autocomplete="off" placeholder="SR-XXXX-XXXX" required>
			<button type="submit" class="btn btn-primary"><?php esc_html_e( 'باز کن', 'catalyzer' ); ?></button>
		</div>
	</form>
	<?php
}

/* -------------------------------------------------------------------------
 * صفحه‌ی پیشخوان
 * ---------------------------------------------------------------------- */

/**
 * «کدهای دسترسی» زیر منوی جزوه‌ها.
 */
function catalyzer_codes_menu() {
	add_submenu_page(
		'edit.php?post_type=note',
		'کدهای دسترسی',
		'کدهای دسترسی',
		'edit_others_posts',
		'catalyzer-codes',
		'catalyzer_render_codes_page'
	);
}
add_action( 'admin_menu', 'catalyzer_codes_menu' );

/**
 * محتواهای قفل‌داری که می‌شود برای‌شان کد ساخت.
 *
 * @return WP_Post[]
 */
function catalyzer_lockable_posts() {
	return get_posts( array(
		'post_type'      => catalyzer_lockable_types(),
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'type title',
		'order'          => 'ASC',
		'meta_key'       => '_cat_access_type', // phpcs:ignore WordPress.DB.SlowDBQuery
		'meta_value'     => 'paid',             // phpcs:ignore WordPress.DB.SlowDBQuery
	) );
}

/**
 * ساخت دسته‌ای کد.
 *
 * @param array $args targets، all، count، max، expires، note.
 * @return string[] کدهای ساخته‌شده.
 */
function catalyzer_generate_codes( $args ) {
	$count   = min( 100, max( 1, (int) $args['count'] ) );
	$max     = max( 1, (int) $args['max'] );
	$all     = ! empty( $args['all'] );
	$targets = array_values( array_filter( array_map( 'intval', (array) $args['targets'] ) ) );
	$expires = $args['expires'] ? sanitize_text_field( $args['expires'] ) : '';
	$note    = sanitize_text_field( $args['note'] );

	$made = array();
	for ( $i = 0; $i < $count; $i++ ) {

		// در عمل هرگز تکرار نمی‌شود، ولی اگر شد دوباره می‌سازیم.
		$tries = 0;
		do {
			$code = catalyzer_new_code_string();
			$tries++;
		} while ( catalyzer_find_code( $code ) && $tries < 10 );

		$post_id = wp_insert_post( array(
			'post_type'   => 'catalyzer_code',
			'post_status' => 'publish',
			'post_title'  => $code,
		) );
		if ( ! $post_id || is_wp_error( $post_id ) ) {
			continue;
		}

		update_post_meta( $post_id, '_cat_code_all', $all ? '1' : '' );
		update_post_meta( $post_id, '_cat_code_targets', $all ? array() : $targets );
		update_post_meta( $post_id, '_cat_code_max', $max );
		update_post_meta( $post_id, '_cat_code_used', array() );
		update_post_meta( $post_id, '_cat_code_expires', $expires );
		update_post_meta( $post_id, '_cat_code_note', $note );

		$made[] = $code;
	}
	return $made;
}

/**
 * صفحه‌ی مدیریت کدها.
 */
function catalyzer_render_codes_page() {
	if ( ! current_user_can( 'edit_others_posts' ) ) {
		wp_die( esc_html__( 'دسترسی ندارید.', 'catalyzer' ) );
	}

	$made = array();

	// ساخت.
	if ( isset( $_POST['catalyzer_codes_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['catalyzer_codes_nonce'] ), 'catalyzer_codes' ) ) {
		if ( isset( $_POST['cat_delete_code'] ) ) {
			$id = (int) $_POST['cat_delete_code'];
			if ( $id && 'catalyzer_code' === get_post_type( $id ) ) {
				wp_delete_post( $id, true );
				echo '<div class="notice notice-success is-dismissible"><p>کد حذف شد.</p></div>';
			}
		} else {
			$made = catalyzer_generate_codes( array(
				'count'   => isset( $_POST['cat_count'] ) ? (int) $_POST['cat_count'] : 1,
				'max'     => isset( $_POST['cat_max'] ) ? (int) $_POST['cat_max'] : 1,
				'all'     => isset( $_POST['cat_all'] ) && '1' === $_POST['cat_all'],
				'targets' => isset( $_POST['cat_targets'] ) ? (array) wp_unslash( $_POST['cat_targets'] ) : array(),
				'expires' => isset( $_POST['cat_expires'] ) ? wp_unslash( $_POST['cat_expires'] ) : '',
				'note'    => isset( $_POST['cat_note'] ) ? wp_unslash( $_POST['cat_note'] ) : '',
			) );
		}
	}

	$lockable = catalyzer_lockable_posts();
	$codes    = get_posts( array(
		'post_type'      => 'catalyzer_code',
		'post_status'    => 'publish',
		'posts_per_page' => 200,
		'orderby'        => 'date',
		'order'          => 'DESC',
	) );
	?>
	<div class="wrap">
		<h1>کدهای دسترسی</h1>
		<p style="max-width:70ch">
			تا وقتی درگاه پرداخت نداریم، خرید بیرون از سایت انجام می‌شود و قفل با کد باز می‌شود:
			اینجا کد می‌سازی، به خریدار می‌دهی، و او کد را در پنل کاربری‌اش وارد می‌کند.
		</p>

		<?php if ( $made ) : ?>
			<div class="notice notice-success">
				<p><strong><?php echo esc_html( sprintf( '%s کد ساخته شد.', number_format_i18n( count( $made ) ) ) ); ?></strong> همین حالا کپی‌شان کن:</p>
				<textarea readonly rows="<?php echo esc_attr( min( 10, count( $made ) ) ); ?>" style="width:100%;max-width:420px;font-family:monospace;direction:ltr"><?php echo esc_textarea( implode( "\n", $made ) ); ?></textarea>
			</div>
		<?php endif; ?>

		<h2>ساخت کد تازه</h2>
		<form method="post">
			<?php wp_nonce_field( 'catalyzer_codes', 'catalyzer_codes_nonce' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row">چه چیزی را باز کند؟</th>
					<td>
						<label style="display:block;margin-bottom:8px">
							<input type="radio" name="cat_all" value="1" checked> همه‌ی جزوه‌های خریدنی
						</label>
						<label style="display:block;margin-bottom:8px">
							<input type="radio" name="cat_all" value="0"> فقط موارد انتخاب‌شده:
						</label>
						<?php if ( ! $lockable ) : ?>
							<p class="description">هنوز محتوای خریدنی‌ای وجود ندارد. اول دسترسی یک جزوه یا ویدیو را «خریدنی» کن.</p>
						<?php else : ?>
							<select name="cat_targets[]" multiple size="<?php echo esc_attr( min( 10, max( 3, count( $lockable ) ) ) ); ?>" style="min-width:420px">
								<?php foreach ( $lockable as $item ) : ?>
									<option value="<?php echo esc_attr( $item->ID ); ?>">
										<?php echo esc_html( ( 'note' === $item->post_type ? 'جزوه: ' : 'ویدیو: ' ) . $item->post_title ); ?>
									</option>
								<?php endforeach; ?>
							</select>
							<p class="description">برای انتخاب چندتایی، کلید Ctrl را نگه دار.</p>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="cat_count">چند کد</label></th>
					<td><input type="number" id="cat_count" name="cat_count" value="1" min="1" max="100" class="small-text"></td>
				</tr>
				<tr>
					<th scope="row"><label for="cat_max">هر کد چند بار</label></th>
					<td>
						<input type="number" id="cat_max" name="cat_max" value="1" min="1" max="500" class="small-text">
						<p class="description">برای فروش تکی همان ۱ درست است. برای یک کلاس یا کمپین می‌توانی عدد بزرگ‌تری بگذاری.</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="cat_expires">تاریخ انقضا</label></th>
					<td><input type="date" id="cat_expires" name="cat_expires"> <span class="description">خالی یعنی بدون انقضا.</span></td>
				</tr>
				<tr>
					<th scope="row"><label for="cat_note">یادداشت</label></th>
					<td>
						<input type="text" id="cat_note" name="cat_note" class="regular-text" placeholder="مثلاً: نام خریدار یا شماره‌ی رسید">
						<p class="description">فقط برای خودت است؛ کاربر آن را نمی‌بیند.</p>
					</td>
				</tr>
			</table>
			<?php submit_button( 'ساخت کد' ); ?>
		</form>

		<h2>کدهای موجود</h2>
		<?php if ( ! $codes ) : ?>
			<p>هنوز کدی ساخته نشده است.</p>
		<?php else : ?>
			<table class="widefat striped">
				<thead>
					<tr>
						<th>کد</th>
						<th>باز می‌کند</th>
						<th>مصرف</th>
						<th>انقضا</th>
						<th>یادداشت</th>
						<th>ساخته‌شده</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ( $codes as $code ) : ?>
					<?php
					$all     = '1' === get_post_meta( $code->ID, '_cat_code_all', true );
					$targets = catalyzer_code_targets( $code );
					$uses    = catalyzer_code_uses( $code );
					$max     = max( 1, (int) get_post_meta( $code->ID, '_cat_code_max', true ) );
					$expires = (string) get_post_meta( $code->ID, '_cat_code_expires', true );
					$spent   = count( $uses ) >= $max;
					?>
					<tr>
						<td><code style="direction:ltr;display:inline-block"><?php echo esc_html( $code->post_title ); ?></code></td>
						<td>
							<?php if ( $all ) : ?>
								همه‌ی جزوه‌های خریدنی
								<small style="color:#666">(<?php echo esc_html( number_format_i18n( count( $targets ) ) ); ?> مورد)</small>
							<?php else : ?>
								<?php echo esc_html( implode( '، ', array_map( 'get_the_title', $targets ) ) ); ?>
							<?php endif; ?>
						</td>
						<td<?php echo $spent ? ' style="color:#a00"' : ''; ?>>
							<?php echo esc_html( number_format_i18n( count( $uses ) ) . ' از ' . number_format_i18n( $max ) ); ?>
							<?php if ( $uses ) : ?>
								<br><small style="color:#666">
								<?php
								$names = array();
								foreach ( $uses as $use ) {
									$u       = get_userdata( (int) $use['user'] );
									$names[] = $u ? $u->display_name : '#' . (int) $use['user'];
								}
								echo esc_html( implode( '، ', $names ) );
								?>
								</small>
							<?php endif; ?>
						</td>
						<td><?php echo $expires ? esc_html( $expires ) : '—'; ?></td>
						<td><?php echo esc_html( (string) get_post_meta( $code->ID, '_cat_code_note', true ) ); ?></td>
						<td><?php echo esc_html( get_the_date( 'Y/m/d', $code ) ); ?></td>
						<td>
							<form method="post" onsubmit="return confirm('این کد حذف شود؟');" style="margin:0">
								<?php wp_nonce_field( 'catalyzer_codes', 'catalyzer_codes_nonce' ); ?>
								<input type="hidden" name="cat_delete_code" value="<?php echo esc_attr( $code->ID ); ?>">
								<button type="submit" class="button-link delete">حذف</button>
							</form>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * پیام نتیجه‌ی مصرف کد، اگر در آدرس بود.
 */
function catalyzer_redeem_notice() {
	// phpcs:disable WordPress.Security.NonceVerification.Recommended
	if ( isset( $_GET['code_error'] ) ) {
		echo '<p class="auth-note auth-note-err">' . esc_html( sanitize_text_field( wp_unslash( $_GET['code_error'] ) ) ) . '</p>';
		return;
	}
	if ( isset( $_GET['code_ok'] ) ) {
		$n   = (int) $_GET['code_ok'];
		$msg = $n > 0
			? sprintf( 'کد پذیرفته شد. %s مورد برایت باز شد.', number_format_i18n( $n ) )
			: 'کد پذیرفته شد، ولی این محتواها از قبل برایت باز بودند.';
		echo '<p class="auth-note auth-note-ok">' . esc_html( $msg ) . '</p>';
	}
	// phpcs:enable WordPress.Security.NonceVerification.Recommended
}
