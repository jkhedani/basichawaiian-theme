<?php

// VOCABULARY GAME: Step One: Get The Category, Display The Difficulty
// function get_game_category() {
// 	global $wpdb;

// 	// Nonce check
// 	$nonce = $_REQUEST['nonce'];
// 	if (!wp_verify_nonce($nonce, 'ajax_scripts_nonce')) die(__('Busted.'));
	
// 	$html = "";
// 	$success = false;
// 	$gameCategory = $_REQUEST['gameCategory'];

// 	// Maybe? https://gist.github.com/2351382
// 	// Seems like get_terms isn't ready/available when this function is called.

// 	$html .= '<h1>' . $gameCategory . '</h1>';
// 	$html .= '<hr />';
// 	$html .= '<h2>Choose a difficulty level:</h2>';

// 	$difficultyLevels = array('easy', 'medium', 'hard', 'expert');
// 	foreach ($difficultyLevels as $difficultyLevel) {
// 		$html .= '<div class="span3">';
// 		$html .= '<h3><a class="btn difficulty-level" href="javascript:void(0);" data-category="'.$gameCategory.'" data-difficulty="'.$difficultyLevel.'">'.$difficultyLevel.'</a></h3>';
// 		$html .='</div>';
// 	}

// 	// Build the response...
// 	$success = true;
// 	$response = json_encode(array(
// 		'success' => $success,
// 		'html' => $html
// 	));
	
// 	// Construct and send the response
// 	header("content-type: application/json");
// 	echo $response;
// 	exit;
// }
// add_action('wp_ajax_nopriv_get_game_category', 'get_game_category');
// add_action('wp_ajax_get_game_category', 'get_game_category');


// VOCABULARY GAME: Step Two: Get The Difficulty, Display The Game
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
	$gameCategory = $_REQUEST['gameCategory'];
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
	  'posts_per_page' => '10',
		'post_type' => 'vocabulary_terms',
	);
	$gameObjects = new WP_Query($gameObjectsArgs);

	//error_log(print_r($gameObjects, true));

	// All IDs associated with this game	
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

	 	//error_log(print_r($gameObjectsViewed,true));

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

/**
	*
	*
	*
	*  	 THE LEARNING ALGORITHM 
	*
	*
	*
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

		// RUN PRESTIGE MACHINE AND SORT INTO PROPER COMFORTABILITY ZONES
		//if ($times_viewed == 0) {
			$new[] = $object_id;
		//}
	}

	error_log(print_r($new, true));

	// Sort levels of comfortability by priorities
	$cardSort = array();		
	// CARD ONE
	if(!empty($failed)) {
		$cardSort[] = current($failed);
	} elseif (!empty($unfamiliar)) {
		$cardSort[] = current($unfamiliar);
	} elseif (!empty($new)) {
		$cardSort[] = current($new);
	}
	// CARD TWO
	if(!empty($failed)) {
		$cardSort[] = next($failed);
	} elseif (!empty($unfamiliar)) {
		$cardSort[] = next($unfamiliar);
	} elseif (!empty($new)) {
		$cardSort[] = next($new);
	}
	// CARD THREE
	if(!empty($failed)) {
		$cardSort[] = next($failed);
	} elseif (!empty($unfamiliar)) {
		$cardSort[] = next($unfamiliar);
	} elseif (!empty($new)) {
		$cardSort[] = next($new);
	}
	// CARD FOUR
	if(!empty($failed)) {
		$cardSort[] = next($failed);
	} elseif (!empty($unfamiliar)) {
		$cardSort[] = next($unfamiliar);
	} elseif (!empty($new)) {
		$cardSort[] = next($new);
	}
	// CARD FIVE
	if(!empty($failed)) {
		$cardSort[] = next($failed);
	} elseif (!empty($unfamiliar)) {
		$cardSort[] = next($unfamiliar);
	} elseif (!empty($new)) {
		$cardSort[] = next($new);
	}
	// CARD SIX
	if(!empty($failed)) {
		$cardSort[] = next($failed);
	} elseif (!empty($unfamiliar)) {
		$cardSort[] = next($unfamiliar);
	} elseif (!empty($new)) {
		$cardSort[] = next($new);
	}
	// CARD SEVEN
	if(!empty($failed)) {
		$cardSort[] = next($failed);
	} elseif (!empty($unfamiliar)) {
		$cardSort[] = next($unfamiliar);
	} elseif (!empty($new)) {
		$cardSort[] = next($new);
	}
	// CARD EIGHT
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

	// Generate the game based on the feedback from the learning algorithm
	$gameObjectsArgs = array(
		'connected_type' => 'vocabulary_terms_to_vocabuarly_games',
	  'connected_items' => $connectedTo,
	  'connected_direction' => 'to',
	  'nopaging' => true,
		'orderby' => 'post__in',
		'post_type' => 'vocabulary_terms',
		'post__in' => $cardSort,
	);
	$gameObjects = new WP_Query($gameObjectsArgs);

	// GENERATE THE GAME NAVIGATION
	$cardStackAmount = $gameObjects->post_count;										// Count the total amount of posts in this set
	$test_frequency = 4; // Every two cards, test
	$testCardAmount = $cardStackAmount / $test_frequency;

	$totalGameObjects = $gameObjects->post_count;
	
	$cardIndex = 1;
	$html .= '<div class="gameProgress">';
	while ($gameObjects->have_posts()) : $gameObjects->the_post();	// Create the game progress bar...
		if ($cardIndex == 1): 																	// When the progress counter is at its first object...
		$html .= '<div class="gameProgressPoint current"></div>';	
		elseif ($cardIndex % $test_frequency == 0):		 												// When the progress counter is perfectly divisible by 4...
		$html .= '<div class="gameProgressPoint"></div>';							// Add the next gameObject...
		$html .= '<div class="gameProgressPoint miniGame"></div>'; 		// Then add the miniGameObject.
		elseif ($cardIndex == $totalGameObjects):								// When the progress counter is on its last object...
		$html .= '<div class="gameProgressPoint last"></div>';
		else:
		$html .= '<div class="gameProgressPoint"></div>';							// Otherwise add a normal gameObject
		endif;
		$cardIndex++;
	endwhile;
	wp_reset_postdata();
	$html .= '<div class="gameProgressPoint finish"></div>';
	$html .= '</div>'; 																							// Finish game progress bar.

	// GENERATE THE GAME BASED ON QUERY
	$cardIndex = 1;																						// Reset progress counter.
	$viewedGameObjects = array();																		// Start storing post ID's for viewed gameObjects
	$html .= '<div class="gameBoard">';															
	while ($gameObjects->have_posts()) : $gameObjects->the_post();	// Create the game board...
		// $title = get_the_title();
		// $postID = $post->ID;

		// THE "LEARN" CARD: Vocabulary
		// Card: Open
		if ($cardIndex == 1):
			$vocabCardOpen = '<div class="gameCard current">';
		elseif ($totalGameObjects == $cardIndex):
			$vocabCardOpen = '<div class="gameCard last">';
		else:
			$vocabCardOpen = '<div class="gameCard">';
		endif;
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


		if ($cardIndex % $test_frequency == 0) { 															// If the progress counter is perfectly divisible by 4 (i.e. Every fourth position, load a test)...
			// Store ID of gameObjects loaded
			$viewedGameObjects[] = $post->ID;
			
			// Learn Card
			$html .= $vocabularyCard;

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