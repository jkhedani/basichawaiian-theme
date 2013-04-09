<?php
/**
 * @package _s
 * @since _s 1.0
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<?php bedrock_postcontentstart(); ?>

	<header class="entry-header">
		
		<?php
			// BREADCRUMB
			echo '<ul class="breadcrumb">';
			echo 	'<li class="breadcrumb-home"><a href="'.get_home_url().'" title="Go back home.">Home</a> <span class="divider">/</span></li>';
			echo 	'<li class="breadcrumb-last active">'.get_the_title().'</li>';
			echo '</ul>';
		?>

		<?php bedrock_abovetitle(); ?>
		
		<h1 class="entry-title"><?php the_title(); ?></h1>
		
		<?php bedrock_belowtitle(); ?>
		
		<hr />

		<div class="entry-meta">
			<?php //_s_posted_on(); ?>
		</div><!-- .entry-meta -->
	</header><!-- .entry-header -->

	<div class="entry-content">
		<?php
		the_content();
		// "Display applicable Lesson Types (i.e. Vocabulary Games, Pronoun Practices, etc.)"

		// VOCABULARY GAMES
		// If this module page has Vocabulary Game(s)...
		$moduleHasVocabGame = p2p_connection_exists( 'vocabulary_games_to_modules', array('to'=> get_queried_object()) );
		if ($moduleHasVocabGame) {
			// ...display connected Vocabulary Games
			// $vocabularyGames = new WP_Query(array(
			//   'connected_type' => 'vocabulary_games_to_modules',
			//   'connected_items' => get_queried_object(),
			//   'nopaging' => true,
			// ));
			// echo '<ul>';
			// echo 	'<li>Vocabulary Games';
			// echo 		'<ul>';
			// while ($vocabularyGames->have_posts()) : $vocabularyGames->the_post();
			// 	echo 		'<li><a href="'.get_permalink().'">'.get_the_title().'</a></li>';
			// endwhile;	
			// wp_reset_postdata();
			// echo 		'</ul>';
			// echo 	'</li>';
			// echo '</ul>';

			// Retrieve all vocabulary games associated with this module...
			$vocabularyGames = new WP_Query(array(
				'connected_type' => 'vocabulary_games_to_modules',
				'connected_items' => get_queried_object(),
				'nopaging' => true,
			));

			$vocabularyGameIDs = array();
			while ($vocabularyGames->have_posts()) : $vocabularyGames->the_post();
				$vocabularyGameIDs[] = $post->ID;
			endwhile;
			rewind_posts();

			if(is_user_logged_in()) {
			 	$current_user = wp_get_current_user();
			 	$user_ID = $current_user->ID;
			 	$post_ids = implode(',',$vocabularyGameIDs);

			 	// Check if vocabulary have been completed (using the data later that's why)
			 	$vocabGamesCompleted = $wpdb->get_results($wpdb->prepare(
			 		"
			 		SELECT times_completed
			 		FROM wp_user_interactions
					WHERE user_id = %d
						AND post_id IN (%s)
					LIMIT 0, 10
					"
					, $user_ID, $post_ids
				));

				if(count($vocabularyGameIDs) != count($vocabGamesCompleted)) {
					// construct the loop for publishing
					$values = array();
					$placeHolders = array();

					// Prepare individual values separately to get passed to the query
					while ($vocabularyGames->have_posts()) : $vocabularyGames->the_post();
						$values[] = $user_ID.',';
						$values[] = $post->ID.',';
						$values[] = '0';
						$placeHolders[] = '(%d, %d, %d)';
					endwhile;
					rewind_posts();

					// Prepare placeholders for the query
					$placeHolderCreate = implode(', ', $placeHolders);

					// Insert records for the user
					$wpdb->query( $wpdb->prepare("
						INSERT IGNORE INTO wp_user_interactions
						( user_id, post_id, times_completed )
						VALUES ".$placeHolderCreate."
					", $values ));
				}
			} // is_user_logged_in

			rewind_posts();

			echo '<ul>';
			// Display all vocab games associated with this module
			$indexCount = 0;
			while ($vocabularyGames->have_posts()) : $vocabularyGames->the_post();
				echo '<li>';
				echo 	'<a class="vocabulary-game" href="'.get_permalink().'" title="Go to the'.get_the_title().' activity"';
				
				if ($vocabGamesCompleted[$indexCount]->times_completed == 0) {
					echo 'data-complete="0"';
				} else {
					echo 'data-complete="1"';
				}
				echo 	'>';
				echo 		'<h2>'.get_the_title().'</h2>';
				echo 	'</a>';
				echo '</li>';
				$indexCount++;
			endwhile;
			echo '</ul>';
			wp_reset_postdata();

		} // VOCABULARY GAMES
		
		// PRONOUN PRACTICE
		// If this module page has Vocabulary Game(s)...
		$moduleHasPronPractice = p2p_connection_exists( 'pronoun_practices_to_modules', array('to'=> get_queried_object()) );
		if ($moduleHasPronPractice) {
			// ...display connected Vocabulary Games
			$pronounPractices = new WP_Query(array(
			  'connected_type' => 'pronoun_practices_to_modules',
			  'connected_items' => get_queried_object(),
			  'nopaging' => true,
			));
			echo '<ul>';
			echo 	'<li>Pronoun Practices';
			echo 		'<ul>';
			while ($pronounPractices->have_posts()) : $pronounPractices->the_post();
				echo 		'<li><a href="'.get_permalink().'">'.get_the_title().'</a></li>';
			endwhile;	
			wp_reset_postdata();
			echo 		'</ul>';
			echo 	'</li>';
			echo '</ul>';
		} // PRONOUN PRACTICE
		?>
		<?php wp_link_pages( array( 'before' => '<div class="page-links">' . __( 'Pages:', '_s' ), 'after' => '</div>' ) ); ?>
	</div><!-- .entry-content -->

	<?php bedrock_postcontentend(); ?>

</article><!-- #post-<?php the_ID(); ?> -->