<?php
/**
 * بخش «تماس» — اطلاعات + فرم درخواست مشاوره.
 *
 * @package Catalyzer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$sent   = isset( $_GET['catalyzer_sent'] ) ? sanitize_key( wp_unslash( $_GET['catalyzer_sent'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
$cf7    = trim( (string) catalyzer_opt( 'contact_cf7' ) );
$phone  = catalyzer_opt( 'contact_phone' );
$socials = array(
	'social_telegram'  => 'telegram',
	'social_instagram' => 'instagram',
	'social_youtube'   => 'youtube',
	'social_aparat'    => 'aparat',
);
?>
<section class="section" id="contact">
	<div class="wrap">
		<div class="contact-grid">
			<div class="contact-info reveal">
				<?php if ( catalyzer_opt( 'contact_eyebrow' ) ) : ?>
					<p class="eyebrow"><?php echo esc_html( catalyzer_opt( 'contact_eyebrow' ) ); ?></p>
				<?php endif; ?>
				<h2><?php echo esc_html( catalyzer_opt( 'contact_heading' ) ); ?></h2>

				<dl>
					<?php if ( catalyzer_opt( 'contact_telegram_value' ) ) : ?>
						<div>
							<dt><?php echo esc_html( catalyzer_opt( 'contact_telegram_label', 'تلگرام' ) ); ?></dt>
							<dd><?php echo esc_html( catalyzer_opt( 'contact_telegram_value' ) ); ?></dd>
						</div>
					<?php endif; ?>
					<?php if ( $phone ) : ?>
						<div>
							<dt><?php esc_html_e( 'شماره تماس', 'catalyzer' ); ?></dt>
							<dd class="ltr"><?php echo esc_html( $phone ); ?></dd>
						</div>
					<?php endif; ?>
					<?php if ( catalyzer_opt( 'contact_location' ) ) : ?>
						<div>
							<dt><?php esc_html_e( 'محل تدریس', 'catalyzer' ); ?></dt>
							<dd><?php echo esc_html( catalyzer_opt( 'contact_location' ) ); ?></dd>
						</div>
					<?php endif; ?>
				</dl>

				<?php
				$social_html = '';
				foreach ( $socials as $key => $icon ) {
					$url = catalyzer_opt( $key );
					if ( $url ) {
						$social_html .= '<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener" aria-label="' . esc_attr( $icon ) . '">' . catalyzer_icon( $icon ) . '</a>';
					}
				}
				if ( $social_html ) :
					?>
					<div class="socials"><?php echo $social_html; // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
				<?php endif; ?>
			</div>

			<div class="reveal">
				<?php if ( '1' === $sent ) : ?>
					<div class="form-ok">
						<?php echo catalyzer_icon( 'check-c' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						<h3><?php esc_html_e( 'درخواستت ثبت شد', 'catalyzer' ); ?></h3>
						<p><?php echo esc_html( catalyzer_opt( 'contact_form_note', 'در کوتاه‌ترین زمان با شما تماس می‌گیریم.' ) ); ?></p>
					</div>
				<?php elseif ( '0' === $sent ) : ?>
					<div class="form-err">
						<?php echo catalyzer_icon( 'alert-c' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						<h3><?php esc_html_e( 'ارسال نشد', 'catalyzer' ); ?></h3>
						<p><?php esc_html_e( 'لطفاً نام و شماره تماس را کامل وارد کنید و دوباره تلاش کنید.', 'catalyzer' ); ?></p>
					</div>
				<?php elseif ( $cf7 ) : ?>
					<?php echo do_shortcode( $cf7 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<?php else : ?>
					<form class="lead" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
						<input type="hidden" name="action" value="catalyzer_contact">
						<?php wp_nonce_field( 'catalyzer_contact', 'catalyzer_contact_nonce' ); ?>
						<div class="hp-field" aria-hidden="true">
							<label>وب‌سایت <input type="text" name="catalyzer_website" tabindex="-1" autocomplete="off"></label>
						</div>

						<label><?php esc_html_e( 'نام و نام خانوادگی', 'catalyzer' ); ?>
							<input type="text" name="catalyzer_name" required autocomplete="name" placeholder="مثلاً علی محمدی">
						</label>
						<label><?php esc_html_e( 'شماره تماس', 'catalyzer' ); ?>
							<input type="tel" name="catalyzer_phone" required inputmode="tel" placeholder="۰۹۱۲...">
						</label>
						<label><?php esc_html_e( 'رشته', 'catalyzer' ); ?>
							<select name="catalyzer_field">
								<option>تجربی</option>
								<option>ریاضی</option>
								<option>سایر</option>
							</select>
						</label>
						<label><?php esc_html_e( 'پیام (اختیاری)', 'catalyzer' ); ?>
							<textarea name="catalyzer_message" placeholder="چه دوره‌ای مدنظرت است؟"></textarea>
						</label>
						<button class="btn btn-primary btn-block" type="submit"><?php esc_html_e( 'ارسال درخواست مشاوره', 'catalyzer' ); ?></button>
						<?php if ( catalyzer_opt( 'contact_form_note' ) ) : ?>
							<p class="form-note"><?php echo esc_html( catalyzer_opt( 'contact_form_note' ) ); ?></p>
						<?php endif; ?>
					</form>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
