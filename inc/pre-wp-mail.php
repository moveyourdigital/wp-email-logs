<?php
/**
 * Add WP Mail
 *
 * @package email-logs
 */

namespace Email_Logs;

/**
 * Filters whether to preempt sending an email.
 *
 * Returning a non-null value will short-circuit {@see wp_mail()}, returning
 * that value instead. A boolean return value should be used to indicate whether
 * the email was successfully sent.
 *
 * @since 5.7.0
 *
 * @param null|bool $return Short-circuit return value.
 * @param array     $atts {
 *     Array of the `wp_mail()` arguments.
 *
 *     @type string|string[] $to          Array or comma-separated list of email addresses to send message.
 *     @type string          $subject     Email subject.
 *     @type string          $message     Message contents.
 *     @type string|string[] $headers     Additional headers.
 *     @type string|string[] $attachments Paths to files to attach.
 * }
 */
add_filter(
	'pre_wp_mail',
	function ( $_return, $args ) {
		$to          = $args['to'];
		$subject     = $args['subject'];
		$message     = $args['message'];
		$headers     = $args['headers'];
		$attachments = $args['attachments'];

		$was_short_circuited   = null !== $_return;
		$was_sent_successfully = true === $_return;

		$post_id = wp_insert_post(
			array(
				'post_type'    => 'email_log',
				'post_title'   => $subject,
				'post_content' => $message,
				'post_status'  => $was_short_circuited
					? $was_sent_successfully ? 'sent' : 'failed'
					: 'queued',
			),
		);

		if ( ! $post_id ) {
			return;
		}

		if ( ! is_array( $to ) ) {
			$to = array( $to );
		}

		foreach ( $to as $address ) {
			add_post_meta( $post_id, '_email_to', sanitize_email( $address ) );
		}

		add_post_meta( $post_id, '_email_headers', $headers );
		add_post_meta( $post_id, '_email_attachments', $attachments );

		if ( $was_short_circuited && ! $was_sent_successfully ) {
			/**
			 * Filters the email_log error message
			 *
			 * Returning something different to WP_Error will ignore to
			 * save the error message.
			 *
			 * @since 0.2.0
			 *
			 * @param WP_Error|null $error
			 * @param int           $post_id Email log post ID.
			 * @param array         $atts {
			 *     Array of the `wp_mail()` arguments.
			 *
			 *     @type string|string[] $to          Array or comma-separated list of email addresses to send message.
			 *     @type string          $subject     Email subject.
			 *     @type string          $message     Message contents.
			 *     @type string|string[] $headers     Additional headers.
			 *     @type string|string[] $attachments Paths to files to attach.
			 * }
			 */
			$error = apply_filters( 'email_logs_error_message', null, $post_id, $args );

			if ( is_wp_error( $error ) ) {
				add_post_meta( $post_id, '_email_error_message', $error->get_error_message() );
			}
		}

		/**
		 * Fires after PHPMailer is initialized.
		 *
		 * @since 0.1.0
		 *
		 * @param \PHPMailer\PHPMailer\PHPMailer $phpmailer The PHPMailer instance (passed by reference).
		 */
		add_action(
			'phpmailer_init',
			function ( \PHPMailer\PHPMailer\PHPMailer $phpmailer ) use ( $post_id, $args ) {
				/**
				 * Fires after PHPMailer has successfully sent an email.
				 *
				 * The firing of this action does not necessarily mean that the recipient(s) received the
				 * email successfully. It only means that the `send` method above was able to
				 * process the request without any errors.
				 *
				 * @since 5.9.0
				 *
				 * @param array $mail_data {
				 *     An array containing the email recipient(s), subject, message, headers, and attachments.
				 *
				 *     @type string[] $to          Email addresses to send message.
				 *     @type string   $subject     Email subject.
				 *     @type string   $message     Message contents.
				 *     @type string[] $headers     Additional headers.
				 *     @type string[] $attachments Paths to files to attach.
				 * }
				 */
				add_action(
					'wp_mail_succeeded',
					function () use ( $post_id, $phpmailer ) {
						wp_update_post(
							array(
								'ID'             => $post_id,
								'post_status'    => 'sent',
								// phpcs:ignore
								'post_mime_type' => $phpmailer->ContentType,
							)
						);

						// phpcs:ignore
						add_post_meta( $post_id, '_phpmailer_message_id', $phpmailer->MessageID );
					}
				);

				/** This filter is documented in wp-includes/pluggable.php */
				add_action(
					'wp_mail_failed',
					function ( $error ) use ( $post_id, $args ) {
						if ( is_wp_error( $error ) ) {
							$error_message = apply_filters( 'email_logs_error_message', $error, $post_id, $args );

							if ( is_wp_error( $error_message ) ) {
								add_post_meta( $post_id, '_email_log_error_message', $error->get_error_message() );
							}
						}

						wp_update_post(
							array(
								'ID'          => $post_id,
								'post_status' => 'failed',
							)
						);
					}
				);
			}
		);
	},
	// try to be the last one!
	PHP_INT_MAX,
	2,
);
