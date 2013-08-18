<?php
/**
 * @package _s
 * @since _s 1.0
 */

// Reflect a view for user on this object (object created if it doesn't already exist)
increment_object_value ( $post->ID, 'times_viewed' );

$postType = get_post_type( $post->ID );
$postTypeObject = get_post_type_object($postType);
$landingPageID = get_connected_object_ID( $post->ID, 'instructional_lessons_to_topics' );
$currencyTypeID = get_connected_object_ID( $post->ID, 'instructional_lessons_to_topics', 'topics_to_modules', 'modules_to_units' );

if ( get_field('instructional_slide') ) {
	$instructionalSlides = get_field('instructional_slide');
	$lessonCardCount = count($instructionalSlides);
}

?>

<article id="post-<?php the_ID(); ?>" <?php post_class('lesson-container'); ?> data-lesson-id="<?php echo $post->ID; ?>" data-lesson-complete="<?php echo is_object_complete( $post->ID ) ? "1" : "0"; ?>">

	<?php bedrock_postcontentstart(); ?>

	<header class="lesson-header">
		<h1 class="lesson-title"><?php the_title(); ?></h1>
		<div class="lesson-progress progress span5">
			<?php
				$width = 100 / $lessonCardCount;
				for ( $i = 0; $i < $lessonCardCount; $i++ ) {
					if ( $i == 0 ) :
						echo '<div class="bar bar-info current" style="width: '.$width.'%;"></div>';
					elseif ( $i == $lessonCardCount - 1 ):
						echo '<div class="bar bar-info last" style="width: '.$width.'%;"></div>';
					else :
						echo '<div class="bar bar-info" style="width: '.$width.'%;"></div>';
					endif;
				}
			?>
		</div>

		<hr class="clear" />

		<?php
			// Second loop here iterates through slides and displays appropriate instructional text
			$lessonCardCounter = 0;
			foreach ($instructionalSlides as $instructionalSlide ) {

				if ( $lessonCardCounter == 0 ) {
					echo '<h4 class="lesson-instructions current">';
				} else {
					echo '<h4 class="lesson-instructions">';
				}

				if ( $instructionalSlide['instructional_slide_optional_instructions'] ) {
					echo $instructionalSlide['instructional_slide_optional_instructions'];
				}	else { 
					echo 'Progress through each slide until you reach the end.';
				}

				echo '</h4>';
				$lessonCardCounter++;

			}
		?>
	
	</header><!-- .entry-header -->

	<div class="lesson-content">
		<?php
			if ( get_field('instructional_slide') ) {
				echo '<div class="instructional-slides lesson-cards">';
					$lessonCardCounter = 0;
					foreach ( $instructionalSlides as $instructionalSlide ) {
						// Construct the slide
						if ( ($lessonCardCounter == $lessonCardCount - 1) && ($lessonCardCounter == 0) ):
							echo '<div class="instructional-slide lesson-card current last">';
						elseif ( $lessonCardCounter == 0 ) :
							echo '<div class="instructional-slide lesson-card current">';
						elseif ( $lessonCardCounter == $lessonCardCount - 1 ):
							echo '<div class="instructional-slide lesson-card last">';
						else:
							echo '<div class="instructional-slide lesson-card">';
						endif;
						// Slide Content
						if ( $instructionalSlide['instructional_slide_content'] )
							echo 	'<div class="instructional-slide-content slide-content">'.$instructionalSlide['instructional_slide_content'].'</div>';
						// Slide Audio
						if ( $instructionalSlide['instructional_slide_audio_ogg'] ) {
							echo 	'<div class="infromation-slide-audio audio-player">';
							echo 		'<button class="btn btn-primary play-audio">Play Audio</button>';
							echo 		'<button class="btn btn-primary pause-audio">Pause Audio</button>';
							echo 		'<audio class="pronunciation" src="'.$instructionalSlide['instructional_slide_audio_ogg'].'"></audio>';
							echo 	'</div>';
						}
						if ( $instructionalSlide['instructional_slide_translation'] )
							echo 	'<button class="btn btn-primary show-translation"><span>Show</span> English</button>';
							echo 	'<div class="translation instructional-slide-translation hidden">'.$instructionalSlide['instructional_slide_translation'].'</div>';
						$lessonCardCounter++;
						echo '</div>'; // Instructional Slide
					}
				echo '</div>'; // Instructional Slides
			}
		?>
	</div><!-- .entry-content -->

	<footer class="lesson-footer">
		<div class="lesson-controls">
			<a class="btn btn-primary advance-lesson" href="javascript:void(0);">Next</a>
			<a class="btn check-lesson" href="javascript:void(0);">Check</a>
			<a class="btn btn-primary finish-lesson" href="javascript:void(0);" data-lesson-outcome="pass" data-currency-type-id="<?php echo $currencyTypeID; ?>" data-landing-id="<?php echo $landingPageID; ?>"><?php echo __('Pau!'); ?></a>
		</div>
	</footer>

	<?php bedrock_postcontentend(); ?>

</article><!-- #post-<?php the_ID(); ?> -->