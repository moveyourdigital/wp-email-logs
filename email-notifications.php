<?php
/**
 * Plugin Name:     Email Notifications
 * Plugin URI:      https://github.com/moveyourdigital/email-notifications
 * Description:     Replace default WordPress email notifications easily
 * Author:          Move Your Digital, Inc.
 * Author URI:      https://moveyourdigital.com
 * Text Domain:     email-notifications
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Email_Notifications
 */

/*
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

define( 'WP_EMAIL_NOTIFICATIONS_PLUGIN_PATH', __DIR__ );

/**
 *
 * @since 0.1.0
 */
add_action( 'plugins_loaded', function () {
	global $email_notifications_mail_handler;

	load_plugin_textdomain(
		'email-notifications',
		false,
		dirname( plugin_basename( __FILE__ ) ) . '/languages/'
	);

	include WP_EMAIL_NOTIFICATIONS_PLUGIN_PATH . '/includes/mail-handler.php';
	include WP_EMAIL_NOTIFICATIONS_PLUGIN_PATH . '/post-types/emailog.php';
	include WP_EMAIL_NOTIFICATIONS_PLUGIN_PATH . '/includes/options.php';
	include WP_EMAIL_NOTIFICATIONS_PLUGIN_PATH . '/includes/hooks.php';

	if ( is_admin() ) {
		new \EmailNotifications\AdminOptions();
	}

	$email_notifications_mail_handler = new \EmailNotifications\MailHandler();
} );

/**
 *
 * @since 0.1.0
 */
register_activation_hook( __FILE__, function () {
	include WP_EMAIL_NOTIFICATIONS_PLUGIN_PATH . '/includes/activation.php';
} );

/**
 *
 * @since 0.1.0
 */
register_deactivation_hook( __FILE__, function () {
	include WP_EMAIL_NOTIFICATIONS_PLUGIN_PATH . '/includes/deactivation.php';
} );
