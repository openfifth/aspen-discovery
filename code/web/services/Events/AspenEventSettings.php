<?php

require_once ROOT_DIR . '/Action.php';
require_once ROOT_DIR . '/services/Admin/ObjectEditor.php';
require_once ROOT_DIR . '/sys/Events/AspenEventSetting.php';

class Events_AspenEventSettings extends ObjectEditor {

	/**
	 * The class name of the object which is being edited
	 */
	function getObjectType(): string {
		return 'AspenEventSetting';
	}

	/**
	 * The page name of the tool (typically the plural of the object)
	 */
	function getToolName(): string {
		return 'AspenEventSettings';
	}

	function getModule(): string {
		return 'Events';
	}

	/**
	 * The title of the page to be displayed
	 */
	function getPageTitle(): string {
		return 'Aspen Event Settings';
	}

	/**
	 * Load all objects into an array keyed by the primary key
	 */
	function getAllObjects($page, $recordsPerPage): array {
		$object = new AspenEventSetting();
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

	/**
	 * Define the properties which are editable for the object
	 * as well as how they should be treated while editing, and a description for the property
	 */
	function getObjectStructure($context = ''): array {
		return AspenEventSetting::getObjectStructure($context);
	}

	/**
	 * The name of the column which defines this as unique
	 */
	function getPrimaryKeyColumn(): string {
		return 'id';
	}

	/**
	 * The id of the column which serves to join other columns
	 */
	function getIdKeyColumn(): string {
		return 'id';
	}

	function getBreadcrumbs(): array {
		$breadcrumbs = [];
		$breadcrumbs[] = new Breadcrumb('/Admin/Home', 'Administration Home');
		$breadcrumbs[] = new Breadcrumb('/Admin/Home#events', 'Events');
		$breadcrumbs[] = new Breadcrumb('/Events/AspenSettings', 'Aspen Settings');
		return $breadcrumbs;
	}

	function canView(): bool {
		return UserAccount::userHasPermission('Administer Events for All Locations');
	}

	function getActiveAdminSection(): string {
		return 'events';
	}
}