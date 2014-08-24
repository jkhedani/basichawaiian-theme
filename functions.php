<?php

/**
 *  Global Helper Functions
 */

/*
 * Split content by More Tags
 * Updated from: http://www.sitepoint.com/split-wordpress-content-into-multiple-sections/
 * NOTE: Needed to update regex as some more tags were not being converted into span tags.
 * This exists as a separate function so that the more tag may be used as intended on other pages.
 */
function split_the_content( $unfilteredcontent ) {
  global $more;
  $more = true;
  $content = preg_split('/<span id="more-\d+"><\/span>|<!--more-->/i', $unfilteredcontent);
  error_log(print_r($content,true));
  for($c = 0, $csize = count($content); $c < $csize; $c++) {
    $content[$c] = apply_filters('the_content', $content[$c]);
    $content[$c] = filter_links_rel_external( $content[$c] );
  }
  return $content;
}

// http://codex.wordpress.org/Function_Reference/get_the_excerpt
function the_excerpt_max_charlength ( $charlength ) {
  $excerpt = get_the_excerpt();
  $charlength++;

  if ( mb_strlen( $excerpt ) > $charlength ) {
    $subex = mb_substr( $excerpt, 0, $charlength - 5 );
    $exwords = explode( ' ', $subex );
    $excut = - ( mb_strlen( $exwords[ count( $exwords ) - 1 ] ) );
    if ( $excut < 0 ) {
      echo mb_substr( $subex, 0, $excut );
    } else {
      echo $subex;
    }
    echo '...';
  } else {
    echo $excerpt;
  }
}

/*
 * Filter external links and append rel="external"
 */
function filter_links_rel_external( $content ) {
  return preg_replace( '/\<a /i', '<a rel="external" ', $content );
}

// Load Tweaks
require( 'lib/tweaks.php' );

// Load Admin Tweaks
require( 'lib/admin-tweaks/admin-tweaks.php' );

// Load User Interactions Functions
require( 'lib/user-interactions/user-interactions-functions.php' );

// Load User Profile Functions
require( 'lib/user-interactions/profile/user-profile-functions.php' );


/**
 * Custom Body Classes
 */
function custom_body_classes( $classes ) {
  // Adds a class of group-blog to blogs with more than 1 published author
  if ( ! is_user_logged_in() ) {
    $classes[] = 'not-logged-in';
  }
  global $post;

  // Instantiate body classes for kukuis and their descendants
  $auntyAlohaID = '204';
  $auntyAlohaDescendants = get_connected_descendants(
    $auntyAlohaID,
    'modules_to_units',
    'topics_to_modules',
    array(
      'instructional_lessons_to_topics',
      'video_lessons_to_topics',
      'listen_repeat_lessons_to_topics',
      'readings_to_topics',
      'vocabulary_lessons_to_topics',
      'phrases_lessons_to_topics',
      'pronoun_lessons_to_topics',
      'song_lessons_to_topics',
      'chants_lessons_to_topics',
    )
  );
  foreach ( $auntyAlohaDescendants as $auntyAlohaDescendant ) {
    if ( $auntyAlohaDescendant == $post->ID ) {
      $classes[] = 'aunty-aloha';
    }
  }
  if ( $auntyAlohaID == $post->ID )  {
    $classes[] = 'aunty-aloha';
  }

  return $classes;
}
add_filter( 'body_class', 'custom_body_classes' );


