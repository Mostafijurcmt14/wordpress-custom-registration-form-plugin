<?php

if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}


class userFromTable extends WP_List_Table{

	function __construct($data){
		parent::__construct();
		$this->items = $data;
	}


	function get_columns(){
		return[
			'id' => 'Id',
			'first_name' => 'First Name',
			'last_name' => 'Last Name',
			'email' => 'Email',
			'details' => 'Details',
			'action' => 'Action',
		];
	}


	function column_cb( $items ){
		return "<input type='checkbox' value='{$items['id']}'>";
	}
	function column_action( $items ){
		$edit_link = admin_url('/admin.php?page=user-data-edit&editid=').$items['id'];
		$delete_link = admin_url('/admin.php?page=all-userform&userid=').$items['id'];
		return "
		<a href='".esc_url($edit_link)."'>Edit</a>
		<a href='".esc_url($delete_link)."'>Delete</a>
		";
	}

	function column_default($item, $column_name){
		return $item[$column_name];
	}


	function prepare_items(){
		$this->_column_headers = array($this->get_columns(), [], []);
	}


}

