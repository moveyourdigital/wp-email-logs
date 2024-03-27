<?php
/**
 * Registers the `email_log` post type.
 *
 * @package email-logs
 */

add_action(
	'init',
	function () {
		register_post_type(
			'email_log',
			array(
				'labels'                => array(
					'name'                  => _x( 'Email Logs', 'name', 'email-logs' ),
					'singular_name'         => _x( 'Email Log', 'singular_name', 'email-logs' ),
					'all_items'             => __( 'Email Logs', 'email-logs' ),
					'attributes'            => __( 'Email Log Attributes', 'email-logs' ),
					'filter_items_list'     => __( 'Filter Email Logs list', 'email-logs' ),
					'items_list_navigation' => __( 'Email Logs list navigation', 'email-logs' ),
					'items_list'            => __( 'Email Logs list', 'email-logs' ),
					'new_item'              => __( 'New Email Log', 'email-logs' ),
					'edit_item'             => __( 'Email Log', 'email-logs' ),
					'view_item'             => __( 'View Email Log', 'email-logs' ),
					'view_items'            => __( 'View Email Logs', 'email-logs' ),
					'search_items'          => __( 'Search Email Logs', 'email-logs' ),
					'not_found'             => __( 'No Email Logs found', 'email-logs' ),
					'not_found_in_trash'    => __( 'No Email Logs found in trash', 'email-logs' ),
					'menu_name'             => _x( 'Email Logs', 'menu_item', 'email-logs' ),
				),
				'public'                => false,
				'hierarchical'          => false,
				'show_ui'               => true,
				'show_in_nav_menus'     => false,
				'supports'              => array( 'revisions' ),
				'has_archive'           => false,
				'rewrite'               => false,
				'query_var'             => false,
				'show_in_menu'          => 'tools.php',
				'show_in_rest'          => true,
				'can_export'            => true,
				'capabilities'          => array(
					'edit_post'          => 'read_email_log',
					'edit_posts'         => 'read_email_log',
					'edit_others_posts'  => 'do_not_use',
					'publish_posts'      => 'do_not_use',
					'read_post'          => 'read_email_log',
					'read_private_posts' => 'read_email_log',
					'delete_post'        => 'delete_email_log',
					'create_posts'       => 'do_not_use',
				),
				'map_meta_cap'          => false,
				'rest_base'             => 'email_log',
				'rest_controller_class' => 'WP_REST_Posts_Controller',
			)
		);

		foreach ( array(
			'queued'    => array(
				_x( 'Queued', 'post', 'email-logs' ),
				/* translators: number of entries in queued state */
				_n_noop(
					'Queued <span class=count>(%s)</span>',
					'Queued <span class=count>(%s)</span>',
					'email-logs'
				),
			),

			'failed'    => array(
				'<span class="email_logs-status-failed">' . _x( 'Failed', 'post', 'email-logs' ) . '</span>',
				/* translators: number of entries in failed state */
				_n_noop(
					'Failed <span class=count>(%s)</span>',
					'Failed <span class=count>(%s)</span>',
					'email-logs'
				),
			),

			'sent'      => array(
				_x( 'Sent', 'post', 'email-logs' ),
				/* translators: number of entries in sent state */
				_n_noop(
					'Sent <span class=count>(%s)</span>',
					'Sent <span class=count>(%s)</span>',
					'email-logs'
				),
			),

			'delivered' => array(
				_x( 'Delivered', 'post', 'email-logs' ),
				/* translators: number of entries in delivered state */
				_n_noop(
					'Delivered <span class=count>(%s)</span>',
					'Delivered <span class=count>(%s)</span>',
					'email-logs'
				),
			),

			'bounced'   => array(
				_x( 'Bounced', 'post', 'email-logs' ),
				/* translators: number of entries in bounced state */
				_n_noop(
					'Bounced <span class=count>(%s)</span>',
					'Bounced <span class=count>(%s)</span>',
					'email-logs'
				),
			),
		) as $status => $labels ) {
			register_post_status(
				$status,
				array(
					'label'                     => $labels[0],
					'public'                    => false,
					'internal'                  => true,
					'private'                   => true,
					'exclude_from_search'       => true,
					'show_in_admin_all_list'    => true,
					'show_in_admin_status_list' => true,
					'date_floating'             => true,
					'label_count'               => $labels[1],
				)
			);
		}
	}
);

