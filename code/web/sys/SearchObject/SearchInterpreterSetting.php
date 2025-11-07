<?php /** @noinspection PhpMissingFieldTypeInspection */
require_once ROOT_DIR . '/sys/SearchObject/SearchInterpreterTermsToSkip.php';
require_once ROOT_DIR . '/sys/SearchObject/SearchInterpreterSpecialTerms.php';

class SearchInterpreterSetting extends DataObject {
	public $__table = 'search_interpreter_settings';
	public $id;
	public $processFormatCategories;
	public $formatCategoriesToSkip;
	public $processFormats;
	public $formatsToSkip;
	public $processPluralFormats;
	public $pluralFormatsToSkip;
	public $processAudiences;
	public $audiencesToSkip;
	public $processPluralAudiences;
	public $pluralAudiencesToSkip;
	public $audienceFacet;
	public $processFictionNonFiction;
	public $fictionNonFictionFacet;
	public $processNew;
	public $processAvailable;

	public $_termsToSkip;
	public $_specialTerms;


	static $_objectStructure = [];
	static function getObjectStructure(string $context = ''): array {
		if (isset(self::$_objectStructure[$context]) && self::$_objectStructure[$context] !== null) {
			return self::$_objectStructure[$context];
		}

		require_once ROOT_DIR . '/sys/Indexing/IndexedFormat.php';
		$indexedFormatObj = new IndexedFormat();
		$indexedFormatObj->orderBy('format');
		$indexedFormats = $indexedFormatObj->fetchAll('format');

		$termsToSkipStructure = SearchInterpreterTermsToSkip::getObjectStructure($context);
		$specialTermsStructure = SearchInterpreterSpecialTerms::getObjectStructure($context);

		$structure = [
			'id' => [
				'property' => 'id',
				'type' => 'label',
				'label' => 'Id',
				'description' => 'The unique id within the database',
				'uniqueProperty' => true,
			],
			'formatSection' => [
				'property' => 'formatSection',
				'type' => 'section',
				'label' => 'Formats',
				'hideInLists' => true,
				'expandByDefault' => true,
				'properties' => [
					'processFormats' => [
						'property' => 'processFormats',
						'type' => 'checkbox',
						'label' => 'Process Formats',
						'description' => 'Whether formats should be processed in the interpreter.',
						'hideInLists' => true,
						'default' => 1,
					],
					'formatsToSkip' => [
						'property' => 'formatsToSkip',
						'type' => 'text',
						'label' => 'Formats to Skip',
						'description' => 'A pipe delimited list of values to skip',
						'noteBullets' => [
							'Separate values with pipes or commas',
							'Valid values are ' . implode(', ', $indexedFormats),
						],
						'default' => '',
						'maxLength' => 255
					],
					'processPluralFormats' => [
						'property' => 'processPluralFormats',
						'type' => 'checkbox',
						'label' => 'Process Plural Formats',
						'description' => 'Whether formats should be processed with an added s in the interpreter.',
						'hideInLists' => true,
						'default' => 1,
					],
					'pluralFormatsToSkip' => [
						'property' => 'pluralFormatsToSkip',
						'type' => 'text',
						'label' => 'Plural Formats to Skip',
						'description' => 'A pipe delimited list of values to skip',
						'default' => '',
						'maxLength' => 255
					],
				],
			],
			'formatCategoriesSection' => [
				'property' => 'formatCategoriesSection',
				'type' => 'section',
				'label' => 'Format Categories',
				'hideInLists' => true,
				'expandByDefault' => true,
				'properties' => [
					'processFormatCategories' => [
						'property' => 'processFormatCategories',
						'type' => 'checkbox',
						'label' => 'Process Format Categories',
						'description' => 'Whether format categories should be processed in the interpreter.',
						'hideInLists' => true,
						'default' => 1,
					],
					'formatCategoriesToSkip' => [
						'property' => 'formatCategoriesToSkip',
						'type' => 'text',
						'label' => 'Format Categories to Skip',
						'description' => 'A pipe delimited list of values to skip',
						'note' => 'Valid values are Books,eBooks,Audio Books,Music,Movies',
						'default' => '',
						'maxLength' => 255
					],
				],
			],
			'audienceSection' => [
				'property' => 'audienceSection',
				'type' => 'section',
				'label' => 'Audiences',
				'hideInLists' => true,
				'expandByDefault' => true,
				'properties' => [
					'processAudiences' => [
						'property' => 'processAudiences',
						'type' => 'checkbox',
						'label' => 'Process Audiences',
						'description' => 'Whether audiences should be processed in the interpreter.',
						'hideInLists' => true,
						'default' => 1,
					],
					'audiencesToSkip' => [
						'property' => 'audiencesToSkip',
						'type' => 'text',
						'label' => 'Audience to Skip',
						'description' => 'A pipe delimited list of values to skip',
						'note' => 'Valid values are Kid,Teen,Adult',
						'default' => '',
						'maxLength' => 255
					],
					'processPluralAudiences' => [
						'property' => 'processPluralAudiences',
						'type' => 'checkbox',
						'label' => 'Process Plural Audiences',
						'description' => 'Whether audiences should be processed with an added s in the interpreter.',
						'note' => 'Valid values are Kids,Teens,Adults',
						'hideInLists' => true,
						'default' => 1,
					],
					'pluralAudiencesToSkip' => [
						'property' => 'pluralAudiencesToSkip',
						'type' => 'text',
						'label' => 'Plural Audiences to Skip',
						'description' => 'A pipe delimited list of values to skip',
						'default' => '',
					],
					'audienceFacet' => [
						'property' => 'audienceFacet',
						'type' => 'text',
						'label' => 'Audience Facet',
						'description' => 'The name of the facet to apply matches to',
						'default' => '',
						'maxLength' => 50
					],
				],
			],
			'fictionSection' => [
				'property' => 'fictionSection',
				'type' => 'section',
				'label' => 'Fiction/Non-Fiction',
				'hideInLists' => true,
				'expandByDefault' => true,
				'properties' => [
					'processFictionNonFiction' => [
						'property' => 'processFictionNonFiction',
						'type' => 'checkbox',
						'label' => 'Process Fiction/Non-Fiction',
						'description' => 'Whether fiction/non-fiction should be processed in the interpreter.',
						'hideInLists' => true,
						'default' => 1,
					],
					'fictionNonFictionFacet' => [
						'property' => 'fictionNonFictionFacet',
						'type' => 'text',
						'label' => 'Fiction/Non Fiction Facet',
						'description' => 'The name of the facet to apply matches to',
						'default' => '',
						'maxLength' => 50
					],
				],
			],
			'modifiersSection' => [
				'property' => 'modifiersSection',
				'type' => 'section',
				'label' => 'Modifiers',
				'hideInLists' => true,
				'expandByDefault' => true,
				'properties' => [
					'processNew' => [
						'property' => 'processNew',
						'type' => 'checkbox',
						'label' => 'Process New Modifier',
						'description' => 'Whether the search should be checked for new searches.',
						'hideInLists' => true,
						'default' => 1,
					],
					'processAvailable' => [
						'property' => 'processAvailable',
						'type' => 'checkbox',
						'label' => 'Process Available Modifier',
						'description' => 'Whether the search should be checked for available searches.',
						'hideInLists' => true,
						'default' => 1,
					],
				],
			],
			'termsToSkip' => [
				'property' => 'termsToSkip',
				'type' => 'oneToMany',
				'label' => 'Terms To Skip Interpreting',
				'description' => 'A list of terms that will cause the search to not be interpreted.',
				'keyThis' => 'id',
				'keyOther' => 'settingId',
				'subObjectType' => 'SearchInterpreterTermsToSkip',
				'structure' => $termsToSkipStructure,
				'sortable' => false,
				'storeDb' => true,
				'allowEdit' => false,
				'canEdit' => false,
				'canAddNew' => true,
				'canDelete' => true,
			],
			'specialTerms' => [
				'property' => 'specialTerms',
				'type' => 'oneToMany',
				'label' => 'Special Terms',
				'description' => 'A list of terms that will be processed according to library defined rules.',
				'keyThis' => 'id',
				'keyOther' => 'settingId',
				'subObjectType' => 'SearchInterpreterSpecialTerms',
				'structure' => $specialTermsStructure,
				'sortable' => false,
				'storeDb' => true,
				'allowEdit' => false,
				'canEdit' => false,
				'canAddNew' => true,
				'canDelete' => true,
			]
		];

		self::$_objectStructure[$context] = $structure;
		return self::$_objectStructure[$context];
	}

