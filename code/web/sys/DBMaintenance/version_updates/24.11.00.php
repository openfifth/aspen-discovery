<?php

function getUpdates24_11_00(): array {
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
		'library_shareit_settings' => [
			'title' => 'Library SHAREit Settings',
			'description' => 'Add a new library SHAREit settings',
			'continueOnError' => false,
			'sql' => [
				"ALTER TABLE library ADD COLUMN repeatInShareIt TINYINT(1) DEFAULT 0",
				"ALTER TABLE library ADD COLUMN shareItCid VARCHAR(80) DEFAULT ''",
				"ALTER TABLE library ADD COLUMN shareItLid VARCHAR(80) DEFAULT ''",
			]
		], //library_shareit_settings
		'location_shareit_settings' => [
			'title' => 'Location SHAREit Settings',
			'description' => 'Add a new location SHAREit settings',
			'continueOnError' => false,
			'sql' => [
				"ALTER TABLE location ADD COLUMN repeatInShareIt TINYINT(1) DEFAULT 0",
			]
		], //location_shareit_settings
		'library_shareit_credentials' => [
			'title' => 'Library SHAREit Credentials',
			'description' => 'Add library SHAREit login credentials',
			'continueOnError' => false,
			'sql' => [
				"ALTER TABLE library ADD COLUMN shareItUsername VARCHAR(80) DEFAULT ''",
				"ALTER TABLE library ADD COLUMN shareItPassword VARCHAR(255) DEFAULT ''",
			]
		], //library_shareit_credentials

		//katherine - ByWater

		//kirstien - ByWater

		//kodi - ByWater

		//alexander - PTFS-Europe
		'add_regular_expression_for_iTypes_to_treat_as_eContent' => [
			'title' => 'Add Regular Expression For iTypes To Treat As eContent',
			'description' => 'Add treatItemsAsEcontent to give control over iTypes to be treated as eContent',
			'sql' => [
				"ALTER TABLE indexing_profiles ADD COLUMN treatItemsAsEcontent VARCHAR(512) DEFAULT 'ebook|ebk|eaudio|evideo|online|oneclick|eaudiobook|download'",
			],
		], //add_treatItemsAsEcontent_field

		//chloe - PTFS-Europe
		'ebsco_passwords_are_stored_as_hash' => [
			'title' => 'EBSCO Passwords Are Stored As Hash',
			'description' => 'allow for longer strings so passwords can be stored as hashed values',
			'continueOnError' => false,
			'sql' => [
				"ALTER TABLE ebsco_eds_settings MODIFY COLUMN edsApiPassword VARCHAR(255)",
				"ALTER TABLE ebscohost_settings MODIFY COLUMN profilePwd VARCHAR(255)"
			]
		] // ebsco_passwords_are_stored_as_hash

		//pedro - PTFS-Europe

		//James Staub - Nashville Public Library

		//Jeremy Eden - Howell Carnegie District Library

		//other

	];
}