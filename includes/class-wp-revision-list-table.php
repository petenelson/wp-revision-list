<?php

if ( !defined( 'ABSPATH' ) ) exit( 'restricted access' );

if ( !class_exists( 'WP_Revision_List_Table' ) ) {

	class WP_Revision_List_Table {


		public function plugins_loaded() {
			add_filter( 'the_posts', array( $this, 'the_posts' ) );
			add_filter( 'the_title', array( $this, 'the_title' ), 10, 2 );
			add_filter( 'post_row_actions', array( $this, 'post_row_actions' ), 10, 2 );
			add_action( 'admin_footer', array( $this, 'admin_footer' ) );
			add_action( 'admin_init', array( $this, 'admin_init' ) );
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
				$revisions = $this->get_revisions_for_posts( $posts );

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


		private function get_revisions_for_posts( $posts ) {
			$number_of_revisions = apply_filters( 'wp-revision-list-setting-get', 3, 'wp-revision-list-settings-general', 'number_of_revisions' );

			$revisions = array();
			foreach( $posts as $post ) {
				foreach ( wp_get_post_revisions( $post->ID, array( 'posts_per_page' => $number_of_revisions ) ) as $revision ) {
					$revisions[] = new WP_Post( $revision );
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


		private function is_revision_list_screen() {
			$screen = get_current_screen();
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
		public function admin_init() {


		}



	} // end class

}
