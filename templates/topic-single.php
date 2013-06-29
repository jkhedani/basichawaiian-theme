<?php
/**
 * @package _s
 * @since _s 1.0
 */

$postType = get_post_type($post->ID);
$postTypeObject = get_post_type_object($postType);

// "Previous" page (doesn't work very well if user is logged in and edits a page...)
$previousPageURL = htmlspecialchars($_SERVER['HTTP_REFERER']);

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<?php bedrock_postcontentstart(); ?>

	<header class="entry-header">
		<?php bedrock_abovetitle(); ?>
		<a class="btn btn-back" href="<?php echo $previousPageURL; ?>"><i class="icon-arrow-left" style="padding-right:10px;"></i>Back to Module View</a>
		<h1 class="entry-title"><?php the_title(); ?></h1>
		<hr />
		<?php bedrock_belowtitle(); ?>
		<div class="entry-meta"><?php //_s_posted_on(); ?></div><!-- .entry-meta -->
	</header><!-- .entry-header -->

	<div class="entry-content">
		
		<ul class="lessons row">

		<?php
			// LECTURES
			$lectures = new WP_Query( array(
				'connected_type' => 'lectures_to_topics',
				'connected_items' => $post->ID,
				'nopaging' => true,
				'orderby' => 'menu_order',
				'order' => 'ASC',
			));
			if ( $lectures->have_posts() ) {
				while( $lectures->have_posts() ) : $lectures->the_post();
					echo '<li class="lesson lecture span4 pull-left"><a class="prompt-lesson-start" href="#lesson-start-modal" data-lesson-url="'.get_permalink().'"><h4>' . get_the_title() . '</h4></a></li>';
				endwhile;
				wp_reset_postdata();
			}

			// VOCABULARY LESSONS
			$vocabLessons = new WP_Query( array(
				'connected_type' => 'vocabulary_lessons_to_topics',
				'connected_items' => $post->ID,
				'nopaging' => true,
				'orderby' => 'menu_order',
				'order' => 'ASC',
			));
			if ( $vocabLessons->have_posts() ) {
				while( $vocabLessons->have_posts() ) : $vocabLessons->the_post();
					echo '<li class="lesson vocabulary span4 pull-left"><a class="prompt-lesson-start" href="#lesson-start-modal" data-lesson-url="'.get_permalink().'"><h4>' . get_the_title() . '</h4></a></li>';
				endwhile;
				wp_reset_postdata();
			}

			// PHRASES LESSONS
			$phrasesLessons = new WP_Query( array(
				'connected_type' => 'phrases_lessons_to_topics',
				'connected_items' => $post->ID,
				'nopaging' => true,
				'orderby' => 'menu_order',
				'order' => 'ASC',
			));
			if ( $phrasesLessons->have_posts() ) {
				while( $phrasesLessons->have_posts() ) : $phrasesLessons->the_post();
					echo '<li class="lesson phrases span4 pull-left"><a class="prompt-lesson-start" href="#lesson-start-modal" data-lesson-url="'.get_permalink().'"><h4>' . get_the_title() . '</h4></a></li>';
				endwhile;
				wp_reset_postdata();
			}

		?>

		</ul><!-- .lessons -->

	<?php wp_link_pages( array( 'before' => '<div class="page-links">' . __( 'Pages:', '_s' ), 'after' => '</div>' ) ); ?>
	</div><!-- .entry-content -->

	<?php bedrock_postcontentend(); ?>

</article><!-- #post-<?php the_ID(); ?> -->

<div id="lesson-start-modal" class="modal hide fade">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h3>Start your lesson</h3>
  </div>
  <div class="modal-body">
    <h2><?php echo __('M&#257;kakau?'); ?></h2>
  </div>
  <div class="modal-footer">
    <a class="btn btn-primary start-lesson" href="javascript:void(0);" data-lesson-type="lecture" data-connected-to-id="<?php echo $post->ID; ?>"><?php echo __('&#8216;Ai'); ?></a>
		<a class="btn btn-primary abort-lesson" href="javascript:void(0);"><?php echo __('A&#8216;ole'); ?></a>
  </div>
</div>