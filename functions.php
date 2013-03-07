<?php

/**
 * Place any hand-crafted Wordpress
 * Please read the documentation on how to use this file within child theme (README.md)
 */

/**
 * Properly add new script files using this function.
 * http://codex.wordpress.org/Plugin_API/Action_Reference/wp_enqueue_scripts
 */
function diamond_scripts() {
	$stylesheetDir = get_stylesheet_directory_uri();
	$protocol='http:'; // discover the correct protocol to use
 	if(!empty($_SERVER['HTTPS'])) $protocol='https:';

	wp_enqueue_style( 'diamond-style', get_stylesheet_directory_uri().'/css/diamond-style.css' );
	wp_enqueue_script('diamond-custom-script', get_stylesheet_directory_uri().'/js/scripts.js', array(), false, true);
		wp_enqueue_script('bootstrap-modal', get_template_directory_uri().'/inc/bootstrap/js/bootstrap-modal.js', array(), false, true);

	// AJAX Calls  
	wp_enqueue_script('json2');
  wp_enqueue_script('ajax_scripts', "$stylesheetDir/js/ajax-game-scripts.js", array('jquery','json2'), true);
	wp_localize_script('ajax_scripts', 'ajax_scripts', array(
		'ajaxurl' => admin_url('admin-ajax.php',$protocol),
		'nonce' => wp_create_nonce('ajax_scripts_nonce')
	));
}
add_action( 'wp_enqueue_scripts', 'diamond_scripts' );

// Basic Hawaiian Custom Post Types
function BASICHWN_post_types() {
	$supportedMetaboxes = array('title', 'editor', 'thumbnail');
	
	// Vocabulary
	$labels = array(
		'name' => __( 'Vocabulary Terms' ),
		'singular_name' => __( 'Vocabulary Term' ),
		'add_new' => __( 'Add New Vocabulary Term' ),
		'add_new_item' => __( 'Add New Vocabulary Term' ),
		'edit_name' => __( 'Edit This Vocabulary Term' ),
		'view_item' => __( 'View This Vocabulary Term' ),
		'search_items' => __('Search Vocabulary Terms'),
		'not_found' => __('No Vocabulary Terms found.'),
	);
	register_post_type( 'vocabulary_terms',
		array(
		'menu_position' => 5,
		'public' => true,
		'supports' => $supportedMetaboxes,
		'labels' => $labels,
		'rewrite' => array('slug' => 'vocabulary'),
		'taxonomies' =>	array('vocabulary_categories'),
		)
	);
}
add_action( 'init', 'BASICHWN_post_types' );

// Basic Hawaiian Custom Taxonomies
function BASICHWN_taxonomies() {
  // Add new taxonomy, make it hierarchical (like categories)
  $labels = array(
    'name' => _x( 'Vocabulary Categories', 'taxonomy general name' ),
    'singular_name' => _x( 'Vocabulary Category', 'taxonomy singular name' ),
    'search_items' =>  __( 'Search Vocabulary Categories' ),
    'all_items' => __( 'All Vocabulary Categories' ),
    'parent_item' => __( 'Parent Vocabulary Category' ),
    'parent_item_colon' => __( 'Parent Vocabulary Category:' ),
    'edit_item' => __( 'Edit Vocabulary Category' ), 
    'update_item' => __( 'Update Vocabulary Category' ),
    'add_new_item' => __( 'Add New Vocabulary Category' ),
    'new_item_name' => __( 'New Vocabulary Category Name' ),
    'menu_name' => __( ' Edit Vocabulary Categories' ),
  ); 	

  register_taxonomy('vocabulary_categories',array('vocabulary_terms'), array(
    'hierarchical' => true,
    'labels' => $labels,
    'show_ui' => true,
    'query_var' => true,
    'rewrite' => array( 'slug' => 'vocabulary-categories' ),
  ));

  // Add new taxonomy, make it hierarchical (like categories)
  $labels = array(
    'name' => _x( 'Difficulty Levels', 'taxonomy general name' ),
    'singular_name' => _x( 'Difficulty Level', 'taxonomy singular name' ),
    'search_items' =>  __( 'Search Difficulty Levels' ),
    'all_items' => __( 'All Difficulty Levels' ),
    'parent_item' => __( 'Parent Difficulty Level' ),
    'parent_item_colon' => __( 'Parent Difficulty Level:' ),
    'edit_item' => __( 'Edit Difficulty Level' ), 
    'update_item' => __( 'Update Difficulty Level' ),
    'add_new_item' => __( 'Add New Difficulty Level' ),
    'new_item_name' => __( 'New Difficulty Level Name' ),
    'menu_name' => __( 'Edit Difficulty Levels' ),
  ); 	

  register_taxonomy('difficulty_level',array('vocabulary_terms','post'), array(
    'hierarchical' => true,
    'labels' => $labels,
    'show_ui' => true,
    'query_var' => true,
    //'rewrite' => array( 'slug' => 'genre' ),
  ));
  error_log('register_taxonomy');
}
add_action( 'init', 'BASICHWN_taxonomies');

