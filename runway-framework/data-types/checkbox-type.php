<?php

class Checkbox_type extends Data_Type {

	public static $type_slug = 'checkbox-type';

	public function __construct( $page, $field, $wp_customize = null, $alias = null, $params = null ) {

		$this->type  = 'checkbox-type';
		$this->label = 'Checkbox List';

		parent::__construct( $page, $field, $wp_customize, $alias, $params );

	}

	public function render_content( $vals = null ) {

		do_action( self::$type_slug . '_before_render_content', $this );

		if ( $vals != null ) {
			$this->field         = (object) $vals;
			$this->field->values = preg_replace( "/\\r\\n|\\n|\\r/", "\r\n", $this->field->values );
		}

		$key_values = array();

		if ( isset( $this->field->values ) && ! empty( $this->field->values ) ) {

			if ( false !== strpos( $this->field->values, "\r\n" ) ) {
				$rows = explode( "\r\n", $this->field->values );
			} else {
				$rows = explode( "\\r\\n", $this->field->values );
			}

			foreach ( $rows as $v ) {
				if ( $v != '' ) {
					$v                   = htmlspecialchars_decode( $v );
					$this->field->values = explode( '=>', $v );
					if ( count( $this->field->values ) == 1 ) {
						$key                = str_replace( ' ', '-', trim( strtolower( $this->field->values[0] ) ) );
						$key_values[ $key ] = $this->field->values[0];
					} else {
						$key                = str_replace( ' ', '-', trim( strtolower( $this->field->values[0] ) ) );
						$key_values[ $key ] = $this->field->values[1];
					}
				}
			}

		}

		$options = array();
		$checked_list = array();

		$this->field->values = ( $vals != null ) ? $this->field->saved : (array) $this->get_value();
		$section             = ( isset( $this->page->section ) && $this->page->section != '' ) ? 'data-section="' . esc_attr( $this->page->section ) . '"' : '';

		if ( isset( $this->field->value ) && isset( $this->field->value[ $this->field->alias ] ) && in_array( 'field_types', (array) $this->field->value ) ) {
			$this->field->value = $this->field->value[ $this->field->alias ];
		}

		if ( empty( $this->field->values[0] ) ) {
			$this->field->values = $checked_list;
		}

		if ( isset( $this->field->values[0] ) && $this->field->values[0] == 'Array' ) {
			$this->field->values = array( 0 => $options['values'] );
		}

		if ( isset( $this->field->repeating ) && $this->field->repeating == 'Yes' ) {
			?>

			<legend class="customize-control-title">
				<span><?php echo stripslashes( $this->field->title ); ?></span>
			</legend>

			<?php

			$this->get_value();
			if ( isset( $this->field->value ) && is_array( $this->field->value ) ) {
				foreach ( $this->field->value as $key => $tmp_value ) {
					if ( is_string( $key ) || ! is_array( $tmp_value ) ) {
						unset( $this->field->value[ $key ] );
					}
				}
			} else if ( ! is_array( $this->field->value ) && is_string( $this->field->value ) ) {
				$tmp_arr            = array();
				$tmp_arr[]          = $this->field->value;
				$this->field->value = $tmp_arr;
			}

			$count = isset( $this->field->value ) ? count( (array) $this->field->value ) : 1;
			if ( $count == 0 ) {
				$count = 1;
			}

			$key_values = apply_filters( $this->field->alias . '_data_options', $key_values );

			$len = count( $key_values );

			for ( $key_val = 0; $key_val < $count; $key_val++ ) {
				$cnt  = 0;
				$html = '<div class="checkbox_group">';
				foreach ( $key_values as $key => $val ) {
					$cnt++;
					if ( ! isset( $options[ $key ] ) ) {
						$options[ $key ] = array( 'class' => '', 'disabled' => '' );
					}
					
					$readonly = $options[ $key ]['disabled'] ? ' disabled="disabled"' : '';

					if ( isset( $this->field->values[ $key_val ] ) && is_array( $this->field->values[ $key_val ] ) ) {
						$checked = in_array( $key, $this->field->values[ $key_val ] ) ? ' checked="checked"' : '';
					} else {
						$checked = '';
					}

					$html .= '<label>
							<input
							class="input-check custom-data-type"
							' . $section . '
							type="checkbox"
							data-type="checkbox-type"
							value="' . esc_attr( $key ) . '"
							name="' . esc_attr( $this->field->alias ) . '[' . esc_attr( $key_val ) . '][]" ' .
					        ( isset( $this->field->class ) ? $this->field->class : '' ) . ' ' .
					        $readonly . ' ' . $checked . '/>' . stripslashes( $val ) . '</label><br/>';
				}

				echo rf_string( $html ) . '</div>';
				?>

				<a href="#" class="delete__checkbox_group_field"><?php echo __( 'Delete', 'runway' ); ?></a><br><br>

				<?php
			}

			$field = array(
				'field_name'   => $this->field->alias,
				'start_number' => $count,
				'type'         => 'checkbox',
				'class'        => 'input-check custom-data-type',
				'data_section' => isset( $this->page->section ) ? $this->page->section : '',
				'data_type'    => 'checkbox-type',
				'after_field'  => '',
				'value'        => '#'
			);
			$this->enable_repeating( $field, $key_values );
			$this->wp_customize_js();

		} else {
			$html  = '';
			$len   = count( $key_values );
			$count = 0;

			$key_values = apply_filters( $this->field->alias . '_data_options', $key_values ); // allow filters to alter values

			foreach ( $key_values as $key => $val ) {
				$count++;

				// Options will over-ride values
				if ( ! isset( $options[ $key ] ) ) {
					$options[ $key ] = array( 'class' => '', 'disabled' => '' );
				}
				
				$readonly = $options[ $key ]['disabled'] ? ' disabled="disabled"' : '';

				if ( array_key_exists( 'value', (array) $options[ $key ] ) ) {
					$checked = $options[ $key ]['value'] ? ' checked="checked" ' : '';
				} elseif ( is_array( $this->field->values ) ) {
					$checked = in_array( $key, $this->field->values ) ? ' checked="checked"' : '';
				}

				if ( ! isset( $this->field->class ) ) {
					$this->field->class = '';
				}
				$section = ( isset($this->page->section) && $this->page->section != '' ) ? 'data-section="' . esc_attr($this->page->section) . '"' : '';
				$html .= '<label>
						<input
						class="input-check custom-data-type" ' .
				        parent::add_data_conditional_display( $this->field ) . ' ' .
				        $section . '
						type="checkbox"
						data-type="checkbox-type"
						value="' . esc_attr( $key ) . '"
						name="' . esc_attr( $this->field->alias ) . '[]" ' . ( isset( $this->field->class ) ? $this->field->class : '' ) . ' ' .
				        $readonly . ' ' . $checked . '/>' . stripslashes( $val ) . '</label>';

				if ( isset( $options[ $key ]['text'] ) ) {
					$t = $options[ $key ]['text'];
					$html .= '<em>' . $t . '</em>';
				}

				if ( $count < $len ) {
					$html .= '<br>';
				}
			}

			// Add the fieldset container
			$html = '<legend class="customize-control-title"><span>' .
			        wp_kses_post( $this->field->title ) .
			        '</span></legend><fieldset><legend class="screen-reader-text"><span>' .
			        wp_kses_post( $this->field->title ) . '</span></legend>' . $html . '</fieldset>';

			echo rf_string( $html );

			/* dirty hack to make multiple elms on customize.php page */
			if ( $this->is_customize_theme_page ) {
				?>
				<input type="hidden" <?php $this->link(); ?> name="<?php echo esc_attr( $this->field->alias ) ?>" value=""/>

				<script type="text/javascript">

					jQuery('input[name="<?php echo esc_js( $this->field->alias );?>[]"]').on('click', function () {
						var value = [];

						jQuery('[name="<?php echo esc_js( $this->field->alias ); ?>[]"]:checked').each(function () {
							value.push(jQuery(this).val());
						});

						jQuery('[name="<?php echo esc_js( $this->field->alias );?>"]').val(value).trigger('change');

					});

				</script>

				<?php
			}
			/* dirty hack to make multiple elms on customize.php page */
		}

		do_action( self::$type_slug . '_after_render_content', $this );

	}

