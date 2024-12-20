<?php

require_once ROOT_DIR . '/Action.php';
require_once ROOT_DIR . '/services/Admin/ObjectEditor.php';
require_once ROOT_DIR . '/sys/OCLCRSFG/OCLCRSFGSetting.php';

class OCLCRSFG_OCLCRSFGSettings extends ObjectEditor {
	function getObjectType(): string {
		return 'OCLCRSFGSetting';
	}

	function getToolName(): string {
		return 'OCLCRSFGSettings';
	}

	function getModule(): string {
		return 'OCLCRSFG';
	}

	function getPageTitle(): string {
		return 'OCLC Resource Sharing For Groups Settings';
	}

	function getAllObjects($page, $recordsPerPage): array {
		$object = new OCLCRSFGSetting();
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
		return 'id asc';
	}

	function getObjectStructure($context = ''): array {
		return OCLCRSFGSetting::getObjectStructure($context);
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
		return '';
	}

	function getBreadcrumbs(): array {
		$breadcrumbs = [];
		$breadcrumbs[] = new Breadcrumb('/Admin/Home', 'Administration Home');
		$breadcrumbs[] = new Breadcrumb('/Admin/Home#ill_integration', 'Interlibrary Loan');
		$breadcrumbs[] = new Breadcrumb('/OCLCRSFG/OCLCRSFGSettings', 'OCLC Resource Sharing For Groups Settings');
		return $breadcrumbs;
	}

	function getActiveAdminSection(): string {
		return 'ill_integration';
	}

	function canView(): bool {
		return UserAccount::userHasPermission([
			'Administer OCLC Resource Sharing For Groups Settings',
		]);
	}

	function canBatchEdit(): bool {
		return UserAccount::userHasPermission([
			'Administer OCLC Resource Sharing For Groups Settings',
		]);
	}
}