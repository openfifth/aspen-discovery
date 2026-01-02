<?php

require_once ROOT_DIR . '/services/Admin/IndexingLog.php';
require_once ROOT_DIR . '/sys/PalaceProject/PalaceProjectLogEntry.php';
require_once ROOT_DIR . '/sys/PalaceProject/PalaceProjectSetting.php';

class PalaceProject_IndexingLog extends Admin_IndexingLog {
	function launch() : void {
		global $interface;
		$setting = new PalaceProjectSetting();
		$settings = $setting->fetchAll('id', 'name');
		$interface->assign('settings', $settings);
		parent::launch();
	}

	function getIndexLogEntryObject(): BaseLogEntry {
		return new PalaceProjectLogEntry();
	}

	function getTemplateName(): string {
		return 'palaceProjectExportLog.tpl';
	}

	function getTitle(): string {
		return 'Palace Project Export Log';
	}

	function getModule(): string {
		return 'PalaceProject';
	}

	function applyMinProcessedFilter(DataObject $indexingObject, $minProcessed) : void {
		if ($indexingObject instanceof PalaceProjectLogEntry) {
			$indexingObject->whereAdd('numProducts >= ' . $minProcessed);
		}
	}

	/**
	 * Apply any additional filters that are custom to the log being viewed.
	 *
	 * @param DataObject $logEntry
	 * @return void
	 */
	function applyAdditionalFilters(DataObject $logEntry) : void {
		if ($logEntry instanceof PalaceProjectLogEntry) {
			global $interface;
			$interface->assign('selectedSetting', -1);
			if (isset($_REQUEST['settingToShow'])) {
				if ($_REQUEST['settingToShow'] != -1 && is_numeric($_REQUEST['settingToShow'])) {
					$logEntry->settingId = $_REQUEST['settingToShow'];
					$interface->assign('selectedSetting', $_REQUEST['settingToShow']);
				}
			}
		}
	}

	function getBreadcrumbs(): array {
		$breadcrumbs = [];
		$breadcrumbs[] = new Breadcrumb('/Admin/Home', 'Administration Home');
		$breadcrumbs[] = new Breadcrumb('/Admin/Home#palace_project', 'Palace Project');
		$breadcrumbs[] = new Breadcrumb('', 'Indexing Log');
		return $breadcrumbs;
	}

	function getActiveAdminSection(): string {
		return 'palace_project';
	}
}
