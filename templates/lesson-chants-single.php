<?php
/**
 * @package _s
 * @since _s 1.0
 */

// Reflect a view for user on this object (object created if it doesn't already exist)
increment_object_value ( $post->ID, 'times_viewed' );

$postType = get_post_type( $post->ID );
$postTypeObject = get_post_type_object($postType);
$landingPageID = get_connected_object_ID( $post->ID, 'chants_lessons_to_topics' );
$currencyTypeID = get_connected_object_ID( $post->ID, 'chants_lessons_to_topics', 'topics_to_modules', 'modules_to_units' );

// get list of all connected vocabulary terms
$chantLines = new WP_Query( array(
	'connected_type' => 'chant_lines_to_chant_lessons',
  'connected_items' => $post->ID,
  'nopaging' => true,
	'post_type' => 'chant_lines',
));

// Count the total amount of lesson objects
$lessonCardCounter = 0;
$lessonCardCount = $chantLines->post_count;

?>

<article id="post-<?php the_ID(); ?>" <?php post_class('lesson-container'); ?> data-lesson-id="<?php echo $post->ID; ?>">

	<header class="lesson-header">
		<?php $previousurl = htmlspecialchars($_SERVER['HTTP_REFERER']); ?>
		<a href="<?php echo $previousurl; ?>" class="lesson-quit">Quit</a>

		<?php
			// Second loop to grab optional instructional text
			$lessonCardCounter = 0;
			while ( $chantLines->have_posts() ) : $chantLines->the_post();

				if ( $lessonCardCounter == 0 ) {
					echo '<h4 class="lesson-instructions current">';
				} else {
					echo '<h4 class="lesson-instructions">';
				}

				if ( get_field('chant_optional_instructional_text') ) {
					echo get_field('chant_optional_instructional_text');
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

		<!-- Lesson Feedback -->
		<div class="lesson-feedback alert">
			<span class="lesson-feedback-correct">That's correct!</span>
			<span class="lesson-feedback-incorrect">Aue! The correct answer was <strong class="lesson-feedback-correct-option"></strong></span>

			<!-- Display Audio in Phrases feedback -->
			<div class="pronunciation-container">
				<button class="play-audio">Play Audio</button>
				<button class="pause-audio">Pause Audio</button>
			</div>
		</div>

		<!-- Lesson Karma -->
		<div class="lesson-karma pull-right">
			<?php
				$karmaAllowance = 100 / $lessonCardCount;
				$karmaAllowance = round( 60 / $karmaAllowance );
				for ( $i = 0; $i < $karmaAllowance; $i++ ) {
					echo '<i class="karma-point icon-leaf icon-white pull-right"></i>';
				}
			?>
		</div>

		<!-- Lesson Content -->
		<?php
		// Grab all IDs associated with this game
		$gameObjectIDs = array();
		$lessonCardCounter = 0;
		while ( $chantLines->have_posts() ) : $chantLines->the_post();
			if ( $lessonCardCounter === 0 ) :
				echo '<div class="lesson-card test-card current" data-lesson-object-id="'.$post->ID.'" data-lesson-object-result="-99">';
			elseif ( $lessonCardCounter == $lessonCardCount - 1 ) :
				echo '<div class="lesson-card test-card last" data-lesson-object-id="'.$post->ID.'" data-lesson-object-result="-99">';
			else :
				echo '<div class="lesson-card test-card" data-lesson-object-id="'.$post->ID.'" data-lesson-object-result="-99">';
			endif;

			echo '<h3>'. get_the_title() .'</h3>';

			// Randomize output by storing options in array, shuffling then displaying content
			$lessonAssessmentOptions =  array();
			$lessonAssessmentOptions[] = get_field('chant_answer');
			$lessonAssessmentOptions[] = get_field('chant_assessment_option_one');
			$lessonAssessmentOptions[] = get_field('chant_assessment_option_two');
			shuffle($lessonAssessmentOptions);
			echo '<div class="lesson-card-assessment">';
			echo '<!-- You spent more cheating then you did learning. -->';
				foreach ( $lessonAssessmentOptions as $lessonAssessmentOption ) {
					if ( $lessonAssessmentOption == get_field('chant_answer') ) :
						error_log(get_field('chant_audio_ogg'));
						echo '<a class="btn btn-cta gray lesson-card-assessment-option correct-option" data-correct-option-audio="'.get_field('chant_audio_ogg').'" data-correct-option-audio-mp3="'.get_field('chant_audio_mp3').'">'.$lessonAssessmentOption.'</a>';
						//echo '<a class="btn btn-cta blue lesson-card-assessment-option correct-option" data-correct-option-audio="'.get_field('chant_audio_ogg').'">'.$lessonAssessmentOption.'</a>';
					else :
						echo '<a class="btn btn-cta gray lesson-card-assessment-option">'.$lessonAssessmentOption.'</a>';
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

		<!-- Lesson Progress -->
		<div class="lesson-progress span5">
			<?php
				for ( $i = 0; $i < $lessonCardCount; $i++ ) {
					if ( $i == 0 ) :
						echo '<div class="lei-counter viewed current"></div>';
					elseif ( $i == $lessonCardCount - 1 ):
						echo '<div class="lei-counter last"></div>';
					else :
						echo '<div class="lei-counter"></div>';
					endif;
				}
			?>
		</div>

		<!-- Lesson Controls -->
		<div class="lesson-controls">
			<a class="btn btn-primary advance-lesson" href="javascript:void(0);">Next</a>
			<a class="btn check-lesson" href="javascript:void(0);">Check</a>
			<a class="btn btn-primary finish-lesson" href="javascript:void(0);" data-lesson-outcome="pass" data-currency-type-id="<?php echo $currencyTypeID; ?>" data-landing-id="<?php echo $landingPageID; ?>"><?php echo __('Pau!'); ?></a>
		</div>
	</footer>

</article><!-- #post-<?php the_ID(); ?> -->
