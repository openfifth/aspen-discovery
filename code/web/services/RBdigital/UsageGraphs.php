<?php

require_once ROOT_DIR . '/services/Admin/AbstractUsageGraphs.php';
require_once ROOT_DIR . '/sys/RBdigital/UserRBdigitalUsage.php';
require_once ROOT_DIR . '/sys/RBdigital/RBdigitalRecordUsage.php';
require_once ROOT_DIR . '/sys/RBdigital/RBdigitalMagazineUsage.php';
require_once ROOT_DIR . '/sys/Utils/GraphingUtils.php';

class RBdigital_UsageGraphs extends Admin_AbstractUsageGraphs {
	function launch(): void {
		$this->launchGraph('RBdigital');
	}

	function getBreadcrumbs(): array {
		$breadcrumbs = [];
		$breadcrumbs[] = new Breadcrumb('/Admin/Home', 'Administration Home');
		$breadcrumbs[] = new Breadcrumb('/Admin/Home#rbdigital', 'RBdigital');
		$breadcrumbs[] = new Breadcrumb('/RBdigital/Dashboard', 'Usage Dashboard');
		$breadcrumbs[] = new Breadcrumb('', 'Usage Graph');
		return $breadcrumbs;
	}

	function getActiveAdminSection(): string {
		return 'rbdigital';
	}

	function canView(): bool {
		return UserAccount::userHasPermission([
			'View Dashboards',
			'View System Reports',
		]);
	}

	private function assignGraphSpecificTitle($stat) {
		global $interface;
		$title = $interface->getVariable('graphTitle');
		switch ($stat) {
			case 'activeUsers':
				$title .= ' - Active Users';
				break;
			case 'recordsUsed':
				$title .= ' - Records With Usage';
				break;
			case 'totalHolds':
				$title .= ' - Holds';
				break;
			case 'totalCheckouts':
				$title .= ' - Loans';
				break;
			case 'activeMagazines':
				$title .= ' - Magazines With Usage';
				break;
			case 'magazineLoans':
				$title .= ' - Magazine Checkouts';
				break;
		}
		$interface->assign('graphTitle', $title);
	}
}