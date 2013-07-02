<?php
/**
 * @package _s
 * @since _s 1.0
 */

// Reflect a view for user on this object (object created if it doesn't already exist)
increment_object_value ( $post->ID, 'times_viewed' );

$postType = get_post_type( $post->ID );
$postTypeObject = get_post_type_object($postType);
$landingPageID = get_connected_object_ID( $post->ID, 'vocabulary_lessons_to_topics' );
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
	'orderby' => 'rand'
));

/**
 *	The Learning Algorithm
 */

// Determine cards to be just shown vs. tested
while ($vocabularyTerms->have_posts()) : $vocabularyTerms->the_post();
	$vocabularyObjectIDs[] = $post->ID;
endwhile;
wp_reset_postdata();

// Retrieve object records
$vocabularyObjectRecords = get_object_record( $vocabularyObjectIDs );
// Create a record if we don't have one already
create_object_record( $vocabularyObjectIDs );

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

	<header class="lesson-header row">
		<h1 class="lesson-title span12"><?php the_title(); ?></h1>
		<h4 class="lesson-instructions span12">Learn the Hawaiian vocabulary term and its pronunciation below.</h4>
		<div class="lesson-progress progress span5">
			<?php
				$width = 100 / $totalLessonCards;
				for ( $i = 0; $i < $totalLessonCards; $i++ ) {
					if ( $i == 0 ) :
						echo '<div class="bar bar-info current" style="width: '.$width.'%;"></div>';
					elseif ( $i == $totalLessonCards - 1 ):
						echo '<div class="bar bar-info last" style="width: '.$width.'%;"></div>';
					else :
						echo '<div class="bar bar-info" style="width: '.$width.'%;"></div>';
					endif;
				}
			?>
		</div>
		<div class="lesson-karma span5 pull-right">
			<?php
				$karmaAllowance = 100 / $totalLessonCards;
				$karmaAllowance = round( 60 / $karmaAllowance );
				for ( $i = 0; $i < $karmaAllowance; $i++ ) {
					echo '<i class="karma-point icon-leaf pull-right"></i>';
				}
			?>
		</div>
	</header><!-- .entry-header -->

	<hr />

	<div class="lesson-content">
		<div class="lesson-feedback alert">
			<span class="lesson-feedback-correct">That's correct!</span>
			<span class="lesson-feedback-incorrect">Whoops! The correct answer was <strong class="lesson-feedback-correct-option"></strong></span>
		</div>
		<?php

		$lessonCardCounter = 0;

		/*
		 * Learn Stack
		 * Display learn cards only if they haven't seen the word before or have demonstrated tha they don't "know" the word.
		 *
		 */
		$teachingCards = new WP_Query( array(
			'post__in' => $lessonCardsToTeach,
			'orderby' => 'post__in',
			'post_type' => 'vocabulary_terms',
		));
		while ( $teachingCards->have_posts() ) : $teachingCards->the_post();

			if ( $lessonCardCounter === 0 ) :
			echo '<div class="lesson-card current" data-lesson-object-id="'.$post->ID.'" data-lesson-object-result="-99">';
			elseif ( $lessonCardCounter == $totalLessonCards - 1 ) :
			echo '<div class="lesson-card last" data-lesson-object-id="'.$post->ID.'" data-lesson-object-result="-99">';
			else :
			echo '<div class="lesson-card" data-lesson-object-id="'.$post->ID.'" data-lesson-object-result="-99">';
			endif;
			echo 	'<h3>'. get_the_title() .'</h3>';
			echo 	'<div style="margin-bottom:15px;">'. get_the_post_thumbnail() . '</div>';
			echo 	'<button class="btn btn-primary play-pronunciation">Play Audio</button>';
			echo 	'<audio class="pronunciation" src="'.get_field('audio_track').'"></audio>';
			echo 	'<button class="btn btn-primary show-english">Show English</button>';
			echo  '<div class="engTranslation hidden">'.get_field('english_translation').'</div>';
			echo '</div>'; // .lesson-card
		
			$lessonCardCounter++;
		endwhile;
		wp_reset_postdata();

		/*
		 * Test Stack
		 * Display learn cards only if they haven't seen the word before or have demonstrated tha they don't "know" the word.
		 *
		 */
		while ( $vocabularyTerms->have_posts() ) : $vocabularyTerms->the_post();

			if ( $lessonCardCounter == $totalLessonCards - 1 ) :
			echo '<div class="lesson-card last" data-lesson-object-id="'.$post->ID.'" data-lesson-object-result="-99">';
			else :
			echo '<div class="lesson-card" data-lesson-object-id="'.$post->ID.'" data-lesson-object-result="-99">';
			endif;
			echo 	'<h3>'. get_the_title() .'</h3>';
			echo 	get_the_post_thumbnail();
			echo 	'<button class="btn btn-primary play-pronunciation">Play Audio</button>';
			echo 	'<audio class="pronunciation" src="'.get_field('audio_track').'"></audio>';
			echo 	'<button class="btn btn-primary show-english">Show English</button>';
			echo  '<div class="engTranslation hidden">'.get_field('english_translation').'</div>';
			echo '</div>'; // .lesson-card
		
			$lessonCardCounter++;
		endwhile;
		rewind_posts();


		?>
	</div><!-- .entry-content -->

	<hr />

	<footer class="lesson-footer">
		<div class="lesson-controls">
			<a class="btn btn-primary advance-lesson" href="javascript:void(0);">Next</a>
			<a class="btn check-lesson" href="javascript:void(0);">Check</a>
			<a class="btn btn-primary finish-lesson" href="javascript:void(0);" data-lesson-outcome="pass" data-currency-type-id="<?php echo $currencyTypeID; ?>" data-landing-id="<?php echo $landingPageID; ?>"><?php echo __('Pau!'); ?></a>
		</div>
	</footer>

	<?php bedrock_postcontentend(); ?>

</article><!-- #post-<?php the_ID(); ?> -->