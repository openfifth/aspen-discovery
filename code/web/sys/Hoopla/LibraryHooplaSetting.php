<?php /** @noinspection PhpMissingFieldTypeInspection */

/**
 * LibraryHooplaSetting - Library-specific Hoopla configuration
 *
 * HOOPLA VERSION 2 ONLY
 *
*/

class LibraryHooplaSetting extends DataObject
{
    public $__table = 'library_hoopla_settings';
    public $id;
    public $weight;
    public $settingId;
    public $libraryId;
    public $hooplaLibraryID;
    public $circulationEnabled;
    public $hooplaInstantEnabled;
    public $hooplaFlexEnabled;
    public $fullUpdateForLibrary;
    public $cleanUpInstant;
    public $cleanUpFlex;
    public function getNumericColumnNames(): array
    {
        return [
            'id',
            'libraryId',
        ];
    }

    static $_objectStructure = [];
    static function getObjectStructure(string $context = ''): array
    {
        if (isset(self::$_objectStructure[$context]) && self::$_objectStructure[$context] !== null) {
            return self::$_objectStructure[$context];
        }

        $hooplaSettings = [];
        require_once ROOT_DIR . '/sys/Hoopla/HooplaSetting.php';
        $hooplaSetting = new HooplaSetting();
        $hooplaSetting->find();
        while ($hooplaSetting->fetch()) {
            $hooplaSettings[$hooplaSetting->id] = (string) $hooplaSetting;
        }

        $libraryList = Library::getLibraryList(!UserAccount::userHasPermission('Administer All Libraries'));

        $structure = [
            'id' => [
                'property' => 'id',
                'type' => 'label',
                'label' => 'Id',
                'description' => 'The unique id',
            ],
            'libraryId' => [
                'property' => 'libraryId',
                'type' => 'enum',
                'values' => $libraryList,
                'label' => 'Library',
                'description' => 'The id of a library',
            ],
            'weight' => [
                'property' => 'weight',
                'type' => 'integer',
                'label' => 'Weight',
                'description' => 'The sort order',
                'default' => 0,
            ],
            'settingId' => [
                'property' => 'settingId',
                'type' => 'enum',
                'values' => $hooplaSettings,
                'label' => 'Hoopla Settings',
                'description' => 'The Hoopla settings to use',
                'default' => -1,
                'forcesReindex' => true,
            ],
            'hooplaLibraryID' => [
                'property' => 'hooplaLibraryID',
                'type' => 'text',
                'label' => 'Hoopla Library ID',
                'description' => 'The Hoopla ID of a library',
                'size' => '20',
            ],
            'hooplaInstantEnabled' => [
                'property' => 'hooplaInstantEnabled',
                'type' => 'checkbox',
                'label' => 'Hoopla Instant Enabled',
                'description' => 'Whether or not Hoopla Instant is enabled',
                'hideInLists' => false,
                'default' => false,
                'forcesReindex' => false,
            ],
            'hooplaFlexEnabled' => [
                'property' => 'hooplaFlexEnabled',
                'type' => 'checkbox',
                'label' => 'Hoopla Flex Enabled',
                'description' => 'Whether or not Hoopla Flex is enabled',
                'hideInLists' => false,
                'default' => false,
                'forcesReindex' => false,
            ],
            'circulationEnabled' => [
                'property' => 'circulationEnabled',
                'type' => 'checkbox',
                'label' => 'Circulation Enabled',
                'description' => 'Whether or not circulation is enabled within Aspen',
                'hideInLists' => false,
                'default' => true,
                'forcesReindex' => false,
            ],
            'fullUpdateForLibrary' => [
                'property' => 'fullUpdateForLibrary',
                'type' => 'checkbox',
                'label' => 'Run Full Update for Library',
                'description' => 'Whether or not run a full update for this library',
                'hideInLists' => false,
                'default' => false,
                'forcesReindex' => false,
            ],
        ];

        self::$_objectStructure[$context] = $structure;
        return self::$_objectStructure[$context];
    }

    public function getEditLink(string $context): string
    {
        if ($context == 'libraries') {
            return '/Admin/Libraries?objectAction=edit&id=' . $this->libraryId . '#propertyRowhooplaScopes';
        } else {
            return '/Hoopla/Settings?objectAction=edit&id=' . $this->settingId;
        }
    }

    private $_hooplaSettings = null;

    public function getHooplaSettings(): ?HooplaSetting
    {
        if ($this->_hooplaSettings == null) {
            require_once ROOT_DIR . '/sys/Hoopla/HooplaSetting.php';
            $this->_hooplaSettings = new HooplaSetting();
            $this->_hooplaSettings->id = $this->settingId;
            if (!$this->_hooplaSettings->find(true)) {
                $this->_hooplaSettings = null;
            }
        }
        return $this->_hooplaSettings;
    }

    public function update(string $context = ''): int|bool
    {
        $existingSetting = new LibraryHooplaSetting();
        $existingSetting->id = $this->id;
        if ($existingSetting->find(true)) {
            if ($existingSetting->hooplaInstantEnabled && !$this->hooplaInstantEnabled) {
                $this->__set('cleanUpInstant', 1);
            } elseif (!$existingSetting->hooplaInstantEnabled && $this->hooplaInstantEnabled) {
                $this->__set('cleanUpInstant', 0);
            }
            if ($existingSetting->hooplaFlexEnabled && !$this->hooplaFlexEnabled) {
                $this->__set('cleanUpFlex', 1);
            } elseif (!$existingSetting->hooplaFlexEnabled && $this->hooplaFlexEnabled) {
                $this->__set('cleanUpFlex', 0);
            }
        }
        return parent::update($context);
    }

}
