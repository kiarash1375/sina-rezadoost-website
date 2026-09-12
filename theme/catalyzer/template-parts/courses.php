<?php
/**
 * بخش «دوره‌ها و پکیج‌ها» — از نوع محتوای course.
 *
 * @package Catalyzer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$q = new WP_Query( array(
	'post_type'      => 'course',
	'posts_per_page' => 3,
	'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
	'no_found_rows'  => true,
) );

if ( ! $q->have_posts() && ! current_user_can( 'edit_posts' ) ) {
	return;
}
?>
<section class="section" id="courses">
	<div class="wrap">
		<div class="section-head reveal">
			<?php if ( catalyzer_opt( 'courses_eyebrow' ) ) : ?>
				<p class="eyebrow"><?php echo esc_html( catalyzer_opt( 'courses_eyebrow' ) ); ?></p>
			<?php endif; ?>
			<h2><?php echo esc_html( catalyzer_opt( 'courses_heading' ) ); ?></h2>
			<?php if ( catalyzer_opt( 'courses_intro' ) ) : ?>
				<p><?php echo esc_html( catalyzer_opt( 'courses_intro' ) ); ?></p>
			<?php endif; ?>
		</div>

		<?php if ( $q->have_posts() ) : ?>
			<div class="courses-grid stagger">
				<?php
				while ( $q->have_posts() ) :
					$q->the_post();
					$featured = get_post_meta( get_the_ID(), '_cat_featured', true );
					$badge    = get_post_meta( get_the_ID(), '_cat_badge', true );
					$subtitle = get_post_meta( get_the_ID(), '_cat_subtitle', true );
					$price    = get_post_meta( get_the_ID(), '_cat_price', true );
					$old      = get_post_meta( get_the_ID(), '_cat_old_price', true );
					$currency = get_post_meta( get_the_ID(), '_cat_currency', true );
					$currency = $currency ? $currency : 'تومان';
					$features = catalyzer_lines( get_post_meta( get_the_ID(), '_cat_features', true ) );
					$btn_txt  = get_post_meta( get_the_ID(), '_cat_btn_text', true );
					$btn_txt  = $btn_txt ? $btn_txt : 'ثبت‌نام';
					$btn_url  = get_post_meta( get_the_ID(), '_cat_btn_url', true );
					$btn_url  = $btn_url ? $btn_url : catalyzer_account_url();
					?>
					<div class="course<?php echo $featured ? ' featured' : ''; ?>">
						<?php if ( $badge ) : ?>
							<span class="badge"><?php echo esc_html( $badge ); ?></span>
						<?php endif; ?>

						<h3><?php the_title(); ?></h3>

						<?php if ( $subtitle ) : ?>
							<p class="desc"><?php echo esc_html( $subtitle ); ?></p>
						<?php endif; ?>

						<?php if ( $price ) : ?>
							<div class="price">
								<span class="amt"><?php echo esc_html( $price ); ?></span>
								<span class="cur"><?php echo esc_html( $currency ); ?></span>
								<?php if ( $old ) : ?>
									<span class="old"><?php echo esc_html( $old ); ?></span>
								<?php endif; ?>
							</div>
						<?php endif; ?>

						<?php if ( $features ) : ?>
							<ul class="feat">
								<?php foreach ( $features as $f ) : ?>
									<li><?php echo catalyzer_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <?php echo esc_html( $f ); ?></li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>

						<a class="btn <?php echo $featured ? 'btn-primary' : 'btn-ghost'; ?> btn-block" href="<?php echo esc_url( $btn_url ); ?>">
							<?php echo esc_html( $btn_txt ); ?>
						</a>
					</div>
				<?php endwhile; ?>
			</div>
		<?php else : ?>
			<p class="form-note">هنوز دوره‌ای ثبت نشده است. از پیشخوان → «دوره‌ها» → «افزودن دوره» موارد را اضافه کنید. (این پیام فقط برای مدیر دیده می‌شود.)</p>
		<?php endif; ?>

		<?php if ( catalyzer_opt( 'courses_note' ) ) : ?>
			<p class="form-note" style="margin-top:22px;text-align:center"><?php echo esc_html( catalyzer_opt( 'courses_note' ) ); ?></p>
		<?php endif; ?>
	</div>
</section>
<?php
wp_reset_postdata();
