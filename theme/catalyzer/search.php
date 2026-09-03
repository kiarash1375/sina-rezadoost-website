<?php
/**
 * نتایج جستجو.
 *
 * @package Catalyzer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<div class="page-hero">
	<div class="wrap">
		<p class="eyebrow"><?php esc_html_e( 'جستجو', 'catalyzer' ); ?></p>
		<h1><?php printf( esc_html__( 'نتایج برای: %s', 'catalyzer' ), '<span>' . esc_html( get_search_query() ) . '</span>' ); ?></h1>
	</div>
</div>
<div class="section">
	<div class="wrap">
		<?php if ( have_posts() ) : ?>
			<div class="post-grid">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/content' );
				endwhile;
				?>
			</div>
			<?php catalyzer_pagination(); ?>
		<?php else : ?>
			<p class="lede"><?php esc_html_e( 'چیزی پیدا نشد. عبارت دیگری را امتحان کنید.', 'catalyzer' ); ?></p>
			<?php get_search_form(); ?>
		<?php endif; ?>
	</div>
</div>
<?php
get_footer();
