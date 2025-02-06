<?php
require_once ROOT_DIR . '/ResultsAction.php';
class BmjBp_Results extends ResultsAction {
	function launch() {
		global $interface;

		/** @var SearchObject_BmjBpSearcher $searchObject */
		$searchObject = SearchObjectFactory::initSearchObject("BmjBp");
		$searchObject->init();
		$result = $searchObject->processSearch();
	}

	function getBreadcrumbs(): array {
		return parent::getResultsBreadcrumbs('Articles & Databases');
	}
}