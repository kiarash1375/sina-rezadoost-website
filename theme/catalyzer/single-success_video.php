<?php
/**
 * تک‌صفحه‌ی ویدیوی رتبه‌برتر.
 *
 * @package Catalyzer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	$id    = get_the_ID();
	$video = get_post_meta( $id, '_cat_sv_url', true );
	$rank  = get_post_meta( $id, '_cat_sv_rank', true );
	$fld   = get_post_meta( $id, '_cat_sv_field', true );
	$year  = get_post_meta( $id, '_cat_sv_year', true );
	$dur   = get_post_meta( $id, '_cat_sv_duration', true );
	$quote = get_post_meta( $id, '_cat_sv_quote', true );
	$embed = $video ? catalyzer_video_embed( $video ) : '';

	$line = array_filter( array(
		$rank ? 'رتبه ' . $rank : '',
		$fld,
		$year ? 'کنکور ' . $year : '',
		$dur,
	) );
	?>
	<div class="page-hero">
		<div class="wrap">
			<p class="eyebrow"><?php echo esc_html( implode( ' · ', $line ) ); ?></p>
			<h1><?php the_title(); ?></h1>
		</div>
	</div>

	<div class="section">
		<div class="wrap">
			<?php if ( $embed ) : ?>
				<div class="lesson-embed">
					<div class="embed-frame"><?php echo $embed; // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
				</div>
			<?php elseif ( has_post_thumbnail() ) : ?>
				<div class="lesson-embed"><?php the_post_thumbnail( 'large' ); ?></div>
			<?php endif; ?>

			<?php if ( $quote ) : ?>
				<figure class="quote" style="margin-top:34px">
					<div class="qm">&rdquo;</div>
					<div><?php echo esc_html( $quote ); ?></div>
					<figcaption class="who">— <?php the_title(); ?></figcaption>
				</figure>
			<?php endif; ?>

			<?php if ( get_the_content() ) : ?>
				<div class="entry" style="margin-top:34px"><?php the_content(); ?></div>
			<?php endif; ?>

			<div style="text-align:center;margin-top:40px">
				<a class="btn btn-ghost" href="<?php echo esc_url( get_post_type_archive_link( 'success_video' ) ); ?>"><?php esc_html_e( 'همه‌ی رتبه‌برترها', 'catalyzer' ); ?></a>
			</div>
		</div>
	</div>
	<?php
endwhile;

get_footer();
