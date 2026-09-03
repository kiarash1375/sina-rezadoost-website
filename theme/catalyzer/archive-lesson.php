<?php
/**
 * بایگانی ویدیوهای کلاس.
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
		<p class="eyebrow"><?php echo esc_html( catalyzer_opt( 'videos_eyebrow', 'ویدیوهای کلاس' ) ); ?></p>
		<h1><?php echo esc_html( catalyzer_opt( 'videos_heading', 'ویدیوهای کلاس' ) ); ?></h1>
		<?php if ( catalyzer_opt( 'videos_intro' ) ) : ?>
			<p><?php echo esc_html( catalyzer_opt( 'videos_intro' ) ); ?></p>
		<?php endif; ?>
	</div>
</div>
<div class="section" id="videos">
	<div class="wrap">
		<?php if ( have_posts() ) : ?>
			<div class="videos-grid stagger">
				<?php
				while ( have_posts() ) :
					the_post();
					$dur  = get_post_meta( get_the_ID(), '_cat_duration', true );
					$catx = get_post_meta( get_the_ID(), '_cat_category', true );
					$free = get_post_meta( get_the_ID(), '_cat_free', true );
					?>
					<a class="video" href="<?php the_permalink(); ?>">
						<div class="thumb">
							<?php
							if ( has_post_thumbnail() ) {
								the_post_thumbnail( 'catalyzer-card', array( 'loading' => 'lazy' ) );
							} else {
								echo catalyzer_icon( 'rings' ); // phpcs:ignore WordPress.Security.EscapeOutput
							}
							?>
							<?php if ( $free ) : ?><span class="tag"><?php esc_html_e( 'نمونه‌ی رایگان', 'catalyzer' ); ?></span><?php endif; ?>
							<span class="play"><?php echo catalyzer_icon( 'play' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
							<?php if ( $dur ) : ?><span class="dur"><?php echo esc_html( $dur ); ?></span><?php endif; ?>
						</div>
						<div class="meta">
							<?php if ( $catx ) : ?><span class="cat"><?php echo esc_html( $catx ); ?></span><?php endif; ?>
							<h3><?php the_title(); ?></h3>
						</div>
					</a>
				<?php endwhile; ?>
			</div>
			<?php catalyzer_pagination(); ?>
		<?php else : ?>
			<p class="lede"><?php esc_html_e( 'هنوز ویدیویی ثبت نشده است.', 'catalyzer' ); ?></p>
		<?php endif; ?>
	</div>
</div>
<?php
get_footer();
