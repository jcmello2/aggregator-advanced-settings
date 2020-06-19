<?php

// Register and load the widget
function agg_load_widget() {
    register_widget( 'agg_widget_meta' );
}
add_action( 'widgets_init', 'agg_load_widget' );

// Register link without admin page option
function agg_register( $before = '<li>', $after = '</li>', $echo = true ) {
	if ( ! is_user_logged_in() ) {
		if ( get_option( 'users_can_register' ) ) {
			$link = $before . '<a href="' . esc_url( wp_registration_url() ) . '">' . __( 'Register' ) . '</a>' . $after;
		} else {
			$link = '';
		}
	} else {
		$link = '';
	}

	/**
	 * Filters the HTML link to the Registration or Admin page.
	 *
	 * Users are sent to the admin page if logged-in, or the registration page
	 * if enabled and logged-out.
	 *
	 * @since 1.5.0
	 *
	 * @param string $link The HTML code for the link to the Registration or Admin page.
	 */
	$link = apply_filters( 'register', $link );

	if ( $echo ) {
		echo $link;
	} else {
		return $link;
	}
}

// Creating the widget 
class agg_widget_meta extends WP_Widget {
 
function __construct() {
parent::__construct(
 
// Base ID of your widget
'agg_widget_meta', 
 
// Widget name will appear in UI
__('Aggregator Logint', 'agg_widget_domain'), 
 
// Widget description
array( 'description' => __( 'Register, Login, Logout', 'agg_widget_domain' ), ) 
);
}
 
// Creating widget front-end
 
public function widget( $args, $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Aggregator Meta' );

		/** This filter is documented in wp-includes/widgets/class-wp-widget-pages.php */
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );
		
		/** before and after widget arguments are defined by themes */
		echo $args['before_widget'];
		
		if ( $title ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
		?>
			<ul>
			<?php agg_register(); ?>
			<li><?php wp_loginout(); ?></li>
			</ul>
			<?php

			echo $args['after_widget'];
	}
         
// Widget Backend 
public function form( $instance ) {
if ( isset( $instance[ 'title' ] ) ) {
$title = $instance[ 'title' ];
}
else {
$title = __( 'New title', 'agg_widget_domain' );
}
// Widget admin form
?>
<p>
<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
</p>
<?php 
}
     
// Updating widget replacing old instances with new
public function update( $new_instance, $old_instance ) {
$instance = array();
$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
return $instance;
}

} // Class agg_widget_meta ends here