// On theme activation, do the following...
function course_theme_activate_enable_roles($old_name, $old_theme = false) {

  // Role: Editor (default)
  remove_role("editor");

  // Role: Author (default)
  remove_role("author");

  // Role: Contributor (default)
  remove_role("contributor");

  // Role: Subscriber (default)
  remove_role("subscriber");

  // Role: Instructional Designer (based on Editor)
  add_role('instructional_designer', 'Instructional Designer', array(
    // Administrator permissions:
    'create_users' => true,
    'delete_users' => true,
    'edit_users' => true,
    'list_users' => true,
    'remove_users' => true,
    //'promote_users' => true,
    'edit_dashboard' => true,
    'manage_options' => true,
    'edit_theme_options' => true,

    // Editor permissions:
    'moderate_comments' => true,
    'manage_categories' => true,
    'manage_links' => true,
    'edit_others_posts' => true,
    'edit_pages' => true,
    'edit_others_pages' => true,
    'edit_published_pages' => true,
    'publish_pages' => true,
    'delete_pages' => true,
    'delete_others_pages' => true,
    'delete_published_pages' => true,
    'delete_others_posts' => true,
    'delete_private_posts' => true,
    'edit_private_posts' => true,
    'read_private_posts' => true,
    'delete_private_pages' => true,
    'edit_private_pages' => true,
    'read_private_pages' => true,

    // Author permissions:
    'edit_published_posts' => true,
    'upload_files' => true,
    'publish_posts' => true,
    'delete_published_posts' => true,

    // Contributor permissions:
    'edit_posts' => true,
    'delete_posts' => true,

    // Subscriber permissions:
    'read' => true,
  ));

  // Role: Student (based on Subscriber)
  add_role('student', 'Student', array(
    // Subscriber permissions:
    'read' => true,
  ));
}
add_action("after_switch_theme", "course_theme_activate_enable_roles", 10, 2);

// On theme deactivation, do the following...
function course_theme_deactivate_disable_roles($newname, $newtheme) {
  // Role: Editor (default)
  add_role('editor', 'Editor', array(
    // Editor permissions:
    'moderate_comments' => true,
    'manage_categories' => true,
    'manage_links' => true,
    'edit_others_posts' => true,
    'edit_pages' => true,
    'edit_others_pages' => true,
    'edit_published_pages' => true,
    'publish_pages' => true,
    'delete_pages' => true,
    'delete_others_pages' => true,
    'delete_published_pages' => true,
    'delete_others_posts' => true,
    'delete_private_posts' => true,
    'edit_private_posts' => true,
    'read_private_posts' => true,
    'delete_private_pages' => true,
    'edit_private_pages' => true,
    'read_private_pages' => true,

    // Author permissions:
    'edit_published_posts' => true,
    'upload_files' => true,
    'publish_posts' => true,
    'delete_published_posts' => true,

    // Contributor permissions:
    'edit_posts' => true,
    'delete_posts' => true,

    // Subscriber permissions:
    'read' => true,
  ));

  // Role: Author (default)
  add_role('author', 'Author', array(
    // Author permissions:
    'edit_published_posts' => true,
    'upload_files' => true,
    'publish_posts' => true,
    'delete_published_posts' => true,

    // Contributor permissions:
    'edit_posts' => true,
    'delete_posts' => true,

    // Subscriber permissions:
    'read' => true,
  ));

  // Role: Contributor (default)
  add_role('contributor', 'Contributor', array(
    // Contributor permissions:
    'edit_posts' => true,
    'delete_posts' => true,

    // Subscriber permissions:
    'read' => true,
  ));

  // Role: Subscriber (default)
  add_role('subscriber', 'Subscriber', array(
    // Subscriber permissions:
    'read' => true,
  ));

  // Role: Student (based on Subscriber)
  remove_role('student');
}
add_action("switch_theme", "course_theme_deactivate_disable_roles", 10 , 2);



/**
 * Properly add new script files using this function.
 * http://codex.wordpress.org/Plugin_API/Action_Reference/wp_enqueue_scripts
 */
