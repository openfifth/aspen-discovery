<?php

require_once ROOT_DIR . '/services/Admin/IndexingLog.php';
require_once ROOT_DIR . '/sys/Hoopla/HooplaExportLogEntry.php';

class Hoopla_IndexingLog extends Admin_IndexingLog {
	function getIndexLogEntryObject(): BaseLogEntry {
		return new HooplaExportLogEntry();
	}

	function getTemplateName(): string {
		return 'hooplaExportLog.tpl';
	}

	function getTitle(): string {
		return 'Hoopla Export Log';
	}

	function getModule(): string {
		return 'Hoopla';
	}

	function applyMinProcessedFilter(DataObject $indexingObject, $minProcessed) {
		if ($indexingObject instanceof HooplaExportLogEntry) {
			$indexingObject->whereAdd('numProducts >= ' . $minProcessed);
		}
	}

	function getBreadcrumbs(): array {
		$breadcrumbs = [];
		$breadcrumbs[] = new Breadcrumb('/Admin/Home', 'Administration Home');
		$breadcrumbs[] = new Breadcrumb('/Admin/Home#hoopla', 'Hoopla');
		$breadcrumbs[] = new Breadcrumb('', 'Indexing Log');
		return $breadcrumbs;
	}

	function getActiveAdminSection(): string {
		return 'hoopla';
	}

	function launch() : void {
		global $interface;

		// Detect Hoopla version and pass it to the template
		require_once ROOT_DIR . '/sys/SystemVariables.php';
		$systemVariables = SystemVariables::getSystemVariables();
		$hooplaVersion2 = ($systemVariables !== false && !empty($systemVariables->hooplaVersion) && (int)$systemVariables->hooplaVersion == 2);

		$interface->assign('hooplaVersion2', $hooplaVersion2);
		parent::launch();
	}
}
