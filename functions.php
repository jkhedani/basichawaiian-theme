<?php

/**
 * Place any hand-crafted Wordpress
 * Please read the documentation on how to use this file within child theme (README.md)
 */


/**
 * Load User Interactions Functions
 */
require( 'lib/user-interactions/user-interactions-functions.php' );

/**
 * Load Scene Generator Functions
 */
require( 'lib/scene-generator/scene-functions.php' );

/**
 * Custom Body Classes
 */
function custom_body_classes( $classes ) {
  // Adds a class of group-blog to blogs with more than 1 published author
  if ( ! is_user_logged_in() ) {
    $classes[] = 'not-logged-in';
  }
  return $classes;
}
add_filter( 'body_class', 'custom_body_classes' );

/**
 * Properly add new script files using this function.
 * http://codex.wordpress.org/Plugin_API/Action_Reference/wp_enqueue_scripts
 */
function diamond_scripts() {
	$stylesheetDir = get_stylesheet_directory_uri();
	$protocol='http:'; // discover the correct protocol to use
 	if(!empty($_SERVER['HTTPS'])) $protocol='https:';

  wp_enqueue_style( 'diamond-style', get_stylesheet_directory_uri().'/css/diamond-style.css' );
  wp_enqueue_style( 'diamond-style-responsive', get_stylesheet_directory_uri().'/css/diamond-style-responsive.css', array('diamond-style','resets','bootstrap-base-styles','bootstrap-parent-style'));
	wp_enqueue_script('bootstrap-modal', get_template_directory_uri().'/inc/bootstrap/js/bootstrap-modal.js', array('jquery'), false, true);
  wp_enqueue_script('bootstrap-carousel', get_template_directory_uri().'/inc/bootstrap/js/bootstrap-carousel.js', array('jquery'), false, true);
  wp_enqueue_script('diamond-custom-script', get_stylesheet_directory_uri().'/js/scripts.js', array('jquery'), false, true);

	// AJAX Calls
  wp_enqueue_script('ajax_scripts', "$stylesheetDir/lib/user-interactions/assessment/ajax-game-scripts.js", array('jquery','json2'), true);
	wp_localize_script('ajax_scripts', 'ajax_scripts', array(
		'ajaxurl' => admin_url('admin-ajax.php',$protocol),
		'nonce' => wp_create_nonce('ajax_scripts_nonce')
	));

  // Course Storage Scripts
  wp_enqueue_script('scene-scripts', "$stylesheetDir/lib/scene-generator/scene-scripts.js", array( 'jquery','json2' ), true);
  wp_localize_script('scene-scripts', 'scene_scripts', array(
    'ajaxurl' => admin_url('admin-ajax.php',$protocol),
    'nonce' => wp_create_nonce('scene_scripts_nonce')
  ));
}
add_action( 'wp_enqueue_scripts', 'diamond_scripts' );

// Basic Hawaiian Localization
function BASICHWN_l10n(){
    load_child_theme_textdomain( 'hwn', get_stylesheet_directory() . '/languages' );
}
add_action('after_setup_theme', 'BASICHWN_l10n');

// Allow URL to change localization
// Example: basichawaiian.org/?l=basichawaiian
// http://codex.wordpress.org/Function_Reference/load_theme_textdomain
function my_theme_localized($locale) {
  if (isset($_GET['l'])) {
    return $_GET['l'];
  }
  return $locale;
}
add_filter( 'locale', 'my_theme_localized' );

// Remove unncessary admin menu items
function remove_admin_menu_items() {
  global $userdata;
  remove_menu_page('edit.php'); // "Posts"
  remove_menu_page('link-manager.php'); // "Links"
  remove_menu_page('upload.php'); // "Media"

  // Disable Comments from the get-go...
  $option_name = 'default_comment_status' ;
  $new_value = 'closed' ;
  if ( get_option( $option_name ) != $new_value ) {
    update_option( $option_name, $new_value );
  }

  // Start removing menu items conditionally
  if (get_option('default_comment_status') == 'closed')
    remove_menu_page('edit-comments.php'); // "Comments"

  // Based on user
  // http://codex.wordpress.org/Roles_and_Capabilities
  //get_currentuserinfo();
  //if ( !is_super_admin() && $userdata->user_level < 2 ) {
  //  remove_menu_page('plugins.php'); // "Plugins"
  //  remove_menu_page('tools.php'); // "Tools"
  //  remove_menu_page('users.php'); // "Users"
  //  remove_menu_page('options-general.php'); // "Settings"
  //}
}
add_action( 'admin_menu', 'remove_admin_menu_items' );

