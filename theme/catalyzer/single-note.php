<?php
/**
 * تک‌صفحه‌ی جزوه.
 *
 * صفحه همیشه معرفیِ جزوه را نشان می‌دهد — عنوان، سرفصل‌ها، تعداد صفحه — و فقط
 * دکمه‌های دانلود پشت قفل می‌مانند. کسی که هنوز نخریده باید بداند دارد چه چیزی
 * می‌خرد.
 *
 * @package Catalyzer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	$cat_id       = get_the_ID();
	$cat_locked   = catalyzer_is_locked( $cat_id );
	$cat_open     = catalyzer_user_can_access( $cat_id );
	$cat_files    = catalyzer_note_available_files( $cat_id );
	$cat_sub      = get_post_meta( $cat_id, '_cat_subtitle', true );
	$cat_pages    = get_post_meta( $cat_id, '_cat_pages', true );
	$cat_features = catalyzer_lines( get_post_meta( $cat_id, '_cat_features', true ) );
	$cat_terms    = get_the_terms( $cat_id, 'note_cat' );
	?>
	<div class="page-hero">
		<div class="wrap">
			<p class="eyebrow">
				<?php esc_html_e( 'جزوه', 'catalyzer' ); ?>
				<?php echo $cat_pages ? ' · ' . esc_html( catalyzer_fa_digits( $cat_pages ) . ' صفحه' ) : ''; ?>
			</p>
			<h1><?php the_title(); ?></h1>
			<?php if ( $cat_sub ) : ?>
				<p><?php echo esc_html( $cat_sub ); ?></p>
			<?php endif; ?>
		</div>
	</div>

	<div class="section">
		<div class="wrap">
			<div class="note-single">
				<div class="note-single-main">
					<?php if ( has_post_thumbnail() ) : ?>
						<div class="note-cover"><?php the_post_thumbnail( 'large' ); ?></div>
					<?php endif; ?>

					<?php if ( get_the_content() ) : ?>
						<div class="entry"><?php the_content(); ?></div>
					<?php endif; ?>

					<?php if ( $cat_features ) : ?>
						<div class="note-features">
							<h2><?php esc_html_e( 'داخلش چیست', 'catalyzer' ); ?></h2>
							<ul>
								<?php foreach ( $cat_features as $cat_line ) : ?>
									<li>
										<span class="tick"><?php echo catalyzer_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
										<?php echo esc_html( $cat_line ); ?>
									</li>
								<?php endforeach; ?>
							</ul>
						</div>
					<?php endif; ?>
				</div>

				<aside class="note-single-side">
					<?php catalyzer_redeem_notice(); ?>

					<?php if ( $cat_open ) : ?>
						<div class="note-download-box">
							<h2>
								<span class="tick"><?php echo catalyzer_icon( 'unlock' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
								<?php esc_html_e( 'دانلود', 'catalyzer' ); ?>
							</h2>
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
								<p class="form-note"><?php esc_html_e( 'لینک‌ها به حساب تو گره خورده‌اند و برای کس دیگری کار نمی‌کنند.', 'catalyzer' ); ?></p>
							<?php else : ?>
								<p class="form-note"><?php esc_html_e( 'فایل این جزوه هنوز بارگذاری نشده است.', 'catalyzer' ); ?></p>
							<?php endif; ?>
						</div>
					<?php else : ?>
						<?php get_template_part( 'template-parts/lock-panel' ); ?>
					<?php endif; ?>

					<?php if ( $cat_terms && ! is_wp_error( $cat_terms ) ) : ?>
						<p class="lesson-terms">
							<?php esc_html_e( 'دسته‌بندی:', 'catalyzer' ); ?>
							<?php foreach ( $cat_terms as $cat_one ) : ?>
								<a href="<?php echo esc_url( get_term_link( $cat_one ) ); ?>"><?php echo esc_html( $cat_one->name ); ?></a>
							<?php endforeach; ?>
						</p>
					<?php endif; ?>

					<a class="btn btn-ghost" href="<?php echo esc_url( get_post_type_archive_link( 'note' ) ); ?>">
						<?php esc_html_e( 'همه‌ی جزوه‌ها', 'catalyzer' ); ?>
					</a>
				</aside>
			</div>
		</div>
	</div>
	<?php
endwhile;

get_footer();
