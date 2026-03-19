<?php

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;

require_once ROOT_DIR . '/services/Authentication/OAuth2Server/_toolkit_loader.php';
require_once ROOT_DIR . '/sys/Authentication/OAuth2/OAuth2Client.php';
require_once ROOT_DIR . '/sys/Authentication/OAuth2/Entities/OAuth2ClientEntity.php';

class ClientRepository implements ClientRepositoryInterface {

	public function getClientEntity($clientIdentifier): ?ClientEntityInterface {
		$client = new OAuth2Client();
		$client->setClientId($clientIdentifier);
		$client->setIsActive(1);

		// DEBUG: Log the lookup attempt
		if (defined('OAUTH2_DEBUG') && OAUTH2_DEBUG) {
			error_log("[OAuth2] ClientRepository::getClientEntity() - Looking for client_id: " . $clientIdentifier);
		}
		
		if ($client->find(true)) {
			if (defined('OAUTH2_DEBUG') && OAUTH2_DEBUG) {
				error_log("[OAuth2] ClientRepository::getClientEntity() - FOUND client with ID: " . $client->getClientId());
				error_log("[OAuth2] ClientRepository::getClientEntity() - Client name: " . $client->getName());
				error_log("[OAuth2] ClientRepository::getClientEntity() - Client type: " . $client->client_type);
				error_log("[OAuth2] ClientRepository::getClientEntity() - Redirect URI: " . (is_array($client->getRedirectUri()) ? json_encode($client->getRedirectUri()) : '"' . $client->getRedirectUri() . '"'));
				error_log("[OAuth2] ClientRepository::getClientEntity() - Is Active: " . ($client->is_active ? '1' : '0'));
			}
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
		if (defined('OAUTH2_DEBUG') && OAUTH2_DEBUG) {
			error_log("[OAuth2] ClientRepository::getClientEntity() - CLIENT NOT FOUND for ID: " . $clientIdentifier);
		}
		return null;
	}

	public function validateClient($clientIdentifier, $clientSecret, $grantType): bool {
		$client = new OAuth2Client();
		$client->setClientId($clientIdentifier);
		$client->setIsActive(1);

		if ($client->find(true)) {
			if ($grantType === 'password') {
				return true;
			}

			$storedSecret = $client->getClientSecret();

			if (defined('OAUTH2_DEBUG') && OAUTH2_DEBUG) {
				error_log("[OAuth2] ClientRepository::validateClient() - Validating client_id: " . $clientIdentifier . " for grant_type: " . $grantType);
				error_log("[OAuth2] ClientRepository::validateClient() - Client found: " . $client->getName());
				error_log("[OAuth2] ClientRepository::validateClient() - Comparing secrets...");
			}

			if ($storedSecret === $clientSecret) {
				if (defined('OAUTH2_DEBUG') && OAUTH2_DEBUG) {
					error_log("[OAuth2] ClientRepository::validateClient() - ✓ Client secret validated");
				}
				return true;
			} else {
				if (defined('OAUTH2_DEBUG') && OAUTH2_DEBUG) {
					error_log("[OAuth2] ClientRepository::validateClient() - ✗ Client secret mismatch");
					error_log("[OAuth2] ClientRepository::validateClient() - Stored (first 20 chars): " . substr($storedSecret, 0, 20));
					error_log("[OAuth2] ClientRepository::validateClient() - Provided (first 20 chars): " . substr($clientSecret, 0, 20));
				}
				return false;
			}
		}

		if (defined('OAUTH2_DEBUG') && OAUTH2_DEBUG) {
			error_log("[OAuth2] ClientRepository::validateClient() - Client not found");
		}
		return false;
	}
}
