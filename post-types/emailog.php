<?php

/**
 * Registers the `emailog` post type.
 */
add_action( 'init', function () {

	register_post_type( 'emailog', array(
		'labels'                => array(
			'name'                  => _x( 'Email Logs', 'name', 'email-notifications' ),
			'singular_name'         => _x( 'Email Log', 'singular_name', 'email-notifications' ),
			'all_items'             => __( 'Email Logs', 'email-notifications' ),
			'attributes'            => __( 'Email Log Attributes', 'email-notifications' ),
			'filter_items_list'     => __( 'Filter Email Logs list', 'email-notifications' ),
			'items_list_navigation' => __( 'Email Logs list navigation', 'email-notifications' ),
			'items_list'            => __( 'Email Logs list', 'email-notifications' ),
			'new_item'              => __( 'New Email Log', 'email-notifications' ),
			'edit_item'             => __( 'Edit Email Log', 'email-notifications' ),
			'view_item'             => __( 'View Email Log', 'email-notifications' ),
			'view_items'            => __( 'View Email Logs', 'email-notifications' ),
			'search_items'          => __( 'Search Email Logs', 'email-notifications' ),
			'not_found'             => __( 'No Email Logs found', 'email-notifications' ),
			'not_found_in_trash'    => __( 'No Email Logs found in trash', 'email-notifications' ),
			'menu_name'             => _x( 'Email Logs', 'menu_item', 'email-notifications' ),
		),
		'public'                => false,
		'hierarchical'          => false,
		'show_ui'               => true,
		'show_in_nav_menus'     => false,
		'supports'              => array( 'revisions' ),
		'has_archive'           => false,
		'rewrite'               => false,
		'query_var'             => false,
		'show_in_menu' 			=> 'tools.php',
		'show_in_rest'          => true,
		'can_export'			=> true,
		'capabilities'       	=> array(
			'edit_post'           => 'read_emailog',
			'edit_posts'          => 'read_emailog',
			'edit_others_posts'   => 'do_not_use',
			'publish_posts'       => 'do_not_use',
			'read_post'           => 'read_emailog',
			'read_private_posts'  => 'read_emailog',
			'delete_post'         => 'delete_emailog',
			'create_posts'		  => 'do_not_use'
		),
		'map_meta_cap' 			=> false,
		'rest_base'             => 'emailog',
		'rest_controller_class' => 'WP_REST_Posts_Controller',
	) );

	register_post_status( 'queued', array(
		'label'                     => _x( 'Queued', 'post', 'email-notifications' ),
		'public'                    => false,
		'internal'					=> true,
		'private'					=> true,
        'exclude_from_search'       => true,
        'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'date_floating'				=> true,
        'label_count'               => _n_noop(
			'Queued <span class=count>(%s)</span>',
			'Queued <span class=count>(%s)</span>', 'email-notifications'
		),
    ) );

	register_post_status( 'failed', array(
		'label'                     => '<span class="email-status-failed">' . _x( 'Failed', 'post', 'email-notifications' ) . '</span>',
		'public'                    => false,
		'internal'					=> true,
		'private'					=> true,
        'exclude_from_search'       => true,
        'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'date_floating'				=> true,
        'label_count'               => _n_noop(
			'Failed <span class=count>(%s)</span>',
			'Failed <span class=count>(%s)</span>', 'email-notifications'
		),
    ) );

	register_post_status( 'sent', array(
		'label'                     => _x( 'Sent', 'post', 'email-notifications' ),
		'public'                    => false,
		'internal'					=> true,
		'private'					=> true,
        'exclude_from_search'       => true,
        'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'date_floating'				=> true,
        'label_count'               => _n_noop(
			'Sent <span class=count>(%s)</span>',
			'Sent <span class=count>(%s)</span>', 'email-notifications'
		),
    ) );

	register_post_status( 'delivered', array(
		'label'                     => _x( 'Delivered', 'post', 'email-notifications' ),
		'public'                    => false,
		'internal'					=> true,
		'private'					=> true,
        'exclude_from_search'       => true,
        'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'date_floating'				=> true,
        'label_count'               => _n_noop(
			'Delivered <span class=count>(%s)</span>',
			'Delivered <span class=count>(%s)</span>', 'email-notifications'
		),
    ) );

	register_post_status( 'bounced', array(
		'label'                     => _x( 'Bounced', 'post', 'email-notifications' ),
		'public'                    => false,
		'internal'					=> true,
		'private'					=> true,
        'exclude_from_search'       => true,
        'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'date_floating'				=> true,
        'label_count'               => _n_noop(
			'Bounced <span class=count>(%s)</span>',
			'Bounced <span class=count>(%s)</span>', 'email-notifications'
		),
	) );

} );

/**
 * Sets the post updated messages for the `emailog` post type.
 *
 * @param  array $messages Post updated messages.
 * @return array Messages for the `emailog` post type.
 */
