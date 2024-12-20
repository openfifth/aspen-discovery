<?php

require_once ROOT_DIR . '/Action.php';
require_once ROOT_DIR . '/sys/OCLCRSFG/OCLCRSFGForm.php';
require_once ROOT_DIR . '/services/Admin/ObjectEditor.php';

class OCLCRSFG_OCLCRSFGForms extends ObjectEditor {
	function getObjectType(): string {
		return 'OCLCRSFGForm';
	}

	function getToolName(): string {
		return 'OCLCRSFGForms';
	}

	function getModule(): string {
		return 'OCLCRSFG';
	}

	function getPageTitle(): string {
		return 'OCLC Resource Sharing For Groups Forms';
	}

	function getAllObjects($page, $recordsPerPage): array {
		$object = new OCLCRSFGForm();
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
		return OCLCRSFGForm::getObjectStructure($context);
	}

	function getPrimaryKeyColumn(): string {
		return 'id';
	}

	function getIdKeyColumn(): string {
		return 'id';
	}

	function getBreadcrumbs(): array {
		$breadcrumbs = [];
		$breadcrumbs[] = new Breadcrumb('/Admin/Home', 'Administration Home');
		$breadcrumbs[] = new Breadcrumb('/Admin/Home#ill_integration', 'Interlibrary Loan');
		$breadcrumbs[] = new Breadcrumb('/OCLCRSFG/OCLCRSFGForms', 'OCLC Resource Sharing For Groups Forms');
		return $breadcrumbs;
	}

	function getActiveAdminSection(): string {
		return 'ill_integration';
	}

	function canView(): bool {
		return UserAccount::userHasPermission([
			'Administer OCLC Resource Sharing For Groups Forms',
		]);
	}

	function canBatchEdit(): bool {
		return UserAccount::userHasPermission([
			'Administer OCLC Resource Sharing For Groups Forms',
		]);
	}
}