<?php
require_once ROOT_DIR . '/sys/OCLCRSFG/OCLCRSFGRequest.php';
require_once ROOT_DIR . '/sys/OCLCRSFG/OCLCRSFGSetting.php';
require_once ROOT_DIR . '/sys/Utils/StringUtils.php';

use League\OAuth2\Client\OptionProvider\HttpBasicAuthOptionProvider;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

class OCLCRSFGDriver {
	private $accessToken;
	private $_registryId;

	public function __construct() {
		$homeLocation = Location::getUserHomeLocation();
		$this->_registryId = $homeLocation ? $homeLocation->oclcRegistryId : "" ;
	}

	// Controllers

	public function getAccountSummary(User $user): AccountSummary {
		[
			$existingId,
			$summary,
		] = $user->getCachedAccountSummary('oclcRSFG');

		if ($summary === null || isset($_REQUEST['reload'])) {
			//Get account information from api
			require_once ROOT_DIR . '/sys/User/AccountSummary.php';
			$summary = new AccountSummary();
			$summary->userId = $user->id;
			$summary->source = 'oclcRSFG';
			$summary->resetCounters();

			$settings = new OCLCRSFGSetting();
			$homeLibrary = Library::getPatronHomeLibrary();
			$settings->whereAdd("id={$homeLibrary->oclcRSFGSettingsId}");
			if($settings->find()) {
				$settings->fetch();
			}
			$requests = $this->getRequests($user, $settings);
			$summary->numUnavailableHolds = count($requests['unavailable']);
			$summary->numAvailableHolds = count($requests['available']);
		}

		return $summary;
	}

	public function getRequests(User $patron, $setting): array {
		if (empty($this->_registryId)) {
			return [];
		}
		try {
			if (empty($this->accessToken)) {
				$this->setAccessToken($setting);
			}
		} catch (Exception $e) {
			global $logger;
			$logger->log("Exception conducting pre-submission checks for an ILL request to the Resource Sharing Requests API: $e", Logger::LOG_ERROR);
			return [
				'title' => translate([
					'text' => 'Request Failed',
					'isPublicFacing' => true,
				]),
				'message' => translate([
					'text' => "Could not send request to the Resource Sharing For Groups system.",
					'isPublicFacing' => true,
				]),
				'success' => false,
			];
		}
		$requestsSent = $this->getAllRequestsFromAspenDbForPatron($patron->id);
		$openRequests = [];
		$processedRequests = [];
		foreach ($requestsSent as $requestInAspenDb) {
			if ($requestInAspenDb->oclcRequestId) {
				$requestInOCLCRSFG = $this->getRequestFromOCLCRSFGWithId($setting, $requestInAspenDb->oclcRequestId);
			}
			if (!empty($requestInOCLCRSFG)) {
				$requestInAspenDb->requestStatus = $requestInOCLCRSFG['illRequest']['requestStatus'];
				$requestInAspenDb->update();
				if(
					$requestInAspenDb->requestStatus == "REVIEW" ||
					$requestInAspenDb->requestStatus == "REVIEWING"
				){
					$openRequests[] = $this->createTemporaryHold($patron->id, $requestInAspenDb);
				}
				if(
					$requestInAspenDb->requestStatus == "RECEIVED"
				){
					$processedRequests[] = $this->createTemporaryHold($patron->id, $requestInAspenDb);
				}
			}
		}
		return [
			'unavailable' => $openRequests,
			'available' => $processedRequests
		];
	}

