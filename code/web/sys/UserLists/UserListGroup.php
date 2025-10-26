<?php /** @noinspection PhpMissingFieldTypeInspection */

class UserListGroup extends DataObject {
	public $__table = 'user_list_group';
	public $id;
	public $title;
	public $parentGroupId;
	public $userId;

	static $_objectStructure = [];

	static function getObjectStructure(string $context = ''): array {
		if (isset(self::$_objectStructure[$context]) && self::$_objectStructure[$context] !== null) {
			return self::$_objectStructure[$context];
		}

		$structure = [
			'id' => [
				'property' => 'id',
				'type' => 'label',
				'label' => 'Id',
				'description' => 'The unique id of the user list group.',
				'storeDb' => true,
				'storeSolr' => false,
			],
			'title' => [
				'property' => 'title',
				'type' => 'text',
				'size' => 100,
				'maxLength' => 255,
				'label' => 'Title',
				'description' => 'The title of the user list group.',
				'required' => true,
				'storeDb' => true,
				'storeSolr' => true,
			],
			'parentGroupId' => [
				'property' => 'parentGroupId',
				'type' => 'integer',
				'label' => 'Parent Group Id',
				'description' => 'The id of the parent group, if any.',
				'required' => false,
				'storeDb' => true,
				'storeSolr' => false,
			],
			'userId' => [
				'property' => 'userId',
				'type' => 'integer',
				'label' => 'User Id',
				'description' => 'The id of the user who owns this group.',
				'required' => true,
				'storeDb' => true,
				'storeSolr' => false,
			]
		];

		self::$_objectStructure[$context] = $structure;
		return self::$_objectStructure[$context];
	}

	public static function getLastViewedGroupForUser(user $user): ?array {
		$lastViewed = new UserListGroup();
		$lastViewed->userId = $user->id;
		$lastViewed->id = $user->lastListGroupViewed;
		if ($lastViewed->find(true)) {
			$lists = [];
			$listGroup = new UserList();
			$listGroup->listGroupId = $lastViewed->id;
			$listGroup->find();
			while ($listGroup->fetch()) {
				$lists[] = clone $listGroup;
			}
			return $lists;
		} else {
			return null;
		}
	}

	public static function getLastViewedGroupDetailsForUser(user $user): ?UserListGroup {
		$lastViewed = new UserListGroup();
		$lastViewed->userId = $user->id;
		$lastViewed->id = $user->lastListGroupViewed;
		if ($lastViewed->find(true)) {
			return $lastViewed;
		} else {
			return null;
		}
	}

	public static function getLastAddedGroupForUser(user $user): ?UserListGroup {
		$lastAdded = new UserListGroup();
		$lastAdded->userId = $user->id;
		$lastAdded->id = $user->lastListGroupAdded;
		if ($lastAdded->find(true)) {
			return $lastAdded;
		} else {
			return null;
		}
	}

	function getListsForGroup(user $user): ?array {
		$group = new UserListGroup();
		$group->userId = $user->id;
		$group->id = $this->id;
		if ($group->find(true)) {
			$lists = [];
			$listGroup = new UserList();
			$listGroup->listGroupId = $group->id;
			$listGroup->find();
			while ($listGroup->fetch()) {
				$lists[] = clone $listGroup;
			}
			return $lists;
		} else {
			return null;
		}
	}

	function numValidLists() {
		require_once ROOT_DIR . '/sys/UserLists/UserList.php';
		$userList = new UserList();
		$userList->listGroupId = $this->id;

		return $userList->count();
	}

	function getListGroups(user $user) {
		require_once ROOT_DIR . '/sys/UserLists/UserListGroup.php';

		$group = new UserListGroup();
		$group->userId = $user->id;
		$group->orderBy('title DESC');
		$allGroups = [];
		if ($group->find()) {
			while ($group->fetch()) {
				$allGroups[$group->id] = clone($group);
			}
		}

		$groups = [];
		foreach ($allGroups as $id => $grp) {
			$titleParts = [];
			$current = $grp;
			$level = 0;
			while ($current->parentGroupId && isset($allGroups[$current->parentGroupId]) && $level < 3) {
				array_unshift($titleParts, $allGroups[$current->parentGroupId]->title);
				$current = $allGroups[$current->parentGroupId];
				$level++;
			}
			$titleParts[] = $grp->title;
			$grp->title = implode(' » ', $titleParts);
			$groups[] = $grp;
		}

		return $groups;
	}

	public static function getFullGroupTitle(UserListGroup $group): string {
		require_once ROOT_DIR . '/sys/UserLists/UserListGroup.php';

		$titleParts = [];
		$current = $group;
		$level = 0;

		// Load all parent groups for the user
		$allGroups = [];
		$parent = new UserListGroup();
		$parent->userId = $group->userId;
		$parent->find();
		while ($parent->fetch()) {
			$allGroups[$parent->id] = clone($parent);
		}

		// Build title chain
		while ($current->parentGroupId && isset($allGroups[$current->parentGroupId]) && $level < 3) {
			array_unshift($titleParts, $allGroups[$current->parentGroupId]->title);
			$current = $allGroups[$current->parentGroupId];
			$level++;
		}
		$titleParts[] = $group->title;

		return implode(' » ', $titleParts);
	}

	public function getUniquenessFields(): array {
		return ['id'];
	}

	public function getNumericColumnNames(): array {
		return [
			'id',
			'parentGroupId',
			'userId'
		];
	}
	public function supportsSoftDelete(): bool {
		return false;
	}
}
