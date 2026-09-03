<?php
/**
 * بخش «متد کاتالیزور».
 *
 * @package Catalyzer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pillars = array();
for ( $i = 1; $i <= 3; $i++ ) {
	$t = catalyzer_opt( "pillar{$i}_title" );
	$x = catalyzer_opt( "pillar{$i}_text" );
	if ( $t || $x ) {
		$pillars[] = array( $t, $x );
	}
}
$digits = array( '', '۰۱', '۰۲', '۰۳' );
?>
<section class="section method" id="method">
	<div class="wrap">
		<div class="section-head reveal">
			<?php if ( catalyzer_opt( 'method_eyebrow' ) ) : ?>
				<p class="eyebrow"><?php echo esc_html( catalyzer_opt( 'method_eyebrow' ) ); ?></p>
			<?php endif; ?>
			<h2><?php echo esc_html( catalyzer_opt( 'method_heading' ) ); ?></h2>
			<?php if ( catalyzer_opt( 'method_intro' ) ) : ?>
				<p><?php echo esc_html( catalyzer_opt( 'method_intro' ) ); ?></p>
			<?php endif; ?>
		</div>

		<?php if ( $pillars ) : ?>
			<div class="pillars stagger">
				<?php foreach ( $pillars as $n => $p ) : ?>
					<div class="pillar">
						<span class="idx"><?php echo esc_html( isset( $digits[ $n + 1 ] ) ? $digits[ $n + 1 ] : ( $n + 1 ) ); ?></span>
						<h3><?php echo esc_html( $p[0] ); ?></h3>
						<p><?php echo esc_html( $p[1] ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
