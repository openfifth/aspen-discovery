<?php /** @noinspection PhpMissingFieldTypeInspection */


class SideLoadedRecordUsage extends DataObject {
	public $__table = 'sideload_record_usage';
	public $id;
	public $instance;
	public $sideloadId;
	public $recordId;
	public $year;
	public $month;
	public $day;
	public $timesUsed;

	public function getUniquenessFields(): array {
		return [
			'instance',
			'sideloadId',
			'recordId',
			'year',
			'month',
			'day',
		];
	}

	public function okToExport(array $selectedFilters): bool {
		$okToExport = parent::okToExport($selectedFilters);
		if (in_array($this->instance, $selectedFilters['instances'])) {
			$okToExport = true;
		}
		return $okToExport;
	}
}