<?php

// Adding Custom Fields to User Registration
// http://justintadlock.com/archives/2009/09/10/adding-and-using-custom-user-profile-fields

// function create_usermeta_form_action($user) {
// 	// Your form action here
// }
// add_action('show_user_profile', 'create_usermeta_form_action');
// add_action('edit_user_profile', 'create_usermeta_form_action');

// function save_usermeta( $user_id ) {
//   if ( !current_user_can( 'edit_user', $user_id ) ) { return false; }
// }
// add_action( 'personal_options_update', 'save_usermeta' );
// add_action( 'edit_user_profile_update', 'save_usermeta' );

// Custom Registration Form
// http://codex.wordpress.org/Customizing_the_Registration_Form

// 1. Add a new form element...

function myplugin_register_form (){
  $first_name = ( isset( $_POST['first_name'] ) ) ? $_POST['first_name']: '';
  ?>
  <p>
  	<label for="first_name"><?php _e('First Name','mydomain') ?><br />
    <input type="text" name="first_name" id="first_name" class="input" value="<?php echo esc_attr(stripslashes($first_name)); ?>" size="25" /></label>
  </p>
  <?php
}
add_action('register_form','myplugin_register_form');

// 2. Add validation. In this case, we make sure first_name is required.
function myplugin_registration_errors ($errors, $sanitized_user_login, $user_email) {
    if ( empty( $_POST['first_name'] ) )
        $errors->add( 'first_name_error', __('<strong>ERROR</strong>: You must include a first name.','mydomain') );

    return $errors;
}
add_filter('registration_errors', 'myplugin_registration_errors', 10, 3);

// As soon as user profile is created...
function myplugin_user_register ($user_id) {
  // 3. Finally, save our extra registration user meta.
  if ( isset( $_POST['first_name'] ) )
  	update_user_meta($user_id, 'first_name', $_POST['first_name']);

}
add_action('user_register', 'myplugin_user_register');

?>