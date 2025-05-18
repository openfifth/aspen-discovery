<?php

class ManuallyGroupedWorkRecord extends DataObject {
	public $__table = 'manually_grouped_work_records';
	public $id;
	public $manually_grouped_work_id;
	public $type;
	public $identifier;
	public $user_provided_identifier;
	public $identifier_type;
	public $date_added;

	/**
	 * Get the structure of the object for use in the edit form
	 *
	 * @param string $context
	 * @return array
	 */
	static function getObjectStructure(string $context = ''): array {
		global $indexingProfiles;
		global $sideLoadSettings;

		// Get available record sources
		$availableSources = [];
		foreach ($indexingProfiles as $profile) {
			$displayName = $profile->name;
			$associatedAccountProfile = $profile->getAccountProfile();
			if ($associatedAccountProfile && !empty($associatedAccountProfile->ils) && $associatedAccountProfile->ils !== 'na') {
				$displayName .= ' (' . ucfirst($associatedAccountProfile->ils) . ')';
			}
			$availableSources[$profile->name] = $displayName;
		}
		foreach ($sideLoadSettings as $profile) {
			$availableSources[$profile->name] = $profile->name;
		}
		$availableSources['axis360'] = 'Boundless';
		$availableSources['cloud_library'] = 'cloudLibrary';
		$availableSources['hoopla'] = 'Hoopla';
		$availableSources['overdrive'] = 'Overdrive';
		$availableSources['palace_project'] = 'Palace Project';

		return [
			'id' => [
				'property' => 'id',
				'type' => 'label',
				'label' => 'Id',
				'description' => 'The unique id',
				'storeDb' => true,
			],
			'manually_grouped_work_id' => [
				'property' => 'manually_grouped_work_id',
				'type' => 'hidden',
				'label' => 'Manually Grouped Work Id',
				'description' => 'The id of the manually grouped work this record belongs to',
				'storeDb' => true,
			],
			'type' => [
				'property' => 'type',
				'type' => 'enum',
				'values' => $availableSources,
				'label' => 'Source',
				'description' => 'The source of the record',
				'storeDb' => true,
				'required' => true,
			],
			'identifier' => [
				'property' => 'identifier',
				'type' => 'text',
				'label' => 'Resolved Record ID',
				'description' => 'The actual system identifier for the record, resolved from user input.',
				'storeDb' => true,
				'readOnly' => true,
				'readOnlyWhenNew' => true,
				'placeholder' => 'Record identifier will populate here...',
			],
			'user_provided_identifier' => [
				'property' => 'user_provided_identifier',
				'type' => 'text',
				'size' => 36,
				'maxLength' => 255,
				'label' => 'Identifier Value',
				'description' => 'Enter the Record ID, ISBN, or Barcode here.',
				'storeDb' => true,
				'required' => true,
			],
			'identifier_type' => [
				'property' => 'identifier_type',
				'type' => 'enum',
				'values' => [
					'record_id' => 'Record ID',
					'isbn' => 'ISBN',
					'barcode' => 'Barcode',
				],
				'label' => 'Identifier Type',
				'description' => 'The type of identifier being used',
				'default' => 'record_id',
				'storeDb' => true,
				'required' => true,
			],
			'date_added' => [
				'property' => 'date_added',
				'type' => 'timestamp',
				'label' => 'Date Added',
				'description' => 'The date this record was added to the group',
				'storeDb' => true,
				'hideInLists' => true,
			],
		];
	}

	/**
	 * Find a record's primary identifier by barcode
	 *
	 * @param string $barcode The item barcode to look up
	 * @param string $source The source system (ILS profile name)
	 * @return array|null Array with 'identifier' and 'type' if found, null if not found
	 */
	private function getPrimaryIdentifierByBarcode(string $barcode, string $source): ?array {
		// Use the grouped work searcher to look up the record by barcode, so fields are cleaned for scoping
		require_once ROOT_DIR . '/sys/SearchObject/SearchObjectFactory.php';
		// Initialize the search object (chooses version 1 or 2 based on system variables)
		/** @var SearchObject_GroupedWorkSearcher2 $searchObject */
		$searchObject = SearchObjectFactory::initSearchObject();
		// Set the search source so scoping is applied correctly
		$searchObject->init($source, null);
		// Perform the lookup
		$recordData = $searchObject->getRecordByBarcode($barcode);

		if ($recordData && isset($recordData['id'])) {
			require_once ROOT_DIR . '/sys/Grouping/GroupedWork.php';
			require_once ROOT_DIR . '/sys/Grouping/GroupedWorkPrimaryIdentifier.php';
			$permanentId = $recordData['id'];
			$groupedWork = new GroupedWork();
			$groupedWork->permanent_id = $permanentId;
			if ($groupedWork->find(true)) {
				$primaryIdentifier = new GroupedWorkPrimaryIdentifier();
				$primaryIdentifier->grouped_work_id = $groupedWork->id;
				$primaryIdentifier->type = $source;
				if ($primaryIdentifier->find(true)) {
					return [
						'identifier' => $primaryIdentifier->identifier,
						'type' => $source
					];
				}
			}
		}

		return null;
	}

