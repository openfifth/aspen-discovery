<?php

class ManualGroupedWork extends DataObject {
	public $__table = 'manually_grouped_works';
	public $id;
	public $title;
	public $description;
	public $created_by;
	public $date_created;
	public $last_updated;

	private $_records;
	// Keep track of grouped work permanent IDs that have been scheduled for reindex this request.
	private static $reindexedPermanentIds = [];

	public function insert($context = ''): bool|int {
		if (empty($this->date_created)) {
			$this->date_created = time();
		}
		$this->last_updated = time();
		$ret = parent::insert();
		if ($ret) {
			// If records were populated via the form, move them into the internal records array.
			if (empty($this->_records) && isset($this->_data['records'])) {
				$this->_records = $this->_data['records'];
			}
			if (!empty($this->_records)) {
				$this->saveRecords();
			}
		}
		return $ret;
	}

	public function update($context = ''): bool|int {
		$this->last_updated = time();
		$ret = parent::update();
		if ($ret) {
			// If the records property has been set (via DataObjectUtil), save changes (including deletions)
			if (isset($this->_records) && is_array($this->_records)) {
				$this->saveRecords();
			}
		}
		return $ret;
	}

	/**
	 * Override the delete functionality to delete related records
	 */
	public function delete($useWhere = false): int {
		$ret = parent::delete($useWhere);
		if ($ret) {
			$this->clearRecords();
		}
		return $ret;
	}

	/**
	 * Get all records associated with this manually grouped work
	 *
	 * @return array|null
	 */
	public function getRecords(): ?array {
		if (!isset($this->_records) && $this->id) {
			$this->_records = [];
			$manuallyGroupedWorkRecord = new ManuallyGroupedWorkRecord();
			$manuallyGroupedWorkRecord->manually_grouped_work_id = $this->id;
			$manuallyGroupedWorkRecord->orderBy('id');
			$manuallyGroupedWorkRecord->find();
			while ($manuallyGroupedWorkRecord->fetch()) {
				$this->_records[$manuallyGroupedWorkRecord->id] = clone($manuallyGroupedWorkRecord);
			}
		}
		return $this->_records;
	}

	/**
	 * Add a record to this manually grouped work
	 *
	 * @param string $type
	 * @param string $identifier
	 * @param string $identifierType
	 * @return bool
	 */
	public function addRecord(string $type, string $identifier, string $identifierType = 'record_id'): bool {
		if (!isset($this->_records)) {
			$this->getRecords();
		}

		$record = new ManuallyGroupedWorkRecord();
		$record->manually_grouped_work_id = $this->id;
		$record->type = $type;
		$record->identifier_type = $identifierType;
		$record->user_provided_identifier = $identifier;
		if (!$record->resolvePrimaryIdentifier()) {
			// Resolution failed or no such record
			$this->displayMessageToUser("Unable to resolve identifier '{$record->user_provided_identifier}' for type '{$record->type}'.", true);
			return false;
		}

		if ($record->identifier_type === 'record_id') {
			$record->identifier = $record->user_provided_identifier;
			// Validate the record ID exists in the system.
			require_once ROOT_DIR . '/sys/Grouping/GroupedWorkPrimaryIdentifier.php';
			$primaryCheck = new GroupedWorkPrimaryIdentifier();
			$primaryCheck->type = $record->type;
			$primaryCheck->identifier = $record->identifier;
			if (!$primaryCheck->find(true)) {
				$this->displayMessageToUser("Record ID '{$record->identifier}' not found for source '{$record->type}'.", true);
				return false;
			}
		}

		// Check if the record already exists
		foreach ($this->_records as $existingRecord) {
			if ($existingRecord->type == $record->type && $existingRecord->identifier == $record->identifier) {
				$this->displayMessageToUser("Record is already in this manual group.", false);
				return false;
			}
		}

		if ($record->insert()) {
			$this->_records[$record->id] = $record;
			$this->reindexRecords();
			return true;
		}
		else {
			$this->displayMessageToUser("Failed to add record '{$record->identifier}' to manual group.", true);
			return false;
		}
	}

