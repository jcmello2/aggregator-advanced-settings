<?php

// create custom plugin settings menu
function agg_as_menu() {
	add_options_page( 'Aggregator Options', __("Advanced Settings",'agg-advanced-settings'), 'manage_options', 'aggregator-options', 'agg_as_options', 1);
}
add_action( 'admin_menu', 'agg_as_menu' );

function agg_as_options() {
	
	  //must check that the user has the required capability 
    if (!current_user_can('manage_options'))
    {
      wp_die( __( _e('You do not have sufficient permissions to access this page.', 'agg-advanced-settings' )));
    }
    
    //Get the active tab from the $_GET param
    $default_tab = 'general';
    $tab = isset($_GET['tab']) ? $_GET['tab'] : $default_tab;
    
    // Show admin notices
    require_once( dirname(__FILE__) . '/agg-as-notice-handling.php' );
    $agg_notice = new Agg_Notice_Handling();
    
    // global variables
    global $_wp_admin_css_colors;
    $admin_color = get_user_option( 'admin_color' );
    $colors = $_wp_admin_css_colors[$admin_color]->colors;
    
    // variables for the fields and options names 
    $opt_form_name = 'form1';
    $hidden_field_name = 'agg_submit_hidden';
    if ($tab == 'general') { 
      $opt = array (
        array ('name' => 'agg_hide_powered', 'value' => 0),
        array ('name' => 'agg_disable_auto_updates', 'value' => 0),
        array ('name' => 'agg_disable_search', 'value' => 0),
        array ('name' => 'agg_disable_rss_feeds', 'value' => 0),
        array ('name' => 'agg_enable_shortcode_widget', 'value' => 0)
        );
    } else if ($tab == 'admin') { 
      $opt = array (
        array ('name' => 'agg_hide_creating', 'value' => 0),
        array ('name' => 'agg_hide_admin_bar', 'value' => 0),
        array ('name' => 'agg_show_all_settings', 'value' => 0),
        array ('name' => 'agg_include_ids', 'value' => 0)
        );
    } else if ($tab == 'login') {  
      $opt = array (
        array ('name' => 'agg_disable_email_login', 'value' => 0),
        array ('name' => 'agg_custom_errors_message', 'value' => ''),
        array ('name' => 'agg_set_login_style', 'value' => ''),
        array ('name' => 'agg_show_site_logo', 'value' => 0),
        array ('name' => 'agg_remove_title', 'value' => 0),
        array ('name' => 'agg_hide_login_nav', 'value' => 0),
        array ('name' => 'agg_hide_login_back', 'value' => 0),
        array ('name' => 'agg_hide_login_privacy', 'value' => 0)
        );
    } else if ($tab == 'security') {  
      $opt = array (
        array ('name' => 'agg_reject_malicious_requests', 'value' => 0),
        array ('name' => 'agg_remove_version', 'value' => 0),
        array ('name' => 'agg_disable_xml_rpc', 'value' => 0),
        array ('name' => 'agg_disable_file_editor', 'value' => 0),
        array ('name' => 'agg_https_with_Non-Secure_Media', 'value' => 0),
        array ('name' => 'agg_disable_links_from_comments', 'value' => 0)
        );
    } else if ($tab == 'performance') {
      $opt = array (  
        array ('name' => 'agg_disable_emoji', 'value' => 0),
        array ('name' => 'agg_disable_embed', 'value' => 0)
        );
    } else if ($tab == 'media') {
      $opt = array (  
        array ('name' => 'agg_disable_jpg_compression', 'value' => 0),
        array ('name' => 'agg_enable_svg_files_upload', 'value' => 0),
        array ('name' => 'agg_change_default_add_media_settings', 'value' => 0)
        );
    }
    
    // Read in existing option value from database
    foreach($opt as $key => $value) {
      $opt[$key]['value'] = get_option($value['name']);
    }
    unset($value);
    
    // See if the user has posted us some information
    // If they did, this hidden field will be set to 'Y'
    if( isset($_POST[ $hidden_field_name ]) && $_POST[ $hidden_field_name ] == 'Y' ) {
        
        // Check nonce    
        check_admin_referer('agg-as-update-options_' . $opt_form_name);
        
        // Read their posted value
        foreach( $opt as $key => $value) {
          if (isset($_POST[ $value['name'] ])) {
            $opt[$key]['value'] = $_POST[ $value['name'] ];
          } else {
            $opt[$key]['value'] = 0;
          }
        }
        
        // Save the posted value in the database
        foreach( $opt as $value) {
          update_option( $value['name'],$value['value'] );
        }
        
// Put a "settings saved" message on the screen
?>

<div class="updated"><p><strong><?php _e('settings saved.', 'agg-advanced-settings' ); ?></strong></p></div>

<?php
    }
?>

<!-- Now display the settings editing screen -->
<div class="wrap">
<!-- Print the page title -->
<h1><?php _e("Advanced Settings", 'agg-advanced-settings' ) ?></h1>

<!-- settings form -->
<form name="<?php echo $opt_form_name ?>" method="post" action="">

<?php 
wp_nonce_field('agg-as-update-options_' . $opt_form_name); 
$i = -1; 
?>

<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">

<!-- Here are our tabs -->
<nav class="nav-tab-wrapper">
  <a href="?page=aggregator-options&tab=general" class="nav-tab <?php if($tab==='general'): ?>nav-tab-active<?php endif; ?>" style="width: 10%; text-align: center; table-layout: fixed;"><span style="font-size: 16px"><i class="fas fa-cog"></i></span> <?php _e("General", 'agg-advanced-settings' ); ?></a>
  <a href="?page=aggregator-options&tab=admin" class="nav-tab <?php if($tab==='admin'): ?>nav-tab-active<?php endif; ?>" style="width: 10%; text-align: center; table-layout: fixed;"><span style="font-size: 16px"><i class="fas fa-user-cog"></i></span> <?php _e("Admin", 'agg-advanced-settings' ); ?></a>
  <a href="?page=aggregator-options&tab=login" class="nav-tab <?php if($tab==='login'):?>nav-tab-active<?php endif; ?>" style="width: 10%; text-align: center; table-layout: fixed;"><span style="font-size: 16px"><i class="fas fa-sign-in-alt"></i></span> <?php _e("Login", 'agg-advanced-settings' ); ?></a>
  <a href="?page=aggregator-options&tab=security" class="nav-tab <?php if($tab==='security'):?>nav-tab-active<?php endif; ?>" style="width: 10%; text-align: center; table-layout: fixed;"><span style="font-size: 16px"><i class="fas fa-shield-alt"></i></span> <?php _e("Security", 'agg-advanced-settings' ); ?></a>
  <a href="?page=aggregator-options&tab=performance" class="nav-tab <?php if($tab==='performance'):?>nav-tab-active<?php endif; ?>" style="width: 10%; text-align: center; table-layout: fixed;"><span style="font-size: 16px"><i class="fas fa-tachometer-alt"></i></span> <?php _e("Performance", 'agg-advanced-settings' ); ?></a>
  <a href="?page=aggregator-options&tab=media" class="nav-tab <?php if($tab==='media'):?>nav-tab-active<?php endif; ?>" style="width: 10%; text-align: center; table-layout: fixed;"><span style="font-size: 16px"><i class="fas fa-photo-video"></i></span> <?php _e("Media", 'agg-advanced-settings' ); ?></a>
</nav>
<div class="tab-content">

<!-- tab general -->
    <?php switch($tab) :
      case 'general': ?>
<table class="form-table" role="presentation">
<!-- option: Try to hide 'Powered by WordPress' -->
<tr>
<th style="white-space: nowrap;" scope="row">
<?php if ($opt[++$i]['value'] == 1) { ?>
<div class="agg-tooltip">
<i class="fab fa-wordpress-simple" style="color:<?php echo $colors[2]; ?>"></i>
<span class="agg-tooltiptext"><?php _e("It might not work depending on your site theme. If it's not working, please report to our plugin support on your Dashboard", 'agg-advanced-settings' ); ?></span>
</div> 
<?php } else { ?>
<i class="fab fa-wordpress-simple" style="color:#808080"></i>
<?php } ?>
<label for="<?php echo $opt[$i]['name']; ?>"><?php _e("Try to hide 'Powered by WordPress'", 'agg-advanced-settings' ); ?> </label>  
</th>
<td><input type="checkbox" name="<?php echo $opt[$i]['name']; ?>" value="1" <?php checked (1,$opt[$i]['value']); ?>> <?php _e("From the footer", 'agg-advanced-settings' ); ?></td>
</tr>
<!-- option: Disable auto updates -->
<tr>
<th scope="row"><i class="fas fa-magic" style="color:<?php echo $opt[++$i]['value'] == 1 ? '#000000' : '#808080' ; ?>;"></i>
<label for="<?php echo $opt[$i]['name']; ?>"><?php _e("Disable automatic updates", 'agg-advanced-settings' ); ?></label></th>
<td><input type="checkbox" name="<?php echo $opt[$i]['name']; ?>" value="1" <?php checked (1,$opt[$i]['value']); ?>> <?php _e("Core, plugins, themes and translations", 'agg-advanced-settings' ) ; ?></td>
</tr>
<!-- option: Disable search feature -->
<tr>
<th scope="row"><i class="fas fa-search" style="color:<?php echo $opt[++$i]['value'] == 1 ? '#000000' : '#808080' ; ?>;"></i>
<label for="<?php echo $opt[$i]['name']; ?>"><?php _e("Disable search feature", 'agg-advanced-settings' ); ?></label></th>
<td><input type="checkbox" name="<?php echo $opt[$i]['name']; ?>" value="1" <?php checked (1,$opt[$i]['value']); ?>> <?php _e("Redirect all search to page not found", 'agg-advanced-settings' ) ; ?></td>
</tr>
<!-- option: Disable RSS feeds -->
<tr>
<th scope="row"><i class="fas fa-rss-square" style="color:<?php echo $opt[++$i]['value'] == 1 ? '#000000' : '#808080' ; ?>;"></i>
<label for="<?php echo $opt[$i]['name']; ?>"><?php _e("Disable RSS feeds", 'agg-advanced-settings' ); ?></label></th>
<td><input type="checkbox" name="<?php echo $opt[$i]['name']; ?>" value="1" <?php checked (1,$opt[$i]['value']); ?>> <?php _e("Redirect to site home", 'agg-advanced-settings' ) ; ?></td>
</tr>
<!-- option: Enable shortcode widget -->
<tr>
<th scope="row"><i class="fab fa-html5" style="color:<?php echo $opt[++$i]['value'] == 1 ? '#000000' : '#808080' ; ?>;"></i>
<label for="<?php echo $opt[$i]['name']; ?>"><?php _e("Enable shortcodes in HTML widgets", 'agg-advanced-settings' ); ?></label></th>
<td><input type="checkbox" name="<?php echo $opt[$i]['name']; ?>" value="1" <?php checked (1,$opt[$i]['value']); ?>> <?php _e("Activate in custom html widgets", 'agg-advanced-settings' ) ; ?></td>
</tr>
</table>
<!-- tab admin -->
    <?php break;
      case 'admin': ?>
<table class="form-table" role="presentation">
<!-- option: Hide 'Thank you for creating with WP' -->
<tr>
<th scope="row"><i class="fab fa-wordpress-simple" style="color:<?php echo $opt[++$i]['value'] == 1 ? '#000000' : '#808080' ; ?>;"></i>
<label for="<?php echo $opt[+$i]['name']; ?>"><?php _e("Hide 'Thank you for creating with WP'", 'agg-advanced-settings' ); ?></label></th>
<td><input type="checkbox" name="<?php echo $opt[$i]['name']; ?>" value="1" <?php checked (1,$opt[$i]['value']); ?>> <?php _e("From the admin footer", 'agg-advanced-settings' ); ?></td>
</tr>
<!-- option: Hide admin bar (and profile) -->
<tr>
<th style="white-space: nowrap;" scope="row"><i class="fas fa-users-cog" style="color:<?php echo $opt[++$i]['value'] == 1 ? '#000000' : '#808080' ; ?>;"></i>
<label for="<?php echo $opt[$i]['name']; ?>"><?php _e("Hide admin bar (and profile)", 'agg-advanced-settings' ); ?></label></th>
<td><input type="checkbox" name="<?php echo $opt[$i]['name']; ?>" value="1" <?php checked (1,$opt[$i]['value']); ?>> <?php _e("From non-admin users", 'agg-advanced-settings' ); ?></td>
</tr>
<!-- option: Show all settings -->
<tr>
<th scope="row"><i class="fas fa-cogs" style="color:<?php echo $opt[++$i]['value'] == 1 ? '#000000' : '#808080' ; ?>;"></i>
<label for="<?php echo $opt[$i]['name']; ?>"><?php _e("Show all settings", 'agg-advanced-settings' ); ?></label></th>
<td><input type="checkbox" name="<?php echo $opt[$i]['name']; ?>" value="1" <?php checked (1,$opt[$i]['value']); ?>> <?php _e("On the settings menu", 'agg-advanced-settings' ) ; echo " (<a href='" . admin_url() . "/options.php" . "'>" . __("Preview",'agg-advanced-settings') . "</a>)" ; ?></td>
</tr>
<!-- option: Include post/page ID's in admin table -->
<tr>
<th scope="row"><i class="fas fa-list-ol" style="color:<?php echo $opt[++$i]['value'] == 1 ? '#000000' : '#808080' ; ?>;"></i>
<label for="<?php echo $opt[$i]['name']; ?>"><?php _e("Include post/page ID's", 'agg-advanced-settings' ); ?></label></th>
<td><input type="checkbox" name="<?php echo $opt[$i]['name']; ?>" value="1" <?php checked (1,$opt[$i]['value']); ?>> <?php _e("In admin tables", 'agg-advanced-settings' ); ?></td>
</tr>
</table>
<!-- tab login -->
    <?php break;
      case 'login': ?>
<table class="form-table" role="presentation">
<!-- option: Disable login by email -->
<tr>
<th scope="row">
<?php if ($opt[$i+1]['value'] == 1 && $opt[$i+2]['value'] == '') { ?>  
<div class="agg-tooltip"><i class="fas fa-at" style="color:<?php echo $opt[++$i]['value'] == 1 ? $colors[2] : '#808080' ; ?>"></i>
<span class="agg-tooltiptext"><?php _e("Works better together with custom errors message", 'agg-advanced-settings' ); ?></span>
</div>
<?php } else { ?>
<i class="fas fa-at" style="color:<?php echo $opt[++$i]['value'] == 1 ? '#000000' : '#808080' ; ?>;"></i>
<?php } ?>
<label for="<?php echo $opt[$i]['name']; ?>"><?php _e("Disable login by email", 'agg-advanced-settings' ); ?></label></th>
<td><input type="checkbox" name="<?php echo $opt[$i]['name']; ?>" value="1" <?php checked (1,$opt[$i]['value']); ?>> <?php _e("Username only", 'agg-advanced-settings' ); ?></td>
</tr>
<!-- option: Custom errors message -->
<tr>
<th scope="row"><i class="fas fa-times-circle" style="color:<?php echo (!empty($opt[++$i]['value'])) ? '#000000' : '#808080' ; ?>;"></i>
<label for="<?php echo $opt[$i]['name']; ?>"><?php _e("Custom errors message", 'agg-advanced-settings' ); ?></label></th>
<td><input type="text" name="<?php echo $opt[$i]['name']; ?>" placeholder="<?php _e("Something is wrong!", 'agg-advanced-settings' ); ?>" value="<?php echo esc_attr($opt[$i]['value']) ?>"> <?php _e("Make your login a bit more secure", 'agg-advanced-settings' ); ?></td>
</tr>
<!-- option: Set login page style -->
<tr>
<th scope="row"><i class="fab fa-css3" style="color:<?php echo (!empty($opt[++$i]['value'])) ? '#000000' : '#808080' ; ?>;"></i>
<label for="theme"><?php _e("Set login page style", 'agg-advanced-settings' ); ?></label></th>
<td>
<select name="<?php echo $opt[$i]['name']; ?>" id="theme">
  <option value=""<?php $opt[$i]['value']=='' ? ' selected="selected"' : ''; ?>><?php _e("Choose theme", 'agg-advanced-settings' ); ?></option>
  <?php
  $agg_as_themes = wp_get_themes();
  foreach( $agg_as_themes as $value) { ?>
    <option value="<?php echo $value->__get('stylesheet') ?>"<?php echo $opt[$i]['value'] == $value->__get('stylesheet') ? ' selected="selected"' : ''; ?>><?php echo $value->__get('name') ?></option>
  <?php
   } ?>
</select>
 <?php _e("Theme", 'agg-advanced-settings' ); ?>
</tr>
<!-- option: Replace WordPress logo -->
<tr>
<th scope="row"><i class="fab fa-wordpress" style="color:<?php echo $opt[++$i]['value'] == 1 ? '#000000' : '#808080' ; ?>;"></i>
<label for="<?php echo $opt[$i]['name']; ?>"><?php _e("Replace WordPress logo", 'agg-advanced-settings' ); ?></label></th>
<?php if (has_custom_logo()) { ?>
<td><input type="checkbox" name="<?php echo $opt[$i]['name']; ?>" value="1" <?php checked (1,$opt[$i]['value']); ?>> <?php _e("With site custom logo and site home link", 'agg-advanced-settings' ); ?></td>
<?php } elseif ($opt[$i]['value'] == 1) { ?>
<td><input type="checkbox" name="<?php echo $opt[$i]['name']; ?>" value="1" <?php checked (1,$opt[$i]['value']); ?>><span style="color:<?php echo $colors[2]; ?>;"> <?php _e("No custom logo was found but the WP logo was removed", 'agg-advanced-settings' ); ?></span></td>
<?php } elseif ($opt[$i]['value'] == 0) { ?>
<td><input type="checkbox" name="<?php echo $opt[$i]['name']; ?>" value="1" <?php checked (1,$opt[$i]['value']); ?>><span style="color:<?php echo $colors[2]; ?>;"> <?php _e("No custom logo was found but the WP logo can be removed", 'agg-advanced-settings' ); ?></span></td>
<?php }?>
</tr>
<!-- option: Replace WordPress title -->
<tr>
<th scope="row"><i class="fab fa-wordpress" style="color:<?php echo $opt[++$i]['value'] == 1 ? '#000000' : '#808080' ; ?>;"></i>
<label for="<?php echo $opt[$i]['name']; ?>"><?php _e("Replace WordPress title", 'agg-advanced-settings' ); ?></label></th>
<td><input type="checkbox" name="<?php echo $opt[$i]['name']; ?>" value="1" <?php checked (1,$opt[$i]['value']); ?>> <?php _e("Remove 'WordPress' expression", 'agg-advanced-settings' ); ?></td>
</tr>
<!-- option: Hide navigation links -->
<tr>
<th scope="row"><i class="fas fa-unlink" style="color:<?php echo $opt[++$i]['value'] == 1 ? '#000000' : '#808080' ; ?>;"></i>
<label for="<?php echo $opt[$i]['name']; ?>"><?php _e("Hide navigation links", 'agg-advanced-settings' ); ?></label></th>
<td><input type="checkbox" name="<?php echo $opt[$i]['name']; ?>" value="1" <?php checked (1,$opt[$i]['value']); ?>> <?php _e("Remove 'Register | Lost your password?' links", 'agg-advanced-settings' ); ?></td>
</tr>
<!-- option: Hide back to home link -->
<tr>
<th scope="row"><i class="fas fa-home" style="color:<?php echo $opt[++$i]['value'] == 1 ? '#000000' : '#808080' ; ?>;"></i>
<label for="<?php echo $opt[$i]['name']; ?>"><?php _e("Hide back to home link", 'agg-advanced-settings' ); ?></label></th>
<td><input type="checkbox" name="<?php echo $opt[$i]['name']; ?>" value="1" <?php checked (1,$opt[$i]['value']); ?>> <?php _e("Remove '← Back to Home' link", 'agg-advanced-settings' ); ?></td>
</tr>
<!-- option: Hide privacy policy link -->
<tr>
<th style="white-space: nowrap;" scope="row"><i class="fas fa-lock" style="color:<?php echo $opt[++$i]['value'] == 1 ? '#000000' : '#808080' ; ?>;"></i>
<label for="<?php echo $opt[$i]['name']; ?>"><?php _e("Hide privacy policy link", 'agg-advanced-settings' ); ?></label></th>
<td><input type="checkbox" name="<?php echo $opt[$i]['name']; ?>" value="1" <?php checked (1,$opt[$i]['value']); ?>> <?php _e("From the footer", 'agg-advanced-settings' ); ?></td>
</tr>
</table>
<!-- tab security -->
    <?php break;
      case 'security': ?>
<table class="form-table" role="presentation">
<!-- option: Reject malicious URL requests -->
<tr>
<th scope="row"><i class="fas fa-virus" style="color:<?php echo $opt[++$i]['value'] == 1 ? '#000000' : '#808080' ; ?>;"></i>
<label for="<?php echo $opt[$i]['name']; ?>"><?php _e("Reject malicious requests", 'agg-advanced-settings' ); ?></label></th>
<td><input type="checkbox" name="<?php echo $opt[$i]['name']; ?>" value="1" <?php checked (1,$opt[$i]['value']); ?>> <?php _e("Block suspicious URL requests", 'agg-advanced-settings' ); ?></td>
</tr>
<!-- option: Remove WordPress version number -->
<tr>
<th scope="row"><i class="fas fa-code-branch" style="color:<?php echo $opt[++$i]['value'] == 1 ? '#000000' : '#808080' ; ?>;"></i>
<label for="<?php echo $opt[+$i]['name']; ?>"><?php _e("Remove WordPress version", 'agg-advanced-settings' ); ?></label></th>
<td><input type="checkbox" name="<?php echo $opt[$i]['name']; ?>" value="1" <?php checked (1,$opt[$i]['value']); ?>> <?php _e("From the html head source", 'agg-advanced-settings' ); ?></td>
</tr>
<!-- option: Disable XML-RPC -->
<tr>
<th scope="row"><i class="fas fa-file-code" style="color:<?php echo $opt[++$i]['value'] == 1 ? '#000000' : '#808080' ; ?>;"></i>
<label for="<?php echo $opt[$i]['name']; ?>"><?php _e("Disable XML-RPC", 'agg-advanced-settings' ); ?></label></th>
<td><input type="checkbox" name="<?php echo $opt[$i]['name']; ?>" value="1" <?php checked (1,$opt[$i]['value']); ?>> <?php _e("Block weblog clients, mobile app, IFTTT, etc", 'agg-advanced-settings' ); ?></td>
</tr>    
<!-- option: Disable the file editor -->
<tr>
<th scope="row"><i class="fas fa-edit" style="color:<?php echo $opt[++$i]['value'] == 1 ? '#000000' : '#808080' ; ?>;"></i>
<label for="<?php echo $opt[$i]['name']; ?>"><?php _e("Disable the file editor", 'agg-advanced-settings' ); ?></label></th>
<td><input type="checkbox" name="<?php echo $opt[$i]['name']; ?>" value="1" <?php checked (1,$opt[$i]['value']); ?>> <?php _e("For both themes and plugins (admin menu)", 'agg-advanced-settings' ); ?></td>
</tr>    
<!-- option: HTTPS with Non-Secure Media -->
<tr>
<th scope="row"><i class="fab fa-expeditedssl" style="color:<?php echo $opt[++$i]['value'] == 1 ? '#000000' : '#808080' ; ?>;"></i>
<label for="<?php echo $opt[$i]['name']; ?>"><?php _e("HTTPS with Non-Secure Media", 'agg-advanced-settings' ); ?></label></th>
<td><input type="checkbox" name="<?php echo $opt[$i]['name']; ?>" value="1" <?php checked (1,$opt[$i]['value']); ?>> <?php _e("Removes the message that some items on the page aren’t delivered securely", 'agg-advanced-settings' ); ?></td>
</tr>
<!-- option: Disable links from user comments -->
<tr>
<th style="white-space: nowrap;" scope="row"><i class="fas fa-unlink" style="color:<?php echo $opt[++$i]['value'] == 1 ? '#000000' : '#808080' ; ?>;"></i>
<label for="<?php echo $opt[$i]['name']; ?>"><?php _e("Disable links from user comments", 'agg-advanced-settings' ); ?></label></th>
<td><input type="checkbox" name="<?php echo $opt[$i]['name']; ?>" value="1" <?php checked (1,$opt[$i]['value']); ?>> <?php _e("Prevents spamming, links in comments stay as plain text on the page.", 'agg-advanced-settings' ); ?></td>
</tr>
</table>
<!-- tab performance -->
    <?php break;
      case 'performance': ?>
<table class="form-table" role="presentation">
<!-- option: Disable emoji -->
<tr>
<th style="white-space: nowrap;" scope="row">
<?php if ($opt[++$i]['value'] == 1) { ?>
<div class="agg-tooltip">
<i class="fas fa-smile" style="color:<?php echo $colors[2]; ?>"></i>
<span class="agg-tooltiptext"><?php _e("Emoticons and emojis will continue to work in browsers that have integrated support for them. This option simply removes the additional JavaScript file that is used to add support for emojis in older browsers.", 'agg-advanced-settings' ); ?></span>
</div> 
<?php } else { ?>
<i class="fas fa-smile" style="color:<?php echo $opt[$i]['value'] == 1 ? '#000000' : '#808080' ; ?>;"></i>
<?php } ?>
<label for="<?php echo $opt[$i]['name']; ?>"><?php _e("Disable emoji support", 'agg-advanced-settings' ); ?></label></th>
<td><input type="checkbox" name="<?php echo $opt[$i]['name']; ?>" value="1" <?php checked (1,$opt[$i]['value']); ?>> <?php _e("From all content and prevent DNS lookup from TinyMCE editor", 'agg-advanced-settings' ) ; ?></td>
</tr>
<!-- option: Disable embeds -->
<tr>
<th scope="row"><i class="fas fa-video-slash" style="color:<?php echo $opt[++$i]['value'] == 1 ? '#000000' : '#808080' ; ?>;"></i>
<label for="<?php echo $opt[$i]['name']; ?>"><?php _e("Disable embeds", 'agg-advanced-settings' ); ?></label></th>
<td><input type="checkbox" name="<?php echo $opt[$i]['name']; ?>" value="1" <?php checked (1,$opt[$i]['value']); ?>> <?php _e("Deactivate easily embed videos, images, tweets, etc.", 'agg-advanced-settings' ); ?></td>
</tr>    
</table>
<!-- tab media -->
    <?php break;
      case 'media': ?>
<table class="form-table" role="presentation">
<!-- option: Disable JPEG compression -->
<tr>
<th scope="row"><i class="fas fa-file-image" style="color:<?php echo $opt[++$i]['value'] == 1 ? '#000000' : '#808080' ; ?>;"></i>
<label for="<?php echo $opt[$i]['name']; ?>"><?php _e("Disable JPEG compression", 'agg-advanced-settings' ); ?></label></th>
<td><input type="checkbox" name="<?php echo $opt[$i]['name']; ?>" value="1" <?php checked (1,$opt[$i]['value']); ?>> <?php _e("Keep original image size", 'agg-advanced-settings' ) ; ?></td>
</tr>
<!-- option: Allow svg files in media library -->
<tr>
<th style="white-space: nowrap;" scope="row"><i class="fas fa-image" style="color:<?php echo $opt[++$i]['value'] == 1 ? '#000000' : '#808080' ; ?>;"></i>
<label for="<?php echo $opt[$i]['name']; ?>"><?php _e("Allow SVG files in media library", 'agg-advanced-settings' ); ?></label></th>
<td><input type="checkbox" name="<?php echo $opt[$i]['name']; ?>" value="1" <?php checked (1,$opt[$i]['value']); ?>> <?php _e("Through uploader", 'agg-advanced-settings' ) ; ?></td>
</tr>
<!-- option: Change default add media settings -->
<tr>
<th style="white-space: nowrap;" scope="row"><i class="fas fa-sliders-h" style="color:<?php echo $opt[++$i]['value'] == 1 ? '#000000' : '#808080' ; ?>;"></i>
<label for="<?php echo $opt[$i]['name']; ?>"><?php _e("Change default add media settings", 'agg-advanced-settings' ); ?></label></th>
<td><input type="checkbox" name="<?php echo $opt[$i]['name']; ?>" value="1" <?php checked (1,$opt[$i]['value']); ?>> <?php _e("‘align center’, ‘link type none’ and ‘default size full’", 'agg-advanced-settings' ) ; ?></td>
</tr>

</table>
    <?php break;
    endswitch; ?>
</div>
<!-- end tab content -->
<p class="submit">
<button type="submit" name="Submit" class="button-primary"><i class="fas fa-save"></i> <?php esc_attr_e('Save Changes') ?></button>
</p>
</form>
</div>

<?php
} //end function agg_as_options
/* EOF */
?>