	public function submitRequest(OCLCRSFGSetting $setting, User $patron, $requestFormData): array {
		global $logger;
		if (empty($this->_registryId)) {
			$logger->log("Could not Authenticate: home location has not been assigned an OCLC Registry Id", Logger::LOG_ERROR);
			throw  new Exception("This library branch is not configured to send ILL requests. Please contact your library.");
		}

		try {
			if (empty($this->accessToken) || time() > $this->accessToken->expires) {
				$this->setAccessToken($setting);
			}
		} catch (Exception $e) {
			global $logger;
			$logger->log("Exception conducting pre-submission checks for an ILL request to the Resource Sharing Requests API: $e", Logger::LOG_DEBUG);
			return [
				'title' => translate([
					'text' => 'Request Failed',
					'isPublicFacing' => true,
				]),
				'message' => translate([
					'text' => "Could not send request to the Resource Sharing For Groups system.",
					'isPublicFacing' => true,
				]),
				'success' => false,
			];
		}

		$requestInAspenDb = new OCLCRSFGRequest();
		$this->populateNewRequest($requestInAspenDb, $requestFormData, $patron);

		if ($this->isDuplicate($setting, $patron->id, $requestInAspenDb)) {
			return [
				'title' => translate([
					'text' => 'Request Failed',
					'isPublicFacing' => true,
				]),
				'message' => translate([
					'text' => "This title has already been requested for you.  You may only have one active request for a title.",
					'isPublicFacing' => true,
				]),
				'success' => false,
			];
		}
		$requestInAspenDb->insert();
		try {
			$IllRequestCreated = $this->postToOCLCRSFG($setting->serviceBaseUrl, $requestInAspenDb);
			$requestInAspenDb->requestStatus = $IllRequestCreated['responses']['illRequest']['requestStatus'];
			$requestInAspenDb->oclcRequestId = $IllRequestCreated['responses']['illRequest']['requestId'];
		} catch (Exception $e) {
			global $logger;
			$logger->log("Exception submitting an ILL request to the Resource Sharing Requests API: $e", Logger::LOG_ERROR);
			return [
				'title' => translate([
					'text' => 'Request Failed',
					'isPublicFacing' => true,
				]),
				'message' => translate([
					'text' => "Could not send request to the Resource Sharing For Groups system.",
					'isPublicFacing' => true,
				]),
				'success' => false,
			];
		}
		$requestInAspenDb->update();
		return [
			'title' => translate([
				'text' => 'Request Sent',
				'isPublicFacing' => true,
			]),
			'message' => translate([
				'text' => "Your request has been submitted. You can check the status of your request within your account under Titles On Hold. Please allow for a few minutes before refreshing the page to see your new interlibrary loan request.",
				'isPublicFacing' => true,
			]),
			'success' => true,
		];
	}

	private function isDuplicate(OCLCRSFGSetting $setting, Int $patronId, OCLCRSFGRequest $requestInAspenDb): bool {
		$this->updateRequestsInAspenDbForPatron($setting, $patronId);
		$existingRequests = $this->getAllRequestsFromAspenDbForPatron($patronId);
		foreach ($existingRequests as $existingRequest) {
			if (!$existingRequest->catalogKey) {
				return false;
			}
			if (
				$requestInAspenDb->catalogKey == $existingRequest->catalogKey
				&& $existingRequest->requestStatus != "RETURNED"
				&& $existingRequest->requestStatus != "CLOSED"
			) {
				return true;
			}
		}
		return false;
	}

	public function updateRequestsInAspenDbForPatron(OCLCRSFGSetting $setting, int $patronId): array {
		try {
			if (empty($this->accessToken)) {
				$this->setAccessToken($setting);
			}
		} catch (Exception $e) {
			global $logger;
			$logger->log("Exception conducting pre-submission checks for an ILL request to the Resource Sharing Requests API: $e", Logger::LOG_ERROR);
			return [
				'title' => translate([
					'text' => 'Request Failed',
					'isPublicFacing' => true,
				]),
				'message' => translate([
					'text' => "Could not send request to the Resource Sharing For Groups system.",
					'isPublicFacing' => true,
				]),
				'success' => false,
			];
		}

		if (empty($this->_registryId)) {
			global $logger;
			$logger->log("Could not Authenticate: home location has not been assigned an OCLC Registry Id", Logger::LOG_ERROR);
			throw  new Exception("This library branch is not configured to send ILL requests. Please contact your library.");
		}
	
		$requests = $this->getAllRequestsFromOCLCRSFGForPatron($setting, $patronId);

		foreach ($requests as $requestInOCLCRSFG) {
			$requestInAspenDb = new OCLCRSFGRequest();
			if (!empty($requestInOCLCRSFG)) {
				$this->updateRequestInAspenDb($requestInAspenDb, $requestInOCLCRSFG);
			}
		}

		return $requests;
	}

