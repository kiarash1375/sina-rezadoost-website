<?php
/**
 * جعبه‌ی قفل — روی هر محتوایی که خریدنی است و هنوز برای کاربر باز نشده.
 *
 * سه حالت دارد:
 *   مهمان   → دعوت به ورود، چون کد به حساب گره می‌خورد.
 *   کاربر   → فرم وارد کردن کد + راه تهیه‌ی کد.
 *   خریدار  → اصلاً نمایش داده نمی‌شود؛ صدا زننده خودش بررسی کرده است.
 *
 * @package Catalyzer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cat_id       = get_the_ID();
$cat_price    = catalyzer_price( $cat_id );
$cat_currency = catalyzer_currency( $cat_id );
$cat_buy_note = catalyzer_notes_opt( 'buy_note' );
$cat_support  = catalyzer_contact_opt( 'support_telegram' );
$cat_telegram = catalyzer_telegram_url( $cat_support ? $cat_support : catalyzer_contact_opt( 'social_telegram' ) );
$cat_phone    = catalyzer_contact_opt( 'contact_phone' );
?>
<div class="lock-panel">
	<span class="lock-mark"><?php echo catalyzer_icon( 'lock' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>

	<div class="lock-body">
		<h2><?php esc_html_e( 'این مورد خریدنی است', 'catalyzer' ); ?></h2>

		<?php if ( $cat_price ) : ?>
			<p class="lock-price">
				<strong><?php echo esc_html( $cat_price ); ?></strong>
				<span><?php echo esc_html( $cat_currency ); ?></span>
			</p>
		<?php endif; ?>

		<p class="lock-how">
			<?php
			echo esc_html( $cat_buy_note );
			?>
		</p>

		<?php if ( $cat_telegram || $cat_phone ) : ?>
			<p class="lock-contact">
				<?php if ( $cat_telegram ) : ?>
					<a class="btn btn-ghost" href="<?php echo esc_url( $cat_telegram ); ?>" target="_blank" rel="noopener">
						<?php echo catalyzer_icon( 'telegram' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						<?php esc_html_e( 'گرفتن کد در تلگرام', 'catalyzer' ); ?>
					</a>
				<?php endif; ?>
				<?php if ( $cat_phone ) : ?>
					<a class="btn btn-ghost" href="tel:<?php echo esc_attr( catalyzer_latin_digits( $cat_phone ) ); ?>" dir="ltr">
						<?php echo catalyzer_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						<?php echo esc_html( $cat_phone ); ?>
					</a>
				<?php endif; ?>
			</p>
		<?php endif; ?>

		<?php catalyzer_redeem_notice(); ?>

		<?php if ( is_user_logged_in() ) : ?>
			<?php catalyzer_redeem_form( get_permalink( $cat_id ) ); ?>
		<?php else : ?>
			<p class="lock-login">
				<?php esc_html_e( 'کد به حساب کاربری گره می‌خورد، پس اول وارد شو:', 'catalyzer' ); ?>
			</p>
			<a class="btn btn-primary" href="<?php echo esc_url( add_query_arg( 'redirect_to', rawurlencode( get_permalink( $cat_id ) ), catalyzer_account_url() ) ); ?>">
				<?php esc_html_e( 'ورود / ثبت‌نام', 'catalyzer' ); ?>
			</a>
		<?php endif; ?>
	</div>
</div>
