<?php

class Code_editor_type extends Data_Type {

	public static $type_slug = 'code-editor-type';

	public function __construct( $page, $field, $wp_customize = null, $alias = null, $params = null ) {

		$this->type  = 'code-editor-type';
		$this->label = 'Code editor';

		parent::__construct( $page, $field, $wp_customize, $alias, $params );

	}

	public function render_content( $vals = null ) {

		do_action( self::$type_slug . '_before_render_content', $this );

		if ( $vals != null ) {
			$this->field = (object) $vals;
		}

		$value           = ( $vals != null ) ? $this->field->saved : $this->get_value();
		$section         = ( isset( $this->page->section ) && $this->page->section != '' ) ? 'data-section="' . esc_attr( $this->page->section ) . '"' : '';
		$customize_title = stripslashes( $this->field->title );
		?>

		<legend class='customize-control-title'>
			<span><?php echo wp_kses_post( $customize_title ); ?></span>
		</legend>

		<?php
		$escaped_attr_alias = esc_attr( $this->field->alias );
		$escaped_js_alias   = esc_js( $this->field->alias );

		if ( isset( $this->field->editorType ) && $this->field->editorType === 'ace' ) {
			?>

			<div id="<?php echo $escaped_attr_alias; ?>" class="code-editor"
			     style="<?php echo isset( $this->field->editorWidth ) ? 'width: ' . esc_attr( $this->field->editorWidth ) . 'px; ' : ' '; ?>
			     <?php echo isset( $this->field->editorHeight ) ? 'height: ' . esc_attr( $this->field->editorHeight ) . 'px; ' : ''; ?>"><?php echo is_string( $value ) ? $value : ''; ?>
			</div>

			<input type="hidden"
			       class="code-editor<?php echo ' ' . esc_attr( $this->field->cssClass ); ?> custom-data-type ace_editor"
			       name="<?php echo $escaped_attr_alias; ?>"
				   <?php $this->link(); ?>
				   id="hidden-<?php echo $escaped_attr_alias; ?>"
				   <?php echo rf_string( $section ); ?>
				   value="<?php echo is_string( $value ) ? $value : ''; ?>"
				   data-type='code-editor'/>

			<script>
				jQuery(document).ready(function ($) {
					var editor = ace.edit("<?php echo $escaped_js_alias; ?>");

					<?php
					if ( isset( $this->field->enableVim ) && ( $this->field->enableVim === 'true' || $this->field->enableVim === true ) ) {
					?>
					editor.setKeyboardHandler('ace/keyboard/vim');
					<?php } ?>

					editor.setTheme("ace/theme/chrome");
					editor.getSession().setMode("ace/mode/<?php echo isset( $this->field->editorLanguage ) ? strtolower( $this->field->editorLanguage ) : 'javascript'; ?>");
					editor.getSession().on('change', function (e) {
						var editor = ace.edit("<?php echo $escaped_js_alias; ?>");
						var code = editor.getSession().getValue();

						if (wp.customize) {
							var alias = "<?php echo $escaped_js_alias; ?>";
							var api = wp.customize;
							api.instance(alias).set($('#hidden-<?php echo $escaped_js_alias; ?>').val());
						}

						jQuery('#hidden-<?php echo $escaped_js_alias; ?>').val(code).trigger('change');
					});

					editor.setShowPrintMargin(false);
				});
			</script>

			<?php
		} else { ?>

			<textarea id="<?php echo $escaped_attr_alias; ?>"
			          class="code-editor<?php echo ' ' . esc_attr( $this->field->cssClass ); ?> custom-data-type"
				      <?php $this->link(); ?>
				      name="<?php echo $escaped_attr_alias; ?>"
				      <?php echo rf_string( $section ); ?>
				      data-type='code-editor' <?php echo parent::add_data_conditional_display( $this->field ); ?> ><?php echo is_string( $value ) ? $value : ''; ?></textarea>

		<?php }

		do_action( self::$type_slug . '_after_render_content', $this );

	}

	public static function assign_actions_and_filters() {

		add_filter( 'get_options_data_type_' . self::$type_slug, array( 'Code_editor_type', 'code_editor_filter' ), 5, 10 );
		add_action( 'admin_print_scripts', array( 'Code_editor_type', 'include_ace' ) );
		add_action( 'customize_register', array( 'Code_editor_type', 'include_ace' ) );

	}

