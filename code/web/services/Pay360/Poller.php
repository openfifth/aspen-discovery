<?php
require_once ROOT_DIR . '/services/Pay360/Client.php';

class Pay360_Poller {
	private $_client;
	private $_maxAttempts = 6; // check for half an hour in total 
	private $_intervalSeconds = 300; // check every 5 minutes
	private $_priorStatus;
	private $_transactionInfo;

	public function __construct($_client) {
		$this->_client = $_client;
	}

	public function poll() {
		sleep(600); // wait 10 minutes for the patron to have likely attempted the payment
		$counter = 1;
		
		$this->_transactionInfo = $this->_client->getOrderStatus(true);

		if ($this->_transactionInfo['state'] === 'COMPLETE') {
			$this->_priorStatus = $this->_transactionInfo['status'];
			$this->_client->handleOutcome($this->_transactionInfo);
			return;
		}

		while ($counter <= $this->_maxAttempts) {

			if ($this->_transactionInfo['state'] === 'COMPLETE') {
				$this->_priorStatus = $this->_transactionInfo['status'];
				break;
			}

			if (isset($this->_transactionInfo['status']) && $this->_transactionInfo['status'] !== $this->_priorStatus) {
				$this->_priorStatus = $this->_transactionInfo['status'];
			}

			sleep($this->_intervalSeconds);
			$counter++;

			$this->_transactionInfo = $this->_client->getOrderStatus(true);
		}

		$this->_client->handleOutcome($this->_transactionInfo);
		return;
	}
}