// Basic Hawaiian Custom Post Types
function BASICHWN_post_types() {

  // Units
  $labels = array(
    'name' => __( 'Units' ),
    'singular_name' => __( 'Unit' ),
    'add_new' => __( 'Add New Unit' ),
    'add_new_item' => __( 'Add New Unit' ),
    'edit_name' => __( 'Edit This Unit' ),
    'view_item' => __( 'View This Unit' ),
    'search_items' => __('Search Units'),
    'not_found' => __('No Units found.'),
  );
  register_post_type( 'units',
    array(
    'menu_position' => 5,
    'public' => true,
    'supports' => array('title', 'editor', 'thumbnail','page-attributes'),
    'labels' => $labels,
    'rewrite' => array('slug' => 'unit'),
    )
  );

  // Modules
  $labels = array(
    'name' => __( 'Modules' ),
    'singular_name' => __( 'Module' ),
    'add_new' => __( 'Add New Module' ),
    'add_new_item' => __( 'Add New Module' ),
    'edit_name' => __( 'Edit This Module' ),
    'view_item' => __( 'View This Module' ),
    'search_items' => __('Search Modules'),
    'not_found' => __('No Modules found.'),
  );
  register_post_type( 'modules',
    array(
    'menu_position' => 5,
    'public' => true,
    'supports' => array('title', 'editor', 'thumbnail','page-attributes'),
    'labels' => $labels,
    'rewrite' => array('slug' => 'module'),
    )
  );

  // Topics
  $labels = array(
    'name' => __( 'Topics' ),
    'singular_name' => __( 'Topic' ),
    'add_new' => __( 'Add New Topic' ),
    'add_new_item' => __( 'Add New Topic' ),
    'edit_name' => __( 'Edit This Topic' ),
    'view_item' => __( 'View This Topic' ),
    'search_items' => __('Search Topics'),
    'not_found' => __('No Topics found.'),
  );
  register_post_type( 'topics',
    array(
    'menu_position' => 5,
    'public' => true,
    'supports' => array('title'),
    'labels' => $labels,
    'rewrite' => array('slug' => 'topic'),
    )
  );

  // Lectures
  $labels = array(
    'name' => __( 'Lectures' ),
    'singular_name' => __( 'Lecture' ),
    'add_new' => __( 'Add New Lecture' ),
    'add_new_item' => __( 'Add New Lecture' ),
    'edit_name' => __( 'Edit This Lecture' ),
    'view_item' => __( 'View This Lecture' ),
    'search_items' => __('Search Lectures'),
    'not_found' => __('No Lectures found.'),
  );
  register_post_type( 'lectures',
    array(
    'menu_position' => 5,
    'public' => true,
    'supports' => array('title', 'editor', 'thumbnail'),
    'labels' => $labels,
    'rewrite' => array('slug' => 'lecture'),
    )
  );

  // Vocabulary Lesson
  $labels = array(
    'name' => __( 'Vocabulary Lessons' ),
    'singular_name' => __( 'Vocabulary Lesson' ),
    'add_new' => __( 'Add New Vocabulary Lesson' ),
    'add_new_item' => __( 'Add New Vocabulary Lesson' ),
    'edit_name' => __( 'Edit This Vocabulary Lesson' ),
    'view_item' => __( 'View This Vocabulary Lesson' ),
    'search_items' => __('Search Vocabulary Lessons'),
    'not_found' => __('No Vocabulary Lessons found.'),
  );
  register_post_type( 'vocabulary_lessons',
    array(
    'menu_position' => 5,
    'public' => true,
    'supports' => array('title', 'editor', 'thumbnail'),
    'labels' => $labels,
    'rewrite' => array('slug' => 'vocabulary-lesson'),
    )
  );

  // Phrases Lesson
  $labels = array(
    'name' => __( 'Phrases Lessons' ),
    'singular_name' => __( 'Phrases Lesson' ),
    'add_new' => __( 'Add New Phrases Lesson' ),
    'add_new_item' => __( 'Add New Phrases Lesson' ),
    'edit_name' => __( 'Edit This Phrases Lesson' ),
    'view_item' => __( 'View This Phrases Lesson' ),
    'search_items' => __('Search Phrases Lesson'),
    'not_found' => __('No Phrases Lesson found.'),
  );
  register_post_type( 'phrases_lessons',
    array(
    'menu_position' => 5,
    'public' => true,
    'supports' => array('title', 'editor', 'thumbnail'),
    'labels' => $labels,
    'rewrite' => array('slug' => 'phrases-lesson'),
    )
  );

  // Chants Lesson
  $labels = array(
    'name' => __( 'Chants Lessons' ),
    'singular_name' => __( 'Chants Lesson' ),
    'add_new' => __( 'Add New Chants Lesson' ),
    'add_new_item' => __( 'Add New Chants Lesson' ),
    'edit_name' => __( 'Edit This Chants Lesson' ),
    'view_item' => __( 'View This Chants Lesson' ),
    'search_items' => __('Search Chants Lesson'),
    'not_found' => __('No Chants Lesson found.'),
  );
  register_post_type( 'chants_lessons',
    array(
    'menu_position' => 5,
    'public' => true,
    'supports' => array('title', 'editor', 'thumbnail'),
    'labels' => $labels,
    'rewrite' => array('slug' => 'chants-lesson'),
    )
  );

  // Scenes
  $labels = array(
    'name' => __( 'Scenes' ),
    'singular_name' => __( 'Scene' ),
    'add_new' => __( 'Add New Scene' ),
    'add_new_item' => __( 'Add New Scene' ),
    'edit_name' => __( 'Edit This Scene' ),
    'view_item' => __( 'View This Scene' ),
    'search_items' => __('Search Scenes'),
    'not_found' => __('No Scenes found.'),
  );
  register_post_type( 'scenes',
    array(
    'menu_position' => 5,
    'public' => true,
    'supports' => array('title', 'editor', 'thumbnail','page-attributes'),
    'labels' => $labels,
    'rewrite' => array('slug' => 'scene'),
    )
  );

  // Vocabulary Terms
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
    'supports' => array('title', 'editor', 'thumbnail'),
    'labels' => $labels,
    'rewrite' => array('slug' => 'vocabulary'),
    'taxonomies' => array('vocabulary_categories'),
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
}
add_action( 'init', 'BASICHWN_taxonomies');