	// Services - interacts with Aspen DB

	private function getAllRequestsFromAspenDbForPatron(Int $patronId): array {
		$requestsToProcess = [];
		$request = new OCLCRSFGRequest();
		$request->userId = $patronId;
		$request->find();
		while ($request->fetch()) {
			if (empty($request->vdxId) && ($request->status != 'Not found in OCLC Resource Sharing For Groups' && $request->status != 'CANCELLED')) {
				$requestsToProcess[] = clone $request;
			}
		}
		return $requestsToProcess;
	}

	private function updateRequestInAspenDb(OCLCRSFGRequest $requestInAspenDb, $request): void {
		if (!isset($request['illRequest']['requestId'])) {
			return;
		}
		$requestInAspenDb->oclcRequestId =	$request['illRequest']['requestId'];
		if (!$requestInAspenDb->find(true)) {
			return;
		}

		// as of now, OCLC RS API will send back all requests, regardless of status. 
		if(isset($request['illRequest']['requestStatus']) && $request['illRequest']['requestStatus'] == "CLOSED") {
			$requestInAspenDb->whereAdd('oclcRequestId=' . $request['illRequest']['requestId']);
			$requestInAspenDb->delete(true);
			return;
		}

		if (isset($request['illRequest']['requestStatus'])) {
			$requestInAspenDb->requestStatus =	$request['illRequest']['requestStatus'];
		}
		if (isset($request['illRequest']['requestStatusDescription'])) {
			$requestInAspenDb->requestStatusDescription = $request['illRequest']['requestStatusDescription'];
		}
		if (isset($request['illRequest']['created'])) {
			$requestInAspenDb->createdDate = date_format(date_create($request['illRequest']['created']), 'Y-m-d g:i:s.u');
		}
		if (isset($request['illRequest']['verification'])) {
			$requestInAspenDb->verification = $request['illRequest']['verification'];
		}
		if (isset($request['illRequest']['needed'])) {
			$requestInAspenDb->needed =	date_format(date_create($request['illRequest']['needed']), 'Y-m-d g:i:s.u');
		}
		if (isset($request['illRequest']['requester']['serviceType'])) {
			$requestInAspenDb->serviceType = $request['illRequest']['requester']['serviceType'];
		}
		if (isset($request['illRequest']['item']['userId'])) {
			$requestInAspenDb->userId =	$request['illRequest']['item']['userId'];
		}
		if (isset($request['illRequest']['item']['email'])) {
			$requestInAspenDb->email = $request['illRequest']['item']['email'];
		}
		if (isset($request['illRequest']['item']['isbn'])) {
			$requestInAspenDb->isbn = $request['illRequest']['item']['isbn'];
		}
		if (isset($request['illRequest']['item']['issn'])) {
			$requestInAspenDb->issn = $request['illRequest']['item']['issn'];
		}
		if (isset($request['illRequest']['item']['oclcNumber'])) {
			$requestInAspenDb->oclcNumber =	$request['illRequest']['item']['oclcNumber'];
		}
		if (isset($request['illRequest']['item']['mediaType'])) {
			$requestInAspenDb->mediaType = $request['illRequest']['item']['mediaType'];
		}
		if (isset($request['illRequest']['item']['title'])) {
			$requestInAspenDb->title = $request['illRequest']['item']['title'];
		}
		if (isset($request['illRequest']['item']['author'])) {
			$requestInAspenDb->author = $request['illRequest']['item']['author'];
		}
		if (isset($request['illRequest']['item']['edition']['editionSpecific'])) {
			$requestInAspenDb->edition = $request['illRequest']['item']['edition']['editionSpecific'];
		}
		if (isset($request['illRequest']['item']['publisherName'])) {
			$requestInAspenDb->publisher = $request['illRequest']['item']['publisherName'];
		}
		if (isset($request['illRequest']['item']['language'])) {
			$requestInAspenDb->language = $request['illRequest']['item']['language'];
		}
		if (isset($request['illRequest']['feeAccepted'])) {
			$requestInAspenDb->feeAccepted = $request['illRequest']['feeAccepted'];
		}
		if (isset($request['illRequest']['maximumFeeAmount'])) {
			$requestInAspenDb->maximumFeeAmount = $request['illRequest']['maximumFeeAmount'];
		}
		if (isset($request['illRequest']['catalogKey'])) {
			$requestInAspenDb->catalogKey = $request['illRequest']['catalogKey'];
		}
		if (isset($request['illRequest']['note'])) {
			$requestInAspenDb->note = $request['illRequest']['note'];
		}
		if (isset($request['illRequest']['pickupLocation'])) {
			$requestInAspenDb->pickupLocation = $request['illRequest']['pickupLocation'];
		}

		$requestInAspenDb->update();
	}

