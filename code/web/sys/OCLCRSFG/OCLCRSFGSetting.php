<?php 

class OCLCRSFGSetting extends DataObject {
	public $__table = 'oclc_resource_sharing_for_groups_setting';
	public $id;
	public $name;
	public $clientKey; 
	public $clientSecret;
	public $serviceBaseUrl;
	public $authBaseUrl;
	public $scopes;
	public $expirationDate;

	private $_libraries;

	function getEncryptedFieldNames(): array {
		return ['clientKey', 'clientSecret'];
	}

	public static function getObjectStructure($context = ''): array {
		$libraryList = Library::getLibraryList(!UserAccount::userHasPermission('Administer All Libraries'));

		return [
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
				'description' => 'The Name of this Setting Profile',
				'maxLength' => 50,
			],
			'clientKey' => [
				'property' => 'clientKey',
				'type' => 'storedPassword',
				'label' => 'OCLC WSKey Client ID',
				'description' => 'The Client ID of the OCLC-issued WSKey to be used for authentication when making requests to Resource Sharing Requests APIs',
				'hideInLists' => true,
			],
			'clientSecret' => [
				'property' => 'clientSecret',
				'type' => 'storedPassword',
				'label' => 'OCLC WSKey Secret',
				'description' => 'The Secret of the OCLC-issued WSKey to be used for authentication when making requests to Resource Sharing Requests APIs',
				'hideInLists' => true,
			],
			'serviceBaseUrl' => [ // may be redundant - might not change
				'property' => 'serviceBaseUrl',
				'type' => 'url',
				'label' => 'Resource Sharing API URL',
				'description' => 'The base URL of the Resource Sharing Requests API',
				'maxLength' => 255,
			],
			'authBaseUrl' => [ // may be redundant - will not change
				'property' => 'authBaseUrl',
				'type' => 'url',
				'label' => 'OCLC Authentication URL ',
				'description' => 'The base URL of the Resource Sharing Requests API',
				'maxLength' => 255,
				'default' => 'https://oauth.oclc.org/'
			],
			'scopes' => [
				'property' => 'scopes',
				'label' => 'Allowed Request Types',
				'listStyle' => 'checkboxSimple',
				'type' => 'hidden',
				'description' => 'A list of the types of requests that can be made',
				'default' => 'resource-sharing:my-requests resource-sharing:create-requests resource-sharing:manage-requests resource-sharing:read-requests resource-sharing:search-requests',
			],
			'expirationDate' => [
				'property' => 'expirationDate',
				'type' => 'date',
				'label' => 'WSKey Expiration Date',
				'description' => 'The data when the WSKey will expire. Format: MM/DD/YYYY',
			],
			'libraries' => [
				'property' => 'libraries',
				'type' => 'multiSelect',
				'listStyle' => 'checkboxSimple',
				'label' => 'Libraries',
				'description' => 'Define libraries that use these settings',
				'values' => $libraryList,
				'hideInLists' => false,
			],
		];
	}

	public function __get($name) {
		if ($name == 'libraries') {
			if (!isset($this->_libraries) && $this->id) {
				$this->_libraries = [];
				$obj = new Library();
				$obj->oclcRSFGSettingsId = $this->id;
				$obj->find();
				while ($obj->fetch()) {
					$this->_libraries[$obj->libraryId] = $obj->libraryId;
				}
			}
			return $this->_libraries;
		} else {
			return parent::__get($name);
		}
	}

	public function __set($name, $value) {
		if ($name == 'libraries') {
			$this->_libraries = $value;
		} else {
			parent::__set($name, $value);
		}
	}

	public function update($context = '') {
		$ret = parent::update();
		if ($ret !== FALSE) {
			$this->saveLibraries();
		}
		return true;
	}

	public function insert($context = '') {
		$ret = parent::insert();
		if ($ret !== FALSE) {
			$this->saveLibraries();
		}
		return $ret;
	}

	public function saveLibraries(): void {
		if (!isset ($this->_libraries) || !is_array($this->_libraries)) {
			return;
		}
		$libraryList = Library::getLibraryList(!UserAccount::userHasPermission('Administer All Libraries'));
		foreach ($libraryList as $libraryId => $displayName) {
			$library = new Library();
			$library->libraryId = $libraryId;
			$library->find(true);
			if (in_array($libraryId, $this->_libraries)) {
				if ($library->oclcRSFGSettingsId != $this->id) {
					$library->oclcRSFGSettingsId = $this->id;
					$library->update();
				}
			} else {
				if ($library->oclcRSFGSettingsId == $this->id) {
					$library->oclcRSFGSettingsId = -1;
					$library->update();
				}
			}
		}
		unset($this->_libraries);
	}
}
