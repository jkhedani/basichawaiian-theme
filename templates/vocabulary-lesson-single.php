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
));

?>

<article id="post-<?php the_ID(); ?>" <?php post_class('lesson-container'); ?> data-lesson-id="<?php echo $post->ID; ?>" data-lesson-complete="<?php echo $objectCompleted; ?>">

	<?php bedrock_postcontentstart(); ?>

	<header class="lesson-header row">
		<?php bedrock_abovetitle(); ?>
		<h1 class="lesson-title span12"><?php the_title(); ?></h1>
		<h3 class="lesson-instructions span12">Listen and repeat each word you hear until you feel comfortable pronouncing each word.</h3>
		<div class="lesson-progress progress span5">
			<?php
				$vocabularyTermCount = $vocabularyTerms->post_count;
				$width = 100 / $vocabularyTermCount;
				for ( $i = 0; $i < $vocabularyTermCount; $i++ ) {
					echo '<div class="bar bar-info" style="width: '.$width.'%;"></div>';
				}
			?>
		</div>
		<?php bedrock_belowtitle(); ?>
	</header><!-- .entry-header -->

	<hr />

	<div class="lesson-content">
		<?php


		// Grab all IDs associated with this game	
		$gameObjectIDs = array();
		while ( $vocabularyTerms->have_posts() ) : $vocabularyTerms->the_post();

			echo '<button class="btn btn-primary play-pronunciation">Play Audio</button>';
			echo '<audio class="pronunciation" src="'.get_field('audio_track').'"></audio>';

			echo '<button class="btn btn-primary show-english">Show English</button>';
			echo '<button class="btn btn-primary show-hawaiian">Show Hawaiian</button>';
			the_title();
			$gameObjectIDs[] = $post->ID;
		endwhile;
		rewind_posts();



		?>
	</div><!-- .entry-content -->

	<hr />

	<footer class="lesson-footer">
		<div id="lesson-controls">
			<a class="btn btn-primary finish-lesson" href="javascript:void(0);" data-lesson-result="pass" data-landing-id="<?php echo $landingPageID; ?>"><?php echo __('Pau!'); ?></a>
		</div>
	</footer>

	<?php bedrock_postcontentend(); ?>

</article><!-- #post-<?php the_ID(); ?> -->