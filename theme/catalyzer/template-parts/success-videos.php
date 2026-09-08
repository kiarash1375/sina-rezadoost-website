<?php
/**
 * بخش «ویدیوهای رتبه‌برترها» — ویدیوهای کوتاه توصیه‌ی دانش‌آموزان برتر.
 *
 * @package Catalyzer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$count = (int) catalyzer_opt( 'success_count', 6 );
$count = $count > 0 ? $count : 6;

$q = new WP_Query( array(
	'post_type'      => 'success_video',
	'posts_per_page' => $count,
	'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
	'no_found_rows'  => true,
) );

if ( ! $q->have_posts() && ! current_user_can( 'edit_posts' ) ) {
	return;
}

$more_url = catalyzer_opt( 'success_more_url' );
if ( ! $more_url && post_type_exists( 'success_video' ) ) {
	$more_url = get_post_type_archive_link( 'success_video' );
}
?>
<section class="section success-videos" id="success">
	<div class="wrap">
		<div class="section-head reveal">
			<?php if ( catalyzer_opt( 'success_eyebrow' ) ) : ?>
				<p class="eyebrow"><?php echo esc_html( catalyzer_opt( 'success_eyebrow' ) ); ?></p>
			<?php endif; ?>
			<h2><?php echo esc_html( catalyzer_opt( 'success_heading', 'رتبه‌برترها چه می‌گویند' ) ); ?></h2>
			<?php if ( catalyzer_opt( 'success_intro' ) ) : ?>
				<p><?php echo esc_html( catalyzer_opt( 'success_intro' ) ); ?></p>
			<?php endif; ?>
		</div>

		<?php if ( $q->have_posts() ) : ?>
			<div class="sv-grid stagger">
				<?php
				while ( $q->have_posts() ) :
					$q->the_post();
					$id    = get_the_ID();
					$url   = get_post_meta( $id, '_cat_sv_url', true );
					$rank  = get_post_meta( $id, '_cat_sv_rank', true );
					$fld   = get_post_meta( $id, '_cat_sv_field', true );
					$year  = get_post_meta( $id, '_cat_sv_year', true );
					$dur   = get_post_meta( $id, '_cat_sv_duration', true );
					$quote = get_post_meta( $id, '_cat_sv_quote', true );
					$embed = $url ? catalyzer_video_embed( $url ) : '';
					?>
					<article class="sv-card" tabindex="0" role="button"
						aria-label="<?php echo esc_attr( sprintf( 'پخش ویدیوی %s', get_the_title() ) ); ?>"
						<?php if ( $embed ) : ?>data-embed="<?php echo esc_attr( rawurlencode( $embed ) ); ?>"<?php endif; ?>>
						<div class="sv-thumb">
							<?php
							if ( has_post_thumbnail() ) {
								the_post_thumbnail( 'catalyzer-card', array( 'loading' => 'lazy', 'alt' => get_the_title() ) );
							} else {
								echo catalyzer_icon( 'rings' ); // phpcs:ignore WordPress.Security.EscapeOutput
							}
							?>
							<span class="play"><?php echo catalyzer_icon( 'play' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
							<?php if ( $dur ) : ?>
								<span class="dur"><?php echo esc_html( $dur ); ?></span>
							<?php endif; ?>
							<?php if ( $rank ) : ?>
								<span class="sv-rank"><?php echo esc_html( 'رتبه ' . $rank ); ?></span>
							<?php endif; ?>
						</div>

						<div class="sv-meta">
							<h3><?php the_title(); ?></h3>
							<?php if ( $fld || $year ) : ?>
								<p class="sv-sub">
									<?php echo esc_html( trim( $fld . ( $fld && $year ? ' · کنکور ' : '' ) . $year ) ); ?>
								</p>
							<?php endif; ?>
							<?php if ( $quote ) : ?>
								<p class="sv-quote">&ldquo;<?php echo esc_html( $quote ); ?>&rdquo;</p>
							<?php endif; ?>
						</div>
					</article>
				<?php endwhile; ?>
			</div>

			<?php if ( $more_url ) : ?>
				<div class="videos-more">
					<a class="btn btn-ghost" href="<?php echo esc_url( $more_url ); ?>">
						<?php echo esc_html( catalyzer_opt( 'success_more_text', 'همه‌ی ویدیوها' ) ); ?>
					</a>
				</div>
			<?php endif; ?>
		<?php else : ?>
			<p class="form-note">هنوز ویدیویی ثبت نشده است. از پیشخوان → «رتبه‌برترها» اضافه کنید. (این پیام فقط برای مدیر دیده می‌شود.)</p>
		<?php endif; ?>
	</div>
</section>
<?php
wp_reset_postdata();
