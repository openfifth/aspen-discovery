<?php

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\Traits\ClientTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;

/**
 * OAuth2 Client Entity implementation
 */
class OAuth2ClientEntity implements ClientEntityInterface {
	use EntityTrait, ClientTrait;

	public function setName($name): void {
		$this->name = $name;
	}

	public function setRedirectUri($uri): void {
		$this->redirectUri = $uri;
	}

	public function setIsConfidential($isConfidential = true): void {
		$this->isConfidential = $isConfidential;
	}
}
