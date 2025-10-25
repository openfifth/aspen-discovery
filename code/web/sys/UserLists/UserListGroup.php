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

	function numValidLists() {
		require_once ROOT_DIR . '/sys/UserLists/UserList.php';
		$userList = new UserList();
		$userList->listGroupId = $this->id;

		return $userList->count();
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
