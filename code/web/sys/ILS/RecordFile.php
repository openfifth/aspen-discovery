<?php /** @noinspection PhpMissingFieldTypeInspection */


class RecordFile extends DataObject {
	public $__table = 'record_files';
	public $id;
	public $type;
	public $identifier;
	public $fileId;

	private static $preloadedFiles = [];

	/**
	 * Preloads files for a type and identifier using minimal database queries
	 *
	 * @param string $type
	 * @param array $identifiers
	 * @return void
	 */
	static function preloadFiles(string $type, array $identifiers) : void {
		if (!isset(self::$preloadedFiles[$type])) {
			self::$preloadedFiles[$type] = [];
		}
		foreach ($identifiers as $identifier) {
			if (!array_key_exists($identifier, self::$preloadedFiles[$type])) {
				self::$preloadedFiles[$type][$identifier] = [];
			}
		}
		$recordFiles = new RecordFile();
		$recordFiles->type = $type;
		$recordFiles->whereAddIn('identifier', $identifiers, true);
		$allRelatedFiles = $recordFiles->fetchAll();
		foreach ($allRelatedFiles as $relatedFile) {
			self::$preloadedFiles[$type][$relatedFile->identifier][] = $relatedFile;
		}
	}

	/**
	 * @param string $type
	 * @param string $identifier
	 * @return RecordFile[]
	 */
	static function getFilesForRecord(string $type, string $identifier) : array {
		if (isset(self::$preloadedFiles[$type]) && array_key_exists($identifier, self::$preloadedFiles[$type])) {
			return self::$preloadedFiles[$type][$identifier];
		}else{
			$recordFiles = new RecordFile();
			$recordFiles->type = $type;
			$recordFiles->identifier = $identifier;
			return $recordFiles->fetchAll();
		}
	}
}