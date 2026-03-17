<?php
require_once ROOT_DIR . '/Action.php';
require_once ROOT_DIR . '/services/API/SystemAPI.php';
require_once ROOT_DIR . '/sys/AspenPWA/Setting.php';

/**
 * This class exists because PWABuilder.com did not except
 * dynamic urls for the manifest. Rather then modify how
 * SystemAPI's get logofile works we are going to point
 * /pwa-icon.png to this function
 */
class AspenPWA_Icon extends Action {

	function launch()
	{
		$setting = new AspenMobileSetting();
		$success = true;
		//TODO we should return an error code instead of 200
		// if we have no settings
		if($setting->find(true))
		{
			$_REQUEST['themeId'] = $setting->themeId;
		} else {
			$_REQUEST['themeId'] = 1;
		}

		$api = new SystemAPI('internal');
		$_REQUEST['type'] = "logoApp";
		$api->getLogoFile();
	}

	function getBreadcrumbs(): array {
		return [];
	}
}
?>