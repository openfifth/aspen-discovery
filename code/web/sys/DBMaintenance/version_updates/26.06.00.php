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
		'addForceReadingHistoryOptIn' => [
			'title' => 'Add option force patrons to opt-in to reading history',
			'description' => 'Add option to ignore Koha/ILS settings and force new patrons to opt-in to reading history',
			'continueOnError' => false,
			'sql' => [
				'ALTER TABLE library ADD COLUMN forceReadingHistoryOptIn TINYINT(1) DEFAULT 0',
			]
		],
		//addForceReadingHistoryOptIn

		//kodi

		//yanjun

		//imani

		//galen

		//chloe

		//pedro

		//mark j

		//lucas

		//tomas

		// stephen

		//other

	];
}
