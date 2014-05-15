<?php
/**
 * @package _s
 * @since _s 1.0
 */

// Reflect a view for user on this object (object created if it doesn't already exist)
increment_object_value ( $post->ID, 'times_viewed' );

$postType = get_post_type( $post->ID );
$postTypeObject = get_post_type_object($postType);
$landingPageID = get_connected_object_ID( $post->ID, 'readings_to_topics' );
$currencyTypeID = get_connected_object_ID( $post->ID, 'readings_to_topics', 'topics_to_modules', 'modules_to_units' );

?>

<article id="post-<?php the_ID(); ?>" <?php post_class('lesson-container'); ?> data-lesson-id="<?php echo $post->ID; ?>" data-lesson-complete="<?php echo is_object_complete( $post->ID ) ? "1" : "0"; ?>">

	<header class="lesson-header">
		<?php $previousurl = htmlspecialchars($_SERVER['HTTP_REFERER']); ?>
		<a href="<?php echo $previousurl; ?>" class="lesson-quit">Quit</a>
		<h4 class="lesson-instructions current">
			<?php 
				if ( get_field('readings_optional_instructional_text') ) {
					echo get_field('readings_optional_instructional_text');
				} else {
					echo 'Letʻs listen to the voice of our ancestors. Here are ancient Hawaiian thoughts and stories printed in the Hawaiian language newspapers over one hundred years ago.';
				}
			?>
		</h4>
	</header><!-- .entry-header -->

	<div class="lesson-content">
		<div class="lesson-card learn-card current last" data-lesson-object-id="<?php echo $post->ID; ?>" data-lesson-object-result="-99">

			<button class="play-audio">Play Audio</button>
			<button class="pause-audio">Pause Audio</button>
			<audio class="pronunciation" src="<?php echo get_field('readings_audio_track'); ?>"></audio>

			<?php if ( get_field('original_newspaper') ): ?>
			<a class="btn btn-primary toggle-original-newspaper">Show Original Clipping</a>
			<?php endif; ?>
			<?php if ( get_field('typed_newspaper') ): ?>
			<a class="btn btn-primary toggle-typed-newspaper">Show Typed Text</a>
			<?php endif; ?>
			<?php if ( get_field('typed_newspaper_with_okinas_and_kahako') ): ?>
			<a class="btn btn-primary toggle-typed-newspaper-with-okinas-and-kahako">Show with 'Okina & Kahakō</a>
			<?php endif; ?>

			<div class="readings-texts">
				<?php if ( get_field('original_newspaper') ): ?>
				<div class="original-newspaper readings-text"><img src="<?php echo get_field('original_newspaper'); ?>" /></div>
				<?php endif; ?>
				<?php if ( get_field('typed_newspaper') ): ?>
				<div class="typed-newspaper readings-text"><?php echo get_field('typed_newspaper'); ?></div>
				<?php endif; ?>
				<?php if ( get_field('typed_newspaper_with_okinas_and_kahako') ): ?>
				<div class="typed-newspaper-with-okinas-and-kahako readings-text"><?php echo get_field('typed_newspaper_with_okinas_and_kahako'); ?></div>
				<?php endif; ?>
			</div>
		</div>
	</div><!-- .entry-content -->

	<footer class="lesson-footer">
		<div class="lesson-controls">
			<a class="btn btn-primary finish-lesson" href="javascript:void(0);" data-lesson-outcome="pass" data-currency-type-id="<?php echo $currencyTypeID; ?>" data-landing-id="<?php echo $landingPageID; ?>"><?php echo __('Pau!'); ?></a>
		</div>
	</footer>

</article><!-- #post-<?php the_ID(); ?> -->