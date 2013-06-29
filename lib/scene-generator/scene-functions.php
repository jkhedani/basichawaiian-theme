<?php

/**
 *  Scene Generator
 *  Description: Core functions to display the appropriate story board
 *  Uses: jStorage (http://www.jstorage.info/)
 *  Requires: json2 & jQuery
 */

/*
 * Split content by More Tags
 * Updated from: http://www.sitepoint.com/split-wordpress-content-into-multiple-sections/
 * NOTE: Needed to update regex as some more tags were not being converted into span tags.
 * This exists as a separate function so that the more tag may be used as intended on other pages.
 */
function split_the_content() {
	global $more;
	$more = true;
	$content = preg_split('/<span id="more-\d+"><\/span>|<!--more-->/i', get_the_content('more'));
	for($c = 0, $csize = count($content); $c < $csize; $c++) {
		$content[$c] = apply_filters('the_content', $content[$c]);
		$content[$c] = filter_links_rel_external( $content[$c] );
	}
	return $content;
}

/*
 * Filter external links and append rel="external"
 */
function filter_links_rel_external( $content ) {
	return preg_replace( '/\<a /i', '<a rel="external" ', $content );
}

/*
 * Scene story board
 */
function check_scene_progress( $queriedPostID ) {
	if ( is_user_logged_in() ) {
		/*
		 * FALL BACK SCENE
		 */
		$selectScene = 219;

		// #1 INTRO SCENE
    //if ( !has_scene_been_viewed( 219 ) ):
   	//   $currentSceneID = 219;
    //endif;

    /**
     *  KUKUI INTRO -  UNCLE IKAIKA
     */
    if ( $queriedPostID ==  203 ):
    	$selectScene = 219;
    endif;
    
    return $selectScene;
  }
}

/**
 * 	Function: Display Scene
 * 	This function runs only if either:
 *  	a) The user is visiting an object with an attached story
 *		b) The user wishes to re-watch a particular scene
 */
function display_scene() {

	// Check if request is valid via nonce
	$nonce = $_REQUEST['nonce'];
	if ( !wp_verify_nonce( $nonce, 'scene_scripts_nonce' ) ) die( __( 'Busted.' ) );

	// Initialize working variables
	$postID = $_REQUEST['postID'];
	$sceneID = "";
	$html = "";
	$success = false;

	// Determine what scene to display
	$sceneID = check_scene_progress( $postID );
	
	// Return proper modal to display
	$sceneSlides = new WP_Query( array(
		'post_type' => 'scenes',
		'p' => $sceneID, 
	));

	while( $sceneSlides->have_posts() ) : $sceneSlides->the_post();
		
		$splitContent = split_the_content();
		$slideCount = count($splitContent); // Plus one to include dynamically generated start slide

		$html .= '<div id="sceneModal" class="modal hide fade" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">';

		// All other slides
		for($i = 0; $i < $slideCount; $i++) {
			// Select the first "modal slide as the initial slide to show"
			$html .= '<div id="modal-slide-'.$i.'" ';
			if ( $i == 0 ) {
				$html .= 'class="modal-slide current-modal-slide"';
			} else {
				$html .= 'class="modal-slide"';
			}
			$html .= '>';	

		  // Modal Slide Content
		  $html .= '	<div class="modal-body">';
	  	$html .= '		<div class="modal-count">'.($i+1).' of '.$slideCount.'</div>';
	  	$html .= 			$splitContent[$i];
		  $html .= ' 	</div>'; // .modal-body
		  
		  // Modal Slide Footer
		  $html .= '	<div class="modal-footer">';
		  // Last slide
		  if ($i == $slideCount - 1) { 
		  $html .= '		<a href="javascript:void(0);" class="btn prev">Prev</a>';
		  $html .= '		<a href="javascript:void(0);" class="btn btn-primary endScene">Done</a>';
		  } else {
		  $html .= '		<a href="javascript:void(0);" class="btn" data-dismiss="modal">Skip Tutorial</a>';
		  $html .= '		<a href="javascript:void(0);" class="btn btn-primary prev">Prev</a>';
		  $html .= '		<a href="javascript:void(0);" class="btn btn-primary next">Next</a>';
		  }
		  $html .= '	</div>'; // .modal-footer
			$html .= '</div>'; // .modal-slide-X
		} // end for (slide iterator)

		$html .= '</div>'; // #sceneModal

	endwhile;
	wp_reset_postdata();

	// Return true in our response
	$success = true;

	// Build response...
	$response = json_encode(array(
		'success' => $success,
		'html' => $html
	));
	
	// Construct and send the response
	header("content-type: application/json");
	echo $response;
	exit;

}
add_action('wp_ajax_nopriv_display_scene', 'display_scene');
add_action('wp_ajax_display_scene', 'display_scene');

/**
 * 	Function: Mark Scene as Viewed
 * 	Marks the scene as viewed.
 */
function mark_scene_viewed() {

	// Check if request is valid via nonce
	$nonce = $_REQUEST['nonce'];
	if ( !wp_verify_nonce( $nonce, 'scene_scripts_nonce' ) ) die( __( 'Busted.' ) );

	// Initialize working variables
	$postID = $_REQUEST['postID'];
	$sceneID = "";
	$html = "";
	$success = false;

	// Determine what scene to display
	$sceneID = check_scene_progress( $postID );

	// Mark the scene as viewed.
	increment_object_value( $sceneID, 'times_viewed' );

	// Return true in our response
	$success = true;

	// Build response...
	$response = json_encode(array(
		'success' => $success,
		'html' => $html
	));
	
	// Construct and send the response
	header("content-type: application/json");
	echo $response;
	exit;

}
add_action('wp_ajax_nopriv_mark_scene_viewed', 'mark_scene_viewed');
add_action('wp_ajax_mark_scene_viewed', 'mark_scene_viewed');

// Run Ajax calls even if user is logged in
// Not sure what we were using this for but it is interfering with our p2p ajax connection calls...
// if ( isset( $_REQUEST['action'] ) ):
// 	do_action( 'wp_ajax_' . $_REQUEST['action'] );
//   do_action( 'wp_ajax_nopriv_' . $_REQUEST['action'] );
// endif;


?>