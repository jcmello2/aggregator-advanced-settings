<?php
/*
Plugin Name: Aggregator Advanced Settings
Plugin URI: https://github.com/jcmello2/aggregator-advanced-settings
Description: WordPress Extra Settings: hide admin bar from non-admin users, set login page style to site theme, show site custom logo in login form, portuguese translation, etc.
Version:     1.1.0
Author:      Miguel Mello
Requires at least: 5.3.2
License:     GPL2
Text Domain: agg-advanced-settings
Domain Path: /languages
*/

// Exit if accessed directly
if (!defined('ABSPATH')){
    exit;
}

class agg_advanced_settings {

public function console_log( $data ){
  echo '<script>';
  echo 'console.log('. json_encode( $data ) .')';
  echo '</script>';
}

} // End class agg_advanced_settings

// Load plugin textdomain
function agg_as_load_textdomain() {
    load_plugin_textdomain( 'agg-advanced-settings', false, dirname( plugin_basename( __FILE__ ) ).'/languages/' );
}
add_action( 'init', 'agg_as_load_textdomain');
  
// Load options
function agg_as_init(){
    $current_user = wp_get_current_user();
    if (is_admin() && current_user_can('manage_options')){
        require_once( dirname(__FILE__) . '/agg-as-options.php' );
    }
}
add_action('init','agg_as_init');

// Load widget
require_once( dirname(__FILE__) . '/agg-as-widget-meta.php' );

// Hide "Proudly powered by WordPress"
if (get_option( 'agg_hide_powered' ) == 1) {
    function agg_as_register_plugin_styles() {
        wp_register_style( 'agg-advanced-settings', plugins_url( 'agg-advanced-settings/agg-advanced-settings.css' ) );
        wp_enqueue_style( 'agg-advanced-settings' );
    }
    add_action( 'wp_enqueue_scripts', 'agg_as_register_plugin_styles' );
}

// Redirect non-admin users
if (get_option( 'agg_hide_admin_bar' ) == 1) {
    function agg_as_no_admin_init() {      
        $redirect_to = get_option('home');
        // Is this the admin interface?
        if (
            // Look for the presence of /wp-admin/ in the url
            stripos($_SERVER['REQUEST_URI'],'/wp-admin') !== false
            &&
            // Allow calls to async-upload.php
            stripos($_SERVER['REQUEST_URI'],'async-upload.php') == false
            &&
            // Allow calls to admin-ajax.php
            stripos($_SERVER['REQUEST_URI'],'admin-ajax.php') == false
        ) {         
            // Does the current user fail the required capability level?
            if (!current_user_can('activate_plugins')) {              
                if ($redirect_to == '') { $redirect_to = get_option('home'); }              
                // Send a temporary redirect
                wp_redirect($redirect_to,302);              
            }           
        }       
    }
    // Add the action with maximum priority
    add_action('init','agg_as_no_admin_init',0);
    
    // Hide admin bar
    function agg_as_remove_admin_bar() {
        if (!current_user_can('administrator') && !is_admin()) {
          show_admin_bar(false);
        }
    }
    add_action('after_setup_theme', 'agg_as_remove_admin_bar');
}

// Set login style
if (get_option( 'agg_set_login_style' ) !== '') {
    function agg_as_set_login_stylesheet() {
        wp_enqueue_style( 'custom-login', wp_get_theme(get_option( 'agg_set_login_style' ))->get_theme_root_uri() . '/' . wp_get_theme(get_option( 'agg_set_login_style' ))->get_stylesheet() . '/style.css' );
    }
    add_action( 'login_enqueue_scripts', 'agg_as_set_login_stylesheet' );
}

// Show custom logo in login
if (get_option( 'agg_show_site_logo' ) == 1) {
    function agg_as_custom_login_logo() {
        if ( has_custom_logo() ) {
            $image = wp_get_attachment_image_src( get_theme_mod( 'custom_logo' ), 'full' );
            ?>
            <style type="text/css">
                .login h1 a {background-image: url(<?php echo esc_url( $image[0] ); ?>);}
                #loginform {background-color:#FFFFFF;}
            </style>
            <?php
        } else {
            ?>
            <style type="text/css">
                .login h1 a {display: none;}
            </style>
            <?php
        }
    }
    add_action( 'login_head', 'agg_as_custom_login_logo', 100 );
    
    // Link site home url in login logo
    function agg_as_custom_login_url($url) {
        return get_site_url();
    }
    add_filter( 'login_headerurl', 'agg_as_custom_login_url' );
}

// Remove wp title in login page
if (get_option( 'agg_remove_title' ) == 1) {
    function agg_as_custom_login_title( $login_title ) {
        return str_replace(array( ' &lsaquo;', ' &#8212; WordPress'), array( ' &bull;', ''),$login_title );
    }
    add_filter( 'login_title', 'agg_as_custom_login_title' );
}

/* EOF */
?>
