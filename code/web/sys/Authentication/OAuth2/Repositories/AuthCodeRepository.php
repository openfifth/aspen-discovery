<?php

use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;

require_once ROOT_DIR . '/services/Authentication/OAuth2Server/_toolkit_loader.php';
require_once ROOT_DIR . '/sys/Authentication/OAuth2/Entities/OAuth2AuthCodeEntity.php';
require_once ROOT_DIR . '/sys/Authentication/OAuth2/Tokens/OAuth2AuthCode.php';

class AuthCodeRepository implements AuthCodeRepositoryInterface {

	public function getNewAuthCode(): AuthCodeEntityInterface {
		return new OAuth2AuthCodeEntity();
	}

	public function persistNewAuthCode(AuthCodeEntityInterface $authCodeEntity): void {
		$authCode = new OAuth2AuthCode();
		$authCode->code_id = $authCodeEntity->getIdentifier();
		$authCode->user_id = $authCodeEntity->getUserIdentifier();
		$authCode->client_id = $authCodeEntity->getClient()->getIdentifier();

		$scopes = [];
		foreach ($authCodeEntity->getScopes() as $scope) {
			$scopes[] = $scope->getIdentifier();
		}
		$authCode->scopes = implode(' ', $scopes);
		$authCode->redirect_uri = $authCodeEntity->getRedirectUri();

		// Handle PKCE (Proof Key for Code Exchange) if supported
		if (method_exists($authCodeEntity, 'getCodeChallenge')) {
			$authCode->code_challenge = $authCodeEntity->getCodeChallenge();
		} else {
			$authCode->code_challenge = null;
		}

		if (method_exists($authCodeEntity, 'getCodeChallengeMethod')) {
			$authCode->code_challenge_method = $authCodeEntity->getCodeChallengeMethod();
		} else {
			$authCode->code_challenge = null;
		}

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
		return true;
	}
}
