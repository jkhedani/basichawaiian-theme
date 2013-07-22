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
