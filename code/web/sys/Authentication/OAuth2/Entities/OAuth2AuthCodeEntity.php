<?php

use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Entities\Traits\AuthCodeTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\TokenEntityTrait;

/**
 * OAuth2 Authorization Code Entity
 * Implements the League OAuth2 Server AuthCodeEntityInterface
 */
class OAuth2AuthCodeEntity implements AuthCodeEntityInterface {
	use EntityTrait, TokenEntityTrait, AuthCodeTrait;

	protected ?string $codeChallenge;
	protected ?string $codeChallengeMethod;

	/**
	 * Get the code challenge (PKCE)
	 */
	public function getCodeChallenge(): ?string {
		return $this->codeChallenge;
	}

	/**
	 * Set the code challenge (PKCE)
	 */
	public function setCodeChallenge(?string $codeChallenge): void {
		$this->codeChallenge = $codeChallenge;
	}

	/**
	 * Get the code challenge method (PKCE)
	 */
	public function getCodeChallengeMethod(): ?string {
		return $this->codeChallengeMethod;
	}

	/**
	 * Set the code challenge method (PKCE)
	 */
	public function setCodeChallengeMethod(?string $codeChallengeMethod): void {
		$this->codeChallengeMethod = $codeChallengeMethod;
	}
}
