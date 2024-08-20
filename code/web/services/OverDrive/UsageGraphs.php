<?php

require_once ROOT_DIR . '/services/Admin/AbstractUsageGraphs.php';
require_once ROOT_DIR . '/sys/OverDrive/UserOverDriveUsage.php';
require_once ROOT_DIR . '/sys/OverDrive/OverDriveRecordUsage.php';
require_once ROOT_DIR . '/sys/OverDrive/OverDriveStats.php';
require_once ROOT_DIR . '/sys/Utils/GraphingUtils.php';


class OverDrive_UsageGraphs extends Admin_Admin {
	function launch() : void {

		global $interface;

		$readerName = new OverDriveDriver();
		$readerName = $readerName->getReaderName();

		$title = $readerName . ' Usage Graph';
		if (!empty($_REQUEST['instance'])) {
			$instanceName = $_REQUEST['instance'];
		} else {
			$instanceName = '';
		}

		$dataSeries = [];
		$columnLabels = [];

		$dataSeries['Total Usage'] = GraphingUtils::getDataSeriesArray(count($dataSeries));
		$dataSeries['Unique Users'] = GraphingUtils::getDataSeriesArray(count($dataSeries));
		$dataSeries['Records Used'] = GraphingUtils::getDataSeriesArray(count($dataSeries));
		$dataSeries['Total Holds'] = GraphingUtils::getDataSeriesArray(count($dataSeries));
		$dataSeries['Total Failed Holds'] = GraphingUtils::getDataSeriesArray(count($dataSeries));
		$dataSeries['Total Checkouts'] = GraphingUtils::getDataSeriesArray(count($dataSeries));
		$dataSeries['Total Failed Checkouts'] = GraphingUtils::getDataSeriesArray(count($dataSeries));
		$dataSeries['Total Renewals'] =  GraphingUtils::getDataSeriesArray(count($dataSeries));
		$dataSeries['Total Early Returns'] =  GraphingUtils::getDataSeriesArray(count($dataSeries));
		$dataSeries['Total Holds Cancelled'] =  GraphingUtils::getDataSeriesArray(count($dataSeries));
		$dataSeries['Total Holds Frozen'] =  GraphingUtils::getDataSeriesArray(count($dataSeries));
		$dataSeries['Total Holds Thawed'] =  GraphingUtils::getDataSeriesArray(count($dataSeries));
		$dataSeries['Total Downloads'] =  GraphingUtils::getDataSeriesArray(count($dataSeries));
		$dataSeries['Total Previews'] =  GraphingUtils::getDataSeriesArray(count($dataSeries));
		$dataSeries['Total Options Updates'] =  GraphingUtils::getDataSeriesArray(count($dataSeries));
		$dataSeries['Total API Errors'] =  GraphingUtils::getDataSeriesArray(count($dataSeries));
		$dataSeries['Total Connection Failures'] =  GraphingUtils::getDataSeriesArray(count($dataSeries));
		$userUsage = new UserOverDriveUsage();
		$userUsage->groupBy('year, month');
		if (!empty($instanceName)) {
			$userUsage->instance = $instanceName;
		}
		$userUsage->selectAdd();
		$userUsage->selectAdd('year');
		$userUsage->selectAdd('month');
		$userUsage->selectAdd('COUNT(*) as numUsers');
		$userUsage->selectAdd('SUM(usageCount) as sumUsage');
		$userUsage->orderBy('year, month');
		$userUsage->find();
		while ($userUsage->fetch()) {
			$curPeriod = "$userUsage->month-$userUsage->year";
			$columnLabels[] = $curPeriod;
			/** @noinspection PhpUndefinedFieldInspection */
			$dataSeries['Total Usage']['data'][$curPeriod] = $userUsage->sumUsage;
			/** @noinspection PhpUndefinedFieldInspection */
			$dataSeries['Unique Users']['data'][$curPeriod] = $userUsage->numUsers;

			//Make sure we have default values for all the other series
			$dataSeries['Records Used']['data'][$curPeriod] = 0;
			$dataSeries['Total Holds']['data'][$curPeriod] = 0;
			$dataSeries['Total Failed Holds']['data'][$curPeriod] = 0;
			$dataSeries['Total Checkouts']['data'][$curPeriod] = 0;
			$dataSeries['Total Failed Checkouts']['data'][$curPeriod] = 0;
			$dataSeries['Total Early Returns']['data'][$curPeriod] = 0;
			$dataSeries['Total Renewals']['data'][$curPeriod] = 0;
			$dataSeries['Total Holds Cancelled']['data'][$curPeriod] = 0;
			$dataSeries['Total Holds Frozen']['data'][$curPeriod] = 0;
			$dataSeries['Total Holds Thawed']['data'][$curPeriod] = 0;
			$dataSeries['Total Downloads']['data'][$curPeriod] = 0;
			$dataSeries['Total Previews']['data'][$curPeriod] = 0;
			$dataSeries['Total Options Updates']['data'][$curPeriod] = 0;
			$dataSeries['Total API Errors']['data'][$curPeriod] = 0;
			$dataSeries['Total Connection Failures']['data'][$curPeriod] = 0;
		}

		//Load Record Stats
		$stats = new OverDriveStats();
		$stats->groupBy('year, month');
		if (!empty($instanceName)) {
			$stats->instance = $instanceName;
		}
		$stats->selectAdd();
		$stats->selectAdd('year');
		$stats->selectAdd('month');
		$stats->selectAdd('SUM(numHoldsPlaced) as numHoldsPlaced');
		$stats->selectAdd('SUM(numFailedHolds) as numFailedHolds');
		$stats->selectAdd('SUM(numCheckouts) as numCheckouts');
		$stats->selectAdd('SUM(numFailedCheckouts) as numFailedCheckouts');
		$stats->selectAdd('SUM(numEarlyReturns) as numEarlyReturns');
		$stats->selectAdd('SUM(numRenewals) as numRenewals');
		$stats->selectAdd('SUM(numHoldsCancelled) as numHoldsCancelled');
		$stats->selectAdd('SUM(numHoldsFrozen) as numHoldsFrozen');
		$stats->selectAdd('SUM(numHoldsThawed) as numHoldsThawed');
		$stats->selectAdd('SUM(numDownloads) as numDownloads');
		$stats->selectAdd('SUM(numPreviews) as numPreviews');
		$stats->selectAdd('SUM(numOptionsUpdates) as numOptionsUpdates');
		$stats->selectAdd('SUM(numApiErrors) as numApiErrors');
		$stats->selectAdd('SUM(numConnectionFailures) as numConnectionFailures');
		$stats->orderBy('year, month');
		$stats->find();
		while ($stats->fetch()) {
			$curPeriod = "$stats->month-$stats->year";
			$dataSeries['Total Holds']['data'][$curPeriod] = $stats->numHoldsPlaced;
			$dataSeries['Total Failed Holds']['data'][$curPeriod] = $stats->numFailedHolds;
			$dataSeries['Total Checkouts']['data'][$curPeriod] = $stats->numCheckouts;
			$dataSeries['Total Failed Checkouts']['data'][$curPeriod] = $stats->numFailedCheckouts;
			$dataSeries['Total Early Returns']['data'][$curPeriod] = $stats->numEarlyReturns;
			$dataSeries['Total Renewals']['data'][$curPeriod] = $stats->numRenewals;
			$dataSeries['Total Holds Cancelled']['data'][$curPeriod] = $stats->numHoldsCancelled;
			$dataSeries['Total Holds Frozen']['data'][$curPeriod] = $stats->numHoldsFrozen;
			$dataSeries['Total Holds Thawed']['data'][$curPeriod] = $stats->numHoldsThawed;
			$dataSeries['Total Downloads']['data'][$curPeriod] = $stats->numDownloads;
			$dataSeries['Total Previews']['data'][$curPeriod] = $stats->numPreviews;
			$dataSeries['Total Options Updates']['data'][$curPeriod] = $stats->numOptionsUpdates;
			$dataSeries['Total API Errors']['data'][$curPeriod] = $stats->numApiErrors;
			$dataSeries['Total Connection Failures']['data'][$curPeriod] = $stats->numConnectionFailures;
		}

		$recordUsage = new OverDriveRecordUsage();
		$recordUsage->groupBy('year, month');
		if (!empty($instanceName)) {
			$recordUsage->instance = $instanceName;
		}
		$recordUsage->selectAdd();
		$recordUsage->selectAdd('year');
		$recordUsage->selectAdd('month');
		$recordUsage->selectAdd('COUNT(*) as numRecordsUsed');
		$recordUsage->selectAdd('SUM(timesHeld) as numHoldsPlaced');
		$recordUsage->selectAdd('SUM(timesCheckedOut) as numCheckouts');
		$recordUsage->orderBy('year, month');
		$recordUsage->find();
		while ($recordUsage->fetch()) {
			$curPeriod = "$stats->month-$stats->year";
			/** @noinspection PhpUndefinedFieldInspection */
			$dataSeries['Records Used']['data'][$curPeriod] = $recordUsage->numRecordsUsed;
			/** @noinspection PhpUndefinedFieldInspection */
			$dataSeries['Total Holds']['data'][$curPeriod] = $recordUsage->numHoldsPlaced;
			/** @noinspection PhpUndefinedFieldInspection */
			$dataSeries['Total Checkouts']['data'][$curPeriod] = $recordUsage->numCheckouts;
		}

		$interface->assign('columnLabels', $columnLabels);
		$interface->assign('dataSeries', $dataSeries);
		$interface->assign('translateDataSeries', true);
		$interface->assign('translateColumnLabels', false);

		$interface->assign('graphTitle', $title);
		$interface->assign('section', 'OverDrive');
		$interface->assign('showCSVExportButton', true);

		$this->display('../Admin/usage-graph.tpl', $title);
	}

	function getBreadcrumbs(): array {
		$readerName = new OverDriveDriver();
		$readerName = $readerName->getReaderName();
		$breadcrumbs = [];
		$breadcrumbs[] = new Breadcrumb('/Admin/Home', 'Administration Home');
		$breadcrumbs[] = new Breadcrumb('/Admin/Home#overdrive', $readerName);
		$breadcrumbs[] = new Breadcrumb('/OverDrive/Dashboard', 'Usage Dashboard');
		$breadcrumbs[] = new Breadcrumb('', 'Usage Graph');
		return $breadcrumbs;
	}

	function getActiveAdminSection(): string {
		return 'overdrive';
	}

	function canView(): bool {
		return UserAccount::userHasPermission([
			'View System Reports',
			'View Dashboards',
		]);
	}
}