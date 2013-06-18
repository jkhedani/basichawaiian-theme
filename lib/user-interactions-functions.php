<?php

/**
 * User Interactions "Plug-in"
 * By: Justin Hedani
 * Creates various methods to track and assess users.
 */

global $user_interactions_db_version;
$user_interactions_db_version = "0.1";

/**
 * Install/Update "User Interactions" Tables
 * NOTE: May be useful: http://codex.wordpress.org/Creating_Tables_with_Plugins#The_Whole_Function
 */
function install_user_interactions_db() {
  global $wpdb;
  global $user_interactions_db_version;
  $installed_ver = get_option( 'user_interactions_db_version' );

	$table_name = $wpdb->prefix . "user_interactions";
  
  if ( $installed_ver != $user_interactions_db_version ) {
	  // WHY MY PRIMARY KEYS ARE SET THE WAY THEY IS
	  //http://stackoverflow.com/questions/5835978/how-to-properly-create-composite-primary-keys-mysql
	  $sql = $wpdb->prepare("CREATE TABLE $table_name (
	    interaction_id bigint(20) unsigned NOT NULL auto_increment,
	    user_id bigint(20) unsigned NOT NULL,
	    post_id bigint(20) unsigned NOT NULL,
	    times_correct bigint(20) NOT NULL,
	    times_wrong bigint(20) NOT NULL,
	    times_viewed bigint(20) NOT NULL,
	    times_completed bigint(20) NOT NULL,
	    UNIQUE KEY  (interaction_id),
	    PRIMARY KEY (user_id, post_id),
	    KEY times_correct (times_correct),
	    KEY times_wrong (times_wrong),
	    KEY times_viewed (times_viewed),
	    KEY times_completed (times_completed))
	    DEFAULT CHARACTER SET = utf8
	    COLLATE = utf8_general_ci
	  ");
  	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
  	dbDelta( $sql );
  	// update_option will also add an option if it doesn't exist.
  	update_option('user_interactions_db_version', $user_interactions_db_version);
	}

}
add_action('after_switch_theme','install_user_interactions_db', 10, 2); // on activate theme
//add_action('switch_theme','basic_database_destroy', 10, 2); // on deactivate theme

function update_user_interactions_db() {
	global $user_interactions_db_version;
	$installed_ver = get_option( 'user_interactions_db_version' );
  if ( get_site_option( 'user_interactions_db_version' ) != $user_interactions_db_version ) {
  	install_user_interactions_db();
  }
}
add_action( 'after_switch_theme', 'update_user_interactions_db', 10, 2);

/**
 * Function: Create Object Record
 * Usage: Run when you want to create a record for a particular object
 *		@param array $postIDs An array IDs you wish to create records for
 */
if ( !function_exists('create_object_record') ) :
function create_object_record( $postIDs ) {
	if ( is_user_logged_in() ) {
		global $wpdb;
		$current_user = wp_get_current_user();
    $affected_user_id = $current_user->ID;
		// Prepare post ids to be passed to wpdb
		$post_ids = implode(', ',$postIDs);
		$post_ids_safe = mysql_real_escape_string($post_ids); // Just because I like being safe.
		// Check if a record for queried objects exist
		$doObjectsExist = $wpdb->get_results( $wpdb->prepare(
			"
			SELECT *
			FROM wp_user_interactions
			WHERE user_id = %d
				AND post_id IN (".$post_ids_safe.")
			ORDER BY post_id DESC
			LIMIT 0, 10
			"
			, $affected_user_id
		));

		// Prepare existing objects to be checked
		$existingObjectIDs = array();
		// Throw all existing object IDs into an array
		foreach ($doObjectsExist as $existingObject) {
			$existingObjectIDs[] = $existingObject->post_id;
		}

		// Remove existing objects from queried post IDs
		// exposing only new object IDs
		$newObjectIDs = array_diff($postIDs, $existingObjectIDs);
		
		// Create new object records if new objects exist.
		if ( !empty($newObjectIDs) ) {
			$values = array();
			$placeHolders = array();

			// Prepare individual values separately to get passed to the query
			foreach ( $newObjectIDs as $newObjectID ) :
				$values[] = $affected_user_id.',';
				$values[] = $newObjectID.',';
				$values[] = '0';
				$placeHolders[] = '(%d, %d, %d)';
			endforeach;

			// Prepare placeholders for the query
			$placeHolderCreate = implode( ', ', $placeHolders );

			// Insert records for the user
			$rows_affected = $wpdb->query( $wpdb->prepare(
				"
				INSERT INTO wp_user_interactions
					( user_id, post_id, times_completed )
				VALUES ".$placeHolderCreate."
				", $values
			));

		} // new objects exist
	} // is_user_logged_in
} // function
endif;

