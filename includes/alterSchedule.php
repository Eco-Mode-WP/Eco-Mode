<?php
namespace EcoMode\EcoModeWP;

/**
 * Dummy class to show the idea of autoloading.
 */
class Alter_Schedule {
	/**
	 * Array of hooks that are to be rescheduled.
	 *
	 * @var array
	 */
	private static array $rescheduled_hooks = [];

	/**
	 * Array of hooks that shall be disabled altogether.
	 *
	 * @var array
	 */
	private static array $disabled_hooks = [];

	/**
	 * Registers a reschedule for a certain hook.
	 *
	 * @see strtotime()
	 *
	 * @param string $action The name of the action to reschedule.
	 * @param array $args {
	 *     An object containing an event's data, or boolean false to prevent the event from being scheduled.
	 *
	 *     @type string $recurrence     The recurrence, like 'daily', 'hourly' etc.
	 *     @type string $start          A strtotime() compatible string which defines when it shall be excecuted for the first time. Optional
	 * }
	 *
	 * @return bool
	 */
	public static function reschedule(string $action, array $args ): bool {
		if ( empty( $args['recurrence'] ) ) {
			return false;
		}

		self::$rescheduled_hooks[ $action ] = $args;

		return ! empty( self::$rescheduled_hooks[ $action ] );
	}

	/**
	 * Registers a hook that shall not be scheduled.
	 *
	 * @param string $action The name of the action to reschedule.
	 * @return bool
	 */
	public static function disable( string $action ): bool {
		if ( in_array( $action, self::$disabled_hooks, true ) ) {
			return true;
		}

		self::$disabled_hooks[] = $action;

		return true;
	}

	/**
	 * Removes all registered hooks.
	 *
	 * @return bool
	 */
	public static function clear_all(): bool {
		foreach ( self::$rescheduled_hooks as $action => $recurrence ) {
			wp_clear_scheduled_hook( $action );
		}

		foreach ( self::$disabled_hooks as $action ) {
			wp_clear_scheduled_hook( $action );
		}

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
	public static function filter_add_events( $event ) {
		// Prevent adding this hook.
		if ( in_array( $event, self::$disabled_hooks, true ) ) {
			self::ping_altered_schedule( $event, 'disabled' );
			return false;
		}

		if ( ! is_object( $event ) ) {
			return $event;
		}

		// Reschedule.
		if ( isset( self::$rescheduled_hooks[ $event->hook ] ) ) {
			self::ping_altered_schedule( $event, self::$rescheduled_hooks[ $event->hook ]['recurrence'] );
			$event->schedule = self::$rescheduled_hooks[ $event->hook ]['recurrence'];

			if ( isset( self::$rescheduled_hooks[ $event->hook ]['start'] ) && is_string( self::$rescheduled_hooks[ $event->hook ]['start'] ) ) {
				$new_start = strtotime( self::$rescheduled_hooks[ $event->hook ]['start'] );
				if ( is_int( $new_start ) && $new_start > 0 ) {
					$event->timestamp = $new_start;
				}
			}
		}

		return $event;
	}

	/**
	 * Injects a fake result for even schedule for disabled events.
	 *
	 * @param null|false|object $pre  Value to return instead. Default null to continue retrieving the event.
	 * @param string            $hook Action hook of the event.
	 * @param array             $args Array containing each separate argument to pass to the hook's callback function.
	 *                                Although not passed to a callback, these arguments are used to uniquely identify
	 *                                the event.
	 * @param int|null  $timestamp Unix timestamp (UTC) of the event. Null to retrieve next scheduled event.
	 *
	 * @return \stdClass
	 */
	public static function filter_get_scheduled( $pre, string $hook, array $args, ?int $timestamp ) {
		if ( in_array( $hook, self::$disabled_hooks, true ) ) {
			$pre = new \stdClass();
			$pre->hook = $hook;
			$pre->schedule = false;
			$pre->args = $args;
			$pre->timestamp = PHP_INT_MAX;
		}

		return $pre;
	}

	/**
	 * Notifies about the altering of an event..
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
	 * @param string $new_recurrence The new recurrence.
	 */
	private static function ping_altered_schedule( $event, string $new_recurrence ): void {
		/**
		 * Notifies about the altering of an event..
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
		 * @param string $new_recurrence The new recurrence, 'disabled' if it is to be disabled.
		 */
		do_action( 'eco_mode_wp_altered_schedule', $event, $new_recurrence );
	}
}