	public function __get($name) {
		if ($name == "termsToSkip") {
			return $this->getTermsToSkip();
		} else if ($name == "specialTerms") {
			return $this->getSpecialTerms();
		} else {
			return parent::__get($name);
		}
	}

	/**
	 * @return SearchInterpreterTermsToSkip[]
	 */
	public function getTermsToSkip(): array {
		if (!isset($this->_termsToSkip)) {
			$this->_termsToSkip = [];
			if ($this->id) {
				$obj = new SearchInterpreterTermsToSkip();
				$obj->settingId = $this->id;
				$obj->find();
				while ($obj->fetch()) {
					$this->_termsToSkip[$obj->id] = clone $obj;
				}
			}
		}
		return $this->_termsToSkip;
	}

	private ?array $_termsToSkipString = null;

	/**
	 * @return string[]
	 */
	public function getTermsToSkipAsStrings() : array {
		if ($this->_termsToSkipString == null) {
			$obj = new SearchInterpreterTermsToSkip();
			$obj->settingId = $this->id;
			$this->_termsToSkipString = $obj->fetchAll('term');
		}
		return $this->_termsToSkipString;
	}

	/**
	 * @return SearchInterpreterSpecialTerms[]
	 */
	public function getSpecialTerms(): array {
		if (!isset($this->_specialTerms)) {
			$this->_specialTerms = [];
			if ($this->id) {
				$obj = new SearchInterpreterSpecialTerms();
				$obj->settingId = $this->id;
				$obj->find();
				while ($obj->fetch()) {
					$this->_specialTerms[$obj->id] = clone $obj;
				}
			}
		}
		return $this->_specialTerms;
	}

