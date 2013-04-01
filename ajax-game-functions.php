<?php

// VOCABULARY GAME: Step One: Get The Category, Display The Difficulty
function get_game_category() {
	global $wpdb;

	// Nonce check
	$nonce = $_REQUEST['nonce'];
	if (!wp_verify_nonce($nonce, 'ajax_scripts_nonce')) die(__('Busted.'));
	
	$html = "";
	$success = false;
	$gameCategory = $_REQUEST['gameCategory'];

	// Maybe? https://gist.github.com/2351382
	// Seems like get_terms isn't ready/available when this function is called.

	$html .= '<h1>' . $gameCategory . '</h1>';
	$html .= '<hr />';
	$html .= '<h2>Choose a difficulty level:</h2>';

	$difficultyLevels = array('easy', 'medium', 'hard', 'expert');
	foreach ($difficultyLevels as $difficultyLevel) {
		$html .= '<div class="span3">';
		$html .= '<h3><a class="btn difficulty-level" href="javascript:void(0);" data-category="'.$gameCategory.'" data-difficulty="'.$difficultyLevel.'">'.$difficultyLevel.'</a></h3>';
		$html .='</div>';
	}

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
add_action('wp_ajax_nopriv_get_game_category', 'get_game_category');
add_action('wp_ajax_get_game_category', 'get_game_category');


// VOCABULARY GAME: Step Two: Get The Difficulty, Display The Game
function get_game_difficulty() {

	// Get everything from init to load taxonomies
	do_action('init');

	// Nonce check
	$nonce = $_REQUEST['nonce'];
	if (!wp_verify_nonce($nonce, 'ajax_scripts_nonce')) die(__('Busted.'));

	global $wpdb, $wp_query, $post, $terms;

	$html = "";
	$success = false;
	$gameCategory = $_REQUEST['gameCategory'];
	$gameDifficulty = $_REQUEST['gameDifficulty'];

	$html .= '<h3 class="gameInstructions">Listen and repeat each word you hear until you feel comfortable pronouncing each word.</h3>';
	$args = array(
		'tax_query' => array(
			'relation' => 'AND',
			array(
				'taxonomy' => 'vocabulary_categories',
				'field' => 'slug',
				'terms' => $gameCategory,
			),
			array(
				'taxonomy' => 'difficulty_level',
				'field' => 'slug',
				'terms' => $gameDifficulty,
			)
		),
		'posts_per_page' => '10',
		'post_type' => 'vocabulary_terms',
	);
	$gameObjects = new WP_Query( $args );
	$totalGameObjects = $gameObjects->post_count;										// Count the total amount of posts in this set
	$progressCounter = 1;
	$html .= '<div class="gameProgress">';
	while ($gameObjects->have_posts()) : $gameObjects->the_post();	// Create the game progress bar...
		if ($progressCounter == 1): 																	// When the progress counter is at its first object...
		$html .= '<div class="gameProgressPoint current"></div>';	
		elseif ($progressCounter % 4 == 0):		 												// When the progress counter is perfectly divisible by 4...
		$html .= '<div class="gameProgressPoint"></div>';							// Add the next gameObject...
		$html .= '<div class="gameProgressPoint miniGame"></div>'; 		// Then add the miniGameObject.
		elseif ($progressCounter == $totalGameObjects):								// When the progress counter is on its last object...
		$html .= '<div class="gameProgressPoint last"></div>';
		else:
		$html .= '<div class="gameProgressPoint"></div>';							// Otherwise add a normal gameObject
		endif;
		$progressCounter++;
	endwhile;
	wp_reset_postdata();
	$html .= '<div class="gameProgressPoint finish"></div>';
	$html .= '</div>'; 																							// Finish game progress bar.

	$progressCounter = 1;																						// Reset progress counter.
	$viewedGameObjects = array();																		// Start storing post ID's for viewed gameObjects
	$html .= '<div class="gameBoard">';															
	while ($gameObjects->have_posts()) : $gameObjects->the_post();	// Create the game board...
		$title = get_the_title();
		$postID = $post->ID;
		if ($progressCounter % 4 == 0) { 															// If the progress counter is perfectly divisible by 4 (i.e. Every fourth position, load a test)...
			
			$html .= '<div class="gameCard">';													// Load the next standard gameObject (needs to be first in this loop)...
			$html .= '<div class="gameCardControls">';
			// Hawaiian Pronunciation
			$html .= 	'<a class="pronunciationPlay" title="Play the pronunciation."></a>';
			$html .= 	'<audio class="pronunciation" src="'.get_field('audio_track').'"></audio>';
			// Hawaiian Translation
			$html .=	'<div class="translationWrapper">';
			$html .= 	'<a class="toggleHwnTranslation" title="Show the Hawaiian Translation">Show Hawaiian</a>';
			$html .= 	'<div class="hwnTranslation hidden">'.$title.'</div>';
			$html .=	'</div>'; // translationWrapper
			// English Translation
			$html .=	'<div class="translationWrapper">';
			$html .= 	'<a class="toggleEngTranslation" title="Show the English Translation">Show English</a>';
			$html .= 	'<div class="engTranslation hidden">'.get_field('english_translation').'</div>';
			$html .=	'</div>'; // translationWrapper
			$html .= '</div>'; // gameCardControls
			// Image
			if (get_the_post_thumbnail()):
			$html .= 	get_the_post_thumbnail();
			else:
			$html .= 	'<img src="http://placehold.it/600x400" alt="placeholder" />';
			endif;
			$html .= '</div>'; // .gameCard

			$html .= '<div class="gameCard miniGame">';									
			$miniGameArgs = array(																			// Then get three random posts of objects that have been seen...
				'posts_per_page' => 3,
				'orderby' => 'rand',
				'post_type' => 'vocabulary_terms',
				'post__in' => $viewedGameObjects
			);
			$miniGameQuery = new WP_Query($miniGameArgs);								// Generate the mini game...
			$randomCorrectNumber = rand(1,3);														// Generate the correct answer
			$choiceNumber = 1;
			$html .= '<!-- You spent more cheating then you did learning. -->';
			$html .= '<span class="correctAnswer hidden">'.$randomCorrectNumber.'</span>';
			$html .= '<div class="gameChoices row-fluid">';
			while ($miniGameQuery->have_posts()) : $miniGameQuery->the_post();
			$html .= '<div class="gameChoice span5">';
			if ($randomCorrectNumber == $choiceNumber) {
				$html .= '<div class="gameCardControls">';
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

			if ($progressCounter == 1):
			$html .= '<div class="gameCard current">';
			elseif ($totalGameObjects == $progressCounter):
			$html .= '<div class="gameCard last">';
			else:
			$html .= '<div class="gameCard">';
			endif;
			$html .= '<div class="gameCardControls">';
			// Hawaiian Pronunciation
			$html .= 	'<a class="pronunciationPlay" title="Play the pronunciation."></a>';
			$html .= 	'<audio class="pronunciation" src="'.get_field('audio_track').'"></audio>';
			// Hawaiian Translation
			$html .=	'<div class="translationWrapper">';
			$html .= 	'<a class="toggleHwnTranslation" title="Show the Hawaiian Translation">Show Hawaiian</a>';
			$html .= 	'<div class="hwnTranslation hidden">'.$title.'</div>';
			$html .=	'</div>'; // translationWrapper
			// English Translation
			$html .=	'<div class="translationWrapper">';
			$html .= 	'<a class="toggleEngTranslation" title="Show the English Translation">Show English</a>';
			$html .= 	'<div class="engTranslation hidden">'.get_field('english_translation').'</div>';
			$html .=	'</div>'; // translationWrapper
			$html .= '</div>'; // gameCardControls
			// Image
			if (get_the_post_thumbnail()):
			$html .= 	get_the_post_thumbnail();
			else:
			$html .= 	'<img src="http://placehold.it/600x400" alt="placeholder" />';
			endif;
			$html .= '</div>'; // .gameCard
		} // %4

		// Store ID of gameObjects loaded
		$viewedGameObjects[] = $postID;
		$progressCounter++;
	endwhile;
	wp_reset_postdata();

	// Create the Finish Page
	$html .= '<div class="gameCard gameFinish">';
	$html .= '<h2>You&#39;ve completed the Hua activity!</h2>';
	$html .= '<div class="gameResults"></div>';
	$html .= '</div>';

	// Create Next/Check Button
	$html .= '<a class="gameSubmit btn visible" href="javascript:void(0);">Next</a>';
	$html .= '<a class="gameCheck btn hidden" href="javascript:void(0);">Check</a>';
	$html .= '<a class="gameFinish btn hidden" href="javascript:void(0);">Finish</a>';
	$html .= '<a class="gameContinue btn hidden" href="javascript:void(0);">Continue</a>';
	$html .= '<a class="gameRestart btn hidden" href="javascript:void(0);">Restart</a>';
	$html .= '</div>'; // gameBoard

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

//Run Ajax calls even if user is logged in
if(isset($_REQUEST['action']) && ($_REQUEST['action']=='get_game_category' || $_REQUEST['action']=='get_game_difficulty')):
	do_action( 'wp_ajax_' . $_REQUEST['action'] );
  do_action( 'wp_ajax_nopriv_' . $_REQUEST['action'] );
endif;

?>