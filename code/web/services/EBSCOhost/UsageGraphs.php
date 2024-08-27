<?php

require_once ROOT_DIR . '/services/Admin/AbstractUsageGraphs.php';
require_once ROOT_DIR . '/sys/SystemLogging/AspenUsage.php';
require_once ROOT_DIR . '/sys/Ebsco/UserEbscohostUsage.php';
require_once ROOT_DIR . '/sys/Ebsco/EbscohostRecordUsage.php';
require_once ROOT_DIR . '/sys/Utils/GraphingUtils.php';

class EBSCOhost_UsageGraphs extends Admin_AbstractUsageGraphs {
	function launch(): void {
		$this->launchGraph('EBSCOhost');
	}

	function getBreadcrumbs(): array {
		$breadcrumbs = [];
		$breadcrumbs[] = new Breadcrumb('/Admin/Home', 'Administration Home');
		$breadcrumbs[] = new Breadcrumb('/Admin/Home#ebsco', 'EBSCO');
		$breadcrumbs[] = new Breadcrumb('/EBSCOhost/Dashboard', 'host Usage Dashboard');
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
}