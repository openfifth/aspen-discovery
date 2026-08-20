<?php

class PinResetService {
	static function emailPinResetToken(User $userToResetPin, PinResetToken $pinResetToken) : bool {
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
