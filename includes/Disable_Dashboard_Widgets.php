<?php
namespace EcoMode\EcoModeWP;

/**
 * Class to disable some dashboard widgets for saving requests
 */
class Disable_Dashboard_Widgets {

	public static function init(): void {

		if (apply_filters('eco_mode_disable_wordpress_news_events_widget', true)) {
			remove_meta_box('dashboard_primary', 'dashboard', 'normal'); // Removes the 'WordPress News' widget
		}

	}
}
