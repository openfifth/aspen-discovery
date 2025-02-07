<?php
require_once ROOT_DIR . '/ResultsAction.php';
class BmjBp_Results extends ResultsAction {
	function launch() {
		global $interface;

		/** @var SearchObject_BmjBpSearcher $searchObject */
		$searchObject = SearchObjectFactory::initSearchObject("BmjBp");
		$searchObject->init();
		$result = $searchObject->processSearch();
		$displayQuery = $searchObject->displayQuery();
		$recordSet = $searchObject->getResultRecordHTML();
		$summary = $searchObject->getResultSummary();

		if (strlen($displayQuery ) > 20) {
			$displayQuery  = substr($displayQuery , 0, 20) . '...';
		}
		if ($summary['resultTotal'] > 0) {
			$this->implementPagination($searchObject, $summary);
		}

		$interface->assign('lookfor', $displayQuery);
		$interface->assign('recordSet', $recordSet);
		$interface->assign('subpage', '../Search/list-list.tpl');
		$interface->assign('sectionLabel', 'BMJ Best Practice');
		$this->display($summary['resultTotal'] > 0 ? '../BmjBp/list.tpl' : '../Search/list-none.tpl', $displayQuery , false, false);
	}

	function implementPagination($searchObject, $summary) {
		global $interface;
		$link = $searchObject->renderLinkPageTemplate();
		$options = [
			'totalItems' => $summary['resultTotal'],
			'fileName' => $link,
			'perPage' => $summary['perPage'],
		];
		$pager = new Pager($options);
		$interface->assign('pageLinks', $pager->getLinks());
	}

	function getBreadcrumbs(): array {
		return parent::getResultsBreadcrumbs('Articles & Databases');
	}
}