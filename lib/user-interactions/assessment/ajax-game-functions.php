<?php

/**
 *	Function: Get Connected Object ID
 *	@param int $postID The ID of the post you wish to find the parent ID of.
 *	@param int $parentConnectionType The p2p connection type of the current post to immediate parent
 *	@param int $grandparentConnectionType optional The p2p connection type of the immediate parent to grandparent
 *	@param int $grandparentConnectionType optional The p2p connection type of the grandparent to great grandparent
 *  Return the ID of a connected parent (currently only works for objects with one parent and/or one grandparent and/or one great grandparent ... poor child)
 *
 */
function get_connected_object_ID( $postID, $parentConnectionType = false, $grandparentConnectionType = false, $greatGrandparentConnectionType = false ) {
	global $post;
	$connectedParentID = false;
	$connectedGrandparentID = false;
	$connectedGreatGrandparentID = false;
	
	// Get connected parent if connection type exists to prevent direction error
	if ( p2p_connection_exists( $parentConnectionType ) ) :
	$connectedParent = new WP_Query( array(
	'connected_type' => $parentConnectionType,
	'connected_items' => $postID,
	'nopaging' => true,
	'orderby' => 'menu_order',
	'order' => 'ASC',
	));
	while( $connectedParent->have_posts() ) : $connectedParent->the_post();
		$connectedParentID = $post->ID;

		// Get connected grandparent if desired & check if connection type exists to prevent direction error
		if ( !empty( $grandparentConnectionType ) && ( p2p_connection_exists( $grandparentConnectionType ) ) ) :
			$connectedGrandparent = new WP_Query( array(
			'connected_type' => $grandparentConnectionType,
			'connected_items' => $connectedParentID,
			'nopaging' => true,
			'orderby' => 'menu_order',
			'order' => 'ASC',
			));
			while( $connectedGrandparent->have_posts() ) : $connectedGrandparent->the_post();
				$connectedGrandparentID = $post->ID;

				// Get connected greatgrandparent if desired & check if connection type exists to prevent direction error
				if ( !empty( $greatGrandparentConnectionType ) && ( p2p_connection_exists( $greatGrandparentConnectionType ) ) ) :
				$connectedGreatGrandparent = new WP_Query( array(
				'connected_type' => $greatGrandparentConnectionType,
				'connected_items' => $connectedGrandparentID,
				'nopaging' => true,
				'orderby' => 'menu_order',
				'order' => 'ASC',
				));
				while( $connectedGreatGrandparent->have_posts() ) : $connectedGreatGrandparent->the_post();
					$connectedGreatGrandparentID = $post->ID;
				endwhile;
				wp_reset_postdata();
				endif;

			endwhile;
			wp_reset_postdata();
		endif;

	endwhile;
	wp_reset_postdata();

	endif;

	// Return desired connections
	if ( !empty( $greatGrandparentConnectionType ) ) {
		return $connectedGreatGrandparentID;
	} elseif ( !empty( $grandparentConnectionType ) ) {
		return $connectedGrandparentID;
	} else {
		return $connectedParentID;
	}
}

