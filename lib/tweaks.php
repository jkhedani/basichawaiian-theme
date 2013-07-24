<?php
/**
 * How to use - add at theme function.php
 	
	add_action('admin_menu','my_admin_menu_separator');
	function my_admin_menu_separator() {
		add_admin_menu_separator(10);
		add_admin_menu_separator(34);
		add_admin_menu_separator(20);
		add_admin_menu_separator(3);
		add_admin_menu_separator(2);
		add_admin_menu_separator(20);
		add_admin_menu_separator(30);
		add_admin_menu_separator(40);
		remove_admin_menu_separator(4);
		//add_admin_menu_separator(4);
		//add_admin_menu_separator(8);
		remove_admin_menu_separator(8);
		add_admin_menu_separator(11);
		add_admin_menu_separator(61);
		add_admin_menu_separator(12);
		add_admin_menu_separator(130);
		add_admin_menu_separator(240);
		add_admin_menu_separator(300);
		add_admin_menu_separator(220);
		//remove_admin_menu_separator(4);
		//var_dump($GLOBALS['menu']);
		//exit;
	}
 */ 


/**
 * Add Top Level menu separator
 * @link http://codex.wordpress.org/Administration_Menus
 * @use global $menu
 * @param int $position separator position, only empty menu position are acceptable
 * @param string $capability The capability required for this menu to be displayed to the user (default 'read').
 * the last separator capability is always read
 */
function add_admin_menu_separator( $position, $capability = 'read' ) {
	global $menu;
	$_separator_count = 0;

	if( isset( $menu[$position] ) )
		return;

	 //add new separator
	$menu[$position] = array('',$capability,"separator_new",'','wp-menu-separator' );
	ksort( $menu );
	
	//normalize separator count
	foreach( $menu as $_offset => $_section){
		if ( substr( $_section[2],0,9 ) == 'separator' ) {
			$_separator_count++;
			$menu[$_offset][2]= "separator{$_separator_count}";
		}
	}
	
	//last separator
	$_last_menu_key = array_pop( array_keys( $menu ) );
	if( substr( $menu[$_last_menu_key][2],0,9 ) == 'separator' ){
		   $menu[$_last_menu_key][2] ='separator-last';
	}

}

/**
 * Remove Top Level Menu Separator
 * This function does not remove the last separator
 * @link http://codex.wordpress.org/Administration_Menus
 * @use global $menu
 * @param int $position separator position
 * @return bool true | false
 */
function remove_admin_menu_separator( $position ) {
	global $menu;
	$_separator_count = 0;

	if( isset( $menu[$position] ) && $menu[$position][2] !== 'separator-last' &&  substr( $menu[$position][2],0,9 ) == 'separator'  ){
		unset( $menu[$position] );
		ksort( $menu );
		
		//normalize separator count
		foreach( $menu as $_offset => $_section){
			if ( substr( $_section[2],0,9 ) == 'separator' ) {
				$_separator_count++;
				$menu[$_offset][2]= "separator{$_separator_count}";
			}
		}
		
		//last separator
		$_last_menu_key = array_pop( array_keys( $menu ) );
		if( substr( $menu[$_last_menu_key][2],0,9 ) == 'separator' ){
			$menu[$_last_menu_key][2] ='separator-last';
		}
		return true;
	}

	return false;

}

/**
 *	Posts 2 Posts Helper Functions
 */

/**
 *	Function: Get Connected Object ID
 *  Retrieves ancestors on a many-to-one connection.
 *	@param int $postID The ID of the post you wish to find the parent ID of.
 *	@param int $parentConnectionType The p2p connection type of the current post to immediate parent
 *	@param int $grandparentConnectionType optional The p2p connection type of the immediate parent to grandparent
 *	@param int $grandparentConnectionType optional The p2p connection type of the grandparent to great grandparent
 *  Return the ID of a connected parent (currently only works for objects with one parent and/or one grandparent and/or one great grandparent ... poor child)
 *
 */
