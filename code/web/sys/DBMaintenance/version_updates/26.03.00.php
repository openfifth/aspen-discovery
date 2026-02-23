<?php
/** @noinspection SqlDialectInspection */

/** @noinspection PhpUnused */
function getUpdates26_03_00(): array {
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
		'lida_general_settings_add_idefinite_hold_freeze' => [
			'title' => 'Add option in Aspen LiDA General Settings to allow indefinite hold freezes in the app',
			'description' => 'Add option in Aspen LiDA General Settings to allow indefinite hold freezes in the app',
			'sql' => [
				"ALTER TABLE aspen_lida_general_settings add COLUMN allowIndefiniteHoldFreezes TINYINT(1) DEFAULT 0"
			]
		],
		//galen

		//alexander

		//chloe

		//mark j

		//lucas


		//tomas

		//other


	];
}
