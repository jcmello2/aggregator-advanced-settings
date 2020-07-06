<?php
/*
Plugin Name: Aggregator Advanced Settings
Plugin URI: https://github.com/jcmello2/aggregator-advanced-settings
Description: WordPress Extra Settings: hide admin bar from non-admin users, set login page style, show site custom logo in login form, etc.
Version:     1.1.1
Author:      Miguel Mello
Requires at least: 5.3.2
Tested up to: 5.4.2
License:     GPL2
Text Domain: agg-advanced-settings
Domain Path: /languages
*/

// Exit if accessed directly
if (!defined('ABSPATH')){
    exit;
}

class Agg_Advanced_Settings {
    
    private static $_instance = null;
    
    public function __construct() {
        // Add actions to make magic happen
        add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
        add_action( 'init', array( $this, 'load_plugin_options' ) );
        add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array( $this, 'add_action_links' ) );
        // Load widget
        require_once( dirname(__FILE__) . '/agg-as-widget-meta.php' );
        // Option 1 - Hide "Powered by WordPress" 
        if (get_option( 'agg_hide_powered' ) == 1) {
            add_action( 'wp_enqueue_scripts', array( $this, 'register_plugin_styles' ) );
        }
        // Option 2 - Hide admin bar from non-admin users
        if (get_option( 'agg_hide_admin_bar' ) == 1) {
            add_action( 'init', array( $this, 'no_admin_init' ), 0 );
            add_action( 'after_setup_theme', array( $this, 'remove_admin_bar' ) );
        }    
        // Option 3 - Set login style
        if (get_option( 'agg_set_login_style' ) !== '') {
            add_action( 'login_enqueue_scripts', array( $this, 'set_login_stylesheet' ) );
        }
        // Option 4 - Show custom logo in login
        if (get_option( 'agg_show_site_logo' ) == 1) {
            add_action( 'login_head', array( $this, 'custom_login_logo' ), 100 );
            add_filter( 'login_headerurl', array( $this, 'custom_login_url' ) );
        }
        // Option 5 -Remove wp title in login page
        if (get_option( 'agg_remove_title' ) == 1) {
            add_filter( 'login_title', array( $this, 'custom_login_title' ) );
        }    
     } // end function __construct
    
    // Load plugin textdomain
    public function load_plugin_textdomain() {
        load_plugin_textdomain( 'agg-advanced-settings', false, dirname( plugin_basename( __FILE__ ) ).'/languages/' );
    } // end function load_plugin_textdomain
    
    // Load options
    public function load_plugin_options(){
        $current_user = wp_get_current_user();
        if (is_admin() && current_user_can('manage_options')){
            require_once( dirname(__FILE__) . '/agg-as-options.php' );
        }
    }
    
    // Add plugin settings link
    public function add_action_links ( $links ) {
        $mylink = array( '<a href="' . admin_url( 'options-general.php?page=aggregator-options' ) . '">' . __('Settings','agg-advanced-settings') . '</a>', );
        return array_merge( $mylink, $links );
    }
    
    // Hide "Powered by WordPress"
    public function register_plugin_styles() {
        wp_register_style( 'agg-advanced-settings', plugins_url( 'aggregator-advanced-settings/agg-advanced-settings.css' ) );
        wp_enqueue_style( 'agg-advanced-settings' );
    }
    
    // Redirect non-admin users
    public function no_admin_init() {      
        // Is this the admin interface
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
            // Does the current user fail the required capability level
            if (!current_user_can('activate_plugins')) {              
                // Send a temporary redirect
                wp_redirect(get_option('home'),302);              
            }           
        }       
    } // end function no_admin_init
    
    // Hide admin bar
    public function remove_admin_bar() {
        if (!current_user_can('administrator') && !is_admin()) {
          show_admin_bar(false);
        }
    } // end function remove_admin_bar
    
    // Set login style
    public function set_login_stylesheet() {
        wp_enqueue_style( 'custom-login', wp_get_theme(get_option( 'agg_set_login_style' ))->get_theme_root_uri() . '/' . wp_get_theme(get_option( 'agg_set_login_style' ))->get_stylesheet() . '/style.css' );
    } // end function set_login_stylesheet
    
    // Show custom logo in login
    public function custom_login_logo() {
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
    } // end function custom_login_logo
    
    // Link site home url in login logo
    public function custom_login_url( $url ) {
        return get_site_url();
    } // end function custom_login_url
    
    // Remove wp title in login page
    public function custom_login_title( $login_title ) {
        return str_replace(array( ' &lsaquo;', ' &#8212; WordPress'), array( ' &bull;', ''),$login_title );
    } // end function custom_login_title
    
    // Prevent cloning
    public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'agg-advanced-settings' ) );
	} // end function __clone
    
    // Prevent unserializing instances of this class
    public function __wakeup() {
        _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'agg-advanced-settings' ) );
    } // end function __wakeup
    
    // Ensure that only one instance of this class is loaded and can be loaded
    public static function instance() {
        if( is_null( self::$_instance ) ) {
          self::$_instance = new self();
        }
        return self::$_instance;
    } // end function instance
    
} // End class agg_advanced_settings

//new Agg_Advanced_Settings();
Agg_Advanced_Settings::instance();

/* EOF */
?>
