<?php
	require_once ROOT_DIR . '/sys/ECommerce/Pay360Setting.php';
	require_once ROOT_DIR . '/sys/Account/UserPayment.php';

// Interacts with the Pay360 API
class Pay360_Client  {
	// class parameters
	public $catalogDriver;
	public $selectedFines;
	public $payment;
	
	// query generation
	private $_pay360Settings;
	private $_soapClient;
	private $_digest;

	// query responses
	public $invokeResponse;
	public $queryResponse;

	public function __construct($settingsId, $paymentId, $selectedFines = [], $catalogDriver = null, $setTimestamp = false) {
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

	public function completeFineInIls(): void {
		$patron = new User;
		$patron->id = $this->payment->userId;
		if(!$patron->find(true)) {
			return;
		}
		$patron->completeFinePayment($this->payment);
	}

	public function setSettings($settingsId): void {
		$this->_pay360Settings = new Pay360Setting();
		$this->_pay360Settings->id = $settingsId;
		$this->_pay360Settings->find(true);
	}

	public function setPayment($paymentId): void {
		$this->payment = new UserPayment();
		$this->payment->id = $paymentId;
		$this->payment->find(true);
	}

	public function getOrderStatus($refresh = false) {
		if (!$refresh) {
			$result = ['state' => $this->queryResponse->transactionState];
			if (isset($this->queryResponse->paymentResult)) {
				$result['status'] = $this->queryResponse->paymentResult->status;
			}
			return $result;
		}
		$this->_getQueryResponse();
		$result = ['state' => $this->queryResponse->transactionState];
		if (isset($this->queryResponse->paymentResult)) {
			$result['status'] = $this->queryResponse->paymentResult->status;
		}
		return $result;
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

		if (empty($this->invokeResponse)) {
			$this->payment->cancelled = true;
			$this->payment->error = true;
			$this->payment->message = "failed to connect to Pay360";
			$this->payment->update(); 
			return false;
		}

		if ($this->invokeResponse->transactionState  !== "IN_PROGRESS") {
			$this->payment->cancelled = true;
			$this->payment->error = true;
			$this->payment->orderId = $this->invokeResponse->scpReference;
			$this->payment->message = "failed invoke request for scpReference " .  $this->invokeResponse->scpReference . "failed with status " . $this->invokeResponse->invokeResult->status;
			$this->payment->update(); 
			return false;
		}

		$this->payment->pay360TransactionStateMessage = "This payment is still in progress. Check back later for an update.";
		$this->payment->orderId = $this->invokeResponse->scpReference;
		$this->payment->update();
		return true;
	}

	public function handleOutcome(array $transactionStatus = []): bool|null {
		
		if (empty($transactionStatus)) {
    	    $transactionStatus = [
    	        'state' => $this->queryResponse->transactionState,
    	        'status' => $this->queryResponse->paymentResult->status
    	    ];
    	}

		if ($transactionStatus['state'] === 'INVALID_REFERENCE') {
			$this->payment->cancelled = true;
			$this->payment->error = true;
			$this->payment->message = "invalid reference";
			$this->payment->pay360TransactionStateMessage = "This payment failed.";
			$this->payment->update();
			return false;
		}

		if ($transactionStatus['state'] === 'IN_PROGRESS') {
			// do nothing, the polling process will be on-going
			return null;
		}

		if ($transactionStatus['state'] === 'COMPLETE') {
			
			if ($transactionStatus['status'] === 'SUCCESS') {
				$this->payment->message = 'Payment successful';
				$this->payment->pay360TransactionStateMessage = "This payment was successful.";
				$this->completeFineInIls();
				return false;
			}

			if ($transactionStatus['status'] === 'CANCELLED') {
				$this->payment->cancelled = true;
				$this->payment->message = 'cancelled by patron. error id: '  . $this->queryResponse->error->errorId;
				$this->payment->pay360TransactionStateMessage = "This payment was cancelled.";
				$this->payment->update();
				return false;
			}

			if ($transactionStatus['status'] === 'CARD_DETAILS_REJECTED') {
				$this->payment->cancelled = true;
				$this->payment->message = 'card details rejected. error id: '  . $this->queryResponse->error->errorId;
				$this->payment->pay360TransactionStateMessage = "This payment failed - card details were rejected.";
				$this->payment->update();
				return false;
			}

			if ($transactionStatus['status'] === 'LOGGED_OUT') {
				$this->payment->cancelled = true;
				$this->payment->message = 'patron logged out. error id: '  . $this->queryResponse->error->errorId;
				$this->payment->pay360TransactionStateMessage = "This payment failed.";
				$this->payment->update();
				return false;
			}

			if ($transactionStatus['status'] === 'NOT_ATTEMPTED') {
				$this->payment->cancelled = true;
				$this->payment->message = 'patron did not attempt payment. error id: '  . $this->queryResponse->error->errorId;
				$this->payment->pay360TransactionStateMessage = "This payment was not attempted.";
				$this->payment->update();
				return false;
			}

			if ($transactionStatus['status'] === 'ERROR') {
				$this->payment->error = true;
				$this->payment->cancelled = true;
				$this->payment->message = 'error: ' . $this->queryResponse->error->errorId . ': ' . $this->queryResponse->error->errorMessage;
				$this->payment->pay360TransactionStateMessage = "This payment failed.";
				$this->payment->update();
				return false;
			}

			return false;
		}
		return false;
	}

	// API queries
	private function _getInvokeResponse(): void {
		if (empty($this->_soapClient) || empty($this->_pay360Settings) || empty($this->payment)) {
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

	private function _getQueryResponse(): void {
		if (empty($this->_soapClient) || empty($this->_pay360Settings) || empty($this->payment)) {
		   return;  
		}
		
		$params = [
			'credentials' => $this->_getCredentialParams(),
			'siteId' => $this->_pay360Settings->siteId,
			'scpReference' => $this->payment->orderId,
		];
		
		try {
			$response = $this->_soapClient->scpSimpleQuery($params);
		} catch (Exception $e) {
			return;
		}
		$this->queryResponse = $response;
	}

	// services
	private function handleTimeout() {}
	private function _getMultiLineItemParameters(): array|null {
		if (!$this->catalogDriver) {
			return null;
		}
		$items = [];
		foreach( $this->selectedFines as $fine) {	
			$fineDetails = $this->catalogDriver->hasAdditionalFineFields() ? $this->catalogDriver->getFineById($fine['id'], true) : [];
			//BLOCKED - TODO: vatCode, fundCode and reference
			$vatCode = 'test';
			$fundCode = 'test';
			$reference = 'test';
			$amountInMinorUnits = $this->getMinorUnitsAmount($fineDetails['amountVal']);
			$item = [
			'itemSummary' =>[
						'description' => $fineDetails['reason'],
						'amountInMinorUnits' => $amountInMinorUnits,
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
		if (!$this->_digest || empty($this->_pay360Settings) || !$this->payment->pay360Timestamp) {
		   return null;  
		}

		global $configArray;
		$amountInMinorUnits = $this->getMinorUnitsAmount($this->payment->totalPaid);

		$returnUrl = $configArray['Site']['url'] . "/MyAccount/AJAX?method=completePay360Order&paymentId=" . $this->payment->id ."&settingsId=" . $this->_pay360Settings->id;

		return [
			'credentials' => $this->_getCredentialParams(),
			'requestType' => 'payOnly',
			'requestId' => 'TEST',
			'routing' => [
				'returnUrl' => new SoapVar($returnUrl, XSD_STRING),
				'backUrl' => new SoapVar($returnUrl, XSD_STRING),
				'siteId' => $this->_pay360Settings->siteId,
				'scpId' => $this->_pay360Settings->scpId,
			],
			'panEntryMethod' => 'ECOM',
			'sale' => [
				'saleSummary' => [
					'description' => "Aspen Discovery - Pay360 integration " .  $this->payment->id,
					'amountInMinorUnits' => $amountInMinorUnits,
				],
			],  
		];
	}

	private function _getCredentialParams(): array|null {
		if (!$this->_digest || empty($this->_pay360Settings) || !$this->payment->pay360Timestamp) {
		   return null;  
		}
		return 	[
			'subject' => [
				'subjectType' => $this->_pay360Settings->subjectType,
				'identifier' => $this->_pay360Settings->scpId,
				'systemCode' => $this->_pay360Settings->systemCode,
			],
			'requestIdentification' => [
				'uniqueReference' => $this->payment->id,
				'timeStamp' => $this->payment->pay360Timestamp,
			],
			'signature' => [
				'algorithm' => $this->_pay360Settings->algorithm,
				'hmacKeyID' => $this->_pay360Settings->hmacKeyId,
				'digest' => $this->_digest,
			],
		];
	}

	private function _setDigest(): void {
		if ($this->_digest || empty($this->_pay360Settings) || !$this->payment->pay360Timestamp || empty($this->payment)) {
		   return;  
		}
		$credentialsStr = $this->_pay360Settings->subjectType . "!" . $this->_pay360Settings->scpId . "!" . $this->payment->id . "!" . $this->payment->pay360Timestamp . "!" . $this->_pay360Settings->algorithm . "!" . $this->_pay360Settings->hmacKeyId;
		$hash = hash_hmac('sha256', $credentialsStr, base64_decode($this->_pay360Settings->privateKey), true);
		$this->_digest = base64_encode($hash);
	}

	private function getMinorUnitsAmount(string $totalAmount): string {
		$numDec = strlen($totalAmount) - strpos($totalAmount, '.') - 1;

		if ($numDec == 1) {
			return str_replace('.', '', $totalAmount .= "0");
		}
		
		if ( $numDec == 0 ) {
			return str_replace('.', '', $totalAmount .= "00");
		}
		
		if ( $numDec == 6 ) {
			// Handle discrete fine item amount format
			return str_replace(['.', '0000'], '', $totalAmount);
		}

		return str_replace('.', '', $totalAmount);
	} 
}