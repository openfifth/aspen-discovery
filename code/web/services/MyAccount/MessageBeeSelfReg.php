<?php

class MessageBeeSelfReg extends Action {
	public function launch(): void {
		global $interface;
		global $library;

		$interface->assign('messageBeeSettings', null);
		require_once ROOT_DIR . '/sys/Enrichment/MessageBeeSetting.php';
		$messageBeeSettings = new MessageBeeSetting();
		if ($library->messageBeeSettingId > 0) {
			$messageBeeSettings->id = $library->messageBeeSettingId;
			if ($messageBeeSettings->find(true)) {
				$interface->assign('messageBeeSettings', $messageBeeSettings);
			}
		}
		global $library;
		global $activeLanguage;
		$languageCode = 'en';
		if (isset($activeLanguage) && !empty($activeLanguage->code)) {
			$languageCode = $activeLanguage->code;
		}
		$selfRegistrationFormMessage = $library->getTextBlockTranslation('selfRegistrationFormMessage', $languageCode);
		if (empty($selfRegistrationFormMessage)) {
			$selfRegistrationFormMessage = $library->selfRegistrationFormMessage;
		}
		$interface->assign('selfRegistrationFormMessage', $selfRegistrationFormMessage);

		$this->display('messageBeeSelfReg.tpl', 'Register for a Library Card', '');
	}

	function getBreadcrumbs(): array {
		$breadcrumbs = [];
		$breadcrumbs[] = new Breadcrumb('', 'Register for a Library Card');
		return $breadcrumbs;
	}
}