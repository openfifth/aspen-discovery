<?php
require_once ROOT_DIR . '/Action.php';


class OCLCRSFGRequestDetails extends Action {
	function launch(){
		global $interface;

		require_once ROOT_DIR . '/Drivers/OCLCRSFGDriver.php';
		require_once ROOT_DIR . '/sys/OCLCRSFG/OCLCRSFGSetting.php';
		$driver = new OCLCRSFGDriver;
		$requestId = $_REQUEST['requestId'];
		$OCLCRSFGSettings = new OCLCRSFGSetting();
		$activeLibrary = Library::getActiveLibrary();
		$OCLCRSFGSettings->whereAdd("id={$activeLibrary->oclcRSFGSettingsId}");
		if (!$OCLCRSFGSettings->find(true)) {
			return false;
		}
		$interface->assign('illRequest', $driver->getRequestDetails($OCLCRSFGSettings, $requestId));

		global $logger;
		$logger->log($requestId, Logger::LOG_ERROR);
		$this->display('oclc-rsfg-request-details.tpl', 'Interlibrary Loan Request');
	}

	function getBreadcrumbs(): array {
		$breadcrumbs = [];
		$breadcrumbs[] = new Breadcrumb('/MyAccount/Holds', 'Holds');
		$breadcrumbs[] = new Breadcrumb('/OCLCRSFG/OCLCRSFGRequestDetails', 'OCLCRSFGRequestDetails');
		return $breadcrumbs;
	}
}