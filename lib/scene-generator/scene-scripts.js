/**
 *  Displays latest "Start Here" modal
 */
function displayScene(postID) {

	// Request data for modal from the server via Ajax.
	jQuery.post(scene_scripts.ajaxurl, {
	  action: 'display_scene',
	  nonce: scene_scripts.nonce,
	  postID: postID, 
	}, function( response ) {
		// Do not announce json as data type and just parse response as json
		// http://www.garyc40.com/2010/03/5-tips-for-using-ajax-in-wordpress/
	  // Intelliguess seems to interpret the data for us as JSON
	  // http://api.jquery.com/jQuery.ajax/

	  if ( response.success === true ) {
	  	// To prevent multiple start here modals from being generated, remove any
	  	// existing start here modal upon success
	  	jQuery('#sceneModal').remove();
	  	// Generate the modal content and place each modal at the bottom of the markup
	  	jQuery('body').append(response.html);
			// Initialize the modal on the page if it exists.
			jQuery('#sceneModal').modal();

			// Clicking on the 'next' button will hide the current modal and show the next until complete
			jQuery('#sceneModal .next').on('click',function(){
				var currentModal = jQuery(this).parents('.modal-slide').attr('id');
				var nextModal = jQuery(this).parents('.modal-slide').next().attr('id');
				jQuery('#'+currentModal).removeClass('current-modal-slide');
				jQuery('#'+nextModal).addClass('current-modal-slide');
			});
			// Clicking on the 'prev' button reverses next button action
			jQuery('#sceneModal .prev').on('click',function(){
				var currentModal = jQuery(this).parents('.modal-slide').attr('id');
				var prevModal = jQuery(this).parents('.modal-slide').prev().attr('id');
				jQuery('#'+currentModal).removeClass('current-modal-slide');
				jQuery('#'+prevModal).addClass('current-modal-slide');
			});

			/*
			 * Helper Functions
			 */
			// Load links with rel="external" in a new window
			jQuery('a[rel=external]').attr('target', '_blank');
			// Disable page scrolling when showing the modal (so the background doesn't scroll)
			jQuery('html').css('overflow', 'hidden');
			// Restore scrolling when dismissing the modal
			jQuery('.modal#sceneModal').on('hidden', function() {
				jQuery('html').css('overflow', 'scroll');
			});
			
	  } else {
	    // Error message if you'd like.
	  }
	});
}

/**
 *  Mark scene viewed
 */
function markSceneViewed(postID) {

	var sceneModal = jQuery('#sceneModal');

	// Request data for modal from the server via Ajax.
	jQuery.post(scene_scripts.ajaxurl, {
	  action: 'mark_scene_viewed',
	  nonce: scene_scripts.nonce,
	  postID: postID,
	}, function(response) {
		if ( response.success === true ) {
			sceneModal.modal('hide');
		}
	});
}

jQuery( document ).ready( function( $ ) {

// How & When our Scenes will display

// DISPLAYING INTRO SCENES
// If user visits the home page, check to see if they have visited before.
	// If not display intro.

	// DISPLAYING KUKUI SCENES
	// Since it's the users first time viewing this page...
	if ( $('body').hasClass('single-units') ) {
		// INTROS
		var postID = $('article').data('postid'); // Get the post ID
		var completed = $('article').data('complete'); // Get the post ID
		
		if ( $('article').data('viewed') == 0 ) {
			displayScene( postID ); // Send to server to retrieve proper intro to be displayed in modal.
		}

		// ENTRO
		// If they visited the page and have completed X,Y,Z.
		if ( completed ) {
			displayScene( postID ); // Send to server to retrieve proper entro to be displayed in modal.
			// Entros should redirect the user to the dashboard.
		}

		// CLOSING SCENES
		$(document).on('click','a.endScene', function() {
			markSceneViewed( postID );
		});
			//	When the user reaches the end of the scene,
			// Mark the scene and the object as viewed
	}

	

});