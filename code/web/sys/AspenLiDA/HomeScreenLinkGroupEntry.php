<?php /** @noinspection PhpMissingFieldTypeInspection */

require_once ROOT_DIR . '/sys/AspenLiDA/HomeScreenLinkGroup.php';

class HomeScreenLinkGroupEntry extends DataObject {
	public $__table = 'aspen_lida_home_screen_link_group_entry';
	public $id;
	public $weight;
	public $homeScreenLinkGroupId;
	public $homeScreenLinkId;

	function getUniquenessFields(): array {
		return [
			'homeScreenLinkGroupId',
			'homeScreenLinkId',
		];
	}

	static $_objectStructure = [];

	static function getObjectStructure(string $context = ''): array {
		if (isset(self::$_objectStructure[$context]) && self::$_objectStructure[$context] !== null) {
			return self::$_objectStructure[$context];
		}
		//Load Groups for lookup values
		$groups = new HomeScreenLinkGroup();
		$groups->orderBy('name');
		$groups->find();
		$groupList = [];
		while ($groups->fetch()) {
			$groupList[$groups->id] = $groups->name;
		}
		require_once ROOT_DIR . '/sys/AspenLiDA/HomeScreenLink.php';
		$homeScreenLinks = new HomeScreenLink();
		$homeScreenLinks->orderBy('title');
		$homeScreenLinksList = [];
		if (!UserAccount::userHasPermission('Administer All Aspen LiDA Home Screen Links')) {
			$library = Library::getPatronHomeLibrary(UserAccount::getActiveUserObj());
			$libraryId = $library == null ? -1 : $library->libraryId;
			$homeScreenLinks->whereAdd("sharing = 'everyone'");
			$homeScreenLinks->whereAdd("sharing = 'library' AND libraryId = " . $libraryId, 'OR');
			$homeScreenLinks->find();
			while ($homeScreenLinks->fetch()) {
				$homeScreenLinksList[$homeScreenLinks->id] = $homeScreenLinks->title . " ($homeScreenLinks->textId)" . " - $homeScreenLinks->id";
			}
		} elseif (UserAccount::userHasPermission('Administer All Aspen LiDA Home Screen Links')) {
			$homeScreenLinks->find();
			while ($homeScreenLinks->fetch()) {
				$homeScreenLinksList[$homeScreenLinks->id] = $homeScreenLinks->title . " ($homeScreenLinks->textId)" . " - $homeScreenLinks->id";
			}
		}
		$homeScreenLinks = new HomeScreenLink();
		$homeScreenLinks->orderBy('title');
		$homeScreenLinks->find();
		$allHomeScreenLinksList = [];
		while ($homeScreenLinks->fetch()) {
			$allHomeScreenLinksList[$homeScreenLinks->id] = $homeScreenLinks->title . " ($homeScreenLinks->textId)" . " - $browseCategories->id";
		}
		$structure = [
			'id' => [
				'property' => 'id',
				'type' => 'label',
				'label' => 'Id',
				'description' => 'The unique id of the hours within the database',
			],
			'homeScreenLinkGroupId' => [
				'property' => 'homeScreenLinkGroupId',
				'type' => 'enum',
				'values' => $groupList,
				'label' => 'Group',
				'description' => 'The group the home screen link should be added in',
			],
			'homeScreenLinkId' => [
				'property' => 'homeScreenLinkId',
				'type' => 'enum',
				'values' => $homeScreenLinksList,
				'allValues' => $allHomeScreenLinksList,
				'label' => 'Home Screen Link',
				'description' => 'The home screen link to display ',
			],
			'weight' => [
				'property' => 'weight',
				'type' => 'numeric',
				'label' => 'Weight',
				'weight' => 'Defines how lists are sorted within the group.  Lower weights are displayed to the left of the screen.',
				'required' => true,
			],
		];

		self::$_objectStructure[$context] = $structure;
		return self::$_objectStructure[$context];
	}

	/** @noinspection PhpUnusedParameterInspection */
	public function getEditLink(string $context): string {
		return '/Admin/BrowseCategories?objectAction=edit&id=' . $this->browseCategoryId;
	}

	protected $_browseCategory = null;

	function getBrowseCategory(): BrowseCategory|false {
		if ($this->_browseCategory == null) {
			require_once ROOT_DIR . '/sys/Browse/BrowseCategory.php';
			$this->_browseCategory = new BrowseCategory();
			$this->_browseCategory->id = $this->browseCategoryId;
			if (!$this->_browseCategory->find(true)) {
				$this->_browseCategory = false;
			}
		}
		return $this->_browseCategory;
	}

	public function canActiveUserChangeSelection(): bool {
		if (UserAccount::userHasPermission('Administer Selected Browse Category Groups')) {
			//Always allow since the only way they can get here is by editing a group they have access to
			return true;
		} else {
			$library = Library::getPatronHomeLibrary(UserAccount::getActiveUserObj());
			$libraryId = $library == null ? -1 : $library->libraryId;
			$browseCatId = $this->getBrowseCategory()->libraryId;
			if (($this->getBrowseCategory()->sharing == 'everyone') || (UserAccount::userHasPermission('Administer All Browse Categories'))) {
				return true;
			} else if ($browseCatId == $libraryId) {
				return UserAccount::userHasPermission('Administer Library Browse Categories');
			}
		}
		return false;
	}

	public function canActiveUserDelete(): bool {
		return UserAccount::userHasPermission('Administer All Browse Categories') || UserAccount::userHasPermission('Administer Library Browse Categories') || UserAccount::userHasPermission('Administer Selected Browse Category Groups');
	}