function is_topic_complete($postID) {
	global $post;
	$topicComplete = true;

	// LECTURES
	$lectures = new WP_Query( array(
		'connected_type' => 'lectures_to_topics',
		'connected_items' => $postID,
		'nopaging' => true,
		'orderby' => 'menu_order',
		'order' => 'ASC',
	));
	if ( $lectures->have_posts() ) {
	while( $lectures->have_posts() ) : $lectures->the_post();
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
		'connected_items' => $post->ID,
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

	// ACTIVITIES
	$activities = new WP_Query( array(
		'connected_type' => 'activities_to_topics',
		'connected_items' => $postID,
		'nopaging' => true,
		'orderby' => 'menu_order',
		'order' => 'ASC',
	));
	if ( $activities->have_posts() ) {
	while( $activities->have_posts() ) : $activities->the_post();
		if (!is_object_complete($post->ID))
		$topicComplete = false;
	endwhile;
	}
	wp_reset_postdata();

	return $topicComplete;
}

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
			} elseif ( $lessonCardResult == 0 ) {
				increment_object_value ( $lessonCardID, 'times_viewed' );
				increment_object_value ( $lessonCardID, 'times_wrong' );
			} else {
				// do nothing (usually means learner failed game and didn't see an object)
			}
			// Increment lesson card count for use in outcome display
			$lessonCardCount++;
		}
	}

	// Update users wallet if they haven't already completed an learning object
	$lessonRecord = get_object_record( $lessonID );
	if ( $lessonRecord[0]->times_completed < 1 ) {
		updateWallet( $currencyTypeID );
	}

	// Display appropriate outcome screen
	if ( $lessonOutcome == 'pass' ) :

		// Add points to times_correct if applicable
		increment_object_value ( $lessonID, 'times_correct' );
		// Complete if applicable
		increment_object_value ( $lessonID, 'times_completed' );

		// Return Lesson Result page
		$html .= '<h1>' . __('Maika&#8216;i!') . '</h1>';
		$html .= '<p>You completed this lesson!</p>';
		if ( !empty( $lessonCardCorrectCount ) )
		$html .= '<p>You got '.$lessonCardCorrectCount.' out of '.$lessonCardCount.' correct!</p>';
		$html .= '<a href="'. get_permalink( $lessonID ) .'" class="replay-lesson btn">Replay Lesson</a>';
		$html .= '<a href="'. get_permalink( $landingID ) .'" class="btn btn-primary">Continue</a>';

	elseif ( $lessonOutcome == 'fail' ) :

		// Add points to times_wrong if applicable
		increment_object_value ( $lessonID, 'times_wrong' );

		// Return Lesson Result page
		$html .= '<h1>' . __('Aue!') . '</h1>';
		$html .= '<p>Take a break and try again!</p>';
		$html .= '<a href="'. get_permalink( $lessonID ) .'" class="replay-lesson btn btn-primary">Replay Lesson</a>';
		$html .= '<a href="'. get_permalink( $landingID ) .'" class="btn btn-primary">Continue</a>';

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














