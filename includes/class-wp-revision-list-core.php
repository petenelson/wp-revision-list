<?php

if ( ! defined( 'ABSPATH' ) ) die( 'restricted access' );

if ( ! class_exists( 'WP_Revision_List_Core' ) ) {

	class WP_Revision_List_Core {

		static $plugin_name = 'wp-revision-list';


		public function plugins_loaded() {
			add_filter( WP_Revision_List_Core::$plugin_name . '-is-revision-list-screen', array( $this, 'is_revision_list_screen' ), 10, 2 );
			add_filter( WP_Revision_List_Core::$plugin_name . '-user-number-of-revisions', array( $this, 'user_number_of_revisions' ), 10, 2 );
			add_filter( WP_Revision_List_Core::$plugin_name . '-selected-post-types', array( $this, 'selected_post_types' ), 10, 1 );
		}


		public function is_revision_list_screen( $is_revision_list_screen, $screen = null ) {
			if ( empty ( $screen ) ) {
				$screen = get_current_screen();
			}
			$is_revision_list_screen = $screen->base == 'edit' && in_array( $screen->post_type, $this->selected_post_types( array() ) );
			return $is_revision_list_screen;
		}


		public function user_number_of_revisions( $revisions, $post_type ) {
			$revisions = get_user_meta( get_current_user_id(), 'wp_revision_list_number_of_revisions_' . $post_type, true );
			return ( $revisions === false || $revisions === NULL || $revisions === '' ) ? $this->config_number_of_revisions() : intval( $revisions );
		}


		public function selected_post_types( $post_types ) {
			if ( ! is_array( $post_types) ) {
				$post_types = array();
			}
			$selected_post_types = apply_filters( WP_Revision_List_Core::$plugin_name . '-setting-get', array( 'post', 'page'), WP_Revision_List_Core::$plugin_name . '-settings-general', 'post_types' );
			return array_merge( $post_types, $selected_post_types );
		}


		private function config_number_of_revisions() {
			return intval( apply_filters( WP_Revision_List_Core::$plugin_name . '-setting-get', 3, WP_Revision_List_Core::$plugin_name . '-settings-general', 'number_of_revisions' ) );
		}


	} // end class

}