	/**
	 * Remove a record from this manually grouped work
	 *
	 * @param int $recordId
	 * @return bool
	 */
	public function removeRecord(int $recordId): bool {
		if (!isset($this->_records)) {
			$this->getRecords();
		}

		if (isset($this->_records[$recordId])) {
			$record = $this->_records[$recordId];
			if ($record->delete()) {
				unset($this->_records[$recordId]);
				$this->reindexRecords();
				return true;
			} else {
				$this->displayMessageToUser("Failed to remove record with ID {$recordId} from this manual group.", true);
				return false;
			}
		} else {
			$this->displayMessageToUser("Record with ID {$recordId} not found in this manual group.", true);
			return false;
		}
	}

	/**
	 * Save all records associated with this manually grouped work
	 */
	private function saveRecords(): void {
		if (isset($this->_records) && is_array($this->_records)) {
			// Validate all records before clearing existing ones
			$recordsToInsert = [];
			foreach ($this->_records as $record) {
				// Skip records flagged for deletion by DataObjectUtil.
				if (!empty($record->_deleteOnSave) && !empty($record->id)) {
					$record->delete();
					continue;
				}
				if ($record->identifier_type === 'record_id') {
					// Validate the record ID exists in the system.
					require_once ROOT_DIR . '/sys/Grouping/GroupedWorkPrimaryIdentifier.php';
					$primaryCheck = new GroupedWorkPrimaryIdentifier();
					$primaryCheck->type = $record->type;
					$primaryCheck->identifier = $record->user_provided_identifier;
					if (!$primaryCheck->find(true)) {
						$this->displayMessageToUser("Record ID '{$record->user_provided_identifier}' not found for source '{$record->type}'.", true);
						continue;
					}
					$record->identifier = $record->user_provided_identifier;
				} else {
					if (!$record->resolvePrimaryIdentifier()) {
						$this->displayMessageToUser("Unable to resolve identifier '{$record->user_provided_identifier}'.", true);
						continue;
					}
				}
				$recordsToInsert[] = $record;
			}

			// Update records one-by-one without clearing all to avoid side effects
			foreach ($recordsToInsert as $record) {
				// Prepare for insert; unique constraint will skip duplicates
				$record->manually_grouped_work_id = $this->id;
				if (empty($record->date_added)) {
					$record->date_added = time();
				}
				// Skip if this record is already in any manual group
				$existing = new ManuallyGroupedWorkRecord();
				$existing->type = $record->type;
				$existing->identifier = $record->identifier;
				if ($existing->find(true)) {
					// If in a different group, inform the user
					if ($existing->manually_grouped_work_id != $this->id) {
						$this->displayMessageToUser("Record '{$record->identifier}' is already in another manual group.", false);
					}
					continue;
				}

				try {
					$record->insert();
				} catch (Exception $e) {
					global $logger;
					$logger->log("Error inserting record to group " . $this->id . ": " . $e->getMessage() . " Data: " . json_encode($record->toArray()), Logger::LOG_ERROR);
					$this->displayMessageToUser("Error inserting record to group {$this->id}: {$e->getMessage()}.", true);
					continue;
				}
			}

			$this->reindexRecords();
		}
	}

	/**
	 * Clear all records associated with this manually grouped work
	 */
	private function clearRecords(): void {
		if ($this->id) {
			$record = new ManuallyGroupedWorkRecord();
			$record->manually_grouped_work_id = $this->id;
			$record->delete(true);
		}
	}

