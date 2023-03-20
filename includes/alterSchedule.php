<?php
namespace EcoMode\EcoModeWP;

/**
 * Dummy class to show the idea of autoloading.
 */
class Alter_Schedule {
    public const OPTION_NAME = 'eco_mode_registered_alteration';

    public static function register_alteration( $name, $condition, $scheduled_action, $schedule, $prevented_requests, $request_url ) {
        $registered_alternation = get_site_option( self::OPTION_NAME );
        
        if ( $condition ) {
            $scheduled_event = wp_get_scheduled_event( $scheduled_action );
            
			if ( $schedule === false && ! empty( $scheduled_event ) ) {
				wp_clear_scheduled_hook( $scheduled_action );
			}
            else if ( $schedule !== $scheduled_event->schedule ) {
                wp_schedule_event( $scheduled_event->timestamp, $schedule, $scheduled_action ); 
            }
        
            if ( ! isset( $registered_alternation[ $name ] ) ) {
                self::set_option( $name, array(
                    'scheduled_action'   => $scheduled_action,
                    'prevented_requests' => $prevented_requests,
                    'request_url'        => $request_url,
                ) );
            }
        }

		// wp_clear_scheduled_hook( $name );
        
        if ( ! $condition && isset( $registered_alternation[ $name ] ) ) {
            self::remove_option( $name );
			wp_clear_scheduled_hook( $scheduled_action );
        }    
    } 
	
	/**
	 * Gets the options array.
	 *
	 * @return array
	 */
	public static function get_options(): array {
 		return get_site_option( self::OPTION_NAME, [] );
	}

    private static function set_option( $name, array $args ) {
        $option = get_site_option( self::OPTION_NAME, array() );

        $option[ $name ] = $args;

        update_site_option( self::OPTION_NAME, $option );    
    }
    
    private static function is_option_set( $name ) {
        $option = get_site_option( self::OPTION_NAME, array() );
        
        return isset( $option[ $name ] );
    }
    
    private static function remove_option( $name ) {
        $option = get_site_option( self::OPTION_NAME, array() );
        
        $default_args = array(
            'scheduled_action'   => '',
            'prevented_requests' => '',
            'request_url'        => '',
        );
        
        if ( isset( $option[ $name ] ) ) {
            unset( $option[ $name ] );
            update_site_option( self::OPTION_NAME, $option );
        }
    }
}