<?php
require_once ROOT_DIR . '/JSON_Action.php';
class EBSCO_AJAX extends JSON_Action {
public function exportUsageData() {
		require_once ROOT_DIR . '/services/EBSCO/EDSUsageGraphs.php';
		$EBSCOEDSUsageGraph = new EBSCO_EDSUsageGraphs();
		$EBSCOEDSUsageGraph->buildCSV('EDS');
	}
}