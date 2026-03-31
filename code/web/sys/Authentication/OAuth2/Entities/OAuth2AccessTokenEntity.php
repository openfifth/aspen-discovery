<?php

use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\Traits\AccessTokenTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\TokenEntityTrait;

/**
 * OAuth2 Access Token Entity implementation
 */
class OAuth2AccessTokenEntity implements AccessTokenEntityInterface {
	use EntityTrait, TokenEntityTrait, AccessTokenTrait;
}
