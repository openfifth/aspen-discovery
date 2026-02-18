<?php
require_once ROOT_DIR . '/Action.php';

/**
 * This class exists because PWABuilder.com did not except
 * dynamic urls for the manifest. Rather then modify how
 * SystemAPI's get logofile works we are going to point
 * /pwa-icon.png to this function
 */
class AspenMobile_Icon extends Action {

    function launch()
    {
        $api = new SystemAPI('internal');
        $_REQUEST['type'] = "logoApp";
        $_REQUEST['themeId'] = 1;
        $api->getLogoFile();
    }

    function getBreadcrumbs(): array {
		return [];
	}
}
?>