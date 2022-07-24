<?php
/*
Plugin Name: User Form
Plugin URI: https://mizu.me
Description: This is the demo plugin.
Version: 1.0.0
Author: Mostafijur Rahman
Author URI: https://mizu.me
License: GPLv2 or later
Text Domain: user-form	
Domain Path: /languages
*/


defined( 'ABSPATH' ) || exit;


/**
* Add WP data-table class file
*/

require_once "class.userformlists.php";


/**
* WP enqueue script call for frontend form design
*/

function userform_style_enqueue_scripts(){
	wp_enqueue_style('userform-style', plugin_dir_url( __FILE__ ) .'/assets/userform.css');
}
add_action('wp_enqueue_scripts','userform_style_enqueue_scripts');


/**
* WP admin enqueue script call for backend form design
*/

function userform_admin_enqueue_scripts(){
	wp_enqueue_style('userform-admin-style', plugin_dir_url(__FILE__) .'/admin/userform-admin.css');
}
add_action('admin_enqueue_scripts', 'userform_admin_enqueue_scripts');


/**
* Admin menu create for this plugin
*/

function userform_admin_menu(){
	add_menu_page(
		__('UserForm', 'user-form'),
		__('UserForm', 'user-form'),
		'manage_option',
		'userform',
		'',
		'dashicons-tagcloud',
		6
	);
	add_submenu_page(
		'userform',
		__('All Lists', 'user-form'),
		__('All Lists', 'user-form'),
		'manage_options', 
		'all-userform',
		'all_userform_lists_callback'
	);
	add_submenu_page(
		'null',
		__('Edit user', 'user-form'),
		__('Edit user', 'user-form'),
		'manage_options',
		'user-data-edit',
		'user_data_edit_callback',
	);
}
add_action('admin_menu', 'userform_admin_menu');



/**
* Admin menu callback function
*/

function all_userform_lists_callback(){
	global $wpdb;
	echo '<h2>All user lists: </h2>';
	?>

	<div class="form_box">
		<div class="form_box_content">
			<?php
				global $wpdb;
				$db_users = $wpdb->get_results("SELECT id, first_name, last_name, email, details FROM {$wpdb->prefix}formdata", ARRAY_A);
				// print_r($db_users);
				$dbTableUser = new userFromTable($db_users);
				$dbTableUser->prepare_items();
				$dbTableUser->display();
			?>
		</div>
	</div>

	<?php
}



/**
* User data edit and update calback function
*/

function user_data_edit_callback(){

	global $wpdb;
	$table_name = $wpdb->prefix.'formdata';

	$id = "";
	if(isset($_POST['update_id'])){
		$id = sanitize_text_field( $_POST['update_id'] );
	}

	$userform_create_nonce = "";

	if(isset($_POST['userform_nonce'])){
	$userform_create_nonce = sanitize_text_field($_POST['userform_nonce']);
	}
	if(isset($_POST['firstname'])){
		$first_name = sanitize_text_field($_POST['firstname']);
	}
	if(isset($_POST['lastname'])){
		$last_name = sanitize_text_field($_POST['lastname']);
	}
	if(isset($_POST['email'])){
		$user_email = sanitize_text_field($_POST['email']);
	}
	if(isset($_POST['user_bio'])){
		$user_bio = sanitize_text_field($_POST['user_bio']);
    }

    // wp nonce verify
	if(wp_verify_nonce($userform_create_nonce, 'userform-nonce')){
		$wpdb->update($table_name,[
			'first_name' => $first_name,
			'last_name' => $last_name,
			'email' => $user_email,
			'details' => $user_bio
		], [ 'id' => $id ]);
	}

	?>


<div class="userform">
  <div class="row">
    <div class="column">
 			<?php 
            global $wpdb;  
            $userId = absint($_GET['editid']);
               if( isset($userId) ){
                  $getresult = $wpdb->get_results( "select * from {$wpdb->prefix}formdata WHERE id='{$userId}'" );
               }
            ?>

      <form action="" method="POST">
      	<?php
		foreach($getresult as $result){
            //print_r($result);
              		
           ?>
      	<input type="hidden" name="userform_nonce" value="<?php echo wp_create_nonce('userform-nonce'); ?>"/>
      	<input type="hidden" name="update_id" value="<?php echo $userId ?>"/>

        <fieldset>

        <legend>Edit user information</legend>
     	</br>

        	<label for="name">First Name:</label>
          	<input type="text" name="firstname" value="<?php echo $result->first_name; ?>">

          	<label for="name">Last Name:</label>
          	<input type="text" name="lastname" value="<?php echo $result->last_name; ?>">

          	<label for="email">Email:</label>
          	<input type="email" name="email" value="<?php echo $result->email; ?>">

		  	<label for="name">Bio</label>
          	<textarea name="user_bio"><?php echo $result->details; ?></textarea>

        </fieldset>
 
		<input type="submit" value="<?php _e('Update'); ?>"/>

			<?php } ?>
      </form>
    </div>
  </div>
 </div>
	<?php
	wp_reset_postdata();
}



