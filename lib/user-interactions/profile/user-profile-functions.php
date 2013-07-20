<?php

/**
 * Extra Profile Fields
 * http://justintadlock.com/archives/2009/09/10/adding-and-using-custom-user-profile-fields
 */

function BASICHWN_create_extra_profile_fields( $user ) { ?>

	<h3><?php _e('Basic Hawaiian Profile Information'); ?></h3>

	<table class="form-table">
		<tr>
			<th><label for="gender">Gender</label></th>
			<td>
				<?php if ( esc_attr( get_user_meta( $user->ID, 'gender', true ) ) === 'male' ) { ?>
				<input type="radio" name="gender" value="male" checked="checked" /><label>Male</label>
				<input type="radio" name="gender" value="female" /><label>Female</label>
				<?php } else { ?>
				<input type="radio" name="gender" value="male" /><label>Male</label>
				<input type="radio" name="gender" value="female" checked="checked" /><label>Female</label>
				<?php } ?>
				<br />
				<span class="description">Please indicate the gender you associate with.</span>
			</td>
		</tr>
	</table>

<?php }
add_action( 'show_user_profile', 'BASICHWN_create_extra_profile_fields' );
add_action( 'edit_user_profile', 'BASICHWN_create_extra_profile_fields' );


function BASICHWN_update_extra_profile_fields( $user_id ) {
	if ( !current_user_can( 'edit_user', $user_id ) )
		return false;
	/* Gender */
	update_user_meta( $user_id, 'gender', $_POST['gender'] );
}
add_action( 'personal_options_update', 'BASICHWN_update_extra_profile_fields' );
add_action( 'edit_user_profile_update', 'BASICHWN_update_extra_profile_fields' );

?>