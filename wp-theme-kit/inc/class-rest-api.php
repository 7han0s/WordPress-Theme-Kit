<?php
/**
 * REST API Class
 *
 * @package WpThemeKit
 */

namespace ThemeKit;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers REST API endpoint for saving user preferences.
 */
class Rest_Api {

	/**
	 * The namespace for the REST API.
	 *
	 * @var string
	 */
	private $namespace = 'theme-kit/v1';

	/**
	 * The meta key for saving the user preference.
	 *
	 * @var string
	 */
	private $meta_key = 'theme_preference';

	/**
	 * Registers the REST API routes upon initialization.
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Registers the REST API routes.
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/preference',
			array(
				'methods'             => \WP_REST_Server::EDITABLE, // Corresponds to POST, PUT, PATCH.
				'callback'            => array( $this, 'update_preference' ),
				'permission_callback' => array( $this, 'permission_check' ),
				'args'                => array(
					'preference' => array(
						'required'          => true,
						'validate_callback' => array( $this, 'is_valid_preference' ),
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);
	}

	/**
	 * Permission check for the REST API endpoint.
	 *
	 * @return bool True if the user is logged in, otherwise false.
	 */
	public function permission_check() {
		// Only logged-in users should be able to save a preference to their profile.
		return is_user_logged_in();
	}

	/**
	 * Validation callback for the 'preference' parameter.
	 *
	 * @param string $param The 'preference' parameter from the request.
	 * @return bool True if the parameter is a valid preference, otherwise false.
	 */
	public function is_valid_preference( $param ) {
		return in_array( $param, array( 'auto', 'light', 'dark' ), true );
	}

	/**
	 * The callback function to update the user's theme preference.
	 *
	 * @param \WP_REST_Request $request The REST request object.
	 * @return \WP_REST_Response The response object.
	 */
	public function update_preference( $request ) {
		$preference = $request->get_param( 'preference' );
		$user_id    = get_current_user_id();

		$result = update_user_meta( $user_id, $this->meta_key, $preference );

		if ( false === $result ) {
			return new \WP_REST_Response(
				array(
					'success' => false,
					'message' => __( 'Could not save preference due to a server error.', 'wp-theme-kit' ),
				),
				500
			);
		}

		return new \WP_REST_Response(
			array(
				'success'    => true,
				'message'    => __( 'Preference saved successfully.', 'wp-theme-kit' ),
				'preference' => $preference,
			),
			200
		);
	}
}
