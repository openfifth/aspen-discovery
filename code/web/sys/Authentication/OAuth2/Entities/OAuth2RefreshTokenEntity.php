<?php

use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\RefreshTokenTrait;

/**
 * OAuth2 Refresh Token Entity implementation
 */
class OAuth2RefreshTokenEntity implements RefreshTokenEntityInterface {
	use EntityTrait, RefreshTokenTrait;
}
