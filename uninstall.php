<?php

// // if uninstall.php is not called by WordPress, die
// if (!defined('WP_UNINSTALL_PLUGIN')) {
//     die;
// }

global $wpdb;
$query = "SELECT option_name FROM wp_options WHERE option_name LIKE 'agg_%'";
$result = $wpdb->get_results($query);
foreach ($result as $row) {
    delete_option($row->option_name);
}

$query = "SELECT meta_key FROM wp_usermeta WHERE meta_key LIKE 'agg_as_%'";
$result = $wpdb->get_results($query);
foreach ($result as $row) {
    delete_option($row->meta_key);
}

// // delete plugin options
// $opt = array (
//       array ('name' => 'agg_hide_powered'),
//       array ('name' => 'agg_hide_admin_bar'),
//       array ('name' => 'agg_show_all_settings'),
//       array ('name' => 'agg_disable_rss_feeds'),
//       array ('name' => 'agg_set_login_style'),
//       array ('name' => 'agg_show_site_logo'),
//       array ('name' => 'agg_remove_title'),
//       array ('name' => 'agg_hide_login_nav'),
//       array ('name' => 'agg_hide_login_back'),
//       array ('name' => 'agg_hide_login_privacy')
//     );
    
// foreach ($opt as $key => $value) {
//     delete_option($opt[$key]['name']);
// }
// unset($value); 

// // delete user options
// $user_id = get_current_user_id();
// if ( get_user_meta( $user_id, 'agg_as_notice_dismissed' ) ) {
//     delete_user_meta($user_id, 'agg_as_notice_dismissed');
// }


/* EOF */
?>
