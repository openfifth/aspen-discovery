<?php
/** @noinspection PhpMissingFieldTypeInspection */

class RecordGroupingOverride extends DataObject {
	public $__table = 'record_grouping_overrides';
	public $id;
	public $source;
	public $record_id;
	public $grouped_work_permanent_id;
	public $added_by;
	public $date_added;

	static array $_objectStructure = [];

	static function getObjectStructure(string $context = ''): array {
		if (isset(self::$_objectStructure[$context]) && self::$_objectStructure[$context] !== null) {
			return self::$_objectStructure[$context];
		}

		global $indexingProfiles;
		global $sideLoadSettings;
		$availableSources = [];
		foreach ($indexingProfiles as $profile) {
			$availableSources[$profile->name] = $profile->name;
		}
		foreach ($sideLoadSettings as $profile) {
			$availableSources[$profile->name] = $profile->name;
		}
		$availableSources['axis360'] = 'Boundless';
		$availableSources['cloud_library'] = 'cloudLibrary';
		$availableSources['hoopla'] = 'Hoopla';
		$availableSources['overdrive'] = 'Overdrive';
		$availableSources['palace_project'] = 'Palace Project';

		$structure = [
			'id' => [
				'property' => 'id',
				'type' => 'label',
				'label' => 'Id',
				'description' => 'The unique id',
			],
			'addedByName' => [
				'property' => 'addedByName',
				'type' => 'label',
				'label' => 'Added By',
				'description' => 'The user who created this override',
			],
			'date_added' => [
				'property' => 'date_added',
				'type' => 'timestamp',
				'label' => 'Date Added',
				'description' => 'When this override was created',
				'readOnly' => true,
			],
			'source' => [
				'property' => 'source',
				'type' => 'enum',
				'values' => $availableSources,
				'label' => 'Source',
				'description' => 'The source of the record',
				'required' => true,
			],
			'record_id' => [
				'property' => 'record_id',
				'type' => 'text',
				'label' => 'Record ID',
				'description' => 'The identifier of the record within its source',
				'maxLength' => 50,
				'required' => true,
			],
			'grouped_work_permanent_id' => [
				'property' => 'grouped_work_permanent_id',
				'type' => 'text',
				'label' => 'Grouped Work Permanent ID',
				'description' => 'The permanent ID of the grouped work this record should belong to',
				'maxLength' => 40,
				'required' => true,
			],
			'grouped_work_display' => [
				'property' => 'grouped_work_display',
				'type' => 'label',
				'label' => 'Grouped Work',
				'description' => 'The grouped work this record is assigned to',
			],
		];

		self::$_objectStructure[$context] = $structure;
		return self::$_objectStructure[$context];
	}

	private static array $usersById = [];

	public function __get($name) {
		if ($name == 'addedByName') {
			if (empty($this->_data['addedByName'])) {
				if (!empty($this->added_by)) {
					if (array_key_exists($this->added_by, RecordGroupingOverride::$usersById)) {
						$this->_data['addedByName'] = RecordGroupingOverride::$usersById[$this->added_by];
					} else {
						require_once ROOT_DIR . '/sys/Account/User.php';
						$user = new User();
						$user->id = $this->added_by;
						if ($user->find(true)) {
							$displayName = $user->getDisplayName();
							$barcode = $user->getBarcode();
							if (!empty($barcode)) {
								$this->_data['addedByName'] = trim($barcode . ' - ' . $displayName, ' -');
							} elseif (!empty($user->username)) {
								$this->_data['addedByName'] = trim($user->username . ' - ' . $displayName, ' -');
							} else {
								$this->_data['addedByName'] = $displayName;
							}
							RecordGroupingOverride::$usersById[$this->added_by] = $this->_data['addedByName'];
						} else {
							$this->_data['addedByName'] = 'User ID: ' . $this->added_by;
						}
					}
				} else {
					$this->_data['addedByName'] = 'Unknown';
				}
			}
		} elseif ($name == 'grouped_work_display') {
			if (empty($this->_data['grouped_work_display']) && !empty($this->grouped_work_permanent_id)) {
				require_once ROOT_DIR . '/sys/Grouping/GroupedWork.php';
				$groupedWork = new GroupedWork();
				$groupedWork->permanent_id = $this->grouped_work_permanent_id;
				if ($groupedWork->find(true)) {
					$this->_data['grouped_work_display'] = $groupedWork->full_title . ' by ' . $groupedWork->author;
				} else {
					$this->_data['grouped_work_display'] = 'Unknown (Permanent ID: ' . $this->grouped_work_permanent_id . ')';
				}
			}
		}
		return $this->_data[$name] ?? null;
	}

	public function insert(string $context = ''): bool|int {
		if (empty($this->date_added)) {
			$this->date_added = time();
		}
		if (empty($this->added_by)) {
			$this->added_by = UserAccount::getActiveUserId();
		}
		$ret = parent::insert();
		if ($ret) {
			$this->triggerReindex();
		}
		return $ret;
	}

	public function update(string $context = ''): bool|int {
		$ret = parent::update();
		if ($ret) {
			$this->triggerReindex();
		}
		return $ret;
	}

	public function delete(bool $useWhere = false, bool $hardDelete = false): int {
		$ret = parent::delete($useWhere);
		if ($ret) {
			$this->triggerReindex();
		}
		return $ret;
	}

	private function triggerReindex(): void {
		require_once ROOT_DIR . '/sys/Indexing/RecordIdentifiersToReload.php';
		$recordToReload = new RecordIdentifiersToReload();
		$recordToReload->type = $this->source;
		$recordToReload->identifier = $this->record_id;
		if (!$recordToReload->find(true)) {
			$recordToReload->insert();
		}

		require_once ROOT_DIR . '/sys/Grouping/GroupedWork.php';
		$groupedWork = new GroupedWork();
		$groupedWork->permanent_id = $this->grouped_work_permanent_id;
		if ($groupedWork->find(true)) {
			$groupedWork->forceReindex(true);
		}
	}

	public function getAdditionalListActions(): array {
		$actions = [];
		if (!empty($this->grouped_work_permanent_id)) {
			$actions[] = [
				'text' => 'View Grouped Work',
				'url' => '/GroupedWork/' . $this->grouped_work_permanent_id,
				'target' => '_blank',
				'icon' => 'fas fa-external-link-alt',
			];
		}
		return $actions;
	}
}
