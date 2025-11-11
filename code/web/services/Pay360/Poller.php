<?php
// DRAFT
require_once ROOT_DIR . '/services/Pay360/Client.php';

class Pay360_Poller {
	private $_client;
	private $_maxAttempts = 5;
	private $_intervalSeconds = 5;
	private $_priorStatus;
	private $_transactionInfo;

	public function __construct($_client) {
		$this->_client = $_client;
	}

	// FIXME: implement ExternalRequestLogEntry::logRequest(); 
	public function poll() {
		sleep(30);
		$counter = 1;
		
		$this->_transactionInfo = $this->_client->getOrderStatus(true);

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
		// TODO: store pay360 transaction state / status in user_payments;
		return;
	}
	// TODO: send email if payment successful -> receipt
 
	// TODO: private function handleSuccess($_transactionId, $_status) {
	// }

	// TODO: private function handleFailure($_transactionId, $_status) {
	// }

	// TODO: private function handleInProgress($_transactionId, $_status) {
	// }

	// TODO: private function handleTimeout($_transactionId) {
	// }
}