	// Services - interacts with the Resource Sharing Request API from OCLC

	private function getAllRequestsFromOCLCRSFGForPatron(OCLCRSFGSetting $setting, Int $patronId): array {
		require_once ROOT_DIR . '/sys/CurlWrapper.php';
		$searchTerm = "searchTerm=patronID";
		$searchValue = "searchValue=" . "$patronId";
		$url = $setting->serviceBaseUrl . "/requests" . "?" . $searchTerm . "&" . $searchValue;
		$curl = new CurlWrapper();
		$customHeaders = [
			"Authorization" => "Authorization: Bearer " . $this->accessToken->getToken(),
		];
		$curl->addCustomHeaders($customHeaders, false);
		$curl->curl_connect($url);
		$response = $curl->curlGetPage($url);
		return json_decode(json_encode(simplexml_load_string($response)), true)['responses'];
	}

	private function getRequestFromOCLCRSFGWithId(OCLCRSFGSetting $setting, string $oclcRequestId): array|null {
		require_once ROOT_DIR . '/sys/CurlWrapper.php';
		$url = $setting->serviceBaseUrl . "/requests" . "/" . $oclcRequestId;
		$curl = new CurlWrapper();
		$customHeaders = [
			"Authorization" => "Authorization: Bearer " . $this->accessToken->getToken(),
		];
		$curl->addCustomHeaders($customHeaders, false);
		$curl->curl_connect($url);
		$response = $curl->curlGetPage($url);
		if (!$response) {
			throw new Exception("No requests found with id $oclcRequestId");
		}
		return json_decode(json_encode(simplexml_load_string($response)), true)['responses'];
	}

	private function postToOCLCRSFG(string $serviceBaseUrl, OCLCRSFGRequest $newRequest): array {
		require_once ROOT_DIR . '/sys/CurlWrapper.php';
		$url = $serviceBaseUrl . "/requests";
		$curl = new CurlWrapper();
		$customHeaders = [
			"Content-type" => "Content-type: application/json",
			"Authorization" => "Authorization: Bearer " . $this->accessToken->getToken(),
		];
		$curl->addCustomHeaders($customHeaders, false);
		$curl->curl_connect($url);
		$response = $curl->curlPostBodyData($url, $this->formatRequestBody($newRequest));
		try {
			$data = json_decode(json_encode(simplexml_load_string($response)), true);
		} catch (Exception $e) {
			throw  new Exception($e->getMessage());
		}
		return $data;
	}

	private function setAccessToken(OCLCRSFGSetting $setting): void {
		require_once 'oauth2_client_php_league/autoload.php';
		$basicAuth_provider = new HttpBasicAuthOptionProvider();
		$setup_options = [
			'clientId' => $setting->clientKey,
			'clientSecret' => $setting->clientSecret,
			'urlAuthorize' => $setting->authBaseUrl . "auth", // not used for this grant type yet field still required - could set to ''
			'urlAccessToken' => $setting->authBaseUrl . "token",
			'urlResourceOwnerDetails' => '',
			'redirectUri' => '',
		];
		$provider = new GenericProvider($setup_options, ['optionProvider' => $basicAuth_provider]);
		try {
			$this->accessToken = $provider->getAccessToken('client_credentials', ['scope' => $setting->scopes . " context:" . $this->_registryId]);
			return;
		} catch (IdentityProviderException $e) {
			throw  new Exception($e->getMessage());
		};
	}

	// Helpers

