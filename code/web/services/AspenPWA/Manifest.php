<?php

require_once ROOT_DIR . '/Action.php';
require_once ROOT_DIR . '/sys/AspenPWA/Setting.php';

class AspenPWA_Manifest extends Action {

	function launch() {
		header('Content-type: application/json');
		header('Cache-Control: no-cache, must-revalidate'); // HTTP/1.1
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		$output = json_encode($this->build_manifest());
		echo $output;
	}

	function build_manifest()
	{
		$setting = new AspenPWASetting();
		$success = true;
		//TODO we should return an error code instead of 200
		// if we have no settings
		if(!$setting->find(true))
		{
			http_response_code(404);
			return ['success' => false,'message'=>'settings not found'];
		}
		$theme = new Theme();
		$theme->id = $setting->themeId;
		$themeColor = '#000000'; //fallback value
		if($theme->find(true) && $theme->primaryForegroundColor)
		{
			$themeColor = $theme->primaryForegroundColor;	
		}

		http_response_code(200);
		return [

			'id' => $setting->manifestID,
			'name' => $setting->name,
			'short_name' => $setting->shortName,
			'theme_color' => $themeColor,
			'start_url' => $setting->startURL,
			'description' => $setting->description,
			'icons' => [
				[
					'src' => '/pwa-icon.png',
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