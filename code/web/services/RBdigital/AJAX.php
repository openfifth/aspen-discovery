<?php
require_once ROOT_DIR . '/JSON_Action.php';
class RBdigital_AJAX extends JSON_Action {
	public function exportUsageData() {
		require_once ROOT_DIR . '/services/RBdigital/UsageGraphs.php';
		$RBdigitalUsageGraph = new RBdigital_UsageGraphs(); 
		$RBdigitalUsageGraph->buildCSV('RBdigital');
	}
}