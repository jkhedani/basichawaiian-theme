<?php

/**
 *	Admin Tweaks
 *	Use this theme add-on (soon to be plugin) to make modifications to admin interfaces.
 *	@todo how do we handle custom post type "editing" - creating and associating should be pretty easy
 *	@todo restrict permissions to view specific pages for particular roles...currently being "hidden" by css.
 *	@todo Probably best to move all admin/login/redirects to this document
 */

/**
 *	Helper Functions
 */
if ( ! function_exists('get_current_user_role') ) :
	function get_current_user_role() { // Where is this being used?!?! Do we need it?!?! Damn it, Justin!
		global $current_user;
		get_currentuserinfo();
		$user_roles = $current_user->roles;
		$user_role = array_shift($user_roles);
		return $user_role;
	};
endif;

/**
 *	Admin Styles
 */
function admin_style() {
    wp_enqueue_style( 'admin-style', get_stylesheet_directory_uri() . '/lib/admin-tweaks/admin-style.css' );
}
add_action('admin_enqueue_scripts', 'admin_style');
add_action('login_enqueue_scripts', 'admin_style');

function admin_class_names($classes) {
	// If user is on the 'admin' side and is not an admin
	// Misleading function name: http://codex.wordpress.org/Function_Reference/is_admin
	if( is_admin() && (!current_user_can('manage_options') || get_current_user_role() == 'instructional_designer')) {
		// add 'class-name' to the $classes array
		$classes .= 'not-admin';
		// return the $classes array
		return $classes;
	} else {
		return $classes;
	}
}
add_filter('admin_body_class','admin_class_names');

// For users that can't edit posts ( students, instructors ), hide admin bar.
function low_level_user_hide_admin_bar() {
  if ( ! current_user_can('edit_posts') ) {
    add_filter('show_admin_bar', '__return_false'); 
  }
}
add_action( 'after_setup_theme', 'low_level_user_hide_admin_bar' );

/**
 *	Toolbar Tweaks
 *	http://codex.wordpress.org/Class_Reference/WP_Admin_Bar
 */
function toolbar_tweaks() {
	global $wp_admin_bar;

	// Remove these menu items (for now)
	$wp_admin_bar->remove_menu( 'search' );
	$wp_admin_bar->remove_menu( 'dashboard' );
	$wp_admin_bar->remove_menu( 'site-name' ); // Re-creating on our own for more control
	$wp_admin_bar->remove_menu( 'wp-logo' );
	$wp_admin_bar->remove_menu( 'comments' );

	// Change "My Sites" title to "My Courses"
	$wp_admin_bar->add_node( array(
		'id' => 'my-sites',
		'title' => 'My Courses' 
	));

	// Hide "My Sites" from users associated with only one course
	$current_user = wp_get_current_user();
	if ( count( get_blogs_of_user( $current_user->ID ) ) == 1 ) {
		$wp_admin_bar->remove_menu( 'my-sites' );		
	}
}
add_action( 'wp_before_admin_bar_render', 'toolbar_tweaks' );

function add_useful_toolbar_menu() {
	global $wp_admin_bar;
	if ( current_user_can('edit_posts') ) {

		if ( !current_user_can('manage_options') ) {
			$location = get_home_url();
		} else {
			if ( is_admin() ) {
				$location = get_home_url();
			} else {
				$location = get_admin_url();
			}
		}
		// Course Name Menu
		$wp_admin_bar->add_menu( array(
			'id' => 'back-to-home',
			'title' => get_bloginfo('name'),
			'meta' => array(),
			'href' => $location,
		));

		// Course Name Menu
		$wp_admin_bar->add_menu( array(
			'id' => 'view-all',
			'title' => 'View All',
			'meta' => array(),
			'href' => $location,
		));
		$postTypes = get_post_types( array(), 'object' );
		foreach ($postTypes as $postType) {
			if ( ($postType->name != 'attachment') || ($postType->name != 'revision') || ($postType->name != 'nav_menu_item') ) :
			// Post Type Menu
			$wp_admin_bar->add_menu( array(
				'parent' => 'view-all',
				'id' => 'site-name-'.$postType->label,
				'meta' => array(),
				'title' => $postType->label,
				'href' => get_admin_url() . 'edit.php?post_type="' .$postType->name. '"',
			));
			endif;
		}

// Options
		$wp_admin_bar->add_menu( array(
			'id' => 'options',
			'title' => 'Options',
			'meta' => array(),
			'href' => admin_url('?page=acf-options'),
		));
		
		// Modify "Howdy in Menu Bar"
		$user_id      = get_current_user_id();
    $current_user = wp_get_current_user();
    $my_url       = 'http://www.google.com';

    if ( ! $user_id )
        return;

    $avatar = get_avatar( $user_id, 16 );
    $howdy  = sprintf( __('Aloha e %1$s'), $current_user->display_name );
    $class  = empty( $avatar ) ? '' : 'with-avatar';

    $wp_admin_bar->add_menu( array(
        'id'        => 'my-account',
        'parent'    => 'top-secondary',
        'title'     => $howdy . $avatar,
        'href'      => $my_url,
        'meta'      => array(
            'class'     => $class,
            'title'     => __('My Account'),
        ),
    ) );

		// // Table of Contents
		// $wp_admin_bar->add_menu( array(
		// 	'parent' => 'course-name',
		// 	'id' => 'course-name-toc',
		// 	'meta' => array(),
		// 	'title' => 'Table of Contents',
		// 	'href' => get_admin_url() . 'admin.php?page=global-sort.php',
		// ));

	}
}
add_action( 'admin_bar_menu', 'add_useful_toolbar_menu', 25 );


/**
 *	Login Redirects
 */

// Redirect logged in student users to the home page instead of the profiles page
// More info: http://codex.wordpress.org/Plugin_API/Filter_Reference/login_redirect
function redirect_instructors_upon_login( $redirect_to, $request, $user ) {
	//is there a user to check?
  global $user;
  if( isset( $user->roles ) && is_array( $user->roles ) ) {
    //check for admins
    if( in_array( "administrator", $user->roles ) ) {
        // redirect them to the default place
        return $redirect_to;
    } else {
        return home_url();
    }
  }
  else {
    return $redirect_to;
  }
}
add_filter( 'login_redirect', 'redirect_instructors_upon_login', 10, 3 );

function low_level_user_redirect_admin() {
  if ( ! current_user_can('edit_posts') ) {
    wp_redirect( site_url() );
    exit;
  }
}
add_action( 'admin_init', 'low_level_user_redirect_admin' );

/**
 *	New User Registration Email Change
 *  http://codex.wordpress.org/Function_Reference/wp_new_user_notification
 */

// Redefine user notification function

function new_welcome_user_msg_filter( $text ) {

	if ( !$text ) {

		return __( 'Dear User,



Your new account is set up.



You can log in with the following information:

Username: USERNAME

Password: PASSWORD

LOGINLINK



Thanks!



--The Team @ SITE_NAME' );

	}

	return $text;

}

add_filter( 'new_user_email_content', 'new_welcome_user_msg_filter' );


?>
