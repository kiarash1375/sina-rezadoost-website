<?php
/**
 * صفحه‌ی فرود (یک‌صفحه‌ای).
 *
 * @package Catalyzer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

get_template_part( 'template-parts/hero' );

if ( catalyzer_section_enabled( 'stats' ) ) {
	get_template_part( 'template-parts/stats' );
}
if ( catalyzer_section_enabled( 'about' ) ) {
	get_template_part( 'template-parts/about' );
}
if ( catalyzer_section_enabled( 'method' ) ) {
	get_template_part( 'template-parts/method' );
}
if ( catalyzer_section_enabled( 'courses' ) ) {
	get_template_part( 'template-parts/courses' );
}
if ( catalyzer_section_enabled( 'videos' ) ) {
	get_template_part( 'template-parts/videos' );
}
if ( catalyzer_section_enabled( 'success' ) ) {
	get_template_part( 'template-parts/success-videos' );
}
if ( catalyzer_section_enabled( 'testimonials' ) ) {
	get_template_part( 'template-parts/testimonials' );
}
if ( catalyzer_section_enabled( 'cta' ) ) {
	get_template_part( 'template-parts/cta' );
}

// اگر صفحه‌ی اصلی روی «آخرین نوشته‌ها» تنظیم شده باشد، محتوای صفحه‌ی برگزیده (در صورت وجود) نمایش داده نمی‌شود؛
// این قالب عمداً فقط بخش‌های بالا را نشان می‌دهد.

get_footer();
