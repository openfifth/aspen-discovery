<?php /** @noinspection PhpMissingFieldTypeInspection */
require_once ROOT_DIR . '/sys/AspenLiDA/HomeScreenLinkGroupEntry.php';

class HomeScreenLinkGroup extends DataObject {
	public $__table = 'aspen_lida_home_screen_link_group';
	public $__displayNameColumn = 'name';
	public $id;
	public $name;

	/** @var HomeScreenLinkGroupEntry[] */
	protected $_homeScreenLinks;

	protected $_libraries;
	protected $_locations;

	static $_objectStructure = [];

	static function getObjectStructure(string $context = ''): array {
		if (isset(self::$_objectStructure[$context]) && self::$_objectStructure[$context] !== null) {
			return self::$_objectStructure[$context];
		}
		$libraryList = Library::getLibraryList(!UserAccount::userHasPermission('Administer All Aspen LiDA Home Screen Links'));
		$locationList = Location::getLocationList(!UserAccount::userHasPermission('Administer All Aspen LiDA Home Screen Links'));

		$homeScreenLinkStructure = HomeScreenLinkGroupEntry::getObjectStructure($context);
		unset($homeScreenLinkStructure['weight']);
		unset($homeScreenLinkStructure['homeScreenLinkGroupId']);

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
				'description' => 'The name of the group',
				'maxLength' => 50,
				'required' => true,
			],

			'homeScreenLinks' => [
				'property' => 'homeScreenLinks',
				'type' => 'oneToMany',
				'label' => 'Home Screen Links',
				'description' => 'The links to display on the home screen for this group',
				'keyThis' => 'id',
				'keyOther' => 'homeScreenLinkGroupId',
				'subObjectType' => 'HomeScreenLinkGroupEntry',
				'structure' => $homeScreenLinkStructure,
				'sortable' => true,
				'storeDb' => true,
				'allowEdit' => true,
				'canEdit' => true,
				'canAddNew' => true,
				'canDelete' => true,
			],

			'libraries' => [
				'property' => 'libraries',
				'type' => 'multiSelect',
				'listStyle' => 'checkboxSimple',
				'label' => 'Libraries',
				'description' => 'Define libraries that use this browse category group',
				'values' => $libraryList,
			],

			'locations' => [
				'property' => 'locations',
				'type' => 'multiSelect',
				'listStyle' => 'checkboxSimple',
				'label' => 'Locations',
				'description' => 'Define locations that use this browse category group',
				'values' => $locationList,
			],
		];

		self::$_objectStructure[$context] = $structure;
		return self::$_objectStructure[$context];
	}

	public function getHomeScreenLinks(): ?array {
		if (!isset($this->_homeScreenLinks) && $this->id) {
			$this->_homeScreenLinks = [];
			$homeScreenLink = new HomeScreenLinkGroupEntry();
			$homeScreenLink->homeScreenLinkGroupId = $this->id;
			$homeScreenLink->orderBy('weight');
			$homeScreenLink->find();
			while ($homeScreenLink->fetch()) {
				$this->_homeScreenLinks[$homeScreenLink->id] = clone($homeScreenLink);
			}
		}
		return $this->_homeScreenLinks;
	}
}