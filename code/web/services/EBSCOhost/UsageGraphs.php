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

	protected function getAndSetInterfaceDataSeries($stat, $instanceName): void {
		global $interface;
		$dataSeries = [];
		$columnLabels = [];

		// gets data from from user_summon_usage
		if ($stat == 'activeUsers') {
			$userSummonUsage = new UserEbscohostUsage();
			$userSummonUsage->groupBy('year, month');
			if (!empty($instanceName)) {
				$userSummonUsage->instance = $instanceName;
			}
			$userSummonUsage->selectAdd();
			$userSummonUsage->selectAdd('year');
			$userSummonUsage->selectAdd('month');
			$userSummonUsage->orderBy('year, month');

			$dataSeries['Active Users'] = GraphingUtils::getDataSeriesArray(count($dataSeries));

			$userSummonUsage->selectAdd('COUNT(DISTINCT userId) as activeUsers');

			// Collects results
			$userSummonUsage->find();
			while($userSummonUsage->fetch()) {
				$curPeriod = "{$userSummonUsage->month}-{$userSummonUsage->year}";
				$columnLabels[] = $curPeriod;
				/** @noinspection PhpUndefinedFieldInspection */
				$dataSeries['Active Users']['data'][$curPeriod] = $userSummonUsage->activeUsers;
			}
		}

		// gets data from from summon_usage
		if (
			$stat == 'numViews' ||
			$stat == 'numClicks' ||
			$stat == 'numRecordsUsed'
		){
			$summonRecordUsage = new EbscohostRecordUsage();
			$summonRecordUsage->groupBy('year, month');
			if (!empty($instanceName)) {
				$summonRecordUsage->instance = $instanceName;
			}
			$summonRecordUsage->selectAdd();
			$summonRecordUsage->selectAdd('year');
			$summonRecordUsage->selectAdd('month');
			$summonRecordUsage->orderBy('year, month');

			if ($stat == 'numClicks') {
				$dataSeries['Total Clicks'] = GraphingUtils::getDataSeriesArray(count($dataSeries));
				$summonRecordUsage ->selectAdd('SUM(timesUsed) as numClicks');
			}
			if ($stat == 'numViews') {
				$dataSeries['Number of Records Viewed'] = GraphingUtils::getDataSeriesArray(count($dataSeries));
				$summonRecordUsage ->selectAdd('SUM(IF(timesViewedInSearch>0,1,0)) as numRecordsViewed');
			}
			if ($stat == 'numRecordsUsed') {
				$dataSeries['Number of Records Clicked'] = GraphingUtils::getDataSeriesArray(count($dataSeries));
				$summonRecordUsage ->selectAdd('SUM(IF(timesUsed>0,1,0)) as numRecordsUsed');
			}
			// Collect results
			$summonRecordUsage->find();
			while ($summonRecordUsage->fetch()) {
				$curPeriod = "{$summonRecordUsage->month}-{$summonRecordUsage->year}";
				$columnLabels[] = $curPeriod;
				if ($stat == 'numClicks') {
					/** @noinspection PhpUndefinedFieldInspection */
					$dataSeries['Total Clicks']['data'][$curPeriod] = $summonRecordUsage->numClicks;
				}
				if ($stat == 'numViews') {
					/** @noinspection PhpUndefinedFieldInspection */
					$dataSeries['Number of Records Viewed']['data'][$curPeriod] = $summonRecordUsage->numRecordsViewed;
				}
				if ($stat == 'numRecordsUsed') {
					/** @noinspection PhpUndefinedFieldInspection */
					$dataSeries['Number of Records Clicked']['data'][$curPeriod] = $summonRecordUsage->numRecordsUsed;
				}
			}
		}

		$interface->assign('columnLabels', $columnLabels);
		$interface->assign('dataSeries', $dataSeries);
		$interface->assign('translateDataSeries', true);
		$interface->assign('translateColumnLabels', false);
	}

	protected function assignGraphSpecificTitle($stat): void {
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