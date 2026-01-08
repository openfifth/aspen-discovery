<?php

require_once ROOT_DIR . '/services/Admin/ObjectEditor.php';
require_once ROOT_DIR . '/sys/AspenMobile/Setting.php';

class AspenMobile_Settings extends ObjectEditor {
	function getObjectType(): string {
		return 'AspenMobileSetting';
	}

	function getToolName(): string {
		return 'Settings';
	}

	function getModule(): string {
		return 'AspenMobile';
	}

	function getPageTitle(): string {
		return 'Aspen Mobile Settings';
	}

	function getAllObjects($page, $recordsPerPage): array {
		$list = [];

		$object = new AspenMobileSetting();
		$object->orderBy($this->getSort());
		$this->applyFilters($object);
		$object->limit(($page - 1) * $recordsPerPage, $recordsPerPage);
		$object->find();
		while ($object->fetch()) {
			$list[$object->id] = clone $object;
		}

		return $list;
	}

	function getDefaultSort(): string {
		return 'name asc';
	}

	function getObjectStructure($context = ''): array {
		return AspenMobileSetting::getObjectStructure($context);
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
		$breadcrumbs[] = new Breadcrumb('/Admin/Home#aspen-mobile', 'Aspen Mobile');
		$breadcrumbs[] = new Breadcrumb('/AspenMobile/Settings', 'Aspen Mobile Settings');
		return $breadcrumbs;
	}

	function getActiveAdminSection(): string {
		return 'aspenMobile';
	}

	function canView(): bool {
		// TODO should we change this to a Aspen Mobile specific Permission?
		return UserAccount::userHasPermission('Administer Aspen LiDA Settings');
	}
}