/*
 * Posts 2 Posts Connections
 */
function BASICHWN_connections() {
  
  /*
   * Core IA Connections
   */
  
  // Connect Modules to Units
  p2p_register_connection_type(array(
    'name' => 'modules_to_units',
    'from' => 'modules',
    'to' => 'units',
    'sortable' => 'any',
    'cardinality' => 'many-to-one', // Many Modules to One Unit
  ));

  // Connect Lessons to Modules
  p2p_register_connection_type(array(
    'name' => 'topics_to_modules',
    'from' => 'topics',
    'to' => 'modules',
    'sortable' => 'any',
    'cardinality' => 'many-to-one', // Many Lessons to One Module
  ));

  /*
   * Assessment IA Connections
   */

  // Connect Lectures to Lessons
  p2p_register_connection_type(array(
    'name' => 'lectures_to_topics',
    'from' => 'lectures',
    'to' => 'topics',
    'sortable' => 'any',
    'cardinality' => 'many-to-one', // Many Lectures to One Module
  ));

  // Connect Vocabulary Practice to Lessons
  p2p_register_connection_type(array(
    'name' => 'vocabulary_lessons_to_topics',
    'from' => 'vocabulary_lessons',
    'to' => 'topics',
    'sortable' => 'any',
    'cardinality' => 'many-to-one', // Many Vocab Games to One Module
  ));
  // Connect Phrases Practice to Lessons
  p2p_register_connection_type(array(
    'name' => 'phrases_lessons_to_topics',
    'from' => 'phrases_lessons',
    'to' => 'topics',
    'sortable' => 'any',
    'cardinality' => 'many-to-one', // Many Pron Practice to One Module
  ));
  // Connect Chants Practice to Lessons
  p2p_register_connection_type(array(
    'name' => 'chants_lessons_to_topics',
    'from' => 'chants_lessons',
    'to' => 'topics',
    'sortable' => 'any',
    'cardinality' => 'many-to-one', // Many Pron Practice to One Module
  ));
  // Connect Vocabulary Terms to Vocabulary Games
  p2p_register_connection_type(array(
    'name' => 'vocabulary_terms_to_vocabulary_lessons',
    'from' => 'vocabulary_terms',
    'to' => 'vocabulary_lessons',
    'sortable' => 'any',
    'cardinality' => 'many-to-one', // Many Vocab Terms to One Module
  ));
}
add_action( 'p2p_init', 'BASICHWN_connections' );

// Load Ajax Game Functions
// IMPORTANT: needs to be loaded after taxonomies if using taxonomies
require( 'lib/user-interactions/assessment/ajax-game-functions.php' );

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

/*
BELOW IS NOT NEEDED AND/OR MAYBE NEEDED IN THE FUTURE


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


*/ 




?>