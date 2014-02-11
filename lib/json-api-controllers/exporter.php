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
		global $wpdb;
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
			// If this is a vocabulary_term, get its p2p data
			$extra = array();
			if ( $post->post_type === 'vocabulary_terms' ) {
				// Get connections and determine this term's difficulty level
				$difficulty_1_unit = 0;
				$difficulty_2_module = 0;
				$difficulty_3_topic = 0;
				$connected_unit = '';
				$connected_module = '';
				$connected_topic = '';
				$connected_lesson = '';

				// Get connected vocabulary lessons
				$connected_vocabulary_lessons = $wpdb->get_results( "SELECT p2p_to FROM wp_p2p WHERE p2p_from = {$post->ID} AND p2p_type = 'vocabulary_terms_to_vocabulary_lessons'" );
				foreach ( $connected_vocabulary_lessons as $lesson ) {
					// Get connected topics
					$connected_topics = $wpdb->get_results( "SELECT p2p_to FROM wp_p2p WHERE p2p_from = {$lesson->p2p_to} AND p2p_type = 'vocabulary_lessons_to_topics'" );
					foreach ( $connected_topics as $topic ) {
						// Get connected modules
						$connected_modules = $wpdb->get_results( "SELECT p2p_to FROM wp_p2p WHERE p2p_from = {$topic->p2p_to} AND p2p_type = 'topics_to_modules'" );
						foreach ( $connected_modules as $module ) {
							// Get connected units
							$connected_units = $wpdb->get_results( "SELECT p2p_to FROM wp_p2p WHERE p2p_from = {$module->p2p_to} AND p2p_type = 'modules_to_units'" );
							foreach ( $connected_units as $unit ) {
								// Get unit menu order (first level of difficulty) and name
								$unit = get_post( $unit->p2p_to );
								$difficulty_1_unit = $unit->menu_order;
								$connected_unit = $unit->post_title;
							}
							// Get module menu order (second level of difficulty) and name
							$module = get_post( $module->p2p_to );
							$difficulty_2_module = $module->menu_order;
							$connected_module = $module->post_title;
							// Our data is messy; look for a module number in the title (since menu_order is often not filled out) and use that instead
							preg_match( '#\d+#', $connected_module, $matches );
							if ( count( $matches ) > 0 ) {
								$difficulty_2_module = intval( $matches[0] );
							}
						}
						// Get topic menu order (third level of difficulty) and name
						$topic = get_post( $topic->p2p_to );
						$difficulty_3_topic = $topic->menu_order;
						$connected_topic = $topic->post_title;
						// Our data is messy; look for a topic number in the title (since menu_order is often not filled out) and use that instead
						preg_match( '#\d+#', $connected_topic, $matches );
						if ( count( $matches ) > 0 ) {
							$difficulty_3_topic = intval( $matches[0] );
						}
					}
					// Get lesson name
					$lesson = get_post( $lesson->p2p_to );
					$connected_lesson = $lesson->post_title;
				}
				
				$extra['difficulty_1_unit'] = $difficulty_1_unit;
				$extra['difficulty_2_module'] = $difficulty_2_module;
				$extra['difficulty_3_topic'] = $difficulty_3_topic;
				$extra['connected_unit'] = $connected_unit;
				$extra['connected_module'] = $connected_module;
				$extra['connected_topic'] = $connected_topic;
				$extra['connected_lesson'] = $connected_lesson;
			}

			$response = array(
				'post' => new JSON_API_Post( $post ),
				'extra' => $extra,
			);
			return $response;
		} else {
			$json_api->error( "Not found." );
		}
	}

}