	public function canActiveUserEdit(): bool {
		if (UserAccount::userHasPermission('Administer Selected Browse Category Groups')) {
			//Always allow since the only way they can get here is by editing a group they have access to
			return true;
		} else {
			$library = Library::getPatronHomeLibrary(UserAccount::getActiveUserObj());
			$libraryId = $library == null ? -1 : $library->libraryId;
			$browseCatId = $this->getBrowseCategory()->libraryId;
			if (($this->getBrowseCategory()->sharing == 'everyone') || (UserAccount::userHasPermission('Administer All Browse Categories'))) {
				return true;
			} elseif ($browseCatId == $libraryId) {
				return UserAccount::userHasPermission('Administer Library Browse Categories');
			}
		}
		return false;
	}

	public function toArray($includeRuntimeProperties = true, $encryptFields = false): array {
		//Unset ids for group and browse category since they will be set by links
		$return = parent::toArray($includeRuntimeProperties, $encryptFields);
		unset($return['browseCategoryGroupId']);
		unset($return['browseCategoryId']);
		return $return;
	}

	public function getLinksForJSON(): array {
		$links = parent::getLinksForJSON();
		$browseCategory = $this->getBrowseCategory();
		$browseCategoryArray = $browseCategory->toArray();
		$browseCategoryArray['links'] = $browseCategory->getLinksForJSON();
		$links['browseCategory'] = $browseCategoryArray;
		return $links;
	}

	public function loadEmbeddedLinksFromJSON($jsonData, $mappings, string $overrideExisting = 'keepExisting'): void {
		parent::loadRelatedLinksFromJSON($jsonData, $mappings, $overrideExisting);
		if (array_key_exists('browseCategory', $jsonData)) {
			require_once ROOT_DIR . '/sys/Browse/BrowseCategory.php';
			$browseCategory = new BrowseCategory();
			$browseCategory->loadFromJSON($jsonData['browseCategory'], $mappings, $overrideExisting);
			$this->browseCategoryId = $browseCategory->id;
		}
	}

	public function isDismissed(): bool {
		require_once ROOT_DIR . '/sys/Browse/BrowseCategory.php';
		require_once ROOT_DIR . '/sys/Browse/BrowseCategoryDismissal.php';
		if (UserAccount::isLoggedIn()) {
			$browseCategory = new BrowseCategory();
			$browseCategory->id = $this->browseCategoryId;
			if ($browseCategory->find(true)) {
				$browseCategoryDismissal = new BrowseCategoryDismissal();
				$browseCategoryDismissal->browseCategoryId = $browseCategory->textId;
				$browseCategoryDismissal->userId = UserAccount::getActiveUserId();
				if ($browseCategoryDismissal->find(true)) {
					return true;
				}
			}
			return false;
		}
		return false;
	}

	public function isValidForDisplay($appUser = null, $checkDismiss = true): bool {
		require_once ROOT_DIR . '/sys/Browse/BrowseCategory.php';
		$browseCategory = new BrowseCategory();
		$browseCategory->id = $this->browseCategoryId;

		if ($browseCategory->find(true)) {
			$curTime = time();
			if ($browseCategory->startDate != 0 && $browseCategory->startDate > $curTime) {
				return false;
			}
			if ($browseCategory->endDate != 0 && $browseCategory->endDate < $curTime) {
				return false;
			}

			$user = UserAccount::getActiveUserObj();

			if ($browseCategory->textId == 'system_user_lists' || $browseCategory->textId == 'system_saved_searches' || $browseCategory->textId == 'system_recommended_for_you') {
				if (UserAccount::isLoggedIn()) {
					if ($browseCategory->textId == 'system_saved_searches' && $user->hasSavedSearches()) {
						if ($checkDismiss) {
							if ($this->isDismissed()) {
								return false;
							}
						}
						return true;
					}
					if ($browseCategory->textId == 'system_user_lists' && $user->hasLists()) {
						if ($checkDismiss) {
							if ($this->isDismissed()) {
								return false;
							}
						}
						return true;
					}
					if ($browseCategory->textId == 'system_recommended_for_you' && $user->hasRatings()) {
						if ($checkDismiss) {
							if ($this->isDismissed()) {
								return false;
							}
						}
						return true;
					}

				}
				return false;
			}
		}

		if ($checkDismiss) {
			if (UserAccount::isLoggedIn()) {
				if ($this->isDismissed()) {
					return false;
				}
			}
		}

		return true;
	}

	public function isValidForDisplayInApp($user, $checkDismiss = false): bool {
		require_once ROOT_DIR . '/sys/Browse/BrowseCategory.php';
		$browseCategory = new BrowseCategory();
		$browseCategory->id = $this->browseCategoryId;

		if ($browseCategory->find(true)) {
			$curTime = time();
			if ($browseCategory->startDate != 0 && $browseCategory->startDate > $curTime) {
				return false;
			}
			if ($browseCategory->endDate != 0 && $browseCategory->endDate < $curTime) {
				return false;
			}

			if ($checkDismiss && ($user && !($user instanceof AspenError))) {
				require_once ROOT_DIR . '/sys/Browse/BrowseCategoryDismissal.php';
				$browseCategoryDismissal = new BrowseCategoryDismissal();
				$browseCategoryDismissal->browseCategoryId = $browseCategory->textId;
				$browseCategoryDismissal->userId = $user->id;
				if ($browseCategoryDismissal->find(true)) {
					return false;
				}
			}

			if ($browseCategory->textId == 'system_user_lists' || $browseCategory->textId == 'system_saved_searches' || $browseCategory->textId == 'system_recommended_for_you') {
				if (!$user || ($user instanceof AspenError)) {
					return false;
				}
			}

			return true;
		}
		return false;
	}
}