	public function save( $value = null ) {

		/* dirty hack to make multiple elms on customize.php page */
		$submited_value = json_decode( stripslashes( $_REQUEST['customized'] ) );
		$value          = $submited_value->{$this->field->alias};

		if ( is_string( $value ) ) {
			$value = explode( ',', $value );
		}

		if ( is_object( $value ) ) {
			$value = '';
		}

		SingletonSaveCusomizeData::getInstance()->set_option( $this->page->option_key );
		SingletonSaveCusomizeData::getInstance()->save_data( $this->field->alias, $value, $this->type );

	}

	public function sanitize_value( $value ) {

		if ( is_string( $value ) ) {
			$value = explode( ',', $value );
		}

		if ( is_object( $value ) ) {
			$value = '';
		}

		if ( is_array( $value ) ) {
			$first = reset($value);
			if ( isset( $this->field->repeating ) && $this->field->repeating == 'Yes' ) {
				if ( is_array( $first ) && count( $value ) === 1 ) {
					$value = $first;
				}
			} else {
				if ( count( $value ) === 1 ) {
					$value = $first;
				}
			}
		}

		return $value;

	}

	public static function render_settings() {
		?>

		<script id="checkbox-type" type="text/x-jquery-tmpl">

		<?php do_action( self::$type_slug . '_before_render_settings' ); ?>

		    <div class="settings-container">
		        <label class="settings-title">
		            <?php echo __( 'Values', 'runway' ); ?>:
		            <br><span class="settings-title-caption"></span>
		        </label>
		        <div class="settings-in">

		            <textarea data-set="values" name="values" class="settings-textarea checkbox-type" id="settings-values">${values}</textarea>

		        </div>
		        <div class="clear"></div>
		    </div>

		    <div class="settings-container">
		        <label class="settings-title">
		            <?php echo __( 'Required', 'runway' ); ?>:
		            <br><span class="settings-title-caption"></span>
		        </label>
		        <div class="settings-in">

		            <label>
		                {{if required == 'true'}}
		                    <input data-set="required" name="required" value="true" checked="true" type="checkbox">
		                {{else}}
		                    <input data-set="required" name="required" value="true" type="checkbox">
		                {{/if}}
		                <?php echo __( 'Yes', 'runway' ); ?>
		            </label>

		            <span class="settings-field-caption"><?php echo __( 'Is this a required field?', 'runway' ); ?></span><br>

		            <input data-set="requiredMessage" name="requiredMessage" value="${requiredMessage}" type="text">

		            <span class="settings-field-caption"><?php echo __( 'Optional. Enter a custom error message.', 'runway' ); ?></span>

		        </div>
		        <div class="clear"></div>
		    </div>

		    <!-- Repeating settings -->
		    <div class="settings-container">
		        <label class="settings-title">
		            <?php echo __( 'Repeating', 'runway' ); ?>:
		        </label>
		        <div class="settings-in">
		            <label>
		                {{if repeating == 'Yes'}}
		                    <input data-set="repeating" name="repeating" value="Yes" checked="true" type="checkbox">
		                {{else}}
		                    <input data-set="repeating" name="repeating" value="Yes" type="checkbox">
		                {{/if}}
		                <?php echo __( 'Yes', 'runway' ); ?>
		            </label>
		            <span class="settings-field-caption"><?php echo __( 'Can this field repeat with multiple values?', 'runway' ); ?></span>
		        </div>
		        <div class="clear"></div>
		    </div>

		<?php
		parent::render_conditional_display();
        do_action( self::$type_slug . '_after_render_settings' );
		?>

		</script>

		<?php
	}

