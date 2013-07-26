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
		$postTypes = get_post_types( array( '_builtin' => false ), 'object' );
		foreach ($postTypes as $postType) {
			// Post Type Menu
			$wp_admin_bar->add_menu( array(
				'parent' => 'view-all',
				'id' => 'site-name-'.$postType->label,
				'meta' => array(),
				'title' => $postType->label,
				'href' => get_admin_url() . 'edit.php?post_type="' .$postType->name. '"',
			));	
		}
		
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

	if( property_exists($user, 'roles') && (in_array( 'course_instructor', $user->roles ) || in_array( 'instructional_designer', $user->roles )) ) {
		return home_url();
	} else {
		return $redirect_to;
	}
}
add_filter( 'login_redirect', 'redirect_instructors_upon_login', 10, 3 );

?>