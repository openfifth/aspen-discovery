<?php

require_once ROOT_DIR . '/sys/OverDrive/OverDriveSetting.php';

class OverDriveScope extends DataObject {
	public $__table = 'overdrive_scopes';
	public $id;
	public $settingId;
	public $name;
	public $includeAdult;
	public $includeTeen;
	public $includeKids;
	public $clientSecret;
	public $clientKey;
	public $authenticationILSName;
	public $requirePin;
	public $readerName;
	public /** @noinspection PhpUnused */
		$overdriveAdvantageName;
	public /** @noinspection PhpUnused */
		$overdriveAdvantageProductsKey;
	public $circulationEnabled;

	private $_libraries;
	private $_locations;

	public function getEncryptedFieldNames(): array {
		return ['clientSecret'];
	}

	public static function getObjectStructure($context = ''): array {
		$overdriveSettings = [];
		$overdriveSetting = new OverDriveSetting();
		$overdriveSetting->find();
		while ($overdriveSetting->fetch()) {
			$overdriveSettings[$overdriveSetting->id] = (string)$overdriveSetting;
		}

		$libraryList = Library::getLibraryList(!UserAccount::userHasPermission('Administer All Libraries'));
		$locationList = Location::getLocationList(!UserAccount::userHasPermission('Administer All Libraries') || UserAccount::userHasPermission('Administer Home Library Locations'));

		return [
			'id' => [
				'property' => 'id',
				'type' => 'label',
				'label' => 'Id',
				'description' => 'The unique id',
			],
			'settingId' => [
				'property' => 'settingId',
				'type' => 'enum',
				'values' => $overdriveSettings,
				'label' => 'Setting Id',
			],
			'name' => [
				'property' => 'name',
				'type' => 'text',
				'label' => 'Name',
				'description' => 'The Name of the scope',
				'maxLength' => 50,
			],
			'circulationEnabled' => [
				'property' => 'circulationEnabled',
				'type' => 'checkbox',
				'label' => 'Circulation Enabled',
				'description' => 'Whether or not circulation is enabled within Aspen',
				'hideInLists' => true,
				'default' => true,
				'forcesReindex' => false,
			],
			'clientKey' => [
				'property' => 'clientKey',
				'type' => 'text',
				'label' => 'Circulation Client Key (if different from settings)',
				'description' => 'The client key provided by OverDrive when registering',
			],
			'clientSecret' => [
				'property' => 'clientSecret',
				'type' => 'storedPassword',
				'label' => 'Circulation Client Secret (if different from settings)',
				'description' => 'The client secret provided by OverDrive when registering',
				'hideInLists' => true,
			],
			'authenticationILSName' => [
				'property' => 'authenticationILSName',
				'type' => 'text',
				'label' => 'The ILS Name Overdrive uses for user Authentication',
				'description' => 'The name of the ILS that OverDrive uses to authenticate users logging into the Overdrive website.',
				'size' => '20',
				'hideInLists' => true,
			],
			'requirePin' => [
				'property' => 'requirePin',
				'type' => 'checkbox',
				'label' => 'Is a Pin Required to log into Overdrive website?',
				'description' => 'Turn on to allow repeat search in Overdrive functionality.',
				'hideInLists' => true,
				'default' => 0,
			],
			'overdriveAdvantageName' => [
				'property' => 'overdriveAdvantageName',
				'type' => 'text',
				'label' => 'Overdrive Advantage Name',
				'description' => 'The name of the OverDrive Advantage account if any.',
				'size' => '80',
				'hideInLists' => true,
				'forcesReindex' => true,
			],
			'overdriveAdvantageProductsKey' => [
				'property' => 'overdriveAdvantageProductsKey',
				'type' => 'text',
				'label' => 'Overdrive Advantage Products Key',
				'description' => 'The products key for use when building urls to the API from the advantageAccounts call.',
				'size' => '80',
				'hideInLists' => false,
				'forcesReindex' => true,
			],
			'readerName' => [
				'property' => 'readerName',
				'type' => 'text',
				'label' => 'Reader Name',
				'description' => 'Name of Libby product to display to patrons. Default is Libby',
			],
			'includeAdult' => [
				'property' => 'includeAdult',
				'type' => 'checkbox',
				'label' => 'Include Adult Titles',
				'description' => 'Whether or not adult titles from the Overdrive collection should be included in searches',
				'default' => true,
				'forcesReindex' => true,
			],
			'includeTeen' => [
				'property' => 'includeTeen',
				'type' => 'checkbox',
				'label' => 'Include Teen Titles',
				'description' => 'Whether or not teen titles from the Overdrive collection should be included in searches',
				'default' => true,
				'forcesReindex' => true,
			],
			'includeKids' => [
				'property' => 'includeKids',
				'type' => 'checkbox',
				'label' => 'Include Kids Titles',
				'description' => 'Whether or not kids titles from the Overdrive collection should be included in searches',
				'default' => true,
				'forcesReindex' => true,
			],
			'libraries' => [
				'property' => 'libraries',
				'type' => 'multiSelect',
				'listStyle' => 'checkboxSimple',
				'label' => "Libraries",
				'description' => "The libraries that use this scope",
				'values' => $libraryList,
				'hideInLists' => false,
			],

			'locations' => [
				'property' => 'locations',
				'type' => 'multiSelect',
				'listStyle' => 'checkboxSimple',
				'label' => "Locations",
				'description' => "The locations that use this scope",
				'values' => $locationList,
				'hideInLists' => false,
			],
		];
	}

	/** @noinspection PhpUnused */
	public function getEditLink($context): string {
		return '/OverDrive/Scopes?objectAction=edit&id=' . $this->id;
	}

