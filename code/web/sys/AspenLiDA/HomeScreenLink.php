<?php /** @noinspection PhpMissingFieldTypeInspection */

require_once ROOT_DIR . '/sys/AspenLiDA/HomeScreenLinkGroup.php';
require_once ROOT_DIR . '/sys/AspenLiDA/LocationSetting.php';

class HomeScreenLink extends DataObject {
	public $__table = 'aspen_lida_home_screen_link';
	public $id;
	public $title;
	public $textId;

	public $userId;
	public $sharing;
	public $libraryId;

	public $typeOfIcon;
	public $materialIcon;
	public $uploadIcon;
	public $linkType;
	public $deepLinkPath;
	public $deepLinkId;
	public $linkUrl;

	function getNumericColumnNames(): array {
		return [
			'id',
			'userId',
		];
	}

	function getUniquenessFields(): array {
		return ['textId'];
	}

	static $_objectStructure = [];

	static function getObjectStructure(string $context = ''): array {
		if (isset(self::$_objectStructure[$context]) && self::$_objectStructure[$context] !== null) {
			return self::$_objectStructure[$context];
		}

		$libraryList = Library::getLibraryList(!UserAccount::userHasPermission('Administer All Aspen LiDA Home Screen Links'));
		$libraryList[-1] = 'No Library Selected';

		$structure = [
			'id' => [
				'property' => 'id',
				'type' => 'label',
				'label' => 'Id',
				'description' => 'The unique id',
			],
			'title' => [
				'property' => 'title',
				'type' => 'text',
				'label' => 'Title',
				'description' => 'The title to display for the home screen link',
				'maxLength' => 100,
				'required' => true,
			],
			'textId' => [
				'property' => 'textId',
				'type' => 'text',
				'label' => 'textId',
				'description' => 'A textual id to identify the home screen link',
				'serverValidation' => 'validateTextId',
				'maxLength' => 150,
			],
			'userId' => [
				'property' => 'userId',
				'type' => 'label',
				'label' => 'userId',
				'description' => 'The User Id who created this home screen link',
				'default' => UserAccount::getActiveUserId(),
			],
			'sharing' => [
				'property' => 'sharing',
				'type' => 'enum',
				'values' => [
					'library' => 'Selected Library',
					'everyone' => 'Everyone',
				],
				'label' => 'Share With',
				'description' => 'Who the category should be shared with',
				'default' => 'library',
				'onchange' => 'return AspenDiscovery.Admin.updateBrowseCategoryFields();',
			],
			'libraryId' => [
				'property' => 'libraryId',
				'type' => 'enum',
				'values' => $libraryList,
				'label' => 'Library',
				'description' => 'A link to the library which the location belongs to',
			],
			'typeOfIcon' => [
				'property' => 'typeOfIcon',
				'type' => 'enum',
				'values' => [
					'imageUpload' => 'Image Upload',
					'materialIcon' => 'Material Icon',
				],
				'label' => 'Type of Icon',
				'description' => 'The type of icon to represent the link',
				'default' => 'none',
				'onchange' => 'return AspenDiscovery.Admin.toggleHomeScreenIconTypeFields();',
			],
			'materialIcon' => [
				'property' => 'materialIcon',
				'type' => 'enum',
				'label' => 'Material Icon',
				'values' => self::getMaterialIconsList(),
				'description' => 'A Google Material Icon to represent the link',
				'hideInLists' => true,
			],
			'uploadIcon' => [
				'property' => 'uploadIcon',
				'type' => 'image',
				'label' => 'Upload an Icon',
				'description' => 'An uploaded icon to represent the link',
				'hideInLists' => true,
			],
			'linkType' => [
				'property' => 'linkType',
				'type' => 'enum',
				'label' => 'On tap, send user to',
				'values' => [
					0 => 'A specific screen in the app',
					1 => 'An external website',
				],
				'default' => 0,
				'onchange' => 'return AspenDiscovery.Admin.getUrlOptions();',
				'hideInLists' => true,
				'canSort' => false,
			],
			'deepLinkPath' => [
				'property' => 'deepLinkPath',
				'type' => 'enum',
				'label' => 'Aspen LiDA Screen',
				'values' => LocationSetting::getDeepLinks(),
				'default' => 'home',
				'onchange' => 'return AspenDiscovery.Admin.getDeepLinkFullPath();',
				'hideInLists' => true,
				'canSort' => false,
			],
			'deepLinkId' => [
				'property' => 'deepLinkId',
				'type' => 'text',
				'label' => 'Id for Object',
				'hideInLists' => true,
				'canSort' => false,
			],
			'linkUrl' => [
				'property' => 'linkUrl',
				'type' => 'url',
				'label' => 'External URL',
				'description' => 'A URL for users to be redirected to when opening the home screen link',
				'hideInLists' => true,
				'canSort' => false,
			],
		];

		self::$_objectStructure[$context] = $structure;
		return self::$_objectStructure[$context];
	}

