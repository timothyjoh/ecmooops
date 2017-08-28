<?php
/**
 * WP User Manager Emails
 *
 * @package     wp-user-manager
 * @author 		Pippin Williamson
 * @author      Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * WPUM_Emails Class
 *
 * @since 1.0.0
 */
class WPUM_Emails {

	/**
	 * Holds the from address
	 *
	 * @since 1.0.0
	 */
	private $from_address;

	/**
	 * Holds the from name
	 *
	 * @since 1.0.0
	 */
	private $from_name;

	/**
	 * Holds the email content type
	 *
	 * @since 1.0.0
	 */
	private $content_type;

	/**
	 * Holds the email headers
	 *
	 * @since 1.0.0
	 */
	private $headers;

	/**
	 * Whether to send email in HTML
	 *
	 * @since 1.0.0
	 */
	private $html = true;

	/**
	 * The email template to use
	 *
	 * @since 1.0.0
	 */
	private $template;

	/**
	 * The header text for the email
	 *
	 * @since 1.0.0
	 */
	private $heading = '';

	/**
	 * The name of the email.
	 * Used during save/retrieval
	 *
	 * @since 1.0.0
	 */
	var $name = '';

	/**
	 * The title of the email.
	 * Used within the editor.
	 *
	 * @since 1.0.0
	 */
	var $title = '';

	/**
	 * The description of the email.
	 * Used within the editor.
	 *
	 * @since 1.0.0
	 */
	var $description = '';

	/**
	 * The subject of the email.
	 * Used only as default option of the email editor.
	 *
	 * @since 1.0.0
	 */
	var $subject = '';

	/**
	 * The message of the email.
	 * Used only as default option of the email editor.
	 *
	 * @since 1.0.0
	 */
	var $message = '';

	/**
	 * Get things going
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		if ( 'none' === $this->get_template() ) {
			$this->html = false;
		}

		add_action( 'wpum_email_send_before', array( $this, 'send_before' ) );
		add_action( 'wpum_email_send_after', array( $this, 'send_after' ) );

		// Register Emails
		add_filter( 'wpum/get_emails', array( $this, 'get_emails' ), 10, 1 );

	}

	/**
	 * Set a property
	 *
	 * @since 1.0.0
	 */
	public function __set( $key, $value ) {
		$this->$key = $value;
	}

	/**
	 * Get the email from name
	 *
	 * @since 1.0.0
	 */
	public function get_from_name() {
		if ( ! $this->from_name ) {
			$this->from_name = wpum_get_option( 'from_name', get_bloginfo( 'name' ) );
		}

		return apply_filters( 'wpum_email_from_name', wp_specialchars_decode( $this->from_name ), $this );
	}

	/**
	 * Get the email from address
	 *
	 * @since 1.0.0
	 */
	public function get_from_address() {
		if ( ! $this->from_address ) {
			$this->from_address = wpum_get_option( 'from_email', get_option( 'admin_email' ) );
		}

		return apply_filters( 'wpum_email_from_address', $this->from_address, $this );
	}

	/**
	 * Get the email content type
	 *
	 * @since 1.0.0
	 */
	public function get_content_type() {

		$this->content_type = 'text/html';

		return apply_filters( 'wpum_email_content_type', $this->content_type, $this );

	}

	/**
	 * Get the email headers
	 *
	 * @since 1.0.0
	 */
	public function get_headers() {
		if ( ! $this->headers ) {
			$this->headers  = "From: {$this->get_from_name()} <{$this->get_from_address()}>\r\n";
			$this->headers .= "Reply-To: {$this->get_from_address()}\r\n";
			$this->headers .= "Content-Type: {$this->get_content_type()}; charset=utf-8\r\n";
		}

		return apply_filters( 'wpum_email_headers', $this->headers, $this );
	}

	/**
	 * Retrieve email templates
	 *
	 * @since 1.0.0
	 */
	public function get_templates() {
		$templates = array(
			'default' => __( 'Default template', 'wpum' ),
			'none'    => __( 'No template, plain text only', 'wpum' )
		);

		return apply_filters( 'wpum_email_templates', $templates );
	}

	/**
	 * Get the enabled email template
	 *
	 * @since 1.0.0
	 */
	public function get_template() {
		if ( ! $this->template ) {
			$this->template = wpum_get_option( 'email_template', 'none' );
		}

		return apply_filters( 'wpum_email_template', $this->template );
	}

