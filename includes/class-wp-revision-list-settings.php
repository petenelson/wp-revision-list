<?php

if ( ! defined( 'ABSPATH' ) ) die( 'restricted access' );

if ( ! class_exists( 'WP_Revision_List_Settings' ) ) {

	class WP_Revision_List_Settings {

		static $plugin_name = 'wp-revision-list';

		private $settings_page    = 'wp-revision-list-settings';
		private $settings_key_general  = 'wp-revision-list-settings-general';
		private $plugin_settings_tabs  = array();


		public function plugins_loaded() {
			// admin menus
			add_action( 'admin_init', array( $this, 'admin_init' ) );
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
			add_action( 'admin_notices', array( $this, 'activation_admin_notice' ) );

			add_action( 'init', array( $this, 'test_custom_post_type' ) );

			add_filter( self::$plugin_name . '-setting-is-enabled', array( $this, 'setting_is_enabled' ), 10, 3 );
			add_filter( self::$plugin_name . '-setting-get', array( $this, 'setting_get' ), 10, 3 );
		}


		public function test_custom_post_type() {
			register_post_type( 'wp-rev-cpt', array(
				'label' => 'Test Revision CPT',
				'public' => true,
				'supports' => array( 'title', 'editor', 'revisions' ),
			));
		}


		public function activation_hook() {

			// create default settings
			add_option( $this->settings_key_general, array(
					'number_of_revisions'  => 3,
					'post_types'           =>array( 'post', 'page' ),
					'prefix'               => '* ',
					'suffix'               => ' (Rev)',
				), '', $autoload = 'no' );

			// add an option so we can show the activated admin notice
			add_option( self::$plugin_name . '-plugin-activated', '1' );

		}


		public function activation_admin_notice() {
			if ( '1' === get_option( self::$plugin_name . '-plugin-activated' ) ) { ?>
					<div class="updated">
						<p><?php
				echo sprintf( __( '<strong>Revision List activated!</strong> Please <a href="%s">visit the Settings page</a> to customize your revision list.', 'wp-revision-list' ), admin_url( 'options-general.php?page=wp-revision-list-settings' ) );
				?></p>
					</div>
				<?php
				delete_option( self::$plugin_name . '-plugin-activated' );
			}
		}


		public function deactivation_hook() {
			// placeholder in case we need deactivation code
		}


		public function admin_init() {
			$this->register_general_settings();
			//$this->register_help_tab();
		}


		private function register_general_settings() {
			$key = $this->settings_key_general;
			$this->plugin_settings_tabs[$key] = __( 'General', 'wp-revision-list' );

			register_setting( $key, $key, array( $this, 'sanitize_general_settings') );

			$section = 'general';

			add_settings_section( $section, '', array( $this, 'section_header' ), $key );

			add_settings_field( 'number_of_revisions', __( 'Number of revisions to display', 'wp-revision-list' ), array( $this, 'settings_input' ), $key, $section,
				array( 'key' => $key, 'name' => 'number_of_revisions', 'size' => 2, 'maxlength' => 2 ) );

			add_settings_field( 'prefix', __( 'Prefix title with', 'wp-revision-list' ), array( $this, 'settings_input' ), $key, $section,
				array( 'key' => $key, 'name' => 'prefix', 'size' => 2, 'maxlength' => 20 ) );

			add_settings_field( 'suffix', __( 'Suffix title with', 'wp-revision-list' ), array( $this, 'settings_input' ), $key, $section,
				array( 'key' => $key, 'name' => 'suffix', 'size' => 10, 'maxlength' => 20 ) );

			$items = array();
			foreach( get_post_types( array(), 'objects' ) as $post_type => $data ) {
				if ( post_type_supports( $post_type, 'revisions' ) ) {
					$items[ $post_type ] = $data->labels->name . ' (' . $post_type . ')';
				}
			}


			add_settings_field( 'post_types', __( 'Post Types', 'wp-revision-list' ), array( $this, 'settings_checkbox_list' ), $key, $section,
				array( 'key' => $key, 'name' => 'post_types', 'items' => $items, 'legend' => __( 'Post Types', 'wp-revision-list' ) ) );
		}


		public function sanitize_general_settings( $settings ) {

			$settings['number_of_revisions'] = intval( $settings['number_of_revisions'] );
			if ( $settings['number_of_revisions'] < 0 ) {
				$settings['number_of_revisions'] = 0;
			}

			return $settings;
		}


		public function setting_is_enabled( $enabled, $key, $setting ) {
			return '1' === $this->setting_get( '0', $key, $setting );
		}


		public function setting_get( $value, $key, $setting ) {

			$args = wp_parse_args( get_option( $key ),
				array(
					$setting => $value,
				)
			);

			return $args[$setting];
		}


		public function settings_input( $args ) {

			$args = wp_parse_args( $args,
				array(
					'name' => '',
					'key' => '',
					'maxlength' => 50,
					'size' => 30,
					'after' => '',
				)
			);


			$name = $args['name'];
			$key = $args['key'];
			$size = $args['size'];
			$maxlength = $args['maxlength'];

			$option = get_option( $key );
			$value = isset( $option[$name] ) ? esc_attr( $option[$name] ) : '';

			echo "<div><input id='{$name}' name='{$key}[{$name}]'  type='text' value='" . $value . "' size='{$size}' maxlength='{$maxlength}' /></div>";
			if ( !empty( $args['after'] ) ) {
				echo '<div>' . __( $args['after'], 'wp-revision-list' ) . '</div>';
			}

		}


		public function settings_checkbox_list( $args ) {
			$args = wp_parse_args( $args,
				array(
					'name' => '',
					'key' => '',
					'items' => array(),
					'after' => '',
					'legend' => '',
				)
			);

			$name = $args['name'];
			$key = $args['key'];
			$items = $args['items'];
			$option = get_option( $key );
			$values = isset( $option[$name] ) ? $option[$name] : '';
			if ( ! is_array( $values ) ) {
				$values = array();
			}

			?>
				<fieldset>
					<legend class="screen-reader-text">
						<?php echo esc_html( $args['legend'] ) ?>
					</legend>

			<?php
			foreach ( $items as $post_type => $post_type_dispay ) {
				?>
					<label>
						<input type="checkbox" name="<?php echo $key ?>[<?php echo $name ?>][]" value="<?php echo $post_type ?>"<?php echo in_array( $post_type, $values) ? ' checked="checked"' : ''  ?> />
						<?php echo esc_html( $post_type_dispay ); ?>
					</label>
					<br/>
				<?php
			}
			?>
				</fieldset>
			<?php

		}


		public function settings_textarea( $args ) {

			$args = wp_parse_args( $args,
				array(
					'name' => '',
					'key' => '',
					'rows' => 10,
					'cols' => 40,
					'after' => '',
				)
			);


			$name = $args['name'];
			$key = $args['key'];
			$rows = $args['rows'];
			$cols = $args['cols'];

			$option = get_option( $key );
			$value = isset( $option[$name] ) ? esc_attr( $option[$name] ) : '';

			echo "<div><textarea id='{$name}' name='{$key}[{$name}]' rows='{$rows}' cols='{$cols}'>" . $value . "</textarea></div>";
			if ( !empty( $args['after'] ) ) {
				echo '<div>' . $args['after'] . '</div>';
			}

		}


		public function settings_yes_no( $args ) {

			$args = wp_parse_args( $args,
				array(
					'name' => '',
					'key' => '',
					'after' => '',
				)
			);

			$name = $args['name'];
			$key = $args['key'];

			$option = get_option( $key );
			$value = isset( $option[$name] ) ? esc_attr( $option[$name] ) : '';

			if ( empty( $value ) )
				$value = '0';

			echo '<div>';
			echo "<label><input id='{$name}_1' name='{$key}[{$name}]'  type='radio' value='1' " . ( '1' === $value ? " checked=\"checked\"" : "" ) . "/>" . __( 'Yes', 'wp-revision-list' ) . "</label> ";
			echo "<label><input id='{$name}_0' name='{$key}[{$name}]'  type='radio' value='0' " . ( '0' === $value ? " checked=\"checked\"" : "" ) . "/>" . __( 'No', 'wp-revision-list' ) . "</label> ";
			echo '</div>';

			if ( !empty( $args['after'] ) )
				echo '<div>' . __( $args['after'], 'wp-revision-list' ) . '</div>';
		}


		public function admin_menu() {
			add_options_page( __( 'WP Revision List Settings', 'wp-revision-list' ), __( 'WP Revision List', 'wp-revision-list' ), 'manage_options', $this->settings_page, array( $this, 'options_page' ), 30 );
		}


		public function options_page() {

			$tab = $this->current_tab(); ?>
			<div class="wrap">
				<?php $this->plugin_options_tabs(); ?>
				<form method="post" action="options.php" class="options-form">
					<?php settings_fields( $tab ); ?>
					<?php do_settings_sections( $tab ); ?>
					<?php
						if ( $this->settings_key_help !== $tab ) {
							submit_button( __( 'Save Settings', 'wp-revision-list' ), 'primary', 'submit', true );
						}
					?>
				</form>
			</div>
			<?php

			//$settings_updated = filter_input( INPUT_GET, 'settings-updated', FILTER_SANITIZE_STRING );
			//if ( ! empty( $settings_updated ) ) {
				//flush_rewrite_rules( );
			//}

		}


		private function current_tab() {
			$current_tab = filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_STRING );
			return empty( $current_tab ) ? $this->settings_key_general : $current_tab;
		}


		private function plugin_options_tabs() {
			$current_tab = $this->current_tab();
			echo '<h2>' . __( 'WP Revision List Settings', 'wp-revision-list' ) . '</h2><h2 class="nav-tab-wrapper">';
			foreach ( $this->plugin_settings_tabs as $tab_key => $tab_caption ) {
				$active = $current_tab == $tab_key ? 'nav-tab-active' : '';
				echo '<a class="nav-tab ' . $active . '" href="?page=' . $this->settings_page . '&tab=' . $tab_key . '">' . $tab_caption . '</a>';
			}
			echo '</h2>';
		}


		public function section_header( $args ) {

			switch ( $args['id'] ) {
			case 'help';
				include_once 'admin-help.php';
				break;
			}

			if ( !empty( $output ) ) {
				echo '<p class="settings-section-header">' . $output . '</p>';
			}

		}


	} // end class

}