	private static function getMaterialIconsList(): array {
		$icons = [
			// Navigation
			'home' => [
				'name' => 'home',
				'label' => 'Home'
			],
			'menu' => [
				'name' => 'menu',
				'label' => 'Menu'
			],
			'arrow_back' => [
				'name' => 'arrow_back',
				'label' => 'Arrow Back'
			],
			'arrow_forward' => [
				'name' => 'arrow_forward',
				'label' => 'Arrow Forward'
			],
			'close' => [
				'name' => 'close',
				'label' => 'Close'
			],
			'more_vert' => [
				'name' => 'more_vert',
				'label' => 'More Vertical'
			],
			'more_horiz' => [
				'name' => 'more_horiz',
				'label' => 'More Horizontal'
			],
			'expand_more' => [
				'name' => 'expand_more',
				'label' => 'Expand More'
			],
			'expand_less' => [
				'name' => 'expand_less',
				'label' => 'Expand Less'
			],
			'refresh' => [
				'name' => 'refresh',
				'label' => 'Refresh'
			],

			// Content
			'search' => [
				'name' => 'search',
				'label' => 'Search'
			],
			'library_books' => [
				'name' => 'library_books',
				'label' => 'Library Books'
			],
			'menu_book' => [
				'name' => 'menu_book',
				'label' => 'Menu Book'
			],
			'book' => [
				'name' => 'book',
				'label' => 'Book'
			],
			'article' => [
				'name' => 'article',
				'label' => 'Article'
			],
			'audiobook' => [
				'name' => 'audiobook',
				'label' => 'Audiobook'
			],
			'headphones' => [
				'name' => 'headphones',
				'label' => 'Headphones'
			],
			'movie' => [
				'name' => 'movie',
				'label' => 'Movie'
			],
			'video_library' => [
				'name' => 'video_library',
				'label' => 'Video Library'
			],
			'music_note' => [
				'name' => 'music_note',
				'label' => 'Music Note'
			],
			'library_music' => [
				'name' => 'library_music',
				'label' => 'Music Library'
			],
			'image' => [
				'name' => 'image',
				'label' => 'Image'
			],
			'photo_library' => [
				'name' => 'photo_library',
				'label' => 'Photo Library'
			],

			// Events & Calendar
			'event' => [
				'name' => 'event',
				'label' => 'Event'
			],
			'calendar_today' => [
				'name' => 'calendar_today',
				'label' => 'Calendar Today'
			],
			'schedule' => [
				'name' => 'schedule',
				'label' => 'Schedule'
			],
			'access_time' => [
				'name' => 'access_time',
				'label' => 'Access Time'
			],

			// Information & Help
			'help' => [
				'name' => 'help',
				'label' => 'Help'
			],
			'help_outline' => [
				'name' => 'help_outline',
				'label' => 'Help Outline'
			],
			'info' => [
				'name' => 'info',
				'label' => 'Info'
			],
			'info_outline' => [
				'name' => 'info_outline',
				'label' => 'Info Outline'
			],
			'announcement' => [
				'name' => 'announcement',
				'label' => 'Announcement'
			],
			'notifications' => [
				'name' => 'notifications',
				'label' => 'Notifications'
			],

			// Actions
			'favorite' => [
				'name' => 'favorite',
				'label' => 'Favorite'
			],
			'favorite_border' => [
				'name' => 'favorite_border',
				'label' => 'Favorite Border'
			],
			'star' => [
				'name' => 'star',
				'label' => 'Star'
			],
			'star_border' => [
				'name' => 'star_border',
				'label' => 'Star Border'
			],
			'bookmark' => [
				'name' => 'bookmark',
				'label' => 'Bookmark'
			],
			'bookmark_border' => [
				'name' => 'bookmark_border',
				'label' => 'Bookmark Border'
			],
			'share' => [
				'name' => 'share',
				'label' => 'Share'
			],
			'download' => [
				'name' => 'download',
				'label' => 'Download'
			],
			'upload' => [
				'name' => 'upload',
				'label' => 'Upload'
			],
			'add' => [
				'name' => 'add',
				'label' => 'Add'
			],
			'remove' => [
				'name' => 'remove',
				'label' => 'Remove'
			],
			'edit' => [
				'name' => 'edit',
				'label' => 'Edit'
			],
			'delete' => [
				'name' => 'delete',
				'label' => 'Delete'
			],

			// User & Account
			'account_circle' => [
				'name' => 'account_circle',
				'label' => 'Account Circle'
			],
			'person' => [
				'name' => 'person',
				'label' => 'Person'
			],
			'people' => [
				'name' => 'people',
				'label' => 'People'
			],
			'group' => [
				'name' => 'group',
				'label' => 'Group'
			],
			'login' => [
				'name' => 'login',
				'label' => 'Login'
			],
			'logout' => [
				'name' => 'logout',
				'label' => 'Logout'
			],

			// Settings & Configuration
			'settings' => [
				'name' => 'settings',
				'label' => 'Settings'
			],
			'tune' => [
				'name' => 'tune',
				'label' => 'Tune'
			],
			'filter_alt' => [
				'name' => 'filter_alt',
				'label' => 'Filter'
			],
			'sort' => [
				'name' => 'sort',
				'label' => 'Sort'
			],

			// Communication
			'email' => [
				'name' => 'email',
				'label' => 'Email'
			],
			'phone' => [
				'name' => 'phone',
				'label' => 'Phone'
			],
			'chat' => [
				'name' => 'chat',
				'label' => 'Chat'
			],
			'message' => [
				'name' => 'message',
				'label' => 'Message'
			],

			// Location & Maps
			'location_on' => [
				'name' => 'location_on',
				'label' => 'Location On'
			],
			'place' => [
				'name' => 'place',
				'label' => 'Place'
			],
			'map' => [
				'name' => 'map',
				'label' => 'Map'
			],
			'directions' => [
				'name' => 'directions',
				'label' => 'Directions'
			],

			// Technology
			'computer' => [
				'name' => 'computer',
				'label' => 'Computer'
			],
			'laptop' => [
				'name' => 'laptop',
				'label' => 'Laptop'
			],
			'tablet' => [
				'name' => 'tablet',
				'label' => 'Tablet'
			],
			'smartphone' => [
				'name' => 'smartphone',
				'label' => 'Smartphone'
			],
			'wifi' => [
				'name' => 'wifi',
				'label' => 'WiFi'
			],
			'print' => [
				'name' => 'print',
				'label' => 'Print'
			],

			// Security & Privacy
			'lock' => [
				'name' => 'lock',
				'label' => 'Lock'
			],
			'lock_open' => [
				'name' => 'lock_open',
				'label' => 'Lock Open'
			],
			'security' => [
				'name' => 'security',
				'label' => 'Security'
			],
			'visibility' => [
				'name' => 'visibility',
				'label' => 'Visibility'
			],
			'visibility_off' => [
				'name' => 'visibility_off',
				'label' => 'Visibility Off'
			],

			// Shopping & Finance
			'shopping_cart' => [
				'name' => 'shopping_cart',
				'label' => 'Shopping Cart'
			],
			'payment' => [
				'name' => 'payment',
				'label' => 'Payment'
			],
			'credit_card' => [
				'name' => 'credit_card',
				'label' => 'Credit Card'
			],
			'receipt' => [
				'name' => 'receipt',
				'label' => 'Receipt'
			],

			// File & Document
			'folder' => [
				'name' => 'folder',
				'label' => 'Folder'
			],
			'folder_open' => [
				'name' => 'folder_open',
				'label' => 'Folder Open'
			],
			'description' => [
				'name' => 'description',
				'label' => 'Description'
			],
			'file_copy' => [
				'name' => 'file_copy',
				'label' => 'File Copy'
			],
			'attach_file' => [
				'name' => 'attach_file',
				'label' => 'Attach File'
			],

			// Status & Indicators
			'check' => [
				'name' => 'check',
				'label' => 'Check'
			],
			'check_circle' => [
				'name' => 'check_circle',
				'label' => 'Check Circle'
			],
			'error' => [
				'name' => 'error',
				'label' => 'Error'
			],
			'warning' => [
				'name' => 'warning',
				'label' => 'Warning'
			],
			'done' => [
				'name' => 'done',
				'label' => 'Done'
			],

			// Lists & Organization
			'list' => [
				'name' => 'list',
				'label' => 'List'
			],
			'view_list' => [
				'name' => 'view_list',
				'label' => 'View List'
			],
			'grid_view' => [
				'name' => 'grid_view',
				'label' => 'Grid View'
			],
			'dashboard' => [
				'name' => 'dashboard',
				'label' => 'Dashboard'
			],
			'category' => [
				'name' => 'category',
				'label' => 'Category'
			],
			'label' => [
				'name' => 'label',
				'label' => 'Label'
			],
		];

		$iconList = [];
		foreach ($icons as $key => $icon) {
			$iconList[$key] = $icon['label'];
		}

		return $iconList;
	}

