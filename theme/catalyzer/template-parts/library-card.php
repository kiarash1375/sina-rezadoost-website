<?php
/**
 * کارت مشترک جزوه و آزمون.
 *
 * هر دو یک شکل دارند: عکس، عنوان، چند مشخصه، و بعد یا دکمه‌های دانلود (اگر باز
 * است) یا قیمت و دکمه‌ی تهیه (اگر قفل است). تفاوتشان فقط در مشخصه‌هاست — جزوه
 * تعداد صفحه دارد، آزمون تعداد سؤال و سختی و زمان.
 *
 * @package Catalyzer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cat_id     = get_the_ID();
$cat_type   = get_post_type( $cat_id );
$cat_locked = catalyzer_is_locked( $cat_id );
$cat_open   = catalyzer_user_can_access( $cat_id );
$cat_files  = $cat_open ? catalyzer_note_available_files( $cat_id ) : array();
$cat_sub    = get_post_meta( $cat_id, '_cat_subtitle', true );
$cat_price  = catalyzer_price( $cat_id );
$cat_terms  = catalyzer_term_names( $cat_id );
$cat_facts  = catalyzer_library_facts( $cat_id );
?>
<article class="note<?php echo $cat_locked && ! $cat_open ? ' is-locked' : ''; ?>">
	<?php if ( has_post_thumbnail( $cat_id ) ) : ?>
		<a class="note-cover-thumb" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
			<?php echo get_the_post_thumbnail( $cat_id, 'catalyzer-cover', array( 'loading' => 'lazy', 'decoding' => 'async', 'alt' => '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</a>
	<?php endif; ?>

	<a class="note-head" href="<?php the_permalink(); ?>">
		<?php if ( ! has_post_thumbnail( $cat_id ) ) : ?>
			<span class="note-mark">
				<?php echo catalyzer_icon( catalyzer_library_icon( $cat_type, $cat_locked && ! $cat_open ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</span>
		<?php endif; ?>
		<span class="note-titles">
			<?php if ( $cat_terms ) : ?>
				<span class="cat"><?php echo esc_html( implode( ' · ', $cat_terms ) ); ?></span>
			<?php endif; ?>
			<h3><?php the_title(); ?></h3>
			<?php if ( $cat_sub ) : ?>
				<span class="note-sub"><?php echo esc_html( $cat_sub ); ?></span>
			<?php endif; ?>
		</span>
	</a>

	<p class="note-facts">
		<?php foreach ( $cat_facts as $cat_fact ) : ?>
			<span><?php echo esc_html( $cat_fact ); ?></span>
		<?php endforeach; ?>
		<?php if ( $cat_locked ) : ?>
			<span class="note-tag note-tag-paid"><?php esc_html_e( 'خریدنی', 'catalyzer' ); ?></span>
		<?php else : ?>
			<span class="note-tag note-tag-free"><?php esc_html_e( 'رایگان', 'catalyzer' ); ?></span>
		<?php endif; ?>
		<?php if ( $cat_locked && ! $cat_open && $cat_price ) : ?>
			<span class="note-price"><?php echo esc_html( $cat_price . ' ' . catalyzer_currency( $cat_id ) ); ?></span>
		<?php endif; ?>
	</p>

	<?php if ( $cat_files ) : ?>
		<div class="note-files">
			<?php foreach ( $cat_files as $cat_file ) : ?>
				<a class="note-dl" href="<?php echo esc_url( $cat_file['url'] ); ?>">
					<?php echo catalyzer_icon( 'download' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<span><?php echo esc_html( $cat_file['label'] ); ?></span>
					<?php if ( $cat_file['size'] ) : ?>
						<small><?php echo esc_html( $cat_file['size'] ); ?></small>
					<?php endif; ?>
				</a>
			<?php endforeach; ?>
		</div>
	<?php else : ?>
		<a class="btn btn-ghost note-open" href="<?php the_permalink(); ?>">
			<?php echo esc_html( catalyzer_library_cta( $cat_type, $cat_locked ) ); ?>
		</a>
	<?php endif; ?>
</article>
