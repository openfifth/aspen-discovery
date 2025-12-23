<?php /** @noinspection PhpMissingFieldTypeInspection */


class ILSRecordUsage extends DataObject {
	public $__table = 'ils_record_usage';
	public $id;
	public $instance;
	public $indexingProfileId;
	public $recordId;
	public $year;
	public $month;
	public $day;
	public $timesUsed; //This is number of holds
	public $pdfDownloadCount;
	public $supplementalFileDownloadCount;
	public $pdfViewCount;

	public function getUniquenessFields(): array {
		return [
			'instance',
			'indexingProfileId',
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