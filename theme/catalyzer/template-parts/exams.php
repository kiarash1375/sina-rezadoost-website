<?php
/**
 * بخش «آزمون‌ها» در صفحه‌ی فرود.
 *
 * @package Catalyzer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$count = (int) catalyzer_exams_opt( 'exams_count' );
$count = $count > 0 ? $count : 6;

$q = new WP_Query( array(
	'post_type'      => 'exam',
	'posts_per_page' => $count,
	'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
	'no_found_rows'  => true,
) );

if ( ! $q->have_posts() && ! current_user_can( 'edit_posts' ) ) {
	return;
}

$more_url = catalyzer_opt( 'exams_more_url' );
if ( ! $more_url && post_type_exists( 'exam' ) ) {
	$more_url = get_post_type_archive_link( 'exam' );
}
?>
<section class="section" id="exams">
	<div class="wrap">
		<div class="section-head reveal">
			<?php if ( catalyzer_exams_opt( 'exams_eyebrow' ) ) : ?>
				<p class="eyebrow"><?php echo esc_html( catalyzer_exams_opt( 'exams_eyebrow' ) ); ?></p>
			<?php endif; ?>
			<h2><?php echo esc_html( catalyzer_exams_opt( 'exams_heading' ) ); ?></h2>
			<?php if ( catalyzer_exams_opt( 'exams_intro' ) ) : ?>
				<p><?php echo esc_html( catalyzer_exams_opt( 'exams_intro' ) ); ?></p>
			<?php endif; ?>
		</div>

		<?php if ( $q->have_posts() ) : ?>
			<div class="notes-grid stagger">
				<?php
				while ( $q->have_posts() ) :
					$q->the_post();
					get_template_part( 'template-parts/library-card' );
				endwhile;
				?>
			</div>

			<?php if ( $more_url ) : ?>
				<div class="videos-more">
					<a class="btn btn-ghost" href="<?php echo esc_url( $more_url ); ?>">
						<?php echo esc_html( catalyzer_exams_opt( 'exams_more_text' ) ); ?>
					</a>
				</div>
			<?php endif; ?>
		<?php else : ?>
			<p class="form-note">هنوز آزمونی ثبت نشده است. از پیشخوان → «آزمون‌ها» → «افزودن آزمون» اضافه‌شان کنید. (این پیام فقط برای مدیر دیده می‌شود.)</p>
		<?php endif; ?>
	</div>
</section>
<?php
wp_reset_postdata();
