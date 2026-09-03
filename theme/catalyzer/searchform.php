<?php
/**
 * فرم جستجو.
 *
 * @package Catalyzer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label>
		<span class="screen-reader-text"><?php esc_html_e( 'جستجو برای:', 'catalyzer' ); ?></span>
		<input type="search" class="search-field" placeholder="<?php esc_attr_e( 'جستجو…', 'catalyzer' ); ?>" value="<?php echo get_search_query(); ?>" name="s" />
	</label>
	<button type="submit" class="btn btn-ghost"><?php esc_html_e( 'جستجو', 'catalyzer' ); ?></button>
</form>
