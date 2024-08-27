<?php
require_once ROOT_DIR . '/JSON_Action.php';
class EBSCOhost_AJAX extends JSON_Action {
public function exportUsageData() {
		require_once ROOT_DIR . '/services/EBSCOhost/UsageGraphs.php';
		$EBSCOhostUsageGraph = new EBSCOhost_UsageGraphs();
		$EBSCOhostUsageGraph->buildCSV('EBSCOhost');
	}
}