<?php

class AccountRenewalService {
	private function cacheKey(string $userId) : string {
		return 'account_renewal_data_' . $userId;
	}

	public function getRenewalInformation(User $user) : array {
		global $memCache;

		$key = $this->cacheKey($user->id);
		$cached = $memCache->get($key);
		if ($cached !== false) {
			return $cached;
		}

		$driver = $user->getCatalogDriver();
		$info = $driver->getAccountRenewalInformationForPatron($user->unique_ils_id);

		if ($driver->isRenewalInformationCacheable($info)) {
			$memCache->set($key, $info, strtotime('tomorrow') - time());
		}
		return $info;
	}

}
