<?php

require_once ROOT_DIR . '/ResultsAction.php';

class Gale_Results extends ResultsAction {
	function launch() {
		global $interface;
		global $timer;
//		global $aspenUsage;

//		$aspenUsage->galeSearches++;

		//Set default sort by setting the request variable so the init grabs it
		if (!array_key_exists('sort', $_REQUEST) && UserAccount::isLoggedIn()) {
			$userId = UserAccount::getActiveUserId();
			require_once ROOT_DIR . '/sys/User/PageDefaults.php';
			$pageDefaults = PageDefaults::getPageDefaultsForUser($userId, 'Gale', 'Results', null);
			if ($pageDefaults != null) {
				$_REQUEST['sort'] = $pageDefaults->pageSort;
			}
		}

		//Include Search Engine
		/** @var SearchObject_GaleSearcher $searchObject */
		$searchObject = SearchObjectFactory::initSearchObject("Gale");
		$timer->logTime('Include search engine');

		$this->setShowCovers();

		$searchObject->init();
		$result = $searchObject->processSearch(true, true);
		if ($result instanceof AspenError) {
			global $serverName;
			$logSearchError = true;
			if ($logSearchError) {
				try {
					require_once ROOT_DIR . '/sys/SystemVariables.php';
					$systemVariables = new SystemVariables();
					if ($systemVariables->find(true) && !empty($systemVariables->searchErrorEmail)) {
						require_once ROOT_DIR . '/sys/Email/Mailer.php';
						$mailer = new Mailer();
						$emailErrorDetails = $_SERVER['REQUEST_URI'] . "\n" . $result->message;
						$mailer->send($systemVariables->searchErrorEmail, "$serverName Error processing EBSCOhost search", $emailErrorDetails);
					}
				} catch (Exception $e) {
					
				}
			}

			$interface->assign('searchError', $result);
			$this->display('searchError.tpl', 'Error in Search');
			return;
		}

		$displayQuery = $searchObject->displayQuery();
		$pageTitle = $displayQuery;
		if (strlen($pageTitle) > 20) {
			$pageTitle = substr($pageTitle, 0, 20) . '...';
		}

		$interface->assign('lookfor', $displayQuery);

		$recordSet = $searchObject->getResultRecordHTML();
		$interface->assign('recordSet', $recordSet);
		$timer->logTime('load result records');

		$interface->assign('sortList', $searchObject->getSortList());
		$interface->assign('searchIndex', $searchObject->getSearchIndex());

		$summary = $searchObject->getResultSummary();
		$interface->assign('recordCount', $summary['resultTotal']);
		$interface->assign('recordStart', $summary['startRecord']);
		$interface->assign('recordEnd', $summary['endRecord']);

		$filterList = $searchObject->getFilterList();
		$interface->assign('filterList', $filterList);
		$limitList = $searchObject->getLimitList();
		$interface->assign('limitList', $limitList);
		$facetSet = $searchObject->getFacetSet();
		$interface->assign('sideFacetSet', $facetSet);
		$interface->assign('hasSearchableFacets', $searchObject->hasSearchableFacets());
		global $library;
		$facetCountsToShow = $library->getGroupedWorkDisplaySettings()->facetCountsToShow;
		$interface->assign('facetCountsToShow', $facetCountsToShow);

		if ($summary['resultTotal'] > 0) {
			$link = $searchObject->renderLinkPageTemplate();
			$options = [
				'totalItems' => $summary['resultTotal'],
				'fileName' => $link,
				'perPage' => $summary['perPage'],
			];
			$pager = new Pager($options);
			$interface->assign('pageLinks', $pager->getLinks());
		}

		$searchObject->close();
		$interface->assign('searchSource', 'gale');
		$interface->assign('searchId', $searchObject->getSearchId());
		$interface->assign('savedSearch', $searchObject->isSavedSearch());

		$_SESSION['lastSearchId'] = $searchObject->getSearchId();
		$_SESSION['lastSearchURL'] = $searchObject->renderSearchUrl();

		$interface->assign('subpage', 'Gale/list-list.tpl');
		$interface->assign('sectionLabel', 'Gale Databases');
		$sidebar = ($searchObject->getResultTotal() > 0 || $searchObject->hasAppliedFacets()) ? 'Gale/results-sidebar.tpl' : '';
		$this->display($summary['resultTotal'] > 0 ? 'list.tpl' : 'list-none.tpl', $pageTitle, $sidebar, false);
	}

	function getBreadcrumbs(): array {
		return parent::getResultsBreadcrumbs('Gale Database');
	}
}