	public function __set($name, $value) {
		if ($name == "termsToSkip") {
			$this->_termsToSkip = $value;
		} else if ($name == "specialTerms") {
			$this->_specialTerms = $value;
		} else {
			parent::__set($name, $value);
		}
	}

	public function update(string $context = '') : int|bool {
		$ret = parent::update();
		if ($ret !== FALSE) {
			$this->saveTermsToSkip();
			$this->saveSpecialTerms();
		}
		return $ret;
	}

	public function insert(string $context = '') : int|bool {
		$ret = parent::insert();
		if ($ret !== FALSE) {
			$this->saveTermsToSkip();
			$this->saveSpecialTerms();
		}
		return $ret;
	}

	public function delete(bool $useWhere = false, bool $hardDelete = false) : bool|int {
		$ret = parent::delete($useWhere, $hardDelete);
		if ($ret && !empty($this->id)) {
			$this->clearOneToManyOptions('SearchInterpreterTermsToSkip', 'settingId');
			$this->clearOneToManyOptions('SearchInterpreterSpecialTerms', 'settingId');
		}
		return $ret;
	}

	public function saveTermsToSkip() : void {
		if (isset ($this->_termsToSkip) && is_array($this->_termsToSkip)) {
			$this->saveOneToManyOptions($this->_termsToSkip, 'settingId');
			unset($this->_termsToSkip);
		}
	}

	public function saveSpecialTerms() : void {
		if (isset ($this->_specialTerms) && is_array($this->_specialTerms)) {
			$this->saveOneToManyOptions($this->_specialTerms, 'settingId');
			unset($this->_specialTerms);
		}
	}
}