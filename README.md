# Eco-Mode
The Eco-Mode plugin reduces the carbon footprint of your WordPress website by intelligently preventing unnecessary web-requests and background tasks.
Out of the box it:
- Reduces the frequency of WordPress version check requests.
- Disables the WP news dashboard widget.
- Reduces the frequency of the https detection.
- Reduces the frequency of PHP and browser version checks.

## Setup

Run `composer install` to generate the autoloader.

Run `npm install` to install dependencies.

Run `npm run start` to run the plugin dashboard settings.

Run `composer cs` to check the CS of the entire codebase. Prefix this with a file path for a specific file.

Run `composer fix-cs` to fix the automatically fixed CS of the entire codebase. Prefix this with a file path for a specific file.

Run `composer test` to run the unit tests.

Run `composer phpstan` to run PHPStan.

## Features

### Reschedules of actions that perform external requests

The plugin's goal is to be an opinionated actor that automatically reduces scheduled actions that contain external requests. That way, it can have a positive impact on the carbon footprint of a website out-of-the-box.

Current examples of such automatic reschedules:
* Reduces runs of the `wp_https_detection` scheduled action from twice per day to weekly, if the site is already on https. Otherwise, it reduces it to daily.
* Reduces runs of the `wp_version_check` scheduled action from twice per day to weekly, if the site is already on an outdated version.
* Disables completely the `wp_version_check` scheduled action, if the site has an active `DISALLOW_FILE_MODS` define.

But in order to maximize potential impact, the plugin also implements a public API for hosts, web owners, etc. to hook into and reschedule such requests even further themselves, according to their site's specific needs and requirements.

**Public API**

To reschedule a scheduled event, just call the following line, and replace 'daily' with your desired recurrence. This is an open API and can be used at will.
This needs to be called prior to the actual register hook.

```php
\EcoMode\EcoModeWP\Alter_Schedule::reschedule( 'action_name', 'daily' )
```

```php
add_action(
	'plugins_loaded',
	function () {
		\EcoMode\EcoModeWP\Alter_Schedule::reschedule( 'wp_https_detection', 'daily' );
	},
	0
);
```

### Manual Throttling

The goal of this feature is to be able to provide hosts, web owners, etc. a public API to throttle specific external requests themselves, according to their site's specific needs and requirements:

```php
add_filter(
    'eco_mode_wp_throttled_requests',
    function ( $throttledRequests ) {
        $throttledRequests[] = new \EcoMode\EcoModeWP\ThrottledRequest(
            'https://some.spammy.plugin/ping',
            MONTH_IN_SECONDS,
            'GET'
        );

        return $throttledRequests;
    }
);
```

### Disabling unnecessary core features

We take an opinionated approach to disabling core features that most users aren't actually using. One of these is the News &amp; Events Widget on the dashboard, which sends requests each day to pull in new data.

**Public API**

To reactivate the News &amp; Events Widget, you can use the following filter:

```php
add_filter( 'eco_mode_disable_wordpress_news_events_widget', '__return_false' );
```

### Settings & Chart
In the plugin's admin setting page, the plugin has:
* a graph that helps provide information to web owners about the amount of scheduled external requests the website is currently saving
* a list of external requests whose frequency the web owner can tweak

Those frontend features are currently implemented, but hardcodedly so they don't carry any dynamic functionality yet.
