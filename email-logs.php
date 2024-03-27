<?php
/**
 * Plugin Name:     Email Logs
 * Plugin URI:      https://github.com/moveyourdigital/wp-email-logs
 * Description:     Log all emails sent from WordPress
 * Version:         0.3.1
 * Requires:        PHP: 7.4
 * Author:          Move Your Digital, Inc.
 * Author URI:      https://moveyourdigital.com
 * License:         GPLv2
 * License URI:     https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:      https://github.com/moveyourdigital/wp-email-logs/raw/main/wp-info.json
 * Text Domain:     email-logs
 * Domain Path:     /languages
 *
 * @package         email-logs
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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load plugin translations and post type
 *
 * @since 0.1.0
 */
add_action(
	'plugins_loaded',
	function () {
		load_plugin_textdomain(
			'email-logs',
			false,
			dirname( plugin_basename( __FILE__ ) ) . '/languages/'
		);

		include __DIR__ . '/inc/post-types/email-log.php';
		include __DIR__ . '/inc/filters/pre-wp-mail.php';
		include __DIR__ . '/updater.php';
	}
);

/**
 * Filters this plugin data
 *
 * @since 0.3.2
 */
add_filter(
	'plugin_basename_file_' . plugin_basename( __DIR__ ),
	function () {
		return plugin_basename( __FILE__ );
	}
);

/**
 * Add capabilities to administrator
 *
 * @since 0.1.0
 */
register_activation_hook(
	__FILE__,
	function () {
		$role = get_role( 'administrator' );
		$role->add_cap( 'read_email_log' );
		$role->add_cap( 'delete_email_log' );
	}
);

/**
 * Remove capabilities from administrator
 *
 * @since 0.1.0
 */
register_deactivation_hook(
	__FILE__,
	function () {
		$role = get_role( 'administrator' );
		$role->remove_cap( 'read_email_log' );
		$role->remove_cap( 'delete_email_log' );
	}
);
