<?php

class PinResetService {
	public static function getIdentifier(array $request) : string {
		foreach ([
			'reset_username',
			'username',
		] as $identifierField) {
			$identifier = self::getTrimmedRequestString($request, $identifierField);
			if (!empty($identifier)) {
				return $identifier;
			}
		}
		return '';
	}

	public static function getTrimmedRequestString(array $request, string $field) : string {
		$value = $request[$field] ?? '';
		if (!is_string($value)) {
			return '';
		}
		return trim($value);
	}

	private static function findUserToResetPin(array $accountProfileInfo, string $identifier, ?User $knownUser) : ?User {
		global $library;
		$accountProfile = $accountProfileInfo['accountProfile'];

		if ($knownUser != null && $knownUser->source == $accountProfile->name) {
			return $knownUser;
		}

		//Check the ILS we are connected to
		if ($library->accountProfileId == $accountProfile->id) {
			return self::findIlsUserForBarcode($accountProfileInfo, $identifier);
		}

		//Check anything we do database authentication for
		if ($accountProfile->authenticationMethod != 'db') {
			return null;
		}
		$userToResetPin = UserAccount::findNewAspenUser('username', $identifier, $accountProfile->name);
		return $userToResetPin === false ? null : $userToResetPin;
	}

	public static function sendPasswordResetEmail(array $accountProfileInfo, string $identifier, ?User $knownUser = null) : array {
		$userToResetPin = self::findUserToResetPin($accountProfileInfo, $identifier, $knownUser);
		if ($userToResetPin == null) {
			return self::getErrorResult("Could not find a patron with that barcode, please contact the library.", false);
		}
		if (empty($userToResetPin->email)) {
			return self::getErrorResult("That account does not have an email associated with it, please contact the library.", true);
		}

		require_once ROOT_DIR . '/sys/Account/PinResetToken.php';
		$pinResetToken = new PinResetToken();
		$pinResetToken->userId = $userToResetPin->id;
		$pinResetToken->generateToken();
		$pinResetToken->dateIssued = time();
		if (!$pinResetToken->insert()) {
			return self::getErrorResult("Could not generate PIN reset token.", true);
		}

		if (!self::emailPinResetToken($userToResetPin, $pinResetToken)) {
			return self::getErrorResult("The email with your PIN reset link could not be sent, please contact the library.", true);
		}

		return [
			'success' => true,
			'foundPatron' => true,
			'message' => translate([
				'text' => "The email with your PIN reset link was sent. Please click on the link within that email or enter the code below.",
				'isPublicFacing' => true,
			]),
		];
	}

	static function findIlsUserForBarcode(array $accountProfileInfo, string $barcode) : ?User {
		$accountProfile = $accountProfileInfo['accountProfile'];
		$userToResetPin = new User();
		$userToResetPin->source = $accountProfile->name;
		$userToResetPin->ils_barcode = $barcode;
		if ($userToResetPin->find(true)) {
			return $userToResetPin;
		}

		require_once ROOT_DIR . '/CatalogFactory.php';
		$catalogConnectionInstance = CatalogFactory::getCatalogConnectionInstance($accountProfileInfo['driver'], $accountProfile);
		$ilsUser = $catalogConnectionInstance->findNewUser($barcode, '');
		return $ilsUser instanceof User ? $ilsUser : null;
	}

	private static function getErrorResult(string $error, bool $foundPatron) : array {
		return [
			'success' => false,
			'foundPatron' => $foundPatron,
			'error' => translate([
				'text' => $error,
				'isPublicFacing' => true,
			]),
		];
	}

	private static function emailPinResetToken(User $userToResetPin, PinResetToken $pinResetToken) : bool {
		global $configArray;
		$resetUrl = $configArray['Site']['url'] . '/MyAccount/CompletePinReset?token=' . $pinResetToken->token;
		$subject = translate([
			'text' => 'Reset PIN',
			'isPublicFacing' => true,
		]);

		$body = translate([
			'text' => 'Hi %1%,',
			1 => $userToResetPin->firstname,
			'isPublicFacing' => true,
		]);
		$body .= "\r\n" . translate([
				'text' => 'It looks like you forgot your PIN. Click on the link or copy/paste the URL below into a browser to reset your password. This link will only work for 60 minutes, after that you’ll have to request a new link.',
				'isPublicFacing' => true,
			]);
		$body .= "\r\n\r\n" . $resetUrl;
		$body .= "\r\n" . translate([
				'text' => 'You can also paste the following code into the page where you generated the reset.',
				'isPublicFacing' => true,
			]);
		$body .= "\r\n\r\n" . $pinResetToken->token;

		/** @noinspection HtmlRequiredLangAttribute */
		$htmlBody = "<html><table><tr><td>" . translate([
				'text' => 'Hi %1%,',
				1 => $userToResetPin->firstname,
				'isPublicFacing' => true,
			]) . '<br/>';
		$htmlBody .= translate([
				'text' => 'It looks like you forgot your PIN. Click on the button or copy/paste the URL below into a browser to reset your password. This link will only work for 60 minutes, after that you’ll have to request a new link',
				'isPublicFacing' => true,
				1 => $userToResetPin->firstname,
			]) . "</td>";
		$htmlBody .= "<tr><td style='text-align: center'><a href='{$resetUrl}'>" . translate([
				'text' => 'CREATE NEW PIN',
				'isPublicFacing' => true,
			]) . '</a></td></tr>';
		$htmlBody .= '<tr><td></td></tr>';
		$htmlBody .= "<tr><td style='text-align: center'>" . translate([
				'text' => 'Reset Token',
				'isPublicFacing' => true,
			]) . '</tr>';
		$htmlBody .= "<tr><td style='text-align: center'>" . $pinResetToken->token . '</td></tr>';
		$htmlBody .= '</table></html>';

		require_once ROOT_DIR . '/sys/Email/Mailer.php';
		$mailer = new Mailer();
		return $mailer->send($userToResetPin->email, $subject, $body, null, $htmlBody);
	}
}
