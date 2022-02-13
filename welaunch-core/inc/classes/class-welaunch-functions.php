<?php
/**
 * weLaunch Framework Private Functions Container Class
 *
 * @class       weLaunch_Functions
 * @package     weLaunch_Framework/Classes
 * @since       3.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Don't duplicate me!
if ( ! class_exists( 'weLaunch_Functions', false ) ) {

	/**
	 * weLaunch Functions Class
	 * Class of useful functions that can/should be shared among all weLaunch files.
	 *
	 * @since       3.0.0
	 */
	class weLaunch_Functions {

		/**
		 * weLaunchFramework object pointer.
		 *
		 * @var object
		 */
		public static $parent;

		/**
		 * weLaunchFramework shim object pointer.
		 *
		 * @var object
		 */
		public static $_parent; // phpcs:ignore PSR2.Classes.PropertyDeclaration.Underscore

		/**
		 * Check for existence of class name via array of class names.
		 *
		 * @param array $class_names Array of class names.
		 * @return string|bool
		 */
		public static function class_exists_ex( $class_names = array() ) {
			foreach ( $class_names as $class_name ) {
				if ( class_exists( $class_name ) ) {
					return $class_name;
				}
			}

			return false;
		}

		/**
		 * Check for existence of file name via array of file names.
		 *
		 * @param array $file_names Array of file names.
		 * @return string|bool
		 */
		public static function file_exists_ex( $file_names = array() ) {
			foreach ( $file_names as $file_name ) {
				if ( file_exists( $file_name ) ) {
					return $file_name;
				}
			}

			return false;
		}

		/** Extract data:
		 * $field = field_array
		 * $value = field values
		 * $core = weLaunch instance
		 * $mode = pro field init mode */

		/**
		 * Load fields from weLaunch Pro.
		 *
		 * @param array $data Pro field data.
		 *
		 * @return bool
		 */
		public static function load_pro_field( $data ) {
			// phpcs:ignore WordPress.PHP.DontExtract
			extract( $data );

			if ( weLaunch_Core::$pro_loaded ) {
				$field_type = str_replace( '_', '-', $field['type'] );

				$field_filter = weLaunch_Pro::$dir . 'core/inc/fields/' . $field['type'] . '/class-welaunch-pro-' . $field_type . '.php';

				if ( file_exists( $field_filter ) ) {
					require_once $field_filter;

					$filter_class_name = 'weLaunch_Pro_' . $field['type'];

					if ( class_exists( $filter_class_name ) ) {
						$extend = new $filter_class_name( $field, $value, $core );
						$extend->init( $mode );

						return true;
					}
				}
			}

			return false;
		}

		/**
		 * Parse args to handle deep arrays.  The WP one does not.
		 *
		 * @param array  $args     Array of args.
		 * @param string $defaults Defaults array.
		 *
		 * @return array|string
		 */
		public static function parse_args( $args, $defaults = '' ) {
			$args     = (array) $args;
			$defaults = (array) $defaults;

			$result = $defaults;

			foreach ( $args as $k => &$v ) {
				if ( is_array( $v ) && isset( $result[ $k ] ) ) {
					$result[ $k ] = self::parse_args( $v, $result[ $k ] );
				} else {
					$result[ $k ] = $v;
				}
			}

			return $result;
		}

		/**
		 * Deprecated: Return min tag for JS and CSS files in dev_mode.
		 *
		 * @deprecated No longer using camelCase naming conventions.
		 *
		 * @return string
		 */
		public static function isMin() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName
			return self::is_min();
		}

		/**
		 * Return min tag for JS and CSS files in dev_mode.
		 *
		 * @return string
		 */
		public static function is_min() {
			$min      = '.min';
			$dev_mode = false;

			$instances = weLaunch::all_instances();

			if ( ! empty( $instances ) ) {
				foreach ( $instances as $opt_name => $instance ) {

					if ( empty( self::$parent ) ) {
						self::$parent  = $instance;
						self::$_parent = self::$parent;
					}
					if ( ! empty( $instance->args['dev_mode'] ) ) {
						$dev_mode      = true;
						self::$parent  = $instance;
						self::$_parent = self::$parent;
					}
				}
				if ( $dev_mode ) {
					$min = '';
				}
			}

			return $min;
		}

		/**
		 * Sets a cookie.
		 * Do nothing if unit testing.
		 *
		 * @since   3.5.4
		 * @access  public
		 * @return  void
		 *
		 * @param   string  $name     The cookie name.
		 * @param   string  $value    The cookie value.
		 * @param   integer $expire   Expiry time.
		 * @param   string  $path     The cookie path.
		 * @param   string  $domain   The cookie domain.
		 * @param   boolean $secure   HTTPS only.
		 * @param   boolean $httponly Only set cookie on HTTP calls.
		 */
		public static function set_cookie( $name, $value, $expire, $path, $domain = null, $secure = false, $httponly = false ) {
			if ( ! defined( 'WP_TESTS_DOMAIN' ) ) {
				setcookie( $name, $value, $expire, $path, $domain, $secure, $httponly );
			}
		}

		/**
		 * Parse CSS from output/compiler array
		 *
		 * @since       3.2.8
		 * @access      private
		 *
		 * @param array  $css_array CSS data.
		 * @param string $style CSS style.
		 * @param string $value CSS values.
		 *
		 * @return string CSS string
		 */
		public static function parse_css( $css_array = array(), $style = '', $value = '' ) {

			// Something wrong happened.
			if ( 0 === count( $css_array ) ) {
				return '';
			} else {
				$css = '';

				foreach ( $css_array as $element => $selector ) {

					// The old way.
					if ( 0 === $element ) {
						$css = self::the_old_way( $css_array, $style );

						return $css;
					}

					// New way continued.
					$css_style = $element . ':' . $value . ';';

					$css .= $selector . '{' . $css_style . '}';
				}
			}

			return $css;
		}

		/**
		 * Parse CSS shim.
		 *
		 * @since       4.0.0
		 * @access      public
		 *
		 * @param array  $css_array CSS data.
		 * @param string $style CSS style.
		 * @param string $value CSS values.
		 *
		 * @return string CSS string
		 */
		public static function parseCSS( $css_array = array(), $style = '', $value = '' ) { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName
			return self::parse_css( $css_array, $style, $value );
		}

		/**
		 * Parse CSS the old way, without mode options.
		 *
		 * @param array  $css_array CSS data.
		 * @param string $style CSS style.
		 *
		 * @return string
		 */
		private static function the_old_way( $css_array, $style ) {
			$keys = implode( ',', $css_array );
			$css  = $keys . '{' . $style . '}';

			return $css;
		}

		/**
		 * Deprecated Initialized the WordPress filesystem, if it already isn't.
		 *
		 * @since       3.2.3
		 * @access      public
		 * @deprecated NO longer using camelCase naming conventions.
		 *
		 * @return      void
		 */
		public static function initWpFilesystem() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName
			self::init_wp_filesystem();
		}

		/**
		 * Initialized the WordPress filesystem, if it already isn't.
		 *
		 * @since       3.2.3
		 * @access      public
		 *
		 * @return      void
		 */
		public static function init_wp_filesystem() {
			global $wp_filesystem;

			// Initialize the WordPress filesystem, no more using file_put_contents function.
			if ( empty( $wp_filesystem ) ) {
				require_once ABSPATH . '/wp-includes/pluggable.php';
				require_once ABSPATH . '/wp-admin/includes/file.php';

				WP_Filesystem();
			}
		}

		/**
		 * Support Hash.
		 */
		public static function support_hash() {
			if ( isset( $_POST['nonce'] ) ) {
				if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['nonce'] ) ), 'welaunch-support-hash' ) ) {
					die();
				}

				$data          = get_option( 'welaunch_support_hash' );
				$data          = wp_parse_args(
					$data,
					array(
						'check'      => '',
						'identifier' => '',
					)
				);
				$generate_hash = true;
				$system_info   = weLaunch_Helpers::compile_system_status();
				$new_hash      = md5( wp_json_encode( $system_info ) );
				$return        = array();

				if ( $data['check'] === $new_hash ) {
					unset( $generate_hash );
				}

				$post_data = array(
					'hash'          => md5( network_site_url() . '-' . weLaunch_Core::$server['REMOTE_ADDR'] ),
					'site'          => esc_url( home_url( '/' ) ),
					'tracking'      => weLaunch_Helpers::get_statistics_object(),
					'system_status' => $system_info,
				);

				$post_data = maybe_serialize( $post_data );

				if ( isset( $generate_hash ) && $generate_hash ) {
					$data['check']      = $new_hash;
					$data['identifier'] = '';
					$response           = wp_remote_post(
						'https://api.welaunch.io/support',
						array(
							'method'      => 'POST',
							'timeout'     => 65,
							'redirection' => 5,
							'httpversion' => '1.0',
							'blocking'    => true,
							'compress'    => true,
							'headers'     => weLaunch_Helpers::get_request_headers(),
							'body'        => array(
								'data'      => $post_data,
								'serialize' => 1,
							),
						)
					);

					if ( is_wp_error( $response ) ) {
						echo wp_json_encode(
							array(
								'status'  => 'error',
								'message' => $response->get_error_message(),
							)
						);

						die( 1 );
					} else {
						$response_code = wp_remote_retrieve_response_code( $response );
						if ( 200 === $response_code ) {
							$response = wp_remote_retrieve_body( $response );
							$return   = json_decode( $response, true );

							if ( isset( $return['identifier'] ) ) {
								$data['identifier'] = $return['identifier'];
								update_option( 'welaunch_support_hash', $data );
							}
						} else {
							$response = wp_remote_retrieve_body( $response );

							echo wp_json_encode(
								array(
									'status'  => 'error',
									'message' => $response,
								)
							);
						}
					}
				}

				if ( ! empty( $data['identifier'] ) ) {
					$return['status']     = 'success';
					$return['identifier'] = $data['identifier'];
				} else {
					$return['status']  = 'error';
					$return['message'] = esc_html__( 'Support hash could not be generated. Please try again later.', 'welaunch-framework' );
				}

				echo wp_json_encode( $return );

				die( 1 );
			}
		}

		/**
		 * Sanatize camcelCase keys in array, makes then snake_case.
		 *
		 * @param array $arr Array of keys.
		 *
		 * @return array
		 */
		public static function sanitize_camel_case_array_keys( $arr ) {
			$keys   = array_keys( $arr );
			$values = array_values( $arr );

			$result = preg_replace_callback(
				'/[A-Z]/',
				function ( $matches ) {
					return '-' . weLaunch_Core::strtolower( $matches[0] );
				},
				$keys
			);

			$output = array_combine( $result, $values );

			return $output;
		}

		/**
		 * Converts an array into a html data string.
		 *
		 * @param array $data example input: array('id'=>'true').
		 *
		 * @return string $data_string example output: data-id='true'
		 */
		public static function create_data_string( $data = array() ) {
			$data_string = '';

			foreach ( $data as $key => $value ) {
				if ( is_array( $value ) ) {
					$value = implode( '|', $value );
				}

				$data_string .= ' data-' . $key . '=' . weLaunch_Helpers::make_bool_str( $value ) . '';
			}

			return $data_string;
		}
	}
}