/**
 * Filters the array of row action links on the Posts list table.
 *
 * The filter is evaluated only for non-hierarchical post types.
 *
 * @since 2.8.0
 *
 * @param string[] $actions An array of row action links. Defaults are
 *                          'Edit', 'Quick Edit', 'Restore', 'Trash',
 *                          'Delete Permanently', 'Preview', and 'View'.
 * @param WP_Post  $post    The post object.
 */
add_filter(
	'post_row_actions',
	function ( $actions, $post ) {

		if ( 'email_log' !== get_post_type( $post ) ) {
			return $actions;
		}

		$new_actions = array();

		// phpcs:ignore
		if ( current_user_can( 'read_email_log', $post->ID ) ) {
			$new_actions['details'] = '<a href="' . get_edit_post_link( $post ) . '" title="'
				. esc_attr( __( 'View details', 'email-logs' ) )
				. '">' . __( 'View', 'email-logs' ) . '</a>';
		}

		// phpcs:ignore
		if ( current_user_can( 'delete_email_log', $post->ID ) ) {
			$new_actions['trash'] = $actions['trash'];
		}

		return $new_actions;
	},
	100,
	2
);

/**
 * Fires after the current screen has been set.
 *
 * @since 0.1.0
 *
 * @param WP_Screen $current_screen Current WP_Screen object.
 */
add_action(
	'current_screen',
	function ( $current_screen ) {

		if ( 'edit-email_log' === $current_screen->id ) {
			/**
			 * Fires when a custom bulk action should be handled.
			 *
			 * The redirect link should be modified with success or failure feedback
			 * from the action to be used to display feedback to the user.
			 *
			 * The dynamic portion of the hook name, `$screen`, refers to the current screen ID.
			 *
			 * @since 0.1.0
			 *
			 * @param string $sendback The redirect URL.
			 * @param string $doaction The action being taken.
			 * @param array  $items    The items to take the action on. Accepts an array of IDs of posts,
			 *                         comments, terms, links, plugins, attachments, or users.
			 */
			add_filter(
				'bulk_actions-edit-email_log',
				function ( $bulk_actions ) {

					$new_bulk_actions           = array();
					$new_bulk_actions['resend'] = __( 'Resend emails', 'email-logs' );
					$new_bulk_actions['trash']  = $bulk_actions['trash'];

					return $new_bulk_actions;
				}
			);

			/**
			 * Fires when a custom bulk action should be handled.
			 *
			 * The redirect link should be modified with success or failure feedback
			 * from the action to be used to display feedback to the user.
			 *
			 * The dynamic portion of the hook name, `$screen`, refers to the current screen ID.
			 *
			 * @since 0.1.0
			 *
			 * @param string $sendback The redirect URL.
			 * @param string $doaction The action being taken.
			 * @param array  $items    The items to take the action on. Accepts an array of IDs of posts,
			 *                         comments, terms, links, plugins, attachments, or users.
			 */
			add_filter(
				'handle_bulk_actions-edit-email_log',
				function ( $redirect_to, $doaction, $post_ids ) {

					if ( 'resend' !== $doaction ) {
						return $redirect_to;
					}

					foreach ( $post_ids as $post_id ) {
						$post = get_post( $post_id );

						$headers = get_post_meta( $post_id, '_email_headers', true );

						if ( ! is_array( $headers ) && empty( $headers ) ) {
							$headers = array();
						}

						$headers['Parent-Post-Id'] = $post_id;
						$headers['Content-Type']   = get_post_mime_type( $post_id );

						wp_mail(
							get_post_meta( $post_id, '_email_to', true ),
							$post->post_title,
							$post->post_content,
							$headers,
							get_post_meta( $post_id, '_email_attachments', true ),
						);
					}

					$redirect_to = add_query_arg( 'bulk_emails_resent', count( $post_ids ), $redirect_to );

					return $redirect_to;
				},
				10,
				3
			);

			/**
			 * Prints admin screen notices.
			 *
			 * @since 0.1.0
			 *
			 * @uses admin_notices
			 */
			add_action(
				'admin_notices',
				function () {
					// phpcs:ignore
					if ( empty( $_REQUEST['bulk_emails_resent'] ) ) {
						return;
					}

					// phpcs:ignore
					$emailed_count = intval( $_REQUEST['bulk_emails_resent'] );

					printf(
						'<div id="message" class="updated fade is-dismissible"><p>' .
						esc_html(
							/* translators: number of entries in bounced state */
							_n(
								'Resent %s email.',
								'Resent %s emails.',
								$emailed_count,
								'email-logs'
							)
						) . '</p></div>',
						esc_attr( number_format_i18n( $emailed_count ) )
					);
				}
			);
		}
	}
);