function get_connected_object_ID( $postID, $parentConnectionType = false, $grandparentConnectionType = false, $greatGrandparentConnectionType = false ) {
	global $post;
	$connectedParentID = false;
	$connectedGrandparentID = false;
	$connectedGreatGrandparentID = false;
	
	// Get connected parent if connection type exists to prevent direction error
	if ( p2p_connection_exists( $parentConnectionType ) ) :
	$connectedParent = new WP_Query( array(
	'connected_type' => $parentConnectionType,
	'connected_items' => $postID,
	'nopaging' => true,
	'orderby' => 'menu_order',
	'order' => 'ASC',
	));
	while( $connectedParent->have_posts() ) : $connectedParent->the_post();
		$connectedParentID = $post->ID;

		// Get connected grandparent if desired & check if connection type exists to prevent direction error
		if ( !empty( $grandparentConnectionType ) && ( p2p_connection_exists( $grandparentConnectionType ) ) ) :
			$connectedGrandparent = new WP_Query( array(
			'connected_type' => $grandparentConnectionType,
			'connected_items' => $connectedParentID,
			'nopaging' => true,
			'orderby' => 'menu_order',
			'order' => 'ASC',
			));
			while( $connectedGrandparent->have_posts() ) : $connectedGrandparent->the_post();
				$connectedGrandparentID = $post->ID;

				// Get connected greatgrandparent if desired & check if connection type exists to prevent direction error
				if ( !empty( $greatGrandparentConnectionType ) && ( p2p_connection_exists( $greatGrandparentConnectionType ) ) ) :
				$connectedGreatGrandparent = new WP_Query( array(
				'connected_type' => $greatGrandparentConnectionType,
				'connected_items' => $connectedGrandparentID,
				'nopaging' => true,
				'orderby' => 'menu_order',
				'order' => 'ASC',
				));
				while( $connectedGreatGrandparent->have_posts() ) : $connectedGreatGrandparent->the_post();
					$connectedGreatGrandparentID = $post->ID;
				endwhile;
				wp_reset_postdata();
				endif;

			endwhile;
			wp_reset_postdata();
		endif;

	endwhile;
	wp_reset_postdata();

	endif;

	// Return desired connections
	if ( !empty( $greatGrandparentConnectionType ) ) {
		return $connectedGreatGrandparentID;
	} elseif ( !empty( $grandparentConnectionType ) ) {
		return $connectedGrandparentID;
	} else {
		return $connectedParentID;
	}
}

/**
 *  Retrieves descendants on a many-to-one connection.
 */
function get_connected_descendants( $postID, $childConnectionType = false, $grandchildConnectionType = false, $greatgrandchildConnectionType = false ) {
	global $post;
	$childrenIDs = array();
	$grandchildrenIDs = array();
	$descendantsIDs = array();

	// First Connection
	$children = new WP_Query( array(
	'connected_type' => $childConnectionType,
	'connected_items' => $postID,
	'nopaging' => true,
	'orderby' => 'menu_order',
	'order' => 'ASC',
	));

	foreach ( $children->posts as $posts ) {
		$descendantsIDs[] = $posts->ID;
		$childrenIDs[] = $posts->ID;
	}

	$grandchildren = new WP_Query( array(
	'connected_type' => $grandchildConnectionType,
	'connected_items' => $childrenIDs,
	'nopaging' => true,
	'orderby' => 'menu_order',
	'order' => 'ASC',
	));

	foreach ( $grandchildren->posts as $posts ) {
		$descendantsIDs[] = $posts->ID;
		$grandchildrenIDs[] = $posts->ID;
	}

	// Multi-type great grand children require iteration!
	foreach ( $grandchildrenIDs as $grandchildID ) {
		for ( $i = 0; $i < count( $greatgrandchildConnectionType ); $i++ ) {

			$greatgrandchildren = new WP_Query( array(
			'connected_type' => $greatgrandchildConnectionType[$i],
			'connected_items' => $grandchildID,
			'nopaging' => true,
			'orderby' => 'menu_order',
			'order' => 'ASC',
			));
			foreach ( $greatgrandchildren->posts as $posts ) {
				$descendantsIDs[] = $posts->ID;
			}

		}
	}
	
	// Output is an array of post IDs
	return $descendantsIDs;	
}