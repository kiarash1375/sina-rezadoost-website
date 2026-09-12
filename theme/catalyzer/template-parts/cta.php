<?php
/**
 * بنر فراخوان.
 *
 * مقدارهای پیش‌فرض همان‌هایی است که در «سفارشی‌سازی» ثبت شده؛ چون
 * catalyzer_opt() مستقیم get_theme_mod() را صدا می‌زند و پیش‌فرضِ
 * سفارشی‌سازی روی صفحه‌ی سایت اعمال نمی‌شود، اینجا تکرار می‌شوند تا
 * بنر روی سایتی که هنوز محتوایش پر نشده خالی نماند.
 *
 * @package Catalyzer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cta_heading   = catalyzer_opt( 'cta_heading', 'همین امروز شیمی‌ات را از نقطه‌ضعف به نقطه‌قوت تبدیل کن' );
$cta_text      = catalyzer_opt( 'cta_text', 'برای شروع، ثبت‌نام کن و دوره‌ات را انتخاب کن.' );
$cta_btn1_text = catalyzer_opt( 'cta_btn1_text', 'ثبت‌نام و شروع' );
$cta_btn1_url  = catalyzer_opt( 'cta_btn1_url', '' );
$cta_btn1_url  = $cta_btn1_url ? catalyzer_anchor_url( $cta_btn1_url ) : catalyzer_account_url();
$cta_btn2_text = catalyzer_opt( 'cta_btn2_text', 'دیدن دوره‌ها' );
$cta_btn2_url  = catalyzer_anchor_url( catalyzer_opt( 'cta_btn2_url', '#courses' ) );

if ( ! $cta_heading && ! $cta_text && ! $cta_btn1_text && ! $cta_btn2_text ) {
	return;
}
?>
<section class="section cta">
	<div class="wrap">
		<div>
			<?php if ( $cta_heading ) : ?>
				<h2><?php echo esc_html( $cta_heading ); ?></h2>
			<?php endif; ?>
			<?php if ( $cta_text ) : ?>
				<p><?php echo esc_html( $cta_text ); ?></p>
			<?php endif; ?>
		</div>
		<div class="cta-actions">
			<?php if ( $cta_btn1_text ) : ?>
				<a class="btn btn-primary" href="<?php echo esc_url( $cta_btn1_url ); ?>"><?php echo esc_html( $cta_btn1_text ); ?></a>
			<?php endif; ?>
			<?php if ( $cta_btn2_text ) : ?>
				<a class="btn btn-ghost" href="<?php echo esc_url( $cta_btn2_url ); ?>"><?php echo esc_html( $cta_btn2_text ); ?></a>
			<?php endif; ?>
		</div>
	</div>
</section>
