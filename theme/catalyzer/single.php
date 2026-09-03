<?php
/**
 * تک‌نوشته (بلاگ).
 *
 * @package Catalyzer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<div class="page-hero">
		<div class="wrap">
			<p class="eyebrow"><?php echo esc_html( get_the_date() ); ?></p>
			<h1><?php the_title(); ?></h1>
		</div>
	</div>
	<div class="section">
		<div class="wrap">
			<?php if ( has_post_thumbnail() ) : ?>
				<figure class="entry" style="max-width:940px">
					<?php the_post_thumbnail( 'large' ); ?>
				</figure>
			<?php endif; ?>
			<div class="entry">
				<?php
				the_content();
				wp_link_pages( array( 'before' => '<nav class="pagination">', 'after' => '</nav>' ) );
				?>
			</div>
			<?php
			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}
			?>
		</div>
	</div>
	<?php
endwhile;

get_footer();
