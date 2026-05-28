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
		'allow_to_renew_ill_items' => [
			'title' => 'Allow Renewing ILL Items',
			'description' => 'Add allowToRenewILL to the library table to control whether patrons can renew ILL items.',
			'continueOnError' => false,
			'sql' => [
				'ALTER TABLE library ADD COLUMN allowToRenewILL TINYINT(1) DEFAULT 1'
			]
		], //allow_to_renew_ill_items



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
