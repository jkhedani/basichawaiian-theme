<?php
/**
 * @package _s
 * @since _s 1.0
 */

// Reflect a view for user on this object (object created if it doesn't already exist)
increment_object_value ( $post->ID, 'times_viewed' );

$postType = get_post_type( $post->ID );
$postTypeObject = get_post_type_object($postType);
$landingPageID = get_connected_object_ID( $post->ID, 'listen_repeat_lessons_to_topics' );
$currencyTypeID = get_connected_object_ID( $post->ID, 'listen_repeat_lessons_to_topics', 'topics_to_modules', 'modules_to_units' );

// get list of all connected phrases
$phrases = new WP_Query( array(
	'connected_type' => 'phrases_to_listen_repeat_lessons',
  'connected_items' => $post->ID,
  'nopaging' => true,
	'post_type' => 'phrases',
));
$lessonCardCounter = 0;
$lessonCardCount = count( $phrases );

?>

<article id="post-<?php the_ID(); ?>" <?php post_class('lesson-container'); ?> data-lesson-id="<?php echo $post->ID; ?>" data-lesson-complete="<?php echo is_object_complete( $post->ID ) ? "1" : "0"; ?>">

	<?php bedrock_postcontentstart(); ?>

	<header class="lesson-header">
		<?php bedrock_abovetitle(); ?>
		<h1 class="lesson-title"><?php the_title(); ?></h1>
		<h3 class="lesson-instructions">To complete this lecture, follow along with the entire video below.</h3>
		<?php bedrock_belowtitle(); ?>
	</header><!-- .entry-header -->

	<hr />

	<div class="lesson-content">
		<?php
			while ( $phrases->have_posts() ) : $phrases->the_post();
				if ( $lessonCardCounter === 0 ) :
				echo '<div class="lesson-card learn-card current" data-lesson-object-id="'.$post->ID.'">';
				elseif ( $lessonCardCounter == $lessonCardCount - 1 ) :
				echo '<div class="lesson-card learn-card last" data-lesson-object-id="'.$post->ID.'">';
				else :
				echo '<div class="lesson-card learn-card" data-lesson-object-id="'.$post->ID.'">';
				endif;
				echo get_the_title();
				echo get_field('english_translation');
				$vocabularyTerm = new WP_Query( array(
					'connected_type' => 'vocabulary_terms_to_phrases',
				  'connected_items' => $post->ID,
				  'nopaging' => true,
					'post_type' => 'vocabulary_terms',
				));
				while ( $vocabularyTerm->have_posts() ) : $vocabularyTerm->the_post();
					echo get_the_post_thumbnail();
				endwhile;
				wp_reset_postdata();
				echo '</div>';
				$lessonCardCounter++;
			endwhile;
			wp_reset_postdata();
		?>
	</div><!-- .entry-content -->

	<hr />

	<footer class="lesson-footer">
		<div id="lesson-controls">
			<a class="btn btn-primary advance-lesson" href="javascript:void(0);">Next</a>
			<a class="btn btn-primary finish-lesson" href="javascript:void(0);" data-lesson-outcome="pass" data-currency-type-id="<?php echo $currencyTypeID; ?>" data-landing-id="<?php echo $landingPageID; ?>"><?php echo __('Pau!'); ?></a>
		</div>
	</footer>

	<?php bedrock_postcontentend(); ?>

</article><!-- #post-<?php the_ID(); ?> -->