	public function __get($name) {
		if ($name == "libraries") {
			if (!isset($this->_libraries) && $this->id) {
				$this->_libraries = [];
				$obj = new Library();
				$obj->overDriveScopeId = $this->id;
				$obj->find();
				while ($obj->fetch()) {
					$this->_libraries[$obj->libraryId] = $obj->libraryId;
				}
			}
			return $this->_libraries;
		} elseif ($name == "locations") {
			if (!isset($this->_locations) && $this->id) {
				$this->_locations = [];
				$obj = new Location();
				$obj->overDriveScopeId = $this->id;
				$obj->find();
				while ($obj->fetch()) {
					$this->_locations[$obj->locationId] = $obj->locationId;
				}
			}
			return $this->_locations;
		} else {
			return parent::__get($name);
		}
	}

	public function __set($name, $value) {
		if ($name == "libraries") {
			$this->_libraries = $value;
		} elseif ($name == "locations") {
			$this->_locations = $value;
		} else {
			parent::__set($name, $value);
		}
	}

	public function update($context = '') {
		$ret = parent::update();
		if ($ret !== FALSE) {
			$this->saveLibraries();
			$this->saveLocations();
		}
		return true;
	}

	public function insert($context = '') {
		$ret = parent::insert();
		if ($ret !== FALSE) {
			$this->saveLibraries();
			$this->saveLocations();
		}
		return $ret;
	}

	public function saveLibraries() {
		if (isset ($this->_libraries) && is_array($this->_libraries)) {
			$libraryList = Library::getLibraryList(!UserAccount::userHasPermission('Administer All Libraries'));
			foreach ($libraryList as $libraryId => $displayName) {
				$library = new Library();
				$library->libraryId = $libraryId;
				if ($library->find(true)) {
					$libraryOverDriveScopes = $library->getLibraryOverdriveScopes();
					if (in_array($libraryId, $this->_libraries)) {
						$foundScope = false;
						foreach ($libraryOverDriveScopes as $libraryOverDriveScope) {
							if ($libraryOverDriveScope->scopeId == $this->id) {
								$foundScope = true;
								break;
							}
						}
						//We want to apply the scope to this library
						if (!$foundScope) {
							$libraryOverDriveScope = new LibraryOverDriveScope();
							$libraryOverDriveScope->scopeId = $this->id;
							$libraryOverDriveScope->libraryId = $libraryId;
							$libraryOverDriveScope->insert();
						}
					} else {
						//It should not be applied to this scope. Only change if it was applied previously
						foreach ($libraryOverDriveScopes as $libraryOverDriveScope) {
							if ($libraryOverDriveScope->scopeId == $this->id) {
								$libraryOverDriveScope->delete();
							}
						}
					}
				}
			}
			unset($this->_libraries);
		}
	}

	public function saveLocations() {
		if (isset ($this->_locations) && is_array($this->_locations)) {
			$locationList = Location::getLocationList(!UserAccount::userHasPermission('Administer All Libraries') || UserAccount::userHasPermission('Administer Home Library Locations'));
			foreach ($locationList as $locationId => $displayName) {
				$location = new Location();
				$location->locationId = $locationId;
				if ($location->find(true)) {
					$locationOverDriveScopes = $location->getLocationOverdriveScopes();
					if (in_array($locationId, $this->_locations)) {
						$foundScope = false;
						foreach ($locationOverDriveScopes as $locationOverDriveScope) {
							if ($locationOverDriveScope->scopeId == $this->id) {
								$foundScope = true;
								break;
							}
						}
						//We want to apply the scope to this location
						if (!$foundScope) {
							$locationOverDriveScope = new LocationOverDriveScope();
							$locationOverDriveScope->scopeId = $this->id;
							$locationOverDriveScope->locationId = $locationId;
							$locationOverDriveScope->insert();
						}
					} else {
						//It should not be applied to this scope. Only change if it was applied previously
						foreach ($locationOverDriveScopes as $locationOverDriveScope) {
							if ($locationOverDriveScope->scopeId == $this->id) {
								$locationOverDriveScope->delete();
							}
						}
					}
				}
			}
			unset($this->_locations);
		}
	}

	/** @return LibraryOverDriveScope[] */
	public function getLibraries() : array {
		if (!isset($this->_libraries) && $this->id) {
			$this->_libraries = [];
			if ($this->id > 0) {
				require_once ROOT_DIR . '/sys/OverDrive/LibraryOverDriveScope.php';
				$libraryOverDriveScope = new LibraryOverDriveScope();
				$libraryOverDriveScope->scopeId = $this->id;
				$this->_libraries = $libraryOverDriveScope->fetchAll('libraryId');
			}
		}
		return $this->_libraries;
	}

	/** @return LocationOverDriveScope[]
	 * @noinspection PhpUnused
	 */
	public function getLocations() : array {
		if (!isset($this->_locations)) {
			$this->_locations = [];
			if ($this->id > 0) {
				require_once ROOT_DIR . '/sys/OverDrive/LocationOverDriveScope.php';
				$locationOverDriveScope = new LocationOverDriveScope();
				$locationOverDriveScope->scopeId = $this->id;
				$this->_locations = $locationOverDriveScope->fetchAll('locationId');
			}
		}
		return $this->_locations;
	}

	/** @noinspection PhpUnused */
	public function setLibraries($val) {
		$this->_libraries = $val;
	}

	/** @noinspection PhpUnused */
	public function setLocations($val) {
		$this->_libraries = $val;
	}

	public function clearLibraries() {
		$this->clearOneToManyOptions('Library', 'overDriveScopeId');
		unset($this->_libraries);
	}

	/** @noinspection PhpUnused */
	public function clearLocations() {
		$this->clearOneToManyOptions('Location', 'overDriveScopeId');
		unset($this->_locations);
	}
}