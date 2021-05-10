<?php
/*
Plugin Name: Aggregator Advanced Settings
Plugin URI: https://wordpress.org/plugins/aggregator-advanced-settings
Description: WordPress Extra Settings: General, Login, Security, Performance, etc
Version:     1.2.0
Author:      Miguel Mello
Requires at least: 5.3.2
Tested up to: 5.7.1
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
		add_action( 'wp_dashboard_setup', array( $this, 'custom_dashboard_widgets' ) );
		
		// Load widget
		require_once( dirname(__FILE__) . '/agg-as-widget-meta.php' );
		
		// Option Hide "Powered by WordPress" 
		if (get_option( 'agg_hide_powered' ) == 1) {
			add_action( 'wp_enqueue_scripts', array( $this, 'register_plugin_styles' ) );
		}
		
		// Option disable automatic updates
		if (get_option( 'agg_disable_auto_updates' ) == 1) {
			add_filter( 'automatic_updater_disabled', '__return_true' );
			// add_filter( 'auto_update_core', '__return_false' );
			add_filter( 'auto_update_plugin', '__return_false' );
			add_filter( 'auto_update_theme', '__return_false' );
			add_filter( 'auto_update_translation', '__return_false' );
		}
		
		// Option diasable search feature
		if (get_option( 'agg_disable_search' ) == 1) {
			add_action( 'parse_query', array( $this, 'filter_query' ) );
			add_filter( 'get_search_form', array( $this, 'filter_search' ) );
			add_action( 'widgets_init', array( $this, 'remove_search_widget' ) );
		}
		
		// Option disable RSS feeds
		if (get_option( 'agg_disable_rss_feeds' ) == 1) {
			add_action( 'do_feed', array( $this, 'disable_feed' ), 1);
			add_action( 'do_feed_rdf', array( $this, 'disable_feed' ), 1);
			add_action( 'do_feed_rss', array( $this, 'disable_feed' ), 1);
			add_action( 'do_feed_rss2', array( $this, 'disable_feed' ), 1);
			add_action( 'do_feed_atom', array( $this, 'disable_feed' ), 1);
			add_action( 'do_feed_rss2_comments', array( $this, 'disable_feed' ), 1);
			add_action( 'do_feed_atom_comments', array( $this, 'disable_feed' ), 1);
			remove_action( 'wp_head', array( $this, 'feed_links_extra' ), 3 );
			remove_action( 'wp_head', array( $this, 'feed_links' ), 2 );
		}
		
		// Option enable shortcodes in widgets
		if (get_option( 'agg_enable_shortcode_widget' ) == 1) {
			add_filter( 'widget_text', 'shortcode_unautop' );
			add_filter( 'widget_text', 'do_shortcode' );
		}
		
		// Option hide "Thank you for creating with WordPress" 
		if (get_option( 'agg_hide_creating' ) == 1) {
			add_filter( 'admin_footer_text', array( $this, 'remove_footer_admin' ) );
		}
		
		// Option hide admin bar from non-admin users
		if (get_option( 'agg_hide_admin_bar' ) == 1) {
			add_action( 'init', array( $this, 'no_admin_init' ), 0 );
			add_action( 'after_setup_theme', array( $this, 'remove_admin_bar' ) );
		}    
		
		// Option show all settings
		if (get_option( 'agg_show_all_settings' ) == 1) {
			add_action('admin_menu', array( $this, 'show_all_settings' ) );
		}
		
		// Option include post/page ID's in admin table
		if (get_option( 'agg_include_ids' ) == 1) {
			add_filter( 'manage_posts_columns', array( $this, 'posts_columns_id' ), 5);
			add_action( 'manage_posts_custom_column', array( $this, 'posts_custom_id_columns') , 5, 2);
			add_filter( 'manage_pages_columns', array( $this, 'posts_columns_id' ), 5);
			add_action( 'manage_pages_custom_column', array( $this, 'posts_custom_id_columns' ) , 5, 2);
			// Determine ID column width
			add_action('admin_head', 'custom_admin_styling');
			function custom_admin_styling() {
			echo '<style type="text/css">';
			echo 'th#wps_post_id{width:50px;}';
			echo '</style>';
			}
		}
		
		// Option disable admin notices
		if (get_option( 'agg_dismiss_admin_notices' ) == 1) {
			add_action('admin_enqueue_scripts', array( $this, 'admin_theme_style' ) );
			add_action('login_enqueue_scripts', array( $this, 'admin_theme_style' ) );
		}

		// Option disable login by email
		if (get_option( 'agg_disable_email_login' ) == 1) {
			remove_filter( 'authenticate', 'wp_authenticate_email_password', 20);
		}
				
		// Option custom login errors message
		if (get_option( 'agg_custom_errors_message' ) !== '') {
			add_filter( 'login_errors', array( $this, 'custom_login_errors' ) );
		}
		
		// Option set login style
		if (get_option( 'agg_set_login_style' ) !== '') {
			add_action( 'login_enqueue_scripts', array( $this, 'set_login_stylesheet' ) );
		}
		
		// Option show custom logo in login
		if (get_option( 'agg_show_site_logo' ) == 1) {
			add_action( 'login_head', array( $this, 'custom_login_logo' ), 100 );
			add_filter( 'login_headerurl', array( $this, 'custom_login_url' ) );
		}
		
		// Option remove wp title in login page
		if (get_option( 'agg_remove_title' ) == 1) {
			add_filter( 'login_title', array( $this, 'custom_login_title' ) );
		}
		
		// Option hide login navigation links
		if (get_option( 'agg_hide_login_nav' ) == 1) {
			add_action( 'login_head', array( $this,'hide_login_nav' ) );
		}
		
		// Option hide login back to blog link
		if (get_option( 'agg_hide_login_back' ) == 1) {
			add_action( 'login_head', array( $this,'hide_login_back' ) );
		}    
		
		// Option hide login privacy policy page link 
		if (get_option( 'agg_hide_login_privacy' ) == 1) {
			add_action( 'login_head', array( $this,'hide_login_privacy' ) );
		}
		
		// Option reject malicious URL requests 
		if (get_option( 'agg_reject_malicious_requests' ) == 1) {
			add_action( 'init', array( $this, 'reject_malicious_requests' ) );
		}
		
		// Option remove WordPress version number 
		if (get_option( 'agg_remove_version' ) == 1) {
			add_filter( 'the_generator', array( $this, 'remove_version' ) );
		}

		// Option disable XML-RPC 
		if (get_option( 'agg_disable_xml_rpc' ) == 1) {
			add_filter( 'xmlrpc_enabled', '__return_false' );
		}
		
		// Option disable file editor 
		if (get_option( 'agg_disable_file_editor' ) == 1) {
			add_action( 'init', array( $this, 'disable_file_editor' ) );	
		}
		
		// Option HTTPS with Non-Secure Media 
		if (get_option( 'agg_https_with_Non-Secure_Media' ) == 1) {
			add_action( 'init', array( $this, 'nocdn_on_ssl_page' ) );	
		}
		
		// Option disable links from comments
		if (get_option( 'agg_disable_links_from_comments' ) == 1) {
			remove_filter('comment_text', 'make_clickable', 9);
		}
		
		// Disable emoji's
		if (get_option( 'agg_disable_emoji' ) == 1) {
			add_action( 'init', array( $this, 'disable_wp_emojicons' ) );
			add_filter( 'emoji_svg_url', '__return_false' );
		}
		
		// Disable embed
		if (get_option( 'agg_disable_embed' ) == 1) {
			add_action( 'init', array( $this, 'speed_stop_loading_wp_embed' ) );
		}
		
		// Disable jpeg compression
		if (get_option( 'agg_disable_jpeg_compression' ) == 1) {
			add_filter( 'jpeg_quality', array( $this, 'smashing_jpeg_quality' ) );
		}
		
		// Enable SVG files upload
		if (get_option( 'agg_enable_svg_files_upload' ) == 1) {
			add_filter('upload_mimes', array( $this, 'cc_mime_types' ) );
		}
		
		// Change default add media settings
		if (get_option( 'agg_change_default_add_media_settings' ) == 1) {
			add_action( 'after_setup_theme', array( $this, 'attachment_display_settings' ) );
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
	
	// Show agg admin notice
	public function admin_notice() {
		global $current_screen;
		if ( $current_screen->parent_base == 'options-general' ) {
			echo '<div class="notice notice-success is-dismissible"><p>' . __("Missing options? Please send suggestions to",'agg-advanced-settings') . ' <a href="mailto:migu.mello@gmail.com?subject=AAS%20plugin%20suggestion">' . __("Plugin Author",'agg-advanced-settings') . '</a>.</p></div>';
		}
	}
	
	// Font awesome icons CSS
	public function custom_fa_css() {
		wp_enqueue_style( 'custom-fa', 'https://use.fontawesome.com/releases/v5.14.0/css/all.css' );
	}
	
	// Admin CSS
	public function register_admin_styles() {
		wp_enqueue_style( 'agg-as-admin' , plugins_url( 'agg-advanced-settings/agg-as-admin.css' ) );
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
		wp_register_style( 'agg-advanced-settings', plugins_url( 'agg-advanced-settings/agg-advanced-settings.css' ) );
		wp_enqueue_style( 'agg-advanced-settings' );
	}
	
	// Disable search feature
	public function filter_query( $query, $error = true ) {
		if ( is_search() ) {
			$query->is_search = false;
			$query->query_vars['s'] = false;
			$query->query['s'] = false;
			// to error
			if ( $error == true )
				$query->is_404 = true;
			}
	}
	
	// Disable search form    
	public function filter_search() {
	   return null; 
	}
	
	// Disable search widget
	public function remove_search_widget() {
		unregister_widget('WP_Widget_Search');
	}
	
	// Disable RSS feeds    
	public function disable_feed() {
		wp_redirect( home_url() ); 
	}
	
	// Hide "Thank you for creating with WordPress"
	public function remove_footer_admin() {
		echo '';
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
		if (current_user_can('manage_options')) {
			global $submenu;
			$permalink = get_site_url() . '/wp-admin/options.php';
			$submenu['options-general.php'][] = array( __("All Settings", 'agg-advanced-settings' ), 'manage_options', $permalink);
		}
	}
	
	// Include post/page ID's in admin table
	public function posts_columns_id( $defaults ) {
		$defaults['wps_post_id'] = __('ID');
    	return $defaults;
	}
	
	public function posts_custom_id_columns($column_name, $id){
	    if($column_name === 'wps_post_id'){
	        echo $id;
	    }
	}
	
	// Disable admin notices
	public function admin_theme_style() {
		if (current_user_can( 'manage_options' )) {
			echo '<style>.update-nag, .updated, .error, .is-dismissible { display: none !important; }</style>';
		}
	}

	// Custom login errors message
	public function custom_login_errors() {
		return get_option('agg_custom_errors_message');    
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
	
	// Reject malicious GET requests
	public function reject_malicious_requests() {
		
		global $user_ID;
		
		if($user_ID) {
			if(!current_user_can('administrator')) {	
		
				$request_uri = $_SERVER['REQUEST_URI'];
				$user_agent = $_SERVER['HTTP_USER_AGENT'];
				$query_string = $_SERVER['QUERY_STRING'] ?? '';
				
				if ( ! current_user_can( 'administrator' ) ) {
				
					// request uri
					if (strlen($request_uri) > 255 || 
					    stripos($request_uri, 'eval(') || 
					    stripos($request_uri, 'CONCAT') || 
					    stripos($request_uri, 'UNION+SELECT') || 
					    stripos($request_uri, '(null)') || 
					    stripos($request_uri, 'base64_') || 
					    stripos($request_uri, '/localhost') || 
					    stripos($request_uri, '/pingserver') || 
					    stripos($request_uri, '/config.') || 
					    stripos($request_uri, '/wwwroot') || 
					    stripos($request_uri, '/makefile') || 
					    stripos($request_uri, 'crossdomain.') || 
					    stripos($request_uri, 'proc/self/environ') || 
					    stripos($request_uri, 'etc/passwd') || 
					    stripos($request_uri, '/https/') || 
					    stripos($request_uri, '/http/') || 
					    stripos($request_uri, '/ftp/') || 
					    stripos($request_uri, '/cgi/') || 
					    stripos($request_uri, '.cgi') || 
					    stripos($request_uri, '.exe') || 
					    stripos($request_uri, '.sql') || 
					    stripos($request_uri, '.ini') || 
					    stripos($request_uri, '.dll') || 
					    stripos($request_uri, '.asp') || 
					    stripos($request_uri, '.jsp') || 
					    stripos($request_uri, '/.bash') || 
					    stripos($request_uri, '/.git') || 
					    stripos($request_uri, '/.svn') || 
					    stripos($request_uri, '/.tar') || 
					    stripos($request_uri, ' ') || 
					    stripos($request_uri, '<') || 
					    stripos($request_uri, '>') || 
					    stripos($request_uri, '/=') || 
					    stripos($request_uri, '...') || 
					    stripos($request_uri, '+++') || 
					    stripos($request_uri, '://') || 
					    stripos($request_uri, '/&&') || 
					    // user agents
					    stripos($user_agent, 'binlar') || 
					    stripos($user_agent, 'casper') || 
					    stripos($user_agent, 'cmswor') || 
					    stripos($user_agent, 'diavol') || 
					    stripos($user_agent, 'dotbot') || 
					    stripos($user_agent, 'finder') || 
					    stripos($user_agent, 'flicky') || 
					    stripos($user_agent, 'libwww') || 
					    stripos($user_agent, 'nutch') || 
					    stripos($user_agent, 'planet') || 
					    stripos($user_agent, 'purebot') || 
					    stripos($user_agent, 'pycurl') || 
					    stripos($user_agent, 'skygrid') || 
					    stripos($user_agent, 'sucker') || 
					    stripos($user_agent, 'turnit') || 
					    stripos($user_agent, 'vikspi') || 
					    stripos($user_agent, 'zmeu') ||
						// query strings
						stripos($query_string, '?') || 
					    stripos($query_string, ':') || 
					    stripos($query_string, '[') || 
					    stripos($query_string, ']') || 
					    stripos($query_string, '../') || 
					    stripos($query_string, '127.0.0.1') || 
					    stripos($query_string, 'loopback') || 
					    stripos($query_string, '%0A') || 
					    stripos($query_string, '%0D') || 
					    stripos($query_string, '%22') || 
					    stripos($query_string, '%27') || 
					    stripos($query_string, '%3C') || 
					    stripos($query_string, '%3E') || 
					    stripos($query_string, '%00') || 
					    stripos($query_string, '%2e%2e') || 
					    stripos($query_string, 'union') || 
					    stripos($query_string, 'input_file') || 
					    stripos($query_string, 'execute') || 
					    stripos($query_string, 'mosconfig') || 
					    stripos($query_string, 'environ') || 
					    // stripos($query_string, 'scanner') || 
					    stripos($query_string, 'path=.') || 
					    stripos($query_string, 'mod=.')
					    )	{
					    		@header('HTTP/1.1 403 Forbidden');
								@header('Status: 403 Forbidden');
								@header('Connection: Close');
								@exit;
							}
				}
			}	
		}
	} // End function reject_malicious_requests
	
	// Remove WP version number
	public function remove_version() {
		return '';
	}
	
	// Disable file editor
	public function disable_file_editor() {
		define('DISALLOW_FILE_EDIT', true);
	}
	
	// HTTPS with Non-Secure Media
	public function nocdn_on_ssl_page() {
		if(array_key_exists('HTTPS', $_SERVER) && $_SERVER['HTTPS'] == "on") {
        	define('DONOTCDN', true);
    	}
	}
	
	// Disable Emoji    
	public function disable_wp_emojicons() {
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
		remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
		remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
		add_filter( 'tiny_mce_plugins', array( $this, 'disable_emojicons_tinymce' ) );
		add_filter( 'wp_resource_hints', array( $this, 'disable_emojis_remove_dns_prefetch'), 10, 2 );
	}
	
	public function disable_emojicons_tinymce() {
		global $plugins;
		if ( is_array( $plugins ) ) {
			return array_diff( $plugins, array( 'wpemoji' ) );
		} else {
			return array();
		}
	}
	
	public function disable_emojis_remove_dns_prefetch( $urls, $relation_type ) {
		if ( 'dns-prefetch' == $relation_type ) {
			/** This filter is documented in wp-includes/formatting.php */
			$emoji_svg_url = apply_filters( 'emoji_svg_url', 'https://s.w.org/images/core/emoji/2/svg/' );
			$urls = array_diff( $urls, array( $emoji_svg_url ) );
		}
			return $urls;
	}
	
	// Remove WP embed script
	public function speed_stop_loading_wp_embed() {
	    if (!is_admin()) {
	        wp_deregister_script('wp-embed');
	    }
	}
	
	// Disable JPEG compression
	public function smashing_jpeg_quality() {
		return 100;
	}
	
	// Allow SVG files upload
	public function cc_mime_types($mimes) {
		$mimes['svg'] = 'image/svg+xml';
		return $mimes;
	}
	
	// Change default add media settings
	public function attachment_display_settings() {
	        update_option( 'image_default_align', 'center' ); // left, center, right, none
	        update_option( 'image_default_link_type', 'none' );
	        update_option( 'image_default_size', 'full size' ); // thumbnail, medium, large, full size
	}
	
} // End class Agg_Advanced_Settings

Agg_Advanced_Settings::instance();

/* EOF */
?>
