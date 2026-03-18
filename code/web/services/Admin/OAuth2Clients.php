<?php

require_once ROOT_DIR . '/services/Admin/ObjectEditor.php';
require_once ROOT_DIR . '/sys/OAuth2/OAuth2Client.php';

class Admin_OAuth2Clients extends ObjectEditor {
	function getObjectType(): string {
		return 'OAuth2Client';
	}

	function getToolName(): string {
		return 'OAuth2Clients';
	}

	function getModule(): string {
		return 'Admin';
	}

	function getPageTitle(): string {
		return 'OAuth2 Clients';
	}

	function getAllObjects($page, $recordsPerPage): array {
		$object = new OAuth2Client();
		$object->orderBy('name');
		$this->applyFilters($object);
		$object->limit(($page - 1) * $recordsPerPage, $recordsPerPage);
		$object->find();
		$objectList = [];
		while ($object->fetch()) {
			$objectList[$object->id] = clone $object;
		}
		return $objectList;
	}

	function getDefaultSort(): string {
		return 'name asc';
	}

	function getObjectStructure($context = ''): array {
		return OAuth2Client::getObjectStructure($context);
	}

	function getPrimaryKeyColumn(): string {
		return 'id';
	}

	function getIdKeyColumn(): string {
		return 'id';
	}

	function getAdditionalObjectActions($existingObject): array {
		$actions = [];

		if ($existingObject && isset($existingObject->id) && !empty($existingObject->id)) {
			$actions[] = [
				'text' => 'View Client Credentials',
				'onClick' => "return AspenDiscovery.Admin.showOAuth2ClientCredentials('{$existingObject->id}');",
			];

			if ($existingObject->is_active) {
				$actions[] = [
					'text' => 'Revoke Client Access',
					'onClick' => "return AspenDiscovery.Admin.revokeOAuth2Client('{$existingObject->id}');",
				];
			} else {
				$actions[] = [
					'text' => 'Reactivate Client',
					'onClick' => "return AspenDiscovery.Admin.reactivateOAuth2Client('{$existingObject->id}');",
				];
			}
		}

		return $actions;
	}

	function canAddNew(): bool {
		return UserAccount::userHasPermission('Administer OAuth2');
	}

	function canEdit(): bool {
		return UserAccount::userHasPermission('Administer OAuth2');
	}

	function canDelete(): bool {
		return UserAccount::userHasPermission('Administer OAuth2');
	}

	function customListActions(): array {
		$actions = [];

		$actions[] = [
			'label' => 'Generate New Client',
			'action' => 'generateNewClient',
			'onClick' => 'return AspenDiscovery.Admin.generateNewOAuth2Client();'
		];

		return $actions;
	}

	function generateNewClient() {
		if (!UserAccount::userHasPermission('Administer OAuth2')) {
			$this->display('../../interface/themes/responsive/Admin/invalidPermissions.tpl', 'Invalid Permissions', '');
			return;
		}

		$client = new OAuth2Client();
		// Pre-fill with some defaults
		$client->name = 'New OAuth2 Client';
		$client->scopes = 'user:read,catalog:read';
		$client->is_active = 1;
		
		// These will be auto-generated on insert
		$result = $client->insert();

		if ($result) {
			header('Location: /Admin/OAuth2Clients?objectAction=edit&id=' . $client->id);
		} else {
			global $interface;
			$interface->assign('error', 'Failed to create new client');
			$this->display('../../interface/themes/responsive/Admin/oauth2ClientError.tpl', 'Error Creating Client', '');
		}
	}

	protected function getDefaultRecordsPerPage() {
		return 25;
	}
}
