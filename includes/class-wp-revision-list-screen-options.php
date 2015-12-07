<?php

if (!defined( 'ABSPATH' )) exit('restricted access');

if (!class_exists('WP_Revision_List_Screen_Options')) {

	class WP_Revision_List_Screen_Options {


		public function plugins_loaded() {

			// outputs custom screen options fields
			add_filter( 'screen_settings', array( $this, 'screen_settings' ), 10, 2 );

			// because the built-in misc/set_screen_options() does a redirect after it
			// saves the _per_page option, this action appears to be the only way to hook
			// into saving custom screen options
			add_action( 'check_admin_referer', array( $this, 'save_user_meta' ), 10, 2 );

		}


		public function screen_settings( $screen_settings, $screen ) {

			// add a field to screen options
			if ( apply_filters( WP_Revision_List_Core::$plugin_name . '-is-revision-list-screen', false, $screen ) ) {

				if ( ! $user = wp_get_current_user() ) {
					return;
				}

				$number_of_revisions = apply_filters( WP_Revision_List_Core::$plugin_name . '-user-number-of-revisions', 3, $screen->post_type );

				ob_start();
				?>
				<div class="screen-options">
					<fieldset>
						<label for="wp_rev_list_number_of_revisions_screen_option"><?php _e( 'Number of revisions to display:', 'wp-revision-list' ); ?></label>
							<input type="number" step="1" min="0" max="20" name="wp_rev_list_number_of_revisions_screen_option" id="wp_rev_list_number_of_revisions_screen_option" maxlength="2" value="<?php echo esc_attr( $number_of_revisions ); ?>" />
							<input type="hidden" name="wp_rev_list_post_type_screen_option" id="wp_rev_list_post_type_screen_option" value="<?php echo esc_attr( $screen->post_type ); ?>" />
						</label>
					</fieldset>
				</div>
				<?php
				$screen_settings = ob_get_contents();
				ob_end_clean();

			}

			return $screen_settings;
		}


		public function save_user_meta( $action, $result ) {

			// see if this is coming from a screen option 'Apply'
			// the first step in misc/set_screen_options() is check_admin_referer( 'screen-options-nonce', 'screenoptionnonce' );

			$post_type = sanitize_key( filter_input( INPUT_POST, 'wp_rev_list_post_type_screen_option', FILTER_SANITIZE_STRING ) );

			if ( $action === 'screen-options-nonce' && $result === 1 && ! empty( $post_type ) ) {

				// yes, we're saving a screen option

				// prevent a loop
				remove_action( 'check_admin_referer', array( $this, 'save_user_meta'), 10 );

				// the action and result check above came from do_action call at the end of check_admin_referer() above,
				// so the nonce should already be valid, but we'll double-check
				check_admin_referer( 'screen-options-nonce', 'screenoptionnonce' );

				if ( ! $user = wp_get_current_user() ) {
					return;
				}

				// make sure the post type is valid
				if ( ! post_type_exists( $post_type ) ) {
					return;
				}

				$set_user_revisions = intval( filter_input( INPUT_POST, 'wp_rev_list_number_of_revisions_screen_option', FILTER_SANITIZE_NUMBER_INT ) );

				if ( $set_user_revisions < 0 ) {
					$set_user_revisions = 0;
				}

				update_user_meta( $user->ID, 'wp_revision_list_number_of_revisions_' . $post_type, $set_user_revisions );

			}

		}


	} // end class

}

