<?php
/*
Plugin Name: Live Enployment
Plugin URI: 
Description: My enploymentstatus
Version: 1.0
Author: Goan
Author URI: https:
License: GPL2
*/

class My_Custom_Widget extends WP_Widget {

    public function __construct() {
        // Define the constructor

        $options = array(
            'classname' => 'live_employment',
            'description' => 'My employmentstatus',
        );

        parent::__construct(
            'live_employment', 'My employmentstatus', $options
        );
    }

    public function widget( $args, $instance ) {
        
        $state = apply_filters('widget_state', $instance['state']);

        echo $args['before_widget'];

        echo $args['before_title'] . apply_filters( 'widget_title', 'State of Employment' ) . $args['after_title'];

        echo $state;

        echo $args['after_widget'];
    }
    
    public function form( $instance ) {
      $state = ! empty( $instance['state'] ) ? $instance['state'] : ''; ?>
      <p>
        <label for="<?php echo $this->get_field_id( 'state' ); ?>">State:</label>
        <input type="text" id="<?php echo $this->get_field_id( 'state' ); ?>" name="<?php echo $this->get_field_name( 'state' ); ?>" value="<?php echo esc_attr( $state ); ?>" />
      </p><?php 
    }
    
    public function update( $new_instance, $old_instance ) {
      $instance = $old_instance;
      $instance[ 'state' ] = strip_tags( $new_instance[ 'state' ] );
      return $instance;
    }
}

// Register the widget
function my_register_custom_widget() {
    register_widget( 'My_Custom_Widget' );
}
add_action( 'widgets_init', 'my_register_custom_widget' );



?>