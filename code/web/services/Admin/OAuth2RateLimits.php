<?php

require_once ROOT_DIR . '/services/Admin/ObjectEditor.php';
require_once ROOT_DIR . '/sys/OAuth2/OAuth2RateLimit.php';
require_once ROOT_DIR . '/sys/OAuth2/OAuth2RateLimiter.php';

class Admin_OAuth2RateLimits extends ObjectEditor {
	function getObjectType(): string {
		return 'OAuth2RateLimit';
	}

	function getToolName(): string {
		return 'OAuth2RateLimits';
	}

	function getModule(): string {
		return 'Admin';
	}

	function getPageTitle(): string {
		return 'OAuth2 Rate Limits';
	}

	function getAllObjects($page, $recordsPerPage): array {
		$object = new OAuth2RateLimit();
		$object->orderBy('last_request DESC');
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
		return 'last_request desc';
	}

	function getObjectStructure($context = ''): array {
		return OAuth2RateLimit::getObjectStructure($context);
	}

	function getPrimaryKeyColumn(): string {
		return 'id';
	}

	function getIdKeyColumn(): string {
		return 'id';
	}

	function canAddNew(): bool {
		return false; // Rate limit records are auto-generated
	}

	function canEdit(): bool {
		return UserAccount::userHasPermission('Administer OAuth2');
	}

	function canDelete(): bool {
		return UserAccount::userHasPermission('Administer OAuth2');
	}

	function customListActions(): array {
		$actions = [];

		if (UserAccount::userHasPermission('Administer OAuth2')) {
			$actions[] = [
				'label' => 'View Rate Limit Statistics',
				'action' => 'viewStatistics',
				'onClick' => 'return AspenDiscovery.Admin.viewRateLimitStats();'
			];

			$actions[] = [
				'label' => 'Configure Rate Limits',
				'action' => 'configureRateLimits',
				'onClick' => 'return AspenDiscovery.Admin.configureRateLimits();'
			];

			$actions[] = [
				'label' => 'Cleanup Expired Records',
				'action' => 'cleanupExpired',
				'onClick' => 'return AspenDiscovery.Admin.cleanupExpiredRateLimits();'
			];
		}

		return $actions;
	}

	function getBreadcrumbs(): array {
		$breadcrumbs = [];
		$breadcrumbs[] = new Breadcrumb('/Admin/Home', 'Administration Home');
		$breadcrumbs[] = new Breadcrumb('/Admin/Home#system_admin', 'System Administration');
		$breadcrumbs[] = new Breadcrumb('', 'OAuth2 Rate Limits');
		return $breadcrumbs;
	}

	function getActiveAdminSection(): string {
		return 'system_admin';
	}

	protected function getDefaultRecordsPerPage() {
		return 50;
	}

	function viewStatistics() {
		global $interface;

		if (!UserAccount::userHasPermission('Administer OAuth2')) {
			$this->display('../../interface/themes/responsive/Admin/invalidPermissions.tpl', 'Invalid Permissions', '');
			return;
		}

		$statistics = OAuth2RateLimiter::getStatistics();
		$rateLimitConfig = OAuth2RateLimiter::getRateLimitConfig();

		$interface->assign('statistics', $statistics);
		$interface->assign('rateLimitConfig', $rateLimitConfig);

		$this->display('oauth2_rate_limit_stats.tpl', 'OAuth2 Rate Limit Statistics', '');
	}

	function cleanupExpired() {
		global $interface;

		if (!UserAccount::userHasPermission('Administer OAuth2')) {
			$this->display('../../interface/themes/responsive/Admin/invalidPermissions.tpl', 'Invalid Permissions', '');
			return;
		}

		$deletedCount = OAuth2RateLimiter::cleanupExpiredRecords();

		$interface->assign('deletedCount', $deletedCount);
		$interface->assign('message', "Cleaned up $deletedCount expired rate limit records.");

		$this->display('oauth2_cleanup_result.tpl', 'Rate Limit Cleanup Complete', '');
	}
}
