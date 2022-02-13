<?php
/**
 * Image Select Field.
 *
 * @package     weLaunchFramework/Fields
 * @author      Dovy Paukstys & Kevin Provance (kprovance)
 * @version     4.0.0
 */

defined( 'ABSPATH' ) || exit;

// Don't duplicate me!
if ( ! class_exists( 'weLaunch_Image_Select', false ) ) {

	/**
	 * Main weLaunch_image_select class
	 *
	 * @since       1.0.0
	 */
	class weLaunch_Image_Select extends weLaunch_Field {

		/**
		 * Field Render Function.
		 * Takes the vars and outputs the HTML for the field in the settings
		 *
		 * @since       1.0.0
		 * @access      public
		 * @return      void
		 */
		public function render() {
			if ( ! empty( $this->field['options'] ) ) {
				echo '<div class="welaunch-table-container">';
				echo '<ul class="welaunch-image-select">';

				$x = 1;

				foreach ( $this->field['options'] as $k => $v ) {

					if ( ! is_array( $v ) ) {
						$v = array( 'img' => $v );
					}

					if ( ! isset( $v['title'] ) ) {
						$v['title'] = '';
					}

					if ( ! isset( $v['alt'] ) ) {
						$v['alt'] = $v['title'];
					}

					if ( ! isset( $v['class'] ) ) {
						$v['class'] = '';
					}

					$style = '';

					if ( ! empty( $this->field['width'] ) ) {
						$style .= 'width: ' . $this->field['width'];

						if ( is_numeric( $this->field['width'] ) ) {
							$style .= 'px';
						}

						$style .= ';';
					} else {
						$style .= ' width: 100%; ';
					}

					if ( ! empty( $this->field['height'] ) ) {
						$style .= 'height: ' . $this->field['height'];

						if ( is_numeric( $this->field['height'] ) ) {
							$style .= 'px';
						}

						$style .= ';';
					}

					$the_value = $k;

					$selected = ( '' !== checked( $this->value, $the_value, false ) ) ? ' welaunch-image-select-selected' : '';

					if ( ! empty( $this->field['tiles'] ) && true === $this->field['tiles'] ) {
						$the_value = $v['img'];
					}

					$presets   = '';
					$is_preset = false;

					$this->field['class']  = trim( str_replace( 'no-update', '', $this->field['class'] ) );
					$this->field['class'] .= ' no-update ';

					if ( isset( $this->field['presets'] ) && false !== $this->field['presets'] ) {
						$this->field['class'] = trim( $this->field['class'] );
						if ( ! isset( $v['presets'] ) ) {
							$v['presets'] = array();
						}

						if ( ! is_array( $v['presets'] ) ) {
							$v['presets'] = json_decode( $v['presets'], true );
						}

						// Only highlight the preset if it's the same.
						if ( $selected ) {
							if ( empty( $v['presets'] ) ) {
								$selected = false;
							} else {
								foreach ( $v['presets'] as $pk => $pv ) {
									if ( isset( $v['merge'] ) && false !== $v['merge'] ) {
										if ( ( true === $v['merge'] || in_array( $pk, $v['merge'], true ) ) && is_array( $this->parent->options[ $pk ] ) ) {
											$pv = array_merge( $this->parent->options[ $pk ], $pv );
										}
									}

									if ( empty( $pv ) && isset( $this->parent->options[ $pk ] ) && ! empty( $this->parent->options[ $pk ] ) ) {
										$selected = false;
									} elseif ( ! empty( $pv ) && ! isset( $this->parent->options[ $pk ] ) ) {
										$selected = false;
									}

									if ( ! $selected ) { // We're still not using the same preset. Let's unset that shall we?
										$this->value = '';
										break;
									}
								}
							}
						}

						$v['presets']['welaunch-backup'] = 1;

						$presets   = ' data-presets="' . esc_attr( htmlspecialchars( wp_json_encode( $v['presets'] ), ENT_QUOTES, 'UTF-8' ) ) . '"';
						$is_preset = true;

						$this->field['class'] = trim( $this->field['class'] ) . ' welaunch-presets';
					}

					$is_preset_class = $is_preset ? '-preset-' : ' ';

					$merge = '';
					if ( isset( $v['merge'] ) && false !== $v['merge'] ) {
						$merge = is_array( $v['merge'] ) ? implode( '|', $v['merge'] ) : 'true';
						$merge = ' data-merge="' . esc_attr( htmlspecialchars( $merge, ENT_QUOTES, 'UTF-8' ) ) . '"';
					}

					echo '<li class="welaunch-image-select">';
					echo '<label class="' . esc_attr( $selected ) . ' welaunch-image-select' . esc_attr( $is_preset_class ) . esc_attr( $this->field['id'] . '_' . $x ) . '" for="' . esc_attr( $this->field['id'] . '_' . ( array_search( $k, array_keys( $this->field['options'] ), true ) + 1 ) ) . '">';
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo '<input type="radio" class="' . esc_attr( $this->field['class'] ) . '" id="' . esc_attr( $this->field['id'] . '_' . ( array_search( $k, array_keys( $this->field['options'] ), true ) + 1 ) ) . '" name="' . esc_attr( $this->field['name'] . $this->field['name_suffix'] ) . '" value="' . esc_attr( $the_value ) . '" ' . checked( $this->value, $the_value, false ) . $presets . $merge . '/>';
					if ( ! empty( $this->field['tiles'] ) && true === $this->field['tiles'] ) {
						echo '<span class="tiles ' . esc_attr( $v['class'] ) . '" style="background-image: url(' . esc_url( $v['img'] ) . ');" rel="' . esc_url( $v['img'] ) . '"">&nbsp;</span>';
					} else {
						echo '<img src="' . esc_url( $v['img'] ) . '" title="' . esc_attr( $v['alt'] ) . '" alt="' . esc_attr( $v['alt'] ) . '" class="' . esc_attr( $v['class'] ) . '" style="' . esc_attr( $style ) . '"' . esc_attr( $presets ) . esc_attr( $merge ) . ' />';
					}

					if ( '' !== $v['title'] ) {
						echo '<br /><span>' . wp_kses_post( ( $v['title'] ) ) . '</span>';
					}

					echo '</label>';
					echo '</li>';

					$x ++;
				}

				echo '</ul>';
				echo '</div>';
			}
		}

		/**
		 * Enqueue Function.
		 * If this field requires any scripts, or css define this function and register/enqueue the scripts/css
		 *
		 * @since       1.0.0
		 * @access      public
		 * @return      void
		 */
		public function enqueue() {

			wp_enqueue_script(
				'welaunch-field-image-select-js',
				weLaunch_Core::$url . 'inc/fields/image_select/welaunch-image-select' . weLaunch_Functions::is_min() . '.js',
				array( 'jquery', 'welaunch-js' ),
				$this->timestamp,
				true
			);

			if ( $this->parent->args['dev_mode'] ) {
				wp_enqueue_style(
					'welaunch-field-image-select-css',
					weLaunch_Core::$url . 'inc/fields/image_select/welaunch-image-select.css',
					array(),
					$this->timestamp,
					'all'
				);
			}
		}

		/**
		 * Compile CSS data for output.
		 *
		 * @param string $data css string.
		 *
		 * @return string|void
		 */
		public function css_style( $data ) {
			$css    = '';
			$output = '';

			$mode = ( isset( $this->field['mode'] ) && ! empty( $this->field['mode'] ) ? $this->field['mode'] : 'background-image' );

			if ( ! empty( $data ) && ! is_array( $data ) ) {
				switch ( $mode ) {
					case 'background-image':
						$img = isset( $this->field['options'][ $data ]['img'] ) ? $this->field['options'][ $data ]['img'] : '';
						if ( '' !== $img ) {
							$output = "background-image: url('" . esc_url( $img ) . "');";
						}
						break;

					default:
						$output = $mode . ': ' . $data . ';';
				}
			}

			$css .= $output;

			return $css;
		}
	}
}

class_alias( 'weLaunch_Image_Select', 'weLaunchFramework_Image_Select' );