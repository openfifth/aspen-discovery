<?php

require_once ROOT_DIR . '/Action.php';
require_once ROOT_DIR . '/sys/AspenMobile/Setting.php';

class AspenMobile_AssetLinks extends Action {

	function launch() {
		header('Content-type: application/json');
		header('Cache-Control: no-cache, must-revalidate'); // HTTP/1.1
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		$output = json_encode($this->build_links());
		echo $output;
	}

	function build_links()
	{
		$setting = new AspenMobileSetting();
		$success = true;
		if(!$setting->find(true))
		{
			return ['success' => false,'message'=>'settings not found'];
		}
		return [[
			"relation" => ["delegate_permission/common.handle_all_urls"],
			"target" => [
				"namespace"=>  "android_app",
				"package_name" => $setting->manifestID,
				"sha256_cert_fingerprints" => [$setting->sha256CertFingerprint]
			]

		]];
	}

	function getBreadcrumbs(): array {
		return [];
	}
}
?>