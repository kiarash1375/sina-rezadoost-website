<?php
/**
 * دیدگاه‌ها.
 *
 * @package Catalyzer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( post_password_required() ) {
	return;
}
?>
<div id="comments" class="comments-area" style="margin-top:48px">
	<?php if ( have_comments() ) : ?>
		<h2 style="font-size:1.3rem;margin-bottom:18px">
			<?php
			printf(
				esc_html( _n( '%s دیدگاه', '%s دیدگاه', get_comments_number(), 'catalyzer' ) ),
				esc_html( number_format_i18n( get_comments_number() ) )
			);
			?>
		</h2>
		<ol class="comment-list">
			<?php
			wp_list_comments( array(
				'style'      => 'ol',
				'avatar_size' => 44,
				'short_ping' => true,
			) );
			?>
		</ol>
		<?php the_comments_navigation(); ?>
	<?php endif; ?>

	<?php
	if ( ! comments_open() && get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) :
		?>
		<p class="form-note"><?php esc_html_e( 'دیدگاه‌ها بسته است.', 'catalyzer' ); ?></p>
	<?php endif; ?>

	<?php
	comment_form( array(
		'class_submit' => 'btn btn-primary',
		'title_reply'  => __( 'دیدگاه شما', 'catalyzer' ),
	) );
	?>
</div>
