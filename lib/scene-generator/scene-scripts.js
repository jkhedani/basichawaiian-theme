/**
 *  Displays latest "Start Here" modal
 */
function displayScene(sceneID) {
	// Request data for modal from the server via Ajax.
	jQuery.post(scene_scripts.ajaxurl, {
	  action: 'display_scene',
	  nonce: scene_scripts.nonce,
	  sceneID: sceneID, 
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
				jQuery('html').css('overflow', 'auto');
			});
			jQuery('body').find('#content').hide();
	  } else {
			jQuery('body').find('#content').show();
	  	// This will return if a scene doesn't need to be presented as well.
	    // Error message if you'd like.
	  }
	});
}

/**
 *  Mark scene viewed
 */
function markSceneComplete(sceneID) {
	var sceneModal = jQuery('#sceneModal');
	// Request data for modal from the server via Ajax.
	jQuery.post(scene_scripts.ajaxurl, {
	  action: 'mark_scene_complete',
	  nonce: scene_scripts.nonce,
	  sceneID: sceneID,
	}, function(response) {
		if ( response.success === true ) {
			sceneModal.modal('hide');
		}
	});
}

/**
 *  Mark scene viewed
 *  Again determine if this is necessary
 */
// function markSceneViewed(postID) {
// 	var sceneModal = jQuery('#sceneModal');
// 	// Request data for modal from the server via Ajax.
// 	jQuery.post(scene_scripts.ajaxurl, {
// 	  action: 'mark_scene_viewed',
// 	  nonce: scene_scripts.nonce,
// 	  postID: postID,
// 	}, function(response) {
// 		if ( response.success === true ) {
// 			sceneModal.modal('hide');
// 		}
// 	});
// }


/**
 * Trigger Scenes
 */

jQuery( document ).ready( function( $ ) {

	// DISPLAYING INTRO SCENES
	// If user visits the home page, check to see if they have visited before.
	// If not display intro.
	if ( $('body').hasClass('home') && $('body').hasClass('logged-in') ) {

		var sceneViewed = $('#content').data('scene-viewed');
		if ( sceneViewed == 'no' ) {
			var sceneID = $('#content').data('assoc-scene'); // Get scene ID
			// Determine if a scene needs to be displayed on every page load....(can we find a better way to do this)
			displayScene( sceneID ); // Send to server to retrieve proper intro to be displayed in modal.
		}

		// End Scene
		$(document).on('click','a.end-scene', function() {
			markSceneComplete( sceneID );
			jQuery('body').find('#content').show();
		});

	}

	// DISPLAYING KUKUI SCENES
	// Since it's the users first time viewing this page...
	if ( $('body').hasClass('single-units') ) {

		var sceneViewed = $('article').data('scene-viewed');
		if ( sceneViewed == 'no' ) {
			var sceneID = $('article').data('assoc-scene'); // Get scene ID
			// INTROS
			// Determine if a scene needs to be displayed on every page load....(can we find a better way to do this)
			displayScene( sceneID ); // Send to server to retrieve proper intro to be displayed in modal.
		}
		
		// End Scene
		$(document).on('click','a.end-scene', function() {
			markSceneComplete( sceneID );
			jQuery('body').find('#content').show();
		});

		// DISPLAYING EXERCISE SCENES
		// Currently electing to display exercises on unit pages only.
		// $('li.topic').each(function(){
		// 	var topicID = $(this).data('topic-id'); // Get the post ID
		// 	var topicCompleted = $(this).data('complete'); // Get the post ID
		// 	var exerciseCompleted = $(this).data('exercise-complete'); // Get the post ID
		// 	if ( topicCompleted == 1 && exerciseCompleted == 0 ) {
		// 		displayScene( topicID );
		// 		markSceneViewed( topicID );
		// 	}
		// });
		
	}

});