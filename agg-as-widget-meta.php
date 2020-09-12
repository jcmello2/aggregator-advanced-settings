<?php

// Register and load the widget
function agg_as_load_widget() {
    register_widget( 'agg_widget_meta' );
}
add_action( 'widgets_init', 'agg_as_load_widget' );

// Register link without admin page option
function agg_as_register( $before = '<li>', $after = '</li>', $echo = true ) {
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
	
	$widget_ops = array(
				'classname'                   => 'wagg_widget_meta',
				'description'                 => __( 'Register, Login, Logout', 'agg-advanced-settings' ),
				'customize_selective_refresh' => true,
			);
	
	parent::__construct(
	 
	// Base ID of your widget
	'agg_widget_meta', 
	 
	// Widget name will appear in UI
	__('Aggregator Login', 'agg-advanced-settings'), 
	 
	$widget_ops
	
	);
	}
	 
	// Creating widget front-end
	 
	public function widget( $args, $instance ) {
			$title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Aggregator Meta' );
	
			/** This filter is documented in wp-includes/widgets/class-wp-widget-pages.php */
			$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );
			
			/**@since 1.1.1 */
			$privacy = ! empty( $instance['privacy'] ) ? '1' : '0';
			
			/** before and after widget arguments are defined by themes */
			echo $args['before_widget'];
			
			if ( $title ) {
				echo $args['before_title'] . $title . $args['after_title'];
			}
			?>
				<ul>
				<?php agg_as_register(); ?>
				<li><?php wp_loginout(); ?></li>
				<?php if ( $privacy && get_privacy_policy_url() !== "" ) { ?>
				<li><a href="<?php echo esc_url( get_privacy_policy_url() ) ?>"><?php _e( "Privacy Policy", 'agg-advanced-settings' ) ?></a></li>
				<?php } ?>
				</ul>
				<?php
	
				echo $args['after_widget'];
		}
	         
	// Widget Backend 
	public function form( $instance ) {
		$instance = wp_parse_args(
			(array) $instance,
			array(
				'title'    => '',
				'privacy'    => 0,
			)
		);
		if ( isset( $instance[ 'title' ] ) ) {
		$title = $instance[ 'title' ];
		}
		else {
		$title = __( 'New title', 'agg-advanced-settings' );
		}
		// Widget admin form
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
		<input class="checkbox" type="checkbox"<?php checked( $instance['privacy'] ); ?> id="<?php echo $this->get_field_id( 'privacy' ); ?>" name="<?php echo $this->get_field_name( 'privacy' ); ?>" /> <label for="<?php echo $this->get_field_id( 'privacy' ); ?>"><?php _e( 'Display privacy policy link', 'agg-advanced-settings' ); ?></label>
		</p>
	<?php 
	}
	     
	// Updating widget replacing old instances with new
	public function update( $new_instance, $old_instance ) {
		$instance             = $old_instance;
		$new_instance         = wp_parse_args(
			(array) $new_instance,
			array(
				'title'    => '',
				'privacy'  => 0,
			)
		);
		$instance['title']    = sanitize_text_field( $new_instance['title'] );
		$instance['privacy']    = $new_instance['privacy'] ? 1 : 0;
		
		return $instance;
	}

} // Class agg_widget_meta ends here