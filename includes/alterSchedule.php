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
	 * @param string $action    The name of the action to reschedule.
	 * @param string $recurrence The recurrence, like 'daily', 'hourly' etc.
	 *
	 * @return bool
	 */
	public static function reschedule(string $action, string $recurrence ): bool {
		self::$rescheduled_hooks[ $action ] = $recurrence;

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
	 * Updates all changed hooks.
	 *
	 * @return bool
	 */
	public static function update(): bool {
		$last_schedules = (array) get_option( 'eco_mode_wp_last_schedules', [] );

		if ( empty( $last_schedules ) ) {
			$delta['rescheduled_hooks'] = self::$rescheduled_hooks;
			$delta['disabled_hooks'] = self::$disabled_hooks;
		} else {
			$delta['rescheduled_hooks'] = array_merge(
				array_diff_assoc( $last_schedules['rescheduled_hooks'], self::$rescheduled_hooks ),
				array_diff_assoc( self::$rescheduled_hooks, $last_schedules['rescheduled_hooks'] )
			);
			$delta['disabled_hooks'] = array_diff( $last_schedules['disabled_hooks'], self::$disabled_hooks );
		}

		foreach ( $delta['rescheduled_hooks'] as $action => $recurrence ) {
			wp_clear_scheduled_hook( $action );
		}

		foreach ( $delta['disabled_hooks'] as $action ) {
			wp_clear_scheduled_hook( $action );
		}

		$schedules['rescheduled_hooks'] = self::$rescheduled_hooks;
		$schedules['disabled_hooks'] = self::$disabled_hooks;

		update_option( 'eco_mode_wp_last_schedules', $schedules );

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
			self::ping_altered_schedule( $event, self::$rescheduled_hooks[ $event->hook ] );
			$event->schedule = self::$rescheduled_hooks[ $event->hook ];
		}

		return $event;
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
		 * Notifies about the altering of an event.
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
