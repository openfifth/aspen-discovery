<?php

require_once ROOT_DIR . '/services/Admin/AbstractUsageGraphs.php';
require_once ROOT_DIR . '/sys/Indexing/UserSideLoadUsage.php';
require_once ROOT_DIR . '/sys/Indexing/SideLoadedRecordUsage.php';
require_once ROOT_DIR . '/sys/Utils/GraphingUtils.php';


class SideLoads_UsageGraphs extends Admin_AbstractUsageGraphs {
	function launch(): void {
		$this->launchGraph('SideLoads');
	}

	function getBreadcrumbs(): array {
		$breadcrumbs = [];
		$breadcrumbs[] = new Breadcrumb('/Admin/Home', 'Administration Home');
		$breadcrumbs[] = new Breadcrumb('/Admin/Home#side_loads', 'Side Loads');
		$breadcrumbs[] = new Breadcrumb('/SideLoads/Dashboard', 'Usage Dashboard');
		$breadcrumbs[] = new Breadcrumb('', 'Usage Graph');
		return $breadcrumbs;
	}

	function getActiveAdminSection(): string {
		return 'side_loads';
	}

	function canView(): bool {
		return UserAccount::userHasPermission([
			'View Dashboards',
			'View System Reports',
		]);
	}

	/*
		The only unique identifier available to determine for which
		sideload to fetch data is the sideload's name as $profileName. It is used
		here to find the sideloads' id as only this exists on the sideload
		usage tables
	*/
	private function getSideloadIdBySideLoadName($name): int {
		$sideload = new SideLoad();
		$sideload->whereAdd('name = "' . $name .'"');
		$sideload->selectAdd();
		$sideload->find();
		return $sideload->fetch()->id;
	}

	protected function getAndSetInterfaceDataSeries($stat, $instanceName): void {
		global $interface;

		$dataSeries = [];
		$columnLabels = [];
		$usage = [];

		$profileName= $_REQUEST['subSection'];
		$sideloadId = $this->getSideloadIdBySideLoadName($profileName);

		// for the graph displaying data retrieved from the user_sideload_usage table
		if ($stat == 'activeUsers') {
			$usage = new UserSideLoadUsage();
			$usage->groupBy('year, month');
			if (!empty($instanceName)) {
				$usage->instance = $instanceName;
			}
			$usage->whereAdd("sideloadId = $sideloadId");
			$usage->selectAdd();
			$usage->selectAdd('year');
			$usage->selectAdd('month');
			$usage->orderBy('year, month');

			$dataSeries['Active Users'] = GraphingUtils::getDataSeriesArray(count($dataSeries));
			$usage->selectAdd('COUNT(id) as numUsers');
		}

		// for the graph displaying data retrieved from the sideload_record_usage table
		if ($stat == 'recordsAccessedOnline' ) {
			$usage = new SideLoadedRecordUsage();
			$usage->groupBy('year, month');
			if (!empty($instanceName)) {
				$usage->instance = $instanceName;
			}

			$usage->whereAdd("sideloadId = $sideloadId");
			$usage->selectAdd(null);
			$usage->selectAdd();
			$usage->selectAdd('year');
			$usage->selectAdd('month');
			$usage->orderBy('year, month');

			$dataSeries['Records Accessed Online'] = GraphingUtils::getDataSeriesArray(count($dataSeries));
			$usage->selectAdd('SUM(IF(timesUsed>0,1,0)) as numRecordsUsed');
		}

		// collect results
		$usage->find();
		while ($usage->fetch()) {
			$curPeriod = "{$usage->month}-{$usage->year}";
			$columnLabels[] = $curPeriod;
			if ($stat == 'activeUsers') {
				/** @noinspection PhpUndefinedFieldInspection */
				$dataSeries['Active Users']['data'][$curPeriod] = $usage->numUsers;
			}
			if ($stat == 'recordsAccessedOnline') {
				/** @noinspection PhpUndefinedFieldInspection */
				$dataSeries['Records Accessed Online']['data'][$curPeriod] = $usage->numRecordsUsed;
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
		if ($stat == 'activeUsers') {
			$title .= ' - Active Users';
		}
		if ($stat == 'recordsAccessedOnline') {
			$title .= ' - Records Accessed Online';
		}
		$interface->assign('graphTitle', $title);
	}
}
