<?php

require_once ROOT_DIR . '/sys/BmjBp/BmjBpSetting.php';
require_once ROOT_DIR . '/sys/Pager.php';
require_once ROOT_DIR . '/sys/SearchObject/BaseSearcher.php';

class SearchObject_BmjBpSearcher extends SearchObject_BaseSearcher{
	// TODO: use the CurlWrapper class instead of vanilla PHP CURL?
	// TODO: implement a JWT library

	static $instance;
	/** @var string */
	private $jwt;
	/** @var BmjBpSetting */
	private $settings;
	/** @var array */
	private $headers;
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

	private $curl_connection;

	protected $lastSearchResults;

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

	public function getCurlConnection(): object {
		if ($this->curl_connection == null) {
			$this->curl_connection = curl_init();
			curl_setopt($this->curl_connection, CURLOPT_CONNECTTIMEOUT, 15);
			curl_setopt($this->curl_connection, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
			curl_setopt($this->curl_connection, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($this->curl_connection, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($this->curl_connection, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($this->curl_connection, CURLOPT_TIMEOUT, 30);
			curl_setopt($this->curl_connection, CURLOPT_RETURNTRANSFER, TRUE);
		}
		return $this->curl_connection;
	}

	public function getJwt(): string {
		if (!$this->jwt) {
			$this->setJwt($this->getSettings());
		}
		return $this->jwt;
	}

	public function setJwt(): void {
		// TO BE REPLACED WITH THE USE OF A JWT LIBRARY
		$header = [
			"alg" => "HS256",
			"typ" => "JWT"
		];
		$header = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode($header)));
		$payload =  [
			"iss" => $this->getSettings()->bmjBpApiKey,
		];
		$payload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode($payload)));
		$signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(hash_hmac('sha256', "$header.$payload", $this->getSettings()->bmjBpApiSecret, true)));
		$this->jwt = "$header.$payload.$signature";
		return;
		// TO BE REPLACED WITH THE USE OF A JWT LIBRARY
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

	public function getHeaders(): array {
		if (empty($headers)) {
			$this->setHeaders($this->getJwt());
		}
		return $this->headers;
	}

	public function setHeaders(): void {
		$this->headers = array(
			'Accept' => 'application/json',
			'Authorization' => 'JWT Bearer ' . $this->getJwt(),
		);
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

	public function getResultRecordHTML(): array {
		global $interface;
		global $timer;
		$html = [];
		$timer->logTime("Starting to load record html");
		if (isset($this->lastSearchResults)) {
			for ($x = 0; $x < count($this->lastSearchResults); $x++) {
				$current = &$this->lastSearchResults[$x];
				$interface->assign('recordIndex', $x + 1);
				$interface->assign('resultIndex', $x + 1 + (($this->page - 1) * $this->limit));

				require_once ROOT_DIR . '/RecordDrivers/BmjBpRecordDriver.php';
				$record = new BmjBpRecordDriver($current);
				$interface->assign('recordDriver', $record);
				$html[] = $interface->fetch($record->getSearchResult());
			}
		};
		return $html;
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

	protected function sendHttpRequest($baseUrl, $queryString, $headers): array {
		foreach ($headers as $key =>$value) {
			$modified_headers[] = $key.": ".$value;
		}
		$curlConnection = $this->getCurlConnection();
		$curlOptions = array(
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_URL => "{$baseUrl}?{$queryString}",
			CURLOPT_HTTPHEADER => $modified_headers
		);

		curl_setopt_array($curlConnection, $curlOptions);
		$result = curl_exec($curlConnection);
		$responseCode = curl_getinfo($curlConnection, CURLINFO_RESPONSE_CODE);
		if ($result === false || $responseCode != '200') {
			AspenError::raiseError("Could not get results from BMJ Best practice: error in HTTP Request.");
		}
		curl_close($curlConnection);
		return json_decode($result, true);
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
		$headers = $this->getHeaders();
		$responseData= $this->sendHttpRequest($baseApiUrl, $queryString, $headers)['monograph'];
		$this->resultsTotal = $responseData['total'];
		$this->lastSearchResults = $responseData['results'];
		return $responseData;
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
