<?php

require_once ROOT_DIR . '/sys/Pager.php';
require_once ROOT_DIR . '/sys/Gale/GaleSetting.php';
require_once ROOT_DIR . '/sys/SearchObject/BaseSearcher.php';

class SearchObject_GaleSearcher extends SearchObject_BaseSearcher {
	static $instance;

	protected $limit = 20;
	protected $sortOptions = array();
	protected $defaultSort = 'relevance';

	/** @var GaleSetting */
	private string $galeBaseUrl = 'https://sru.galegroup.com/';
	/** @var SimpleXMLElement[] */
	private array $lastSearchResults = [];
	private ?SimpleXMLElement $lastResponse = null;
    private $curl_connection;

    /**Track query time info */
	protected $queryStartTime = null;
	protected $queryEndTime = null;
	protected $queryTime = null;


	private array $searchIndexFieldMap = [
		'Keyword' => null,
		'Title' => 'dc.title',
		'Author' => 'dc.creator',
		'Subject' => 'dc.subject',
		'Publication' => 'dc.relation',
		'ISSN' => 'bath.issn',
	];
	private array $facetFieldMap = [
		'Format' => 'dc.type',
		'Subject' => 'dc.subject',
		'Publication' => 'dc.relation',
	];

	public function __construct() {
		parent::__construct();
		$this->searchSource = 'gale';
		$this->resultsModule = 'Gale';
		$this->resultsAction = 'Results';
	}

	
	/**
	 * Initialise the object from the global
	 *  search parameters in $_REQUEST.
	 */
	public function init($searchSource = null) : bool {
		//********************
		// Check if we have a saved search to restore -- if restored successfully,
		// our work here is done; if there is an error, we should report failure;
		// if restoreSavedSearch returns false, we should proceed as normal.
		$restored = $this->restoreSavedSearch();
		if ($restored === true) {
			return true;
		} elseif ($restored instanceof AspenError) {
			return false;
		}

		//********************
		// Initialize standard search parameters
		$this->initView();
		$this->initPage();
		$this->initFilters();
		$this->initLimiters();
		//Sorting needs to be initialized after filters since they depend on the selected database
		$this->initSort();

		//********************
		// Basic Search logic
		if (!$this->initBasicSearch()) {
			$this->initAdvancedSearch();
		}

		// If a query override has been specified, log it here
		if (isset($_REQUEST['q'])) {
			$this->query = $_REQUEST['q'];
		}

		return true;
	}

    /**
	 * Create an instance of the Gale Searcher
	 * @return SearchObject_GaleSearcher
	 */
    public static function getInstance(): SearchObject_GaleSearcher {
		if (SearchObject_GaleSearcher::$instance == null) {
			SearchObject_GaleSearcher::$instance = new SearchObject_GaleSearcher();
		}
		return SearchObject_GaleSearcher::$instance;
	}

    // Getsettings is where i left off for tomorrow

	public function getEngineName() {
		return 'Gale';
	}

