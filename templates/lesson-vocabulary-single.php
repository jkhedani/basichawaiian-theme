<?php
/**
 * @package _s
 * @since _s 1.0
 */

// Reflect a view for user on this object (object created if it doesn't already exist)
increment_object_value ( $post->ID, 'times_viewed' );

$postType = get_post_type( $post->ID );
$postTypeObject = get_post_type_object($postType);
$landingPageID = get_connected_object_ID( $post->ID, 'vocabulary_lessons_to_topics', 'topics_to_modules', 'modules_to_units' );
$currencyTypeID = get_connected_object_ID( $post->ID, 'vocabulary_lessons_to_topics', 'topics_to_modules', 'modules_to_units' );

if ( is_object_complete ( $post->ID ) ) {
	$objectCompleted = 1;
} else {
	$objectCompleted = 0;
};

// get list of all connected vocabulary terms
$vocabularyTerms = new WP_Query( array(
	'connected_type' => 'vocabulary_terms_to_vocabulary_lessons',
  'connected_items' => $post->ID,
  'nopaging' => true,
	'post_type' => 'vocabulary_terms',
	'orderby' => 'rand',
	'posts_per_page' => -1,
	'posts_per_archive_page' => -1,
));

/**
 *	The Learning Algorithm
 */

// Determine cards to be just shown vs. tested
while ($vocabularyTerms->have_posts()) : $vocabularyTerms->the_post();
	$vocabularyObjectIDs[] = $post->ID;
endwhile;
wp_reset_postdata();

// Create a record if we don't have one already
create_object_record( $vocabularyObjectIDs );
// Retrieve object records
$vocabularyObjectRecords = get_object_record( $vocabularyObjectIDs );

