<?php
/**
 * پاورقی سایت.
 *
 * @package Catalyzer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
</main><!-- .site-main -->

<footer class="site-footer">
	<div class="wrap">
		<a class="brand-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( catalyzer_opt( 'brand_name', 'کاتالیزور' ) ); ?>">
			<img src="<?php echo esc_url( catalyzer_footer_logo_url() ); ?>" alt="<?php esc_attr_e( 'لوگوی برند کاتالیزور', 'catalyzer' ); ?>" width="128" height="128" loading="lazy" decoding="async">
		</a>

		<?php catalyzer_footer_menu(); ?>

		<p class="fine">
			<span><?php echo esc_html( catalyzer_opt( 'footer_copy', '© ۱۴۰۴ کاتالیزور — دکتر سینا رضادوست. تمام حقوق محفوظ است.' ) ); ?></span>
			<span><?php echo esc_html( catalyzer_opt( 'footer_tagline', 'شتاب‌دهنده‌ی رشد شیمیِ کنکور' ) ); ?></span>
		</p>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
