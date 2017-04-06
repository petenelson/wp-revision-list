<?php
/*
Plugin Name: WP Revision List
Description: Show revisions when viewing lists of posts, pages, or custom post types in the admin dashboard
Version: 1.1.6
Plugin URI: https://github.com/petenelson/wp-revision-list
Author: Pete Nelson <a href="https://twitter.com/GunGeekATX">(@GunGeekATX)</a>
Text Domain: wp-revision-list
Domain Path: /lang
*/

if ( ! defined( 'ABSPATH' ) ) exit( 'restricted access' );

// load the text domain
add_action( 'plugins_loaded', 'wp_revision_list_load_text_domain' );

if ( ! function_exists( 'wp_revision_list_load_text_domain' ) ) {
	function wp_revision_list_load_text_domain() {
		load_plugin_textdomain( 'wp-revision-list', false, basename( plugin_dir_path( __FILE__ ) ) . '/lang/' );
	}
}

// include required files
$includes = array( 'core', 'settings', 'screen-options', 'table' );
foreach ( $includes as $include ) {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-revision-list-' . $include . '.php';
}


// load our classes, hook them to WordPress
if ( class_exists( 'WP_Revision_List_Core' ) ) {
	$revision_core = new WP_Revision_List_Core();
	add_action( 'plugins_loaded', array( $revision_core, 'plugins_loaded' ) );
}


if ( class_exists( 'WP_Revision_List_Settings' ) ) {
	$revision_settings = new WP_Revision_List_Settings();
	add_action( 'plugins_loaded', array( $revision_settings, 'plugins_loaded' ) );
	register_activation_hook( __FILE__, array( $revision_settings, 'activation_hook' ) );
}


if ( class_exists( 'WP_Revision_List_Screen_Options' ) ) {
	$revision_screen_opt = new WP_Revision_List_Screen_Options();
	add_action( 'plugins_loaded', array( $revision_screen_opt, 'plugins_loaded' ) );
}


if ( class_exists( 'WP_Revision_List_Table' ) ) {
	$revision_table = new WP_Revision_List_Table();
	add_action( 'plugins_loaded', array( $revision_table, 'plugins_loaded' ) );
}
