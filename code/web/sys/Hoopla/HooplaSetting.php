<?php
require_once ROOT_DIR . '/sys/Hoopla/HooplaScope.php';

class HooplaSetting extends DataObject {
	public $__table = 'hoopla_settings';    // table name
	public $id;
	public $apiUrl;
	public $libraryId;
	public $apiUsername;
	public $apiPassword;
	public $accessToken;
	public $tokenExpirationTime;
	public $regroupAllRecords;
	public $runFullUpdateInstant;
	public $lastUpdateOfChangedRecordsInstant;
	public $lastUpdateOfAllRecordsInstant;
	public $hooplaInstantEnabled;
	public $runFullUpdateFlex;
	public $lastUpdateOfChangedRecordsFlex;
	public $lastUpdateOfAllRecordsFlex;
	public $hooplaFlexEnabled;

	private $_scopes;

	public static function getObjectStructure($context = ''): array {
		$hooplaScopeStructure = HooplaScope::getObjectStructure($context);
		unset($hooplaScopeStructure['settingId']);

		return [
			'id' => [
				'property' => 'id',
				'type' => 'label',
				'label' => 'Id',
				'description' => 'The unique id',
			],
			'apiUrl' => [
				'property' => 'apiUrl',
				'type' => 'url',
				'label' => 'url',
				'description' => 'The URL to the API',
			],
			'libraryId' => [
				'property' => 'libraryId',
				'type' => 'integer',
				'label' => 'Library Id',
				'description' => 'The Library Id to use with the API',
			],
			'apiUsername' => [
				'property' => 'apiUsername',
				'type' => 'text',
				'label' => 'API Username',
				'description' => 'The API Username provided by your Aspen support vendor (or Hoopla when registering if not using third-party support or hosting)',
			],
			'apiPassword' => [
				'property' => 'apiPassword',
				'type' => 'storedPassword',
				'label' => 'API Password',
				'description' => 'The API Password provided by your Aspen support vendor (or Hoopla when registering if not using third-party support or hosting)',
				'hideInLists' => true,
			],
			'regroupAllRecords' => [
				'property' => 'regroupAllRecords',
				'type' => 'checkbox',
				'label' => 'Regroup all Records',
				'description' => 'Whether or not all existing records should be regrouped',
				'default' => 0,
			],
			'hooplaInstantRecords' => [
				'property' => 'hooplaInstantRecords',
				'type' => 'section',
				'label' => 'Hoopla Instant',
				'expandByDefault' => true,
				'properties' => [
					'hooplaInstantEnabled' => [
						'property' => 'hooplaInstantEnabled',
						'type' => 'checkbox',
						'label' => 'Hoopla Instant Enabled',
						'description' => 'Whether or not to use Hoopla Instant Records',
						'default' => 1,
					],
					'runFullUpdateInstant' => [
						'property' => 'runFullUpdateInstant',
						'type' => 'checkbox',
						'label' => 'Run Full Update for Instant',
						'description' => 'Whether or not a full update of all records should be done on the next pass of indexing',
						'default' => 0,
					],
					'lastUpdateOfChangedRecordsInstant' => [
						'property' => 'lastUpdateOfChangedRecordsInstant',
						'type' => 'timestamp',
						'label' => 'Last Update of Changed Instant Records',
						'description' => 'The timestamp when just changes were loaded',
						'default' => 0,
					],
					'lastUpdateOfAllRecordsInstant' => [
						'property' => 'lastUpdateOfAllRecordsInstant',
						'type' => 'timestamp',
						'label' => 'Last Update of All Instant Records',
						'description' => 'The timestamp when all records were loaded',
						'default' => 0,
					],
				],
			],
			'hooplaFlexRecords' => [
				'property' => 'hooplaFlexRecords',
				'type' => 'section',
				'label' => 'Hoopla Flex',
				'expandByDefault' => true,
				'properties' => [
					'hooplaFlexEnabled' => [
						'property' => 'hooplaFlexEnabled',
						'type' => 'checkbox',
						'label' => 'Hoopla Flex Enabled',
						'description' => 'Whether or not to use Hoopla Flex',
						'default' => 0,
					],
					'runFullUpdateFlex' => [
						'property' => 'runFullUpdateFlex',
						'type' => 'checkbox',
						'label' => 'Run Full Update for Flex',
						'description' => 'Whether or not a full update of all records should be done on the next pass of indexing',
						'default' => 0,
					],
					'lastUpdateOfChangedRecordsFlex' => [
						'property' => 'lastUpdateOfChangedRecordsFlex',
						'type' => 'timestamp',
						'label' => 'Last Update of Changed Flex Records',
						'description' => 'The timestamp when just changes were loaded',
						'default' => 0,
					],
					'lastUpdateOfAllRecordsFlex' => [
						'property' => 'lastUpdateOfAllRecordsFlex',
						'type' => 'timestamp',
						'label' => 'Last Update of All Flex Records',
						'description' => 'The timestamp when all records were loaded',
						'default' => 0,
					],
				],
			],
			'scopes' => [
				'property' => 'scopes',
				'type' => 'oneToMany',
				'label' => 'Scopes',
				'description' => 'Define scopes for the settings',
				'keyThis' => 'id',
				'keyOther' => 'settingId',
				'subObjectType' => 'HooplaScope',
				'structure' => $hooplaScopeStructure,
				'sortable' => false,
				'storeDb' => true,
				'allowEdit' => true,
				'canEdit' => true,
				'canAddNew' => true,
				'canDelete' => true,
				'additionalOneToManyActions' => [],
			],
		];
	}

	public function __toString() {
		return 'Library ' . $this->libraryId . ' (' . $this->apiUsername . ')';
	}

	public function update($context = '') {
		$ret = parent::update();
		if ($ret !== FALSE) {
			$this->saveScopes();
		}
		return true;
	}

	public function insert($context = '') {
		$ret = parent::insert();
		if ($ret !== FALSE) {
			if (empty($this->_scopes)) {
				$this->_scopes = [];
				$allScope = new HooplaScope();
				$allScope->settingId = $this->id;
				$allScope->name = "All Records";
				$allScope->includeEAudiobook = true;
				$allScope->maxCostPerCheckoutEAudiobook = 5;
				$allScope->includeEBooks = true;
				$allScope->maxCostPerCheckoutEBooks = 5;
				$allScope->includeEComics = true;
				$allScope->maxCostPerCheckoutEComics = 5;
				$allScope->includeMovies = true;
				$allScope->maxCostPerCheckoutMovies = 5;
				$allScope->includeMusic = true;
				$allScope->maxCostPerCheckoutTelevision = 5;

				$this->_scopes[] = $allScope;
			}
			$this->saveScopes();
		}
		return $ret;
	}

	public function saveScopes() {
		if (isset ($this->_scopes) && is_array($this->_scopes)) {
			$this->saveOneToManyOptions($this->_scopes, 'settingId');
			unset($this->_scopes);
		}
	}

	public function __get($name) {
		if ($name == "scopes") {
			if (!isset($this->_scopes) && $this->id) {
				$this->_scopes = [];
				$scope = new HooplaScope();
				$scope->settingId = $this->id;
				$scope->find();
				while ($scope->fetch()) {
					$this->_scopes[$scope->id] = clone($scope);
				}
			}
			return $this->_scopes;
		} else {
			return parent::__get($name);
		}
	}

	public function __set($name, $value) {
		if ($name == "scopes") {
			$this->_scopes = $value;
		} else {
			parent::__set($name, $value);
		}
	}
}