<?php

require_once ROOT_DIR . '/Action.php';
require_once ROOT_DIR . '/sys/AspenMobile/Setting.php';

class AspenMobile_Manifest extends Action {

	function launch() {
		header('Content-type: application/json');
		header('Cache-Control: no-cache, must-revalidate'); // HTTP/1.1
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		$output = json_encode($this->build_manifest());
		http_response_code(200);
		echo $output;
	}

	function build_manifest()
	{
		$setting = new AspenMobileSetting();
		$success = true;
		//TODO we should return an error code instead of 200
		// if we have no settings
		if(!$setting->find(true))
		{
			return ['success' => false,'message'=>'settings not found'];
		}

		return [

			'id' => $setting->manifestID,
			'name' => 'Aspen Mobile',
			'short_name' => 'Aspen Mobile',
			'theme_color' => '#000000',
			'start_url' => $setting->startURL,
			'description' => 'testing this',
			'icons' => [
				[
					//'src' => '/API/SystemAPI?method=getLogoFile&themeId=1&type=appIcon&slug='.$setting->slug,
					'src' => 'https://placehold.co/512x512?text='.$setting->slug,
					'type' => 'image/png',
					'sizes' => '512x512',
					'purpose' => 'any'
				]
			],
			'orientation' => 'portrait',
			'display' => 'standalone',
			'categories' => [
				'books',
				'entertainment',
				'magazines'
			],
			'dir' => 'auto',
			'launch_handler' => [
				'client_mode' => ['navigate-existing', 'auto']
			]

		];
	}

	function getBreadcrumbs(): array {
		return [];
	}
}
?>