function diamond_scripts() {
	$stylesheetDir = get_stylesheet_directory_uri();
	$protocol='http:'; // discover the correct protocol to use
 	if(!empty($_SERVER['HTTPS'])) $protocol='https:';

  wp_enqueue_style( 'open-sans-google-fonts', '//fonts.googleapis.com/css?family=Open+Sans:400italic,400,600,700', array(), false, 'all');
  wp_enqueue_style( 'raleway-google-fonts', '//fonts.googleapis.com/css?family=Raleway:200,700', array(), false, 'all');
  wp_enqueue_style( 'font-awesome-icons', '//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css', array(), false, 'all');
  wp_enqueue_style( 'diamond-style', get_stylesheet_directory_uri().'/css/diamond-style.css' );
	wp_enqueue_script( 'bootstrap-modal', get_template_directory_uri().'/inc/bootstrap/js/bootstrap-modal.js', array('jquery'), false, true);
  wp_enqueue_script( 'bootstrap-carousel', get_template_directory_uri().'/inc/bootstrap/js/bootstrap-carousel.js', array('jquery'), false, true);
  wp_enqueue_script( 'bootstrap-tooltip', get_template_directory_uri().'/inc/bootstrap/js/bootstrap-tooltip.js', array('jquery'), false, true);
  wp_enqueue_script( 'bootstrap-popover', get_template_directory_uri().'/inc/bootstrap/js/bootstrap-popover.js', array('jquery', 'bootstrap-tooltip'), false, true);
  wp_enqueue_script( 'bootstrap-dropdown', get_template_directory_uri().'/inc/bootstrap/js/bootstrap-dropdown.js', array('jquery'), false, true);
  wp_enqueue_script( 'chart-script', get_stylesheet_directory_uri().'/js/chart/Chart.min.js', array(), false, true);
  wp_enqueue_script( 'diamond-custom-script', get_stylesheet_directory_uri().'/js/scripts.js', array('jquery'), false, true);

	// AJAX Calls
  wp_enqueue_script('ajax_scripts', "$stylesheetDir/lib/user-interactions/assessment/ajax-game-scripts.js", array('jquery','json2','bootstrap-popover'), true);
	wp_localize_script('ajax_scripts', 'ajax_scripts', array(
		'ajaxurl' => admin_url('admin-ajax.php',$protocol),
		'nonce' => wp_create_nonce('ajax_scripts_nonce')
	));

  // Scene Generation Scripts
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

// Basic Hawaiian Menu Locations
function BASICHWN_designate_menu_locations() {
  register_nav_menu( 'public-menu', __( 'Public Menu' ) );
  register_nav_menu( 'footer-menu', __( 'Footer Menu' ) );
}
add_action( 'init', 'BASICHWN_designate_menu_locations' );

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
  //remove_menu_page('edit.php'); // "Posts"
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

  // Move

  // Add menu separators
  add_admin_menu_separator(10);
  add_admin_menu_separator(15);
  add_admin_menu_separator(25);
}
add_action( 'admin_menu', 'remove_admin_menu_items' );


