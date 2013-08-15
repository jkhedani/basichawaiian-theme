<?php

/**
 *  Scene Generator
 *  Description: Core functions to display the appropriate story board
 *  Uses: jStorage (http://www.jstorage.info/)
 *  Requires: json2 & jQuery
 */

/*
 * Scene story board
 */
function check_scene_progress( $queriedPostID ) {
	if ( is_user_logged_in() ) {

		$selectScene = 0;

		// #1 INTRO SCENE (First Time logging in)
    if ( $queriedPostID ==  2 ):
    	$selectScene = 259;
    endif;

    /**
     *  KUKUI INTROS
     */

    // UNCLE IKAIKA
    if ( $queriedPostID ==  203 ):
    	$selectScene = 219;
    endif;
    
    // AUNTY ALOHA 
    if ( $queriedPostID ==  204 ):
    	$selectScene = 289;
    endif;

    /**
     *  EXERCISES
     */

    // Completing BASIC II shows CARDIO I
    if ( $queriedPostID ==  238 ):
    	$selectScene = 262;
    endif;

    // Completing LAU LAU MAKING shows CARDIO II
    //if ( $queriedPostID ==  239 ):
    //	$selectScene = 266;
    //endif;
    
    if ( $selectScene == 0 ) {
    	return false;
    } else {
    	return $selectScene;
    }    
  }
}

/*
 * Is scene viewed?
 */
function scene_viewed( $postID ) {
	$sceneID = check_scene_progress( $postID ); // Find associated scene
	$sceneViewed = true;
	if ( $sceneID ) {
		$sceneRecord =  get_object_record( $sceneID );
		if ( $sceneRecord[0]->times_viewed < 1 ) {
			$sceneViewed = false;
		}
	}
	return $sceneViewed;
}

/**
 * 	Function: Display Scene
 * 	This function runs only if either:
 *  	a) The user is visiting an object with an attached story
 *		b) The user wishes to re-watch a particular scene
 */

// function split_the_content_before_filter() {
//   global $more;
//   $more = true;
//   $content = preg_split('/<span id="more-\d+"><\/span>|<!--more-->/i', get_the_content());
//   error_log(print_r($content,true));
//   for($c = 0, $csize = count($content); $c < $csize; $c++) {
//     $content[$c] = apply_filters('the_content', $content[$c]);
//     $content[$c] = filter_links_rel_external( $content[$c] );
//   }
//   return $content;
// }

function display_scene() {

	// Check if request is valid via nonce
	$nonce = $_REQUEST['nonce'];
	if ( !wp_verify_nonce( $nonce, 'scene_scripts_nonce' ) ) die( __( 'Busted.' ) );

	// Determine what scene to display
	$postID = $_REQUEST['postID'];
	$sceneID = check_scene_progress( $postID );

	// Ensure a scene is available to be presented
	if ( $sceneID && !is_object_complete($sceneID) ) {
		// Initialize working variables
		$html = "";
		$success = false;


		// Return proper modal to display
		$sceneSlides = new WP_Query( array( 'post_type' => 'scenes', 'p' => $sceneID, ));
		while( $sceneSlides->have_posts() ) : $sceneSlides->the_post();

			do_action('init');
			global $post;

			$sceneSlideObject = get_field('scene_slide');
			$slideCount = count($sceneSlideObject);

			$html .= '<div id="sceneModal" class="modal hide fade" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">';

			$html .= '<div class="modal-count">';
			$html .= '<div class="modal-count-wrap">';
			  foreach ($sceneSlideObject as $token) {
			  	$html .= '<span class="modal-counter"></span>';
			  }
		  $html .= '</div>';
		  $html .= '</div>';
	  	//$html .= '		<div class="modal-count">'.($i+1).' of '.$slideCount.'</div>';

			// All other slides
			for($i = 0; $i < $slideCount; $i++) {

				$slide = $sceneSlideObject[$i];

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
			  $html .= '		<h1>'.$slide['scene_title'].'</h1>';
		  	$html .= '		<div class="scene-content">'.$slide['scene_content'].'</div>';
			  $html .= ' 	</div>'; // .modal-body
			  
			  // Modal Slide Footer
			  $html .= '	<div class="modal-footer">';
			 	$html .= '   <div class="scene-caption">'.$slide['scene_caption'].'</div>';

			  // Last slide
			  if ($i == $slideCount - 1) { 
			  $html .= '		<a href="javascript:void(0);" class="btn prev">Prev</a>';
			  $html .= '		<a href="javascript:void(0);" class="btn btn-primary end-scene" data-dismiss="modal">Done</a>';
			  } else {
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
	
	} else {
		// If no scene is to be presented, return nothing.
		$success = false;
		$html = "";
	}

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


function mark_scene_complete() {
	// Check if request is valid via nonce
	$nonce = $_REQUEST['nonce'];
	if ( !wp_verify_nonce( $nonce, 'scene_scripts_nonce' ) ) die( __( 'Busted.' ) );
	$success = false;

	// Determine story post ID based on current query
	$postID = $_REQUEST['postID'];
	$sceneID = check_scene_progress( $postID );

	// Mark scene as "complete" 
	increment_object_value( $sceneID, 'times_completed' );	
	$success = true;

	// Build response...
	$response = json_encode(array(
		'success' => $success,
	));
	
	// Construct and send the response
	header("content-type: application/json");
	echo $response;
	exit;

}
add_action('wp_ajax_nopriv_mark_scene_complete', 'mark_scene_complete');
add_action('wp_ajax_mark_scene_complete', 'mark_scene_complete');


// Run Ajax calls even if user is logged in
// MAYBE NOT RELEVANT: Not sure what we were using this for but it is interfering with our p2p ajax connection calls...
// Attempting specificity in this request to maybe prevent conflicts with other requests
if ( ( isset($_REQUEST['action']) && ($_REQUEST['action']=='display_scene') ) || ( isset($_REQUEST['action']) && ($_REQUEST['action']=='mark_scene_viewed') ) || ( isset($_REQUEST['action']) && ($_REQUEST['action']=='mark_scene_complete') ) ):
	do_action( 'wp_ajax_' . $_REQUEST['action'] );
  do_action( 'wp_ajax_nopriv_' . $_REQUEST['action'] );
endif;





























/**
 * 	Function: Mark Scene as Viewed
 * 	Marks the scene as viewed.
 *	Verify that we need this function at a later time.
 */
function mark_scene_viewed() {

	// Check if request is valid via nonce
	$nonce = $_REQUEST['nonce'];
	if ( !wp_verify_nonce( $nonce, 'scene_scripts_nonce' ) ) die( __( 'Busted.' ) );

	// Determine what scene to display
	$postID = $_REQUEST['postID'];
	$sceneID = check_scene_progress( $postID );

	// Ensure a scene is available to be presented
	if ( $sceneID ) {

		$html = "";
		$success = false;

		// Mark the scene as viewed.
		increment_object_value( $sceneID, 'times_viewed' );

		// Return true in our response
		$success = true;

	} else {
		$html = "";
		$success = false;
	}

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



?>