<?php
/**
 * weLaunch Primary Enqueue Class
 *
 * @class weLaunch_Core
 * @version 4.0.0
 * @package weLaunch Framework/Classes
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'weLaunch_Enqueue', false ) ) {

	/**
	 * Class weLaunch_Enqueue
	 */
	class weLaunch_Enqueue extends weLaunch_Class {

		/**
		 * Data to localize.
		 *
		 * @var array
		 */
		public $localize_data = array();

		/**
		 * Min string for .min files.
		 *
		 * @var string
		 */
		private $min = '';

		/**
		 * Timestamp for file versions.
		 *
		 * @var string
		 */
		private $timestamp = '';

		/**
		 * Localize data required for the repeater extension.
		 *
		 * @var array
		 */
		private $repeater_data = array();

		/**
		 * weLaunch_Enqueue constructor.
		 *
		 * @param     object $parent weLaunchFramework pointer.
		 */
		public function __construct( $parent ) {
			parent::__construct( $parent );

			// Enqueue the admin page CSS and JS.
			if ( isset( $_GET['page'] ) && $_GET['page'] === $parent->args['page_slug'] ) { // phpcs:ignore WordPress.Security.NonceVerification
				add_action( 'admin_enqueue_scripts', array( $this, 'init' ), 1 );
			}

			add_action( 'wp_enqueue_scripts', array( $this, 'frontend_init' ), 10 );

			// phpcs:ignore WordPress.NamingConventions.ValidHookName
			do_action( "welaunch/{$parent->args['opt_name']}/enqueue/construct", $this );
			// phpcs:ignore WordPress.NamingConventions.ValidHookName
			do_action( 'welaunch/enqueue/construct', $this );
		}

		/**
		 * Scripts to enqueue on the frontend
		 */
		public function frontend_init() {
			$core = $this->core();

			if ( $core->args['elusive_frontend'] ) {
				wp_enqueue_style(
					'welaunch-elusive-icon',
					weLaunch_Core::$url . 'assets/css/vendor/elusive-icons.min.css',
					array(),
					weLaunch_Core::$version,
					'all'
				);
			}
		}

		/**
		 * Class init functions.
		 */
		public function init() {
			$core = $this->core();

			weLaunch_Functions::$parent = $core;
			weLaunch_CDN::$parent       = $core;

			$this->min = weLaunch_Functions::is_min();

			$this->timestamp = weLaunch_Core::$version;
			if ( $core->args['dev_mode'] ) {
				$this->timestamp .= '.' . time();
			}

			$this->register_styles( $core );
			$this->register_scripts( $core );

			add_thickbox();

			$this->enqueue_fields( $core );

			add_filter( "welaunch/{$core->args['opt_name']}/localize", array( 'weLaunch_Helpers', 'localize' ) );

			$this->set_localized_data( $core );

			/**
			 * Action 'welaunch/page/{opt_name}/enqueue'
			 */
			// phpcs:ignore WordPress.NamingConventions.ValidHookName
			do_action( "welaunch/page/{$core->args['opt_name']}/enqueue" );
		}

		/**
		 * Register all core framework styles.
		 *
		 * @param     object $core weLaunchFramework object.
		 */
		private function register_styles( $core ) {

			// *****************************************************************
			// weLaunch Admin CSS
			// *****************************************************************
			if ( 'wordpress' === $core->args['admin_theme'] || 'wp' === $core->args['admin_theme'] ) { // phpcs:ignore WordPress.WP.CapitalPDangit
				$color_scheme = get_user_option( 'admin_color' );
			} elseif ( 'classic' === $core->args['admin_theme'] || '' === $core->args['admin_theme'] ) {
				$color_scheme = 'classic';
			} else {
				$color_scheme = $core->args['admin_theme'];
			}

			if ( ! file_exists( weLaunch_Core::$dir . "assets/css/colors/$color_scheme/colors{$this->min}.css" ) ) {
				$color_scheme = 'fresh';
			}

			$css = weLaunch_Core::$url . "assets/css/colors/$color_scheme/colors{$this->min}.css";

			// phpcs:ignore WordPress.NamingConventions.ValidHookName
			$css = apply_filters( 'welaunch/enqueue/' . $core->args['opt_name'] . '/args/admin_theme/css_url', $css );

			wp_register_style(
				'welaunch-admin-theme-css',
				$css,
				array(),
				$this->timestamp,
				'all'
			);

			wp_enqueue_style(
				'welaunch-admin-css',
				weLaunch_Core::$url . "assets/css/welaunch-admin{$this->min}.css",
				array( 'welaunch-admin-theme-css' ),
				$this->timestamp,
				'all'
			);

			// *****************************************************************
			// weLaunch Fields CSS
			// *****************************************************************
			if ( ! $core->args['dev_mode'] ) {
				wp_enqueue_style(
					'welaunch-fields-css',
					weLaunch_Core::$url . 'assets/css/welaunch-fields.min.css',
					array(),
					$this->timestamp,
					'all'
				);
			}

			// *****************************************************************
			// Select2 CSS
			// *****************************************************************
			wp_enqueue_style(
				'select2-css',
				weLaunch_Core::$url . 'assets/css/vendor/select2.min.css',
				array(),
				'4.0.5',
				'all'
			);

			// *****************************************************************
			// Spectrum CSS
			// *****************************************************************
			$css_file = 'welaunch-spectrum.css';

			wp_register_style(
				'welaunch-spectrum-css',
				weLaunch_Core::$url . "assets/css/vendor/spectrum{$this->min}.css",
				array(),
				'1.3.3',
				'all'
			);

			// *****************************************************************
			// Elusive Icon CSS
			// *****************************************************************
			wp_enqueue_style(
				'welaunch-elusive-icon',
				weLaunch_Core::$url . "assets/css/vendor/elusive-icons{$this->min}.css",
				array(),
				$this->timestamp,
				'all'
			);

			// *****************************************************************
			// QTip CSS
			// *****************************************************************
			wp_enqueue_style(
				'qtip-css',
				weLaunch_Core::$url . "assets/css/vendor/qtip{$this->min}.css",
				array(),
				'2.2.0',
				'all'
			);

			// *****************************************************************
			// JQuery UI CSS
			// *****************************************************************

			wp_enqueue_style(
				'jquery-ui-css',
				// phpcs:ignore WordPress.NamingConventions.ValidHookName
				apply_filters(
					// phpcs:ignore WordPress.NamingConventions.ValidHookName
					"welaunch/page/{$core->args['opt_name']}/enqueue/jquery-ui-css",
					weLaunch_Core::$url . 'assets/css/vendor/jquery-ui-1.10.0.custom.css'
				),
				array(),
				$this->timestamp,
				'all'
			);

			// *****************************************************************
			// Iris CSS
			// *****************************************************************
			wp_enqueue_style( 'wp-color-picker' );

			if ( $core->args['dev_mode'] ) {
				// *****************************************************************
				// Media CSS
				// *****************************************************************
				wp_enqueue_style(
					'welaunch-field-media-css',
					weLaunch_Core::$url . 'assets/css/media.css',
					array(),
					$this->timestamp,
					'all'
				);
			}

			// *****************************************************************
			// RTL CSS
			// *****************************************************************
			if ( is_rtl() ) {
				wp_enqueue_style(
					'welaunch-rtl-css',
					weLaunch_Core::$url . 'assets/css/rtl.css',
					array( 'welaunch-admin-css' ),
					$this->timestamp,
					'all'
				);
			}
		}

		/**
		 * Register all core framework scripts.
		 *
		 * @param     object $core weLaunchFramework object.
		 */
		private function register_scripts( $core ) {
			// *****************************************************************
			// JQuery / JQuery UI JS
			// *****************************************************************
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'jquery-ui-core' );
			wp_enqueue_script( 'jquery-ui-dialog' );

			// *****************************************************************
			// Select2 Sortable JS
			// *****************************************************************
			wp_register_script(
				'welaunch-select2-sortable-js',
				weLaunch_Core::$url . 'assets/js/vendor/select2-sortable/welaunch.select2.sortable' . $this->min . '.js',
				array( 'jquery', 'jquery-ui-sortable' ),
				$this->timestamp,
				true
			);

			wp_enqueue_script(
				'select2-js',
				weLaunch_Core::$url . 'assets/js/vendor/select2/select2' . $this->min . '.js`',
				array( 'jquery', 'welaunch-select2-sortable-js' ),
				'4.0.5',
				true
			);

			// *****************************************************************
			// QTip JS
			// *****************************************************************
			wp_enqueue_script(
				'qtip-js',
				weLaunch_Core::$url . 'assets/js/vendor/qtip/qtip' . $this->min . '.js',
				array( 'jquery' ),
				'2.2.0',
				true
			);

			// *****************************************************************
			// Spectrum JS
			// *****************************************************************
			wp_register_script(
				'welaunch-spectrum-js',
				weLaunch_Core::$url . 'assets/js/vendor/spectrum/welaunch-spectrum' . $this->min . '.js',
				array( 'jquery' ),
				'1.3.3',
				true
			);

			// *****************************************************************
			// Vendor JS
			// *****************************************************************
			wp_register_script(
				'welaunch-vendor',
				weLaunch_Core::$url . 'assets/js/welaunch-vendors' . $this->min . '.js',
				array( 'jquery' ),
				$this->timestamp,
				true
			);

			// *****************************************************************
			// weLaunch JS
			// *****************************************************************
			wp_register_script(
				'welaunch-js',
				weLaunch_Core::$url . 'assets/js/welaunch' . $this->min . '.js',
				array( 'jquery', 'welaunch-vendor' ),
				$this->timestamp,
				true
			);

			if ( $core->args['async_typography'] ) {
				wp_enqueue_script(
					'webfontloader',
					// phpcs:ignore Generic.Strings.UnnecessaryStringConcat
					'//' . 'ajax' . '.googleapis' . '.com/ajax/libs/webfont/1.6.26/webfont.js',
					array( 'jquery', 'welaunch-vendor' ),
					'1.6.26',
					true
				);
			}
		}

		/**
		 * Enqueue fields that are in use.
		 *
		 * @param     object $core weLaunchFramework object.
		 * @param     array  $field Field array.
		 */
		public function enqueue_field( $core, $field ) {
			if ( isset( $field['type'] ) && 'callback' !== $field['type'] ) {

				/**
				 * Field class file
				 * filter 'welaunch/{opt_name}/field/class/{field.type}
				 *
				 * @param     string        field class file path
				 * @param     array     $field field config data
				 */
				$field_type = str_replace( '_', '-', $field['type'] );
				$core_path  = weLaunch_Core::$dir . "inc/fields/{$field['type']}/class-welaunch-{$field_type}.php";

				// Shim for v3 extension class names.
				if ( ! file_exists( $core_path ) ) {
					$core_path = weLaunch_Core::$dir . "inc/fields/{$field['type']}/field_{$field['type']}.php";
				}

				$filter_path = $core_path;

				// phpcs:ignore WordPress.NamingConventions.ValidHookName
				$class_file = apply_filters(
					// phpcs:ignore WordPress.NamingConventions.ValidHookName
					"welaunch/{$core->args['opt_name']}/field/class/{$field['type']}",
					$filter_path,
					$field
				);

				$field_classes = array( 'weLaunch_' . $field['type'], 'weLaunchFramework_' . $field['type'] );

				if ( $class_file ) {
					$field_class = weLaunch_Functions::class_exists_ex( $field_classes );
					if ( false === $field_class ) {
						if ( file_exists( $class_file ) ) {
							require_once $class_file;

							$field_class = weLaunch_Functions::class_exists_ex( $field_classes );
						} else {
							return;
						}
					}

					if ( ( method_exists( $field_class, 'enqueue' ) ) || method_exists( $field_class, 'localize' ) ) {
						if ( ! isset( $core->options[ $field['id'] ] ) ) {
							$core->options[ $field['id'] ] = '';
						}

						$data = array(
							'field' => $field,
							'value' => $core->options[ $field['id'] ],
							'core'  => $core,
							'mode'  => 'enqueue',
						);

						weLaunch_Functions::load_pro_field( $data );

						$the_field = new $field_class( $field, $core->options[ $field['id'] ], $core );

						if ( weLaunch_Core::$pro_loaded ) {
							$field_filter = weLaunch_Pro::$dir . 'core/inc/fields/' . $field['type'] . '/class-welaunch-pro-' . $field_type . '.php';

							if ( file_exists( $field_filter ) ) {
								require_once $field_filter;

								$filter_class_name = 'weLaunch_Pro_' . $field['type'];

								if ( class_exists( $filter_class_name ) ) {
									$extend = new $filter_class_name( $field, $core->options[ $field['id'] ], $core );
									$extend->init( 'enqueue' );
								}
							}
						}

						// Move dev_mode check to a new if/then block.
						if ( ( ! wp_script_is( 'welaunch-field-' . $field_type . '-js', 'enqueued' ) || ! wp_script_is(
							'welaunch-extension-' . $field_type . '-js',
							'enqueued'
						) || ! wp_script_is(
							'welaunch-pro-field-' . $field_type . '-js',
							'enqueued'
						) ) && class_exists( $field_class ) && method_exists( $field_class, 'enqueue' ) ) {
							$the_field->enqueue();
						}

						if ( method_exists( $field_class, 'localize' ) ) {
							$params = $the_field->localize( $field );
							if ( ! isset( $this->localize_data[ $field['type'] ] ) ) {
								$this->localize_data[ $field['type'] ] = array();
							}

							$localize_data = $the_field->localize( $field );

							$shims = array( 'repeater' );

							// phpcs:ignore WordPress.NamingConventions.ValidHookName
							$shims = apply_filters( 'welaunch/' . $core->args['opt_name'] . '/localize/shims', $shims );

							if ( is_array( $shims ) && in_array( $field['type'], $shims, true ) ) {
								$this->repeater_data[ $field['type'] ][ $field['id'] ] = $localize_data;
							}

							$this->localize_data[ $field['type'] ][ $field['id'] ] = $localize_data;
						}

						unset( $the_field );
					}
				}
			}
		}

		/**
		 * Enqueue field files.
		 *
		 * @param     object $core weLaunchFramework object.
		 */
		private function enqueue_fields( $core ) {
			$data = array();

			foreach ( $core->sections as $section ) {
				if ( isset( $section['fields'] ) ) {
					foreach ( $section['fields'] as $field ) {
						$this->enqueue_field( $core, $field );
					}
				}
			}
		}

		/**
		 * Build localize array from field functions, if any.
		 *
		 * @param     object $core weLaunchFramework object.
		 * @param     string $type Field type.
		 */
		private function build_local_array( $core, $type ) {
			if ( isset( $core->transients['last_save_mode'] ) && ! empty( $core->transients['notices'][ $type ] ) ) {
				$the_total = 0;
				$messages  = array();

				foreach ( $core->transients['notices'][ $type ] as $msg ) {
					$messages[ $msg['section_id'] ][ $type ][] = $msg;

					if ( ! isset( $messages[ $msg['section_id'] ]['total'] ) ) {
						$messages[ $msg['section_id'] ]['total'] = 0;
					}

					$messages[ $msg['section_id'] ]['total'] ++;
					$the_total ++;
				}

				$this->localize_data[ $type ] = array(
					'total'   => $the_total,
					"{$type}" => $messages,
				);

				unset( $core->transients['notices'][ $type ] );
			}
		}

		/**
		 * Compile panel errors and warings for locaize array.
		 */
		public function get_warnings_and_errors_array() {
			$core = $this->core();

			$this->build_local_array( $core, 'errors' );
			$this->build_local_array( $core, 'warnings' );
			$this->build_local_array( $core, 'sanitize' );

			if ( empty( $core->transients['notices'] ) ) {
				unset( $core->transients['notices'] );
			}
		}

		/**
		 * Commit localized data to global array.
		 *
		 * @param     object $core weLaunchFramework object.
		 */
		private function set_localized_data( $core ) {
			if ( ! empty( $core->args['last_tab'] ) ) {
				$this->localize_data['last_tab'] = $core->args['last_tab'];
			}

			$this->localize_data['core_instance'] = $core->core_instance;
			$this->localize_data['core_thread']   = $core->core_thread;

			$this->localize_data['font_weights'] = $this->args['font_weights'];

			$this->localize_data['required'] = $core->required;
			$this->repeater_data['fonts']    = $core->fonts;
			if ( ! isset( $this->repeater_data['opt_names'] ) ) {
				$this->repeater_data['opt_names'] = array();
			}
			$this->repeater_data['opt_names'][]    = $core->args['opt_name'];
			$this->repeater_data['folds']          = array();
			$this->localize_data['required_child'] = $core->required_child;
			$this->localize_data['fields']         = $core->fields;

			if ( isset( $core->font_groups['google'] ) ) {
				$this->repeater_data['googlefonts'] = $core->font_groups['google'];
			}

			if ( isset( $core->font_groups['std'] ) ) {
				$this->repeater_data['stdfonts'] = $core->font_groups['std'];
			}

			if ( isset( $core->font_groups['customfonts'] ) ) {
				$this->repeater_data['customfonts'] = $core->font_groups['customfonts'];
			}

			if ( isset( $core->font_groups['typekitfonts'] ) ) {
				$this->repeater_data['typekitfonts'] = $core->font_groups['typekitfonts'];
			}

			$this->localize_data['folds'] = $core->folds;

			// Make sure the children are all hidden properly.
			foreach ( $core->fields as $key => $value ) {
				if ( in_array( $key, $core->fields_hidden, true ) ) {
					foreach ( $value as $k => $v ) {
						if ( ! in_array( $k, $core->fields_hidden, true ) ) {
							$core->fields_hidden[] = $k;
							$core->folds[ $k ]     = 'hide';
						}
					}
				}
			}

			$this->localize_data['fields_hidden'] = $core->fields_hidden;
			$this->localize_data['options']       = $core->options;
			$this->localize_data['defaults']      = $core->options_defaults;

			/**
			 * Save pending string
			 * filter 'welaunch/{opt_name}/localize/save_pending
			 *
			 * @param     string        save_pending string
			 */
			// phpcs:ignore WordPress.NamingConventions.ValidHookName
			$save_pending = apply_filters(
				// phpcs:ignore WordPress.NamingConventions.ValidHookName
				"welaunch/{$core->args['opt_name']}/localize/save_pending",
				esc_html__(
					'You have changes that are not saved. Would you like to save them now?',
					'welaunch-framework'
				)
			);

			/**
			 * Reset all string
			 * filter 'welaunch/{opt_name}/localize/reset
			 *
			 * @param     string        reset all string
			 */
			// phpcs:ignore WordPress.NamingConventions.ValidHookName
			$reset_all = apply_filters(
				// phpcs:ignore WordPress.NamingConventions.ValidHookName
				"welaunch/{$core->args['opt_name']}/localize/reset",
				esc_html__(
					'Are you sure? Resetting will lose all custom values.',
					'welaunch-framework'
				)
			);

			/**
			 * Reset section string
			 * filter 'welaunch/{opt_name}/localize/reset_section
			 *
			 * @param     string        reset section string
			 */
			// phpcs:ignore WordPress.NamingConventions.ValidHookName
			$reset_section = apply_filters(
				// phpcs:ignore WordPress.NamingConventions.ValidHookName
				"welaunch/{$core->args['opt_name']}/localize/reset_section",
				esc_html__(
					'Are you sure? Resetting will lose all custom values in this section.',
					'welaunch-framework'
				)
			);

			/**
			 * Preset confirm string
			 * filter 'welaunch/{opt_name}/localize/preset
			 *
			 * @param     string        preset confirm string
			 */
			// phpcs:ignore WordPress.NamingConventions.ValidHookName
			$preset_confirm = apply_filters(
				// phpcs:ignore WordPress.NamingConventions.ValidHookName
				"welaunch/{$core->args['opt_name']}/localize/preset",
				esc_html__(
					'Your current options will be replaced with the values of this preset. Would you like to proceed?',
					'welaunch-framework'
				)
			);

			/**
			 * Import confirm string
			 * filter 'welaunch/{opt_name}/localize/import
			 *
			 * @param     string        import confirm string
			 */
			// phpcs:ignore WordPress.NamingConventions.ValidHookName
			$import_confirm = apply_filters(
				// phpcs:ignore WordPress.NamingConventions.ValidHookName
				"welaunch/{$core->args['opt_name']}/localize/import",
				esc_html__(
					'Your current options will be replaced with the values of this import. Would you like to proceed?',
					'welaunch-framework'
				)
			);

			global $pagenow;

			$this->localize_data['args'] = array(
				'dev_mode'               => $core->args['dev_mode'],
				'save_pending'           => $save_pending,
				'reset_confirm'          => $reset_all,
				'reset_section_confirm'  => $reset_section,
				'preset_confirm'         => $preset_confirm,
				'import_section_confirm' => $import_confirm,
				'please_wait'            => esc_html__( 'Please Wait', 'welaunch-framework' ),
				'opt_name'               => $core->args['opt_name'],
				'flyout_submenus'        => isset( $core->args['pro']['flyout_submenus'] ) ? $core->args['pro']['flyout_submenus'] : false,
				'slug'                   => $core->args['page_slug'],
				'hints'                  => $core->args['hints'],
				'disable_save_warn'      => $core->args['disable_save_warn'],
				'class'                  => $core->args['class'],
				'ajax_save'              => $core->args['ajax_save'],
				'menu_search'            => $pagenow . '?page=' . $core->args['page_slug'] . '&tab=',
			);

			$this->localize_data['ajax'] = array(
				'console' => esc_html__(
					'There was an error saving. Here is the result of your action:',
					'welaunch-framework'
				),
				'alert'   => esc_html__(
					'There was a problem with your action. Please try again or reload the page.',
					'welaunch-framework'
				),
			);

			// phpcs:ignore WordPress.NamingConventions.ValidHookName
			$this->localize_data = apply_filters( "welaunch/{$core->args['opt_name']}/localize", $this->localize_data );

			// phpcs:ignore WordPress.NamingConventions.ValidHookName
			$this->repeater_data = apply_filters( "welaunch/{$core->args['opt_name']}/repeater", $this->repeater_data );

			$this->get_warnings_and_errors_array();

			if ( ! isset( $core->repeater_data ) ) {
				$core->repeater_data = array();
			}
			$core->repeater_data = weLaunch_Functions_Ex::nested_wp_parse_args(
				$this->repeater_data,
				$core->repeater_data
			);

			if ( ! isset( $core->localize_data ) ) {
				$core->localize_data = array();
			}
			$core->localize_data = weLaunch_Functions_Ex::nested_wp_parse_args(
				$this->localize_data,
				$core->localize_data
			);

			// Shim for extension compatibility.
			if ( weLaunch::$extension_compatibility ) {
				$this->repeater_data = weLaunch_Functions_Ex::nested_wp_parse_args(
					$this->repeater_data,
					$core->localize_data
				);
			}

			wp_localize_script(
				'welaunch-js',
				'welaunch',
				$this->repeater_data
			);

			wp_localize_script(
				'welaunch-js',
				'welaunch_' . str_replace( '-', '_', $core->args['opt_name'] ),
				$this->localize_data
			);

			wp_enqueue_script( 'welaunch-js' ); // Enqueue the JS now.
		}
	}
}

if ( ! class_exists( 'welaunchCoreEnqueue' ) ) {
	class_alias( 'weLaunch_Enqueue', 'welaunchCoreEnqueue' );
}
