<?php

require_once ROOT_DIR . '/Action.php';
require_once ROOT_DIR . '/services/Admin/Admin.php';
require_once ROOT_DIR . '/services/Admin/ObjectEditor.php';
require_once ROOT_DIR . '/sys/HeroSlider/HeroSliderPlaylist.php';

class Admin_HeroSliderPlaylists extends ObjectEditor {
	function getObjectType(): string {
		return 'HeroSliderPlaylist';
	}

	function getToolName(): string {
		return 'HeroSliderPlaylists';
	}

	function getPageTitle(): string {
		return 'Hero Slider Playlists';
	}

	function getAllObjects($page, $recordsPerPage): array {
		$list = [];
		$object = new HeroSliderPlaylist();

		if (!UserAccount::userHasPermission('Administer All Hero Sliders')) {
			$homeLibrary = Library::getPatronHomeLibrary();
			$object->whereAdd("libraryId = {$homeLibrary->libraryId} OR libraryId = -1");
		}

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
		return HeroSliderPlaylist::getObjectStructure($context);
	}

	function getPrimaryKeyColumn(): string {
		return 'id';
	}

	function getIdKeyColumn(): string {
		return 'id';
	}

	function canAddNew(): bool {
		return UserAccount::userHasPermission(['Administer All Hero Sliders', 'Administer Library Hero Sliders']);
	}

	function canDelete(): bool {
		return UserAccount::userHasPermission(['Administer All Hero Sliders', 'Administer Library Hero Sliders']);
	}

	function getBreadcrumbs(): array {
		$breadcrumbs = [];
		$breadcrumbs[] = new Breadcrumb('/Admin/Home', 'Administration Home');
		$breadcrumbs[] = new Breadcrumb('/Admin/Home#local_enrichment', 'Local Enrichment');
		$breadcrumbs[] = new Breadcrumb('/Admin/HeroSliderPlaylists', 'Hero Slider Playlists');
		return $breadcrumbs;
	}

	function getActiveAdminSection(): string {
		return 'local_enrichment';
	}

	function canView(): bool {
		return UserAccount::userHasPermission(['Administer All Hero Sliders', 'Administer Library Hero Sliders']);
	}
}