    public function getCurlConnection() {
		if ($this->curl_connection == null) {
			$this->curl_connection = curl_init();
			curl_setopt($this->curl_connection, CURLOPT_CONNECTTIMEOUT, 15);
			curl_setopt($this->curl_connection, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
			curl_setopt($this->curl_connection, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($this->curl_connection, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($this->curl_connection, CURLOPT_TIMEOUT, 30);
			curl_setopt($this->curl_connection, CURLOPT_RETURNTRANSFER, TRUE);
		}
		return $this->curl_connection;
	}

	public function processSearch(bool $returnIndexErrors = false, bool $recommendations = false, bool $preventQueryModification = false): AspenError|SimpleXMLElement|array|null {
        global $logger;
        $galeSettings = $this->getSettings();
		if ($galeSettings == null || empty($galeSettings->locationId)) {
			return new AspenError('Gale searching is not configured for this library.');
		}

		$productCode = $this->getProductCode();
		$searchTerm = $this->searchTerms[0]['lookfor'] ?? '';
		// TODO not allow empty search term
		$searchIndex = $this->searchTerms[0]['index'] ?? '';
		$mappedIndex = $this->searchIndexFieldMap[$searchIndex] ?? '';
		if ($mappedIndex === '') {
			$searchQuery = $searchTerm;
		} else {
			$searchQuery = $mappedIndex . '=' . $searchTerm;
		}

		$this->lastSearchResults = [];
		$this->lastResponse = null;

		$this->startQueryTimer();

		$startRecord = (($this->page - 1) * $this->limit) + 1;
		$params = [
			'startRecord' => $startRecord,
			'maximumRecords' => $this->limit,
			'operation' => 'searchRetrieve',
			'version' => '1.1',
			'query' => $searchQuery,
			'x-username' => $galeSettings->locationId,
		];
		$queryParams = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
		$requestUrl = rtrim($this->galeBaseUrl, '/') . '/' . rawurlencode($productCode) . '?' . $queryParams;
		$logger->log("Gale request URL: $requestUrl", Logger::LOG_ERROR);

		$curl = $this->getCurlConnection();
		curl_setopt($curl, CURLOPT_URL, $requestUrl);
		$response = curl_exec($curl);
		if ($response === false || strlen($response) === 0) {
			$this->stopQueryTimer();
			return new AspenError('Error retrieving data from Gale.');
		}

		$xml = simplexml_load_string($response);
		if ($xml === false) {
			$this->stopQueryTimer();
			return new AspenError('Error processing search in Gale.');
		}
		$xml->registerXPathNamespace('zs', 'http://www.loc.gov/zing/srw/');
		$xml->registerXPathNamespace('dc', 'http://purl.org/dc/elements/1.1/');

		if (isset($xml->diagnostics->diagnostic)) {
			$message = (string)$xml->diagnostics->diagnostic->message;
			$this->stopQueryTimer();
			return new AspenError("Error processing search in Gale: $message");
		}

		$this->lastResponse = $xml;
		$numberNodes = $xml->xpath('//zs:numberOfRecords');
		$this->resultsTotal = $numberNodes ? (int)$numberNodes[0] : 0;

		$this->lastSearchResults = $xml->xpath('//zs:records/zs:record/zs:recordData/dc:dc') ?: [];

		$this->stopQueryTimer();
		return $this->lastSearchResults;
	}

	public function getResultSummary(): array {
		$summary = [];
		$summary['page'] = $this->page;
		$summary['perPage'] = $this->limit;
		$summary['resultTotal'] = $this->resultsTotal;
		$summary['startRecord'] = (($this->page - 1) * $this->limit) + 1;
		if ($this->resultsTotal < $this->limit) {
			$summary['endRecord'] = $this->resultsTotal;
		} elseif (($this->page * $this->limit) > $this->resultsTotal) {
			$summary['endRecord'] = $this->resultsTotal;
		} else {
			$summary['endRecord'] = $this->page * $this->limit;
		}
        global $logger;
        $logger->log("Gale result summary: " . print_r($summary, true), Logger::LOG_ERROR);
		return $summary;
	}

	public function getResultRecordHTML(): array {
		global $interface;
		$html = [];

		if (!empty($this->lastSearchResults)) {
			require_once ROOT_DIR . '/RecordDrivers/GaleRecordDriver.php';
			foreach ($this->lastSearchResults as $index => $recordNode) {
				$interface->assign('recordIndex', $index + 1);
				$interface->assign('resultIndex', $index + 1 + (($this->page - 1) * $this->limit));

				$record = new GaleRecordDriver($recordNode);
				if ($record->isValid()) {
					global $memCache;
					$id = $record->getUniqueID();
					if (!empty($id)) {
						$memCache->set('gale_min_' . $id, [
							'title'  => $record->getTitle(),
							'author' => $record->getAuthor(),
							'format' => $record->getFormats(),
						], 9000);
					}
					$interface->assign('recordDriver', $record);
					$html[] = $interface->fetch($record->getSearchResult());
				}
			}
		}

		$this->addToHistory();

		return $html;
	}

	public function getCombinedResultHTML(): array {
		global $interface;
		$html = [];

		if (!empty($this->lastSearchResults)) {
			require_once ROOT_DIR . '/RecordDrivers/GaleRecordDriver.php';
			foreach ($this->lastSearchResults as $index => $recordNode) {
				$interface->assign('recordIndex', $index + 1);
				$interface->assign('resultIndex', $index + 1 + (($this->page - 1) * $this->limit));

				$recordDriver = new GaleRecordDriver($recordNode);
				if ($recordDriver->isValid()) {
					$interface->assign('recordDriver', $recordDriver);
					$html[] = $interface->fetch($recordDriver->getCombinedResult());
				}
			}
		}

		return $html;
	}

	public function retrieveRecord(string $identifier): ?SimpleXMLElement {
	/*	$settings = $this->getSettings();
		if ($settings === null || empty($settings->locationId)) {
			return null;
		}
		global $logger;
		$logger->log("Gale retrieve record: " . $identifier, Logger::LOG_ERROR);
	
		$productCode = $this->getProductCode();
		$params = [
			'startRecord'    => 1,
			'maximumRecords' => 1,
			'operation'      => 'searchRetrieve',
			'version'        => '1.1',
			'query'          => 'dc.identifier=' . $identifier . '',
			'x-username'     => $settings->locationId,
		];
		$recordUrl = rtrim($this->galeBaseUrl, '/') . '/' . rawurlencode($productCode) .
			'?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
		$curl = $this->getCurlConnection();
		$logger->log("Gale record URL: $recordUrl", Logger::LOG_ERROR);
		curl_setopt($curl, CURLOPT_URL, $recordUrl);
		$response = curl_exec($curl);
		if ($response === false || $response === '') {
			return null;
		}
	
		$xml = simplexml_load_string($response);
		if (!$xml) {
			return null;
		}
		$xml->registerXPathNamespace('zs', 'http://www.loc.gov/zing/srw/');
		$xml->registerXPathNamespace('dc', 'http://purl.org/dc/elements/1.1/');
		$records = $xml->xpath('//zs:records/zs:record/zs:recordData/dc:dc');
		return $records ? $records[0] : null;*/

		global $memCache;
	
		$data = $memCache->get('gale_min_' . $identifier);
		if (empty($data)) {
			return null;
		}
	
		$xml = '<dc:dc xmlns:dc="http://purl.org/dc/elements/1.1/">';
		if (!empty($data['title'])) {
			$xml .= '<dc:title>' . htmlspecialchars($data['title'], ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</dc:title>';
		}
		if (!empty($data['author'])) {
			$xml .= '<dc:creator>' . htmlspecialchars($data['author'], ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</dc:creator>';
		}
		if (!empty($data['format'])) {
			$xml .= '<dc:type>' . htmlspecialchars($data['format'], ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</dc:type>';
		}
		$xml .= '</dc:dc>';
	
			return simplexml_load_string($xml);
	}
	

	public function getSearchIndexes(): array {
		return [
    		'Keyword' => translate([
				'text' => 'Keyword',
				'isPublicFacing' => true,
				'inAttribute' => true,
			]),
			'Title' => translate([
				'text' => 'Title',
				'isPublicFacing' => true,
				'inAttribute' => true,
			]),
			'Author' => translate([
				'text' => 'Author',
				'isPublicFacing' => true,
				'inAttribute' => true,
			]),
			'Subject' => translate([
				'text' => 'Subject',
				'isPublicFacing' => true,
				'inAttribute' => true,
			]),
			'Publication' => translate([
				'text' => 'Publication',
				'isPublicFacing' => true,
				'inAttribute' => true,
			]),
		];
	}

	public function getSortOptions(): array {
		return [
			'relevance' => translate([
				'text' => 'Relevance',
				'isPublicFacing' => true,
				'inAttribute' => true,
			]),
		];
	}

	public function getFacetSet(): array {
		if (empty($this->lastSearchResults)) {
			return [];
		}
		$facetDefinitions = $this->getFacetDefinitions();
		if (empty($facetDefinitions)) {
			return [];
		}

		$facetCounts = [];
		foreach (array_keys($facetDefinitions) as $facetKey) {
			$facetCounts[$facetKey] = [];
		}

		foreach ($this->lastSearchResults as $recordNode) {
			$dcChildren = $recordNode->children('dc', true);
			foreach ($facetDefinitions as $facetKey => $definition) {
				$field = $definition['dcField'];
				$fieldName = substr($field, strpos($field, '.') + 1);
				if (!isset($dcChildren->$fieldName)) {
					continue;
				}
				foreach ($dcChildren->$fieldName as $valueNode) {
					$value = trim((string)$valueNode);
					if ($value === '') {
						continue;
					}
					if (!isset($facetCounts[$facetKey][$value])) {
						$facetCounts[$facetKey][$value] = 0;
					}
					$facetCounts[$facetKey][$value]++;
				}
			}
		}

		$facetSet = [];
		foreach ($facetCounts as $facetKey => $valueCounts) {
			if (empty($valueCounts)) {
				continue;
			}
			$definition = $facetDefinitions[$facetKey];
			$facetSet[$facetKey] = [
				'label' => $definition['label'],
				'multiSelect' => true,
				'list' => [],
			];
			foreach ($valueCounts as $value => $count) {
				$facetSet[$facetKey]['list'][$value] = [
					'type' => $facetKey,
					'value' => $value,
					'display' => $value,
					'count' => $count,
					'url' => $this->renderLinkWithFilter($facetKey, $value),
					'removalUrl' => $this->renderLinkWithoutFilter("$facetKey:$value"),
					'isApplied' => isset($this->filterList[$facetKey]) && in_array($value, $this->filterList[$facetKey], true),
				];
			}
		}

		return $facetSet;
	}

	public function getFilterList(): array {
		$list = [];
		$facetDefinitions = $this->getFacetDefinitions();
		foreach ($this->filterList as $field => $values) {
			$label = $facetDefinitions[$field]['label'] ?? ucfirst($field);
			foreach ($values as $value) {
				$list[$label][] = [
					'value' => $value,
					'display' => $value,
					'field' => $field,
					'removalUrl' => $this->renderLinkWithoutFilter("$field:$value"),
					'countIsApproximate' => false,
				];
			}
		}
		return $list;
	}

	public function getSearchesFile(): bool|string {
		return false;
	}

	public function buildRSS($result = null) {
		return null;
	}

	public function buildExcel($result = null) {
		return null;
	}

	public function getResultRecordSet(): array {
		return $this->lastSearchResults;
	}

	public function getSearchName() {
		return $this->searchSource;
	}

	public function getDefaultIndex() {
		return 'Keyword';
	}

	public function loadValidFields(): array {
		return array_keys($this->searchIndexFieldMap);
	}

	public function loadDynamicFields(): array {
		return [];
	}

	public function getIndexError() {
		return null;
	}

	private function getFacetDefinitions(): array {
		return [
			'type' => [
				'label' => translate([
					'text' => 'Format',
					'isPublicFacing' => true,
					'inAttribute' => true,
				]),
				'dcField' => 'dc.type',
			],
			'subject' => [
				'label' => translate([
					'text' => 'Subjects',
					'isPublicFacing' => true,
					'inAttribute' => true,
				]),
				'dcField' => 'dc.subject',
			],
			'publication' => [
				'label' => translate([
					'text' => 'Publication',
					'isPublicFacing' => true,
					'inAttribute' => true,
				]),
				'dcField' => 'dc.relation',
			],
		];
	}

	private function getSettings() {
		global $library;
		if ($library->galeSettingsId != -1) {
			$galeSetting = new GaleSetting();
			$galeSetting->id = $library->galeSettingsId;
			if (!$galeSetting->find(true)) {
				$galeSetting = null;
			}
			return $galeSetting;
		}
		AspenError::raiseError(new AspenError('There are no Gale Settings set for this library system.'));
	}	

	private function getProductCode(): string {
		return 'GPS';
	}

    public function __destruct() {
		if ($this->curl_connection) {
			curl_close($this->curl_connection);
		}
	}

	public function setSearchTerm($searchTerm) {
		if (strpos($searchTerm, ':') !== false) {
			[
				$searchIndex,
				$term,
			] = explode(':', $searchTerm, 2);
			$this->setSearchTerms([
				'lookfor' => $term,
				'index' => $searchIndex,
			]);
		} else {
			$this->setSearchTerms([
				'lookfor' => $searchTerm,
				'index' => $this->getDefaultIndex(),
			]);
		}
	}
		
}
