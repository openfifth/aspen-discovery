<?php

require_once ROOT_DIR . '/Action.php';
require_once ROOT_DIR . '/sys/Community/Milestone.php';
require_once ROOT_DIR . '/services/Admin/ObjectEditor.php';

class Community_Milestones extends ObjectEditor {
    function getObjectType(): string {
        return 'Milestone';
    }

    function getToolName(): string {
        return 'Milestones';
    }

    function getModule(): string {
		return 'Community';
	}

    function getPageTitle(): string {
		return 'Milestones';
	}

    function getAllObjects($page, $recordsPerPage): array {
        $object = new Milestone();
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
		return Milestone::getObjectStructure($context);
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
		$breadcrumbs[] = new Breadcrumb('/Admin/Home#community', 'Community');
		$breadcrumbs[] = new Breadcrumb('/Community/Milestones', 'Milestones');
		return $breadcrumbs;
	}

	function getActiveAdminSection(): string {
		return 'community';
	}

    function canView(): bool {
		return UserAccount::userHasPermission([
            'Administer Community Module',
        ]);
	}

    function canBatchEdit(): bool {
		return UserAccount::userHasPermission([
			'Administer Community Module',
		]);
	}
}