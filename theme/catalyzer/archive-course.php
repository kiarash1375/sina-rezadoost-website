<?php
/**
 * بایگانی دوره‌ها.
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
		<p class="eyebrow"><?php echo esc_html( catalyzer_opt( 'courses_eyebrow', 'دوره‌ها و پکیج‌ها' ) ); ?></p>
		<h1><?php echo esc_html( catalyzer_opt( 'courses_heading', 'دوره‌ها و پکیج‌های آموزشی' ) ); ?></h1>
		<?php if ( catalyzer_opt( 'courses_intro' ) ) : ?>
			<p><?php echo esc_html( catalyzer_opt( 'courses_intro' ) ); ?></p>
		<?php endif; ?>
	</div>
</div>
<div class="section" id="courses">
	<div class="wrap">
		<?php if ( have_posts() ) : ?>
			<div class="courses-grid stagger">
				<?php
				while ( have_posts() ) :
					the_post();
					$featured = get_post_meta( get_the_ID(), '_cat_featured', true );
					$badge    = get_post_meta( get_the_ID(), '_cat_badge', true );
					$subtitle = get_post_meta( get_the_ID(), '_cat_subtitle', true );
					$price    = get_post_meta( get_the_ID(), '_cat_price', true );
					$old      = get_post_meta( get_the_ID(), '_cat_old_price', true );
					$currency = get_post_meta( get_the_ID(), '_cat_currency', true ) ?: 'تومان';
					$features = catalyzer_lines( get_post_meta( get_the_ID(), '_cat_features', true ) );
					?>
					<div class="course<?php echo $featured ? ' featured' : ''; ?>">
						<?php if ( $badge ) : ?><span class="badge"><?php echo esc_html( $badge ); ?></span><?php endif; ?>
						<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
						<?php if ( $subtitle ) : ?><p class="desc"><?php echo esc_html( $subtitle ); ?></p><?php endif; ?>
						<?php if ( $price ) : ?>
							<div class="price">
								<span class="amt"><?php echo esc_html( $price ); ?></span>
								<span class="cur"><?php echo esc_html( $currency ); ?></span>
								<?php if ( $old ) : ?><span class="old"><?php echo esc_html( $old ); ?></span><?php endif; ?>
							</div>
						<?php endif; ?>
						<?php if ( $features ) : ?>
							<ul class="feat">
								<?php foreach ( array_slice( $features, 0, 4 ) as $f ) : ?>
									<li><?php echo catalyzer_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <?php echo esc_html( $f ); ?></li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
						<a class="btn <?php echo $featured ? 'btn-primary' : 'btn-ghost'; ?> btn-block" href="<?php the_permalink(); ?>"><?php esc_html_e( 'جزئیات و ثبت‌نام', 'catalyzer' ); ?></a>
					</div>
				<?php endwhile; ?>
			</div>
			<?php catalyzer_pagination(); ?>
		<?php else : ?>
			<p class="lede"><?php esc_html_e( 'هنوز دوره‌ای ثبت نشده است.', 'catalyzer' ); ?></p>
		<?php endif; ?>
	</div>
</div>
<?php
get_footer();
