<?php

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

	$html .= '<h3 class="gameInstructions">Listen and repeat each word you hear until you feel comfortable pronouncing each word.</h3>';
	
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
  	'connected_type' => 'vocabulary_terms_to_vocabuarly_games',
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

/*
 * THE LEARNING ALGORITHM 
 */

	// Assign each ID to a prestige level
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

		$new[] = $object_id;
		// RUN PRESTIGE MACHINE AND SORT INTO PROPER COMFORTABILITY ZONES
		/*
		// "NEW"
		if ($times_viewed == 0):
			$new[] = $object_id;
		
		// "UNTESTED"
		elseif (($times_correct == 0) && ($times_wrong == 0) && ($times_viewed > 0)):
			$untested[] = $object_id;
		
		// "NEUTRAL"
		elseif (($times_correct == $times_wrong) && ($times_viewed > 0)):
			$neutral[] = $object_id;

		// "PRACTICED"
		elseif ($times_correct >= ($times_wrong + 1)):
			$practiced[] = $object_id;

		// "LEARNED"
		elseif ($times_correct >= ($times_wrong + 3)):
			$learned[] = $object_id;

		// "MASTERED"
		elseif ($times_correct >= ($times_wrong + 6)):
			$mastered[] = $object_id;
		
		// "UNFAMILIAR"
		elseif ($times_wrong >= ($times_correct + 1)):
			$unfamiliar[] = $object_id;

		// "FAILED"
		elseif ($times_wrong >= ($times_correct + 3)):
			$failed[] = $object_id;

		endif;

		// NOW CONSTRUCT A TEACHING ARRAY
		$objectsToTeach = array();
		if failed exists
			array_merge($objectsToTeach, $failed)

		if new exists
			array_merge($objectsToTeach, $new)

		// CONSTRUCT A TESTING ARRAY
		if failed exists
			array_merge($objectsToTeach, $failed)
					
		*/
	}

	/*
		TEACHING OBJECT:
			if (failed)
			elseif (unfamiliar)
			elseif (new)
		
		TESTING OBJECT:
			if (failed)
			elseif (untested)
			elseif (new)
			=================
			elseif (unfamiliar)
			elseif (neutral)
      =================
			elseif (practiced/learned/mastered)

		OBJECT 1	TEACH
		OBJECT 2	TEACH
		
		OBJECT 4	TEST
		
		OBJECT 5	TEACH 
		OBJECT 6	TEACH 
		
		OBJECT 8	TEST
		
		OBJECT 9	TEACH 
		OBJECT 10	TEACH 
		
		OBJECT 11	TEST
		OBJECT 12	TEST
		OBJECT 13	TEST
		OBJECT 14	TEST

	*/

	// Identify posts to be displayed in the game based on comfortability zones
	$maxGameObjects = 12; // Max number of objects a game can have
	$cardSort = array();
	$testFrequency = 3; // Number MUST be perfectly divisible (modulus==0)
	$maxTeach = 8; // Maximum number of times a learning card can be considered

	// CARD ONE TEACH
	if(!empty($failed)) {
		$cardSort[] = current($failed);
	} elseif (!empty($unfamiliar)) {
		$cardSort[] = current($unfamiliar);
	} elseif (!empty($new)) {
		$cardSort[] = current($new);
		//$cardSortTeach[] = current($new);
	}

	// CARD TWO TEACH
	if(!empty($failed)) {
		$cardSort[] = next($failed);
	} elseif (!empty($unfamiliar)) {
		$cardSort[] = next($unfamiliar);
	} elseif (!empty($new)) {
		$cardSort[] = next($new);
	}

	// CARD THREE TEST
	if(!empty($failed)) {
		$cardSort[] = next($failed);
	} elseif (!empty($unfamiliar)) {
		$cardSort[] = next($unfamiliar);
	} elseif (!empty($new)) {
		$cardSort[] = next($new);
	}

	// CARD FOUR TEACH
	if(!empty($failed)) {
		$cardSort[] = next($failed);
	} elseif (!empty($unfamiliar)) {
		$cardSort[] = next($unfamiliar);
	} elseif (!empty($new)) {
		$cardSort[] = next($new);
	}

	// CARD FIVE TEACH
	if(!empty($failed)) {
		$cardSort[] = next($failed);
	} elseif (!empty($unfamiliar)) {
		$cardSort[] = next($unfamiliar);
	} elseif (!empty($new)) {
		$cardSort[] = next($new);
	}

	// CARD SIX TEST
	if(!empty($failed)) {
		$cardSort[] = next($failed);
	} elseif (!empty($unfamiliar)) {
		$cardSort[] = next($unfamiliar);
	} elseif (!empty($new)) {
		$cardSort[] = next($new);
	}

	// CARD SEVEN TEACH
	if(!empty($failed)) {
		$cardSort[] = next($failed);
	} elseif (!empty($unfamiliar)) {
		$cardSort[] = next($unfamiliar);
	} elseif (!empty($new)) {
		$cardSort[] = next($new);
	}

	// CARD EIGHT TEACH
	if(!empty($failed)) {
		$cardSort[] = next($failed);
	} elseif (!empty($unfamiliar)) {
		$cardSort[] = next($unfamiliar);
	} elseif (!empty($new)) {
		$cardSort[] = next($new);
	}
	// CARD NINE
	if(!empty($failed)) {
		$cardSort[] = next($failed);
	} elseif (!empty($unfamiliar)) {
		$cardSort[] = next($unfamiliar);
	} elseif (!empty($new)) {
		$cardSort[] = next($new);
	}
	// CARD TEN
	if(!empty($failed)) {
		$cardSort[] = next($failed);
	} elseif (!empty($unfamiliar)) {
		$cardSort[] = next($unfamiliar);
	} elseif (!empty($new)) {
		$cardSort[] = next($new);
	}
	// CARD ELEVEN
	if(!empty($failed)) {
		$cardSort[] = next($failed);
	} elseif (!empty($unfamiliar)) {
		$cardSort[] = next($unfamiliar);
	} elseif (!empty($new)) {
		$cardSort[] = next($new);
	}
	// CARD TWELVE
	if(!empty($failed)) {
		$cardSort[] = next($failed);
	} elseif (!empty($unfamiliar)) {
		$cardSort[] = next($unfamiliar);
	} elseif (!empty($new)) {
		$cardSort[] = next($new);
	}
	// CARD THIRTEEN
	if(!empty($failed)) {
		$cardSort[] = next($failed);
	} elseif (!empty($unfamiliar)) {
		$cardSort[] = next($unfamiliar);
	} elseif (!empty($new)) {
		$cardSort[] = next($new);
	}
	// CARD FOURTEEN
	if(!empty($failed)) {
		$cardSort[] = next($failed);
	} elseif (!empty($unfamiliar)) {
		$cardSort[] = next($unfamiliar);
	} elseif (!empty($new)) {
		$cardSort[] = next($new);
	}
	// CARD FIFTEEN
	if(!empty($failed)) {
		$cardSort[] = next($failed);
	} elseif (!empty($unfamiliar)) {
		$cardSort[] = next($unfamiliar);
	} elseif (!empty($new)) {
		$cardSort[] = next($new);
	}


