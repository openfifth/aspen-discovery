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

		if ($client->find(true)) {
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
		}
		return false;
	}
}
