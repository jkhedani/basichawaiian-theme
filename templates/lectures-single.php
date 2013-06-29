<?php
/**
 * @package _s
 * @since _s 1.0
 */

$postType = get_post_type($post->ID);
$postTypeObject = get_post_type_object($postType);

echo get_connected_parent_ID( $post->ID, 'lectures_to_topics' );

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<?php bedrock_postcontentstart(); ?>

	<header class="lesson-header">
		<?php bedrock_abovetitle(); ?>
		<h1 class="lesson-title"><?php the_title(); ?></h1>
		<h3 class="lesson-instructions">To complete this lecture, follow along with the entire video below.</h3>
		<?php bedrock_belowtitle(); ?>
	</header><!-- .entry-header -->

	<hr />

	<div class="entry-content">
		<iframe width="420" height="315" src="//www.youtube.com/embed/O7X9AAeDCr4" frameborder="0" allowfullscreen></iframe>
	</div><!-- .entry-content -->

	<hr />

	<footer class="lesson-footer">
		<div id="lesson-controls">
			<a class="btn btn-primary finish-lesson" href="javascript:void(0);"><?php echo __('Pau!'); ?></a>
		</div>
		<div id="lesson-results">
			<span data-lesson-passed="true"></span>
		</div>
	</footer>

	<?php bedrock_postcontentend(); ?>

</article><!-- #post-<?php the_ID(); ?> -->