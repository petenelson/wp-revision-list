<?php
/*
Plugin Name: Revisions in List Table
Description: Description
Author: Pete Nelson
Version: 1.0
*/

if ( ! defined( 'ABSPATH' ) ) exit( 'restricted access');


add_filter( 'the_posts', 'pn_the_posts' );
add_filter( 'the_title', 'pn_the_title', 10, 2 );
add_action( 'init', 'pn_custom_post_type' );


function pn_the_posts( $posts ) {

	if ( is_admin() && ! empty( $posts ) ) {

		// limit addition of revisions to only specific post types
		$post_types = array( 'post', 'page', 'my-post-type', 'gmec-product-price' );

		if ( in_array( $posts[0]->post_type, $post_types ) ) {
			$posts = pn_add_revisions_to_posts( $posts );
		}

	}

	return $posts;

}


function pn_add_revisions_to_posts( $posts ) {

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


function pn_the_title( $parent_post_title, $parent_ID ) {
	global $post;
	if ( $post->post_type == 'revision' ) {
		return  '* ' . $parent_post_title . ' (Rev)';
	} else {
		return $parent_post_title;
	}

}


function pn_custom_post_type() {
	register_post_type( 'my-post-type', array(
		'public' => true,
		'label' => 'My CPT',
		'supports' => array( 'title', 'author', 'editor', 'revisions' ),
	));
}