/**
 * Function: Add View To Object
 * Usage: Run when you want to add a single view to an object
 *    @param int, array $value The ID or IDS of the specific post(s) value you wish to increment
 *    @param string $col The name of the db col you wish to update (must be a sanctioned value)
 */

if ( !function_exists('increment_object_value') ) :
function increment_object_value ( $value, $col ) {

  if ( is_user_logged_in() ) {
    global $wpdb;
    $current_user = wp_get_current_user();
    $affected_user_id = $current_user->ID;
    $col_to_update = '0,0,1,0'; // Graceful fallback updates views just in case

    if ( $col == 'times_correct' ) {
    	$col_to_update = '1,0,0,0';
    } else if ( $col == 'times_wrong' ) {
    	$col_to_update = '0,1,0,0';
    } else if ( $col == 'times_viewed' ) {
    	$col_to_update = '0,0,1,0';
    } else if ( $col == 'times_completed' ) {
    	$col_to_update = '0,0,0,1';
    } else {
    	$col_to_update = '0,0,1,0'; // Graceful fallback updates views just in case
    }

    // If value is an array, treat it as multiple values
    if ( is_array( $value ) ) {
    	$values = array();
      foreach( $value as $objectID ) :
      	$values[] = '('.$affected_user_id.','.$objectID.','.$col_to_update.')';
      endforeach;
    } else {
    	$values = array('('.$affected_user_id.','.$value.','.$col_to_update.')'); 
    }

    $rows_affected = $wpdb->query(
    "
    INSERT INTO wp_user_interactions
    (user_id, post_id, times_correct, times_wrong, times_viewed, times_completed)
    VALUES ".implode(',',$values)."
    ON DUPLICATE KEY UPDATE times_correct=times_correct+VALUES(times_correct), times_wrong=times_wrong+VALUES(times_wrong), times_viewed=times_viewed+VALUES(times_viewed), times_completed=times_completed+VALUES(times_completed)
    ");
  }

}
endif;

/**
 * Function: Is Object Complete
 * Usage: Run when you want to check if a particular user has completed a particular object
 *    @param int postID: The ID of the specific post you want to check is "completed"
 *		@return bool True if user has completed, false if not.
 */
if ( !function_exists('is_object_complete') ) :
function is_object_complete ( $postID ) {
  if ( is_user_logged_in() ) {
    global $wpdb;
    $current_user = wp_get_current_user();
    $affected_user_id = $current_user->ID;
		$post_ids_safe = mysql_real_escape_string($postID); // Just because I like being safe.
			
		// Check if user has completed the queried object before.
		$isObjectComplete = $wpdb->get_results( $wpdb->prepare(
			"
			SELECT times_completed
			FROM wp_user_interactions
			WHERE user_id = %d
				AND post_id IN (".$post_ids_safe.")
			ORDER BY post_id DESC
			LIMIT 0, 10
			"
			, $affected_user_id
		));
		// Return false if user has not completed an object
		if ( $isObjectComplete[0]->times_completed == 0 )
			return false;
		// Return true if user has
		return true;	
  }
}
endif;



?>