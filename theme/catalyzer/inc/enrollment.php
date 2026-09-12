<?php
/**
 * دسترسی کاربر به دوره‌های خریداری‌شده.
 *
 * فهرست دوره‌های هر کاربر در متای `_cat_courses` نگهداری می‌شود (آرایه‌ای از
 * شناسه‌ی نوشته‌های «دوره»). دوره به سه راه به کاربر داده می‌شود:
 *
 *   ۱. دستی، از صفحه‌ی پروفایل کاربر در پیشخوان.
 *   ۲. خودکار، وقتی سفارش ووکامرسِ محصولِ متصل به دوره تکمیل شود.
 *   ۳. برنامه‌نویسی، با فراخوانی catalyzer_grant_course().
 *
 * @package Catalyzer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const CATALYZER_COURSES_META = '_cat_courses';

/* -------------------------------------------------------------------------
 * خواندن و نوشتن
 * ---------------------------------------------------------------------- */

/**
 * شناسه‌ی دوره‌هایی که کاربر به آن‌ها دسترسی دارد.
 *
 * فقط دوره‌های منتشرشده برگردانده می‌شوند؛ دوره‌ای که حذف یا پیش‌نویس شده
 * نباید در پنل ظاهر شود.
 *
 * @param int $user_id شناسه‌ی کاربر.
 * @return int[]
 */
function catalyzer_user_course_ids( $user_id = 0 ) {
	$user_id = $user_id ? (int) $user_id : get_current_user_id();
	if ( ! $user_id ) {
		return array();
	}

	$stored = get_user_meta( $user_id, CATALYZER_COURSES_META, true );
	$ids    = is_array( $stored ) ? array_map( 'intval', $stored ) : array();

	$ids = array_values( array_filter( array_unique( $ids ), function ( $id ) {
		return $id > 0 && 'course' === get_post_type( $id ) && 'publish' === get_post_status( $id );
	} ) );

	/**
	 * فیلتر دوره‌های کاربر — نقطه‌ی اتصال برای هر منبع دیگری (مثلاً افزونه‌ی LMS).
	 *
	 * @param int[] $ids     شناسه‌ی دوره‌ها.
	 * @param int   $user_id شناسه‌ی کاربر.
	 */
	$ids = (array) apply_filters( 'catalyzer_user_courses', $ids, $user_id );

	return array_values( array_unique( array_map( 'intval', $ids ) ) );
}

/**
 * آیا کاربر به این دوره دسترسی دارد؟
 */
function catalyzer_user_has_course( $course_id, $user_id = 0 ) {
	return in_array( (int) $course_id, catalyzer_user_course_ids( $user_id ), true );
}

/**
 * دادن دسترسی یک دوره به کاربر.
 *
 * @return bool آیا چیزی تغییر کرد.
 */
function catalyzer_grant_course( $user_id, $course_id ) {
	$user_id   = (int) $user_id;
	$course_id = (int) $course_id;
	if ( ! $user_id || 'course' !== get_post_type( $course_id ) ) {
		return false;
	}

	$stored = get_user_meta( $user_id, CATALYZER_COURSES_META, true );
	$ids    = is_array( $stored ) ? array_map( 'intval', $stored ) : array();
	if ( in_array( $course_id, $ids, true ) ) {
		return false;
	}

	$ids[] = $course_id;
	update_user_meta( $user_id, CATALYZER_COURSES_META, array_values( array_unique( $ids ) ) );

	/**
	 * پس از افزوده‌شدن دوره به حساب کاربر.
	 */
	do_action( 'catalyzer_course_granted', $user_id, $course_id );
	return true;
}

/**
 * گرفتن دسترسی یک دوره از کاربر.
 */
function catalyzer_revoke_course( $user_id, $course_id ) {
	$stored = get_user_meta( (int) $user_id, CATALYZER_COURSES_META, true );
	$ids    = is_array( $stored ) ? array_map( 'intval', $stored ) : array();
	$next   = array_values( array_diff( $ids, array( (int) $course_id ) ) );
	if ( count( $next ) === count( $ids ) ) {
		return false;
	}
	update_user_meta( (int) $user_id, CATALYZER_COURSES_META, $next );
	do_action( 'catalyzer_course_revoked', (int) $user_id, (int) $course_id );
	return true;
}

/**
 * همه‌ی دوره‌های منتشرشده — برای فهرست‌های مدیریتی.
 *
 * @return WP_Post[]
 */
function catalyzer_all_courses() {
	return get_posts( array(
		'post_type'      => 'course',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'menu_order title',
		'order'          => 'ASC',
	) );
}

/* -------------------------------------------------------------------------
 * ووکامرس: سفارش تکمیل‌شده → دسترسی به دوره
 * ---------------------------------------------------------------------- */

/**
 * دوره‌ای که به این محصول ووکامرس وصل است.
 *
 * @param int $product_id شناسه‌ی محصول.
 * @return int شناسه‌ی دوره یا صفر.
 */
function catalyzer_course_for_product( $product_id ) {
	$found = get_posts( array(
		'post_type'      => 'course',
		'post_status'    => 'publish',
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'meta_key'       => '_cat_wc_product',       // phpcs:ignore WordPress.DB.SlowDBQuery
		'meta_value'     => (string) (int) $product_id, // phpcs:ignore WordPress.DB.SlowDBQuery
	) );
	return $found ? (int) $found[0] : 0;
}

