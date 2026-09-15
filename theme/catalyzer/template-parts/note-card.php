<?php
/**
 * یک کارت جزوه، مشترک بین صفحه‌ی فرود، بایگانی و پنل کاربری.
 *
 * کارتِ جزوه‌ی رایگان و جزوه‌ی بازشده مستقیم دکمه‌ی دانلود دارد؛ کارتِ قفل‌دار
 * قیمت را نشان می‌دهد و به صفحه‌ی خودش می‌برد.
 *
 * @package Catalyzer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cat_id     = get_the_ID();
$cat_locked = catalyzer_is_locked( $cat_id );
$cat_open   = catalyzer_user_can_access( $cat_id );
$cat_files  = $cat_open ? catalyzer_note_available_files( $cat_id ) : array();
$cat_sub    = get_post_meta( $cat_id, '_cat_subtitle', true );
$cat_pages  = get_post_meta( $cat_id, '_cat_pages', true );
$cat_price  = catalyzer_price( $cat_id );

$cat_terms = array();
foreach ( (array) get_the_terms( $cat_id, 'note_cat' ) as $cat_term ) {
	if ( $cat_term instanceof WP_Term ) {
		$cat_terms[] = $cat_term->name;
	}
}
?>
<article class="note<?php echo $cat_locked && ! $cat_open ? ' is-locked' : ''; ?>">
	<a class="note-head" href="<?php the_permalink(); ?>">
		<span class="note-mark">
			<?php echo catalyzer_icon( $cat_locked && ! $cat_open ? 'lock' : 'pdf' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</span>
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
		<?php if ( $cat_pages ) : ?>
			<span><?php echo esc_html( catalyzer_fa_digits( $cat_pages ) . ' صفحه' ); ?></span>
		<?php endif; ?>
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
			<?php foreach ( $cat_files as $cat_slot => $cat_file ) : ?>
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
			<?php echo esc_html( $cat_locked ? 'تهیه‌ی این جزوه' : 'مشاهده‌ی جزوه' ); ?>
		</a>
	<?php endif; ?>
</article>
