<?php
	require_once ROOT_DIR . '/sys/ECommerce/Pay360Setting.php';
	require_once ROOT_DIR . '/sys/Account/UserPayment';

// Interacts with the Pay360 API
class Pay360_Client  {
	// class parameters
	public $catalogDriver;
	public $selectedFines;
	
	// query generation
	private $_pay360Settings;
	private $_soapClient;
	private $_payment;
	private $_digest;
	private $_transactionStates = ['IN_PROGRESS', 'COMPLETE', 'INVALID_REFERENCE'];
	private $_statuses = ['SUCCESS', 'CARD_DETAILS_REJECTED', 'CANCELLED', 'LOGGED_OUT', 'NOT_ATTEMPTED', 'ERROR'];

	// query responses
	public $invokeResponse;

	public function __construct($settingsId, $paymentId, $selectedFines = [], $catalogDriver = [], $setTimestamp = false) {
		$this->setSettings($settingsId);
		$this->setPayment($paymentId);

		$this->catalogDriver = $catalogDriver;
		$this->selectedFines = $selectedFines;

		if ($setTimestamp) {
			$this->setTimeStamp();
		}

		$this->setSoapClient();
		$this->_setDigest();
	}

	public function setSettings($settingsId): void {
		$this->_pay360Settings = new Pay360Setting();
		$this->_pay360Settings->id = $settingsId;
		$this->_pay360Settings->find(true);
	}

	public function setPayment($paymentId): void {
		$this->_payment = new UserPayment();
		$this->_payment->id = $paymentId;
		$this->_payment->find(true);
	}

	public function setSoapClient() {
		$this->_soapClient = new SoapClient($this->_pay360Settings->wsldUrl, ['features' => SOAP_SINGLE_ELEMENT_ARRAYS]);
	}
	
	public function setTimeStamp() {
		$this->payment->pay360Timestamp = gmdate("YmdHis");
	}

	public function createOrder(): bool {
		if (empty($this->_pay360Settings)) {
			return false;
		}

		$this->_getInvokeResponse();

		if (empty($this->invokeResponse) || $this->invokeResponse->invokeResult->status !== "SUCCESS") {
			$this->_payment->cancelled = true;
			$this->_payment->error = "invoke request for scpReference " .  $this->invokeResponse->scpReference . "failed with status " . $this->invokeResponse->invokeResult->status;
			$this->_payment->update(); 
			return false;
		}

		$this->_payment->pay360PaymentReferenceNumber = $this->invokeResponse->scpReference;
		$this->_payment->update();
		return true;
	}

	// API queries
	private function _getInvokeResponse(): void {
		if (empty($this->_soapClient) || empty($this->_pay360Settings) || empty($this->_payment)) {
		   return;  
		}

		$params = $this->_getInvokeParameters();
		$params['sale']['items']['item'] = $this->_getMultiLineItemParameters();
		
		try {
			$response = $this->_soapClient->scpSimpleInvoke($params);
		} catch (Exception $e) {
			$this->payment->error = true;
			$this->payment->message = $e->getMessage();
			$this->payment->update();
			return;
		}
		$this->invokeResponse = $response;
	}

	// services
	private function _getMultiLineItemParameters(): array|null {
		$items = [];
		foreach( $this->selectedFines as $fine) {	
			$fineDetails = $this->catalogDriver->hasAdditionalFineFields() ? $this->catalogDriver->getFineById($fine['id'], true) : [];
			//BLOCKED - TODO: vatCode, fundCode and reference
			$vatCode = 'test';
			$fundCode = 'test';
			$reference = 'test';
			$fineAmountInMinorUnits = str_replace(['.', '0000'], '', $fineDetails['amountVal']);
			$item = [
			'itemSummary' =>[
						'description' => $fineDetails['reason'],
						'amountInMinorUnits' => $fineAmountInMinorUnits,
						'reference' => $reference,
						'displayableReference' => $fineDetails['reason'], 
				],
				'tax' => $vatCode,
				'IgItemDetails' => [
					'fundCode' => $fundCode,
					'additionalReference' => $fineDetails['reason'],
					'narrative' => $fineDetails['reason'],
					'customerInfo' => $fineDetails['message'],
				],
				'lineId' => $fineDetails['fineId']
			];
			array_push($items, $item);
		}
		return $items;
	}

	private function _getInvokeParameters(): array| null {
		if (!$this->_digest || empty($this->_pay360Settings) || !$this->_payment->pay360Timestamp) {
		   return null;  
		}

		global $configArray;
		$amountInMinorUnits = str_replace('.', '', $this->_payment->totalPaid);

		return [
			'credentials' => $this->_getCredentialParams(),
			'requestType' => 'payOnly',
			'requestId' => 'TEST',
			'routing' => [
				'returnUrl' => $this->_pay360Settings->returnUrl,
				'backUrl' => $configArray['Site']['url'] . "/AJAX?method=completePay360Order&paymentId=" . $this->_payment->id ,
				'siteId' => $this->_pay360Settings->siteId,
				'scpId' => $this->_pay360Settings->scpId,
			],
			'panEntryMethod' => 'ECOM',
			'sale' => [
				'saleSummary' => [
					'description' => "Aspen Discovery - Pay360 integration " .  $this->_payment->id,
					'amountInMinorUnits' => $amountInMinorUnits,
				],
			],  
		];
	}

	private function _getCredentialParams(): array|null {
		if (!$this->_digest || empty($this->_pay360Settings) || !$this->_payment->pay360Timestamp) {
		   return null;  
		}
		return 	[
			'subject' => [
				'subjectType' => $this->_pay360Settings->subjectType,
				'identifier' => $this->_pay360Settings->scpId,
				'systemCode' => $this->_pay360Settings->systemCode,
			],
			'requestIdentification' => [
				'uniqueReference' => $this->_payment->id,
				'timeStamp' => $this->_payment->pay360Timestamp,
			],
			'signature' => [
				'algorithm' => $this->_pay360Settings->algorithm,
				'hmacKeyID' => $this->_pay360Settings->hmacKeyId,
				'digest' => $this->_digest,
			],
		];
	}

	private function _setDigest(): void {
		if ($this->_digest || empty($this->_pay360Settings) || !$this->_payment->pay360Timestamp || empty($this->_payment)) {
		   return;  
		}
		$credentialsStr = $this->_pay360Settings->subjectType . "!" . $this->_pay360Settings->scpId . "!" . $this->_payment->id . "!" . $this->_payment->pay360Timestamp . "!" . $this->_pay360Settings->algorithm . "!" . $this->_pay360Settings->hmacKeyId;
		$hash = hash_hmac('sha256', $credentialsStr, base64_decode($this->_pay360Settings->privateKey), true);
		$this->_digest = base64_encode($hash);
	}
}