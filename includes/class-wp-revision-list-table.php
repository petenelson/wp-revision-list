<?php

if ( !defined( 'ABSPATH' ) ) exit( 'restricted access' );

if ( !class_exists( 'WP_Revision_List_Table' ) ) {

	class WP_Revision_List_Table {


		public function plugins_loaded() {

			add_filter( 'the_posts', array( $this, 'the_posts' ) );
			add_filter( 'the_title', array( $this, 'the_title' ), 10, 2 );
			add_filter( 'post_row_actions', array( $this, 'post_row_actions' ), 10, 2 );
			add_action( 'admin_footer', array( $this, 'admin_footer' ) );

			// outputs custom screen options fields
			add_filter( 'screen_settings', array( $this, 'screen_settings' ), 10, 2 );

			// because the built-in misc/set_screen_options() does a redirect after it
			// saves the _per_page option, this action appears to be the only way to hook
			// into saving custom screen options
			add_action( 'check_admin_referer', array( $this, 'save_user_meta' ), 10, 2 );

		}


		public function the_posts( $posts ) {

			if ( is_admin() && ! empty( $posts ) ) {

				// limit addition of revisions to only specific post types
				$post_types = $this->get_selected_post_types();

				if ( in_array( $posts[0]->post_type, $post_types ) ) {
					$posts = $this->add_revisions_to_posts( $posts );
				}

			}

			return $posts;

		}


		private function get_selected_post_types() {
			$post_types = apply_filters( 'wp-revision-list-setting-get', array( 'post', 'page'), 'wp-revision-list-settings-general', 'post_types' );
			return ! array( $post_types ) ? array() : $post_types;
		}


		private function add_revisions_to_posts( $posts ) {

			$screen = get_current_screen();
			$is_trash = filter_input( INPUT_GET, 'post_status', FILTER_SANITIZE_STRING );
			if ( $screen->base == 'edit' && $screen->post_type == $posts[0]->post_type ) {

				$new_post_list = array();
				$revisions = $this->get_revisions_for_posts( $posts, $screen->post_type );

				foreach ($posts as $post) {
					$new_post_list[] = $post;
					if ( ! $is_trash ) {
						foreach( $revisions as $revision ) {
							if ( $revision->post_parent === $post->ID ) {
								$new_post_list[] = $revision;
							}
						}
					}
				}

			}

			return $new_post_list;
		}


		private function get_user_number_of_revisions( $post_type ) {
			$user = wp_get_current_user();
			$revisions = get_user_meta( $user->ID, 'wp_revision_list_number_of_revisions_' . $post_type, true );
			return $revisions === false ? $this->get_config_number_of_revisions() : intval( $revisions );
		}


		private function get_config_number_of_revisions() {
			return intval( apply_filters( 'wp-revision-list-setting-get', 3, 'wp-revision-list-settings-general', 'number_of_revisions' ) );
		}


		private function get_revisions_for_posts( $posts, $post_type ) {
			$revisions = array();
			$number_of_revisions = $this->get_user_number_of_revisions( $post_type );
			if ( $number_of_revisions > 0 ) {
				foreach( $posts as $post ) {
					foreach ( wp_get_post_revisions( $post->ID, array( 'posts_per_page' => $number_of_revisions ) ) as $revision ) {
						$revisions[] = new WP_Post( $revision );
					}
				}
			}

			return $revisions;
		}


		public function the_title( $parent_post_title, $parent_ID ) {
			global $post;
			if ( $post->post_type == 'revision' ) {
				$prefix = apply_filters( 'wp-revision-list-setting-get', '* ', 'wp-revision-list-settings-general', 'prefix' );
				$suffix = apply_filters( 'wp-revision-list-setting-get', ' (Rev)', 'wp-revision-list-settings-general', 'suffix' );
				return  $prefix . $parent_post_title . $suffix;
			} else {
				return $parent_post_title;
			}

		}


		public function post_row_actions( $actions, $post ) {
			if ( $post->post_type == 'revision' ) {
				unset( $actions['inline hide-if-no-js'] );
				unset( $actions['trash'] );
			}
			return $actions;
		}


		private function is_revision_list_screen( $screen = null ) {
			if ( empty ( $screen ) ) {
				$screen = get_current_screen();
			}
			return $screen->base == 'edit' && in_array( $screen->post_type, $this->get_selected_post_types() );
		}


		public function admin_footer() {

			if ( $this->is_revision_list_screen() ) {
				?>
				<script type="text/javascript">
					jQuery(document).ready(function() {
						jQuery('.wp-list-table .type-revision input[type="checkbox"]').hide();
					});
				</script>
				<?php
			}

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


		public function screen_settings( $screen_settings, $screen ) {

			// add a field to screen options
			if ( $this->is_revision_list_screen( $screen ) ) {

				if ( ! $user = wp_get_current_user() ) {
					return;
				}

				$number_of_revisions = $this->get_user_number_of_revisions( $screen->post_type );

				ob_start();
				?>
				<div class="screen-options">
					<fieldset>
						<label for="wp_rev_list_number_of_revisions_screen_option"><?php _e( 'Number of revisions to display:', 'wp-revision-list' ); ?></label>
							<input type="number" step="1" min="0" max="20" name="wp_rev_list_number_of_revisions_screen_option" id="wp_rev_list_number_of_revisions_screen_option" maxlength="2" value="<?php echo $number_of_revisions ?>" />
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



	} // end class

}
