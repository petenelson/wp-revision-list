<?php

if ( ! defined( 'ABSPATH' ) ) die( 'restricted access' );

if ( ! class_exists( 'WP_Revision_List_Settings' ) ) {

	class WP_Revision_List_Settings {

		private $settings_page         = 'wp-revision-list-settings';
		private $settings_key_general  = 'wp-revision-list-settings-general';
		private $settings_key_help     = 'wp-revision-list-settings-help';
		private $plugin_settings_tabs  = array();


		public function plugins_loaded() {
			// admin menus
			add_action( 'admin_init', array( $this, 'admin_init' ) );
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
			add_action( 'admin_notices', array( $this, 'activation_admin_notice' ) );

			add_filter( WP_Revision_List_Core::$plugin_name . '-setting-is-enabled', array( $this, 'setting_is_enabled' ), 10, 3 );
			add_filter( WP_Revision_List_Core::$plugin_name . '-setting-get', array( $this, 'setting_get' ), 10, 3 );
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
			add_option( WP_Revision_List_Core::$plugin_name . '-plugin-activated', '1' );

		}


		public function activation_admin_notice() {
			if ( '1' === get_option( WP_Revision_List_Core::$plugin_name . '-plugin-activated' ) ) { ?>
					<div class="updated">
						<p><?php
				echo sprintf( __( '<strong>Revision List activated!</strong> Please <a href="%s">visit the Settings page</a> to customize your revision list.', 'wp-revision-list' ), admin_url( 'options-general.php?page=wp-revision-list-settings' ) );
				?></p>
					</div>
				<?php
				delete_option( WP_Revision_List_Core::$plugin_name . '-plugin-activated' );
			}
		}


		public function deactivation_hook() {
			// placeholder in case we need deactivation code
		}


		public function admin_init() {
			$this->register_general_settings();
			$this->register_help_tab();
		}


		private function register_general_settings() {
			$key = $this->settings_key_general;
			$this->plugin_settings_tabs[$key] = __( 'General', 'wp-revision-list' );

			register_setting( $key, $key, array( $this, 'sanitize_general_settings') );

			$section = 'general';

			add_settings_section( $section, '', array( $this, 'section_header' ), $key );

			add_settings_field( 'number_of_revisions', __( 'Default number of revisions to display', 'wp-revision-list' ), array( $this, 'settings_input' ), $key, $section,
				array( 'key' => $key, 'name' => 'number_of_revisions', 'size' => 2, 'maxlength' => 2, 'min' => 0, 'max' => 99, 'type' => 'number', 'after' => __( 'Users can chose their own setting in Screen Options', 'wp-revision-list' ) ) );

			add_settings_field( 'prefix', __( 'Prefix title with', 'wp-revision-list' ), array( $this, 'settings_input' ), $key, $section,
				array( 'key' => $key, 'name' => 'prefix', 'size' => 2, 'maxlength' => 20 ) );

			add_settings_field( 'suffix', __( 'Suffix title with', 'wp-revision-list' ), array( $this, 'settings_input' ), $key, $section,
				array( 'key' => $key, 'name' => 'suffix', 'size' => 10, 'maxlength' => 20 ) );

			$items = array();
			foreach( get_post_types( array( 'public' => true ), 'objects' ) as $post_type => $data ) {
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


		private function register_help_tab() {
			$key = $this->settings_key_help;
			$this->plugin_settings_tabs[$key] =  __( 'Help' );
			register_setting( $key, $key );
			$section = 'help';
			add_settings_section( $section, '', array( $this, 'section_header' ), $key );
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

		/**
		 * Display a settings input field.
		 *
		 * @param  array $args List of args.
		 * @return void
		 */
		public function settings_input( $args ) {

			$args = wp_parse_args( $args,
				array(
					'name' => '',
					'key' => '',
					'maxlength' => 50,
					'size' => 30,
					'after' => '',
					'type' => 'text',
					'min' => 0,
					'max' => 0,
					'step' => 1,
				)
			);

			$key = $args['key'];
			$name = $args['name'];
			$type = $args['type'];
			$size = $args['size'];
			$maxlength = $args['maxlength'];

			$option = get_option( $key );
			$value = isset( $option[$name] ) ? esc_attr( $option[$name] ) : '';

			$min_max_step = '';
			if ( $type === 'number' ) {
				$min = absint( $args['min'] );
				$max = absint( $args['max'] );
				$step = absint( $args['step'] );
				$min_max_step = " step='{$step}' min='{$min}' max='{$max}' ";
			}

			?>
				<div>
					<input
						id="<?php echo esc_attr( $name ); ?>"
						name="<?php echo esc_attr( "{$key}[{$name}]" ); ?>"
						type="<?php echo esc_attr( $type ); ?>"
						value="<?php echo esc_attr( $value ); ?>"
						size="<?php echo esc_attr( $size ); ?>"
						maxlength="<?php echo esc_attr( $maxlength ); ?>"
						<?php echo $min_max_step ?> />
				</div>
			<?php

			$this->output_after( $args['after'] );
		}


		public function settings_checkbox_list( $args ) {
			extract( wp_parse_args( $args,
				array(
					'name' => '',
					'key' => '',
					'items' => array(),
					'after' => '',
					'legend' => '',
				)
			) );

			$option = get_option( $key );
			$values = isset( $option[$name] ) ? $option[$name] : '';
			if ( ! is_array( $values ) ) {
				$values = array();
			}

			?>
				<fieldset>
					<legend class="screen-reader-text">
						<?php echo esc_html( $legend ) ?>
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

		private function output_after( $after ) {
			if ( !empty( $after ) ) {
				echo '<p class="description">' . wp_kses_post( $after ) . '</p>';
			}
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
					include_once 'partials/admin-help.php';
					break;
			}

			if ( ! empty( $output ) ) {
				echo '<p class="settings-section-header">' . wp_kses_post( $output ) . '</p>';
			}

		}


	} // end class

}