	/**
	 * Find a record's primary identifier by ISBN
	 *
	 * @param string $isbn The ISBN to look up (ISBN-10 or ISBN-13)
	 * @param string $source The source system (ILS profile name)
	 * @return array|null Array with 'identifier' and 'type' if found, null if not found
	 */
	private function getPrimaryIdentifierByISBN(string $isbn, string $source): ?array {
		// Normalize the ISBN
		require_once ROOT_DIR . '/sys/ISBN.php';
		$isbnObj = new ISBN($isbn);

		// Get both ISBN formats for more complete searching
		$searchIsbns = [];
		if ($isbn13 = $isbnObj->get13()) {
			$searchIsbns[] = $isbn13;
		}
		if ($isbn10 = $isbnObj->get10()) {
			$searchIsbns[] = $isbn10;
		}

		if (empty($searchIsbns)) {
			// Invalid ISBN provided
			global $logger;
			$logger->log("Invalid ISBN provided: $isbn", Logger::LOG_ERROR);
			return null;
		}

		// Use the grouped work searcher to look up the record by ISBN
		require_once ROOT_DIR . '/sys/SearchObject/SearchObjectFactory.php';

		// Initialize the search object (chooses version 1 or 2)
		/** @var SearchObject_GroupedWorkSearcher2 $searchObject */
		$searchObject = SearchObjectFactory::initSearchObject();
		$searchObject->init($source, null);
		// Perform the lookup
		$recordData = $searchObject->getRecordByIsbn($searchIsbns);

		if ($recordData && isset($recordData['id'])) {
			require_once ROOT_DIR . '/sys/Grouping/GroupedWork.php';
			require_once ROOT_DIR . '/sys/Grouping/GroupedWorkPrimaryIdentifier.php';
			$permanentId = $recordData['id'];
			$groupedWork = new GroupedWork();
			$groupedWork->permanent_id = $permanentId;
			if ($groupedWork->find(true)) {
				$primaryIdentifier = new GroupedWorkPrimaryIdentifier();
				$primaryIdentifier->grouped_work_id = $groupedWork->id;
				$primaryIdentifier->type = $source;
				if ($primaryIdentifier->find(true)) {
					return [
						'identifier' => $primaryIdentifier->identifier,
						'type' => $source
					];
				}
			}
		}
		else {
			global $logger;
			$logger->log("No record found for ISBN: $isbn", Logger::LOG_ERROR);
		}

		return null;
	}

	/**
	 * Resolve this record's identifier if it's a barcode or ISBN to the primary record ID.
	 * Updates $this->identifier to the primary record identifier.
	 * @return bool True if the identifier is resolved or no resolution needed; false to skip this record.
	 */
	public function resolvePrimaryIdentifier(): bool {
		// Only resolve for barcode or ISBN types
		if ($this->identifier_type === 'barcode' || $this->identifier_type === 'isbn') {
			// Determine which lookup to use
			if ($this->identifier_type === 'barcode') {
				$result = $this->getPrimaryIdentifierByBarcode($this->user_provided_identifier, $this->type);
			} else {
				$result = $this->getPrimaryIdentifierByISBN($this->user_provided_identifier, $this->type);
			}
			if ($result !== null && isset($result['identifier'])) {
				// Update to the resolved primary identifier
				$this->identifier = $result['identifier'];
				return true;
			}
			global $logger;
			$logger->log("Could not find record for {$this->identifier_type} '{$this->user_provided_identifier}' in source '{$this->type}'", Logger::LOG_ERROR);
			return false;
		}
		// No resolution needed for record_id
		return true;
	}
}