// Basic Hawaiian Custom Post Types
function BASICHWN_post_types() {

  /**
   * Structure
   */

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
    'menu_position' => 6,
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
    'menu_position' => 7,
    'public' => true,
    'supports' => array('title'),
    'labels' => $labels,
    'rewrite' => array('slug' => 'topic'),
    )
  );

  /**
   * Custom/AUX Post Types
   */
  // Testimonials
  $labels = array(
    'name' => __( 'Testimonials' ),
    'singular_name' => __( 'Testimonials' ),
    'add_new' => __( 'Add New Testimonial' ),
    'add_new_item' => __( 'Add New Testimonial' ),
    'edit_name' => __( 'Edit This Testimonial' ),
    'view_item' => __( 'View This Testimonial' ),
    'search_items' => __('Search Testimonials'),
    'not_found' => __('No Testimonials found.'),
  );
  register_post_type( 'testimonials',
    array(
    'menu_position' => 8,
    'public' => true,
    'supports' => array( 'title', 'editor', 'thumbnail' ),
    'labels' => $labels,
    'rewrite' => array('slug' => 'testimonials'),
    )
  );

  /**
   * Instructional Lessons
   */

  // Instructional Lessons
  $labels = array(
    'name' => __( 'Instructional Lessons' ),
    'singular_name' => __( 'Instructional Lesson' ),
    'add_new' => __( 'Add New Instructional Lesson' ),
    'add_new_item' => __( 'Add New Instructional Lesson' ),
    'edit_name' => __( 'Edit This Instructional Lesson' ),
    'view_item' => __( 'View This Instructional Lesson' ),
    'search_items' => __('Search Instructional Lessons'),
    'not_found' => __('No Instructional Lessons found.'),
  );
  register_post_type( 'instruction_lessons',
    array(
    'menu_position' => 11,
    'public' => true,
    'supports' => array( 'title' ),
    'labels' => $labels,
    'rewrite' => array('slug' => 'instruction-lessons'),
    )
  );

  // Video Lessons
  $labels = array(
    'name' => __( 'Video Lessons' ),
    'singular_name' => __( 'Video Lesson' ),
    'add_new' => __( 'Add New Video Lesson' ),
    'add_new_item' => __( 'Add New Video Lesson' ),
    'edit_name' => __( 'Edit This Video Lesson' ),
    'view_item' => __( 'View This Video Lesson' ),
    'search_items' => __('Search Video Lessons'),
    'not_found' => __('No Video Lessons found.'),
  );
  register_post_type( 'video_lessons',
    array(
    'menu_position' => 11,
    'public' => true,
    'supports' => array( 'title' ),
    'labels' => $labels,
    'rewrite' => array('slug' => 'video-lessons'),
    )
  );

  // Listen and Repeat Lessons
  $labels = array(
    'name' => __( 'Listen and Repeat Lessons' ),
    'singular_name' => __( 'Listen and Repeat Lesson' ),
    'add_new' => __( 'Add New Listen and Repeat Lesson' ),
    'add_new_item' => __( 'Add New Listen and Repeat Lesson' ),
    'edit_name' => __( 'Edit This Listen and Repeat Lesson' ),
    'view_item' => __( 'View This Listen and Repeat Lesson' ),
    'search_items' => __('Search Listen and Repeat Lessons'),
    'not_found' => __('No Listen and Repeat Lessons found.'),
  );
  register_post_type( 'listenrepeat_lessons',
    array(
    'menu_position' => 11,
    'public' => true,
    'supports' => array( 'title' ),
    'labels' => $labels,
    'rewrite' => array('slug' => 'listen-repeat-lessons'),
    )
  );

  // Readings
  $labels = array(
    'name' => __( 'Readings' ),
    'singular_name' => __( 'Reading' ),
    'add_new' => __( 'Add New Reading' ),
    'add_new_item' => __( 'Add New Reading' ),
    'edit_name' => __( 'Edit This Reading' ),
    'view_item' => __( 'View This Reading' ),
    'search_items' => __('Search Readings'),
    'not_found' => __('No Readings found.'),
  );
  register_post_type( 'readings',
    array(
    'menu_position' => 11,
    'public' => true,
    'supports' => array('title', 'editor'),
    'labels' => $labels,
    'rewrite' => array('slug' => 'reading'),
    )
  );

  /**
   * Instructional Objects
   */

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
    'menu_position' => 16,
    'public' => true,
    'supports' => array('title', 'editor', 'thumbnail'),
    'labels' => $labels,
    'rewrite' => array('slug' => 'vocabulary'),
    'taxonomies' => array('vocabulary_categories'),
    )
  );

  // Phrases
  $labels = array(
    'name' => __( 'Phrases' ),
    'singular_name' => __( 'Phrase' ),
    'add_new' => __( 'Add New Phrase' ),
    'add_new_item' => __( 'Add New Phrase' ),
    'edit_name' => __( 'Edit This Phrase' ),
    'view_item' => __( 'View This Phrase' ),
    'search_items' => __('Search Phrases'),
    'not_found' => __('No Phrases found.'),
  );
  register_post_type( 'phrases',
    array(
    'menu_position' => 16,
    'public' => true,
    'supports' => array('title', 'editor', 'thumbnail'),
    'labels' => $labels,
    'rewrite' => array('slug' => 'phrase'),
    )
  );

  // Pronoun
  $labels = array(
    'name' => __( 'Pronouns' ),
    'singular_name' => __( 'Pronoun' ),
    'add_new' => __( 'Add New Pronoun' ),
    'add_new_item' => __( 'Add New Pronoun' ),
    'edit_name' => __( 'Edit This Pronoun' ),
    'view_item' => __( 'View This Pronoun' ),
    'search_items' => __('Search Pronouns'),
    'not_found' => __('No Pronouns found.'),
  );
  register_post_type( 'pronouns',
    array(
    'menu_position' => 16,
    'public' =>true,
    'supports' => array('title', 'editor', 'thumbnail'),
    'labels' => $labels,
    'rewrite' => array('slug' => 'pronoun'),

    // Hide Pronouns as they are not in use
    'show_ui' => false,

    )
  );

  // Song Lines
  $labels = array(
    'name' => __( 'Song Lines' ),
    'singular_name' => __( 'Song Line' ),
    'add_new' => __( 'Add New Song Line' ),
    'add_new_item' => __( 'Add New Song Line' ),
    'edit_name' => __( 'Edit This Song Line' ),
    'view_item' => __( 'View This Song Line' ),
    'search_items' => __('Search Song Lines'),
    'not_found' => __('No Song Lines found.'),
  );
  register_post_type( 'song_lines',
    array(
    'menu_position' => 16,
    'public' => true,
    'supports' => array('title'),
    'labels' => $labels,
    'rewrite' => array('slug' => 'song-line'),
    )
  );

  // Chant Lines
  $labels = array(
    'name' => __( 'Chant Lines' ),
    'singular_name' => __( 'Chant Line' ),
    'add_new' => __( 'Add New Chant Line' ),
    'add_new_item' => __( 'Add New Chant Line' ),
    'edit_name' => __( 'Edit This Chant Line' ),
    'view_item' => __( 'View This Chant Line' ),
    'search_items' => __('Search Chant Lines'),
    'not_found' => __('No Chant Lines found.'),
  );
  register_post_type( 'chant_lines',
    array(
    'menu_position' => 16,
    'public' => true,
    'supports' => array('title'),
    'labels' => $labels,
    'rewrite' => array('slug' => 'chant-line'),
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
    'menu_position' => 16,
    'public' => true,
    'supports' => array('title', 'editor', 'thumbnail','page-attributes'),
    'labels' => $labels,
    'rewrite' => array('slug' => 'scene'),
    )
  );

  /**
   * Assessment Objects
   */

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
    'menu_position' => 25,
    'public' => true,
    'supports' => array('title'),
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
    'menu_position' => 25,
    'public' => true,
    'supports' => array('title'),
    'labels' => $labels,
    'rewrite' => array('slug' => 'phrases-lesson'),
    )
  );

  // Pronoun Lesson
  $labels = array(
    'name' => __( 'Pronoun Lessons' ),
    'singular_name' => __( 'Pronoun Lesson' ),
    'add_new' => __( 'Add New Pronoun Lesson' ),
    'add_new_item' => __( 'Add New Pronoun Lesson' ),
    'edit_name' => __( 'Edit This Pronoun Lesson' ),
    'view_item' => __( 'View This Pronoun Lesson' ),
    'search_items' => __('Search Pronoun Lesson'),
    'not_found' => __('No Pronoun Lesson found.'),
  );
  register_post_type( 'pronoun_lessons',
    array(
    'menu_position' => 25,
    'public' => true,
    'supports' => array('title'),
    'labels' => $labels,
    'rewrite' => array('slug' => 'pronoun-lesson'),
    )
  );

  // Songs Lesson
  $labels = array(
    'name' => __( 'Song Lessons' ),
    'singular_name' => __( 'Song Lesson' ),
    'add_new' => __( 'Add New Song Lesson' ),
    'add_new_item' => __( 'Add New Song Lesson' ),
    'edit_name' => __( 'Edit This Song Lesson' ),
    'view_item' => __( 'View This Song Lesson' ),
    'search_items' => __('Search Song Lessons'),
    'not_found' => __('No Song Lesson found.'),
  );
  register_post_type( 'song_lessons',
    array(
    'menu_position' => 25,
    'public' => true,
    'supports' => array('title'),
    'labels' => $labels,
    'rewrite' => array('slug' => 'songs-lesson'),
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
    'menu_position' => 25,
    'public' => true,
    'supports' => array('title'),
    'labels' => $labels,
    'rewrite' => array('slug' => 'chants-lesson'),
    )
  );

}
add_action( 'init', 'BASICHWN_post_types' );

