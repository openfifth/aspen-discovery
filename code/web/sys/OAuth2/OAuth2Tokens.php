<?php

require_once ROOT_DIR . '/sys/DB/DataObject.php';

class OAuth2AccessToken extends DataObject {
	public $__table = 'oauth2_access_tokens';
	protected $id;
	protected $token_id;
	protected $user_id;
	protected $client_id;
	protected $scopes;
	protected $revoked;
	protected $expires_at;
	protected $created_at;

	static function getObjectStructure($context = ''): array {
		return [
			'id' => [
				'property' => 'id',
				'type' => 'label',
				'label' => 'Id',
				'description' => 'The unique id within the database',
			],
			'token_id' => [
				'property' => 'token_id',
				'type' => 'text',
				'label' => 'Token ID',
				'description' => 'The unique token identifier',
				'maxLength' => 100,
			],
			'user_id' => [
				'property' => 'user_id',
				'type' => 'integer',
				'label' => 'User ID',
				'description' => 'The user this token belongs to',
			],
			'client_id' => [
				'property' => 'client_id',
				'type' => 'text',
				'label' => 'Client ID',
				'description' => 'The OAuth2 client identifier',
				'maxLength' => 255,
			],
			'scopes' => [
				'property' => 'scopes',
				'type' => 'text',
				'label' => 'Scopes',
				'description' => 'The scopes granted to this token',
			],
			'revoked' => [
				'property' => 'revoked',
				'type' => 'checkbox',
				'label' => 'Revoked',
				'description' => 'Whether this token has been revoked',
				'default' => false,
			],
			'expires_at' => [
				'property' => 'expires_at',
				'type' => 'timestamp',
				'label' => 'Expires At',
				'description' => 'When this token expires',
			],
			'created_at' => [
				'property' => 'created_at',
				'type' => 'timestamp',
				'label' => 'Created',
				'description' => 'When this token was created',
			],
		];
	}

	function getNumericColumnNames(): array {
		return ['id', 'user_id'];
	}

	public function insert($context = '') {
		$this->created_at = date('Y-m-d H:i:s');
		return parent::insert($context);
	}

	public function isExpired(): bool {
		return strtotime($this->expires_at) < time();
	}

	public function isRevoked(): bool {
		return $this->revoked == 1;
	}

	public function getScopesArray(): array {
		if (empty($this->scopes)) {
			return [];
		}
		return explode(' ', $this->scopes);
	}

	public function setScopesFromArray(array $scopes): void {
		$this->scopes = implode(' ', $scopes);
	}
}

class OAuth2AuthCode extends DataObject {
	public $__table = 'oauth2_auth_codes';
	protected $id;
	protected $code_id;
	protected $user_id;
	protected $client_id;
	protected $scopes;
	protected $redirect_uri;
	protected $code_challenge;
	protected $code_challenge_method;
	protected $revoked;
	protected $expires_at;
	protected $created_at;

	static function getObjectStructure($context = ''): array {
		return [
			'id' => [
				'property' => 'id',
				'type' => 'label',
				'label' => 'Id',
				'description' => 'The unique id within the database',
			],
			'code_id' => [
				'property' => 'code_id',
				'type' => 'text',
				'label' => 'Code ID',
				'description' => 'The unique authorization code identifier',
				'maxLength' => 100,
			],
			'user_id' => [
				'property' => 'user_id',
				'type' => 'integer',
				'label' => 'User ID',
				'description' => 'The user this code belongs to',
			],
			'client_id' => [
				'property' => 'client_id',
				'type' => 'text',
				'label' => 'Client ID',
				'description' => 'The OAuth2 client identifier',
				'maxLength' => 255,
			],
			'scopes' => [
				'property' => 'scopes',
				'type' => 'text',
				'label' => 'Scopes',
				'description' => 'The scopes granted to this code',
			],
			'redirect_uri' => [
				'property' => 'redirect_uri',
				'type' => 'url',
				'label' => 'Redirect URI',
				'description' => 'The redirect URI for this code',
				'maxLength' => 2000,
			],
			'code_challenge' => [
				'property' => 'code_challenge',
				'type' => 'text',
				'label' => 'Code Challenge',
				'description' => 'PKCE code challenge',
			],
			'code_challenge_method' => [
				'property' => 'code_challenge_method',
				'type' => 'text',
				'label' => 'Code Challenge Method',
				'description' => 'PKCE code challenge method',
				'maxLength' => 10,
			],
			'revoked' => [
				'property' => 'revoked',
				'type' => 'checkbox',
				'label' => 'Revoked',
				'description' => 'Whether this code has been revoked',
				'default' => false,
			],
			'expires_at' => [
				'property' => 'expires_at',
				'type' => 'timestamp',
				'label' => 'Expires At',
				'description' => 'When this code expires',
			],
			'created_at' => [
				'property' => 'created_at',
				'type' => 'timestamp',
				'label' => 'Created',
				'description' => 'When this code was created',
			],
		];
	}

	function getNumericColumnNames(): array {
		return ['id', 'user_id'];
	}

	public function insert($context = '') {
		$this->created_at = date('Y-m-d H:i:s');
		return parent::insert($context);
	}

	public function isExpired(): bool {
		return strtotime($this->expires_at) < time();
	}

	public function isRevoked(): bool {
		return $this->revoked == 1;
	}

	public function getScopesArray(): array {
		if (empty($this->scopes)) {
			return [];
		}
		return explode(' ', $this->scopes);
	}

	public function setScopesFromArray(array $scopes): void {
		$this->scopes = implode(' ', $scopes);
	}
}

class OAuth2RefreshToken extends DataObject {
	public $__table = 'oauth2_refresh_tokens';
	protected $id;
	protected $token_id;
	protected $access_token_id;
	protected $revoked;
	protected $expires_at;
	protected $created_at;

	static function getObjectStructure($context = ''): array {
		return [
			'id' => [
				'property' => 'id',
				'type' => 'label',
				'label' => 'Id',
				'description' => 'The unique id within the database',
			],
			'token_id' => [
				'property' => 'token_id',
				'type' => 'text',
				'label' => 'Token ID',
				'description' => 'The unique refresh token identifier',
				'maxLength' => 100,
			],
			'access_token_id' => [
				'property' => 'access_token_id',
				'type' => 'text',
				'label' => 'Access Token ID',
				'description' => 'The access token this refresh token belongs to',
				'maxLength' => 100,
			],
			'revoked' => [
				'property' => 'revoked',
				'type' => 'checkbox',
				'label' => 'Revoked',
				'description' => 'Whether this token has been revoked',
				'default' => false,
			],
			'expires_at' => [
				'property' => 'expires_at',
				'type' => 'timestamp',
				'label' => 'Expires At',
				'description' => 'When this token expires',
			],
			'created_at' => [
				'property' => 'created_at',
				'type' => 'timestamp',
				'label' => 'Created',
				'description' => 'When this token was created',
			],
		];
	}

	function getNumericColumnNames(): array {
		return ['id'];
	}

	public function insert($context = '') {
		$this->created_at = date('Y-m-d H:i:s');
		return parent::insert($context);
	}

	public function isExpired(): bool {
		return strtotime($this->expires_at) < time();
	}

	public function isRevoked(): bool {
		return $this->revoked == 1;
	}
}
