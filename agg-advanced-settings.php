<?php
/*
Plugin Name: Aggregator Advanced Settings
Plugin URI: https://github.com/jcmello2/aggregator-advanced-settings
Description: WordPress Extra Settings: hide admin bar from non-admin users, set login page style and options, remove WordPress references in the frontend, etc.
Version:     1.1.5
Author:      Miguel Mello
Requires at least: 5.3.2
Tested up to: 5.5.1
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
        add_action( 'admin_enqueue_scripts', array( $this, 'custom_fa_css' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_styles' ) );
        add_action('wp_dashboard_setup', array( $this, 'custom_dashboard_widgets' ) );
        
        // Load widget
        require_once( dirname(__FILE__) . '/agg-as-widget-meta.php' );
        
        // Option Hide "Powered by WordPress" 
        if (get_option( 'agg_hide_powered' ) == 1) {
            add_action( 'wp_enqueue_scripts', array( $this, 'register_plugin_styles' ) );
        }
        
        // Option Hide "Thank you for creating with WordPress" 
        if (get_option( 'agg_hide_creating' ) == 1) {
            add_filter('admin_footer_text', array( $this, 'remove_footer_admin' ) );
        }
        
        // Option Remove WordPress version number 
        if (get_option( 'agg_remove_version' ) == 1) {
            add_filter('the_generator', array( $this, 'remove_version' ) );
        }
        
        // Option Hide admin bar from non-admin users
        if (get_option( 'agg_hide_admin_bar' ) == 1) {
            add_action( 'init', array( $this, 'no_admin_init' ), 0 );
            add_action( 'after_setup_theme', array( $this, 'remove_admin_bar' ) );
        }    
        
        // Option Show all settings
        if (get_option( 'agg_show_all_settings' ) == 1) {
            add_action('admin_menu', array( $this, 'show_all_settings'));
        }
        
        // Option Disable RSS feeds
        if (get_option( 'agg_disable_rss_feeds' ) == 1) {
            add_action('do_feed', array( $this, 'disable_feed'), 1);
            add_action('do_feed_rdf', array( $this, 'disable_feed'), 1);
            add_action('do_feed_rss', array( $this, 'disable_feed'), 1);
            add_action('do_feed_rss2', array( $this, 'disable_feed'), 1);
            add_action('do_feed_atom', array( $this, 'disable_feed'), 1);
            add_action('do_feed_rss2_comments', array( $this, 'disable_feed'), 1);
            add_action('do_feed_atom_comments', array( $this, 'disable_feed'), 1);
            remove_action( 'wp_head', array( $this, 'feed_links_extra'), 3 );
            remove_action( 'wp_head', array( $this, 'feed_links'), 2 );
        }
        
        // Option Set login style
        if (get_option( 'agg_set_login_style' ) !== '') {
            add_action( 'login_enqueue_scripts', array( $this, 'set_login_stylesheet' ) );
        }
        
        // Option Show custom logo in login
        if (get_option( 'agg_show_site_logo' ) == 1) {
            add_action( 'login_head', array( $this, 'custom_login_logo' ), 100 );
            add_filter( 'login_headerurl', array( $this, 'custom_login_url' ) );
        }
        
        // Option Remove wp title in login page
        if (get_option( 'agg_remove_title' ) == 1) {
            add_filter( 'login_title', array( $this, 'custom_login_title' ) );
        }
        
        // Option Hide login navigation links
        if (get_option( 'agg_hide_login_nav' ) == 1) {
            add_action( 'login_head', array( $this,'hide_login_nav' ) );
        }
        
        // Option Hide login back to blog link
        if (get_option( 'agg_hide_login_back' ) == 1) {
            add_action( 'login_head', array( $this,'hide_login_back' ) );
        }    
        
        // Option Hide login privacy policy page link 
        if (get_option( 'agg_hide_login_privacy' ) == 1) {
            add_action( 'login_head', array( $this,'hide_login_privacy' ) );
        }
        
     } // end function __construct
    
    // Load plugin textdomain
    public function load_plugin_textdomain() {
        load_plugin_textdomain( 'agg-advanced-settings', false, dirname( plugin_basename( __FILE__ ) ).'/languages/' );
    }
    
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
    
    // Prevent cloning
    public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'agg-advanced-settings' ) );
	}
    
    // Prevent unserializing instances of this class
    public function __wakeup() {
        _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'agg-advanced-settings' ) );
    }
    
    // Ensure that only one instance of this class is loaded and can be loaded
    public static function instance() {
        if( is_null( self::$_instance ) ) {
          self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    // Show admin notices
    public function admin_notice() {
        global $current_screen;
        if ( $current_screen->parent_base == 'options-general' ) {
            echo '<div class="notice notice-info is-dismissible"><p>' . __("Missing options? Please send suggestions to",'agg-advanced-settings') . ' <a href="mailto:migu.mello@gmail.com?subject=AAS%20plugin%20suggestion">' . __("Plugin Author",'agg-advanced-settings') . '</a>.</p></div>';
        }
    }
    
    // Font awesome icons CSS
    public function custom_fa_css() {
        wp_enqueue_style( 'custom-fa', 'https://use.fontawesome.com/releases/v5.0.6/css/all.css' );
    }
    
    // Admin CSS
    public function register_admin_styles() {
        wp_enqueue_style( 'agg-as-admin' , plugins_url( 'aggregator-advanced-settings/agg-as-admin.css' ) );
    }
    
    // Dashboard help panel
    public function custom_dashboard_widgets() {
        global $wp_meta_boxes;
        wp_add_dashboard_widget('custom_help_widget', __('Aggregator Advanced Settings Support','agg-advanced-settings') , array( $this, 'custom_dashboard_help' ) );
    }
    
    // Dashboard help html    
    public function custom_dashboard_help() {
        echo '<p>' . __('Welcome to Aggregator Advanced Settings. Need help? Contact the developer','agg-advanced-settings') . ' <a href="mailto:migu.mello@gmail.com">' . __('here','agg-advanced-settings') . '</a>.</p>';
    }
    
    // Hide "Powered by WordPress"
    public function register_plugin_styles() {
        wp_register_style( 'agg-advanced-settings', plugins_url( 'aggregator-advanced-settings/agg-advanced-settings.css' ) );
        wp_enqueue_style( 'agg-advanced-settings' );
    }
    
    // Hide "Thank you for creating with WordPress"
    public function remove_footer_admin() {
        echo '';
    }
    
    // Remove WordPress version number
    public function remove_version() {
        return '';
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
    }
    
    // Hide admin bar
    public function remove_admin_bar() {
        if (!current_user_can('administrator') && !is_admin()) {
          show_admin_bar(false);
        }
    }
    
    // Show all settings    
    public function show_all_settings() {
        global $submenu;
        $permalink = get_site_url() . '/wp-admin/options.php';
        $submenu['options-general.php'][] = array( __("All Settings", 'agg-advanced-settings' ), 'manage_options', $permalink);
    }
    
    // Disable RSS feeds    
    public function disable_feed() {
        wp_redirect( home_url() ); 
    }
    
    // Set login style
    public function set_login_stylesheet() {
        wp_enqueue_style( 'custom-login', wp_get_theme(get_option( 'agg_set_login_style' ))->get_theme_root_uri() . '/' . wp_get_theme(get_option( 'agg_set_login_style' ))->get_stylesheet() . '/style.css' );
    }
    
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
    }
    
    // Link site home url in login logo
    public function custom_login_url( $url ) {
        return get_site_url();
    }
    
    // Remove wp title in login page
    public function custom_login_title( $login_title ) {
        return str_replace(array( ' &lsaquo;', ' &#8212; WordPress'), array( ' &bull;', ''),$login_title );
    }
    
    // Hide login navigation links
    public function hide_login_nav() {
        ?><style>#nav{display:none}</style><?php
    }
    
    // Hide login back to blog link
    public function hide_login_back() {
        ?><style>#backtoblog{display:none}</style><?php
    }
    
    // Hide login privacy policy link
    public function hide_login_privacy() {
        ?><style>.privacy-policy-page-link{display:none}</style><?php
    }
    
} // End class Agg_Advanced_Settings

Agg_Advanced_Settings::instance();

/* EOF */
?>