/**
	*
	*
	*
	*  	 THE GAME 
	*
	*
	*
	*/

	// CARD SORT: All cards in the loop
	// CARD SORT TEACH: All cards deemed necessary to teach
	// CARD SORT TEST: All cards deemed necessary to test

	/*
	 * Generate the game based on the feedback from the learning algorithm
	 */
	$sortedGameObjectsArgs = array(
		'orderby' => 'post__in',
		'post__in' => $cardSort,
		'posts_per_page' => $maxGameObjects,
	);
	$finalGameObjectsArgs = array_merge($sortedGameObjectsArgs, $gameObjectsArgs);

	$gameObjects = new WP_Query($finalGameObjectsArgs);
	$test_frequency = 4; // Every two cards, test
	$totalGameObjects = $gameObjects->post_count; // Number of actual objects in a game
	

	/*
	 * GENERATE THE GAME NAVIGATION
	 */
	$cardIndex = 1;
	$html .= '<div class="gameProgress">';
	while ($gameObjects->have_posts()) : $gameObjects->the_post();	// Create the game progress bar...
		if ($cardIndex % $test_frequency == 0):		 								// When the progress counter is perfectly divisible by 4...
		$html .= '<div class="gameProgressPoint miniGame"></div>'; 		// Then add the miniGameObject.
		else:
		$html .= '<div class="gameProgressPoint"></div>';							// Otherwise add a normal gameObject
		endif;
		$cardIndex++;
	endwhile;
	wp_reset_postdata();
	$html .= '<div class="gameProgressPoint finish"></div>';
	$html .= '</div>'; 																							// Finish game progress bar.


	/*
	 * GENERATE THE GAME BASED ON QUERY
	 */
	$cardIndex = 1;																									// Reset progress counter.
	$viewedGameObjects = array();																		// Start storing post ID's for viewed gameObjects
	$html .= '<div class="gameBoard">';															
	while ($gameObjects->have_posts()) : $gameObjects->the_post();	// Create the game board...

		// THE "LEARN" CARD: Vocabulary
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



		// GAME BOARD CONSTRUCTION
		if ($cardIndex % $test_frequency == 0) { 															// If the progress counter is perfectly divisible by 4 (i.e. Every fourth position, load a test)...
			// Store ID of gameObjects loaded
			$viewedGameObjects[] = $post->ID;

			// Then get three random posts of objects that have been seen...
			$miniGameQuery = new WP_Query( array(
				'posts_per_page' => 3,
				'orderby' => 'rand',
				'post_type' => 'vocabulary_terms',
				'post__in' => $viewedGameObjects
			));
			$numberOfTestItems = $miniGameQuery->post_count;							
			$randomCorrectNumber = rand(1,$numberOfTestItems);														// Generate the correct answer
			$choiceNumber = 1;

			// Generate the mini game...
			$html .= '<div class="gameCard miniGame">';
			$html .= '<!-- You spent more cheating then you did learning. -->';
			$html .= '<span class="correctAnswer hidden">'.$randomCorrectNumber.'</span>';
			$html .= '<div class="gameChoices row-fluid">';
			while ($miniGameQuery->have_posts()) : $miniGameQuery->the_post();
			$html .= '<div class="gameChoice span5">';

			// Only generate an audio file for the correct number
			if ($randomCorrectNumber == $choiceNumber) {
				$html .= '<div class="gameCardControls" data-card-id="'.$post->ID.'">';
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

			// Image
			if (get_the_post_thumbnail()):
			$html .= 	'<a href="javascript:void(0);" id="'.$choiceNumber.'" class="choiceSelect" title="Choice for answer">'.get_the_post_thumbnail().'</a>';
			else:
			$html .= 	'<a href="javascript:void(0);" id="'.$choiceNumber.'" class="choiceSelect" title="Choice for answer"><img src="http://placehold.it/600x400" alt="placeholder" /></a>';
			endif;
			$html .= '</div>'; // gameChoice
			
			$choiceNumber++;
			endwhile;
			$html .= '</div>'; // gameChoices
			wp_reset_postdata();
			$html .= '</div>'; // miniGame

		} else {																											// Otherwise generate a standard gameObject
			// Store ID of gameObjects loaded
			$viewedGameObjects[] = $post->ID;
			//Learn Card
			$html .= $vocabularyCard;

		} // %4

		$cardIndex++;
	endwhile;
	wp_reset_postdata();
	$html .= '</div>'; // gameBoard



	// User Game Controls
	$html .= '<div class="gameUserControls">';
	$html .= '<a class="gameNext btn visible" href="javascript:void(0);">Next</a>';
	$html .= '<a class="gameCheck btn hidden" href="javascript:void(0);">Check</a>';
	$html .= '<a class="gameFinish btn hidden" href="javascript:void(0);">Finish</a>';
	$html .= '<a class="gameContinue btn hidden" href="javascript:void(0);">Continue</a>';
	$html .= '<a class="gameRestart btn hidden" href="javascript:void(0);">Restart</a>';
	$html .= '</div>'; // gameUserControls

	// Results Bin
	$html .= '<div class="gameResults" data-total-tested="0" data-total-correct="0">';
	$html .= '<div class="cardsViewed" data-viewed="'.implode(', ',array_filter($cardSort)).'"></div>';
	$html .= '<div class="cardsTested"></div>';
	$html .= '</div>';

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

	//error_log(print_r($objectsTested,true));

	$html .= '<h3>You&#39;ve completed the Hua activity!</h3>';
	$html .= '<hr />';
	$html .= 'You scored ';
	$html .= $totalCorrect;
	$html .= ' out of ';
	$html .= $totalTested;
	$html .= '!';
	$html .= '<a class="gameFinish btn" href="javascript:void(0);">Finish</a>';
	$html .= '<a class="gameRestart btn" href="javascript:void(0);">Restart</a>';
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
		//error_log(print_r($objectsViewedOnly, true));

		// Jumping through jQuery object
		$objectsTestedOnly = array();
		foreach ($objectsTested as $objectTested) {
				$objectToString = '('.$user_ID.','.implode(',',$objectTested).')';
				$objectsTestedOnly[] = $objectToString;
				$placeHolders[] = '(%d, %d, %d, %d)';
		}
		//error_log(print_r($objectsTestedOnly, true));

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

//Run Ajax calls even if user is logged in
if(isset($_REQUEST['action']) && ($_REQUEST['action']=='get_game_category' || $_REQUEST['action']=='get_game_difficulty' || $_REQUEST['action']=='publish_results')):
	do_action( 'wp_ajax_' . $_REQUEST['action'] );
  do_action( 'wp_ajax_nopriv_' . $_REQUEST['action'] );
endif;

?>