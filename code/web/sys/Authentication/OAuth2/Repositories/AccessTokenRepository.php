<?php

use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;

require_once ROOT_DIR . '/services/Authentication/OAuth2Server/_toolkit_loader.php';
require_once ROOT_DIR . '/sys/Authentication/OAuth2/Tokens/OAuth2AccessToken.php';
require_once ROOT_DIR . '/sys/Authentication/OAuth2/Entities/OAuth2AccessTokenEntity.php';

class AccessTokenRepository implements AccessTokenRepositoryInterface {

	public function getNewToken(ClientEntityInterface $clientEntity, array $scopes, $userIdentifier = null): AccessTokenEntityInterface {
		$accessToken = new OAuth2AccessTokenEntity();
		$accessToken->setClient($clientEntity);

		foreach ($scopes as $scope) {
			$accessToken->addScope($scope);
		}

		$accessToken->setUserIdentifier($userIdentifier ?? '');

		return $accessToken;
	}

	public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity): void {
		$token = new OAuth2AccessToken();
		$token->token_id = $accessTokenEntity->getClient()->getIdentifier();
		$token->user_id = $accessTokenEntity->getUserIdentifier();
		$token->client_id = $accessTokenEntity->getClient()->getIdentifier();

		$scopes = [];
		foreach ($accessTokenEntity->getScopes() as $scope) {
			$scopes[] = $scope->getIdentifier();
		}
		$token->scopes = implode(',', $scopes);
		$token->expires_at = $accessTokenEntity->getExpiryDateTime()->format('Y-m-d H:i:s');
		$token->revoked = 0;

		$token->insert();
	}

	public function revokeAccessToken($tokenId): void {
		$token = new OAuth2AccessToken();
		$token->token_id = $tokenId;
		if ($token->find(true)) {
			$token->revoked = 1;
			$token->update();
		}
	}

	public function isAccessTokenRevoked($tokenId): bool {
		$token = new OAuth2AccessToken();
		$token->token_id = $tokenId;
		if ($token->find(true)) {
			return $token->isRevoked();
		}
		return false;
	}
}