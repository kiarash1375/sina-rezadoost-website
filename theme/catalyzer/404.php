<?php
/**
 * صفحه‌ی ۴۰۴.
 *
 * @package Catalyzer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<div class="section" style="min-height:52vh;display:grid;place-items:center;text-align:center">
	<div class="wrap">
		<p class="eyebrow" style="justify-content:center">۴۰۴</p>
		<h1><?php esc_html_e( 'این صفحه پیدا نشد', 'catalyzer' ); ?></h1>
		<p class="lede" style="margin:14px auto 26px;max-width:44ch"><?php esc_html_e( 'شاید آدرس اشتباه است یا صفحه جابه‌جا شده. از اینجا ادامه بده:', 'catalyzer' ); ?></p>
		<a class="btn btn-primary" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'بازگشت به خانه', 'catalyzer' ); ?></a>
	</div>
</div>
<?php
get_footer();
