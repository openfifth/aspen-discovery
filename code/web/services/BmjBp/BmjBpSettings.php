<?php

require_once ROOT_DIR . '/Action.php';
require_once ROOT_DIR . '/sys/BmjBp/BmjBpSetting.php';
require_once ROOT_DIR . '/services/Admin/ObjectEditor.php';


class BmjBpSettings extends ObjectEditor {	
	function getObjectType(): string {
		return 'BmjBpSetting';
	}

	function getToolName(): string {
		return 'BmjBpSettings';
	}

	function getModule(): string {
		return 'BmjBp';
	}

	function getPageTitle(): string {
		return 'BMJ Best Practice Settings';
	}

	function getAllObjects($page, $recordsPerPage): array {
		$object = new BmjBpSetting();
		$object->limit(($page - 1) * $recordsPerPage, $recordsPerPage);
		$this->applyFilters($object);
		$object->orderBy($this->getSort());
		$object->find();
		$objectList = [];
		while ($object->fetch()) {
			$objectList[$object->id] = clone $object;
		}
		return $objectList;
	}

	function getDefaultSort(): string {
		return 'name asc';
	}

	function getObjectStructure($context = ''): array {
		return BmjBpSetting::getObjectStructure($context);
	}

	function getPrimaryKeyColumn(): string {
		return 'id';
	}

	function getIdKeyColumn(): string {
		return 'id';
	}

	function getAdditionalObjectActions($existingObject): array {
		return [];
	}

	function getInstructions(): string {
		return 'https://help.aspendiscovery.org/bmjbp';
	}

	function getBreadcrumbs(): array {
		$breadcrumbs = [];
		$breadcrumbs[] = new Breadcrumb('/Admin/Home', 'Administration Home');
		$breadcrumbs[] = new Breadcrumb('/Admin/Home#bmjbp', 'BmjBp');
		$breadcrumbs[] = new Breadcrumb('/BmjBp/BmjBpSettings', 'Settings');
		return $breadcrumbs;
	}

	function getActiveAdminSection(): string {
		return 'bmjbp';
	}

	function canView(): bool {
		return UserAccount::userHasPermission('View Dashboards');
	}
}