	public static function data_type_register() {
		?>

		<script type="text/javascript">

			jQuery(document).ready(function ($) {
				builder.registerDataType({
					name: '<?php echo __( 'Checkbox List', 'runway' ); ?>',
					alias: '<?php echo self::$type_slug ?>',
					settingsFormTemplateID: '<?php echo self::$type_slug ?>'
				});

				function render_values(selector) {
					var str_render = new String($(selector).val());
					var result_string = new Array();
					str_render = str_render.split('\n');
					for (var key in str_render) {
						if (str_render[key] != '') {
							str_render[key] = new String(str_render[key]);
							str_render[key] = str_render[key].split('=>');
							if (str_render[key].length == 1) {
								result_string.push($.trim(str_render[key][0]).toLowerCase().split(' ').join('-') + '=>' + $.trim(str_render[key][0]));
							}
							else if (str_render[key].length == 2) {
								if (str_render[key][1] != '') {
									result_string.push($.trim(str_render[key][0]).toLowerCase().split(' ').join('-') + '=>' + $.trim(str_render[key][1]));
								}
								else {
									result_string.push($.trim(str_render[key][0]).toLowerCase().split(' ').join('-') + '=>' + $.trim(str_render[key][0]));
								}
							}
						}
					}
					$(selector).val(result_string.join('\n') + '\n');
				}

				$('body').on('keyup', '.checkbox-type', function (e) {
					if (e.keyCode == 13) {
						render_values(this);
					}
				});

				$('body').on('blur', '.checkbox-type', function (e) {
					render_values(this);
				});
			});

		</script>

		<?php
	}

