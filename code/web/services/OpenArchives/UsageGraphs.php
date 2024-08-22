<?php

require_once ROOT_DIR . '/services/Admin/AbstractUsageGraphs.php';
require_once ROOT_DIR . '/sys/OpenArchives/OpenArchivesCollection.php';
require_once ROOT_DIR . '/sys/OpenArchives/OpenArchivesRecord.php';
require_once ROOT_DIR . '/sys/OpenArchives/UserOpenArchivesUsage.php';
require_once ROOT_DIR . '/sys/OpenArchives/OpenArchivesRecordUsage.php';
require_once ROOT_DIR . '/sys/Utils/GraphingUtils.php';

class OpenArchives_UsageGraphs extends Admin_AbstractUsageGraphs {
	function launch(): void {
		$this->launchGraph('OpenArchives');
	}

	function getBreadcrumbs(): array {
		$breadcrumbs = [];
		$breadcrumbs[] = new Breadcrumb('/Admin/Home', 'Administration Home');
		$breadcrumbs[] = new Breadcrumb('/Admin/Home#side_loads', 'Side Loads');
		$breadcrumbs[] = new Breadcrumb('/openArchivesCollections/UsageDashboard', 'Usage Dashboard');
		$breadcrumbs[] = new Breadcrumb('', 'Usage Graph');
		return $breadcrumbs;
	}

	function getActiveAdminSection(): string {
		return 'open_archives';
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
			case 'numRecordViewed':
				$title .= ' - Unique Records Viewed';
				break;
			case 'numViews':
				$title .= ' - Total Views';
				break;
			case 'numRecordsUsed':
				$title .= ' - Unique Records Used (clicked on)';
				break;
			case 'numClicks':
				$title .= ' - Total Clicks';
				break;
			case 'activeUsers':
				$title .= ' - Unique Logged In Users';
				break;
		}
		$interface->assign('graphTitle', $title);
	}
}