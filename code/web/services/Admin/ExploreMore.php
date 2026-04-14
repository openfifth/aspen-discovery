<?php

require_once ROOT_DIR . '/Action.php';
require_once ROOT_DIR . '/services/Admin/ObjectEditor.php';
require_once ROOT_DIR . '/sys/ExploreMoreSource.php';

class Admin_ExploreMore extends ObjectEditor {
    function getObjectType(): string {
        return 'ExploreMoreSource';
    }

    function getToolName(): string {
        return 'ExploreMore';
    }

    function getPageTitle(): string {
        return 'Explore More Sources';
    }

    function getAllObjects(int $page, int $recordsPerPage): array {
        $list = [];
        $object = new ExploreMoreSource();
        $object->orderBy('weight ASC');
        $object->limit(($page - 1) * $recordsPerPage, $recordsPerPage);
        $object->find();
        while ($object->fetch()) {
            $list[$object->id] = clone $object;
        }
        return $list;
    }

    function getDefaultSort(): string {
        return 'weight asc';
    }

    function getObjectStructure($context = ''): array {
        return ExploreMoreSource::getObjectStructure($context);
    }

    function getPrimaryKeyColumn(): string {
        return 'id';
    }

    function getIdKeyColumn(): string {
        return 'id';
    }

    function getInstructions(): string {
        return 'https://aspen-discovery.atlassian.net/wiki/spaces/Help/pages/188121091/Explore+More';
    }

    function getBreadcrumbs(): array {
        $breadcrumbs = [];
        $breadcrumbs[] = new Breadcrumb('/Admin/Home', 'Administration Home');
        $breadcrumbs[] = new Breadcrumb('/Admin/Home#local_enrichment', 'Local Enrichment');
        $breadcrumbs[] = new Breadcrumb('/Admin/ExploreMore', 'Explore More');
        return $breadcrumbs;
    }

    function getActiveAdminSection(): string {
        return 'local_enrichment';
    }

    function canAddNew(): bool {
        return false;
    }

    function canDelete(): bool {
        return false;
    }

    public function getViewPermissions(): array {
        return [
            'Administer All Explore More',
            'Administer Library Explore More',
        ];
    }

	function canView(): bool {
		return UserAccount::userHasPermission([
			'Administer All Explore More',
			'Administer Library Explore More',
		]);
	}

}
