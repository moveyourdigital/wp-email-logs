<?php

/**
 *
 * @since 0.1.0
 */
function email_notifications_variables_replace( $message, $user, $blogname, $key = '' ) {

	if ( is_array( $user ) ) {
		$user = get_user_by( 'ID', $user['ID'] );
	}

	$replacers = apply_filters( 'email_notifications_user_emails_variables_callbacks', [
		'site_url' => site_url('/'),
		'site_title' => $blogname,
		'user_login' => $user->user_login,
		'user_display_name' => $user->display_name,
		'login_url' => wp_login_url(),
		'user_email' => $user->user_email,
		'user_activation_link' => is_wp_error( $key ) ? '' : network_site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user->user_login ), 'login' ),
	] );

	$message = stripslashes( $message );

	foreach ( $replacers as $variable => $replacer ) {
		$message = str_replace( "%$variable%",
		is_callable( $replacer ) ? $replacer() : $replacer
		, $message );
	}

	return $message;

}

/**
 * Filters the contents of the new user notification email sent to the new user.
 *
 * @since 0.1.0
 *
 * @param array   $wp_new_user_notification_email {
 *     Used to build wp_mail().
 *
 *     @type string $to      The intended recipient - New user email address.
 *     @type string $subject The subject of the email.
 *     @type string $message The body of the email.
 *     @type string $headers The headers of the email.
 * }
 * @param WP_User $user     User object for new user.
 * @param string  $blogname The site title.
 * @uses wp_new_user_notification_email
 */
add_filter( 'wp_new_user_notification_email', function ( $wp_new_user_notification_email, $user, $blogname ) {

	$options = get_option( 'email_notifications' );

	if ( isset( $options['when_a_user_is_added'] ) ) {

		$message = email_notifications_variables_replace(
			$options['when_a_user_is_added'],
			$user,
			$blogname,
			get_password_reset_key( $user )
		);

		$wp_new_user_notification_email['message'] = $message;
	}

	if ( isset( $options['when_a_user_is_added_subject'] ) ) {
		$subject = $options['when_a_user_is_added_subject'];
		$wp_new_user_notification_email['subject'] = sprintf( $subject, $blogname );
	}

	return $wp_new_user_notification_email;

}, 10, 3 );

/**
 * Filters the subject of the password reset email.
 *
 * @since 0.1.0
 * @since 4.4.0 Added the `$user_login` and `$user_data` parameters.
 *
 * @param string  $title      Default email title.
 * @param string  $user_login The username for the user.
 * @param WP_User $user_data  WP_User object.
 */
add_filter( 'retrieve_password_title', function ( $title, $user_login, $user_data ) {

	$options = get_option( 'email_notifications' );

	if ( isset( $options['when_password_reset_subject'] ) ) {
		if ( is_multisite() ) {
			$site_name = get_network()->site_name;
		} else {
			/*
			 * The blogname option is escaped with esc_html on the way into the database
			 * in sanitize_option we want to reverse this for the plain text arena of emails.
			 */
			$site_name = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
		}

		sprintf( $options['when_password_reset_subject'], $site_name );
	}

	return $title;

}, 10, 3 );

/**
 * Filters the message body of the password reset mail.
 *
 * If the filtered message is empty, the password reset email will not be sent.
 *
 * @since 0.1.0
 * @since 4.1.0 Added `$user_login` and `$user_data` parameters.
 *
 * @param string  $message    Default mail message.
 * @param string  $key        The activation key.
 * @param string  $user_login The username for the user.
 * @param WP_User $user_data  WP_User object.
 */
add_filter( 'retrieve_password_message', function ( $message, $key, $user_login, $user_data ) {

	$options = get_option( 'email_notifications' );

	if ( isset( $options['when_password_reset'] ) ) {
		if ( is_multisite() ) {
			$site_name = get_network()->site_name;
		} else {
			/*
			 * The blogname option is escaped with esc_html on the way into the database
			 * in sanitize_option we want to reverse this for the plain text arena of emails.
			 */
			$site_name = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
		}

		$message = email_notifications_variables_replace(
			$options['when_password_reset'],
			$user_data,
			$site_name,
			get_password_reset_key( $key )
		);
	}

	return $message;

}, 10, 4 );

/**
 * Filters the contents of the email sent when the user's password is changed.
 *
 * @since 4.3.0
 *
 * @param array $pass_change_email {
 *            Used to build wp_mail().
 *
 *            @type string $to      The intended recipients. Add emails in a comma separated string.
 *            @type string $subject The subject of the email.
 *            @type string $message The content of the email.
 *                The following strings have a special meaning and will get replaced dynamically:
 *                - ###USERNAME###    The current user's username.
 *                - ###ADMIN_EMAIL### The admin email in case this was unexpected.
 *                - ###EMAIL###       The user's email address.
 *                - ###SITENAME###    The name of the site.
 *                - ###SITEURL###     The URL to the site.
 *            @type string $headers Headers. Add headers in a newline (\r\n) separated string.
 *        }
 * @param array $user     The original user array.
 * @param array $userdata The updated user array.
 */
add_filter( 'password_change_email', function ( $pass_change_email, $user, $userdata ) {

	$options = get_option( 'email_notifications' );

	if ( isset( $options['when_password_changes'] ) ) {
		if ( is_multisite() ) {
			$site_name = get_network()->site_name;
		} else {
			/*
			 * The blogname option is escaped with esc_html on the way into the database
			 * in sanitize_option we want to reverse this for the plain text arena of emails.
			 */
			$site_name = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
		}

		$pass_change_email['subject'] = $options['when_password_changes_subject'];

		$pass_change_email['message'] = email_notifications_variables_replace(
			$options['when_password_changes'],
			$userdata,
			$site_name,
		);
	}

	return $pass_change_email;

}, 10, 3 );

/**
 * Fires in head section for all admin pages.
 *
 * @since 0.1.0
 */
add_action( 'admin_head', function () {
?>
<style>
.email-status-failed {
	color: #a00;
}
</style>
<?php
} );

