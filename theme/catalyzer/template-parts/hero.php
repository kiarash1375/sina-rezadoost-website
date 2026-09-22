<?php
/**
 * بخش هیرو.
 *
 * @package Catalyzer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$bg = catalyzer_hero_backgrounds();
?>
<section class="hero hero--photo" data-hero-fade>
	<div class="hero-media" aria-hidden="true">
		<picture>
			<?php foreach ( $bg['mobile'] as $type => $src ) : ?>
				<source media="(orientation: portrait)" srcset="<?php echo esc_url( $src ); ?>"<?php echo 'webp' === $type ? ' type="image/webp"' : ''; ?>>
			<?php endforeach; ?>
			<?php if ( isset( $bg['desktop']['webp'] ) ) : ?>
				<source srcset="<?php echo esc_url( $bg['desktop']['webp'] ); ?>" type="image/webp">
			<?php endif; ?>
			<img src="<?php echo esc_url( end( $bg['desktop'] ) ); ?>" alt="" width="1672" height="941" fetchpriority="high" decoding="async">
		</picture>
	</div>
	<div class="hero-scrim" aria-hidden="true"></div>
	<div class="wrap">
		<div class="hero-grid">
			<div class="hero-copy reveal">
				<?php if ( catalyzer_opt( 'hero_eyebrow' ) ) : ?>
					<p class="eyebrow"><?php echo esc_html( catalyzer_opt( 'hero_eyebrow' ) ); ?></p>
				<?php endif; ?>

				<h1><?php echo catalyzer_highlight( catalyzer_opt( 'hero_heading', 'شیمیِ کنکور را [مثل یک کاتالیزور] جلو ببر.' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h1>

				<?php if ( catalyzer_opt( 'hero_lede' ) ) : ?>
					<p class="lede"><?php echo esc_html( catalyzer_opt( 'hero_lede' ) ); ?></p>
				<?php endif; ?>

				<div class="hero-actions">
					<?php if ( catalyzer_opt( 'hero_btn1_text' ) ) : ?>
						<a class="btn btn-primary" href="<?php echo esc_url( catalyzer_anchor_url( catalyzer_opt( 'hero_btn1_url', '#courses' ) ) ); ?>">
							<?php echo esc_html( catalyzer_opt( 'hero_btn1_text' ) ); ?>
						</a>
					<?php endif; ?>
					<?php if ( catalyzer_opt( 'hero_btn2_text' ) ) : ?>
						<a class="btn btn-ghost" href="<?php echo esc_url( catalyzer_anchor_url( catalyzer_opt( 'hero_btn2_url', '#videos' ) ) ); ?>">
							<?php echo catalyzer_icon( 'play-o' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
							<?php echo esc_html( catalyzer_opt( 'hero_btn2_text' ) ); ?>
						</a>
					<?php endif; ?>
				</div>

				<?php if ( catalyzer_opt( 'hero_trust' ) ) : ?>
					<p class="hero-trust"><span class="dot"></span> <?php echo esc_html( catalyzer_opt( 'hero_trust' ) ); ?></p>
				<?php endif; ?>
			</div>

		</div>
	</div>
</section>
