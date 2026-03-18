<?php

require_once ROOT_DIR . '/services/Authentication/OAuth2Server/_toolkit_loader.php';

class ScopeRepository implements \League\OAuth2\Server\Repositories\ScopeRepositoryInterface {

	public function getScopeEntityByIdentifier($identifier): ?\League\OAuth2\Server\Entities\ScopeEntityInterface {
		$validScopes = [
			'user:read' => 'Read user information',
			'user:write' => 'Modify user information', 
			'catalog:read' => 'Read catalog information',
			'catalog:write' => 'Modify catalog information',
			'admin:read' => 'Read admin information',
			'admin:write' => 'Modify admin information',
		];

		if (array_key_exists($identifier, $validScopes)) {
			$scope = new \League\OAuth2\Server\Entities\ScopeEntity();
			$scope->setIdentifier($identifier);
			return $scope;
		}

		return null;
	}

	public function finalizeScopes(array $scopes, $grantType, \League\OAuth2\Server\Entities\ClientEntityInterface $clientEntity, $userIdentifier = null): array {
		// Load the client to check which scopes it's allowed to request
		require_once ROOT_DIR . '/sys/OAuth2/OAuth2Client.php';
		$client = new OAuth2Client();
		$client->client_id = $clientEntity->getIdentifier();
		$client->is_active = 1;

		if (!$client->find(true)) {
			return [];
		}

		$allowedScopes = $client->getScopesArray();
		$finalScopes = [];

		foreach ($scopes as $scope) {
			// Only allow scopes that the client is authorized for
			if (in_array($scope->getIdentifier(), $allowedScopes)) {
				$finalScopes[] = $scope;
			}
		}

		return $finalScopes;
	}
}

class UserRepository implements \League\OAuth2\Server\Repositories\UserRepositoryInterface {

	public function getUserEntityByUserCredentials($username, $password, $grantType, \League\OAuth2\Server\Entities\ClientEntityInterface $clientEntity): ?\League\OAuth2\Server\Entities\UserEntityInterface {
		// Use the existing Aspen authentication system
		require_once ROOT_DIR . '/CatalogFactory.php';

		global $library;
		$driversToTest = UserAccount::getAccountProfiles();

		foreach ($driversToTest as $driverName => $additionalInfo) {
			/** @var AccountProfile $tmpAccountProfile **/
			$tmpAccountProfile = $additionalInfo['accountProfile'];
			
			// Only allow login with the active library account profile
			if ($library->accountProfileId == $tmpAccountProfile->id) {
				try {
					$authN = AuthenticationFactory::initAuthentication($additionalInfo['authenticationMethod'], $additionalInfo);
				} catch (UnknownAuthenticationMethodException $e) {
					continue;
				}

				$parentAccount = null;
				$validatedViaSSO = false;
				$validatedUser = $authN->validateAccount($username, $password, $additionalInfo['accountProfile'], $parentAccount, $validatedViaSSO);
				
				if ($validatedUser && !($validatedUser instanceof AspenError)) {
					// Create OAuth2 user entity
					$user = new \League\OAuth2\Server\Entities\UserEntity();
					$user->setIdentifier($validatedUser->id);
					return $user;
				}
			}
		}

		return null;
	}
}
