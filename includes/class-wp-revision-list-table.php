<?php

if ( !defined( 'ABSPATH' ) ) exit( 'restricted access' );

if ( !class_exists( 'WP_Revision_List_Table' ) ) {

	class WP_Revision_List_Table {


		public function plugins_loaded() {

			add_filter( 'the_posts', array( $this, 'the_posts' ) );
			add_filter( 'the_title', array( $this, 'the_title' ), 10, 2 );
			add_filter( 'post_row_actions', array( $this, 'post_row_actions' ), 10, 2 );
			add_action( 'admin_footer', array( $this, 'admin_footer' ) );

		}


		public function the_posts( $posts ) {

			if ( is_admin() && ! empty( $posts ) ) {

				// limit addition of revisions to only specific post types
				$post_types = apply_filters( WP_Revision_List_Core::$plugin_name . '-selected-post-types', array() );

				if ( in_array( $posts[0]->post_type, $post_types ) ) {
					$posts = $this->add_revisions_to_posts( $posts );
				}

			}

			return $posts;

		}


		private function add_revisions_to_posts( $posts ) {

			$new_post_list = array();
			$screen = get_current_screen();
			$is_trash = filter_input( INPUT_GET, 'post_status', FILTER_SANITIZE_STRING );

			$revisions = $this->get_revisions_for_posts( $posts, $screen->post_type );

			foreach ( $posts as $post ) {
				$new_post_list[] = $post;
				if ( $screen->base == 'edit' && $screen->post_type == $post->post_type && ! $is_trash ) {
					foreach( $revisions as $revision ) {
						if ( $revision->post_parent === $post->ID ) {
							$new_post_list[] = $revision;
						}
					}
				}
			}

			return $new_post_list;
		}


		private function get_revisions_for_posts( $posts, $post_type ) {
			$revisions = array();
			$number_of_revisions = apply_filters( WP_Revision_List_Core::$plugin_name . '-user-number-of-revisions', 3, $post_type );
			if ( $number_of_revisions > 0 ) {
				foreach( $posts as $post ) {
					foreach ( wp_get_post_revisions( $post->ID, array( 'posts_per_page' => $number_of_revisions ) ) as $revision ) {
						$revisions[] = new WP_Post( $revision );
					}
				}
			}

			return $revisions;
		}


		public function the_title( $parent_post_title, $parent_id = null ) {
			global $post;
			if ( ! empty( $post ) && $post->post_type == 'revision' && $this->is_revision_list_screen() ) {
				$prefix = apply_filters( WP_Revision_List_Core::$plugin_name . '-setting-get', '* ', WP_Revision_List_Core::$plugin_name . '-settings-general', 'prefix' );
				$suffix = apply_filters( WP_Revision_List_Core::$plugin_name . '-setting-get', ' (Rev)', WP_Revision_List_Core::$plugin_name . '-settings-general', 'suffix' );
				return  $prefix . $parent_post_title . $suffix;
			} else {
				return $parent_post_title;
			}

		}


		public function post_row_actions( $actions, $post ) {
			if ( $post->post_type == 'revision' && $this->is_revision_list_screen() ) {
				unset( $actions['inline hide-if-no-js'] );
				unset( $actions['trash'] );
			}
			return $actions;
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


		private function is_revision_list_screen() {
			return apply_filters( WP_Revision_List_Core::$plugin_name . '-is-revision-list-screen', false );
		}


	} // end class

}
