<?php /** @noinspection PhpMissingFieldTypeInspection */

class Language extends DataObject {
	public $__table = 'languages';
	public $id;
	public $weight;
	public $code;
	public $displayName;
	public $displayNameEnglish;
	public $locale;
	public $facetValue;
	public $displayToTranslatorsOnly;

	static $_objectStructure = [];
	static function getObjectStructure(string $context = ''): array {
		if (isset(self::$_objectStructure[$context]) && self::$_objectStructure[$context] !== null) {
			return self::$_objectStructure[$context];
		}
		$structure = [
			'id' => [
				'property' => 'id',
				'type' => 'hidden',
				'label' => 'Id',
				'description' => 'The unique id',
			],
			'weight' => [
				'property' => 'weight',
				'type' => 'integer',
				'label' => 'Weight',
				'description' => 'The sort order',
				'default' => 0,
			],
			'code' => [
				'property' => 'code',
				'type' => 'text',
				'label' => 'Code',
				'description' => 'The code for the language see https://www.w3schools.com/tags/ref_language_codes.asp',
				'size' => '3',
				'required' => true,
			],
			'displayName' => [
				'property' => 'displayName',
				'type' => 'text',
				'label' => 'Display name - native',
				'description' => 'Display Name for the language in the language itself',
				'size' => '50',
				'required' => true,
			],
			'displayNameEnglish' => [
				'property' => 'displayNameEnglish',
				'type' => 'text',
				'label' => 'Display name - English',
				'description' => 'The display name of the language in English',
				'size' => '50',
				'required' => true,
			],
			'locale' => [
				'property' => 'locale',
				'type' => 'text',
				'label' => 'Locale (i.e. en-US, en-CA, es-US, fr-CA)',
				'description' => 'The locale to use when formatting numbers',
				'default' => 'en-US',
				'required' => true,
			],
			'facetValue' => [
				'property' => 'facetValue',
				'type' => 'text',
				'label' => 'Facet Value',
				'description' => 'The facet value for filtering results and applying preferences',
				'size' => '100',
				'required' => true,
			],
			'displayToTranslatorsOnly' => [
				'property' => 'displayToTranslatorsOnly',
				'type' => 'checkbox',
				'label' => 'Display To Translators Only',
				'description' => 'Whether or not only translators should see the translation (good practice before the translation is completed)',
				'default' => 0,
			],
		];

		self::$_objectStructure[$context] = $structure;
		return self::$_objectStructure[$context];
	}

	/**
	 * @return string[]
	 */
	public static function getLanguageList() : array {
		$language = new Language();
		$language->selectAdd();
		$language->selectAdd('id');
		$language->selectAdd('displayName');
		$language->orderBy('displayName');
		$language->find();
		$languageList = [];
		while ($language->fetch()) {
			$languageList[$language->id] = $language->displayName;
		}
		return $languageList;
	}

	public static function getLanguageIdsByCode(): array {
		$language = new Language();
		$language->selectAdd('code');
		$language->selectAdd('id');
		$language->orderBy('displayName');
		$language->find();
		$languageList = [];
		while ($language->fetch()) {
			$languageList[$language->code] = $language->id;
		}
		return $languageList;
	}

	private static $_validLanguages = null;

	/**
	 * @return Language[]
	 */
	public static function getValidLanguages() : array {
		if (self::$_validLanguages == null) {
			$validLanguages = [];
			try {
				require_once ROOT_DIR . '/sys/Translation/Language.php';
				$validLanguage = new Language();
				$validLanguage->orderBy(["weight", "displayName"]);
				$validLanguage->find();
				$userIsTranslator = UserAccount::userHasPermission('Translate Aspen');
				while ($validLanguage->fetch()) {
					if (!$validLanguage->displayToTranslatorsOnly || $userIsTranslator) {
						$validLanguages[$validLanguage->code] = clone $validLanguage;
					}
				}
			} catch (Exception) {
				$defaultLanguage = new Language();
				$defaultLanguage->code = 'en';
				$defaultLanguage->displayName = 'English';
				$defaultLanguage->displayNameEnglish = 'English';
				$defaultLanguage->facetValue = 'English';
				$validLanguages['en'] = $defaultLanguage;
			}
			self::$_validLanguages = $validLanguages;
		}
		return  self::$_validLanguages;
	}

	public function getNumericColumnNames(): array {
		return [
			'id',
			'weight',
		];
	}

	static $rtl_languages = [
		'ar',
		'he',
        'ku',
	];

	public function isRTL() : bool {
		return in_array($this->code, Language::$rtl_languages);
	}
}