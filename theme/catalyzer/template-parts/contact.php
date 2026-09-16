<?php
/**
 * بخش «تماس با ما».
 *
 * فقط راه‌های ارتباطی — هر کدام که در «سفارشی‌سازی» خالی باشد اصلاً نمایش داده
 * نمی‌شود، تا صفحه با یک دکمه‌ی بی‌مقصد پر نشود.
 *
 * @package Catalyzer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cat_links = catalyzer_contact_links();
if ( ! $cat_links ) {
	return;
}
?>
<section class="section" id="contact">
	<div class="wrap">
		<div class="section-head reveal">
			<?php if ( catalyzer_contact_opt( 'contact_eyebrow' ) ) : ?>
				<p class="eyebrow"><?php echo esc_html( catalyzer_contact_opt( 'contact_eyebrow' ) ); ?></p>
			<?php endif; ?>
			<h2><?php echo esc_html( catalyzer_contact_opt( 'contact_heading' ) ); ?></h2>
			<?php if ( catalyzer_contact_opt( 'contact_intro' ) ) : ?>
				<p><?php echo esc_html( catalyzer_contact_opt( 'contact_intro' ) ); ?></p>
			<?php endif; ?>
		</div>

		<div class="contact-grid stagger">
			<?php foreach ( $cat_links as $cat_link ) : ?>
				<a class="contact-card"
					href="<?php echo esc_url( $cat_link['url'] ); ?>"
					<?php echo $cat_link['external'] ? 'target="_blank" rel="noopener"' : ''; ?>>
					<span class="contact-mark"><?php echo catalyzer_icon( $cat_link['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
					<span class="contact-body">
						<span class="contact-label"><?php echo esc_html( $cat_link['label'] ); ?></span>
						<span class="contact-value" dir="<?php echo esc_attr( $cat_link['dir'] ); ?>"><?php echo esc_html( $cat_link['value'] ); ?></span>
					</span>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>