	public function enable_repeating( $field = array(), $default_values = array() ) {
		if ( ! empty( $field ) ) {
			extract( $field );

			$add_id = 'add_' . $field_name;
			?>

			<div id="<?php echo esc_attr( $add_id ); ?>">
				<a href="#"><?php echo __( 'Add Field', 'runway' ); ?></a>
			</div>

			<script type="text/javascript">
				(function ($) {
					$(document).ready(function () {
						var field = $.parseJSON('<?php echo json_encode( $field ); ?>');
						var start_radio_groups_index = <?php echo esc_js( $start_number ); ?>;
						var $container = $('#<?php echo esc_js( $add_id ); ?>').parent();

						$('#<?php echo esc_js( $add_id ); ?>').click(function (e) {
							e.preventDefault();
							var field = $('<div class="checkbox_group">');

							<?php foreach ($default_values as $val_key => $val) { ?>

							var child_field = $('<label>' +
								'<input class="<?php echo esc_attr( $class ); ?>" data-type="<?php echo esc_attr( $data_type ); ?>" data-section="<?php echo esc_attr( $data_section ); ?>" type="<?php echo esc_attr( $type ); ?>" name="<?php echo esc_attr( $field_name ); ?>[' + start_radio_groups_index + '][]" value="<?php echo esc_attr( $val_key ); ?>"/>' +
								'<?php echo rf_string( $val ); ?> ' +
								'</label><br/>');

							field.append(child_field);
							<?php } ?>
							start_radio_groups_index++;

							field.insertBefore($(this));

							$('#header').focus();
							field.after('<br><br>');
							field.after('<span class="field_label"> <?php echo rf_string( $after_field ); ?> </span>');
							field.next().after('<a href="#" class="delete__checkbox_group_field"><?php echo __( 'Delete', 'runway' ); ?></a>');

							if (typeof reinitialize_customize_checkbox_list_instance == 'function') {
								reinitialize_customize_checkbox_list_instance('<?php echo esc_js( $field_name ); ?>');
							}
						});

						$('body').on('click', '.delete__checkbox_group_field', function (e) {
							var $this = $(this);

							e.preventDefault();

							$this.prev('.field_label').remove();
							$this.prev('.checkbox_group').remove();
							$this.next('br').remove();
							$this.next('br').remove();
							$this.remove();

							if (typeof reinitialize_customize_checkbox_list_instance == 'function') {
								reinitialize_customize_checkbox_list_instance('<?php echo esc_js( $field_name ); ?>');
							}
						});

						if (wp.customize) {
							if (typeof reinitialize_customize_checkbox_list_instance == 'function') {
								var api = wp.customize;
								api.bind('ready', function () {
									reinitialize_customize_checkbox_list_instance('<?php echo esc_js( $field_name ); ?>');
								});
							}
						}

						setTimeout(function() {
							if (typeof check_inputs_amount === 'function') {
								check_inputs_amount($container);
							}
						}, 0);
					});
				})(jQuery);
			</script>
			<?php
		}
	}

	public function wp_customize_js() {
		?>

		<script type="text/javascript">
			(function ($) {
				$('body').on('click', 'input[name^="<?php echo esc_js( $this->field->alias ); ?>"]', function () {
					reinitialize_customize_checkbox_list_instance('<?php echo esc_js( $this->field->alias ); ?>');
				});
			})(jQuery);

			if (typeof reinitialize_customize_checkbox_list_instance !== 'function') {
				function reinitialize_customize_checkbox_list_instance(alias) {
					(function ($) {
						if (wp.customize) {
							var values_array = [];
							var current_index = -1;

							alias = alias.replace(/(\[\d*\])?\[\d*\]$/, '');
							$('input[name^="' + alias + '"]').each(function () {
								var matched = $(this).attr('name').match(/\[(\d*)\]\[\]$/);

								if (current_index != parseInt(matched[1], 10)) {
									values_array.push([]);
									current_index = parseInt(matched[1], 10);
								}

								if ($('input[name="' + $(this).attr('name') + '"]:checked').length === 0) {
									values_array[values_array.length - 1] = [];
									values_array[values_array.length - 1].push('');
								} else if ($(this).prop('checked')) {
									values_array[values_array.length - 1].push($(this).val());
								}
							});

							var api = wp.customize;
							api.instance(alias).set(values_array);
						}
					})(jQuery);
				}
			}
		</script>

		<?php
	}

}
