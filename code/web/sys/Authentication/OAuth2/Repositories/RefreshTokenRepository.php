<?php

use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;

require_once ROOT_DIR . '/services/Authentication/OAuth2Server/_toolkit_loader.php';
require_once ROOT_DIR . '/sys/Authentication/OAuth2/Tokens/OAuth2RefreshToken.php';
require_once ROOT_DIR . '/sys/Authentication/OAuth2/Entities/OAuth2RefreshTokenEntity.php';

class RefreshTokenRepository implements RefreshTokenRepositoryInterface {

	public function getNewRefreshToken(): RefreshTokenEntityInterface {
		return new OAuth2RefreshTokenEntity();
	}

	public function persistNewRefreshToken(RefreshTokenEntityInterface $refreshTokenEntity): void {
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
		return true;
	}
}