	/** @noinspection PhpUnusedParameterInspection */
	public function getEditLink(string $context): string {
		return '/AspenLiDA/HomeScreenLinks?objectAction=edit&id=' . $this->id;
	}

	function validateTextId(): array {
		$validationResults = [
			'validatedOk' => true,
			'errors' => [],
		];

		if (!$this->textId || strlen($this->textId) == 0) {
			$this->textId = $this->label . ' ' . $this->sharing;
			if ($this->sharing == 'private') {
				$this->textId .= '_' . $this->userId;
			} elseif ($this->sharing == 'location') {
				$location = Location::getUserHomeLocation();
				$this->textId .= '_' . $location->code;
			} elseif ($this->sharing == 'library') {
				$this->textId .= '_' . Library::getPatronHomeLibrary()->subdomain;
			}
		}

		$this->textId = strtolower($this->textId);
		// Convert any non-word characters to an underscore.
		$this->textId = preg_replace('/\W/', '_', $this->textId);
		// Ensure the length is 150 or fewer characters.
		if (strlen($this->textId) > 150) {
			$this->textId = substr($this->textId, 0, 150);
		}

		return $validationResults;
	}

	public function canActiveUserEdit(): bool {
		if ($this->sharing == 'everyone') {
			return UserAccount::userHasPermission('Administer All Aspen LiDA Home Screen Links') || ($this->userId == UserAccount::getActiveUserId());
		}
		return true;
	}

