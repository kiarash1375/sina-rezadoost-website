<?php
/**
 * سربرگ سایت.
 *
 * @package Catalyzer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!doctype html>
<html dir="rtl" lang="<?php echo esc_attr( str_replace( '_', '-', get_locale() ) ); ?>">
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="profile" href="https://gmpg.org/xfn/11">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="screen-reader-text skip-link" href="#content"><?php esc_html_e( 'پرش به محتوای اصلی', 'catalyzer' ); ?></a>

<canvas class="bg-net" id="bgNet" aria-hidden="true"></canvas>

<header class="site-header" id="top">
	<div class="wrap">
		<nav class="nav" aria-label="<?php esc_attr_e( 'ناوبری اصلی', 'catalyzer' ); ?>">
			<?php if ( has_custom_logo() ) : ?>
				<div class="brand"><?php the_custom_logo(); ?></div>
			<?php else : ?>
				<a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( catalyzer_opt( 'brand_name', 'کاتالیزور' ) ); ?>">
					<?php echo catalyzer_brand_mark(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<span>
						<span class="brand-name"><?php echo esc_html( catalyzer_opt( 'brand_name', 'کاتالیزور' ) ); ?></span>
						<span class="brand-sub"><?php echo esc_html( catalyzer_opt( 'brand_sub', 'SINA REZADOOST' ) ); ?></span>
					</span>
				</a>
			<?php endif; ?>

			<?php catalyzer_nav_menu( 'nav-links', false ); ?>

			<div class="nav-right">
				<?php if ( catalyzer_opt( 'show_account_btn', true ) && function_exists( 'catalyzer_account_url' ) ) : ?>
					<a class="btn btn-ghost nav-account" href="<?php echo esc_url( catalyzer_account_url() ); ?>">
						<?php
						echo esc_html(
							is_user_logged_in()
								? catalyzer_opt( 'account_btn_text', 'پنل کاربری' )
								: catalyzer_opt( 'login_btn_text', 'ورود / ثبت‌نام' )
						);
						?>
					</a>
				<?php endif; ?>
				<a class="btn btn-primary nav-cta" href="<?php echo esc_url( catalyzer_anchor_url( catalyzer_opt( 'nav_cta_url', '#contact' ) ) ); ?>">
					<?php echo esc_html( catalyzer_opt( 'nav_cta_text', 'مشاوره و ثبت‌نام' ) ); ?>
				</a>
				<button class="icon-btn nav-toggle" id="navToggle" type="button" aria-label="<?php esc_attr_e( 'منو', 'catalyzer' ); ?>" aria-expanded="false" aria-controls="navPanel">
					<?php echo catalyzer_icon( 'menu' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</button>
			</div>
		</nav>
	</div>
	<?php catalyzer_nav_menu( 'nav-panel', true ); ?>
</header>

<main class="site-main" id="content">