/**
* User delete query
*/
function userform_item_delete_query(){ 
 	if( isset($_GET['userid']) ){
		global $wpdb;  
		$deleteItem= absint($_GET['userid']);
		$table_name = $wpdb->prefix.'formdata';
		$wpdb->delete( $table_name, array( 'id' => $deleteItem ) );	 
 	} 
}
userform_item_delete_query();



/**
* Register actiovation hook for create table
*/

function userform_init(){
	global $wpdb;
	$charset_collate = $wpdb->get_charset_collate();
	$table_name = $wpdb->prefix.'formdata';
	$sql = "CREATE TABLE {$table_name} (
		id INT NOT NULL AUTO_INCREMENT,
		first_name VARCHAR(200),
		last_name VARCHAR(200),
		email VARCHAR(200),
		details VARCHAR(200),
		PRIMARY KEY(id)
	) $charset_collate;";
	require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);	
}
register_activation_hook(__FILE__,"userform_init");


/**
* Register deactivation hook for truncate the table
*/

function rushfilter_deactivation_hook(){
		global $wpdb;
		$table_name = $wpdb->prefix.'formdata';
		$query = "TRUNCATE TABLE {$table_name}";
		$wpdb->query($query);
}
register_deactivation_hook(__FILE__,"rushfilter_deactivation_hook");


/**
* Userform inset query, Get user input data and Submit to database
*/

function get_user_data(){
	ob_start();

	global $wpdb;
	$table_name = $wpdb->prefix.'formdata';

	$userform_create_nonce = "";

	if(isset($_POST['userform_nonce'])){
	$userform_create_nonce = sanitize_text_field($_POST['userform_nonce']);
	}
	if(isset($_POST['firstname'])){
		$first_name = sanitize_text_field($_POST['firstname']);
	}
	if(isset($_POST['lastname'])){
		$last_name = sanitize_text_field($_POST['lastname']);
	}
	if(isset($_POST['email'])){
		$user_email = sanitize_text_field($_POST['email']);
	}
	if(isset($_POST['user_bio'])){
		$user_bio = sanitize_text_field($_POST['user_bio']);
    }

 	// wp nonce verify
	if(wp_verify_nonce($userform_create_nonce, 'userform-nonce')){
		$wpdb->insert($table_name,[
			'first_name' => $first_name,
			'last_name' => $last_name,
			'email' => $user_email,
			'details' => $user_bio
		]);
		//wp_redirect(home_url());
	}

	?>


<div class="userform">
  <div class="row">
    <div class="col-md-12">
 
      <form action="" method="POST">

      	<input type="hidden" name="userform_nonce" value="<?php echo wp_create_nonce('userform-nonce'); ?>"/>

        <fieldset>

          <legend>Your Information</legend>

          <label for="name">First Name:</label>
          <input type="text" name="firstname" required>

          <label for="name">Last Name:</label>
          <input type="text" name="lastname">

          <label for="email">Email:</label>
          <input type="email" name="email" required>

		  <label for="name">Bio</label>
          <textarea name="user_bio"></textarea>

        </fieldset>
 
		<input type="submit" value="<?php _e('Register Account'); ?>"/>


      </form>
    </div>
  </div>
 </div>

	<?php
	wp_reset_postdata();
	return ob_get_clean();
}
add_shortcode('get_user_data_shortcode','get_user_data');
