Repo for the Eco-Mode project: https://www.cloudfest.com/eco-mode-reduce-outgoing-network-traffic-of-your-wordpress-server


## Setup

Run `composer install` to generate the autoloader.

## Reschedule

### Public API

To reschedule a scheduled event, just call the following line, and replace 'daily' with your desired recurrence. This is an open API and can be used at you.
This needs to be called prior to the actual register hook.

```\EcoMode\EcoModeWP\Alter_Schedule::reschedule( 'action_name', 'daily' )```

```
add_action(
	'plugins_loaded',
	function () {
		\EcoMode\EcoModeWP\Alter_Schedule::reschedule( 'wp_https_detection', 'daily' );
	},
	0
);
```
