<?php

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;

require_once ROOT_DIR . '/services/Authentication/OAuth2Server/_toolkit_loader.php';
require_once ROOT_DIR . '/sys/Authentication/OAuth2/OAuth2Client.php';
require_once ROOT_DIR . '/sys/Authentication/OAuth2/Entities/OAuth2ClientEntity.php';

class ClientRepository implements ClientRepositoryInterface {

	public function getClientEntity($clientIdentifier): ?ClientEntityInterface {
		global $logger;
		$client = new OAuth2Client();
		$client->setClientId($clientIdentifier);
		$client->setIsActive(1);

		// DEBUG: Log the lookup attempt
		$logger->log("[OAuth2] ClientRepository::getClientEntity() - Looking for client_id: " . $clientIdentifier, Logger::LOG_DEBUG);
		
		if ($client->find(true)) {
			$logger->log("[OAuth2] ClientRepository::getClientEntity() - FOUND client with ID: " . $client->getClientId(), Logger::LOG_DEBUG);
			$logger->log("[OAuth2] ClientRepository::getClientEntity() - Client name: " . $client->getName(), Logger::LOG_DEBUG);
			$logger->log("[OAuth2] ClientRepository::getClientEntity() - Client type: " . $client->client_type, Logger::LOG_DEBUG);
			$logger->log("[OAuth2] ClientRepository::getClientEntity() - Redirect URI: " . (is_array($client->getRedirectUri()) ? json_encode($client->getRedirectUri()) : '"' . $client->getRedirectUri() . '"'), Logger::LOG_DEBUG);
			$logger->log("[OAuth2] ClientRepository::getClientEntity() - Is Active: " . ($client->is_active ? '1' : '0'), Logger::LOG_DEBUG);


			$clientEntity = new OAuth2ClientEntity();
			$clientEntity->setIdentifier($client->getClientId());
			$clientEntity->setName($client->getName());
			$clientEntity->setRedirectUri($client->getRedirectUri());
			
			// Mark as confidential based on client type
			// Service applications and web applications are confidential
			// Only public clients (native/mobile) are not confidential
			$isConfidential = ($client->client_type !== 'native_application');
			$clientEntity->setIsConfidential($isConfidential);

			return $clientEntity;
		}

		// DEBUG: Log lookup failure
		$logger->log("[OAuth2] ClientRepository::getClientEntity() - CLIENT NOT FOUND for ID: " . $clientIdentifier, Logger::LOG_DEBUG);
		return null;
	}

	public function validateClient($clientIdentifier, $clientSecret, $grantType): bool {
		global $logger;
		$client = new OAuth2Client();
		$client->setClientId($clientIdentifier);
		$client->setIsActive(1);

		if ($client->find(true)) {
			if ($grantType === 'password') {
				return true;
			}

			$storedSecret = $client->getClientSecret();

			$logger->log("[OAuth2] ClientRepository::validateClient() - Validating client_id: " . $clientIdentifier . " for grant_type: " . $grantType, Logger::LOG_DEBUG);
			$logger->log("[OAuth2] ClientRepository::validateClient() - Client found: " . $client->getName(), Logger::LOG_DEBUG);
			$logger->log("[OAuth2] ClientRepository::validateClient() - Comparing secrets...", Logger::LOG_DEBUG);

			if ($storedSecret === $clientSecret) {
				$logger->log("[OAuth2] ClientRepository::validateClient() - Client secret found", Logger::LOG_DEBUG);
				return true;
			} else {
				$logger->log("[OAuth2] ClientRepository::validateClient() - ✗ Client secret mismatch", Logger::LOG_DEBUG);
				$logger->log("[OAuth2] ClientRepository::validateClient() - Stored (first 20 chars): " . substr($storedSecret, 0, 20), Logger::LOG_DEBUG);
				$logger->log("[OAuth2] ClientRepository::validateClient() - Provided (first 20 chars): " . substr($clientSecret, 0, 20), Logger::LOG_DEBUG);
				return false;
			}
		}
		
		$logger->log("[OAuth2] ClientRepository::validateClient() - Client not found", Logger::LOG_DEBUG);
		return false;
	}
}
