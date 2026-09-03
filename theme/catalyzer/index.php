<?php
/**
 * قالب پیش‌فرض — بلاگ / بایگانی عمومی.
 *
 * @package Catalyzer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<div class="section">
	<div class="wrap">
		<?php
		$posts_page_id = (int) get_option( 'page_for_posts' );
		$blog_title    = ( $posts_page_id && ! is_front_page() ) ? get_the_title( $posts_page_id ) : __( 'یادداشت‌ها', 'catalyzer' );
		?>
		<header class="section-head reveal">
			<h1 class="page-title"><?php echo esc_html( $blog_title ); ?></h1>
		</header>

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
			<p class="lede"><?php esc_html_e( 'چیزی برای نمایش نیست.', 'catalyzer' ); ?></p>
		<?php endif; ?>
	</div>
</div>
<?php
get_footer();
