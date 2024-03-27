<?php
/**
 * Hook plugin updater from git repository
 *
 * @package email-logs
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gets and updates plugin Update URI
 *
 * @since 0.3.5
 */
add_filter(
	'plugin_update_uri_' . plugin_basename( __DIR__ ),
	function ( $delete = false ) {
		if ( true === $delete ) {
			delete_option( 'plugin_update_uri_' . plugin_basename( __DIR__ ) );
		}

		$update_uri = get_option( 'plugin_update_uri_' . plugin_basename( __DIR__ ) );

		if ( ! $update_uri ) {
			if ( ! function_exists( 'get_plugin_data' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			$basename_file = apply_filters( 'plugin_basename_file_' . plugin_basename( __DIR__ ), '' );

			$plugin_data = get_plugin_data( trailingslashit( WP_PLUGIN_DIR ) . $basename_file );
			$update_uri  = $plugin_data['UpdateURI'];

			update_option( 'plugin_update_uri_' . plugin_basename( __DIR__ ), $update_uri );
		}

		return $update_uri;
	}
);

/**
 * Fetch and return plugin data from declared remote
 *
 * @since 0.3.2
 *
 * @param false|object|array $default_result default data to return if fetch fails
 */
add_filter(
	'plugin_update_remote_data_' . plugin_basename( __DIR__ ),
	function ( $default_result = '' ) {
		$remote_data = wp_remote_get(
			apply_filters( 'plugin_update_uri_' . plugin_basename( __DIR__ ), null ),
			array(
				'timeout' => 10,
				'headers' => array(
					'Accept' => 'application/json',
				),
			)
		);

		if ( is_wp_error( $remote_data )
			|| 200 !== wp_remote_retrieve_response_code( $remote_data )
			|| empty( wp_remote_retrieve_body( $remote_data ) ) ) {
			return $default_result;
		}

		return json_decode( wp_remote_retrieve_body( $remote_data ) );
	}
);

/**
 * Filters the response for the current WordPress.org Plugin Installation API request.
 *
 * Returning a non-false value will effectively short-circuit the WordPress.org API request.
 *
 * If `$action` is 'query_plugins' or 'plugin_information', an object MUST be passed.
 * If `$action` is 'hot_tags' or 'hot_categories', an array should be passed.
 *
 * @since 2.7.0
 *
 * @param false|object|array $result The result object or array. Default false.
 * @param string             $action The type of information being requested from the Plugin Installation API.
 * @param object             $args   Plugin API arguments.
 */
add_filter(
	'plugins_api',
	function ( $result, $action, $args ) {
		if ( 'plugin_information' !== $action ) {
			return $result;
		}

		if ( plugin_basename( __DIR__ ) !== $args->slug ) {
			return $result;
		}

		$result           = apply_filters( 'plugin_update_remote_data_' . plugin_basename( __DIR__ ), $result );
		$result->slug     = plugin_basename( __DIR__ );
		$result->trunk    = $result->download_url;
		$result->sections = array(
			'description'  => $result->sections->description,
			'installation' => $result->sections->installation,
		);
		$result->banners  = array(
			'low'  => $result->banners->low,
			'high' => $result->banners->high,
		);

		return $result;
	},
	20,
	3
);

/**
 * Fires once activated plugins have loaded.
 *
 * Pluggable functions are also available at this point in the loading order.
 *
 * @since 1.5.0
 */
add_action(
	'init',
	function () {
		$update_uri = apply_filters( 'plugin_update_uri_' . plugin_basename( __DIR__ ), null );
		$hostname   = wp_parse_url( sanitize_url( $update_uri ), PHP_URL_HOST );

		add_filter(
			'update_plugins_' . $hostname,
			function ( $update, $plugin_data, $plugin_file ) {
				$basename_file = apply_filters( 'plugin_basename_file_' . plugin_basename( __DIR__ ), '' );

				if ( $plugin_file !== $basename_file ) {
					return $update;
				}

				if ( ! empty( $update ) ) {
					return $update;
				}

				$remote_data = apply_filters( 'plugin_update_remote_data_' . plugin_basename( __DIR__ ), $update );

				if ( ! $remote_data ) {
					return $update;
				}

				if ( version_compare( $remote_data->version, $plugin_data['Version'], '<=' ) ) {
					return $update;
				}

				if ( version_compare( get_bloginfo( 'version' ), $remote_data->requires, '<' ) ) {
					return $update;
				}

				if ( version_compare( PHP_VERSION, $remote_data->requires_php, '<' ) ) {
					return $update;
				}

				return array(
					'slug'    => plugin_basename( __DIR__ ),
					'version' => $remote_data->version,
					'url'     => $plugin_data['PluginURI'],
					'package' => $remote_data->download_url,
				);
			},
			10,
			4
		);
	}
);

/**
 * Fires when the upgrader process is complete.
 *
 * See also {@see 'upgrader_package_options'}.
 *
 * @since 3.6.0
 * @since 3.7.0 Added to WP_Upgrader::run().
 * @since 4.6.0 `$translations` was added as a possible argument to `$hook_extra`.
 *
 * @param WP_Upgrader $upgrader   WP_Upgrader instance. In other contexts this might be a
 *                                Theme_Upgrader, Plugin_Upgrader, Core_Upgrade, or Language_Pack_Upgrader instance.
 * @param array       $hook_extra {
 *     Array of bulk item update data.
 *
 *     @type string $action       Type of action. Default 'update'.
 *     @type string $type         Type of update process. Accepts 'plugin', 'theme', 'translation', or 'core'.
 *     @type bool   $bulk         Whether the update process is a bulk update. Default true.
 *     @type array  $plugins      Array of the basename paths of the plugins' main files.
 *     @type array  $themes       The theme slugs.
 *     @type array  $translations {
 *         Array of translations update data.
 *
 *         @type string $language The locale the translation is for.
 *         @type string $type     Type of translation. Accepts 'plugin', 'theme', or 'core'.
 *         @type string $slug     Text domain the translation is for. The slug of a theme/plugin or
 *                                'default' for core translations.
 *         @type string $version  The version of a theme, plugin, or core.
 *     }
 * }
 */
add_action(
	'upgrader_process_complete',
	function ( $upgrader_object, $options ) {
		$basename_file = apply_filters( 'plugin_basename_file_' . plugin_basename( __DIR__ ), '' );

		if ( 'update' === $options['action'] && 'plugin' === $options['type'] ) {
			foreach ( $options['plugins'] as $each_plugin ) {
				if ( $each_plugin === $basename_file ) {
					apply_filters( 'plugin_update_uri_' . plugin_basename( __DIR__ ), true );
				}
			}
		}
	},
	10,
	2
);
