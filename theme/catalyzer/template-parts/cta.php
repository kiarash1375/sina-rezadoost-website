<?php
/**
 * بنر فراخوان.
 *
 * @package Catalyzer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section class="section cta">
	<div class="wrap">
		<div>
			<h2><?php echo esc_html( catalyzer_opt( 'cta_heading' ) ); ?></h2>
			<?php if ( catalyzer_opt( 'cta_text' ) ) : ?>
				<p><?php echo esc_html( catalyzer_opt( 'cta_text' ) ); ?></p>
			<?php endif; ?>
		</div>
		<div class="cta-actions">
			<?php if ( catalyzer_opt( 'cta_btn1_text' ) ) : ?>
				<a class="btn btn-primary" href="<?php echo esc_url( catalyzer_anchor_url( catalyzer_opt( 'cta_btn1_url', catalyzer_account_url() ) ) ); ?>"><?php echo esc_html( catalyzer_opt( 'cta_btn1_text' ) ); ?></a>
			<?php endif; ?>
			<?php if ( catalyzer_opt( 'cta_btn2_text' ) ) : ?>
				<a class="btn btn-ghost" href="<?php echo esc_url( catalyzer_anchor_url( catalyzer_opt( 'cta_btn2_url', '#courses' ) ) ); ?>"><?php echo esc_html( catalyzer_opt( 'cta_btn2_text' ) ); ?></a>
			<?php endif; ?>
		</div>
	</div>
</section>
