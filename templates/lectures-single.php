<?php
/**
 * @package _s
 * @since _s 1.0
 */

// Reflect a view for user on this object (object created if it doesn't already exist)
increment_object_value ( $post->ID, 'times_viewed' );create_object_record( $post->ID );

$postType = get_post_type( $post->ID );
$postTypeObject = get_post_type_object($postType);
$landingPageID = get_connected_object_ID( $post->ID, 'lectures_to_topics' );
if ( is_object_complete ( $post->ID ) ) {
	$objectCompleted = 1;
} else {
	$objectCompleted = 0;
};

?>

<article id="post-<?php the_ID(); ?>" <?php post_class('lesson-container'); ?> data-lesson-id="<?php echo $post->ID; ?>" data-lesson-complete="<?php echo $objectCompleted; ?>">

	<?php bedrock_postcontentstart(); ?>

	<header class="lesson-header">
		<?php bedrock_abovetitle(); ?>
		<h1 class="lesson-title"><?php the_title(); ?></h1>
		<h3 class="lesson-instructions">To complete this lecture, follow along with the entire video below.</h3>
		<?php bedrock_belowtitle(); ?>
	</header><!-- .entry-header -->

	<hr />

	<div class="lesson-content">
		<iframe width="420" height="315" src="//www.youtube.com/embed/O7X9AAeDCr4" frameborder="0" allowfullscreen></iframe>
	</div><!-- .entry-content -->

	<hr />

	<footer class="lesson-footer">
		<div id="lesson-controls">
			<a class="btn btn-primary finish-lesson" href="javascript:void(0);" data-lesson-result="pass" data-landing-id="<?php echo $landingPageID; ?>"><?php echo __('Pau!'); ?></a>
		</div>
	</footer>

	<?php bedrock_postcontentend(); ?>

</article><!-- #post-<?php the_ID(); ?> -->