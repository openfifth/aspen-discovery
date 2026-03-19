<?php

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;

require_once ROOT_DIR . '/services/Authentication/OAuth2Server/_toolkit_loader.php';
require_once ROOT_DIR . '/sys/Authentication/OAuth2/Entities/OAuth2ScopeEntity.php';

class ScopeRepository implements ScopeRepositoryInterface {

	public function getScopeEntityByIdentifier($identifier): ?ScopeEntityInterface {
		$validScopes = OAuth2Client::getScopeOptions();

		if (array_key_exists($identifier, $validScopes)) {
			$scope = new OAuth2ScopeEntity();
			$scope->setIdentifier($identifier);
			return $scope;
		}

		return null;
	}

	public function finalizeScopes(array $scopes, $grantType, ClientEntityInterface $clientEntity, $userIdentifier = null, ?string $authCodeId = null): array {
		require_once ROOT_DIR . '/sys/Authentication/OAuth2/OAuth2Client.php';
		$client = new OAuth2Client();
		$client->setClientId($clientEntity->getIdentifier());
		$client->setIsActive(1);

		if (!$client->find(true)) {
			return [];
		}

		$allowedScopes = $client->getScopesArray();
		$finalScopes = [];

		foreach ($scopes as $scope) {
			if (in_array($scope->getIdentifier(), $allowedScopes)) {
				$finalScopes[] = $scope;
			}
		}

		return $finalScopes;
	}
}