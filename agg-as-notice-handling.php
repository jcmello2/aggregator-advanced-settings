<?php

/**
 * Agg_Notice_Handling
 */
class Agg_Notice_Handling {
 
     // Constructor
    public function __construct() {
        $this->plugin_notice_dismissed();
        $this->plugin_notice();
    }

    public function plugin_notice() {
        $user_id = get_current_user_id();
        global $wp;
        if ( ! get_user_meta( $user_id, 'agg_as_notice_dismissed' ) ) {
            echo '<div class="notice notice-info" style="padding-right: 38px;position:relative" ><p>' . __("Missing options? Please send suggestions to",'agg-advanced-settings') .
            ' <a href="mailto:migu.mello@gmail.com?subject=AAS%20plugin%20suggestion">' . __("Plugin Author",'agg-advanced-settings') . '</a>.'; ?>
            <button type="button" class="notice-dismiss" onclick="location.href='<?php echo add_query_arg( $wp->query_vars, home_url( $wp->request ) )?>&notice-dismissed'">
                <span class="screen-reader-text">Descartar este aviso.</span>
            </button></p></div>
            <?php
        }
    }

    public function plugin_notice_dismissed() {
        $user_id = get_current_user_id();
        if ( isset( $_GET['notice-dismissed'] ) ) {
            add_user_meta( $user_id, 'agg_as_notice_dismissed', 'true', true );
        }
    }

} // End class Agg_Notice_Handling

/* EOF */
?>