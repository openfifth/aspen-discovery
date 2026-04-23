<?php

require_once ROOT_DIR . '/Action.php';
require_once(ROOT_DIR . '/services/Admin/Admin.php');
require_once(ROOT_DIR . '/sys/MaterialsRequests/MaterialsRequest.php');
require_once(ROOT_DIR . '/sys/MaterialsRequests/MaterialsRequestTitle.php');
require_once(ROOT_DIR . '/sys/MaterialsRequests/MaterialsRequestStatus.php');
require_once(ROOT_DIR . '/sys/Administration/StickyFilter.php');
require_once ROOT_DIR . '/sys/User/PageDefaults.php';

class MaterialsRequest_ManageTitleRequests extends Admin_Admin {

	function launch() : void {
		global $interface;
		global $aspen_db;
		$homeLibrary = Library::getPatronHomeLibrary();
		$user = UserAccount::getLoggedInUser();

		$interface->assign('showExistingTitleInformation', $homeLibrary->checkRequestsForExistingTitles);

		//Get a list of all material requests for the user
		$allRequests = [];
		if ($user) {

			$materialsRequestTitles = new MaterialsRequestTitle();

			if (isset($_REQUEST['pageSize']) && (is_numeric($_REQUEST['pageSize']) || $_REQUEST['pageSize'] == 'all')) {
				$materialsRequestTitlessPerPage =  $_REQUEST['pageSize'];
				PageDefaults::updatePageDefaultsForUser($user->id, 'MaterialsRequest', 'ManageTitleRequests',null, $materialsRequestTitlessPerPage, null);
			} else {
				$pageDefaults = PageDefaults::getPageDefaultsForUser($user->id, 'MaterialsRequest', 'ManageRequests',null);
				if ($pageDefaults !== null && !empty($pageDefaults->pageSize)) {
					$materialsRequestTitlessPerPage =  $pageDefaults->pageSize;
				}else{
					$materialsRequestTitlessPerPage = 30;
				}
			}
			if($materialsRequestTitlessPerPage == 'all') {
				$materialsRequestTitlessPerPage = $materialsRequestTitles->count();
				$interface->assign('showingAllRequests', true);
			} else {
				$interface->assign('showingAllRequests', false);
			}
			$interface->assign('materialsRequestsPerPage', $materialsRequestTitlessPerPage);
			$page = $_REQUEST['page'] ?? 1;
			if (!isset($_REQUEST['exportAll'])) {
				$materialsRequestTitles->limit(((int)$page - 1) * (int)$materialsRequestTitlessPerPage,(int)$materialsRequestTitlessPerPage);
			}
			$materialsRequestTitleCount = $materialsRequestTitles->count();

			if ($materialsRequestTitles->find()) {
				$stmt = $aspen_db->query("SELECT mrt.*, COUNT(mr.id) as numRequests 
							FROM materials_request_title mrt
    						LEFT JOIN materials_request mr ON mrt.id = mr.materialsRequestTitleId
    						GROUP BY mrt.id
    						ORDER BY mrt.dateLastRequested DESC
    						");
				$allRequests = $stmt->fetchAll(PDO::FETCH_CLASS, 'MaterialsRequestTitle');
			}

			$options = [
				'totalItems' => $materialsRequestTitleCount,
				'perPage' => $materialsRequestTitlessPerPage,
			];

			$pager = new Pager($options);

			$interface->assign('pageLinks', $pager->getLinks());

			$columnsToDisplay = [
				'id' => 'Id',
				'title' => 'Title',
				'author' => 'Author',
				'format' => 'Format',
				'dateFirstRequested' => 'First Requested',
				'dateLastRequested' => 'Last Requested',
				'numRequests' => 'Number of Requests',
			];
			$interface->assign('columnsToDisplay', $columnsToDisplay);

			// Find Date Columns for Javascript Table sorter
			$dateColumns = [];
			foreach (array_keys($columnsToDisplay) as $index => $column) {
				if (in_array($column, [
					'dateFirstRequested',
					'dateLastRequested',
				])) {
					$dateColumns[] = $index;
				}
			}
			$interface->assign('dateColumns', $dateColumns); //data gets added within template

		} else {
			$interface->assign('error', "You must be logged in to manage requests.");
		}
		$interface->assign('allRequests', $allRequests);


		$this->display('manageTitleRequests.tpl', 'Manage Materials Requests');

	}

	function getBreadcrumbs(): array {
		$breadcrumbs = [];
		$breadcrumbs[] = new Breadcrumb('/MaterialsRequest/ManageTitleRequests', 'Manage Materials Requests');
		return $breadcrumbs;
	}

	function getActiveAdminSection(): string {
		return 'materials_request';
	}

	function canView(): bool {
		return UserAccount::userHasPermission('Manage Library Materials Requests');
	}
}
