<?php

require_once ROOT_DIR . '/sys/DB/DataObject.php';

class OAuth2Client extends DataObject {
	public $__table = 'oauth2_clients';
	protected $id;
	protected $name;
	protected $client_id;
	protected $client_secret;
	protected $scopes;
	protected $redirect_uri;
	protected $is_active;
	protected $created_by;
	protected $created_date;
	protected $last_modified;

	static function getObjectStructure($context = ''): array {
		return [
			'id' => [
				'property' => 'id',
				'type' => 'label',
				'label' => 'Id',
				'description' => 'The unique id within the database',
			],
			'name' => [
				'property' => 'name',
				'type' => 'text',
				'label' => 'Client Name',
				'description' => 'A descriptive name for this OAuth2 client',
				'maxLength' => 255,
				'required' => true,
			],
			'client_id' => [
				'property' => 'client_id',
				'type' => 'text',
				'label' => 'Client ID',
				'description' => 'The OAuth2 client identifier',
				'maxLength' => 255,
				'required' => true,
			],
			'client_secret' => [
				'property' => 'client_secret',
				'type' => 'password',
				'label' => 'Client Secret',
				'description' => 'The OAuth2 client secret',
				'maxLength' => 255,
				'hideInLists' => true,
			],
			'scopes' => [
				'property' => 'scopes',
				'type' => 'multiSelect',
				'label' => 'Allowed Scopes',
				'description' => 'The scopes this client is allowed to request',
				'listStyle' => 'checkboxSimple',
				'values' => [
					'user:read' => 'User Read Access',
					'user:write' => 'User Write Access',
					'catalog:read' => 'Catalog Read Access',
					'catalog:write' => 'Catalog Write Access',
					'admin:read' => 'Admin Read Access',
					'admin:write' => 'Admin Write Access',
				],
			],
			'redirect_uri' => [
				'property' => 'redirect_uri',
				'type' => 'url',
				'label' => 'Redirect URI',
				'description' => 'Valid redirect URI for this client',
				'maxLength' => 2000,
			],
			'is_active' => [
				'property' => 'is_active',
				'type' => 'checkbox',
				'label' => 'Active',
				'description' => 'Whether this client is currently active',
				'default' => true,
			],
			'created_by' => [
				'property' => 'created_by',
				'type' => 'label',
				'label' => 'Created By',
				'description' => 'The user who created this client',
			],
			'created_date' => [
				'property' => 'created_date',
				'type' => 'timestamp',
				'label' => 'Created',
				'description' => 'When this client was created',
			],
			'last_modified' => [
				'property' => 'last_modified',
				'type' => 'timestamp',
				'label' => 'Last Modified',
				'description' => 'When this client was last modified',
			],
		];
	}

	function getNumericColumnNames(): array {
		return ['id', 'created_by'];
	}

	function getEncryptedFieldNames(): array {
		return ['client_secret'];
	}

	public function insert($context = '') {
		$this->created_date = date('Y-m-d H:i:s');
		$this->last_modified = date('Y-m-d H:i:s');
		if (empty($this->created_by)) {
			global $user;
			if (!empty($user)) {
				$this->created_by = $user->id;
			}
		}
		if (empty($this->client_id)) {
			$this->client_id = $this->generateClientId();
		}
		if (empty($this->client_secret)) {
			$this->client_secret = $this->generateClientSecret();
		}
		return parent::insert($context);
	}

	public function update($context = '') {
		$this->last_modified = date('Y-m-d H:i:s');
		return parent::update($context);
	}

	private function generateClientId(): string {
		return 'aspen_' . bin2hex(random_bytes(16));
	}

	private function generateClientSecret(): string {
		return bin2hex(random_bytes(32));
	}

	public function getScopesArray(): array {
		if (empty($this->scopes)) {
			return [];
		}
		return explode(',', $this->scopes);
	}

	public function setScopesFromArray(array $scopes): void {
		$this->scopes = implode(',', $scopes);
	}

	public function isValidScope(string $scope): bool {
		$allowedScopes = $this->getScopesArray();
		return in_array($scope, $allowedScopes);
	}
}
