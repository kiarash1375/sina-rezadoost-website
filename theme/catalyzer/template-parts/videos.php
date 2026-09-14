<?php
/**
 * بخش «ویدیوهای کلاس» — از نوع محتوای lesson.
 *
 * ویدیوها از کانال آپارات درون‌ریزی می‌شوند و همین‌جا، داخل خود سایت، پخش
 * می‌شوند؛ کارت لایت‌باکس را باز می‌کند و کاربر از سایت بیرون نمی‌رود.
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

// فیلتر فقط وقتی معنی دارد که روی همین کارت‌ها بیش از یک دسته باشد.
$shown_terms = array();
foreach ( $q->posts as $p ) {
	foreach ( (array) get_the_terms( $p->ID, 'lesson_cat' ) as $t ) {
		if ( $t instanceof WP_Term ) {
			$shown_terms[ $t->slug ] = $t->name;
		}
	}
}
?>
<section class="section" id="videos">
	<div class="wrap">
		<div class="section-head reveal">
			<?php if ( catalyzer_opt( 'videos_eyebrow' ) ) : ?>
				<p class="eyebrow"><?php echo esc_html( catalyzer_opt( 'videos_eyebrow' ) ); ?></p>
			<?php endif; ?>
			<h2><?php echo esc_html( catalyzer_opt( 'videos_heading', 'ویدیوهای کلاس' ) ); ?></h2>
			<?php if ( catalyzer_opt( 'videos_intro' ) ) : ?>
				<p><?php echo esc_html( catalyzer_opt( 'videos_intro' ) ); ?></p>
			<?php endif; ?>
		</div>

		<?php if ( $q->have_posts() ) : ?>
			<?php if ( count( $shown_terms ) > 1 ) : ?>
				<div class="video-filter" role="group" aria-label="<?php esc_attr_e( 'دسته‌بندی ویدیوها', 'catalyzer' ); ?>">
					<button type="button" class="video-filter-btn is-on" data-filter="*" aria-pressed="true">
						<?php esc_html_e( 'همه', 'catalyzer' ); ?>
					</button>
					<?php foreach ( $shown_terms as $slug => $name ) : ?>
						<button type="button" class="video-filter-btn" data-filter="<?php echo esc_attr( $slug ); ?>" aria-pressed="false">
							<?php echo esc_html( $name ); ?>
						</button>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<div class="videos-grid stagger">
				<?php
				while ( $q->have_posts() ) :
					$q->the_post();
					get_template_part( 'template-parts/video-card' );
				endwhile;
				?>
			</div>

			<?php if ( $more_url ) : ?>
				<div class="videos-more">
					<a class="btn btn-ghost" href="<?php echo esc_url( $more_url ); ?>">
						<?php echo esc_html( catalyzer_opt( 'videos_more_text', 'همه‌ی ویدیوها' ) ); ?>
					</a>
				</div>
			<?php endif; ?>
		<?php else : ?>
			<p class="form-note">هنوز ویدیویی ثبت نشده است. از پیشخوان → «ویدیوهای کلاس» → «درون‌ریزی از آپارات» واردشان کنید. (این پیام فقط برای مدیر دیده می‌شود.)</p>
		<?php endif; ?>
	</div>
</section>
<?php
wp_reset_postdata();
