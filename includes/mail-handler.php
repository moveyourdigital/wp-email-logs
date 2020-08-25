<?php

namespace EmailNotifications;

/**
 *
 * @since 0.1.0
 */
class MailHandler {

	/**
	 *
	 * @param int Mailog post ID
	 */
	protected $ID;

	/**
	 *
	 */
	protected $wp_mail_content_type;

	/**
	 *
	 */
	public function __construct() {

		/**
		 * Filters the wp_mail() arguments.
		 *
		 * @since 0.1.0
		 *
		 * @param array $args A compacted array of wp_mail() arguments, including the "to" email,
		 *                    subject, message, headers, and attachments values.
		 */
		add_filter( 'wp_mail', function ( $args ) {
			$this->ID = $this->createMessage(
				$args['to'], $args['subject'], $args['message'], $args['headers'], $args['attachments']
			);

			return $args;
		} );

		/**
		 * Fires after PHPMailer is initialized.
		 *
		 * @since 0.1.0
		 *
		 * @param \PHPMailer\PHPMailer\PHPMailer $phpmailer The PHPMailer instance (passed by reference).
		 */
		add_action( 'phpmailer_init', function ( \PHPMailer\PHPMailer\PHPMailer $phpmailer ) {
			$phpmailer->MessageID = $this->generateMessageId();

			if ( $phpmailer->ContentType === 'text/plain'
				&& $phpmailer->Body !== strip_tags( $phpmailer->Body ) ) {
				$mime_type = 'text/html';

				$phpmailer->ContentType = $mime_type;
				$phpmailer->isHTML( true );
			} else {
				$mime_type = null;
			}

			add_post_meta( $this->ID, '_email_message_id', $phpmailer->MessageID );

			wp_update_post( [
				'ID' => $this->ID,
				'post_status' => 'sent',
				'post_mime_type' => $mime_type
			] );
		} );

		/** This filter is documented in wp-includes/pluggable.php */
		add_action( 'wp_mail_failed', function ( $error ) {
			if ( is_wp_error( $error ) ) {
				add_post_meta( $this->ID, '_email_error_message', $error->get_error_message() );
			}

			wp_update_post( [
				'ID' => $this->ID,
				'post_status' => 'failed',
			] );
		} );
	}

	/**
	 *
	 * @since 0.1.0
	 */
	public function createMessage( $to, $subject, $message, $headers, $attachments ) {
		$parent_id = isset( $headers['Parent-Post-Id'] )
			? $headers['Parent-Post-Id']
			: null;

		$mime_type = isset( $headers['Content-Type'] )
			? $headers['Content-Type']
			: 'text/plain';

		$post_id = wp_insert_post( [
			'post_type'	 		=> 'emailog',
			'post_title'		=> $subject,
			'post_content'		=> $message,
			'post_status'		=> 'queued',
			'post_parent'		=> $parent_id,
			'post_mime_type'	=> $mime_type,
		] );

		add_post_meta( $post_id, '_email_to', sanitize_email( $to ) );
		add_post_meta( $post_id, '_mail_headers', $headers );
		add_post_meta( $post_id, '_mail_attachments', $attachments );

		return $post_id;
	}

	/**
	 *
	 * @since 0.1.0
	 */
	public function getEmailDomain() {

		if ( defined( 'SMTP_EMAIL_FROM' ) ) {
			$from = explode( '@', SMTP_EMAIL_FROM );
			if ( isset( $from[1] ) ) return $from[1];
		}

		$sitename = wp_parse_url( network_home_url(), PHP_URL_HOST );
		if ( 'www.' === substr( $sitename, 0, 4 ) ) {
			$sitename = substr( $sitename, 4 );
		}

		return $sitename;
	}

	/**
	 *
	 * @since 0.1.0
	 */
	public function generateMessageId() {
		/**
		 * Filters the message ID generation arguments.
		 *
		 * @since 2.2.0
		 *
		 * @param array  $components The components to generate message ID
		 * @param int	 $id The post ID for this message
		 * @param string $domain The domain used for this message ID
		 *
		 * @uses email_notifications_message_id_components
		 */
		$components = apply_filters( 'email_notifications_message_id_components', [
			md5( 'WP-' . $this->ID . ( idate("U") - 1000000000 ) . uniqid() ),
			$this->getEmailDomain(),
		], $this->ID, $this->getEmailDomain() );

		return "<" . join( '@', $components ) . '>';
	}

}
