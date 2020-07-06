<?php

// create custom plugin settings menu
function agg_as_menu() {
	add_options_page( 'Aggregator Options', __("Advanced Settings",'agg-advanced-settings'), 'manage_options', 'aggregator-options', 'agg_as_options');
}
add_action( 'admin_menu', 'agg_as_menu' );

function agg_as_options() {
	
	  //must check that the user has the required capability 
    if (!current_user_can('manage_options'))
    {
      wp_die( __( _e('You do not have sufficient permissions to access this page.', 'agg-advanced-settings' )));
    }

    // variables for the fields and options names 
    $opt_form_name = 'form1';
    $hidden_field_name = 'agg_submit_hidden';
    $opt = array (
      array ('name' => 'agg_hide_powered', 'value' => 0),
      array ('name' => 'agg_hide_admin_bar', 'value' => 0),
      array ('name' => 'agg_set_login_style', 'value' => ''),
      array ('name' => 'agg_show_site_logo', 'value' => 0),
      array ('name' => 'agg_remove_title', 'value' => 0)
    );
    
    // Read in existing option value from database
    foreach($opt as $key => $value) {
      $opt[$key]['value'] = get_option($value['name']);
    }
    unset($value);
    
    // update option 'agg_set_login_style' for backward compatibility
    $i = 2;
    if ($opt[$i]['value'] == "0") {
      update_option( $opt[$i]['name'], '' );
      $opt[$i]['value'] = '';
    } else if ($opt[$i]['value'] == "1") {
      update_option( $opt[$i]['name'], get_template() );
      $opt[$i]['value'] = get_template();
    }
    
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
<h1><?php _e("Advanced Settings", 'agg-advanced-settings' ) ?></h1>

<!-- settings form -->
<form name="<?php echo $opt_form_name ?>" method="post" action="">

<?php wp_nonce_field('agg-as-update-options_' . $opt_form_name); ?>

<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
<table class="form-table" role="presentation">
<tr>
<th scope="row"><label><h2><?php _e("General Options", 'agg-advanced-settings' ); ?></h2></label></th>
</tr>
<tr>
<th style="white-space: nowrap;" scope="row"><label for="<?php echo $opt[0]['name']; ?>"><?php _e("Try to hide 'Powered by WordPress' ", 'agg-advanced-settings' ); ?></label></th>
<td><input type="checkbox" name="<?php echo $opt[0]['name']; ?>" value="1" <?php checked (1,$opt[0]['value']); ?>><?php _e(" From the footer", 'agg-advanced-settings' ); ?></td>
</tr>
<tr>
<th scope="row"><label for="<?php echo $opt[1]['name']; ?>"><?php _e("Hide admin bar (and profile)", 'agg-advanced-settings' ); ?></label></th>
<td><input type="checkbox" name="<?php echo $opt[1]['name']; ?>" value="1" <?php checked (1,$opt[1]['value']); ?>><?php _e(" From non-admin users", 'agg-advanced-settings' ); ?></td>
</tr>
<tr>
<th scope="row"><label><h2><?php _e("Login Options", 'agg-advanced-settings' ); ?></h2></label></th>
</tr>
<tr>
<th scope="row"><label for="theme"><?php _e("Set login page style", 'agg-advanced-settings' ); ?></label></th>
<td>
<select name="<?php echo $opt[2]['name']; ?>" id="theme">
  <option value=""<?php $opt[2]['value']=='' ? ' selected="selected"' : ''; ?>><?php _e("Choose theme", 'agg-advanced-settings' ); ?></option>
  <?php
  $agg_as_themes = wp_get_themes();
  foreach( $agg_as_themes as $value) { ?>
    <option value="<?php echo $value->__get('stylesheet') ?>"<?php echo $opt[2]['value'] == $value->__get('stylesheet') ? ' selected="selected"' : ''; ?>><?php echo $value->__get('name') ?></option>
  <?php
   } ?>
</select>
</tr>
<tr>
<th scope="row"><label for="<?php echo $opt[3]['name']; ?>"><?php _e("Replace WP logo in login page", 'agg-advanced-settings' ); ?></label></th>
<?php if (has_custom_logo()) { ?>
<td><input type="checkbox" name="<?php echo $opt[3]['name']; ?>" value="1" <?php checked (1,$opt[3]['value']); ?>><?php _e(" With site custom logo and site home link", 'agg-advanced-settings' ); ?></td>
<?php } elseif ($opt[3]['value'] == 1) { ?>
<td><input type="checkbox" name="<?php echo $opt[3]['name']; ?>" value="1" <?php checked (1,$opt[3]['value']); ?>><span style="color: #0000CD;"><?php _e(" No custom logo was found but the WP logo was removed", 'agg-advanced-settings' ); ?></span></td>
<?php } elseif ($opt[3]['value'] == 0) { ?>
<td><input type="checkbox" name="<?php echo $opt[3]['name']; ?>" value="1" <?php checked (1,$opt[3]['value']); ?>><span style="color: #0000CD;"><?php _e(" No custom logo was found but the WP logo can be removed", 'agg-advanced-settings' ); ?></span></td>
<?php }?>
</tr>
<tr>
<th scope="row"><label for="<?php echo $opt[4]['name']; ?>"><?php _e("Replace WP title in login page", 'agg-advanced-settings' ); ?></label></th>
<td><input type="checkbox" name="<?php echo $opt[4]['name']; ?>" value="1" <?php checked (1,$opt[4]['value']); ?>><?php _e(" Remove 'WordPress' expression", 'agg-advanced-settings' ); ?></td>
</tr>
</table>
<hr />
<p class="submit">
<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" />
</p>
</form>
</div>

<?php
}
/* EOF */
?>