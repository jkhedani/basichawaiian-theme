<?php

// REMOVE AFTER TESTING!!!!!!!!!!!
// VOCABULARY GAME: Step Two: Finish Game and Publish Results
function reset_scores() {
	global $wpdb;

	// Nonce check
	$nonce = $_REQUEST['nonce'];
	if (!wp_verify_nonce($nonce, 'ajax_scripts_nonce')) die(__('Busted.'));

	$html = "";
	$success = false;

	if(is_user_logged_in()) {
	 	$current_user = wp_get_current_user();
	 	$user_ID = $current_user->ID;

	 	$wpdb->query($wpdb->prepare("
	 		DELETE FROM wp_user_interactions
	 		WHERE user_id = %d
	 	", $user_ID));
	} // is_user_logged_in


	// Build the response...
	$success = true;
	$response = json_encode(array(
		'success' => $success,
		'html' => $html
	));

	// Construct and send the response
	header("content-type: application/json");
	echo $response;
	exit;
}
add_action('wp_ajax_nopriv_reset_scores', 'reset_scores');
add_action('wp_ajax_reset_scores', 'reset_scores');
// REMOVE AFTER TESTING!!!!!!!!!!!

function get_wallet_balance( $postID ) {
	global $post;
	$current_user = wp_get_current_user();
	$user_id = $current_user->ID;

	if ( get_post_type($postID) == "units" ) :
		$currencyIssuer = $postID;
	elseif ( get_post_type($postID) == "topics" ) :
		$currencyIssuer = get_connected_object_ID( $postID, 'topics_to_modules', 'modules_to_units');
	endif;

	// Aunty Aloha
	if ( $currencyIssuer == "204" ) :
		$currencyType = "bh_currency_flowers";
	endif;

	$walletBalance = get_user_meta($user_id, $currencyType, true);

	return $walletBalance;
}

function updateWallet( $currencyTypeID ) {
	$current_user = wp_get_current_user();
 	$user_id = $current_user->ID;

	// AUNTY ALOHA
	if ( $currencyTypeID == 204 ) {
		$currentBalance = get_user_meta( $user_id, 'bh_currency_flowers', true);
		if ( !$currentBalance ) :
			add_user_meta( $user_id, 'bh_currency_flowers', 1 );
		else :
			$updatedBalance = intval($currentBalance) + 1;
			update_user_meta( $user_id, 'bh_currency_flowers', $updatedBalance );
		endif;
	}
}

/*
 * Update records and display results screen
 */
function finish_lesson() {
	global $wpdb;

	// Nonce check
	$nonce = $_REQUEST['nonce'];
	if (!wp_verify_nonce($nonce, 'ajax_scripts_nonce')) die(__('Busted.'));

	$html = "";
	$success = false;
	$lessonID = $_REQUEST['lessonID'];
	$lessonOutcome = $_REQUEST['lessonOutcome'];
	$currencyTypeID = $_REQUEST['currencyTypeID'];
	$lessonResults = $_REQUEST['lessonResults'];
	$landingID = $_REQUEST['landingID'];

	// Update learner's score card
	if ( !empty($lessonResults) ) {
		$lessonCardCount = 0;
		$lessonCardCorrectCount = 0;
		foreach ( $lessonResults as $lessonResult ) {
			$lessonCardScore = explode(',', $lessonResult);
			$lessonCardID = $lessonCardScore[0];
			$lessonCardResult = $lessonCardScore[1];
			// If user got lesson card correct...
			if ( $lessonCardResult == 1 ) {
				increment_object_value ( $lessonCardID, 'times_viewed' );
				increment_object_value ( $lessonCardID, 'times_correct' );
				// Increment lesson card correct count for use in outcome display
				$lessonCardCorrectCount++;
				// Increment lesson card count only for assessed objects
				$lessonCardCount++;
			} elseif ( $lessonCardResult == 0 ) {
				increment_object_value ( $lessonCardID, 'times_viewed' );
				increment_object_value ( $lessonCardID, 'times_wrong' );
				// Increment lesson card count only for assessed objects
				$lessonCardCount++;
			} elseif ( $lessonCardResult == -99 ) {
				// From an instructional card
				increment_object_value ( $lessonCardID, 'times_viewed' );
			} else {
				// do nothing (usually means learner failed game and didn't see an object)
			}
		}
	}

	// Determine user gender
	$user = wp_get_current_user();
	$user_id = $user->ID;
	$gender = get_user_meta( $user_id, 'gender', true );

	// Display appropriate outcome screen
	if ( $lessonOutcome == 'pass' ) :

		// Add points to times_correct if applicable
		increment_object_value ( $lessonID, 'times_correct' );
		// Complete if applicable
		increment_object_value ( $lessonID, 'times_completed' );

		// Update users wallet if they haven't already completed an learning object
		$lessonRecord = get_object_record( $lessonID );
		if ( $lessonRecord[0]->times_completed <= 1 ) {
			updateWallet( $currencyTypeID );
		}

		// Return Lesson Result page
		$html .= '<div class="lesson-results success '.$gender.'">';
		$html .= '<h1 class="lesson-results-title">' . __('Maika&#8216;i!') . '</h1>';
		$html .= '<p class="lesson-results-blurb">You completed this lesson!</p>';
		if ( !empty( $lessonCardCorrectCount ) )
		$html .= '<p class="lesson-results-score">You got '.$lessonCardCorrectCount.' out of '.$lessonCardCount.' correct!</p>';
		$html .= '<div class="lesson-results-control">';
		$html .= '<a href="'. get_permalink( $lessonID ) .'" class="replay-lesson btn"><i class="fa fa-refresh"></i></a>';
		$html .= '<a href="'. get_permalink( $landingID ) .'?lesson='.$lessonID.'" class="btn btn-primary">Continue</a>';
		$html .= '</div>';
		$html .= '</div>';

	elseif ( $lessonOutcome == 'fail' ) :

		// Add points to times_wrong if applicable
		increment_object_value ( $lessonID, 'times_wrong' );

		// Return Lesson Result page
		$html .= '<div class="lesson-results fail '.$gender.'">';
		$html .= '<h1 class="lesson-results-title">' . __('Aue!') . '</h1>';
		$html .= '<p>Take a break and try again!</p>';
		$html .= '<div class="lesson-results-control">';
		$html .= '<a href="'. get_permalink( $lessonID ) .'" class="replay-lesson btn btn-primary">Replay Lesson</a>';
		$html .= '<a href="'. get_permalink( $landingID ) .'?lesson='.$lessonID.'" class="btn btn-primary">Continue</a>';
		$html .= '</div>';
		$html .= '</div>';

	endif;

	// Build the response...
	$success = true;
	$response = json_encode(array(
		'success' => $success,
		'html' => $html
	));

	// Construct and send the response
	header("content-type: application/json");
	echo $response;
	exit;
}
add_action('wp_ajax_nopriv_finish_lesson', 'finish_lesson');
add_action('wp_ajax_finish_lesson', 'finish_lesson');

// Run Ajax calls even if user is logged in
// MAYBE NOT RELEVANT: Not sure what we were using this for but it is interfering with our p2p ajax connection calls...
// Attempting specificity in this request to maybe prevent conflicts with other requests
if ( ( isset($_REQUEST['action']) && ( $_REQUEST['action'] == 'finish_lesson' ) ) || ( isset($_REQUEST['action']) && ( $_REQUEST['action'] == 'reset_scores' ) ) ):
	do_action( 'wp_ajax_' . $_REQUEST['action'] );
  do_action( 'wp_ajax_nopriv_' . $_REQUEST['action'] );
endif;


function is_unit_complete($postID) {
	global $post;
	$unitComplete = true;

	// Find all connected modules and see if they are complete
	$modules = new WP_Query( array(
		'connected_type' => 'modules_to_units',
		'connected_items' => $postID,
		'nopaging' => true,
		'orderby' => 'menu_order',
		'order' => 'ASC',
	));
	if ( $modules->have_posts() ) {
	while( $modules->have_posts() ) : $modules->the_post();
		if (!is_object_complete($post->ID))
			$unitComplete = false;
	endwhile;
	}
	wp_reset_postdata();
	return $unitComplete;
}

function is_module_complete($postID) {
	global $post;
	$moduleComplete = true;
	// Find all connected topics and see if they are complete
	$topics = new WP_Query( array(
		'connected_type' => 'topics_to_modules',
		'connected_items' => $postID,
		'nopaging' => true,
		'orderby' => 'menu_order',
		'order' => 'ASC',
	));
	if ( $topics->have_posts() ) {
	while( $topics->have_posts() ) : $topics->the_post();
		if (!is_object_complete($post->ID))
			$moduleComplete = false;
	endwhile;
	}
	wp_reset_postdata();
	return $moduleComplete;
}

function is_topic_complete($postID) {
	global $post;
	$topicComplete = true;

	// INSTRUCTIONAL LESSON
	$instructional = new WP_Query( array(
		'connected_type' => 'instructional_lessons_to_topics',
		'connected_items' => $postID,
		'nopaging' => true,
		'orderby' => 'menu_order',
		'order' => 'ASC',
	));
	if ( $instructional->have_posts() ) {
	while( $instructional->have_posts() ) : $instructional->the_post();
		if (!is_object_complete($post->ID))
			$topicComplete = false;
	endwhile;
	}
	wp_reset_postdata();

	// LISTEN AND REPEAT LESSON
	$listenRepeat = new WP_Query( array(
		'connected_type' => 'listen_repeat_lessons_to_topics',
		'connected_items' => $postID,
		'nopaging' => true,
		'orderby' => 'menu_order',
		'order' => 'ASC',
	));
	if ( $listenRepeat->have_posts() ) {
	while( $listenRepeat->have_posts() ) : $listenRepeat->the_post();
		if (!is_object_complete($post->ID))
			$topicComplete = false;
	endwhile;
	}
	wp_reset_postdata();

	// READINGS
	$readings = new WP_Query( array(
		'connected_type' => 'readings_to_topics',
		'connected_items' => $postID,
		'nopaging' => true,
		'orderby' => 'menu_order',
		'order' => 'ASC',
	));
	if ( $readings->have_posts() ) {
	while( $readings->have_posts() ) : $readings->the_post();
		if (!is_object_complete($post->ID))
			$topicComplete = false;
	endwhile;
	}
	wp_reset_postdata();

	// VOCABULARY LESSONS
	$vocabLessons = new WP_Query( array(
		'connected_type' => 'vocabulary_lessons_to_topics',
		'connected_items' => $postID,
		'nopaging' => true,
		'orderby' => 'menu_order',
		'order' => 'ASC',
	));
	if ( $vocabLessons->have_posts() ) {
	while( $vocabLessons->have_posts() ) : $vocabLessons->the_post();
		if (!is_object_complete($post->ID))
			$topicComplete = false;
	endwhile;
	}
	wp_reset_postdata();

	// PHRASES LESSONS
	$phrasesLessons = new WP_Query( array(
		'connected_type' => 'phrases_lessons_to_topics',
		'connected_items' => $postID,
		'nopaging' => true,
		'orderby' => 'menu_order',
		'order' => 'ASC',
	));
	if ( $phrasesLessons->have_posts() ) {
	while( $phrasesLessons->have_posts() ) : $phrasesLessons->the_post();
		if (!is_object_complete($post->ID))
			$topicComplete = false;
	endwhile;
	}
	wp_reset_postdata();

	// PRONOUN LESSONS
	$pronounLessons = new WP_Query( array(
		'connected_type' => 'pronoun_lessons_to_topics',
		'connected_items' => $postID,
		'nopaging' => true,
		'orderby' => 'menu_order',
		'order' => 'ASC',
	));
	if ( $pronounLessons->have_posts() ) {
		while( $pronounLessons->have_posts() ) : $pronounLessons->the_post();
			if (!is_object_complete($post->ID))
				$topicComplete = false;
		endwhile;
	}
	wp_reset_postdata();

	// SONG LESSONS
	$songLessons = new WP_Query( array(
		'connected_type' => 'song_lessons_to_topics',
		'connected_items' => $postID,
		'nopaging' => true,
		'orderby' => 'menu_order',
		'order' => 'ASC',
	));
	if ( $songLessons->have_posts() ) {
		while( $songLessons->have_posts() ) : $songLessons->the_post();
			if (!is_object_complete($post->ID))
				$topicComplete = false;
		endwhile;
	}
	wp_reset_postdata();

	// CHANTS LESSONS
	$chantsLessons = new WP_Query( array(
		'connected_type' => 'chants_lessons_to_topics',
		'connected_items' => $postID,
		'nopaging' => true,
		'orderby' => 'menu_order',
		'order' => 'ASC',
	));
	if ( $chantsLessons->have_posts() ) {
		while( $chantsLessons->have_posts() ) : $chantsLessons->the_post();
			if (!is_object_complete($post->ID))
				$topicComplete = false;
		endwhile;
	}
	wp_reset_postdata();

	return $topicComplete;
}

?>
