<?php /** @noinspection PhpMissingFieldTypeInspection */

require_once ROOT_DIR . '/sys/BaseLogEntry.php';

class HooplaExportLogEntry extends BaseLogEntry {
	public $__table = 'hoopla_export_log';   // table name
	public $id;
	public $notes;
	public $numProducts;
	public $numErrors;
	public $numAdded;
	public $numDeleted;
	public $numUpdated;
	public $numEntitlementsUpdated;
	public $numEntitlementsDeleted;
	public $numAvailabilityChanges;
	/** @noinspection PhpUnused */
	public $numInvalidRecords;
	// Legacy Hoopla v1 columns
	public $numSkipped;
}