add_filter( 'post_updated_messages', function ( $messages ) {
	global $post;

	$permalink = get_permalink( $post );

	$messages['emailog'] = array(
		0  => '', // Unused. Messages start at index 1.
		/* translators: %s: post permalink */
		1  => sprintf( __( 'Email Log updated. <a target="_blank" href="%s">View Email Log</a>', 'email-notifications' ), esc_url( $permalink ) ),
		2  => __( 'Custom field updated.', 'email-notifications' ),
		3  => __( 'Custom field deleted.', 'email-notifications' ),
		4  => __( 'Email Log updated.', 'email-notifications' ),
		/* translators: %s: date and time of the revision */
		5  => isset( $_GET['revision'] ) ? sprintf( __( 'Email Log restored to revision from %s', 'email-notifications' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		/* translators: %s: post permalink */
		6  => sprintf( __( 'Email Log published. <a href="%s">View Email Log</a>', 'email-notifications' ), esc_url( $permalink ) ),
		7  => __( 'Email Log saved.', 'email-notifications' ),
		/* translators: %s: post permalink */
		8  => sprintf( __( 'Email Log submitted. <a target="_blank" href="%s">Preview Email Log</a>', 'email-notifications' ), esc_url( add_query_arg( 'preview', 'true', $permalink ) ) ),
		/* translators: 1: Publish box date format, see https://secure.php.net/date 2: Post permalink */
		9  => sprintf( __( 'Email Log scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview Email Log</a>', 'email-notifications' ),
		date_i18n( __( 'M j, Y @ G:i', 'email-notifications' ), strtotime( $post->post_date ) ), esc_url( $permalink ) ),
		/* translators: %s: post permalink */
		10 => sprintf( __( 'Email Log draft updated. <a target="_blank" href="%s">Preview Email Log</a>', 'email-notifications' ), esc_url( add_query_arg( 'preview', 'true', $permalink ) ) ),
	);

	return $messages;
} );

/**
 * Filter for post_row_actions/page_row_actions which is only added for emailog type.
 *
 * @param  array $actions Post row actions.
 * @return WP_Post $post
 */
add_filter('post_row_actions', function ( $actions, $post ) {

	if ( 'emailog' !== get_post_type( $post) ) return $actions;

	$new_actions = [];

	$new_actions['view'] = '<a href="' . get_edit_post_link( $post ) . '" title="'
		. esc_attr( __( 'View details', 'email-notifications' ) )
		. '">' . __( 'View', 'email-notifications' ) . '</a>';

	$new_actions['trash'] = $actions['trash'];

	return $new_actions;

}, 100, 2);

/**
 * Fires after the current screen has been set.
 *
 * @since 0.1.0
 *
 * @param WP_Screen $current_screen Current WP_Screen object.
 */
add_action( 'current_screen', function ( $current_screen ) {

    if ( 'edit-emailog' === $current_screen->id ) {
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
		add_filter( 'bulk_actions-edit-emailog', function ( $bulk_actions ) {

			$new_bulk_actions = [];
			$new_bulk_actions['resend'] = __( 'Resend emails', 'email-notifications' );
			$new_bulk_actions['trash']	= $bulk_actions['trash'];

			return $new_bulk_actions;
		} );

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
		add_filter( 'handle_bulk_actions-edit-emailog', function ( $redirect_to, $doaction, $post_ids ) {

			if ( $doaction !== 'resend' ) return $redirect_to;

			foreach ( $post_ids as $post_id ) {
				$post = get_post( $post_id );

				$headers = get_post_meta( $post_id, '_mail_headers', true );

				if ( ! is_array( $headers ) && empty( $headers ) ) {
					$headers = [];
				}

				$headers['Parent-Post-Id'] = $post_id;
				$headers['Content-Type'] = get_post_mime_type( $post_id );

				wp_mail(
					get_post_meta( $post_id, '_email_to', true ),
					$post->post_title,
					$post->post_content,
					$headers,
					get_post_meta( $post_id, '_mail_attachments', true ),
				);
			}

			$redirect_to = add_query_arg( 'bulk_emails_resent', count( $post_ids ), $redirect_to );

			return $redirect_to;

		}, 10, 3 );

		/**
		 * Prints admin screen notices.
		 *
		 * @since 0.1.0
		 *
		 * @uses admin_notices
		 */
		add_action( 'admin_notices', function () {

			if ( ! empty( $_REQUEST['bulk_emails_resent'] ) ) {
			  $emailed_count = intval( $_REQUEST['bulk_emails_resent'] );

			  printf( '<div id="message" class="updated fade is-dismissible"><p>' .
				_n( 'Resent %s email.',
				  'Resent %s emails.',
				  $emailed_count,
				  'email-notifications'
				) . '</p></div>', $emailed_count );
			}

		} );
	}

} );

/**
 * Disabling the Gutenberg editor on emailog post type
 *
 * @param bool   $can_edit  Whether to use the Gutenberg editor.
 * @param string $post_type Name of WordPress post type.
 * @return bool  $can_edit
 */
add_filter( 'use_block_editor_for_post_type', function ( $can_edit, $post_type ) {

	if ( 'emailog' === $post_type ) return false;
	return $can_edit;

}, 1, 2 );

/**
 * Fires at the beginning of the edit form.
 *
 * At this point, the required hidden fields and nonces have already been output.
 *
 * @since 3.7.0
 *
 * @param WP_Post $post Post object.
 */
add_action( 'edit_form_after_title', function ( $post ) {
	if ( 'emailog' !== get_post_type( $post ) ) return;

	$post_status = get_post_status( $post );
	$post_status_object = get_post_status_object( $post_status );

	$fields = [
		__( 'Status', 'email-notifications' ) 	=> $post_status_object->label,
		__( 'To', 'email-notifications' ) 		=> get_post_meta( $post->ID, '_email_to' , true ),
		__( 'Subject', 'email-notifications' ) 	=> $post->post_title,
		__( 'Body', 'email-notifications' ) 	=> $post->post_content,
		__( 'Message ID', 'email-notifications' ) => get_post_meta( $post->ID, '_email_message_id' , true ),
	];

	$attachs = get_post_meta( $post->ID, '_mail_attachments' , true );

	if ( ! empty( $attachs ) ) {
		$list = [];
		foreach ( $attachs as $header => $value ) {
			$list[] = '<b>' . $header . '</b>: <var>' . $value . '</var>';
		}
		$fields[ __( 'Attachments', 'email-notifications' ) ] = '<ul><li>' . join( '</li><li>', $list ) . '</li></ul>';
	}

	$headers = get_post_meta( $post->ID, '_mail_headers' , true );

	if ( ! empty( $headers ) ) {
		$list = [];
		foreach ( $headers as $header => $value ) {
			$list[] = '<b>' . $header . '</b>: <var>' . $value . '</var>';
		}
		$fields[ __( 'Headers', 'email-notifications' ) ] = '<ul><li>' . join( '</li><li>', $list ) . '</li></ul>';
	}

	if ( 'failed' === $post_status ) {
		$fields[ __( 'Error', 'email-notifications' ) ] = get_post_meta( $post->ID, '_email_error_message' , true );
	}

?>
<table class="form-table" role="presentation">
<tbody>

<?php foreach ( $fields as $label => $field ) : ?>
<tr class="emailog-">
	<th scope="row">
		<?php echo $label ?>
	</th>
	<td>
		<?php echo $field ?>
	</td>
</tr>
<?php endforeach ?>

</tbody>
</table>
<?php

} );

/**
 * Fires after all built-in meta boxes have been added, contextually for the given post type.
 *
 * The dynamic portion of the hook, `$post_type`, refers to the post type of the post.
 *
 * @since 3.0.0
 *
 * @param WP_Post $post Post object.
 */
add_action( 'add_meta_boxes_emailog', function ( $post ) {

	remove_meta_box( 'submitdiv', get_current_screen(), 'side' );

} );

/**
 * Filters the columns displayed in the Posts list table for a specific post type.
 *
 * The dynamic portion of the hook name, `$post_type`, refers to the post type slug.
 *
 * @since 0.1.0
 *
 * @param string[] $post_columns An associative array of column headings.
 */
add_filter( 'manage_emailog_posts_columns', function ( $columns ) {

	$new_columns = [];

	$new_columns['cb'] 		= $columns['cb'];
	$new_columns['to'] 		= __( 'To', 'email-notifications' );
	$new_columns['subject'] = __( 'Subject', 'email-notifications' );
	$new_columns['status'] 	= __( 'Status', 'email-notifications' );
	$new_columns['date'] 	= $columns['date'];

	return $new_columns;

} );

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
add_filter( 'manage_edit-emailog_sortable_columns', function ( $columns ) {

	$columns['to'] = 'email_to';
	$columns['status'] = 'email_status';

	return $columns;

} );

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
add_action( 'pre_get_posts', function ( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() || 'emailog' !== $query->get( 'post_type' ) ) {
	  return;
	}

	if ( 'email_to' === $query->get( 'orderby') ) {
	  $query->set( 'orderby', 'meta_value' );
	  $query->set( 'meta_key', '_email_to' );
	  $query->set( 'meta_type', 'string' );
	}

	if ( 'email_status' === $query->get( 'orderby') ) {
		$query->set( 'orderby', 'post_status' );
	}

} );

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
add_action( 'manage_emailog_posts_custom_column' , function ( $column, $post_id ) {

    switch ( $column ) {

        case 'to' :
            echo get_post_meta( $post_id, '_email_to', true );
            break;

        case 'subject' :
            echo get_the_title( $post_id );
            break;

		case 'status' :
			$post_status = get_post_status( $post_id );
			$post_status_object = get_post_status_object( $post_status );

			echo '<a href="' . add_query_arg( [
				'post_status' => $post_status,
			] ) . '">' . $post_status_object->label . '</a>';
			break;

	}

}, 10, 2 );