// VOCABULARY GAME: Step One: Run user choice through TLA & display the game
function get_game_difficulty() {

	// Get everything from init to load taxonomies.
	do_action('init');

	// Need to grab connection types as well.
	// Safe hook for calling p2p_register_connection_type()
	// https://github.com/scribu/wp-posts-to-posts/blob/master/posts-to-posts.php
	 _p2p_init();

	// Nonce check
	$nonce = $_REQUEST['nonce'];
	if (!wp_verify_nonce($nonce, 'ajax_scripts_nonce')) die(__('Busted.'));

	global $wpdb, $wp_query, $post, $terms;

	$html = "";
	$success = false;
	$connectedTo = $_REQUEST['connectedTo'];
	//$gameDifficulty = $_REQUEST['gameDifficulty'];
	
/**
	*
	*
	*
	*  	 OBJECT QUERY
	*
	*
	*
	*/

	// Query all objects for use in this Vocabulary game
	$gameObjectsArgs = array(
  	'connected_type' => 'vocabulary_terms_to_vocabulary_games',
	  'connected_items' => $connectedTo,
	  'connected_direction' => 'to',
	  'nopaging' => true,
		'post_type' => 'vocabulary_terms',
	);
	$gameObjects = new WP_Query($gameObjectsArgs);

	// Grab all IDs associated with this game	
	$gameObjectIDs = array();
	while ($gameObjects->have_posts()) : $gameObjects->the_post();
		$gameObjectIDs[] = $post->ID;
	endwhile;
	rewind_posts();

/**
	*
	*
	*
	*  	 DATA COLLECTION 
	*
	*
	*
	*/

	if(is_user_logged_in()) {
	 	$current_user = wp_get_current_user();
	 	$user_ID = $current_user->ID;
	 	$post_ids = implode(',',$gameObjectIDs);
	 	$post_ids_safe = mysql_real_escape_string($post_ids); // Just because I like being

	 	// Grab all data associated with the game object
	 	$gameObjectsViewed = $wpdb->get_results($wpdb->prepare(
	 		"
	 		SELECT *
	 		FROM wp_user_interactions
			WHERE user_id = %d
				AND post_id IN (".$post_ids_safe.")
			LIMIT 0, 30
			"
			, $user_ID
		));

	 	// Error check: This database will only be updated if the amount
		// of IDs that exist are different from the IDs the user has interacted with.
		if(count($gameObjectIDs) != count($gameObjectsViewed)) {
			$values = array();
			$placeHolders = array();

			while ($gameObjects->have_posts()) : $gameObjects->the_post();
				$values[] = $user_ID.',';
				$values[] = $post->ID.',';
				$values[] = '0';
				$placeHolders[] = '(%d, %d, %d)';
			endwhile;
			rewind_posts();

			$placeHolderCreate = implode(', ', $placeHolders);

			// Insert records for the user
			$wpdb->query( $wpdb->prepare("
	 			INSERT IGNORE INTO wp_user_interactions
	 			( user_id, post_id, times_completed )
	 			VALUES ".$placeHolderCreate."
	 		", $values ));

	 		// Since user is new, we need to overwrite $gameObjectsViewed
	 		// with a new query to populate some data to pass to the TLA.
	 		// Essentially, we are telling the TLA that everything is new. :)
		 	$gameObjectsViewed = $wpdb->get_results($wpdb->prepare(
		 		"
		 		SELECT *
		 		FROM wp_user_interactions
				WHERE user_id = %d
					AND post_id IN (".$post_ids_safe.")
				LIMIT 0, 30
				"
				, $user_ID
			));
	 	}
	} // is_user_logged_in


	// * THE LEARNING ALGORITHM * //

	/*
	 * Assign each ID to a prestige level
	 */
	$new = array();
	$untested = array();
	$practiced = array();
	$learned = array();
	$mastered = array();
	$neutral = array();
	$unfamiliar = array();
	$failed = array();
	foreach ($gameObjectsViewed as $gameObject) {
		$object_id = $gameObject->post_id;
		$times_correct = $gameObject->times_correct;
		$times_wrong = $gameObject->times_wrong;
		$times_viewed = $gameObject->times_viewed;

		// "NEW"
		if ($times_viewed == 0):
			$new[] = $object_id;
		
		// "UNTESTED"
		elseif(($times_correct == 0) && ($times_wrong == 0) && ($times_viewed > 0)):
			$untested[] = $object_id;
		
		// "NEUTRAL"
		elseif(($times_correct == $times_wrong) && ($times_viewed > 0)):
			$neutral[] = $object_id;

		// "PRACTICED"
		elseif( ($times_correct >= ($times_wrong + 1)) && ($times_correct < ($times_wrong + 3)) ):
			$practiced[] = $object_id;

		// "LEARNED"
		elseif( ($times_correct >= ($times_wrong + 3)) && ($times_correct < ($times_wrong + 6)) ):
			$learned[] = $object_id;

		// "MASTERED"
		elseif($times_correct >= ($times_wrong + 6)):
			$mastered[] = $object_id;
		
		// "UNFAMILIAR"
		elseif( ($times_wrong >= ($times_correct + 1)) && ($times_wrong < ($times_correct + 3)) ):
			$unfamiliar[] = $object_id;

		// "FAILED"
		elseif($times_wrong >= ($times_correct + 3)):
			$failed[] = $object_id;
		endif;

	}
	
	/*
	 * Generate final list of IDs to be displayed
	 */
	
	$cardsToTeach = array();
	$cardsToTest = array();
	
	$maxTeach = 5; // Maximum number of times a learning card can be considered.
	$minTest = 5; // Maximum number of times a test card can be considered.
	$maxCards = $maxTeach + $minTest; // Max number of objects a game can have

	/*
	 * Generate the game navigation
	 */
	$html .= '<div class="gameProgress">';
	for($cardIndex = 1; $cardIndex <= $maxCards; $cardIndex++) {		 								
	$html .= 		'<div class="gameProgressPoint"></div>';
	}
	$html .= 		'<div class="gameProgressPoint finish"></div>';
	$html .= '</div>';

	/*
	 * Generate the game instructions
	 */
	$html .= '<h3 class="gameInstructions">Listen and repeat each word you hear until you feel comfortable pronouncing each word.</h3>';

	/*
	 * Generate the game board
	 */
	$html .= '<div class="gameBoard">';															


				/*
				 * Generate all the learning cards
				 * SAMPLE: There are six cards in total.
				 */
		
				// Grab all cards we want to teach...
				if (!empty($failed)) { $cardsToTeach[] = $failed; } // FAILED
				if (!empty($unfamiliar)) { $cardsToTeach[] = $unfamiliar; } // UNFAMILIAR
				if (!empty($new)) { $cardsToTeach[] = $new; } // NEW
				
				// Then generate a single array of IDs from the cards we want to teach.
				$unflatCardsToTeach = new RecursiveIteratorIterator(new RecursiveArrayIterator($cardsToTeach));
				foreach($unflatCardsToTeach as $values) { $teachflatValue[] = $values; }
				$finalCardIDsToTeach = array_filter($teachflatValue);

				// Trim cards down before dealing
				$dealtCardsToTeach = array_slice($finalCardIDsToTeach, 0, $maxTeach);

				error_log('TeachObjects');
				error_log(print_r($dealtCardsToTeach,true));

				// Show the cards we want to teach if they exist.
				if (!empty($finalCardIDsToTeach)) {
				$teachingCardArgs = array(
					'post__in' => $dealtCardsToTeach,
					'orderby' => 'post__in',
					'posts_per_page' => $maxTeach,
					'post_type' => 'vocabulary_terms',
				);
				$teachingCards = new WP_Query($teachingCardArgs);
				while ($teachingCards->have_posts()) : $teachingCards->the_post();

								// THE "LEARN" CARD TEMPLATE: Vocabulary
								$vocabCardOpen = '<div class="gameCard">';
								$vocabCardContent = '
									<div class="gameCardControls">
										<!-- Hawaiian Pronunciation -->
										<a class="pronunciationPlay" title="Play the pronunciation."></a>
										<audio class="pronunciation" src="'.get_field('audio_track').'"></audio>
										<!-- Hawaiian Translation -->
										<div class="translationWrapper">
											<a class="toggleHwnTranslation" title="Show the Hawaiian Translation">Show Hawaiian</a>
											<div class="hwnTranslation hidden">'.get_the_title().'</div>
										</div>
										<!-- English Translation -->
										<div class="translationWrapper">
											<a class="toggleEngTranslation" title="Show the English Translation">Show English</a>
											<div class="engTranslation hidden">'.get_field('english_translation').'</div>
										</div>
									</div>
									'.
								get_the_post_thumbnail();
								$vocabCardClose = '</div>'; // .gameCard
								$vocabularyCard = $vocabCardOpen . $vocabCardContent . $vocabCardClose;
								$html .= $vocabularyCard;

				endwhile;
				wp_reset_postdata();
				}

				/*
				 * Generate extra test cards if needed
				 * NOTE: If the amount of teaching objects is less than the max amount of teaching objects alotted,
				 * find the difference.
				 */
				if (count($finalCardIDsToTeach) < $maxTeach) {
					$numberOfAdditionalTestCardsNeeded = $maxTeach - count($finalCardIDsToTeach);
					$minTest = $minTest + $numberOfAdditionalTestCardsNeeded;
				}	

				/*
				 * Generate all the test cards
				 * SAMPLE: There should be a minimum of six.
				 */

				// Grab all the objects we want to test.
				if (!empty($failed)) { $cardsToTest[] = $failed; } // FAILED
				if (!empty($unfamiliar)) { $cardsToTest[] = $unfamiliar; } // UNFAMILIAR
				if (!empty($new)) { $cardsToTest[] = $new; } // NEW
				if (!empty($untested)) { $cardsToTest[] = $untested; } // UNTESTED
				if (!empty($neutral)) { $cardsToTest[] = $neutral; }	// NEUTRAL			
				if (!empty($practiced)) { $cardsToTest[] = $practiced; } // PRACTICED
				if (!empty($learned)) { $cardsToTest[] = $learned; } // LEARNED
				if (!empty($mastered)) { $cardsToTest[] = $mastered; } // MASTERED

				// Then generate a single array of IDs from the cards we want to teach.
				$unflatCardsToTest = new RecursiveIteratorIterator(new RecursiveArrayIterator($cardsToTest));
				foreach($unflatCardsToTest as $values) { $testflatValue[] = $values; }
				$finalCardIDsToTest = array_filter($testflatValue);				

				// Trim cards down before dealing
				$dealtCardsToTest = array_slice($finalCardIDsToTest, 0, $minTest);

				error_log('TestObjects');
				error_log(print_r($dealtCardsToTest,true));

				// Show the cards we want to test.
				$testingCardArgs = array(
					'post__in' => $dealtCardsToTest,
					'orderby' => 'post__in',
					'posts_per_page' => $minTest,
					'post_type' => 'vocabulary_terms',
				);
				$testingCards = new WP_Query($testingCardArgs);
				while ($testingCards->have_posts()) : $testingCards->the_post();


								// THE "TEST" CARD TEMPLATE: Vocabulary
								// Get the ID of the card we want to test...
								$currentCard = $post->ID;
								// Reset each test card set...
								$testCards = array();
								// Strip current card being tested from random query...
								$tempCardQuery = $finalCardIDsToTest;
								if (($key = array_search($currentCard, $tempCardQuery)) !== false) {
								    unset($tempCardQuery[$key]);
								}
								// Generate enough cards to use in each game; two to be precise.
								$randomTestCardQuery = array_rand($tempCardQuery, 2);
								$randomTestCards = array();
								for ($i = 0; $i < 2; $i++) {
									$testCards[] = $tempCardQuery[$randomTestCardQuery[$i]];
								}
								$testCards[] = $currentCard;

								// Generate each mini game
								$miniGameQuery = new WP_Query( array(
									'posts_per_page' => 3,
									'orderby' => 'rand',
									'post_type' => 'vocabulary_terms',
									'post__in' => $testCards,
								));
								//$numberOfTestItems = $miniGameQuery->post_count;							
								$randomCorrectNumber = $currentCard;

								// Generate the mini game...
								$html .= '<div class="gameCard miniGame">';
								$html .= '<!-- You spent more cheating then you did learning. -->';
								//$html .= '<span class="correctAnswer hidden">'.$randomCorrectNumber.'</span>';
								$html .= '<div class="gameChoices row-fluid">';
								while ($miniGameQuery->have_posts()) : $miniGameQuery->the_post();
									$html .= '<div class="gameChoice span4">';

									// Only generate an audio file for the correct number
									if ($randomCorrectNumber == $post->ID) {
										$html .= '<div class="gameCardControls correctAnswer" data-card-id="'.$post->ID.'">';
										// Hawaiian Pronunciation
										$html .= 	'<a class="pronunciationPlay" title="Play the pronunciation."></a>';
										$html .= 	'<audio class="pronunciation" src="'.get_field('audio_track').'"></audio>';
										// Hawaiian Translation
										$html .=	'<div class="translationWrapper">';
										$html .= 	'<a class="toggleHwnTranslation" title="Show the Hawaiian Translation">Show Hawaiian</a>';
										$html .= 	'<div class="hwnTranslation hidden">'.get_the_title().'</div>';
										$html .=	'</div>'; // translationWrapper
										// English Translation
										$html .=	'<div class="translationWrapper">';
										$html .= 	'<a class="toggleEngTranslation" title="Show the English Translation">Show English</a>';
										$html .= 	'<div class="engTranslation hidden">'.get_field('english_translation').'</div>';
										$html .=	'</div>'; // translationWrapper
										$html .= '</div>'; // gameCardControls
									}
									// Choices
									$html .= 	'<a href="javascript:void(0);" id="'.$post->ID.'" class="choiceSelect" title="Choice for answer">'.get_the_post_thumbnail().'</a>';				
									$html .= '</div>'; // gameChoice
								//$choiceNumber++;
								endwhile;
								$html .= '</div>'; // gameChoices
								wp_reset_postdata();
								$html .= '</div>'; // miniGame


				endwhile;
				wp_reset_postdata();

				/*
				 * Calculate which cards have been viewed
				 */
				if (empty($dealtCardsToTeach)) {
					$cardIDsViewed = $dealtCardsToTest;	
				} else {
					$overlappingCards = array_intersect($dealtCardsToTeach, $dealtCardsToTest);
					$testedOnlyCards = array_diff($dealtCardsToTest, $overlappingCards);
					$cardIDsViewed = array_merge($overlappingCards, $testedOnlyCards);	
				}
				
				error_log('Cards viewed:');
				error_log(print_r($cardIDsViewed, true));
				error_log(count($dealtCardsToTest));

	$html .= '</div>'; // gameBoard

	// User Game Controls
	$html .= '<div class="gameUserControls">';
	$html .= '<a class="gameNext btn hidden" href="javascript:void(0);">Next</a>';
	$html .= '<a class="gameCheck btn hidden" href="javascript:void(0);">Check</a>';
	$html .= '<a class="gameFinish btn hidden" href="javascript:void(0);">Finish</a>';
	$html .= '<a class="gameContinue btn hidden" href="javascript:void(0);">Continue</a>';
	$html .= '<a class="gameRestart btn hidden" href="javascript:void(0);">Restart</a>';
	$html .= '</div>'; // gameUserControls

	// Results Bin
	$html .= '<div class="gameResults" data-total-tested="'.count($dealtCardsToTest).'" data-total-correct="0">';
	$html .= '<div class="cardsViewed" data-viewed="'.implode(', ',array_filter($cardIDsViewed)).'"></div>';
	$html .= '<div class="cardsTested"></div>';
	$html .= '</div>';




	// // FAILED
	// if(!empty($failed))
	// 	$cardSort[] = $failed;

	// // UNFAMILIAR
	// if (!empty($unfamiliar))
	// 	$cardSort[] = $unfamiliar;

	// // NEW
	// if (!empty($new))
	// 	$cardSort[] = $new;

	// // UNTESTED
	// if (!empty($untested))
	// 	$cardSort[] = $untested;

	// // NEUTRAL
	// if (!empty($neutral))
	// 	$cardSort[] = $neutral;

	// // PRACTICED
	// if (!empty($practiced))
	// 	$cardSort[] = $practiced;

	// // LEARNED
	// if (!empty($learned))
	// 	$cardSort[] = $learned;

	// // MASTERED
	// if (!empty($mastered))
	// 	$cardSort[] = $mastered;

	// $unflatCardSort = new RecursiveIteratorIterator(new RecursiveArrayIterator($cardSort));
	// foreach($unflatCardSort as $values) {
	//   $flatValue[] = $values;
	// }
	// $finalCardSort = array_filter($flatValue);
	// error_log(print_r($finalCardSort,true));




	// /*
	//  * Generate the the query for the game based on the feedback from the learning algorithm
	//  */
	// $sortedGameObjectsArgs = array(
	// 	'post__in' => $finalCardSort,
	// 	'orderby' => 'post__in',
	// 	'posts_per_page' => $maxCardObjects,
	// 	'post_type' => 'vocabulary_terms',
	// );
	// $finalGameObjectsArgs = array_merge($sortedGameObjectsArgs, $gameObjectsArgs);
	// $gameObjects = new WP_Query($sortedGameObjectsArgs);
	// $availableObjectsTotal = $gameObjects->post_count; // Number of actual objects in a game

	// /*
	//  * Generate the game navigation
	//  */
	// $html .= '<div class="gameProgress">';
	// for($cardIndex = 1; $cardIndex <= $availableObjectsTotal; $cardIndex++) {
	// 	if ($cardIndex % $testFrequency == 0):		 								
	// 		$html .= '<div class="gameProgressPoint miniGame"></div>';
	// 	else:
	// 		$html .= '<div class="gameProgressPoint"></div>';
	// 	endif;
	// }
	// $html .= '<div class="gameProgressPoint finish"></div>';
	// $html .= '</div>';

	// /*
	//  * Generate the game board
	//  */
	// $cardIndex = 1;																									// Reset progress counter.
	// $viewedGameObjects = array();																		// Start storing post ID's for viewed gameObjects
	// $html .= '<div class="gameBoard">';															
	// while ($gameObjects->have_posts()) : $gameObjects->the_post();
	// // Grab all IDs shown to place in gameResults as cards viewed
	// $availableObjectsID[] = $post->ID;

	// 	// THE "LEARN" CARD TEMPLATE: Vocabulary
	// 	$vocabCardOpen = '<div class="gameCard">';
	// 	$vocabCardContent = '
	// 		<div class="gameCardControls">
	// 			<!-- Hawaiian Pronunciation -->
	// 			<a class="pronunciationPlay" title="Play the pronunciation."></a>
	// 			<audio class="pronunciation" src="'.get_field('audio_track').'"></audio>
	// 			<!-- Hawaiian Translation -->
	// 			<div class="translationWrapper">
	// 				<a class="toggleHwnTranslation" title="Show the Hawaiian Translation">Show Hawaiian</a>
	// 				<div class="hwnTranslation hidden">'.get_the_title().'</div>
	// 			</div>
	// 			<!-- English Translation -->
	// 			<div class="translationWrapper">
	// 				<a class="toggleEngTranslation" title="Show the English Translation">Show English</a>
	// 				<div class="engTranslation hidden">'.get_field('english_translation').'</div>
	// 			</div>
	// 		</div>
	// 		'.
	// 	get_the_post_thumbnail();
	// 	$vocabCardClose = '</div>'; // .gameCard
	// 	$vocabularyCard = $vocabCardOpen . $vocabCardContent . $vocabCardClose; 

	// 		/*
	// 		 * Generate the game view.
	// 		 */
	// 		$miniGameQuery = new WP_Query( array(
	// 			'posts_per_page' => 3,
	// 			'orderby' => 'rand',
	// 			'post_type' => 'vocabulary_terms',
	// 			'post__in' => $testGameObjects
	// 		));

	// 		$numberOfTestItems = $miniGameQuery->post_count;							
	// 		$randomCorrectNumber = $currentCard;
	// 		//$choiceNumber = 1;

	// 		// Generate the mini game...
	// 		$html .= '<div class="gameCard miniGame">';
	// 		$html .= '<!-- You spent more cheating then you did learning. -->';
	// 		//$html .= '<span class="correctAnswer hidden">'.$randomCorrectNumber.'</span>';
	// 		$html .= '<div class="gameChoices row-fluid">';
	// 		while ($miniGameQuery->have_posts()) : $miniGameQuery->the_post();
	// 			$html .= '<div class="gameChoice span5">';

	// 			// Only generate an audio file for the correct number
	// 			if ($randomCorrectNumber == $post->ID) {
	// 				$html .= '<div class="gameCardControls correctAnswer" data-card-id="'.$post->ID.'">';
	// 				// Hawaiian Pronunciation
	// 				$html .= 	'<a class="pronunciationPlay" title="Play the pronunciation."></a>';
	// 				$html .= 	'<audio class="pronunciation" src="'.get_field('audio_track').'"></audio>';
	// 				// Hawaiian Translation
	// 				$html .=	'<div class="translationWrapper">';
	// 				$html .= 	'<a class="toggleHwnTranslation" title="Show the Hawaiian Translation">Show Hawaiian</a>';
	// 				$html .= 	'<div class="hwnTranslation hidden">'.get_the_title().'</div>';
	// 				$html .=	'</div>'; // translationWrapper
	// 				// English Translation
	// 				$html .=	'<div class="translationWrapper">';
	// 				$html .= 	'<a class="toggleEngTranslation" title="Show the English Translation">Show English</a>';
	// 				$html .= 	'<div class="engTranslation hidden">'.get_field('english_translation').'</div>';
	// 				$html .=	'</div>'; // translationWrapper
	// 				$html .= '</div>'; // gameCardControls
	// 			}
	// 			// Choices
	// 			$html .= 	'<a href="javascript:void(0);" id="'.$post->ID.'" class="choiceSelect" title="Choice for answer">'.get_the_post_thumbnail().'</a>';				
	// 			$html .= '</div>'; // gameChoice
	// 		//$choiceNumber++;
	// 		endwhile;
	// 		$html .= '</div>'; // gameChoices
	// 		wp_reset_postdata();
	// 		$html .= '</div>'; // miniGame
	// 	}
	
	// $cardIndex++;
	// endwhile;
	// wp_reset_postdata();
	// $html .= '</div>'; // gameBoard

	// // User Game Controls
	// $html .= '<div class="gameUserControls">';
	// $html .= '<a class="gameNext btn hidden" href="javascript:void(0);">Next</a>';
	// $html .= '<a class="gameCheck btn hidden" href="javascript:void(0);">Check</a>';
	// $html .= '<a class="gameFinish btn hidden" href="javascript:void(0);">Finish</a>';
	// $html .= '<a class="gameContinue btn hidden" href="javascript:void(0);">Continue</a>';
	// $html .= '<a class="gameRestart btn hidden" href="javascript:void(0);">Restart</a>';
	// $html .= '</div>'; // gameUserControls

	// // Results Bin
	// $html .= '<div class="gameResults" data-total-tested="0" data-total-correct="0">';
	// $html .= '<div class="cardsViewed" data-viewed="'.implode(', ',array_filter($availableObjectsID)).'"></div>';
	// $html .= '<div class="cardsTested"></div>';
	// $html .= '</div>';

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
add_action('wp_ajax_nopriv_get_game_difficulty', 'get_game_difficulty' );
add_action('wp_ajax_get_game_difficulty', 'get_game_difficulty' );





























// VOCABULARY GAME: Step Two: Finish Game and Publish Results
function publish_results() {
	global $wpdb;

	// Nonce check
	$nonce = $_REQUEST['nonce'];
	if (!wp_verify_nonce($nonce, 'ajax_scripts_nonce')) die(__('Busted.'));
	
	$html = "";
	$success = false;

	$totalTested = $_REQUEST['totalTested'];
	$totalCorrect = $_REQUEST['totalCorrect'];
	$objectsViewed = $_REQUEST['objectsViewed'];
	$objectsTested = $_REQUEST['objectsTested'];

	$html .= '<h3>You&#39;ve completed the Hua activity!</h3>';
	$html .= '<hr />';
	$html .= 'You scored ';
	$html .= $totalCorrect;
	$html .= ' out of ';
	$html .= $totalTested;
	$html .= '!';
	$html .= '<a class="gameFinish btn btn-primary span3" href="javascript:void(0);">Finish</a>';
	$html .= '<a class="gameRestart btn btn-primary span3" href="javascript:history.go(0);">Restart</a>';
	$html .= '<a class="gameRestart btn btn-primary span3" href="'.site_url().'/scoresheet" target="_blank">View Scoresheet</a>';
	$html .= '<br />';
	
/**
	*
	*
	* Update Userdata
	*
	*
	*
	*/
	if(is_user_logged_in()) {
	 	$current_user = wp_get_current_user();
	 	$user_ID = $current_user->ID;

	 	//$post_ids = explode(',',$objectsTested);
	 	//$post_ids_safe = mysql_real_escape_string($post_ids); // Just because I like being

	 	// Update score for tested objects
	 	// Possibly: http://stackoverflow.com/questions/3432/multiple-updates-in-mysql
	 	
	 	//$values = array();
		$placeHolders = array();
	
		// Create Viewed only arrays
		$objectsViewedArray = explode(',', $objectsViewed);
		$objectsViewedOnly = array();
		foreach ($objectsViewedArray as $objectViewedArray) {
			$tmpString = '('.$user_ID.','.$objectViewedArray.',0,0,1)';
			$objectsViewedOnly[] = $tmpString;
			$placeHolders[] = '(%d, %d, %d, %d)';
		}

		// Jumping through jQuery object
		$objectsTestedOnly = array();
		foreach ($objectsTested as $objectTested) {
				$objectToString = '('.$user_ID.','.implode(',',$objectTested).')';
				$objectsTestedOnly[] = $objectToString;
				$placeHolders[] = '(%d, %d, %d, %d)';
		}

		//while ($vocabularyGames->have_posts()) : $vocabularyGames->the_post();
		//$values[] = $post_id.',';
		//$values[] = $times_correct.',';
		//$values[] = $times_wrong.',';
		//$values[] = $times_viewed;
		//$placeHolders[] = '(%d, %d, %d, %d)';
		//endwhile;
		//rewind_posts();

		$values = array_merge($objectsViewedOnly, $objectsTestedOnly);
		$placeHolderCreate = implode(', ', $placeHolders);
	 	
	 	$wpdb->query(
		"
		INSERT INTO wp_user_interactions
		(user_id, post_id, times_correct, times_wrong, times_viewed)
		VALUES ".implode(',',$values)."
		ON DUPLICATE KEY UPDATE times_correct=times_correct+VALUES(times_correct), times_wrong=times_wrong+VALUES(times_wrong), times_viewed=times_viewed+VALUES(times_viewed)
		");
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
add_action('wp_ajax_nopriv_publish_results', 'publish_results');
add_action('wp_ajax_publish_results', 'publish_results');







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









//Run Ajax calls even if user is logged in
if(isset($_REQUEST['action']) && ($_REQUEST['action']=='get_game_category' || $_REQUEST['action']=='get_game_difficulty' || $_REQUEST['action']=='publish_results' || $_REQUEST['action']=='reset_scores')):
	do_action( 'wp_ajax_' . $_REQUEST['action'] );
  do_action( 'wp_ajax_nopriv_' . $_REQUEST['action'] );
endif;

?>