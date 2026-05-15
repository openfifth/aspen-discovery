<?php
/** @noinspection SqlDialectInspection */

/** @noinspection PhpUnused */
function getUpdates26_06_00(): array {
	$now = time();

	return [
		/*'name' => [
			 'title' => '',
			 'description' => '',
			 'continueOnError' => false,
			 'sql' => [
				 ''
			 ]
		 ], //name*/

		//mark n

		//kirstien

		//kodi

		//yanjun

		//imani

		//galen

		//chloe

		//pedro

		//mark j

		//lucas
		'language_add_is_default' => [
			'title' => 'Add Default Language Flag',
			'description' => 'Adds an isDefault column to the languages table to allow admins to designate a default language for unauthenticated users. English is set as the initial default to preserve existing behavior.',
			'sql' => [
				'ALTER TABLE languages ADD COLUMN isDefault TINYINT(1) NOT NULL DEFAULT 0',
				"UPDATE languages SET isDefault = 1 WHERE code = 'en' LIMIT 1",
			],
		], //language_add_is_default

		//tomas

		// stephen

		//other

	];
}
