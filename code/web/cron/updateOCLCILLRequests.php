<?php

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../bootstrap_aspen.php';

require_once ROOT_DIR . '/sys/Account/User.php';
require_once ROOT_DIR . '/sys/OCLCRSFG/OCLCRSFGRequest.php';


$patron = new User();
$oclcRSFGIllRequest = new OCLCRSFGRequest();
$patron->selectAdd();
$patron->selectAdd('DISTINCT user.id, user.homeLocationId');
$patron->joinAdd($oclcRSFGIllRequest, 'INNER', 'patronWithIllRequests', 'id', 'userId');
$patronWithIllRequestsList = $patron->fetchAll();

if (empty($patronWithIllRequestsList)) {
	die();
}

require_once ROOT_DIR . '/sys/OCLCRSFG/OCLCRSFGSetting.php';
require_once ROOT_DIR . '/Drivers/OCLCRSFGDriver.php';

foreach ($patronWithIllRequestsList as $patronWithIllRequests) {
	$oclcRegistryId = $patronWithIllRequests->getHomeLocation()->oclcRegistryId;
	if ($oclcRegistryId == -1) {
		continue;
	}
	$driver = new OCLCRSFGDriver($oclcRegistryId);
	$settings = new OCLCRSFGSetting();

	$settings->id = $patronWithIllRequests->getHomeLibrary()->oclcRSFGSettingsId;
	if(!$settings->find(true)) {
		continue;
	};
	$driver->updateRequestsInAspenDbForPatron($settings, $patronWithIllRequests->id);
}

die();