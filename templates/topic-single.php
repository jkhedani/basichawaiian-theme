<?php
/**
 * @package _s
 * @since _s 1.0
 */

// Reflect a view for user on this object (object created if it doesn't already exist)
increment_object_value ( $post->ID, 'times_viewed' );

$previousPageURL = get_permalink( get_connected_object_ID( $post->ID, 'topics_to_modules', 'modules_to_units') );
$walletBalance = get_wallet_balance($post->ID);

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<?php bedrock_postcontentstart(); ?>

	<header class="entry-header">
		<div class="wallet-balance span4 pull-right">
			<?php if ( $walletBalance > 1 ) { echo '<a class="btn btn-small pull-right claim-kukui" href="javascript:void(0);">Claim a kukui</a>'; } ?>
			<p class="pull-right">Flowers: <strong><?php echo !empty($walletBalance) ? $walletBalance : "0"; ?></strong></p>
		</div>
		<a class="btn btn-back" href="<?php echo $previousPageURL; ?>"><i class="icon-arrow-left" style="padding-right:10px;"></i>Back to Module View</a>
		<h1 class="entry-title"><?php the_title(); ?></h1>
		<hr />
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

			// PROTOCOL LESSONS
			$protocolLessons = new WP_Query( array(
				'connected_type' => 'protocol_lessons_to_topics',
				'connected_items' => $post->ID,
				'nopaging' => true,
				'orderby' => 'menu_order',
				'order' => 'ASC',
			));
			if ( $protocolLessons->have_posts() ) {
				while( $protocolLessons->have_posts() ) : $protocolLessons->the_post();
					echo '<li class="lesson pronoun span4 pull-left" ';
						if ( is_object_complete( $post->ID ) ) { echo 'data-complete="1"'; } else { echo 'data-complete="0"'; }
					echo '>';
					echo '<a class="prompt-lesson-start" href="#lesson-start-modal" data-lesson-url="'.get_permalink().'"><h4>' . get_the_title() . '</h4></a></li>';
				endwhile;
				wp_reset_postdata();
			}

			// PROVERB LESSONS
			$proverbLessons = new WP_Query( array(
				'connected_type' => 'proverb_lessons_to_topics',
				'connected_items' => $post->ID,
				'nopaging' => true,
				'orderby' => 'menu_order',
				'order' => 'ASC',
			));
			if ( $proverbLessons->have_posts() ) {
				while( $proverbLessons->have_posts() ) : $proverbLessons->the_post();
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

			// ACTIVITIES
			$activities = new WP_Query( array(
				'connected_type' => 'activities_to_topics',
				'connected_items' => $post->ID,
				'nopaging' => true,
				'orderby' => 'menu_order',
				'order' => 'ASC',
			));
			if ( $activities->have_posts() ) {
				while( $activities->have_posts() ) : $activities->the_post();
					echo '<li class="lesson activity span4 pull-left" ';
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