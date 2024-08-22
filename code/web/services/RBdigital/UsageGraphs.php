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

	protected function getAndSetInterfaceDataSeries($stat, $instanceName): void {
		global $interface;
		$dataSeries = [];
		$columnLabels = [];

		// Load stats from user_rbdigital_usage
		if ($stat =='activeUsers'){
			$userUsage = new UserRBdigitalUsage();
			$userUsage->groupBy('year, month');
			if (!empty($instanceName)) {
				$userUsage->instance = $instanceName;
			}
			$userUsage->selectAdd();
			$userUsage->selectAdd('year');
			$userUsage->selectAdd('month');
			$userUsage->orderBy('year, month');
			$userUsage->selectAdd('COUNT(*) as numUsers');
			$dataSeries['Unique Users'] = GraphingUtils::getDataSeriesArray(count($dataSeries));

			$userUsage->find();
			while ($userUsage->fetch()) {
				$curPeriod = "{$userUsage->month}-{$userUsage->year}";
				$columnLabels[] = $curPeriod;
				/** @noinspection PhpUndefinedFieldInspection */
				$dataSeries['Unique Users']['data'][$curPeriod] = $userUsage->numUsers;
			}
		}

		// Load stats from rbdigital_record_usage
		if (
			$stat == 'recordsUsed' ||
			$stat == 'totalHolds' ||
			$stat == 'totalCheckouts'
		) {
			$recordUsage = new RBdigitalRecordUsage();
			$recordUsage->groupBy('year, month');
			if (!empty($instanceName)) {
				$recordUsage->instance = $instanceName;
			}
			$recordUsage->selectAdd();
			$recordUsage->selectAdd('year');
			$recordUsage->selectAdd('month');
			$recordUsage->orderBy('year, month');

			if ($stat =='recordsUsed'){
				$recordUsage->selectAdd('COUNT(id) as recordsUsed');
				$dataSeries['Records Used'] = GraphingUtils::getDataSeriesArray(count($dataSeries));
			}
			if ($stat =='totalHolds'){
				$recordUsage->selectAdd('SUM(timesHeld) as totalHolds');
				$dataSeries['Total Holds'] = GraphingUtils::getDataSeriesArray(count($dataSeries));
			}
			if ($stat =='totalCheckouts'){
				$recordUsage->selectAdd('SUM(timesCheckedOut) as totalCheckouts');
				$dataSeries['Total Checkouts'] = GraphingUtils::getDataSeriesArray(count($dataSeries));
			}
			//Collect results
			$recordUsage->find();
			while ($recordUsage->fetch()) {
					$curPeriod = "{$recordUsage->month}-{$recordUsage->year}";
					$columnLabels[] = $curPeriod;
				if ($stat =='recordsUsed'){
					/** @noinspection PhpUndefinedFieldInspection */
					$dataSeries['Records Used']['data'][$curPeriod] = $recordUsage->recordsUsed;
				}
				if ($stat =='totalHolds'){
					/** @noinspection PhpUndefinedFieldInspection */
					$dataSeries['Total Holds']['data'][$curPeriod] = $recordUsage->totalHolds;
				}
				if ($stat =='totalCheckouts'){
					/** @noinspection PhpUndefinedFieldInspection */
					$dataSeries['Total Checkouts']['data'][$curPeriod] = $recordUsage->totalCheckouts;
				}
			}
		}

		// Load stats from rbdigital_magazine_usage
		if (
			$stat == 'activeMagazines' ||
			$stat == 'magazineLoans'
		) {
			$magazineUsage = new RBdigitalMagazineUsage();
			$magazineUsage->groupBy('year, month');
			if (!empty($instanceName)) {
				$magazineUsage->instance = $instanceName;
			}
			$magazineUsage->selectAdd();
			$magazineUsage->selectAdd('year');
			$magazineUsage->selectAdd('month');
			$magazineUsage->orderBy('year, month');

			if ($stat =='activeMagazines'){
				$magazineUsage->selectAdd('COUNT(id) as recordsUsed');
				$dataSeries['Magazines Used'] = GraphingUtils::getDataSeriesArray(count($dataSeries));
			}
			if ($stat =='magazineLoans'){
				$magazineUsage->selectAdd('SUM(timesCheckedOut) as totalCheckouts');
				$dataSeries['Total Checkouts'] = GraphingUtils::getDataSeriesArray(count($dataSeries));
			}

			//Collect results
			$magazineUsage->find();
			while ($magazineUsage->fetch()) {
				$curPeriod = "{$magazineUsage->month}-{$magazineUsage->year}";
				$columnLabels[] = $curPeriod;
				if ($stat =='activeMagazines'){
					/** @noinspection PhpUndefinedFieldInspection */
					$dataSeries['Magazines Used']['data'][$curPeriod] = $magazineUsage->recordsUsed;
				}
				if ($stat =='magazineLoans' || $stat =='general'){
					/** @noinspection PhpUndefinedFieldInspection */
					$dataSeries['Total Checkouts']['data'][$curPeriod] = $magazineUsage->totalCheckouts;
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