<?php

require_once ROOT_DIR . '/services/Authentication/OAuth2Server/_toolkit_loader.php';
require_once ROOT_DIR . '/sys/OAuth2/OAuth2Tokens.php';

class AccessTokenRepository implements \League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface {

	public function getNewToken(\League\OAuth2\Server\Entities\ClientEntityInterface $clientEntity, array $scopes, $userIdentifier = null): \League\OAuth2\Server\Entities\AccessTokenEntityInterface {
		$accessToken = new \League\OAuth2\Server\Entities\AccessTokenEntity();
		$accessToken->setClient($clientEntity);

		foreach ($scopes as $scope) {
			$accessToken->addScope($scope);
		}

		$accessToken->setUserIdentifier($userIdentifier);

		return $accessToken;
	}

	public function persistNewAccessToken(\League\OAuth2\Server\Entities\AccessTokenEntityInterface $accessTokenEntity): void {
		$token = new OAuth2AccessToken();
		$token->token_id = $accessTokenEntity->getIdentifier();
		$token->user_id = $accessTokenEntity->getUserIdentifier();
		$token->client_id = $accessTokenEntity->getClient()->getIdentifier();
		
		$scopes = [];
		foreach ($accessTokenEntity->getScopes() as $scope) {
			$scopes[] = $scope->getIdentifier();
		}
		$token->setScopesFromArray($scopes);
		
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
		return true; // If token doesn't exist, consider it revoked
	}
}

class AuthCodeRepository implements \League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface {

	public function getNewAuthCode(): \League\OAuth2\Server\Entities\AuthCodeEntityInterface {
		return new \League\OAuth2\Server\Entities\AuthCodeEntity();
	}

	public function persistNewAuthCode(\League\OAuth2\Server\Entities\AuthCodeEntityInterface $authCodeEntity): void {
		$authCode = new OAuth2AuthCode();
		$authCode->code_id = $authCodeEntity->getIdentifier();
		$authCode->user_id = $authCodeEntity->getUserIdentifier();
		$authCode->client_id = $authCodeEntity->getClient()->getIdentifier();
		
		$scopes = [];
		foreach ($authCodeEntity->getScopes() as $scope) {
			$scopes[] = $scope->getIdentifier();
		}
		$authCode->setScopesFromArray($scopes);
		
		$authCode->redirect_uri = $authCodeEntity->getRedirectUri();
		$authCode->code_challenge = $authCodeEntity->getCodeChallenge();
		$authCode->code_challenge_method = $authCodeEntity->getCodeChallengeMethod();
		$authCode->expires_at = $authCodeEntity->getExpiryDateTime()->format('Y-m-d H:i:s');
		$authCode->revoked = 0;

		$authCode->insert();
	}

	public function revokeAuthCode($codeId): void {
		$authCode = new OAuth2AuthCode();
		$authCode->code_id = $codeId;
		if ($authCode->find(true)) {
			$authCode->revoked = 1;
			$authCode->update();
		}
	}

	public function isAuthCodeRevoked($codeId): bool {
		$authCode = new OAuth2AuthCode();
		$authCode->code_id = $codeId;
		if ($authCode->find(true)) {
			return $authCode->isRevoked();
		}
		return true; // If code doesn't exist, consider it revoked
	}
}

class RefreshTokenRepository implements \League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface {

	public function getNewRefreshToken(): \League\OAuth2\Server\Entities\RefreshTokenEntityInterface {
		return new \League\OAuth2\Server\Entities\RefreshTokenEntity();
	}

	public function persistNewRefreshToken(\League\OAuth2\Server\Entities\RefreshTokenEntityInterface $refreshTokenEntity): void {
		$refreshToken = new OAuth2RefreshToken();
		$refreshToken->token_id = $refreshTokenEntity->getIdentifier();
		$refreshToken->access_token_id = $refreshTokenEntity->getAccessToken()->getIdentifier();
		$refreshToken->expires_at = $refreshTokenEntity->getExpiryDateTime()->format('Y-m-d H:i:s');
		$refreshToken->revoked = 0;

		$refreshToken->insert();
	}

	public function revokeRefreshToken($tokenId): void {
		$refreshToken = new OAuth2RefreshToken();
		$refreshToken->token_id = $tokenId;
		if ($refreshToken->find(true)) {
			$refreshToken->revoked = 1;
			$refreshToken->update();
		}
	}

	public function isRefreshTokenRevoked($tokenId): bool {
		$refreshToken = new OAuth2RefreshToken();
		$refreshToken->token_id = $tokenId;
		if ($refreshToken->find(true)) {
			return $refreshToken->isRevoked();
		}
		return true; // If token doesn't exist, consider it revoked
	}
}