/**
 * Disabling the Gutenberg editor on email_log post type
 *
 * @param bool   $can_edit  Whether to use the Gutenberg editor.
 * @param string $post_type Name of WordPress post type.
 * @return bool  $can_edit
 */
add_filter(
	'use_block_editor_for_post_type',
	function ( $can_edit, $post_type ) {
		if ( 'email_log' === $post_type ) {
			return false;
		}
		return $can_edit;
	},
	1,
	2
);

/**
 * Fires at the beginning of the edit form.
 *
 * At this point, the required hidden fields and nonces have already been output.
 *
 * @since 3.7.0
 *
 * @param WP_Post $post Post object.
 */
add_action(
	'edit_form_after_title',
	function ( $post ) {
		if ( 'email_log' !== get_post_type( $post ) ) {
			return;
		}

		$post_status        = get_post_status( $post );
		$post_status_object = get_post_status_object( $post_status );

		$fields = array(
			__( 'Status', 'email-logs' )     => $post_status_object->label,
			__( 'To Address', 'email-logs' ) => esc_html( get_post_meta( $post->ID, '_email_to', true ) ),
			__( 'Subject', 'email-logs' )    => esc_html( $post->post_title ),
			__( 'Body', 'email-logs' )       => nl2br( $post->post_content ),
			__( 'Message ID', 'email-logs' ) => esc_html( get_post_meta( $post->ID, '_phpmailer_message_id', true ) ),
		);

		$attachs = get_post_meta( $post->ID, '_email_attachments', true );

		if ( ! empty( $attachs ) ) {
			$list = array();
			foreach ( $attachs as $header => $value ) {
				$list[] = '<b>' . $header . '</b>: <var>' . $value . '</var>';
			}
			$fields[ __( 'Attachments', 'email-logs' ) ] = '<ul><li>' . join( '</li><li>', $list ) . '</li></ul>';
		}

		$headers = get_post_meta( $post->ID, '_email_headers', true );

		if ( ! empty( $headers ) ) {
			$list = array();

			if ( ! is_array( $headers ) ) {
				$headers = array( $headers );
			}

			foreach ( $headers as $header ) {
				foreach ( preg_split( '/\r\n|\r|\n/', $header ) as $header ) {
					list( $header, $value ) = explode( ':', $header );

					if ( $header && $value ) {
						$list[] = '<b>' . esc_html( trim( $header ) ) . '</b>: <var>' . htmlentities2( trim( $value ) ) . '</var>';
					}
				}
			}

			$fields[ __( 'Headers', 'email-logs' ) ] = '<ul><li>' . join( '</li><li>', $list ) . '</li></ul>';
		}

		if ( 'failed' === $post_status ) {
			$fields[ __( 'Error', 'email-logs' ) ] = htmlentities2( get_post_meta( $post->ID, '_email_error_message', true ) );
		}

		?>
<table class="form-table email_log-form-table" role="presentation">
<tbody>

		<?php
		foreach ( $fields as $label => $field ) :
			if ( $field ) :
				?>
<tr class="email_log-form-row">
	<th scope="row">
				<?php echo esc_html( $label ); ?>
	</th>
	<td>
			<?php /* phpcs:ignore */ echo $field; ?>
	</td>
</tr>
	<?php endif; endforeach ?>

</tbody>
</table>
		<?php
	}
);

