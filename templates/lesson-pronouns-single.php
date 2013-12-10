<?php
/**
 * @package _s
 * @since _s 1.0
 */

// Reflect a view for user on this object (object created if it doesn't already exist)
increment_object_value ( $post->ID, 'times_viewed' );

$postType = get_post_type( $post->ID );
$postTypeObject = get_post_type_object($postType);
$landingPageID = get_connected_object_ID( $post->ID, 'pronoun_lessons_to_topics' );
$currencyTypeID = get_connected_object_ID( $post->ID, 'pronoun_lessons_to_topics', 'topics_to_modules', 'modules_to_units' );

// get list of all connected vocabulary terms
$pronouns = new WP_Query( array(
	'connected_type' => 'pronouns_to_pronoun_lessons',
  'connected_items' => $post->ID,
  'nopaging' => true,
	'post_type' => 'pronouns',
));

// Count the total amount of lesson objects
$pronounsCount = $pronouns->post_count;

?>

<article id="post-<?php the_ID(); ?>" <?php post_class('lesson-container'); ?> data-lesson-id="<?php echo $post->ID; ?>">

	<header class="lesson-header">
		<h1 class="lesson-title"><?php the_title(); ?></h1>

		<div class="lesson-progress progress span5">
			<?php
				$width = 100 / $pronounsCount;
				for ( $i = 0; $i < $pronounsCount; $i++ ) {
					if ( $i == 0 ) :
						echo '<div class="bar bar-info current" style="width: '.$width.'%;"></div>';
					elseif ( $i == $pronounsCount - 1 ):
						echo '<div class="bar bar-info last" style="width: '.$width.'%;"></div>';
					else :
						echo '<div class="bar bar-info" style="width: '.$width.'%;"></div>';
					endif;
				}
			?>
		</div>

		<hr class="clear" />

		<div class="lesson-karma pull-right">
			<?php
				$karmaAllowance = 100 / $pronounsCount;
				$karmaAllowance = round( 60 / $karmaAllowance );
				for ( $i = 0; $i < $karmaAllowance; $i++ ) {
					echo '<i class="karma-point icon-leaf icon-white pull-right"></i>';
				}
			?>
		</div>

		<?php
			// Second loop to grab optional instructional text
			$lessonCardCounter = 0;
			while ( $pronouns->have_posts() ) : $pronouns->the_post();

				if ( $lessonCardCounter == 0 ) {
					echo '<h4 class="lesson-instructions current">';
				} else {
					echo '<h4 class="lesson-instructions">';
				}

				if ( get_field('pronoun_optional_instructional_text') ) {
					echo get_field('pronoun_optional_instructional_text');
				} else {
					echo 'Choose the sentence below that answers the question using the proper pronoun.';
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
		while ( $pronouns->have_posts() ) : $pronouns->the_post();
			if ( $lessonCardCounter === 0 ) :
				echo '<div class="lesson-card test-card current" data-lesson-object-id="'.$post->ID.'" data-lesson-object-result="-99">';
			elseif ( $lessonCardCounter == $pronounsCount - 1 ) :
				echo '<div class="lesson-card test-card last" data-lesson-object-id="'.$post->ID.'" data-lesson-object-result="-99">';
			else :
				echo '<div class="lesson-card test-card" data-lesson-object-id="'.$post->ID.'" data-lesson-object-result="-99">';
			endif;

			echo '<h3>'. get_field('hawaiian_question') .'</h3>';

			// Randomize output by storing options in array, shuffling then displaying content
			$lessonAssessmentOptions =  array();
			$lessonAssessmentOptions[] = get_field('hawaiian_answer');
			$lessonAssessmentOptions[] = get_field('assessment_option_one_hawaiian');
			$lessonAssessmentOptions[] = get_field('assessment_option_two_hawaiian');
			shuffle($lessonAssessmentOptions);
			echo '<div class="lesson-card-assessment">';
			echo '<!-- You spent more cheating then you did learning. -->';
				foreach ( $lessonAssessmentOptions as $lessonAssessmentOption ) {
					if ( $lessonAssessmentOption == get_field('hawaiian_answer') ) :
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