<?php

function getUpdates25_07_00(): array {
	$curTime = time();
	return [
		/*'name' => [
			 'title' => '',
			 'description' => '',
			 'continueOnError' => false,
			 'sql' => [
				 ''
			 ]
		 ], //name*/

		//mark - Grove

		//katherine - Grove

		//kirstien - Grove

		//kodi - Grove

		//Mark - Grove

		// Myranda - Grove

		//Yanjun Li - ByWater

		// Leo Stoyanov - BWS

		// Laura Escamilla - ByWater Solutions

		//alexander - Open Fifth
		'add_grapes_templates_to_db' => [
			'title' => 'Add Grapes Temaplates To DB',
			'description' => 'Add Grapes templates to db',
			'sql' => [
				'addTemplateFromJson'
			]
		], //add_grapes_templates_to_db

		//chloe - Open Fifth

		//James Staub - Nashville Public Library

		//Lucas Montoya - Theke Solutions

		//other

	];
}

 function addTemplateFromJson(&$update) {
	require_once ROOT_DIR . '/sys/WebBuilder/GrapesTemplate.php';

	$jsonFile = './web_builder/templates.json';
	if(file_exists($jsonFile)){
		$jsonData = file_get_contents($jsonFile);
		$jsonDecoded = json_decode($jsonData, true);
		$templates = $jsonDecoded['templates'];

		foreach($templates as $preMadeTemplate) {
			$template = new GrapesTemplate();
			$template->addTemplate($preMadeTemplate['templateName'], $preMadeTemplate['templateContent'], $preMadeTemplate['htmlData'] ?? '', $preMadeTemplate['cssData'] ?? '');
		}
		$update['success'] = true;
	}
}