/**
 * Fires after all built-in meta boxes have been added, contextually for the given post type.
 *
 * The dynamic portion of the hook, `$post_type`, refers to the post type of the post.
 *
 * @since 3.0.0
 *
 * @param WP_Post $post Post object.
 */
add_action(
	'add_meta_boxes_email_log',
	function () {
		remove_meta_box( 'submitdiv', get_current_screen(), 'side' );
	}
);

/**
 * Filters the columns displayed in the Posts list table for a specific post type.
 *
 * The dynamic portion of the hook name, `$post_type`, refers to the post type slug.
 *
 * @since 0.1.0
 *
 * @param string[] $post_columns An associative array of column headings.
 */
add_filter(
	'manage_email_log_posts_columns',
	function ( $columns ) {
		$new_columns = array();

		$new_columns['cb']      = $columns['cb'];
		$new_columns['to']      = __( 'To', 'email-logs' );
		$new_columns['subject'] = __( 'Subject', 'email-logs' );
		$new_columns['status']  = __( 'Status', 'email-logs' );
		$new_columns['date']    = $columns['date'];

		return $new_columns;
	}
);

/**
 * Filters the list table sortable columns for a specific screen.
 *
 * The dynamic portion of the hook name, `$this->screen->id`, refers
 * to the ID of the current screen, usually a string.
 *
 * @since 0.1.0
 *
 * @param array $sortable_columns An array of sortable columns.
 */
add_filter(
	'manage_edit-email_log_sortable_columns',
	function ( $columns ) {
		$columns['to']     = 'email_to';
		$columns['status'] = 'email_status';

		return $columns;
	}
);

/**
 * Fires after the query variable object is created, but before the actual query is run.
 *
 * Note: If using conditional tags, use the method versions within the passed instance
 * (e.g. $this->is_main_query() instead of is_main_query()). This is because the functions
 * like is_main_query() test against the global $wp_query instance, not the passed one.
 *
 * @since 0.1.0
 *
 * @param WP_Query $this The WP_Query instance (passed by reference).
 */
add_action(
	'pre_get_posts',
	function ( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() || 'email_log' !== $query->get( 'post_type' ) ) {
			return;
		}

		if ( 'email_to' === $query->get( 'orderby' ) ) {
			$query->set( 'orderby', 'meta_value' );
			$query->set( 'meta_key', '_email_to' );
			$query->set( 'meta_type', 'string' );
		}

		if ( 'email_status' === $query->get( 'orderby' ) ) {
			$query->set( 'orderby', 'post_status' );
		}
	}
);

/**
 * Fires for each custom column of a specific post type in the Posts list table.
 *
 * The dynamic portion of the hook name, `$post->post_type`, refers to the post type.
 *
 * @since 0.1.0
 *
 * @param string $column_name The name of the column to display.
 * @param int    $post_id     The current post ID.
 */
add_action(
	'manage_email_log_posts_custom_column',
	function ( $column, $post_id ) {

		switch ( $column ) {
			case 'to':
				echo esc_html( get_post_meta( $post_id, '_email_to', true ) );
				break;

			case 'subject':
				echo esc_html( get_the_title( $post_id ) );
				break;

			case 'status':
				$post_status        = get_post_status( $post_id );
				$post_status_object = get_post_status_object( $post_status );

				echo '<a href="' . esc_attr(
					add_query_arg(
						array(
							'post_status' => $post_status,
						)
					)
					// phpcs:ignore
				) . '">' . $post_status_object->label . '</a>';
				break;

		}
	},
	10,
	2
);

/**
 * Fires in head section for all admin pages.
 *
 * @since 0.1.0
 */
add_action(
	'admin_print_styles',
	function () {
		global $typenow;

		if ( 'email_log' !== $typenow ) {
			return;
		}
		?>
<style>
.email_logs-status-failed { color: #a00; }
.post-type-email_log .page-title-action { display: none !important; }
</style>
		<?php
	}
);
