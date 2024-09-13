<?php

require_once ROOT_DIR . '/services/Admin/AbstractUsageGraphs.php';
require_once ROOT_DIR . '/sys/OverDrive/UserOverDriveUsage.php';
require_once ROOT_DIR . '/sys/OverDrive/OverDriveRecordUsage.php';
require_once ROOT_DIR . '/sys/OverDrive/OverDriveStats.php';
require_once ROOT_DIR . '/sys/Utils/GraphingUtils.php';

class OverDrive_UsageGraphs extends Admin_AbstractUsageGraphs {
	function launch(): void {
		$this->launchGraph('OverDrive');
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

	private function getAndSetInterfaceDataSeries($stat, $instanceName) {
		global $interface;

		$dataSeries = [];
		$columnLabels = [];

		// Load stats from user_overdrive_usage
		if ($stat =='activeUsers' || $stat =='general'){
			$userUsage = new UserOverDriveUsage();
			$userUsage->groupBy('year, month');
			if (!empty($instanceName)) {
				$userUsage->instance = $instanceName;
			}
			$userUsage->selectAdd();
			$userUsage->selectAdd('year');
			$userUsage->selectAdd('month');
			$userUsage->orderBy('year, month');

			if ($stat =='activeUsers' || $stat =='general'){
				$userUsage->selectAdd('COUNT(*) as numUsers');
				$dataSeries['Unique Users'] = GraphingUtils::getDataSeriesArray(count($dataSeries));
			}
			if ($stat =='general'){			
				$userUsage->selectAdd('SUM(usageCount) as sumUsage');
				$dataSeries['Total Usage'] = GraphingUtils::getDataSeriesArray(count($dataSeries));
			}
			$userUsage->find();

			while ($userUsage->fetch()) {
				$curPeriod = "{$userUsage->month}-{$userUsage->year}";
				$columnLabels[] = $curPeriod;
				if ($stat =='activeUsers' || $stat =='general'){
					/** @noinspection PhpUndefinedFieldInspection */
					$dataSeries['Unique Users']['data'][$curPeriod] = $userUsage->numUsers;
				}
				if ($stat =='general'){	
					/** @noinspection PhpUndefinedFieldInspection */
					$dataSeries['Total Usage']['data'][$curPeriod] = $userUsage->sumUsage;
				}
			}
		}

		// Load stats from overdrive_stats
		if ($stat =='failedLoans' ||
			$stat =='failedHolds' ||
			$stat =='earlyReturns' ||
			$stat =='renewals' ||
			$stat =='holdsCancelled' ||
			$stat =='holdsFrozen' ||
			$stat =='holdsThawed' ||
			$stat =='downloads' ||
			$stat =='previews' ||
			$stat =='optionUpdates' ||
			$stat =='apiErrors' ||
			$stat =='connectionFailures' ||
			$stat == 'general') {
			$stats = new OverDriveStats();
			$stats->groupBy('year, month');
			if (!empty($instanceName)) {
				$stats->instance = $instanceName;
			}
			$stats->selectAdd();
			$stats->selectAdd('year');
			$stats->selectAdd('month');
			$stats->orderBy('year, month');
			
			if ($stat =='failedLoans' || $stat =='general'){
				$dataSeries['Total Failed Holds'] = GraphingUtils::getDataSeriesArray(count($dataSeries));
				$stats->selectAdd('SUM(numFailedCheckouts) as numFailedCheckouts');
			}
			if ($stat =='failedHolds' || $stat =='general'){
				$dataSeries['Total Failed Loans'] = GraphingUtils::getDataSeriesArray(count($dataSeries));
				$stats->selectAdd('SUM(numFailedHolds) as numFailedHolds');
			}
			if ($stat =='earlyReturns' || $stat =='general'){
				$dataSeries['Total Early Returns'] =  GraphingUtils::getDataSeriesArray(count($dataSeries));
				$stats->selectAdd('SUM(numEarlyReturns) as numEarlyReturns');
			}
			if ($stat =='renewals' || $stat =='general'){
				$dataSeries['Total Renewals'] =  GraphingUtils::getDataSeriesArray(count($dataSeries));
				$stats->selectAdd('SUM(numRenewals) as numRenewals');
			}
			if ($stat =='holdsCancelled' || $stat =='general'){
				$dataSeries['Total Holds Cancelled'] =  GraphingUtils::getDataSeriesArray(count($dataSeries));
				$stats->selectAdd('SUM(numHoldsCancelled) as numHoldsCancelled');
			}
			if ($stat =='holdsFrozen' || $stat =='general'){
				$dataSeries['Total Holds Frozen'] =  GraphingUtils::getDataSeriesArray(count($dataSeries));
				$stats->selectAdd('SUM(numHoldsFrozen) as numHoldsFrozen');
			}
			if ($stat =='holdsThawed' || $stat =='general'){
				$dataSeries['Total Holds Thawed'] =  GraphingUtils::getDataSeriesArray(count($dataSeries));
				$stats->selectAdd('SUM(numHoldsThawed) as numHoldsThawed');
			}
			if ($stat =='downloads' || $stat =='general'){
				$dataSeries['Total Downloads'] =  GraphingUtils::getDataSeriesArray(count($dataSeries));
				$stats->selectAdd('SUM(numDownloads) as numDownloads');
			}
			if ($stat =='previews' || $stat =='general'){
				$dataSeries['Total Previews'] =  GraphingUtils::getDataSeriesArray(count($dataSeries));
				$stats->selectAdd('SUM(numPreviews) as numPreviews');
			}
			if ($stat =='optionUpdates' || $stat =='general'){
				$dataSeries['Total Options Updates'] =  GraphingUtils::getDataSeriesArray(count($dataSeries));
				$stats->selectAdd('SUM(numOptionsUpdates) as numOptionsUpdates');
			}
			if ($stat =='apiErrors' || $stat =='general'){
				$dataSeries['Total API Errors'] =  GraphingUtils::getDataSeriesArray(count($dataSeries));
				$stats->selectAdd('SUM(numApiErrors) as numApiErrors');
			}
			if ($stat =='connectionFailures' || $stat =='general'){
				$dataSeries['Total Connection Failures'] =  GraphingUtils::getDataSeriesArray(count($dataSeries));
				$stats->selectAdd('SUM(numConnectionFailures) as numConnectionFailures');
			}

			$stats->find();
			while ($stats->fetch()) {
				$curPeriod = "{$stats->month}-{$stats->year}";
				if ( $stat != 'general' || !in_array("{$stats->month}-{$stats->year}", $columnLabels)) {  // prevents the multiple addition of a curPeriod
					$columnLabels[] = $curPeriod;
				}
				if ($stat =='failedLoans' || $stat =='general'){
					/** @noinspection PhpUndefinedFieldInspection */
					$dataSeries['Total Failed Loans']['data'][$curPeriod] = $stats->numFailedCheckouts;
				}
				if ($stat =='failedHolds' || $stat =='general'){
					/** @noinspection PhpUndefinedFieldInspection */
					$dataSeries['Total Failed Holds']['data'][$curPeriod] = $stats->numFailedHolds;
				}
				if ($stat =='earlyReturns' || $stat =='general'){
					/** @noinspection PhpUndefinedFieldInspection */
					$dataSeries['Total Early Returns']['data'][$curPeriod] = $stats->numEarlyReturns;
				}
				if ($stat =='renewals' || $stat =='general'){
					/** @noinspection PhpUndefinedFieldInspection */
					$dataSeries['Total Renewals']['data'][$curPeriod] = $stats->numRenewals;
				}
				if ($stat =='holdsCancelled' || $stat =='general'){
					/** @noinspection PhpUndefinedFieldInspection */
					$dataSeries['Total Holds Cancelled']['data'][$curPeriod] = $stats->numHoldsCancelled;
				}
				if ($stat =='holdsFrozen' || $stat =='general'){
					/** @noinspection PhpUndefinedFieldInspection */
					$dataSeries['Total Holds Frozen']['data'][$curPeriod] = $stats->numHoldsFrozen;
				}
				if ($stat =='holdsThawed' || $stat =='general'){
					/** @noinspection PhpUndefinedFieldInspection */
					$dataSeries['Total Holds Thawed']['data'][$curPeriod] = $stats->numHoldsThawed;
				}
				if ($stat =='downloads' || $stat =='general'){
					/** @noinspection PhpUndefinedFieldInspection */
					$dataSeries['Total Downloads']['data'][$curPeriod] = $stats->numDownloads;
				}
				if ($stat =='previews' || $stat =='general'){
					/** @noinspection PhpUndefinedFieldInspection */
					$dataSeries['Total Previews']['data'][$curPeriod] = $stats->numPreviews;
				}
				if ($stat =='optionUpdates' || $stat =='general'){
					/** @noinspection PhpUndefinedFieldInspection */
					$dataSeries['Total Options Updates']['data'][$curPeriod] = $stats->numOptionsUpdates;
				}
				if ($stat =='apiErrors' || $stat =='general'){
					/** @noinspection PhpUndefinedFieldInspection */
					$dataSeries['Total API Errors']['data'][$curPeriod] = $stats->numApiErrors;
				}
				if ($stat =='connectionFailures' || $stat =='general'){
					/** @noinspection PhpUndefinedFieldInspection */
					$dataSeries['Total Connection Failures']['data'][$curPeriod] = $stats->numConnectionFailures;
				}
			}
		}

		// Load stats from overdrive_record_usage
		if ($stat =='recordsWithUsage' ||
			$stat =='holds' ||
			$stat =='loans' ||
			$stat == 'general') {		
			$recordUsage = new OverDriveRecordUsage();
			$recordUsage->groupBy('year, month');
			if (!empty($instanceName)) {
				$recordUsage->instance = $instanceName;
			}
			$recordUsage->selectAdd();
			$recordUsage->selectAdd('year');
			$recordUsage->selectAdd('month');
			$recordUsage->orderBy('year, month');

			if ($stat =='recordsWithUsage' || $stat =='general'){
				$recordUsage->selectAdd('COUNT(*) as numRecordsUsed');
				$dataSeries['Records Used'] = GraphingUtils::getDataSeriesArray(count($dataSeries));
			}
			if ($stat =='holds' || $stat =='general'){
				$recordUsage->selectAdd('SUM(timesHeld) as numHoldsPlaced');
				$dataSeries['Total Holds'] = GraphingUtils::getDataSeriesArray(count($dataSeries));
			}
			if ($stat =='loans' || $stat =='general'){
				$recordUsage->selectAdd('SUM(timesCheckedOut) as numCheckouts');
				$dataSeries['Total Checkouts'] = GraphingUtils::getDataSeriesArray(count($dataSeries));
			}

			$recordUsage->find();
			while ($recordUsage->fetch()) {
				$curPeriod = "{$recordUsage->month}-{$recordUsage->year}";
				if ( $stat != 'general' || !in_array("{$recordUsage->month}-{$recordUsage->year}", $columnLabels)) {  // prevents the multiple addition of a curPeriod
					$columnLabels[] = $curPeriod;
				}
				if ($stat =='recordsWithUsage' || $stat =='general'){
					/** @noinspection PhpUndefinedFieldInspection */
					$dataSeries['Records Used']['data'][$curPeriod] = $recordUsage->numRecordsUsed;
				}
				if ($stat =='holds' || $stat =='general'){
					/** @noinspection PhpUndefinedFieldInspection */
					$dataSeries['Total Holds']['data'][$curPeriod] = $recordUsage->numHoldsPlaced;
				}
				if ($stat =='loans' || $stat =='general'){
					/** @noinspection PhpUndefinedFieldInspection */
					$dataSeries['Total Checkouts']['data'][$curPeriod] = $recordUsage->numCheckouts;
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
			case 'failedLoans':
				$title .= ' - Failed Loans';
				break;
			case 'renewals':
				$title .= ' - Renewals';
				break;
			case 'earlyReturns':
				$title .= ' - Early Returns';
				break;
			case 'holds':
				$title .= ' - Holds';
				break;
			case 'failedHolds':
				$title .= ' - Failed Holds';
				break;
			case 'holdsCancelled':
				$title .= ' - Cancelled Holds';
				break;
			case 'holdsFrozen':
				$title .= ' - Holds Frozen';
				break;
			case 'holdsThawed':
				$title .= ' - Holds Thawed';
				break;
			case 'downloads':
				$title .= ' - Downloads';
				break;
			case 'previews':
				$title .= ' - Previews';
				break;
			case 'optionUpdates':
				$title .= ' - Option Updates';
				break;
			case 'apiErrors':
				$title .= ' - API Errors';
				break;
			case 'connectionFailures':
				$title .= ' - Connection Failures';
				break;
			case 'general':
				$title .= ' - General';
				break;
		}
		$interface->assign('graphTitle', $title);
	}
}