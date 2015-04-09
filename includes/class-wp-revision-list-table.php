<?php

if ( !defined( 'ABSPATH' ) ) exit( 'restricted access' );

if ( !class_exists( 'WP_Revision_List_Table' ) ) {

	class WP_Revision_List_Table {


		public function plugins_loaded() {
			add_filter( 'the_posts', array( $this, 'the_posts' ) );
			add_filter( 'the_title', array( $this, 'the_title' ), 10, 2 );
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
			return array( 'post', 'page' );
		}


		private function add_revisions_to_posts( $posts ) {

			$screen = get_current_screen();
			if ( $screen->base == 'edit' && $screen->post_type == $posts[0]->post_type ) {

				$post_index = 0;

				foreach( $posts as $post ) {

					$revisions = array();
					foreach ( wp_get_post_revisions( $post->ID ) as $revision ) {
						$revisions[] = new WP_Post( $revision );
					}

					if ( ! empty( $revisions ) ) {
						array_splice( $posts, $post_index + 1, 0, $revisions );
					}

					$post_index++;
				}
			}

			return $posts;
		}


		public function the_title( $parent_post_title, $parent_ID ) {
			global $post;
			if ( $post->post_type == 'revision' ) {
				return  '* ' . $parent_post_title . ' (Rev)';
			} else {
				return $parent_post_title;
			}

		}




	} // end class

}
