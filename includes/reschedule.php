<?php
namespace EcoMode\EcoModeWP;

/**
 * Dummy class to show the idea of autoloading.
 */
class Reschedule {

	private static array $registered_hooks;

	public static function add( string $action, $recurrence ): bool {
		self::$registered_hooks[ $action ] = $recurrence;

		return ! empty( self::$registered_hooks[ $action ] );
	}

	/**
	 * Removes all registered hooks
	 *
	 * @return bool
	 */
	public static function clear_all(): bool {
		foreach ( self::$registered_hooks as $action => $recurrence ) {
			wp_clear_scheduled_hook( $action );
		}

		return true;
	}

	/**
	 * Adds the filter to alter the recurrence of the event.
	 *
	 * @return bool
	 */
	public static function add_filter(): bool {
		add_filter( 'schedule_event', [ __CLASS__, 'filter_events' ] );

		return true;
	}

	/**
	 * Modifies an event before it is scheduled.
	 *
	 * @param \stdClass|false $event {
	 *     An object containing an event's data, or boolean false to prevent the event from being scheduled.
	 *
	 *     @type string       $hook      Action hook to execute when the event is run.
	 *     @type int          $timestamp Unix timestamp (UTC) for when to next run the event.
	 *     @type string|false $schedule  How often the event should subsequently recur.
	 *     @type array        $args      Array containing each separate argument to pass to the hook's callback function.
	 *     @type int          $interval  The interval time in seconds for the schedule. Only present for recurring events.
	 * }
	 */
	public static function filter_events( $event ) {
		if ( isset( self::$registered_hooks[ $event->hook ] ) ) {
			$event->schedule = self::$registered_hooks[ $event->hook ];
		}

		return $event;
	}
}
