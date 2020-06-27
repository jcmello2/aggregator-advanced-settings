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
    $opt_name_1 = 'agg_hide_powered';
    $opt_name_2 = 'agg_hide_admin_bar';
    $opt_name_3 = 'agg_set_login_style';
    $opt_name_4 = 'agg_show_site_logo';
    $opt_name_5 = 'agg_remove_title';
    
    // Read in existing option value from database
    $opt_val_1 = get_option( $opt_name_1 );
    $opt_val_2 = get_option( $opt_name_2 );
    $opt_val_3 = get_option( $opt_name_3 );
    $opt_val_4 = get_option( $opt_name_4 );
    $opt_val_5 = get_option( $opt_name_5 );
    
    // update $opt_3 for backward compatibility
    if ($opt_val_3 == '0') {
      update_option( $opt_name_3, '' );
      $opt_val_3 = '';
    } else if ($opt_val_3 == '1') {
      update_option( $opt_name_3, get_stylesheet() );
      $opt_val_3 = get_template();
    }
    
    // See if the user has posted us some information
    // If they did, this hidden field will be set to 'Y'
    if( isset($_POST[ $hidden_field_name ]) && $_POST[ $hidden_field_name ] == 'Y' ) {
        
        // Check nonce    
        check_admin_referer('agg-as-update-options_' . $opt_form_name);
        
        // Read their posted value
        (isset($_POST[ $opt_name_1 ]))?$opt_val_1 = 1:$opt_val_1 = 0;
        (isset($_POST[ $opt_name_2 ]))?$opt_val_2 = 1:$opt_val_2 = 0;    
        $opt_val_3 = $_POST[ $opt_name_3 ];
        (isset($_POST[ $opt_name_4 ]))?$opt_val_4 = 1:$opt_val_4 = 0;
        (isset($_POST[ $opt_name_5 ]))?$opt_val_5 = 1:$opt_val_5 = 0;
        
        // Save the posted value in the database
        update_option( $opt_name_1, $opt_val_1 );
        update_option( $opt_name_2, $opt_val_2 );
        update_option( $opt_name_3, $opt_val_3 );
        update_option( $opt_name_4, $opt_val_4 );
        update_option( $opt_name_5, $opt_val_5 );

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
<th style="white-space: nowrap;" scope="row"><label for="<?php echo $opt_name_1; ?>"><?php _e("Try to hide 'Powered by WordPress' ", 'agg-advanced-settings' ); ?></label></th>
<td><input type="checkbox" name="<?php echo $opt_name_1; ?>" value="1" <?php checked ('1',$opt_val_1); ?>><?php _e(" From the footer", 'agg-advanced-settings' ); ?></td>
</tr>
<tr>
<th scope="row"><label for="<?php echo $opt_name_2; ?>"><?php _e("Hide admin bar (and profile)", 'agg-advanced-settings' ); ?></label></th>
<td><input type="checkbox" name="<?php echo $opt_name_2; ?>" value="1" <?php checked ('1',$opt_val_2); ?>><?php _e(" From non-admin users", 'agg-advanced-settings' ); ?></td>
</tr>
<tr>
<th scope="row"><label><h2><?php _e("Login Options", 'agg-advanced-settings' ); ?></h2></label></th>
</tr>
<tr>
<th scope="row"><label for="theme"><?php _e("Set login page style", 'agg-advanced-settings' ); ?></label></th>
<td>
<select name="<?php echo $opt_name_3; ?>" id="theme">
  <option value=""<?= $opt_val_3=='' ? ' selected="selected"' : ''; ?>><?php _e("Choose theme", 'agg-advanced-settings' ); ?></option>
  <?php
  
  $agg_as_themes = wp_get_themes();
  
  foreach( $agg_as_themes as $value) { ?>
    <option value="<?php echo $value->__get('stylesheet') ?>"<?=$opt_val_3 == $value->__get('stylesheet') ? ' selected="selected"' : ''; ?>><?php echo $value->__get('name') ?></option>
  <?php
   } ?>
</select>
</td>
</tr>
<tr>
<th scope="row"><label for="<?php echo $opt_name_4; ?>"><?php _e("Replace WP logo in login page", 'agg-advanced-settings' ); ?></label></th>
<td><input type="checkbox" name="<?php echo $opt_name_4; ?>" value="1" <?php checked ('1',$opt_val_4); ?>><?php _e(" With site custom logo and site home link", 'agg-advanced-settings' ); ?></td>
</tr>
<tr>
<th scope="row"><label for="<?php echo $opt_name_5; ?>"><?php _e("Replace WP title in login page", 'agg-advanced-settings' ); ?></label></th>
<td><input type="checkbox" name="<?php echo $opt_name_5; ?>" value="1" <?php checked ('1',$opt_val_5); ?>><?php _e(" Remove 'WordPress' expression", 'agg-advanced-settings' ); ?></td>
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
?>