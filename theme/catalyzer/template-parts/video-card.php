<?php
/**
 * یک کارت ویدیو، مشترک بین صفحه‌ی فرود و بایگانی.
 *
 * کارت دکمه است، نه پیوند: کلیک، ویدیو را داخل همین صفحه در لایت‌باکس باز
 * می‌کند. اگر جاسازی در دسترس نباشد به صفحه‌ی خود ویدیو برمی‌گردد تا کارت
 * هیچ‌وقت بن‌بست نشود.
 *
 * @package Catalyzer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cat_id    = get_the_ID();
$cat_dur   = get_post_meta( $cat_id, '_cat_duration', true );
$cat_free  = get_post_meta( $cat_id, '_cat_free', true );
$cat_label = function_exists( 'catalyzer_lesson_category_label' ) ? catalyzer_lesson_category_label( $cat_id ) : get_post_meta( $cat_id, '_cat_category', true );
$cat_embed = function_exists( 'catalyzer_lesson_embed' ) ? catalyzer_lesson_embed( $cat_id ) : '';
$cat_thumb = function_exists( 'catalyzer_lesson_thumb' ) ? catalyzer_lesson_thumb( $cat_id ) : '';

$cat_slugs = array();
foreach ( (array) get_the_terms( $cat_id, 'lesson_cat' ) as $cat_term ) {
	if ( $cat_term instanceof WP_Term ) {
		$cat_slugs[] = $cat_term->slug;
	}
}
?>
<?php if ( $cat_embed ) : ?>
	<article class="video" tabindex="0" role="button"
		data-cats="<?php echo esc_attr( implode( ' ', $cat_slugs ) ); ?>"
		data-embed="<?php echo esc_attr( rawurlencode( $cat_embed ) ); ?>"
		aria-label="<?php echo esc_attr( sprintf( 'پخش ویدیوی %s', get_the_title() ) ); ?>">
<?php else : ?>
	<a class="video" href="<?php the_permalink(); ?>" data-cats="<?php echo esc_attr( implode( ' ', $cat_slugs ) ); ?>">
<?php endif; ?>
		<div class="thumb">
			<?php echo $cat_thumb; // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php if ( $cat_free ) : ?>
				<span class="tag"><?php esc_html_e( 'رایگان', 'catalyzer' ); ?></span>
			<?php endif; ?>
			<span class="play"><?php echo catalyzer_icon( 'play' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
			<?php if ( $cat_dur ) : ?>
				<span class="dur"><?php echo esc_html( $cat_dur ); ?></span>
			<?php endif; ?>
		</div>
		<div class="meta">
			<?php if ( $cat_label ) : ?>
				<span class="cat"><?php echo esc_html( $cat_label ); ?></span>
			<?php endif; ?>
			<h3><?php the_title(); ?></h3>
		</div>
<?php if ( $cat_embed ) : ?>
	</article>
<?php else : ?>
	</a>
<?php endif; ?>
