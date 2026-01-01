<?php

require_once ROOT_DIR . '/services/Admin/Admin.php';
require_once ROOT_DIR . '/sys/PalaceProject/PalaceProjectSetting.php';
require_once ROOT_DIR . '/sys/PalaceProject/PalaceProjectScope.php';
require_once ROOT_DIR . '/sys/PalaceProject/PalaceProjectTitleAvailability.php';

class PalaceProject_CollectionReport extends Admin_Admin {
	function launch() : void {
		global $interface;
		$palaceProjectScope = new PalaceProjectScope();
		/** @var PalaceProjectScope[] $palaceProjectScopes */
		$palaceProjectScopes = $palaceProjectScope->fetchAll(null, null, false, true);

		/** @var PalaceProjectSetting[] $palaceProjectScopes */
		$palaceProjectSetting = new PalaceProjectSetting();
		$palaceProjectSettings = $palaceProjectSetting->fetchAll(null, null, false, true);

		//Create a report of the number of active titles by library and collection within the library
		$library = new Library();
		$library->whereAdd('palaceProjectScopeId > 0');
		$library->orderBy('displayName');
		$library->selectAdd(null);
		$library->selectAdd('libraryId');
		$library->selectAdd('displayName');
		$library->selectAdd('palaceProjectScopeId');
		$library->find();
		$activePalaceProjectLibraries = $library->fetchAll();
		$allLibraries = [];
		foreach ($activePalaceProjectLibraries as $library) {
			$libraryInfo = [
				'libraryId' => $library->libraryId,
				'displayName' => $library->displayName,
				'palaceProjectScopeId' => $library->palaceProjectScopeId
			];
			$activeScope = $palaceProjectScopes[$library->palaceProjectScopeId];
			/** @var PalaceProjectSetting $activeSetting */
			$activeSetting = $palaceProjectSettings[$activeScope->settingId];
			$allCollectionObjects = $activeSetting->getCollections();
			$allCollections = [];
			foreach ($allCollectionObjects as $collectionObject) {
				$titleAvailability = new PalaceProjectTitleAvailability();
				$titleAvailability->collectionId = $collectionObject->id;
				$titleAvailability->deleted = 0;
				$numTitles = $titleAvailability->count();
				$titleAvailability->deleted = 1;
				$numDeletedTitles = $titleAvailability->count();
				$titleAvailability->deleted = 0;
				$titleAvailability->needsHold = 1;
				$numNeedingHolds = $titleAvailability->count();
				$allCollections[] = [
					'palaceProjectName' => $collectionObject->palaceProjectName,
					'displayName' => $collectionObject->displayName,
					'numTitles' => $numTitles,
					'numDeletedTitles' => $numDeletedTitles,
					'numNeedingHolds' => $numNeedingHolds,
				];
			}
			$libraryInfo['collections'] = $allCollections;
			$allLibraries[] = $libraryInfo;
		}
		$interface->assign('allLibraries', $allLibraries);

		$this->display('collectionReport.tpl', 'Collection Report');
	}

	function getBreadcrumbs(): array {
		$breadcrumbs = [];
		$breadcrumbs[] = new Breadcrumb('/Admin/Home', 'Administration Home');
		$breadcrumbs[] = new Breadcrumb('/Admin/Home#palace_project', 'Palace Project');
		$breadcrumbs[] = new Breadcrumb('/PalaceProject/CollectionReport', 'Collection Report');
		return $breadcrumbs;
	}

	function getActiveAdminSection(): string {
		return 'palace_project';
	}

	function canView(): bool {
		return UserAccount::userHasPermission(['Administer Palace Project', 'View System Reports']);
	}
}