<?php
require_once ROOT_DIR . '/JSON_Action.php';
class OpenArchives_AJAX extends JSON_Action {
public function exportUsageData() {
		require_once ROOT_DIR . '/services/OpenArchives/UsageGraphs.php';
		$openArchivesUsageGraph = new OpenArchives_UsageGraphs();
		$openArchivesUsageGraph->buildCSV('OpenArchives');
	}
}