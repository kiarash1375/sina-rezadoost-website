<?php
/**
 * بایگانی ویدیوهای کلاس — و همین فایل، بایگانی هر دسته‌بندی ویدیو.
 *
 * @package Catalyzer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cat_term  = is_tax( 'lesson_cat' ) ? get_queried_object() : null;
$cat_terms = function_exists( 'catalyzer_lesson_categories' ) ? catalyzer_lesson_categories() : array();

get_header();
?>
<div class="page-hero">
	<div class="wrap">
		<p class="eyebrow"><?php echo esc_html( catalyzer_opt( 'videos_eyebrow', 'ویدیوهای کلاس' ) ); ?></p>
		<h1>
			<?php
			echo esc_html( $cat_term instanceof WP_Term
				? $cat_term->name
				: catalyzer_opt( 'videos_heading', 'ویدیوهای کلاس' ) );
			?>
		</h1>
		<?php if ( $cat_term instanceof WP_Term && $cat_term->description ) : ?>
			<p><?php echo esc_html( $cat_term->description ); ?></p>
		<?php elseif ( ! $cat_term && catalyzer_opt( 'videos_intro' ) ) : ?>
			<p><?php echo esc_html( catalyzer_opt( 'videos_intro' ) ); ?></p>
		<?php endif; ?>
	</div>
</div>
<div class="section" id="videos">
	<div class="wrap">
		<?php if ( count( $cat_terms ) > 1 ) : ?>
			<nav class="video-filter" aria-label="<?php esc_attr_e( 'دسته‌بندی ویدیوها', 'catalyzer' ); ?>">
				<a class="video-filter-btn<?php echo $cat_term ? '' : ' is-on'; ?>"
					href="<?php echo esc_url( get_post_type_archive_link( 'lesson' ) ); ?>">
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

		<?php if ( have_posts() ) : ?>
			<div class="videos-grid stagger">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/video-card' );
				endwhile;
				?>
			</div>
			<?php catalyzer_pagination(); ?>
		<?php else : ?>
			<p class="lede"><?php esc_html_e( 'هنوز ویدیویی ثبت نشده است.', 'catalyzer' ); ?></p>
		<?php endif; ?>
	</div>
</div>
<?php
get_footer();