/**
 * با تکمیل سفارش، دوره‌های داخل آن را به خریدار بده.
 *
 * @param int $order_id شناسه‌ی سفارش.
 */
function catalyzer_grant_courses_from_order( $order_id ) {
	if ( ! function_exists( 'wc_get_order' ) ) {
		return;
	}
	$order = wc_get_order( $order_id );
	if ( ! $order ) {
		return;
	}
	$user_id = (int) $order->get_user_id();
	if ( ! $user_id ) {
		return; // سفارش مهمان — حسابی برای اتصال وجود ندارد.
	}

	foreach ( $order->get_items() as $item ) {
		$product_id = (int) $item->get_product_id();
		$course_id  = catalyzer_course_for_product( $product_id );
		if ( $course_id ) {
			catalyzer_grant_course( $user_id, $course_id );
		}
	}
}
add_action( 'woocommerce_order_status_completed', 'catalyzer_grant_courses_from_order' );
add_action( 'woocommerce_order_status_processing', 'catalyzer_grant_courses_from_order' );

/* -------------------------------------------------------------------------
 * پیشخوان: دادن دسترسی به‌صورت دستی
 * ---------------------------------------------------------------------- */

/**
 * فهرست دوره‌ها را به صفحه‌ی پروفایل کاربر اضافه می‌کند.
 *
 * @param WP_User $user کاربر در حال ویرایش.
 */
function catalyzer_user_courses_field( $user ) {
	if ( ! current_user_can( 'edit_users' ) ) {
		return;
	}
	$courses = catalyzer_all_courses();
	$owned   = catalyzer_user_course_ids( $user->ID );
	?>
	<h2><?php esc_html_e( 'دوره‌های این کاربر', 'catalyzer' ); ?></h2>
	<table class="form-table" role="presentation">
		<tr>
			<th><label><?php esc_html_e( 'دسترسی به دوره‌ها', 'catalyzer' ); ?></label></th>
			<td>
				<?php if ( ! $courses ) : ?>
					<p class="description"><?php esc_html_e( 'هنوز دوره‌ای ساخته نشده است.', 'catalyzer' ); ?></p>
				<?php else : ?>
					<?php wp_nonce_field( 'catalyzer_user_courses', 'catalyzer_user_courses_nonce' ); ?>
					<fieldset>
						<?php foreach ( $courses as $course ) : ?>
							<label style="display:block;margin-bottom:6px">
								<input type="checkbox" name="catalyzer_courses[]" value="<?php echo esc_attr( $course->ID ); ?>"
									<?php checked( in_array( (int) $course->ID, $owned, true ) ); ?>>
								<?php echo esc_html( $course->post_title ); ?>
							</label>
						<?php endforeach; ?>
					</fieldset>
					<p class="description">
						<?php esc_html_e( 'تیک‌خورده‌ها در پنل کاربری زیر «دوره‌های من» دیده می‌شوند. سفارش‌های ووکامرس هم پس از تکمیل، دوره را خودکار اضافه می‌کنند.', 'catalyzer' ); ?>
					</p>
				<?php endif; ?>
			</td>
		</tr>
	</table>
	<?php
}
add_action( 'show_user_profile', 'catalyzer_user_courses_field' );
add_action( 'edit_user_profile', 'catalyzer_user_courses_field' );

/**
 * ذخیره‌ی تیک‌های فهرست دوره‌ها.
 *
 * @param int $user_id شناسه‌ی کاربر.
 */
function catalyzer_save_user_courses( $user_id ) {
	if ( ! current_user_can( 'edit_users' ) ) {
		return;
	}
	$nonce = isset( $_POST['catalyzer_user_courses_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['catalyzer_user_courses_nonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'catalyzer_user_courses' ) ) {
		return; // فرم فهرست دوره‌ها ارسال نشده است.
	}

	$raw = isset( $_POST['catalyzer_courses'] ) ? (array) wp_unslash( $_POST['catalyzer_courses'] ) : array();
	$ids = array_values( array_filter( array_map( 'intval', $raw ), function ( $id ) {
		return $id > 0 && 'course' === get_post_type( $id );
	} ) );

	update_user_meta( $user_id, CATALYZER_COURSES_META, array_values( array_unique( $ids ) ) );
}
add_action( 'personal_options_update', 'catalyzer_save_user_courses' );
add_action( 'edit_user_profile_update', 'catalyzer_save_user_courses' );

/**
 * ستون «دوره‌ها» در فهرست کاربران.
 */
function catalyzer_users_course_column( $cols ) {
	$cols['catalyzer_courses'] = 'دوره‌ها';
	return $cols;
}
add_filter( 'manage_users_columns', 'catalyzer_users_course_column' );

function catalyzer_users_course_column_content( $out, $col, $user_id ) {
	if ( 'catalyzer_courses' !== $col ) {
		return $out;
	}
	$n = count( catalyzer_user_course_ids( $user_id ) );
	return $n ? esc_html( number_format_i18n( $n ) ) : '—';
}
add_filter( 'manage_users_custom_column', 'catalyzer_users_course_column_content', 10, 3 );