// Load Ajax Game Functions
require( 'ajax-game-functions.php' );

// User Avatar Profile Field
// http://wordpress.stackexchange.com/questions/54044/wordpress-user-profile-upload-if-page-is-saved-file-reset
function extra_user_profile_fields( $user ) { 
$r = get_user_meta( $user->ID, 'picture', true );
?>

<!-- Artist Photo Gallery -->
<h3><?php _e("Public Profile - Gallery", "BASICHWN"); ?></h3>

<table class="form-table">
	<tr>
  	<th scope="row">Picture</th>
    <td>
    	<input type="file" name="picture" value="" />
	    <?php //print_r($r); 
	        if (!isset($r['error'])) {
	            $r = $r['url'];
	            echo "<img src='$r' />";
	        } else {
	            $r = $r['error'];
	            echo $r . 'tsk';
	        }
	    ?>
    </td>
  </tr>
  <tr>
  	<th scope="row">Remove User Image</th>
  	<td>
  		<input type="submit" name="remove_user_picture" value="Remove your image" />
	  	<?php
	  	if ( function_exists('wp_nonce_field') ) 
				wp_nonce_field('remove_user_picture','user-profile-remove_user_picture');
			?>
  	</td>
  </tr>
</table> 

<?php
}
add_action( 'show_user_profile', 'extra_user_profile_fields' );
add_action( 'edit_user_profile', 'extra_user_profile_fields' );

function save_extra_user_profile_fields( $user_id ) {
	if ( !current_user_can( 'edit_user', $user_id ) ) { return false; }
	
	if( $_FILES['picture']['error'] === UPLOAD_ERR_OK ) { //http://www.php.net/manual/en/features.file-upload.errors.php
    $r = wp_handle_upload( $_FILES['picture'], array('test_form' => FALSE) ); //http://codex.wordpress.org/Function_Reference/wp_handle_upload#Parameters
    update_user_meta( $user_id, 'picture', $r );
	}
  
  // Remove user image
  if ( empty($_POST) || !wp_verify_nonce($_POST['user-profile-remove_user_picture'],'remove_user_picture') ) {
  	print 'sorry';// Maybe
  	exit;
  } else {
	  if(isset($_POST['remove_user_picture'])) {
	  	if ( ! delete_user_meta($user_id, 'picture') ) {
	  		echo "Ooops! Error while deleting this information!";
			} else {
				delete_user_meta($user_id, 'picture');
			}
	  }
  }

}
add_action( 'personal_options_update', 'save_extra_user_profile_fields' );
add_action( 'edit_user_profile_update', 'save_extra_user_profile_fields' );


function make_form_accept_uploads() {
    echo ' enctype="multipart/form-data"';
}
add_action('user_edit_form_tag', 'make_form_accept_uploads');

/**
 * Custom Hook Functions
 *
 * Use these hooks to add/insert functions/content at specific load points within the Wordpress loading process.
 * Inspired by Thematic
 * A list of all hook functions and what templates they are used in:
 *
 *	bedrock_before()
 *		bedrock_aboveheader()
 *		(header)
 *		bedrock_belowheader()
 *		bedrock_mainstart()
 *			bedrock_contentstart()
 *			(breadcrumbs)
 *			bedrock_abovepostcontent()
 *				bedrock_postcontentstart()
 *				(postcontent)
 *					bedrock_abovetitle()
 *					bedrock_belowtitle()
 *				bedrock_postcontentend()
 *			bedrock_belowpostcontent()
 *			bedrock_contentend()
 *			bedrock_sidebarstart()
 *			(sidebar)
 *			bedrock_sidebarend()
 *			(pager)
 *		bedrock_mainend()
 *	bedrock_after()
 *
 * Here is an example of how to properly hook into a function:
 *
 *		function nameOfMyNewFunction() {
 *			// content to output
 *		}
 *		add_action('theNameOfTheHookTheContentAboveWillGetLoaded','nameOfMyNewFunction');
 *
 */




?>