<?php
/**
 * @package _s
 * @since _s 1.0
 */

// Reflect a view for user on this object (object created if it doesn't already exist)
increment_object_value ( $post->ID, 'times_viewed' );

$postType = get_post_type( $post->ID );
$postTypeObject = get_post_type_object($postType);
$landingPageID = get_connected_object_ID( $post->ID, 'phrases_lessons_to_topics' );
$currencyTypeID = get_connected_object_ID( $post->ID, 'phrases_lessons_to_topics', 'topics_to_modules', 'modules_to_units' );

// get list of all connected vocabulary terms
$phrases = new WP_Query( array(
	'connected_type' => 'phrases_to_phrases_lessons',
  'connected_items' => $post->ID,
  'nopaging' => true,
	'post_type' => 'phrases',
));

// Count the total amount of lesson objects
$lessonCardCounter = 0;
$lessonCardCount = $phrases->post_count;

?>

<article id="post-<?php the_ID(); ?>" <?php post_class('lesson-container'); ?> data-lesson-id="<?php echo $post->ID; ?>">

	<header class="lesson-header">
		<h1 class="lesson-title"><?php the_title(); ?></h1>

		<div class="lesson-progress span5">
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
			?>
		</div>

		<div class="lesson-karma pull-right">
			<?php
				$karmaAllowance = 100 / $lessonCardCount;
				$karmaAllowance = round( 60 / $karmaAllowance );
				for ( $i = 0; $i < $karmaAllowance; $i++ ) {
					echo '<i class="karma-point icon-leaf icon-white pull-right"></i>';
				}
			?>
		</div>

		<?php
			// Second loop to grab optional instructional text
			$lessonCardCounter = 0;
			while ( $phrases->have_posts() ) : $phrases->the_post();

				if ( $lessonCardCounter == 0 ) {
					echo '<h4 class="lesson-instructions current">';
				} else {
					echo '<h4 class="lesson-instructions">';
				}

				if ( get_field('phrases_optional_instructional_text') ) {
					echo get_field('phrases_optional_instructional_text');
				} else {
					echo 'Choose the English sentence that best represents the Hawaiian phrase below.';
				}
				$lessonCardCounter++;
				echo '</h4>';
			endwhile;
			wp_reset_postdata();
		?>
	</header><!-- .entry-header -->

	<div class="lesson-content">
		<div class="lesson-feedback alert">
			<span class="lesson-feedback-correct">That's correct!</span>
			<span class="lesson-feedback-incorrect">Aue! The correct answer was <strong class="lesson-feedback-correct-option"></strong></span>
		</div>
		<?php

		// Grab all IDs associated with this game	
		$gameObjectIDs = array();
		$lessonCardCounter = 0;
		while ( $phrases->have_posts() ) : $phrases->the_post();
			// Retrieve "correct" answers
			$correctAnswer = get_field('english_translation');

			// Randomize output by storing options in array, shuffling then displaying content
			$lessonAssessmentOptions =  array();
			$lessonAssessmentOptions[] = get_field('english_translation');
			$lessonAssessmentOptions[] = get_field('assessment_option_one');
			$lessonAssessmentOptions[] = get_field('assessment_option_two');
			shuffle($lessonAssessmentOptions);

			// Attempt to retrieve connected vocabulary terms
			$connectedVocabulary = new WP_Query( array(
				'connected_type' => 'vocabulary_terms_to_phrases',
				'connected_items' => $post->ID,
				'nopaging' => true,
			));
			
			if ( $lessonCardCounter === 0 ) :
				echo '<div class="lesson-card test-card current" data-lesson-object-id="'.$post->ID.'" data-lesson-object-result="-99">';
			elseif ( $lessonCardCounter == $lessonCardCount - 1 ) :
				echo '<div class="lesson-card test-card last" data-lesson-object-id="'.$post->ID.'" data-lesson-object-result="-99">';
			else :
				echo '<div class="lesson-card test-card" data-lesson-object-id="'.$post->ID.'" data-lesson-object-result="-99">';
			endif;

			echo '<h3>'. get_the_title() .'</h3>';

			echo '<div class="lesson-image-container">';
			// If there are connected vocabulary terms, retrieve those images
			if ( $connectedVocabulary->have_posts() ) {
				if ( $connectedVocabulary->have_posts() ) :
					while( $connectedVocabulary->have_posts() ) : $connectedVocabulary->the_post();
						echo get_the_post_thumbnail();
					endwhile;
					wp_reset_postdata();
				endif;
			// If not, get the post thumbnail 
			} else {
				if ( get_the_post_thumbnail() ) {
					echo get_the_post_thumbnail();
				}
			}
			echo '</div>';

			echo '<div class="lesson-card-assessment">';
			echo '<!-- You spent more cheating then you did learning. -->';
				foreach ( $lessonAssessmentOptions as $lessonAssessmentOption ) {
					if ( $lessonAssessmentOption === $correctAnswer ) :
						echo '<a class="btn lesson-card-assessment-option correct-option">'.$lessonAssessmentOption.'</a>';
					else :
						echo '<a class="btn lesson-card-assessment-option">'.$lessonAssessmentOption.'</a>';
					endif;
				}
			echo '</div>';

			echo '</div>'; // .lesson-card
			$lessonCardCounter++;
			$gameObjectIDs[] = $post->ID;
		endwhile;
		rewind_posts();

		?>
	</div><!-- .entry-content -->

	<footer class="lesson-footer">
		<div class="lesson-controls">
			<a class="btn btn-primary advance-lesson" href="javascript:void(0);">Next</a>
			<a class="btn check-lesson" href="javascript:void(0);">Check</a>
			<a class="btn btn-primary finish-lesson" href="javascript:void(0);" data-lesson-outcome="pass" data-currency-type-id="<?php echo $currencyTypeID; ?>" data-landing-id="<?php echo $landingPageID; ?>"><?php echo __('Pau!'); ?></a>
		</div>
	</footer>

</article><!-- #post-<?php the_ID(); ?> -->