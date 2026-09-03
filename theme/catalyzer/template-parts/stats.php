<?php
/**
 * نوار آمار.
 *
 * @package Catalyzer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rows = array();
for ( $i = 1; $i <= 4; $i++ ) {
	$v = catalyzer_opt( "stat{$i}_value" );
	$k = catalyzer_opt( "stat{$i}_label" );
	if ( $v || $k ) {
		$rows[] = array( $v, $k );
	}
}
if ( ! $rows ) {
	return;
}
?>
<section class="stats" aria-label="<?php esc_attr_e( 'یک نگاه به کارنامه', 'catalyzer' ); ?>">
	<div class="wrap stagger" data-cols="<?php echo (int) min( 4, max( 1, count( $rows ) ) ); ?>">
		<?php foreach ( $rows as $row ) : ?>
			<div class="stat">
				<div class="v"><?php echo esc_html( $row[0] ); ?></div>
				<div class="k"><?php echo esc_html( $row[1] ); ?></div>
			</div>
		<?php endforeach; ?>
	</div>
</section>