	public static function include_ace() {

		$data_type_directory   = __DIR__;
		$framework_dir         = basename( FRAMEWORK_DIR );
		$framework_pos         = strlen( $data_type_directory ) - strlen( $framework_dir ) - strrpos( $data_type_directory, $framework_dir ) - 1;
		$current_data_type_dir = str_replace( '\\', '/', substr( $data_type_directory, - $framework_pos ) );

		wp_register_script( 'ace', FRAMEWORK_URL . $current_data_type_dir . '/js/ace/src-noconflict/ace.js' );

	}

	public static function code_editor_filter( $val ) {

		$val = stripslashes( $val );
		$val = htmlspecialchars_decode( $val, ENT_QUOTES );

		return $val;

	}

	public function get_value() {

		$value = parent::get_value();

		if ( is_string( $value ) ) {  // because string is array always
			return $value;
		} else {
			return isset( $this->field->values ) ? $this->field->values : '';
		}

	}

	public static function render_settings() {
		?>

		<script id="code-editor-type" type="text/x-jquery-tmpl">

		    <?php do_action( self::$type_slug . '_before_render_settings' ); ?>

			<div class="settings-container">
			    <label class="settings-title">
					<?php echo __( 'Values', 'runway' ); ?>:
					<br><span class="settings-title-caption"></span>
			    </label>
			    <div class="settings-in">

					<textarea data-set="values" name="values" class="settings-textarea">${values}</textarea>

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

			<div class="settings-container">
			    <label class="settings-title">
					<?php echo __( 'CSS Class', 'runway' ); ?>:
					<br><span class="settings-title-caption"></span>
			    </label>
			    <div class="settings-in">

					<input data-set="cssClass" name="cssClass" value="${cssClass}" class="settings-input" type="text">

			    </div>
			    <div class="clear"></div>

			</div>

			<div class="settings-container">
			    <label class="settings-title">
					<?php echo __( 'Width', 'runway' ); ?>:
					<br><span class="settings-title-caption"></span>
			    </label>
			    <div class="settings-in">

				<input data-set="editorWidth" name="editorWidth" value="${editorWidth}" class="settings-input" type="text">

			    </div>
			    <div class="clear"></div>

			</div>

			<div class="settings-container">
			    <label class="settings-title">
					<?php echo __( 'Height', 'runway' ); ?>:
					<br><span class="settings-title-caption"></span>
			    </label>
			    <div class="settings-in">

				<input data-set="editorHeight" name="editorHeight" value="${editorHeight}" class="settings-input" type="text">

			    </div>
			    <div class="clear"></div>

			</div>

			<div class="settings-container">
			    <label class="settings-title">
					<?php echo __( 'Code editor type', 'runway' ); ?>:
					<br><span class="settings-title-caption"></span>
			    </label>
			    <div class="settings-in">

				<select name="editorType">
					<option {{if editorType == "default"}} selected="true" {{/if}} value="default"><?php echo __( 'Default', 'runway' ); ?></option>
					<option {{if editorType == "ace"}} selected="true" {{/if}} value="ace"><?php echo __( 'ACE', 'runway' ); ?></option>
				</select>

			    </div>
			    <div class="clear"></div>
			</div>

			<div class="settings-container">
			    <label class="settings-title">
					<?php echo __( 'Editor language', 'runway' ); ?>:
				<br><span class="settings-title-caption"></span>
			    </label>
			    <div class="settings-in">

				<select name="editorLanguage">
					<option {{if editorLanguage == "javascript"}} selected="true" {{/if}} value="javascript">JavaScript</option>
					<option {{if editorLanguage == "css"}} selected="true" {{/if}} value="css">CSS</option>
				</select>

			    </div>
			    <div class="clear"></div>
			</div>

			<div class="settings-container">
			    <label class="settings-title">
				<?php echo __( 'Enable Vim keys', 'runway' ); ?>:
				<br><span class="settings-title-caption"></span>
			    </label>
			    <div class="settings-in">

				<label>
				    {{if enableVim == 'true'}}
				    <input data-set="enableVim" name="enableVim" value="true" checked="true" type="checkbox">
				    {{else}}
				    <input data-set="enableVim" name="enableVim" value="true" type="checkbox">
				    {{/if}}
				    <?php echo __( 'Yes', 'runway' ); ?>
				</label>

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
					name: '<?php echo __( 'Code editor', 'runway' ); ?>',
					separate: 'none',
					alias: '<?php echo self::$type_slug ?>',
					settingsFormTemplateID: '<?php echo self::$type_slug ?>'
				});
			});

		</script>

		<?php
	}

	public function wp_customize_js() {
	}

}