// Basic Hawaiian Custom Taxonomies
function BASICHWN_taxonomies() {

  $labels = array(
    'name' => _x( 'Instructional Categories', 'taxonomy general name' ),
    'singular_name' => _x( 'Instructional Category', 'taxonomy singular name' ),
    'search_items' =>  __( 'Search Instructional Categories' ),
    'all_items' => __( 'All Instructional Categories' ),
    'parent_item' => __( 'Parent Instructional Category' ),
    'parent_item_colon' => __( 'Parent Instructional Category:' ),
    'edit_item' => __( 'Edit Instructional Category' ),
    'update_item' => __( 'Update Instructional Category' ),
    'add_new_item' => __( 'Add New Instructional Category' ),
    'new_item_name' => __( 'New Instructional Category Name' ),
    'menu_name' => __( ' Edit Instructional Categories' ),
  );

  register_taxonomy('instructional_lesson_categories',array('instruction_lessons'), array(
    'hierarchical' => true,
    'labels' => $labels,
    'show_ui' => true,
    'query_var' => true,
    //'rewrite' => array( 'slug' => 'vocabulary-categories' ),
  ));

  // Add new taxonomy, make it hierarchical (like categories)
  // $labels = array(
  //   'name' => _x( 'Vocabulary Categories', 'taxonomy general name' ),
  //   'singular_name' => _x( 'Vocabulary Category', 'taxonomy singular name' ),
  //   'search_items' =>  __( 'Search Vocabulary Categories' ),
  //   'all_items' => __( 'All Vocabulary Categories' ),
  //   'parent_item' => __( 'Parent Vocabulary Category' ),
  //   'parent_item_colon' => __( 'Parent Vocabulary Category:' ),
  //   'edit_item' => __( 'Edit Vocabulary Category' ),
  //   'update_item' => __( 'Update Vocabulary Category' ),
  //   'add_new_item' => __( 'Add New Vocabulary Category' ),
  //   'new_item_name' => __( 'New Vocabulary Category Name' ),
  //   'menu_name' => __( ' Edit Vocabulary Categories' ),
  // );

  // register_taxonomy('vocabulary_categories',array('vocabulary_terms'), array(
  //   'hierarchical' => true,
  //   'labels' => $labels,
  //   'show_ui' => true,
  //   'query_var' => true,
  //   'rewrite' => array( 'slug' => 'vocabulary-categories' ),
  // ));

  // // Add new taxonomy, make it hierarchical (like categories)
  // $labels = array(
  //   'name' => _x( 'Difficulty Levels', 'taxonomy general name' ),
  //   'singular_name' => _x( 'Difficulty Level', 'taxonomy singular name' ),
  //   'search_items' =>  __( 'Search Difficulty Levels' ),
  //   'all_items' => __( 'All Difficulty Levels' ),
  //   'parent_item' => __( 'Parent Difficulty Level' ),
  //   'parent_item_colon' => __( 'Parent Difficulty Level:' ),
  //   'edit_item' => __( 'Edit Difficulty Level' ),
  //   'update_item' => __( 'Update Difficulty Level' ),
  //   'add_new_item' => __( 'Add New Difficulty Level' ),
  //   'new_item_name' => __( 'New Difficulty Level Name' ),
  //   'menu_name' => __( 'Edit Difficulty Levels' ),
  // );

  // register_taxonomy('difficulty_level',array('vocabulary_terms','post'), array(
  //   'hierarchical' => true,
  //   'labels' => $labels,
  //   'show_ui' => true,
  //   'query_var' => true,
  //   //'rewrite' => array( 'slug' => 'genre' ),
  // ));
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
  // Connect Instructional Lessons to Topics
  p2p_register_connection_type(array(
    'name' => 'instructional_lessons_to_topics',
    'from' => 'instruction_lessons',
    'to' => 'topics',
    'sortable' => 'any',
    'cardinality' => 'many-to-one', // Many Lectures to One Module
  ));
  // Connect Video Lessons to Topics
  p2p_register_connection_type(array(
    'name' => 'video_lessons_to_topics',
    'from' => 'video_lessons',
    'to' => 'topics',
    'sortable' => 'any',
    'cardinality' => 'many-to-one', // Many Lectures to One Module
  ));
  // Connect Listen and Repeat Lessons to Topics
  p2p_register_connection_type(array(
    'name' => 'listen_repeat_lessons_to_topics',
    'from' => 'listenrepeat_lessons',
    'to' => 'topics',
    'sortable' => 'any',
    'cardinality' => 'many-to-one', // Many Lectures to One Module
  ));
  // Connect Readings to Topics
  p2p_register_connection_type(array(
    'name' => 'readings_to_topics',
    'from' => 'readings',
    'to' => 'topics',
    'sortable' => 'any',
    'cardinality' => 'many-to-one', // Many Readings to One Module
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
  // Connect Pronouns to Topics
  p2p_register_connection_type(array(
    'name' => 'pronoun_lessons_to_topics',
    'from' => 'pronoun_lessons',
    'to' => 'topics',
    'sortable' => 'any',
    'cardinality' => 'many-to-one', // Many Pron Practice to One Module
  ));
  // Connect Song Lessons to Topics
  p2p_register_connection_type(array(
    'name' => 'song_lessons_to_topics',
    'from' => 'song_lessons',
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

  // Connect Vocabulary Terms to Vocabulary Lessons
  p2p_register_connection_type(array(
    'name' => 'vocabulary_terms_to_vocabulary_lessons',
    'from' => 'vocabulary_terms',
    'to' => 'vocabulary_lessons',
    'sortable' => 'any',
    'cardinality' => 'many-to-one', // Many Vocab Terms to One Module
  ));
  // Connect Phrases to Phrases Lessons
  p2p_register_connection_type(array(
    'name' => 'phrases_to_phrases_lessons',
    'from' => 'phrases',
    'to' => 'phrases_lessons',
    'sortable' => 'any',
    'cardinality' => 'many-to-one', // Many Phrases to One Lesson
  ));
  // Connect Pronouns to Pronoun Lessons
  p2p_register_connection_type(array(
    'name' => 'pronouns_to_pronoun_lessons',
    'from' => 'pronouns',
    'to' => 'pronoun_lessons',
    'sortable' => 'any',
    'cardinality' => 'many-to-one', // Many Phrases to One Lesson
  ));
  // Connect Song Lines to Song Lessons
  p2p_register_connection_type(array(
    'name' => 'song_lines_to_song_lessons',
    'from' => 'song_lines',
    'to' => 'song_lessons',
    'sortable' => 'any',
    'cardinality' => 'many-to-one', // Many Phrases to One Lesson
  ));
  // Connect Chant Lines to Chant Lessons
  p2p_register_connection_type(array(
    'name' => 'chant_lines_to_chant_lessons',
    'from' => 'chant_lines',
    'to' => 'chants_lessons',
    'sortable' => 'any',
    'cardinality' => 'many-to-one', // Many Phrases to One Lesson
  ));

  // Connect Vocabulary to Phrases
  p2p_register_connection_type(array(
    'name' => 'vocabulary_terms_to_phrases',
    'from' => 'vocabulary_terms',
    'to' => 'phrases',
    'sortable' => 'any',
    'cardinality' => 'one-to-many', // Many Phrases to One Lesson
  ));
  // Connect Phrases to Listen Repeat Lessons
  p2p_register_connection_type(array(
    'name' => 'phrases_to_listen_repeat_lessons',
    'from' => 'phrases',
    'to' => 'listenrepeat_lessons',
    'sortable' => 'any',
    'cardinality' => 'many-to-one', // Many Phrases to One Lesson
  ));

  /**
   *  Scene Selection Connections
   */
  p2p_register_connection_type(array(
    'name' => 'scenes_to_pages',
    'from' => 'scenes',
    'to' => 'page',
    'sortable' => 'any',
    'cardinality' => 'many-to-many',
    'fields' => array(
      'gender' => array(
        'title' => 'Gender',
        'type' => 'select',
        'values' => array( 'male', 'female', 'both' )
      ),
    ),
    'title' => array(
      'from' => __('Pages to Connect Scenes to', 'basichawaiian'),
      'to' => __('Connected Scenes', 'basichawaiian'),
    ),
  ));
  p2p_register_connection_type(array(
    'name' => 'scenes_to_units',
    'from' => 'scenes',
    'to' => 'units',
    'sortable' => 'any',
    'cardinality' => 'many-to-many',
    'fields' => array(
      'gender' => array(
        'title' => 'Gender',
        'type' => 'select',
        'values' => array( 'male', 'female', 'both' )
      ),
    ),
    'title' => array(
      'from' => __('Units to Connect Scenes to', 'basichawaiian'),
      'to' => __('Connected Scenes', 'basichawaiian'),
    ),
  ));

}
add_action( 'p2p_init', 'BASICHWN_connections' );

// Load Ajax Game Functions
// IMPORTANT: needs to be loaded after taxonomies if using taxonomies
require( 'lib/user-interactions/assessment/ajax-game-functions.php' );

/**
 * Load Scene Generator Functions
 */
require( 'lib/scene-generator/scene-functions.php' );

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


/**
 * Add JSON API Controllers for Exporter
 * @source http://wordpress.org/plugins/json-api/other_notes/#5.1.-Plugin-hooks
 */
function add_exporter_controller($controllers) {
  $controllers[] = 'exporter';
  return $controllers;
}
add_filter('json_api_controllers', 'add_exporter_controller');

function set_exporter_controller_path() {
  return get_stylesheet_directory() . "/lib/json-api-controllers/exporter.php";
}
add_filter('json_api_exporter_controller_path', 'set_exporter_controller_path');
