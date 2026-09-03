<?php
/**
 * پردازش فرم «درخواست مشاوره».
 *
 * فرم به admin-post.php ارسال می‌شود، اینجا اعتبارسنجی، ذخیره به‌عنوان
 * «درخواست مشاوره» و ارسال ایمیل انجام می‌شود، سپس کاربر به بخش تماس
 * برمی‌گردد با پارامتر catalyzer_sent=1 (موفق) یا 0 (خطا).
 *
 * @package Catalyzer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function catalyzer_handle_contact() {
	$referer = wp_get_referer();
	if ( ! $referer ) {
		$referer = home_url( '/' );
	}
	$back_ok  = add_query_arg( 'catalyzer_sent', '1', $referer ) . '#contact';
	$back_err = add_query_arg( 'catalyzer_sent', '0', $referer ) . '#contact';

	// نانس + هانی‌پات.
	if ( ! isset( $_POST['catalyzer_contact_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['catalyzer_contact_nonce'] ), 'catalyzer_contact' ) ) {
		wp_safe_redirect( $back_err );
		exit;
	}
	if ( ! empty( $_POST['catalyzer_website'] ) ) {
		// ربات — وانمود به موفقیت می‌کنیم.
		wp_safe_redirect( $back_ok );
		exit;
	}

	$name  = isset( $_POST['catalyzer_name'] ) ? sanitize_text_field( wp_unslash( $_POST['catalyzer_name'] ) ) : '';
	$phone = isset( $_POST['catalyzer_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['catalyzer_phone'] ) ) : '';
	$field = isset( $_POST['catalyzer_field'] ) ? sanitize_text_field( wp_unslash( $_POST['catalyzer_field'] ) ) : '';
	$msg   = isset( $_POST['catalyzer_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['catalyzer_message'] ) ) : '';

	if ( '' === $name || '' === $phone ) {
		wp_safe_redirect( $back_err );
		exit;
	}

	// ذخیره به‌عنوان «درخواست مشاوره».
	$lead_id = wp_insert_post( array(
		'post_type'    => 'catalyzer_lead',
		'post_status'  => 'private',
		'post_title'   => sprintf( '%s — %s', $name, $phone ),
		'post_content' => $msg,
	) );
	if ( $lead_id && ! is_wp_error( $lead_id ) ) {
		update_post_meta( $lead_id, '_cat_lead_phone', $phone );
		update_post_meta( $lead_id, '_cat_lead_field', $field );
	}

	// ارسال ایمیل.
	$to = catalyzer_opt( 'contact_email' );
	if ( ! is_email( $to ) ) {
		$to = get_option( 'admin_email' );
	}
	$subject = sprintf( '[%s] درخواست مشاوره‌ی جدید از %s', wp_specialchars_decode( get_bloginfo( 'name' ) ), $name );
	$body    = "نام: {$name}\nتلفن: {$phone}\nرشته: {$field}\n\nپیام:\n{$msg}\n";
	wp_mail( $to, $subject, $body );

	wp_safe_redirect( $back_ok );
	exit;
}
add_action( 'admin_post_nopriv_catalyzer_contact', 'catalyzer_handle_contact' );
add_action( 'admin_post_catalyzer_contact', 'catalyzer_handle_contact' );
