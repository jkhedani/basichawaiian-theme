<?php
/*
Controller name: Exporter
Controller description: Methods for getting other post types.
 */

class JSON_API_Exporter_Controller {

	// Get all post types, including custom post types.
	public function get_post_types() {
		$post_types = get_post_types(
			array(
				"public" => true,
				"publicly_queryable" => true,
			)
		);
		return array(
			"post_types" => $post_types,
		);
	}

	// Get list of all post IDs of a single post type.
	public function get_post_ids_with_type() {
		global $json_api;
		extract( $json_api->query->get( array( 'type', 'post_type', 'verbose', 'very_verbose' ) ) );
		if ( $type || $post_type ) {
			if ( ! $type ) {
				$type = $post_type;
			}
		} else {
			$json_api->error( "Include 'type' or 'post_type' var in your request. Include 'verbose' for extra post info, and 'very_verbose' for full post objects." );
		}

		$posts = get_posts(
			array(
				'posts_per_page' => -1,
				'post_type' => $type,
				'orderby' => 'menu_order',
			)
		);

		if ( $posts ) {
			if ( $very_verbose ) {
				$verbose_posts = array();
				foreach ( $posts as $post ) {
					$verbose_post = get_post( $post->ID );
					if ( $verbose_post ) {
						$verbose_posts[] = new JSON_API_Post( $verbose_post );
					}
				}
				$response = array(
					'posts' => $verbose_posts,
				);
			} else if ( $verbose ) {
				$response = array(
					'posts' => $posts,
				);
			} else {
				$post_ids = array_map(
					function($post) { return array( 'ID' => $post->ID ); },
					$posts
				);
				$response = array(
					'posts' => $post_ids,
				);
			}
			return $response;
		} else {
			$json_api->error( "Not found." );
		}
	}

	// Get any post (including custom post types) by id.
	public function get_post_with_id() {
		global $json_api;
		extract( $json_api->query->get( array( 'id', 'page_id' ) ) );
		if ( $id || $page_id ) {
			if ( ! $id ) {
				$id = $page_id;
			}
		} else {
			$json_api->error( "Include 'id' or 'page_id' var in your request." );
		}
		$id = intval( $id );

		$post = get_post( $id );
		if ( $post ) {
			$response = array(
				'post' => new JSON_API_Post( $post ),
			);
			return $response;
		} else {
			$json_api->error( "Not found." );
		}
	}

}