	/**
	 * Get the header text for the email
	 *
	 * @since 1.0.0
	 */
	public function get_heading() {
		return apply_filters( 'wpum_email_heading', $this->heading );
	}

	/**
	 * Parse email template tags
	 *
	 * @since 1.0.0
	 */
	public function parse_tags( $content ) {
		return $content;
	}

	/**
	 * Build the final email
	 *
	 * @since 1.0.0
	 */
	public function build_email( $message ) {

		if ( false === $this->html ) {
			return apply_filters( 'wpum_email_message', wp_strip_all_tags( $message ), $this );
		}

		$message = $this->text_to_html( $message );

		ob_start();

		get_wpum_template_part( 'emails/header', $this->get_template() );

		do_action( 'wpum_email_header', $this );

		if ( has_action( 'wpum_email_template_' . $this->get_template() ) ) {
			do_action( 'wpum_email_template_' . $this->get_template() );
		} else {
			get_wpum_template_part( 'emails/body', $this->get_template() );
		}

		do_action( 'wpum_email_body', $this );

		get_wpum_template_part( 'emails/footer', $this->get_template() );

		do_action( 'wpum_email_footer', $this );

		$body    = ob_get_clean();
		$message = str_replace( '{email}', $message, $body );

		return apply_filters( 'wpum_email_message', $message, $this );

	}

	/**
	 * Send the email
	 * @param  string  $to               The To address to send to.
	 * @param  string  $subject          The subject line of the email to send.
	 * @param  string  $message          The body of the email to send.
	 * @param  string|array $attachments Attachments to the email in a format supported by wp_mail()
	 * @since 1.0.0
	 */
	public function send( $to, $subject, $message, $attachments = '' ) {

		if ( ! did_action( 'init' ) && ! did_action( 'admin_init' ) ) {
			_doing_it_wrong( __FUNCTION__, __( 'You cannot send email with WPUM_Emails until init/admin_init has been reached', 'wpum' ), null );
			return false;
		}

		do_action( 'wpum_email_send_before', $this );

		$subject = $this->parse_tags( $subject );
		$message = $this->parse_tags( $message );

		if( $this->get_template() == 'none' ) {
			add_filter( 'wp_mail_content_type', array( $this, 'get_content_type' ) );
		} else {
			$message = $this->build_email( $message );
		}

		$attachments = apply_filters( 'wpum_email_attachments', $attachments, $this );

		$sent = wp_mail( $to, $subject, $message, $this->get_headers(), $attachments );

		if( $this->get_template() == 'none' ) {
			remove_filter( 'wp_mail_content_type', array( $this, 'get_content_type' ) );
		}

		do_action( 'wpum_email_send_after', $this );

		return $sent;

	}

	/**
	 * Add filters / actions before the email is sent
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function send_before() {
		add_filter( 'wp_mail_from', array( $this, 'get_from_address' ) );
		add_filter( 'wp_mail_from_name', array( $this, 'get_from_name' ) );
		add_filter( 'wp_mail_content_type', array( $this, 'get_content_type' ) );
	}

	/**
	 * Remove filters / actions after the email is sent
	 *
	 * @since 1.0.0
	 * @return  void
	 */
	public function send_after() {
		remove_filter( 'wp_mail_from', array( $this, 'get_from_address' ) );
		remove_filter( 'wp_mail_from_name', array( $this, 'get_from_name' ) );
		remove_filter( 'wp_mail_content_type', array( $this, 'get_content_type' ) );

		// Reset heading to an empty string
		$this->heading = '';
	}

	/**
	 * Converts text to formatted HTML. This is primarily for turning line breaks into <p> and <br/> tags.
	 *
	 * @since 1.0.0
	 * @return $message string - The message.
	 */
	public function text_to_html( $message ) {

		if ( 'text/html' == $this->content_type || true === $this->html ) {
			$message = wpautop( $message );
		}

		return $message;
	}

	/**
	 * Get a list of registered emails.
	 *
	 * @since 1.0.0
	 * @return $emails array - Array list of the available emails.
	 */
	public function get_emails( $emails ) {

		if( !empty( $this->name ) ) :

			$emails[ $this->name ] = array(
				'id'          => $this->name,
				'title'       => $this->title,
				'description' => $this->description,
				'subject'     => $this->subject,
				'message'     => $this->message
			);

		endif;

		// return array
		return $emails;

	}

}
