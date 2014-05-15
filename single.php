<?php
/**
 * The Template for displaying all single posts.
 *
 * @package _s
 * @since _s 1.0
 */

get_header(); ?>

		<div id="primary" class="content-area">
			<div id="content" class="site-content" role="main">

			<?php
				// UNIT PAGE CONTENT
				if ( get_post_type( $post->ID ) == 'units' ) {
					get_template_part( 'templates/unit', 'single' );

				// TOPIC PAGE CONTENT
				} elseif ( get_post_type( $post->ID ) == 'topics') {
					get_template_part( 'templates/topic', 'single' );
				
				// INSTRUCTIONAL CONTENT
				} elseif(get_post_type($post->ID) == 'instruction_lessons') {
					get_template_part( 'templates/lesson-instructional', 'single' );

				// LISTEN AND REPEAT CONTENT
				} elseif(get_post_type($post->ID) == 'listenrepeat_lessons') {
					get_template_part( 'templates/lesson-listen-repeat', 'single' );

				// READINGS CONTENT
				} elseif(get_post_type($post->ID) == 'readings') {
					get_template_part( 'templates/lesson-readings', 'single' );

				// VOCABULARY LESSON CONTENT
				} elseif(get_post_type($post->ID) == 'vocabulary_lessons') {
					get_template_part( 'templates/lesson-vocabulary', 'single' );

				// PHRASES LESSON CONTENT
				} elseif ( get_post_type( $post->ID ) == 'phrases_lessons' ) {
					get_template_part( 'templates/lesson-phrases', 'single' );

				// PROVERBS LESSON CONTENT
				} elseif ( get_post_type( $post->ID ) == 'proverbs_lessons' ) {
					get_template_part( 'templates/lesson-proverbs', 'single' );

				// PROTOCOL LESSON CONTENT
				} elseif ( get_post_type( $post->ID ) == 'protocol_lessons' ) {
					get_template_part( 'templates/lesson-proverbs', 'single' );

				// PRONOUN LESSON CONTENT
				} elseif ( get_post_type( $post->ID ) == 'pronoun_lessons' ) {
					get_template_part( 'templates/lesson-pronouns', 'single' );

				// SONGS CONTENT
				} elseif ( get_post_type( $post->ID ) == 'song_lessons' ) {
					get_template_part( 'templates/lesson-songs', 'single' );

				// CHANTS CONTENT
				} elseif ( get_post_type( $post->ID ) == 'chants_lessons' ) {
					get_template_part( 'templates/lesson-chants', 'single' );

				// ACTIVITIES CONTENT
				} elseif ( get_post_type( $post->ID ) == 'activities' ) {
					get_template_part( 'templates/activities', 'single' );

				// POST CONTENT
				} elseif ( get_post_type( $post->ID ) == 'post' ) {
					while ( have_posts() ) : the_post();
					get_template_part( 'templates/post', 'single' );
					endwhile;
				// DEFAULT SINGLE LOOP
				} else {
					while ( have_posts() ) : the_post();
						get_template_part( 'templates/content', 'single' );
						// If comments are open or we have at least one comment, load up the comment template
						if ( comments_open() || '0' != get_comments_number() )
							comments_template( '', true );
					endwhile;
				} // endif;
			?>

			</div><!-- #content .site-content -->
		</div><!-- #primary .content-area -->
		<?php //get_sidebar(); ?>

<?php get_footer(); ?>