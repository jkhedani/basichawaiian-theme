<?php

/**
 *  Registration Tweaks
 */

//  A. Ensure all new registered users are "Students"
//  B. Add First Name, Last Name fields
    
function custom_registration_form () { // B.1. Add a new form element...
    $first_name = ( isset( $_POST['first_name'] ) ) ? $_POST['first_name']: '';
    $last_name = ( isset( $_POST['last_name'] ) ) ? $_POST['last_name']: '';
    ?>
    <p>
        <label for="first_name"><?php _e('First Name','BASICHWN') ?><br />
        <input type="text" name="first_name" id="first_name" class="input" value="<?php echo esc_attr(stripslashes($first_name)); ?>" size="25" /></label>
    </p>
    <p>
        <label for="last_name"><?php _e('Last Name','BASICHWN') ?><br />
        <input type="text" name="last_name" id="last_name" class="input" value="<?php echo esc_attr(stripslashes($last_name)); ?>" size="25" /></label>
    </p>
    <p>
        <label for="gender"><?php _e( 'Gender', 'BASICHWN') ?><br />
        <?php if ( esc_attr( get_user_meta( $user->ID, 'gender', true ) ) === 'male' ) { ?>
        <input type="radio" name="gender" value="male" checked="checked" /><span>Male</span>
        <input type="radio" name="gender" value="female" /><span>Female</span>
        <?php } else { ?>
        <input type="radio" name="gender" value="male" /><span>Male</span>
        <input type="radio" name="gender" value="female" checked="checked" /><span>Female</span>
        <?php } ?>
        </label>
        <br />
        <span class="description">Please indicate the gender you associate with.</span>
    </p>
    <?php
}
add_action( 'register_form','custom_registration_form' );

function custom_registration_errors ( $errors, $sanitized_user_login, $user_email ) { // B.2. Add validation. In this case, we make sure first_name is required.
    // First Name
    if ( empty( $_POST['first_name'] ) )
        $errors->add( 'first_name_error', __('<strong>ERROR</strong>: You must include a first name.','BASICHWN') );
    // Last Name
    if ( empty( $_POST['first_name'] ) )
        $errors->add( 'first_name_error', __('<strong>ERROR</strong>: You must include a first name.','BASICHWN') );
    return $errors;
}
add_filter('registration_errors', 'custom_registration_errors', 10, 3);

function custom_user_register ( $user_id ) { // B.3. Finally, save our extra registration user meta.
    if ( isset( $_POST['first_name'] ) )
        update_user_meta($user_id, 'first_name', $_POST['first_name']);
    if ( isset( $_POST['last_name'] ) )
        update_user_meta($user_id, 'last_name', $_POST['last_name']);
    /* Gender */
    update_user_meta( $user_id, 'gender', $_POST['gender'] );
}
add_action('user_register', 'custom_user_register');

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