<?php
require_once ROOT_DIR . '/services/MyAccount/MyAccount.php';

class MyAccount_CacheInspector extends MyAccount {
	function launch() : void {
		global $interface;
		$user = UserAccount::getLoggedInUser();
		require_once ROOT_DIR . '/sys/User/AccountSummary.php';
		$summary = new AccountSummary();
		$summary->userId = $user->id;
		$summary->orderBy('source');
		$accountSummaries = $summary->fetchAll();
		$interface->assign('accountSummaries', $accountSummaries);

		$this->display('cacheInspector.tpl', 'Cache Inspector', '');
	}

	function getBreadcrumbs(): array {
		$breadcrumbs = [];
		$breadcrumbs[] = new Breadcrumb('/MyAccount/Home', 'Your Account');
		$breadcrumbs[] = new Breadcrumb('', 'Cache Inspector');
		return $breadcrumbs;
	}
}