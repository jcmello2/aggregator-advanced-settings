<?php

// // if uninstall.php is not called by WordPress, die
// if (!defined('WP_UNINSTALL_PLUGIN')) {
//     die;
// }

// delete plugin options
global $wpdb;
$query = "SELECT option_name FROM wp_options WHERE option_name LIKE 'agg_%'";
$result = $wpdb->get_results($query);
foreach ($result as $row) {
    delete_option($row->option_name);
}

// delete user options
$user_id = get_current_user_id();
if ( get_user_meta( $user_id, 'agg_as_notice_dismissed' ) ) {
    delete_user_meta($user_id, 'agg_as_notice_dismissed');
}


/* EOF */
?>
