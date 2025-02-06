<?php

require_once ROOT_DIR . '/sys/BmjBp/BmjBpSetting.php';
require_once ROOT_DIR . '/sys/Pager.php';
require_once ROOT_DIR . '/sys/SearchObject/BaseSearcher.php';

class SearchObject_BmjBpSearcher extends SearchObject_BaseSearcher{

	/** @var array */
	private $searchOptions;

	/** Values for the searchOptions array*/
	/** @var string */
	protected $language = 'en-gb'; // 'en' is not supported
	/** @var string */
	protected $type = 'MONOGRAPH';
	/** @var int */
	protected $page = 1; // or use page?
	/** @var int */
	protected $limit = 10; // or use limit?

	/** @var BmjBpSetting */
	private $settings;

	private $searchIndex = '';

	public function __construct() {
		$this->searchSource = 'bmjBp';
		$this->searchType = 'bmjBp';
		$this->resultsModule = 'BmjBp';
		$this->resultsAction = 'Results';
	}

	public function init($searchSource = null): bool {
		$this->initView();
		$this->initPage();
		$this->initSort();
		$this->initFilters();
		$this->initLimiters();

		if (!$this->initBasicSearch()) {
			$this->initAdvancedSearch();
		}

		return true;
	}

	public function getSettings(): BmjBpSetting {
		if (empty($this->settings)) {
			$this->setSettings();
		}
		return $this->settings;
	}

	public function setSettings(): void {
		global $library;
		if ($library->bmjBpSettingId == -1) {
			AspenError::raiseError(new AspenError('There are no BmjBp Settings set for this library system.'));
			return;
		}
		$settings = new BmjBpSetting();
		$settings->id = $library->bmjBpSettingId;
		if (!$settings->find(true)) {
			$settings = null;
		}
		$this->settings = $settings;
	}

	private function getSearchOptions (): array{
		if (empty($searchOptions)) {
			$this->setSearchOptions();
		}
		return $this->searchOptions;
	}

	private function setSearchOptions (): void {
		$this->searchOptions = [
			'searchTerm'=> $this->searchTerms[0]['lookfor'],
			'language' => $this->language,
			'type' => $this->type,
			'from' => $this->page - 1,
			'size' => $this->limit,
		];
	}

	function buildQueryString(): string {
		$query = "";
		$index = 0;
		foreach ($this->getSearchOptions() as $key => $value) {
			if ($index > 0) {
				$query .= '&';
			}
			$value = urlencode($value);
			$query .= "$key=$value";
			$index ++;
		}
		return $query;
	}
	
	public function getSearchIndexes(): array {
		return [
			'Keyword' => translate([
				'text' => "Keyword",
				'isPublicFacing' => true,
				'inAttribute' => true,
			])
		];
	}

	function getSearchName(): string {
		return $this->searchSource;
	}

	public function getEngineName(): string {
		return 'bmjBp';
	}

	public function processSearch($returnIndexErrors = false, $recommendations = false, $preventQueryModification = false) : AspenError|array|null {
		// TODO: determine which guard clauses are needed here
		$baseApiUrl = $this->getSettings()->bmjBpBaseApiUrl;
		$queryString = $this->buildQueryString();
	}

	public function __destruct() {
		if ($this->curl_connection) {
			curl_close($this->curl_connection);
		}
	}

	// are set as mandatory by the base searcher class but not used in this POC
	public function loadValidFields() {}
	public function loadDynamicFields() {}
	public function getIndexError() {}
	public function buildRSS($result = null) {}
	public function buildExcel($result = null) {}
	public function getResultRecordSet() {}
	public function getSearchesFile() {}
}