	public function toArray($includeRuntimeProperties = true, $encryptFields = false): array {
		$return = parent::toArray($includeRuntimeProperties, $encryptFields);
		unset ($return['libraryId']);
		unset ($return['userId']);

		return $return;
	}

	public function getLinksForJSON(): array {
		$links = parent::getLinksForJSON();
		//library
		$allLibraries = Library::getLibraryListAsObjects(false);
		if (array_key_exists($this->libraryId, $allLibraries)) {
			$library = $allLibraries[$this->libraryId];
			$links['library'] = empty($library->subdomain) ? $library->ilsCode : $library->subdomain;
		}
		//user
		$user = new User();
		$user->id = $this->userId;
		if ($user->find(true)) {
			$links['user'] = $user->ils_barcode;
		}

		return $links;
	}

	public function loadEmbeddedLinksFromJSON($jsonData, $mappings, string $overrideExisting = 'keepExisting'): void {
		parent::loadEmbeddedLinksFromJSON($jsonData, $mappings, $overrideExisting);

		if (isset($jsonData['library'])) {
			$allLibraries = Library::getLibraryListAsObjects(false);
			$subdomain = $jsonData['library'];
			if (array_key_exists($subdomain, $mappings['libraries'])) {
				$subdomain = $mappings['libraries'][$subdomain];
			}
			foreach ($allLibraries as $tmpLibrary) {
				if ($tmpLibrary->subdomain == $subdomain || $tmpLibrary->ilsCode == $subdomain) {
					$this->libraryId = $tmpLibrary->libraryId;
					break;
				}
			}
		}
		if (isset($jsonData['user'])) {
			$username = $jsonData['user'];
			$user = new User();
			$user->ils_barcode = $username;
			if ($user->find(true)) {
				$this->userId = $user->id;
			}
		}
	}

	public function loadRelatedLinksFromJSON($jsonData, $mappings, string $overrideExisting = 'keepExisting'): bool {
		$result = parent::loadRelatedLinksFromJSON($jsonData, $mappings, $overrideExisting);
		return $result;
	}

}
