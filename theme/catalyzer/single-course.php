<?php
/**
 * تک‌صفحه‌ی دوره.
 *
 * @package Catalyzer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	$subtitle = get_post_meta( get_the_ID(), '_cat_subtitle', true );
	$price    = get_post_meta( get_the_ID(), '_cat_price', true );
	$old      = get_post_meta( get_the_ID(), '_cat_old_price', true );
	$currency = get_post_meta( get_the_ID(), '_cat_currency', true ) ?: 'تومان';
	$features = catalyzer_lines( get_post_meta( get_the_ID(), '_cat_features', true ) );
	$btn_txt  = get_post_meta( get_the_ID(), '_cat_btn_text', true ) ?: 'ثبت‌نام و خرید';
	$btn_url  = get_post_meta( get_the_ID(), '_cat_btn_url', true );
	?>
	<div class="page-hero">
		<div class="wrap">
			<p class="eyebrow"><?php esc_html_e( 'دوره', 'catalyzer' ); ?></p>
			<h1><?php the_title(); ?></h1>
			<?php if ( $subtitle ) : ?>
				<p><?php echo esc_html( $subtitle ); ?></p>
			<?php endif; ?>
		</div>
	</div>

	<div class="section">
		<div class="wrap">
			<div class="about-grid">
				<div class="entry" style="max-width:none">
					<?php if ( has_post_thumbnail() ) : ?>
						<?php the_post_thumbnail( 'large' ); ?>
					<?php endif; ?>
					<?php the_content(); ?>
				</div>

				<div class="course featured" style="transform:none">
					<?php if ( $price ) : ?>
						<div class="price">
							<span class="amt"><?php echo esc_html( $price ); ?></span>
							<span class="cur"><?php echo esc_html( $currency ); ?></span>
							<?php if ( $old ) : ?><span class="old"><?php echo esc_html( $old ); ?></span><?php endif; ?>
						</div>
					<?php endif; ?>

					<?php if ( $features ) : ?>
						<ul class="feat">
							<?php foreach ( $features as $f ) : ?>
								<li><?php echo catalyzer_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <?php echo esc_html( $f ); ?></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>

					<?php if ( $btn_url ) : ?>
						<a class="btn btn-primary btn-block" href="<?php echo esc_url( $btn_url ); ?>"><?php echo esc_html( $btn_txt ); ?></a>
					<?php else : ?>
						<a class="btn btn-primary btn-block" href="<?php echo esc_url( catalyzer_anchor_url( '#contact' ) ); ?>"><?php esc_html_e( 'درخواست مشاوره', 'catalyzer' ); ?></a>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
	<?php
endwhile;

get_footer();
