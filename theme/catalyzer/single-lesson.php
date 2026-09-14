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
	$cat_id    = get_the_ID();
	$cat_dur   = get_post_meta( $cat_id, '_cat_duration', true );
	$cat_uid   = get_post_meta( $cat_id, '_cat_aparat_uid', true );
	$cat_watch = get_post_meta( $cat_id, '_cat_video_url', true );
	$cat_label = function_exists( 'catalyzer_lesson_category_label' ) ? catalyzer_lesson_category_label( $cat_id ) : get_post_meta( $cat_id, '_cat_category', true );
	$cat_embed = function_exists( 'catalyzer_lesson_embed' ) ? catalyzer_lesson_embed( $cat_id ) : '';
	$cat_terms = get_the_terms( $cat_id, 'lesson_cat' );
	?>
	<div class="page-hero">
		<div class="wrap">
			<p class="eyebrow">
				<?php echo esc_html( $cat_label ? $cat_label : 'ویدیوی کلاس' ); ?>
				<?php echo $cat_dur ? ' · ' . esc_html( $cat_dur ) : ''; ?>
			</p>
			<h1><?php the_title(); ?></h1>
		</div>
	</div>

	<div class="section">
		<div class="wrap">
			<?php if ( $cat_embed ) : ?>
				<div class="lesson-embed">
					<div class="embed-frame"><?php echo $cat_embed; // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
				</div>
			<?php elseif ( has_post_thumbnail() ) : ?>
				<div class="lesson-embed"><?php the_post_thumbnail( 'large' ); ?></div>
			<?php endif; ?>

			<?php if ( get_the_content() ) : ?>
				<div class="entry" style="margin-top:34px"><?php the_content(); ?></div>
			<?php endif; ?>

			<div class="lesson-foot">
				<?php if ( $cat_terms && ! is_wp_error( $cat_terms ) ) : ?>
					<p class="lesson-terms">
						<?php esc_html_e( 'دسته‌بندی:', 'catalyzer' ); ?>
						<?php foreach ( $cat_terms as $cat_one ) : ?>
							<a href="<?php echo esc_url( get_term_link( $cat_one ) ); ?>"><?php echo esc_html( $cat_one->name ); ?></a>
						<?php endforeach; ?>
					</p>
				<?php endif; ?>

				<a class="btn btn-ghost" href="<?php echo esc_url( get_post_type_archive_link( 'lesson' ) ); ?>"><?php esc_html_e( 'همه‌ی ویدیوها', 'catalyzer' ); ?></a>
				<?php if ( $cat_uid && $cat_watch ) : ?>
					<a class="lesson-source" href="<?php echo esc_url( $cat_watch ); ?>" target="_blank" rel="noopener">
						<?php esc_html_e( 'تماشا در آپارات', 'catalyzer' ); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<?php
endwhile;

get_footer();
