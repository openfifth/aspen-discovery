<?php

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;

require_once ROOT_DIR . '/services/Authentication/OAuth2Server/_toolkit_loader.php';
require_once ROOT_DIR . '/sys/Authentication/OAuth2/Entities/OAuth2UserEntity.php';

class UserRepository implements UserRepositoryInterface {

	public function getUserEntityByUserCredentials($username, $password, $grantType, ClientEntityInterface $clientEntity): ?\League\OAuth2\Server\Entities\UserEntityInterface {
		require_once ROOT_DIR . '/CatalogFactory.php';

		global $library;
		$driversToTest = UserAccount::getAccountProfiles();

		foreach ($driversToTest as $driverName => $additionalInfo) {
			/** @var AccountProfile $tmpAccountProfile * */
			$tmpAccountProfile = $additionalInfo['accountProfile'];

			if ($library->accountProfileId == $tmpAccountProfile->id) {
				try {
					$authN = AuthenticationFactory::initAuthentication($additionalInfo['authenticationMethod'], $additionalInfo);
				} catch (UnknownAuthenticationMethodException $e) {
					continue;
				}

				$parentAccount = null;
				$validatedViaSSO = false;
				$validatedUser = $authN->validateAccount($username, $password, $additionalInfo['accountProfile'], $parentAccount, $validatedViaSSO);

				if ($validatedUser && !($validatedUser instanceof AspenError)) {
					$user = new OAuth2UserEntity();
					$user->setIdentifier($validatedUser->id);
					return $user;
				}
			}
		}

		return null;
	}
}

