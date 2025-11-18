<?php /** @noinspection PhpMissingFieldTypeInspection */
require_once ROOT_DIR . '/sys/Events/LibraryEventsSetting.php';
require_once ROOT_DIR . '/sys/Events/EventsBranchMapping.php';

/**
 * Settings for Aspen Events (Registration)
 */
class AspenEventSetting extends DataObject {
	public $__table = 'aspen_event_settings';
	public $id;
	public $name;
	public $registrationModalBody;
	public $runFullUpdate;
	/** @noinspection PhpUnused */
	public $numberOfDaysToIndex;

	private $_libraries;
	private $_locationMap;


	static $_objectStructure = [];
	static function getObjectStructure(string $context = ''): array {
		if (isset(self::$_objectStructure[$context]) && self::$_objectStructure[$context] !== null) {
			return self::$_objectStructure[$context];
		}
		$libraryList = Library::getLibraryList(!UserAccount::userHasPermission('Administer Events for All Locations'));

		/** @noinspection HtmlRequiredAltAttribute */
		$structure = [
			'id' => [
				'property' => 'id',
				'type' => 'label',
				'label' => 'Id',
				'description' => 'The unique id',
			],
			'name' => [
				'property' => 'name',
				'type' => 'text',
				'label' => 'Name',
				'description' => 'A name for the settings',
			],
			'indexingSection' => [
				'property' => 'indexingSection',
				'type' => 'section',
				'label' => 'Indexing Settings',
				'hideInLists' => true,
				'expandByDefault' => true,
				'properties' => [
					'runFullUpdate' => [
						'property' => 'runFullUpdate',
						'type' => 'checkbox',
						'label' => 'Run Full Update',
						'description' => 'Whether or not a full update of all records should be done on the next pass of indexing',
						'default' => 0,
					],
					'numberOfDaysToIndex' => [
						'property' => 'numberOfDaysToIndex',
						'type' => 'integer',
						'label' => 'Number of Days to Index',
						'description' => 'How many days in the future to index events',
						'default' => 365,
					],
					'lastUpdateOfAllEvents' => [
						'property' => 'lastUpdateOfAllEvents',
						'type' => 'timestamp',
						'label' => 'Last Update Of All Events',
						'readOnly' => 1,
					],
					'lastUpdateOfChangedEvents' => [
						'property' => 'lastUpdateOfChangedEvents',
						'type' => 'timestamp',
						'label' => 'Last Update Of Changed Events',
						'readOnly' => 1,
					],
				]
			],
			'registrationSection' => [
				'property' => 'registrationSection',
				'type' => 'section',
				'label' => 'Registration Settings',
				'hideInLists' => true,
				'expandByDefault' => true,
				'properties' => [
					'registrationModalBody' => [
						'property' => 'registrationModalBody',
						'type' => 'html',
						'label' => 'Registration Modal Body',
						'description' => 'The body of the modal for event registration information',
						'allowableTags' => '<p><em><i><strong><b><a><ul><ol><li><h1><h2><h3><h4><h5><h6><h7><pre><code><hr><table><tbody><tr><th><td><caption><img><br><div><span><sub><sup>',
						'hideInLists' => true,
					],
				]
			],
			'libraries' => [
				'property' => 'libraries',
				'type' => 'multiSelect',
				'listStyle' => 'checkboxSimple',
				'label' => 'Libraries',
				'description' => 'Define libraries that use these settings',
				'values' => $libraryList,
				'hideInLists' => true,
			],
		];

		self::$_objectStructure[$context] = $structure;
		return self::$_objectStructure[$context];
	}

	/**
	 * Override the update functionality to save related objects
	 *
	 * @see DB/DB_DataObject::update()
	 */
	public function update(string $context = '') : int|bool {
		$ret = parent::update();
		if ($ret !== FALSE) {
			$this->saveLibraries();
		}
		return $ret;
	}

	/**
	 * Override the insert functionality to save the related objects
	 *
	 * @see DB/DB_DataObject::insert()
	 */
	public function insert(string $context = '') : int|bool {
		$ret = parent::insert();
		if ($ret !== FALSE) {
			$this->saveLibraries();
		}
		return $ret;
	}

	public function __get($name) {
		if ($name == "libraries") {
			return $this->getLibraries();
		}
		if ($name == "locationMap") {
			return $this->getLocationMap();
		} else {
			return parent::__get($name);
		}
	}

	public function __set($name, $value) {
		if ($name == "libraries") {
			$this->_libraries = $value;
		} else {
			parent::__set($name, $value);
		}
	}

	public function delete(bool $useWhere = false, bool $hardDelete = false) : bool|int {
		$ret = parent::delete($useWhere, $hardDelete);
		if ($ret && !empty($this->id)) {
			$this->clearLibraries();
		}
		return $ret;
	}

	public function getLibraries() : ?array {
		if (!isset($this->_libraries) && $this->id) {
			$this->_libraries = [];
			$library = new LibraryEventsSetting();
			$library->settingSource = 'aspenEvent';
			$library->settingId = $this->id;
			$library->find();
			while ($library->fetch()) {
				$this->_libraries[$library->libraryId] = $library->libraryId;
			}
		}
		return $this->_libraries;
	}

	public function getLocationMap() : array {
		if (!isset($this->_locationMap)) {
			//Get the list of translation maps
			$this->_locationMap = [];
			$locationMap = new EventsBranchMapping();
			$locationMap->orderBy('id');
			$locationMap->find();
			while ($locationMap->fetch()) {
				$this->_locationMap[$locationMap->id] = clone($locationMap);
			}
		}
		return $this->_locationMap;
	}

	public function saveLibraries() : void {
		if (isset($this->_libraries) && is_array($this->_libraries)) {
			$this->clearLibraries();

			foreach ($this->_libraries as $libraryId) {
				$libraryEventSetting = new LibraryEventsSetting();

				$libraryEventSetting->settingSource = 'aspenEvent';
				$libraryEventSetting->settingId = $this->id;
				$libraryEventSetting->libraryId = $libraryId;
				$libraryEventSetting->insert();
			}
			unset($this->_libraries);
		}
	}

	public function saveLocationMap() : void {
		if (isset($this->_locationMap)) {
			foreach ($this->_locationMap as $location) {
				$locationMap = new EventsBranchMapping();
				$locationMap->locationId = $location->locationId;
				if ($locationMap->find(true)) {
					$locationMap->eventsLocation = $location->eventsLocation;
					$locationMap->update();
				}
			}
			unset($this->_locationMap);
		}
	}

	private function clearLibraries() : void {
		//Delete links to the libraries
		$libraryEventSetting = new LibraryEventsSetting();
		$libraryEventSetting->settingSource = 'aspenEvent';
		$libraryEventSetting->settingId = $this->id;
		$libraryEventSetting->delete(true);
	}
}