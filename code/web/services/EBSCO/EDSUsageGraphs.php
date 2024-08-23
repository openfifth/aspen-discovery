<?php

require_once ROOT_DIR . '/services/Admin/AbstractUsageGraphs.php';
require_once ROOT_DIR . '/sys/SystemLogging/AspenUsage.php';
require_once ROOT_DIR . '/sys/Ebsco/UserEbscoEdsUsage.php';
require_once ROOT_DIR . '/sys/Ebsco/EbscoEdsRecordUsage.php';
require_once ROOT_DIR . '/sys/Utils/GraphingUtils.php';

class EBSCO_EDSUsageGraphs extends Admin_AbstractUsageGraphs {
	function launch(): void {
		$this->launchGraph('EBSCO');
	}

	function getBreadcrumbs(): array {
		$breadcrumbs = [];
		$breadcrumbs[] = new Breadcrumb('/Admin/Home', 'Administration Home');
		$breadcrumbs[] = new Breadcrumb('/Admin/Home#ebsco', 'EBSCO');
		$breadcrumbs[] = new Breadcrumb('/EBSCO/EDSDashboard', 'EDS Usage Dashboard');
		$breadcrumbs[] = new Breadcrumb('', 'Usage Graph');
		return $breadcrumbs;
	}

	function getActiveAdminSection(): string {
		return 'ebsco';
	}

	function canView(): bool {
		return UserAccount::userHasPermission([
			'View System Reports',
			'View Dashboards',
		]);
	}

	private function assignGraphSpecificTitle($stat) {
		global $interface;
		$title = $interface->getVariable('graphTitle');
		switch ($stat) {
			case 'activeUsers':
				$title .= ' - Active Users';
			break;
			case 'numViews':
				$title .= ' - Number of Records Viewed';
			break;
			case 'numRecordsUsed':
				$title .= ' - Number of Records Clicked';
			break;
			case 'numClicks':
				$title .= ' - Total Clicks';
			break;
		}
		$interface->assign('graphTitle', $title);
	}
}