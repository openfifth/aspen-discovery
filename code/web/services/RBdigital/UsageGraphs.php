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
}