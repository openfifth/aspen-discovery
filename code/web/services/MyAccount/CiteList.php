<?php

require_once ROOT_DIR . '/Action.php';
require_once ROOT_DIR . '/sys/UserLists/UserList.php';

class CiteList extends Action {
	private ?string $listId;
	private ?string $listTitle;

	function launch() : void {
		global $interface;

		//Get all lists for the user

		// Fetch List object
		$list = null;
		if (isset($_REQUEST['listId'])) {
			$list = new UserList();
			$list->id = $_GET['listId'];
			if ($list->find(true)) {
				$this->listId = $list->id;
				$this->listTitle = $list->title;
			}else{
				$list = null;
			}
		}
		if ($list == null) {
			AspenError::raiseError("Invalid List ID provided when generating citations.");
		}
		$interface->assign('favList', $list);
		$_GET['id'] = $list->id;
		$selectedResourceTypes = empty($_REQUEST['selectedResourceTypes']) ? [] : explode('|',$_REQUEST['selectedResourceTypes']);
		$activeFilters = empty($_REQUEST['activeFilters']) ? [] : explode('|',$_REQUEST['activeFilters']);

		// Get all titles on the list
		$citationFormat = $_REQUEST['citationFormat'];
		$citationFormats = CitationBuilder::getCitationFormats();
		$interface->assign('citationFormat', $citationFormats[$citationFormat]);
		if (count($selectedResourceTypes) && in_array('GroupedWork', $selectedResourceTypes) && !empty($activeFilters)) {
			$titleDetailInfo = $list->getListRecordsUsingSolr(0, -1, false, 'citations', $citationFormat, null, $activeFilters);
			$citations = $titleDetailInfo['formattedRecords'];
		}else{
			$citations = $list->getListRecords(0, -1, false, 'citations', $citationFormat, null, false, 0, $selectedResourceTypes);
		}

		$interface->assign('citations', $citations);

		// Display Page
		$interface->assign('listId', $list->id);
		$pageTitle = translate([
			'text' => 'Citations for %1%',
			'1' => $list->title,
		]);
		$this->display('listCitations.tpl', $pageTitle, '', false);
	}

	function getBreadcrumbs(): array {
		$breadcrumbs = [];
		$breadcrumbs[] = new Breadcrumb('/MyAccount/Home', 'Your Account');
		if (!empty($this->listId)) {
			$breadcrumbs[] = new Breadcrumb('/MyAccount/MyList/' . $this->listId, $this->listTitle);
		}
		$breadcrumbs[] = new Breadcrumb('', 'Citations');
		return $breadcrumbs;
	}
}