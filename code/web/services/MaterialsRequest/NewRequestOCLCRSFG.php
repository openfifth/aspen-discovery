<?php
require_once ROOT_DIR . "/Action.php";

class MaterialsRequest_NewRequestOCLCRSFG extends Action {
	function launch() {
		global $interface;
		
		if (!UserAccount::isLoggedIn()) {
			header('Location: /MyAccount/Home?followupModule=MaterialsRequest&followupAction=NewRequestOCLCRSFG');
			exit;
		}

		require_once ROOT_DIR . '/sys/LibraryLocation/Library.php';
		$activeLibrary = Library::getActiveLibrary();
		if (empty($activeLibrary)) {
			$error = translate([
				'text' => "Unable to determine home library to place request from.",
				'isPublicFacing' => true,
			]);
			$interface->assign('error', $error);
			$this->display('oclc-rsfg-request.tpl', 'Materials Request');
			return;
		}
		
		require_once ROOT_DIR . '/sys/OCLCRSFG/OCLCRSFGSetting.php';
		$settings = new OCLCRSFGSetting();
		$settings->whereAdd("id={$activeLibrary->oclcRSFGSettingsId}");
		if (!$settings->find(true)) {
			$error = translate([
				'text' => "OCLC Resource Sharing For Groups Settings do not exist, please contact the library to make a request.",
				'isPublicFacing' => true,
			]);
			$interface->assign('error', $error);
			$this->display('oclc-rsfg-request.tpl', 'Materials Request');
			return;
		}
		
		if (isset($_REQUEST['submit'])) {			
			require_once ROOT_DIR . '/Drivers/OCLCRSFGDriver.php';
			$driver = new OCLCRSFGDriver();
			$results = $driver->submitRequest($settings, UserAccount::getActiveUserObj(), $_REQUEST, true);
			if ($results['success']) {
				header('Location: /MyAccount/Holds#interlibrary_loan');
				exit;
			} else {
				$error = $results['message'];
				$interface->assign('error', $error);
				$this->display('oclc-rsfg-request.tpl', 'Materials Request');
				return;
			}
		}

		require_once ROOT_DIR . '/sys/OCLCRSFG/OCLCRSFGForm.php';
		$form = new OCLCRSFGForm();
		$form->id = $activeLibrary->OCLCRSFGFormId;
		if ($form->find(true)) {
			$interface->assign('OCLCRSFGForm', $form);
			$formFields = $form->getFormFields(null);
			$interface->assign('structure', $formFields);
			$interface->assign('saveButtonText', 'Submit Request');
			$fieldsForm = $interface->fetch('DataObjectUtil/objectEditForm.tpl');
			$interface->assign('oclcRSFGForm', $fieldsForm);
		} else {
			$error = translate([
				'text' => "Unable to find the specified form.",
				'isPublicFacing' => true,
			]);
			$interface->assign('error', $error);
		}
		
		$this->display('oclc-rsfg-request.tpl', 'Materials Request');
	}

	function getBreadcrumbs(): array {
		$breadcrumbs = [];
		$breadcrumbs[] = new Breadcrumb('/MyAccount/Home', 'Your Account');
		$breadcrumbs[] = new Breadcrumb('', 'New Materials Request');
		return $breadcrumbs;
	}
}