// Assign each ID to a prestige level
$new = array();
$untested = array();
$practiced = array();
$learned = array();
$mastered = array();
$neutral = array();
$unfamiliar = array();
$failed = array();
foreach ( $vocabularyObjectRecords as $vocabularyObjectRecord ) {
	$object_id = $vocabularyObjectRecord->post_id;
	$times_correct = $vocabularyObjectRecord->times_correct;
	$times_wrong = $vocabularyObjectRecord->times_wrong;
	$times_viewed = $vocabularyObjectRecord->times_viewed;

	// "NEW"
	if ( $times_viewed == 0 ):
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

// Grab all cards we want to teach...
$lessonCardsToTeach = array_merge( $failed, $unfamiliar, $new );
$lessonCardsToTeachCount = count($lessonCardsToTeach);

// Count the total amount of lesson objects
$totalLessonCards = $vocabularyTerms->post_count + $lessonCardsToTeachCount;

?>

<article id="post-<?php the_ID(); ?>" <?php post_class('lesson-container'); ?> data-lesson-id="<?php echo $post->ID; ?>" data-lesson-complete="<?php echo $objectCompleted; ?>">

	<header class="lesson-header">
		<?php $previousurl = htmlspecialchars($_SERVER['HTTP_REFERER']); ?>
		<a href="<?php echo $previousurl; ?>" class="lesson-quit">Quit</a>
		<?php
		 // Learn Instructional Text
		 if ( get_field('vocab_lesson_optional_learn_instructional_text') ) {
		 	echo '<h4 class="lesson-instructions learn-instructions hide span12">'.get_field('vocab_lesson_optional_learn_instructional_text').'</h4>';
		 } else {
		 	echo '<h4 class="lesson-instructions learn-instructions hide span12">Learn the Hawaiian vocabulary term and its pronunciation below.</h4>';
		 }

		 // Test Instructional Text
		 if ( get_field('vocab_lesson_optional_test_instructional_text') ) {
		 	echo '<h4 class="lesson-instructions test-instructions hide span12">'.get_field('vocab_lesson_optional_test_instructional_text').'</h4>';
		 } else {
			echo '<h4 class="lesson-instructions test-instructions hide span12">Select the image the matches the hawaiian word below.</h4>'; 	
		 }
		
		?>
	</header><!-- .entry-header -->

	<div class="lesson-content">

		<!-- Feedback -->
		<div class="lesson-feedback alert">
			<span class="lesson-feedback-correct">That's correct!</span>
			<span class="lesson-feedback-incorrect">Aue! The correct answer was <strong class="lesson-feedback-correct-option"></strong></span>
		</div>

		<!-- Lesson Progress -->
		<div class="lesson-progress">
			<?php
				for ( $i = 0; $i < $totalLessonCards; $i++ ) {
					if ( $i == 0 ) :
						echo '<div class="lei-counter viewed current"></div>';
					elseif ( $i == $totalLessonCards - 1 ):
						echo '<div class="lei-counter last"></div>';
					else :
						echo '<div class="lei-counter"></div>';
					endif;
				}
				// $width = 100 / $totalLessonCards;
				// for ( $i = 0; $i < $totalLessonCards; $i++ ) {
				// 	if ( $i == 0 ) :
				// 		echo '<div class="lei-counter viewed current" style="width: '.$width.'%;"></div>';
				// 	elseif ( $i == $totalLessonCards - 1 ):
				// 		echo '<div class="lei-counter last" style="width: '.$width.'%;"></div>';
				// 	else :
				// 		echo '<div class="lei-counter" style="width: '.$width.'%;"></div>';
				// 	endif;
				// }
			?>
		</div>

		<!-- Lesson Karma -->
		<div class="lesson-karma pull-right">
			<?php
				$percentToPass = 0.90;
				$karmaAllowance = count($vocabularyObjectIDs) * $percentToPass; // Vocab karma allowance based on total amount of testable cards.
				$karmaAllowance = round( count($vocabularyObjectIDs) - $karmaAllowance );

				for ( $i = 0; $i < $karmaAllowance; $i++ ) {
					echo '<i class="karma-point"></i>';
				}
			?>
		</div>

		
		<?php

		$lessonCardCounter = 0;

		/*
		 * Learn Stack
		 * Display learn cards only if they haven't seen the word before or have demonstrated tha they don't "know" the word.
		 *
		 */
		if ( !empty($lessonCardsToTeach) ) {
		$teachingCards = new WP_Query( array(
			'post__in' => $lessonCardsToTeach,
			'orderby' => 'post__in',
			'post_type' => 'vocabulary_terms',
			'posts_per_page' => -1,
			'posts_per_archive_page' => -1,
		));
		while ( $teachingCards->have_posts() ) : $teachingCards->the_post();

			if ( $lessonCardCounter === 0 ) :
			echo '<div class="lesson-card learn-card current" data-lesson-object-id="'.$post->ID.'" data-lesson-object-result="-99">';
			else :
			echo '<div class="lesson-card learn-card" data-lesson-object-id="'.$post->ID.'" data-lesson-object-result="-99">';
			endif;
			echo 	'<h3>'. get_the_title() .'</h3>';
			echo 	'<div style="margin-bottom:15px;">'. get_the_post_thumbnail($post->ID, 'medium') . '</div>';
			
			echo 	'<button class="play-audio">Play Audio</button>';
			echo 	'<button class="pause-audio">Pause Audio</button>';
			echo 	'<audio class="pronunciation" src="'.get_field('audio_track').'"></audio>';

			if ( get_field('english_translation') ) {
			echo 	'<button class="btn show-translation lang-hawaiian"><span>Show</span> Hawaiian</button>';
			echo 	'<button class="btn show-translation lang-english"><span>Show</span> English</button>';
			echo  '<div class="translation english-translation hidden">'.get_field('english_translation').'</div>';
		  }
			echo '</div>'; // .lesson-card
		
			$lessonCardCounter++;
		endwhile;
		wp_reset_postdata();
		}

		/*
		 * Test Stack
		 * Display learn cards only if they haven't seen the word before or have demonstrated tha they don't "know" the word.
		 *
		 */
		while ( $vocabularyTerms->have_posts() ) : $vocabularyTerms->the_post();
			if ( $lessonCardCounter === 0 ) :
			echo '<div class="lesson-card test-card current" data-lesson-object-id="'.$post->ID.'" data-lesson-object-result="-99">';
			elseif ( $lessonCardCounter == $totalLessonCards - 1 ) :
			echo '<div class="lesson-card test-card last" data-lesson-object-id="'.$post->ID.'" data-lesson-object-result="-99">';
			else :
			echo '<div class="lesson-card test-card" data-lesson-object-id="'.$post->ID.'" data-lesson-object-result="-99">';
			endif;
			echo 	'<h3>'. get_the_title() .'</h3>';

			// Randomize output by storing options in array, shuffling then displaying content
			$lessonAssessmentOptions =  array();
			$lessonAssessmentFalseOptions = array_rand( $vocabularyObjectIDs , 2 );
			$lessonAssessmentOptions[] = $post->ID;
			$lessonAssessmentOptions[] = $vocabularyObjectIDs[$lessonAssessmentFalseOptions[0]];
			$lessonAssessmentOptions[] = $vocabularyObjectIDs[$lessonAssessmentFalseOptions[1]];
		
			shuffle($lessonAssessmentOptions);
			echo '<div class="lesson-card-assessment">';
			echo '<!-- You spent more cheating then you did learning. -->';
				foreach ( $lessonAssessmentOptions as $lessonAssessmentOption ) {
					if ( get_field('english_translation') ) {
						$englishTranslation = get_field('english_translation' , $lessonAssessmentOption);	
					}
					
					if ( $lessonAssessmentOption == $post->ID ) :
						echo '<a class="lesson-card-assessment-option correct-option">';
						echo '<div class="lesson-card-image-wrap">';
						echo 	get_the_post_thumbnail($lessonAssessmentOption, 'post-thumbnail', array( 'alt' => get_field('english_translation', $lessonAssessmentOption), ));
						echo '</div>';
						echo $englishTranslation . '</a>';
					else :
						echo '<a class="lesson-card-assessment-option">';
						echo '<div class="lesson-card-image-wrap">';
						echo 	get_the_post_thumbnail($lessonAssessmentOption, 'post-thumbnail', array( 'alt' => get_field('english_translation', $lessonAssessmentOption), ));
						echo '</div>';
						echo $englishTranslation.'</a>';
					endif;
				}
			echo '</div>';

			echo 	'<button class="play-audio">Play Audio</button>';
			echo 	'<button class="pause-audio">Pause Audio</button>';
			echo 	'<audio class="pronunciation" src="'.get_field('audio_track').'"></audio>';

			echo 	'<button class="btn btn-primary show-translation lang-english"><span>Show</span> English</button>';
			echo  '<div class="translation english-translation hidden">'.get_field('english_translation').'</div>';


			echo '</div>'; // .lesson-card
		
			$lessonCardCounter++;
		endwhile;
		rewind_posts();


		?>
	</div><!-- .entry-content -->

	<footer class="lesson-footer">
		<div class="lesson-controls">
			<a class="btn btn-cta blue advance-lesson" href="javascript:void(0);">Next <i class="fa fa-arrow-right"></i></a>
			<a class="btn btn-cta blue check-lesson" href="javascript:void(0);">Check <i class="fa fa-check"></i></a>
			<a class="btn btn-cta blue finish-lesson" href="javascript:void(0);" data-lesson-outcome="pass" data-currency-type-id="<?php echo $currencyTypeID; ?>" data-landing-id="<?php echo $landingPageID; ?>"><?php echo __('Pau!'); ?></a>
		</div>
	</footer>

</article><!-- #post-<?php the_ID(); ?> -->