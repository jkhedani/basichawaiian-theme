<?php
/**
 * @package _s
 * @since _s 1.0
 */

// Reflect a view for user on this object (object created if it doesn't already exist)
increment_object_value ( $post->ID, 'times_viewed' );

$previousPageURL = get_permalink( get_connected_object_ID( $post->ID, 'topics_to_modules', 'modules_to_units') );

$parentModule = new WP_Query( array(
	'connected_type' => 'topics_to_modules',
	'connected_items' => $post->ID,
	'post_type' => 'modules'
));
$parentModuleTitle = $parentModule->posts[0]->post_title;
$grandparentUnitID = get_connected_object_ID( $post->ID, 'topics_to_modules', 'modules_to_units');

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<header class="entry-header">
		<h1 class="entry-title"><?php echo get_the_title($grandparentUnitID); ?></h1>
		<h2 class="entry-super-title"><?php echo $parentModuleTitle; ?> / <?php the_title(); ?></h2>
		<a class="btn btn-inverse btn-back" href="<?php echo $previousPageURL; ?>"><i class="icon-arrow-left icon-white" style="padding-right:10px;"></i>Back to Module View</a>
	</header><!-- .entry-header -->

	<div class="entry-content">
		
		<ul class="lessons row">

		<?php
			// INFORMATION LESSONS
			$instructional = new WP_Query( array(
				'connected_type' => 'instructional_lessons_to_topics',
				'connected_items' => $post->ID,
				'nopaging' => true,
				'orderby' => 'menu_order',
				'order' => 'ASC',
			));
			if ( $instructional->have_posts() ) {
				while( $instructional->have_posts() ) : $instructional->the_post();
					echo '<li class="lesson lecture span4 pull-left" ';
						if ( is_object_complete( $post->ID ) ) { echo 'data-complete="1"'; } else { echo 'data-complete="0"'; }
					echo '>';
					echo 	'<a class="prompt-lesson-start" href="#lesson-start-modal" data-lesson-url="'.get_permalink().'"><h4>' . get_the_title() . '</h4></a>';
					echo '</li>';
				endwhile;
				wp_reset_postdata();
			}

			// INFORMATION LESSONS
			$listenRepeat = new WP_Query( array(
				'connected_type' => 'listen_repeat_lessons_to_topics',
				'connected_items' => $post->ID,
				'nopaging' => true,
				'orderby' => 'menu_order',
				'order' => 'ASC',
			));
			if ( $listenRepeat->have_posts() ) {
				while( $listenRepeat->have_posts() ) : $listenRepeat->the_post();
					echo '<li class="lesson lecture span4 pull-left" ';
						if ( is_object_complete( $post->ID ) ) { echo 'data-complete="1"'; } else { echo 'data-complete="0"'; }
					echo '>';
					echo 	'<a class="prompt-lesson-start" href="#lesson-start-modal" data-lesson-url="'.get_permalink().'"><h4>' . get_the_title() . '</h4></a>';
					echo '</li>';
				endwhile;
				wp_reset_postdata();
			}

			// READINGS
			$readings = new WP_Query( array(
				'connected_type' => 'readings_to_topics',
				'connected_items' => $post->ID,
				'nopaging' => true,
				'orderby' => 'menu_order',
				'order' => 'ASC',
			));
			if ( $readings->have_posts() ) {
				while( $readings->have_posts() ) : $readings->the_post();
					echo '<li class="lesson lecture span4 pull-left" ';
						if ( is_object_complete( $post->ID ) ) { echo 'data-complete="1"'; } else { echo 'data-complete="0"'; }
					echo '>';
					echo 	'<a class="prompt-lesson-start" href="#lesson-start-modal" data-lesson-url="'.get_permalink().'"><h4>' . get_the_title() . '</h4></a>';
					echo '</li>';
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
					echo '<li class="lesson vocabulary span4 pull-left" ';
						if ( is_object_complete( $post->ID ) ) { echo 'data-complete="1"'; } else { echo 'data-complete="0"'; }
					echo '>';
					echo '<a class="prompt-lesson-start" href="#lesson-start-modal" data-lesson-url="'.get_permalink().'"><h4>' . get_the_title() . '</h4></a></li>';
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
					echo '<li class="lesson phrase span4 pull-left" ';
						if ( is_object_complete( $post->ID ) ) { echo 'data-complete="1"'; } else { echo 'data-complete="0"'; }
					echo '>';
					echo '<a class="prompt-lesson-start" href="#lesson-start-modal" data-lesson-url="'.get_permalink().'"><h4>' . get_the_title() . '</h4></a></li>';
				endwhile;
				wp_reset_postdata();
			}

			// PRONOUN LESSONS
			$pronounLessons = new WP_Query( array(
				'connected_type' => 'pronoun_lessons_to_topics',
				'connected_items' => $post->ID,
				'nopaging' => true,
				'orderby' => 'menu_order',
				'order' => 'ASC',
			));
			if ( $pronounLessons->have_posts() ) {
				while( $pronounLessons->have_posts() ) : $pronounLessons->the_post();
					echo '<li class="lesson pronoun span4 pull-left" ';
						if ( is_object_complete( $post->ID ) ) { echo 'data-complete="1"'; } else { echo 'data-complete="0"'; }
					echo '>';
					echo '<a class="prompt-lesson-start" href="#lesson-start-modal" data-lesson-url="'.get_permalink().'"><h4>' . get_the_title() . '</h4></a></li>';
				endwhile;
				wp_reset_postdata();
			}

			// SONG LESSONS
			$songLessons = new WP_Query( array(
				'connected_type' => 'song_lessons_to_topics',
				'connected_items' => $post->ID,
				'nopaging' => true,
				'orderby' => 'menu_order',
				'order' => 'ASC',
			));
			if ( $songLessons->have_posts() ) {
				while( $songLessons->have_posts() ) : $songLessons->the_post();
					echo '<li class="lesson pronoun span4 pull-left" ';
						if ( is_object_complete( $post->ID ) ) { echo 'data-complete="1"'; } else { echo 'data-complete="0"'; }
					echo '>';
					echo '<a class="prompt-lesson-start" href="#lesson-start-modal" data-lesson-url="'.get_permalink().'"><h4>' . get_the_title() . '</h4></a></li>';
				endwhile;
				wp_reset_postdata();
			}

			// CHANTS LESSONS
			$chantsLessons = new WP_Query( array(
				'connected_type' => 'chants_lessons_to_topics',
				'connected_items' => $post->ID,
				'nopaging' => true,
				'orderby' => 'menu_order',
				'order' => 'ASC',
			));
			if ( $chantsLessons->have_posts() ) {
				while( $chantsLessons->have_posts() ) : $chantsLessons->the_post();
					echo '<li class="lesson pronoun span4 pull-left" ';
						if ( is_object_complete( $post->ID ) ) { echo 'data-complete="1"'; } else { echo 'data-complete="0"'; }
					echo '>';
					echo '<a class="prompt-lesson-start" href="#lesson-start-modal" data-lesson-url="'.get_permalink().'"><h4>' . get_the_title() . '</h4></a></li>';
				endwhile;
				wp_reset_postdata();
			}

		?>

		</ul><!-- .lessons -->

	<?php wp_link_pages( array( 'before' => '<div class="page-links">' . __( 'Pages:', '_s' ), 'after' => '</div>' ) ); ?>
	</div><!-- .entry-content -->

</article><!-- #post-<?php the_ID(); ?> -->

<div id="lesson-start-modal" class="modal hide fade">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h3>Start your lesson</h3>
  </div>
  <div class="modal-body">
    <h2><?php echo __('M&#257;kaukau?'); ?></h2>
  </div>
  <div class="modal-footer">
    <a class="btn btn-primary start-lesson" href="javascript:void(0);" data-lesson-type="lecture" data-connected-to-id="<?php echo $post->ID; ?>"><?php echo __('&#8216;Ae'); ?></a>
		<a class="btn btn-primary abort-lesson" href="javascript:void(0);"><?php echo __('&#8216;A&#8216;ole'); ?></a>
  </div>
</div>