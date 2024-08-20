<?php

require_once ROOT_DIR . '/services/Admin/AbstractUsageGraphs.php';
require_once ROOT_DIR . '/sys/CloudLibrary/UserCloudLibraryUsage.php';
require_once ROOT_DIR . '/sys/CloudLibrary/CloudLibraryRecordUsage.php';
require_once ROOT_DIR . '/sys/Utils/GraphingUtils.php';

class CloudLibrary_UsageGraphs extends Admin_AbstractUsageGraphs {
	function launch(): void {
		$this->launchGraph('CloudLibrary');
	}

	function getBreadcrumbs(): array {
		$breadcrumbs = [];
		$breadcrumbs[] = new Breadcrumb('/Admin/Home', 'Administration Home');
		$breadcrumbs[] = new Breadcrumb('/Admin/Home#cloud_library', 'cloudLibrary');
		$breadcrumbs[] = new Breadcrumb('/CloudLibrary/Dashboard', 'Usage Dashboard');
		$breadcrumbs[] = new Breadcrumb('', 'Usage Graph');
		return $breadcrumbs;
	}

	function getActiveAdminSection(): string {
		return 'cloud_library';
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
			case 'recordsWithUsage':
				$title .= ' - Records With Usage';
				break;
			case 'loans':
				$title .= ' - Loans';
				break;
			case 'holds':
				$title .= ' - Holds';
				break;
		}
		$interface->assign('graphTitle', $title);
	}
}