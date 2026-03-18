<?php

require_once ROOT_DIR . '/services/Authentication/OAuth2Server/_toolkit_loader.php';
require_once ROOT_DIR . '/sys/OAuth2/OAuth2Client.php';

class ClientRepository implements \League\OAuth2\Server\Repositories\ClientRepositoryInterface {

	public function getClientEntity($clientIdentifier): ?\League\OAuth2\Server\Entities\ClientEntityInterface {
		$client = new OAuth2Client();
		$client->client_id = $clientIdentifier;
		$client->is_active = 1;

		if ($client->find(true)) {
			$clientEntity = new \League\OAuth2\Server\Entities\ClientEntity();
			$clientEntity->setIdentifier($client->client_id);
			$clientEntity->setName($client->name);
			$clientEntity->setRedirectUri($client->redirect_uri);
			return $clientEntity;
		}

		return null;
	}

	public function validateClient($clientIdentifier, $clientSecret, $grantType): bool {
		$client = new OAuth2Client();
		$client->client_id = $clientIdentifier;
		$client->is_active = 1;

		if ($client->find(true)) {
			// For password grant, we don't require client secret verification
			if ($grantType === 'password') {
				return true;
			}
			
			// For other grants, verify the client secret
			return password_verify($clientSecret, $client->client_secret) || $clientSecret === $client->client_secret;
		}

		return false;
	}
}