	private function createTemporaryHold($patronId, $request): Hold {
		require_once ROOT_DIR . '/sys/User/Hold.php';
		$curRequest = new Hold();
		$curRequest->userId = $patronId;
		$curRequest->type = 'interlibrary_loan';
		$curRequest->isIll = true;
		$curRequest->source = 'oclcRSFG';
		$curRequest->sourceId = $request->catalogKey;
		$curRequest->recordId = $request->catalogKey;
		$curRequest->title = $request->title;
		$curRequest->author = $request->author;
		$curRequest->status = $request->requestStatus;
		$curRequest->pickupLocationName = $request->pickupLocation;
		$curRequest->cancelId = $request->oclcRequestId;
		$curRequest->cancelable = false;
		if ($request->requestStatus == 'REVIEW' || $request->requestStatus == 'REVIEWING') {
			$curRequest->cancelable = true;
		}
		return $curRequest;
	}

	private function formatRequestBody(OCLCRSFGRequest $newRequest): object {
		$illRequest = [];
		$illRequest["requestStatus"] = "PROFILING";
		$illRequest["requester"] = [
			"institution" => [
				"institutionId" => $newRequest->oclcRequesterRegistryId
			],
			"serviceType" => "LOAN",
		];
		$illRequest["item"] = [
			"verification" => "item discovered on Aspen Discovery"
		];
		if (!empty($newRequest->isbn)) {
			$illRequest["item"]["isbn"] = $newRequest->isbn;
		}
		if (!empty($newRequest->issn)) {
			$illRequest["item"]["issn"] = $newRequest->issn;
		}
		if (!empty($newRequest->oclcNumber)) {
			$illRequest["item"]["oclcNumber"] = $newRequest->oclcNumber;
		}
		$illRequest["patron"] = [
			"patronApproved" => true,
			"userId" => "{$newRequest->userId}"
		];
		return (object)["illRequest" => $illRequest];
	}

	private function populateNewRequest(OCLCRSFGRequest $requestInAspenDb, &$requestFormData, User $patron): void {
		$requestInAspenDb->title = isset($requestFormData["title"]) ? strip_tags($requestFormData["title"]) : "";
		$requestInAspenDb->author = isset($requestFormData["author"]) ? strip_tags($requestFormData["author"]): "";
		$requestInAspenDb->publisher = isset($requestFormData["publisher"]) ? strip_tags($requestFormData["publisher"]) : "";
		$requestInAspenDb->isbn = isset($requestFormData["isbn"]) ? strip_tags($requestFormData["isbn"]) : "";
		$requestInAspenDb->issn = isset($requestFormData["issn"]) ? strip_tags($requestFormData["issn"]) : "";
		$requestInAspenDb->oclcNumber = isset($requestFormData["oclcNumber"]) ? strip_tags($requestFormData["oclcNumber"]) : "";
		if (isset($requestFormData["uniqueIdentifierKey"]) && isset($requestFormData["uniqueIdentifierValue"])) {
			$requestInAspenDb->{$requestFormData["uniqueIdentifierKey"]} = $requestFormData["uniqueIdentifierValue"];
		}
		$requestInAspenDb->feeAccepted = (isset($requestFormData['acceptFee']) && $requestFormData['acceptFee'] == 'true') ? 1 : 0;
		$requestInAspenDb->maximumFeeAmount = isset($requestFormData["maximumFeeAmount"]) ? strip_tags($requestFormData["maximumFeeAmount"]) : "";
		$requestInAspenDb->catalogKey = isset($requestFormData["catalogKey"]) ? strip_tags($requestFormData["catalogKey"]) : "";
		$requestInAspenDb->requestStatus = "NEW";
		$requestInAspenDb->note = isset($requestFormData["note"]) ? strip_tags($requestFormData["note"]) : "";
		$requestInAspenDb->oclcRequesterRegistryId = $this->_registryId;
		$requestInAspenDb->userId = $patron->id;
		$patronHomeLocation = $patron->getHomeLocation();
		$requestInAspenDb->pickupLocation = empty($patronHomeLocation->oclcRSFGLocation) ? $patronHomeLocation->code : $patronHomeLocation->oclcRSFGLocation;
	}
}
