<?php

require_once ROOT_DIR . '/Action.php';
require_once ROOT_DIR . '/services/Admin/Dashboard.php';
require_once ROOT_DIR . '/sys/PalaceProject/UserPalaceProjectUsage.php';
require_once ROOT_DIR . '/sys/PalaceProject/PalaceProjectRecordUsage.php';

class PalaceProject_Dashboard extends Admin_Dashboard {
	function launch() : void {
		global $interface;
		$instanceName = $this->loadInstanceInformation('UserPalaceProjectUsage');
		$this->loadDates();

		$activeUsersThisMonth = $this->getUserStats($instanceName, $this->thisMonth, $this->thisYear);
		$interface->assign('activeUsersThisMonth', $activeUsersThisMonth);
		$activeUsersLastMonth = $this->getUserStats($instanceName, $this->lastMonth, $this->lastMonthYear);
		$interface->assign('activeUsersLastMonth', $activeUsersLastMonth);
		$activeUsersThisYear = $this->getUserStats($instanceName, null, $this->thisYear);
		$interface->assign('activeUsersThisYear', $activeUsersThisYear);
		$activeUsersLastYear = $this->getUserStats($instanceName, null, $this->lastYear);
		$interface->assign('activeUsersLastYear', $activeUsersLastYear);
		$activeUsersAllTime = $this->getUserStats($instanceName, null, null);
		$interface->assign('activeUsersAllTime', $activeUsersAllTime);

		[
			$activeRecordsThisMonth,
			$loansThisMonth,
			$holdsThisMonth,
		] = $this->getRecordStats($instanceName, $this->thisMonth, $this->thisYear);
		$interface->assign('activeRecordsThisMonth', $activeRecordsThisMonth);
		$interface->assign('loansThisMonth', $loansThisMonth);
		$interface->assign('holdsThisMonth', $holdsThisMonth);
		[
			$activeRecordsLastMonth,
			$loansLastMonth,
			$holdsLastMonth,
		] = $this->getRecordStats($instanceName, $this->lastMonth, $this->lastMonthYear);
		$interface->assign('activeRecordsLastMonth', $activeRecordsLastMonth);
		$interface->assign('loansLastMonth', $loansLastMonth);
		$interface->assign('holdsLastMonth', $holdsLastMonth);
		[
			$activeRecordsThisYear,
			$loansThisYear,
			$holdsThisYear,
		] = $this->getRecordStats($instanceName, null, $this->thisYear);
		$interface->assign('activeRecordsThisYear', $activeRecordsThisYear);
		$interface->assign('loansThisYear', $loansThisYear);
		$interface->assign('holdsThisYear', $holdsThisYear);
		[
			$activeRecordsLastYear,
			$loansLastYear,
			$holdsLastYear,
		] = $this->getRecordStats($instanceName, null, $this->lastYear);
		$interface->assign('activeRecordsLastYear', $activeRecordsLastYear);
		$interface->assign('loansLastYear', $loansLastYear);
		$interface->assign('holdsLastYear', $holdsLastYear);
		[
			$activeRecordsAllTime,
			$loansAllTime,
			$holdsAllTime,
		] = $this->getRecordStats($instanceName, null, null);
		$interface->assign('activeRecordsAllTime', $activeRecordsAllTime);
		$interface->assign('loansAllTime', $loansAllTime);
		$interface->assign('holdsAllTime', $holdsAllTime);

		$this->display('dashboard.tpl', 'Palace Project Dashboard');
	}

	/**
	 * @param string|null $instanceName
	 * @param string|null $month
	 * @param string|null $year
	 * @return int
	 */
	public function getUserStats(?string $instanceName, ?string $month, ?string $year): int {
		$userUsage = new UserPalaceProjectUsage();
		if (!empty($instanceName)) {
			$userUsage->instance = $instanceName;
		}
		if ($month != null) {
			$userUsage->month = $month;
		}
		if ($year != null) {
			$userUsage->year = $year;
		}
		return $userUsage->count();
	}


	/**
	 * @param string|null $instanceName
	 * @param string|null $month
	 * @param string|null $year
	 * @return array
	 */
	public function getRecordStats(?string $instanceName, ?string $month, ?string $year): array {
		$usage = new PalaceProjectRecordUsage();
		if (!empty($instanceName)) {
			$usage->instance = $instanceName;
		}
		if ($month != null) {
			$usage->month = $month;
		}
		if ($year != null) {
			$usage->year = $year;
		}

		$usage->selectAdd();
		$usage->selectAdd('COUNT(DISTINCT palaceProjectId) as numRecordsUsed');
		$usage->selectAdd('SUM(timesCheckedOut) as numCheckouts');
		$usage->selectAdd('SUM(timesHeld) as numHolds');

		$usage->find(true);
		return [
			$usage->numRecordsUsed ?? 0,
			$usage->numCheckouts ?? 0,
			$usage->numHolds ?? 0,
		];
	}

	function getBreadcrumbs(): array {
		$breadcrumbs = [];
		$breadcrumbs[] = new Breadcrumb('/Admin/Home', 'Administration Home');
		$breadcrumbs[] = new Breadcrumb('/Admin/Home#palace_project', 'Palace Project');
		$breadcrumbs[] = new Breadcrumb('/PalaceProject/Dashboard', 'Usage Dashboard');
		return $breadcrumbs;
	}

	function getActiveAdminSection(): string {
		return 'palace_project';
	}

	function canView(): bool {
		return UserAccount::userHasPermission([
			'View Dashboards',
			'View System Reports',
		]);
	}
}