	/**
	 * Force reindexing of all records in this manually grouped work
	 * This method will add each record's identifier and type to the
	 * record_identifiers_to_reload table, so they are picked up by the indexer.
	 */
	public function reindexRecords(): int {
		if (!isset($this->_records)) {
			$this->getRecords();
		}
		if (empty($this->_records)) {
			return 0;
		}

		$reindexedCount = 0;
		require_once ROOT_DIR . '/sys/Indexing/RecordIdentifiersToReload.php';
		foreach ($this->_records as $recordLink) {
			$identifier = $recordLink->identifier;
			$source = $recordLink->type;

			if (!empty($identifier) && !empty($source)) {
				$reindexKey = $source . '|' . $identifier;
				if (isset(self::$reindexedPermanentIds[$reindexKey])) {
					continue;
				}
				self::$reindexedPermanentIds[$reindexKey] = true;

				try {
					$recordToReload = new RecordIdentifiersToReload();
					$recordToReload->type = $source;
					$recordToReload->identifier = $identifier;
					$recordToReload->insert();
					$reindexedCount++;
				} catch (Exception $e) {
					global $logger;
					$logger->log("Error adding record to reload queue: $identifier (source: $source) for manual group ID $this->id. Error: {$e->getMessage()}", Logger::LOG_ERROR);
				}
			} else {
				global $logger;
				$logger->log("Skipping reindex for a record in manual group ID $this->id due to missing identifier or source. Record link ID: $recordLink->id.", Logger::LOG_ERROR);
			}
		}
		return $reindexedCount;
	}

	/**
	 * Get the structure of the object for use in the edit form.
	 *
	 * @param string $context
	 * @return array
	 */
	static function getObjectStructure(string $context = ''): array {
		return [
			'id' => [
				'property' => 'id',
				'type' => 'label',
				'label' => 'Id',
				'description' => 'The unique id of the manually created group',
				'storeDb' => true,
			],
			'title' => [
				'property' => 'title',
				'type' => 'text',
				'size' => 250,
				'maxLength' => 500,
				'label' => 'Title',
				'description' => 'The title of this manually created group',
				'storeDb' => true,
				'required' => true,
			],
			'description' => [
				'property' => 'description',
				'type' => 'textarea',
				'label' => 'Description',
				'description' => 'A description of why this manual group exists',
				'storeDb' => true,
				'required' => false,
			],
			'created_by' => [
				'property' => 'created_by',
				'type' => 'hidden',
				'label' => 'Created By',
				'description' => 'The user who created this manual group',
				'storeDb' => true,
				'default' => UserAccount::getActiveUserId(),
			],
			'date_created' => [
				'property' => 'date_created',
				'type' => 'timestamp',
				'label' => 'Date Created',
				'description' => 'The date this manual group was created',
				'storeDb' => true,
				'hideInLists' => true,
				'readOnly' => true,
			],
			'last_updated' => [
				'property' => 'last_updated',
				'type' => 'timestamp',
				'label' => 'Last Updated',
				'description' => 'The date this manual group was last updated',
				'storeDb' => true,
				'hideInLists' => true,
				'readOnly' => true,
			],
			'records' => [
				'property' => 'records',
				'type' => 'oneToMany',
				'label' => 'Records',
				'description' => 'The records to include in this manual group',
				'infoBullets' => [
					'In the table below, select the source of the record, input its identifier value, and select the identifier\'s type.',
					'The "Resolved Record ID" field cannot be edited and will be automatically populated with the record\'s primary identifier if a record is found.',
				],
				'keyThis' => 'id',
				'keyOther' => 'manually_grouped_work_id',
				'subObjectType' => 'ManuallyGroupedWorkRecord',
				'structure' => ManuallyGroupedWorkRecord::getObjectStructure(),
				'storeDb' => true,
				'canAddNew' => true,
				'canDelete' => true,
				'sortable' => false,
			],
		];
	}

	/**
	 * Expose records to the template by loading from DB when getting property.
	 */
	public function __get($name) {
		if ($name === 'records') {
			return $this->getRecords();
		}
		return parent::__get($name);
	}

	/**
	 * Allow DataObjectUtil to assign posted records before saving.
	 */
	public function __set($name, $value) {
		if ($name === 'records') {
			$this->_records = $value;
		} else {
			parent::__set($name, $value);
		}
	}

	private function displayMessageToUser(string $message, bool $isError): void {
		$user = UserAccount::getActiveUserObj();
		if ($user) {
			$user->updateMessage .= !empty($user->updateMessage) ? "<br>{$message}" : $message;
			$user->updateMessageIsError = $isError;
			$user->update();
		}
	}
}