<?php
namespace EcoMode\EcoModeWP;

/**
 * Class to disable some dashboard widgets for saving requests
 */
class DisableDashboardWidgets {

	public static function init(): void {
		// TODO: make this optional through a setting
		remove_meta_box('dashboard_primary', 'dashboard', 'normal'); // Removes the 'WordPress News' widget
	}
}
