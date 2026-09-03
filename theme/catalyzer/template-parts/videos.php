<?php
/**
 * بخش «ویدیوهای کلاس» — از نوع محتوای lesson.
 *
 * @package Catalyzer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$count = (int) catalyzer_opt( 'videos_count', 6 );
$count = $count > 0 ? $count : 6;

$q = new WP_Query( array(
	'post_type'      => 'lesson',
	'posts_per_page' => $count,
	'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
	'no_found_rows'  => true,
) );

if ( ! $q->have_posts() && ! current_user_can( 'edit_posts' ) ) {
	return;
}

$more_url = catalyzer_opt( 'videos_more_url' );
if ( ! $more_url && post_type_exists( 'lesson' ) ) {
	$more_url = get_post_type_archive_link( 'lesson' );
}
?>
<section class="section" id="videos">
	<div class="wrap">
		<div class="section-head reveal">
			<?php if ( catalyzer_opt( 'videos_eyebrow' ) ) : ?>
				<p class="eyebrow"><?php echo esc_html( catalyzer_opt( 'videos_eyebrow' ) ); ?></p>
			<?php endif; ?>
			<h2><?php echo esc_html( catalyzer_opt( 'videos_heading' ) ); ?></h2>
			<?php if ( catalyzer_opt( 'videos_intro' ) ) : ?>
				<p><?php echo esc_html( catalyzer_opt( 'videos_intro' ) ); ?></p>
			<?php endif; ?>
		</div>

		<?php if ( $q->have_posts() ) : ?>
			<div class="videos-grid stagger">
				<?php
				while ( $q->have_posts() ) :
					$q->the_post();
					$dur  = get_post_meta( get_the_ID(), '_cat_duration', true );
					$catx = get_post_meta( get_the_ID(), '_cat_category', true );
					$free = get_post_meta( get_the_ID(), '_cat_free', true );
					?>
					<a class="video" href="<?php the_permalink(); ?>">
						<div class="thumb">
							<?php
							if ( has_post_thumbnail() ) {
								the_post_thumbnail( 'catalyzer-card', array( 'loading' => 'lazy', 'alt' => get_the_title() ) );
							} else {
								echo catalyzer_icon( 'rings' ); // phpcs:ignore WordPress.Security.EscapeOutput
							}
							?>
							<?php if ( $free ) : ?>
								<span class="tag"><?php esc_html_e( 'نمونه‌ی رایگان', 'catalyzer' ); ?></span>
							<?php endif; ?>
							<span class="play"><?php echo catalyzer_icon( 'play' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
							<?php if ( $dur ) : ?>
								<span class="dur"><?php echo esc_html( $dur ); ?></span>
							<?php endif; ?>
						</div>
						<div class="meta">
							<?php if ( $catx ) : ?>
								<span class="cat"><?php echo esc_html( $catx ); ?></span>
							<?php endif; ?>
							<h3><?php the_title(); ?></h3>
						</div>
					</a>
				<?php endwhile; ?>
			</div>

			<?php if ( $more_url ) : ?>
				<div class="videos-more">
					<a class="btn btn-ghost" href="<?php echo esc_url( $more_url ); ?>">
						<?php echo esc_html( catalyzer_opt( 'videos_more_text', 'همه‌ی ویدیوها' ) ); ?>
					</a>
				</div>
			<?php endif; ?>
		<?php else : ?>
			<p class="form-note">هنوز ویدیویی ثبت نشده است. از پیشخوان → «ویدیوهای کلاس» اضافه کنید. (این پیام فقط برای مدیر دیده می‌شود.)</p>
		<?php endif; ?>
	</div>
</section>
<?php
wp_reset_postdata();
