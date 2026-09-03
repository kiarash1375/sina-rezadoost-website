<?php
/**
 * بخش «درباره» + رزومه.
 *
 * @package Catalyzer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$creds = catalyzer_lines( catalyzer_opt( 'about_creds' ) );
$body  = catalyzer_opt( 'about_body' );
?>
<section class="section" id="about">
	<div class="wrap">
		<div class="about-grid">
			<div class="about-copy reveal">
				<?php if ( catalyzer_opt( 'about_eyebrow' ) ) : ?>
					<p class="eyebrow"><?php echo esc_html( catalyzer_opt( 'about_eyebrow' ) ); ?></p>
				<?php endif; ?>
				<h2><?php echo esc_html( catalyzer_opt( 'about_heading', 'سینا رضادوست، دبیر شیمی کنکور' ) ); ?></h2>

				<?php
				if ( $body ) {
					$paras = preg_split( '/\n\s*\n/', trim( $body ) );
					foreach ( $paras as $para ) {
						echo '<p class="lede">' . wp_kses_post( trim( $para ) ) . '</p>';
					}
				}
				?>

				<?php if ( catalyzer_opt( 'about_sign' ) ) : ?>
					<p class="sign">
						<?php echo esc_html( catalyzer_opt( 'about_sign' ) ); ?>
						<?php if ( catalyzer_opt( 'about_sign_sub' ) ) : ?>
							<span><?php echo esc_html( catalyzer_opt( 'about_sign_sub' ) ); ?></span>
						<?php endif; ?>
					</p>
				<?php endif; ?>
			</div>

			<?php if ( $creds ) : ?>
				<div class="creds reveal">
					<?php
					foreach ( $creds as $line ) :
						$c = catalyzer_split_credential( $line );
						?>
						<div class="cred">
							<span class="hx"><?php echo catalyzer_icon( 'hexagon' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
							<p><strong><?php echo esc_html( $c['strong'] ); ?></strong><?php echo $c['rest'] ? ' — ' . esc_html( $c['rest'] ) : ''; ?></p>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
