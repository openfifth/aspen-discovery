<?php

function getUpdates25_04_00(): array {
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

		'library_preferred_mail_settings' => [
			'title' => 'Library Preferred Mail Settings',
			'description' => 'Add field to store preferred mail settings for each library',
			'continueOnError' => false,
			'sql' => [
				"ALTER TABLE library ADD COLUMN preferredMailSettingsId VARCHAR(50) DEFAULT NULL",
			]
		], //library_preferred_mail_settings

	];
}
