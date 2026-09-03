<?php
/**
 * بخش هیرو.
 *
 * @package Catalyzer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$portrait = catalyzer_hero_portrait_url();
?>
<section class="hero">
	<div class="hero-bg" aria-hidden="true"></div>
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

			<div class="portrait reveal">
				<svg viewBox="0 0 400 452" role="img" aria-label="<?php echo esc_attr( catalyzer_opt( 'about_heading', 'دکتر سینا رضادوست' ) ); ?>">
					<defs>
						<clipPath id="catHex">
							<path d="M200 6 L392 116 L392 336 L200 446 L8 336 L8 116 Z"/>
						</clipPath>
						<linearGradient id="catHexBg" x1="0" y1="0" x2="1" y2="1">
							<stop offset="0" stop-color="var(--bg-3)"/>
							<stop offset="1" stop-color="var(--bg-inset)"/>
						</linearGradient>
					</defs>
					<g class="spin-slow" opacity="0.5">
						<path d="M200 -6 L416 118 L416 366 L200 490 L-16 366 L-16 118 Z" fill="none" stroke="var(--line-strong)" stroke-width="1.4" stroke-dasharray="3 9"/>
					</g>
					<path d="M200 6 L392 116 L392 336 L200 446 L8 336 L8 116 Z" class="hexframe-fill"/>
					<g clip-path="url(#catHex)">
						<rect x="0" y="0" width="400" height="452" fill="url(#catHexBg)"/>
						<image href="<?php echo esc_url( $portrait ); ?>" xlink:href="<?php echo esc_url( $portrait ); ?>" x="-16" y="0" width="432" height="452" preserveAspectRatio="xMidYMid slice"/>
					</g>
					<path d="M200 6 L392 116 L392 336 L200 446 L8 336 L8 116 Z" class="hexframe-ring"/>
				</svg>

				<div class="tile" aria-hidden="true">
					<div class="num"><span><?php echo esc_html( catalyzer_opt( 'hero_tile_num', '۶' ) ); ?></span><span><?php echo esc_html( catalyzer_opt( 'hero_tile_sym', 'C' ) ); ?></span></div>
					<div class="sym"><?php echo esc_html( catalyzer_opt( 'hero_tile_sym', 'C' ) ); ?></div>
					<div class="nm"><?php echo esc_html( catalyzer_opt( 'hero_tile_name', 'کربن · کاتالیزور' ) ); ?></div>
				</div>
			</div>
		</div>
	</div>
</section>
