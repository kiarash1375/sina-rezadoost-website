<?php
/**
 * بخش «نظرات دانش‌آموزان» — از نوع محتوای testimonial.
 *
 * @package Catalyzer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$q = new WP_Query( array(
	'post_type'      => 'testimonial',
	'posts_per_page' => 6,
	'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
	'no_found_rows'  => true,
) );

if ( ! $q->have_posts() ) {
	return;
}
?>
<section class="section quotes">
	<div class="wrap">
		<div class="section-head reveal">
			<?php if ( catalyzer_opt( 'quotes_eyebrow' ) ) : ?>
				<p class="eyebrow"><?php echo esc_html( catalyzer_opt( 'quotes_eyebrow' ) ); ?></p>
			<?php endif; ?>
			<h2><?php echo esc_html( catalyzer_opt( 'quotes_heading' ) ); ?></h2>
		</div>

		<div class="quotes-grid stagger">
			<?php
			while ( $q->have_posts() ) :
				$q->the_post();
				?>
				<figure class="quote">
					<div class="qm">&rdquo;</div>
					<div><?php the_content(); ?></div>
					<?php if ( get_the_title() ) : ?>
						<figcaption class="who">— <?php the_title(); ?></figcaption>
					<?php endif; ?>
				</figure>
			<?php endwhile; ?>
		</div>
	</div>
</section>
<?php
wp_reset_postdata();
