<?php
/**
 * تک‌صفحه‌ی ویدیوی کلاس.
 *
 * @package Catalyzer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	$video = get_post_meta( get_the_ID(), '_cat_video_url', true );
	$dur   = get_post_meta( get_the_ID(), '_cat_duration', true );
	$catx  = get_post_meta( get_the_ID(), '_cat_category', true );
	$embed = $video ? catalyzer_video_embed( $video ) : '';
	?>
	<div class="page-hero">
		<div class="wrap">
			<p class="eyebrow">
				<?php echo esc_html( $catx ? $catx : 'ویدیوی کلاس' ); ?>
				<?php echo $dur ? ' · ' . esc_html( $dur ) : ''; ?>
			</p>
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

			<?php if ( get_the_content() ) : ?>
				<div class="entry" style="margin-top:34px"><?php the_content(); ?></div>
			<?php endif; ?>

			<div style="text-align:center;margin-top:40px">
				<a class="btn btn-ghost" href="<?php echo esc_url( get_post_type_archive_link( 'lesson' ) ); ?>"><?php esc_html_e( 'همه‌ی ویدیوها', 'catalyzer' ); ?></a>
			</div>
		</div>
	</div>
	<?php
endwhile;

get_footer();
