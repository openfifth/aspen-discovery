<?php

/** @noinspection PhpUnused */
function getUpdates25_11_00(): array {
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
		'library_allow_automatic_faceting' => [
			'title' => 'Library - Allow Automatic Faceting',
			'description' => 'Add a setting of whether applying automatic facets to search term is allowed.',
			'continueOnError' => false,
			'sql' => [
				'ALTER TABLE library add COLUMN allowAutomaticFaceting TINYINT DEFAULT 0'
			]
		], //library_allow_automatic_faceting
		'google_translate_api_key' => [
			'title' => 'Add Google Translate API Key',
			'description' => 'Add the ability to store a Google Translate API Key.',
			'continueOnError' => false,
			'sql' => [
				'ALTER TABLE google_api_settings ADD COLUMN googleTranslateKey varchar(60) DEFAULT NULL'
			]
		], //google_translate_api_key
		'translator_indicate_google_translations' => [
			'title' => 'Translator - Indicate Google Translations',
			'description' => 'Track which translations were loaded using Google Translate.',
			'continueOnError' => false,
			'sql' => [
				'ALTER TABLE translations ADD COLUMN googleTranslated TINYINT DEFAULT 0',
			]
		], //translator_indicate_google_translations

		//katherine - Grove

		//kirstien - Grove

		//kodi - Grove

		// Myranda - Grove

		//Yanjun Li - ByWater

		// Leo Stoyanov - BWS

		//alexander - Open Fifth
		'add_use_library_name_for_maps' => [
			'title' => 'Add Use Library Name For Maps',
			'description' => 'Allow libraries to use library name for google maps',
			'sql' => [
				"ALTER TABLE location ADD COLUMN useLocationNameForMaps TINYINT(1) DEFAULT 0",
			]
		], //add_use_library_name_for_maps

		//chloe - Open Fifth


		//Jacob - Open Fifth

		//Pedro - Open Fifth


		//James Staub - Nashville Public Library

		//Lucas Montoya - Theke Solutions
		'forceDebugLog' => [
			'title' => 'Enable Forced Logging of Debugging Information for WorldPay Payments',
			'description' => 'Enable to show debugging information about WorldPay payments regardless of whether the user IP is authorized or not',
			'sql' => [
				'ALTER TABLE worldpay_settings ADD COLUMN forceDebugLog TINYINT(1) DEFAULT 0',
			]
		], //enable_worldpay_debug_logging

		//other

		//Talpa Search

		// Brendan Lawlor

	];
}
