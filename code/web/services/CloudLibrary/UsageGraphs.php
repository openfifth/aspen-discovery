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

	protected function getAndSetInterfaceDataSeries($stat, $instanceName): void {
		global $interface;

		$dataSeries = [];
		$columnLabels = [];

		// Load stats from cloud_library_record_usage
		if ($stat == 'recordsWithUsage' ||
			$stat == 'loans' ||
			$stat == 'holds') {
			$stats = new CloudLibraryRecordUsage();
			$stats->groupBy('year, month');
			if (!empty($instanceName)) {
				$stats->instance = $instanceName;
			}
			$stats->selectAdd();
			$stats->selectAdd('year');
			$stats->selectAdd('month');
			$stats->orderBy('year, month');

			if ($stat == 'Records With Usage'){
				$dataSeries['Records With Usage'] = GraphingUtils::getDataSeriesArray(count($dataSeries));
				$stats->selectAdd('COUNT(id) as recordsUsed');
			}
			if ($stat == 'totalHolds'){
				$dataSeries['Total Holds'] = GraphingUtils::getDataSeriesArray(count($dataSeries));
				$stats->selectAdd('SUM(timesHeld) as totalHolds');
			}
			if ($stat == 'totalCheckouts'){
				$dataSeries['Total Loans'] =  GraphingUtils::getDataSeriesArray(count($dataSeries));
				$stats->selectAdd('SUM(timesCheckedOut) as totalCheckouts');
			}

			$stats->find();
			while ($stats->fetch()) {
				$curPeriod = "{$stats->month}-{$stats->year}";
				if (!in_array("{$stats->month}-{$stats->year}", $columnLabels)) {  // prevents the multiple addition of a curPeriod
					$columnLabels[] = $curPeriod;
				}
				if ($stat == 'recordsWithUsage'){
					/** @noinspection PhpUndefinedFieldInspection */
					$dataSeries['Records With Usage']['data'][$curPeriod] = $stats->recordsUsed;
				}
				if ($stat == 'holds'){
					/** @noinspection PhpUndefinedFieldInspection */
					$dataSeries['Total Holds']['data'][$curPeriod] = $stats->totalHolds;
				}
				if ($stat == 'loans'){
					/** @noinspection PhpUndefinedFieldInspection */
					$dataSeries['Total Loans']['data'][$curPeriod] = $stats->totalCheckouts;
				}

			}
		}

		$interface->assign('columnLabels', $columnLabels);
		$interface->assign('dataSeries', $dataSeries);
		$interface->assign('translateDataSeries', true);
		$interface->assign('translateColumnLabels', false);
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