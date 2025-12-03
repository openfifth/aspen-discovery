<?php

class eCARD extends Action {
	public function launch() : void {
		global $interface;

		require_once ROOT_DIR . '/sys/Enrichment/QuipuECardSetting.php';
		$quipuECardSettings = new QuipuECardSetting();
		if ($quipuECardSettings->find(true) && $quipuECardSettings->hasECard) {
			$interface->assign('eCardSettings', $quipuECardSettings);
		} else {
			$interface->assign('eCardSettings', null);
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

		$this->display('quipuECard.tpl', 'Register for a Library Card', '');
	}

	function getBreadcrumbs(): array {
		$breadcrumbs = [];
		$breadcrumbs[] = new Breadcrumb('', 'Register for a Library Card');
		return $breadcrumbs;
	}
}