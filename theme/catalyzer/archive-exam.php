<?php
/**
 * بایگانی آزمون‌ها — و همین فایل، بایگانی هر دسته‌بندی آزمون.
 *
 * @package Catalyzer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cat_term  = is_tax( 'exam_cat' ) ? get_queried_object() : null;
$cat_terms = get_terms( array( 'taxonomy' => 'exam_cat', 'hide_empty' => true ) );
$cat_terms = is_wp_error( $cat_terms ) ? array() : $cat_terms;

get_header();
?>
<div class="page-hero">
	<div class="wrap">
		<p class="eyebrow"><?php echo esc_html( catalyzer_exams_opt( 'exams_eyebrow' ) ); ?></p>
		<h1>
			<?php
			echo esc_html( $cat_term instanceof WP_Term
				? $cat_term->name
				: catalyzer_exams_opt( 'exams_heading' ) );
			?>
		</h1>
		<?php if ( $cat_term instanceof WP_Term && $cat_term->description ) : ?>
			<p><?php echo esc_html( $cat_term->description ); ?></p>
		<?php elseif ( ! $cat_term && catalyzer_exams_opt( 'exams_intro' ) ) : ?>
			<p><?php echo esc_html( catalyzer_exams_opt( 'exams_intro' ) ); ?></p>
		<?php endif; ?>
	</div>
</div>
<div class="section" id="exams">
	<div class="wrap">
		<?php if ( count( $cat_terms ) > 1 ) : ?>
			<nav class="video-filter" aria-label="<?php esc_attr_e( 'دسته‌بندی آزمون‌ها', 'catalyzer' ); ?>">
				<a class="video-filter-btn<?php echo $cat_term ? '' : ' is-on'; ?>"
					href="<?php echo esc_url( get_post_type_archive_link( 'exam' ) ); ?>">
					<?php esc_html_e( 'همه', 'catalyzer' ); ?>
				</a>
				<?php foreach ( $cat_terms as $cat_one ) : ?>
					<a class="video-filter-btn<?php echo ( $cat_term && $cat_term->term_id === $cat_one->term_id ) ? ' is-on' : ''; ?>"
						href="<?php echo esc_url( get_term_link( $cat_one ) ); ?>">
						<?php echo esc_html( $cat_one->name ); ?>
						<span class="video-filter-n"><?php echo esc_html( number_format_i18n( $cat_one->count ) ); ?></span>
					</a>
				<?php endforeach; ?>
			</nav>
		<?php endif; ?>

		<?php catalyzer_redeem_notice(); ?>

		<?php if ( have_posts() ) : ?>
			<div class="notes-grid stagger">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/library-card' );
				endwhile;
				?>
			</div>
			<?php catalyzer_pagination(); ?>
		<?php else : ?>
			<p class="lede"><?php esc_html_e( 'هنوز آزمونی ثبت نشده است.', 'catalyzer' ); ?></p>
		<?php endif; ?>
	</div>
</div>
<?php
get_footer();
