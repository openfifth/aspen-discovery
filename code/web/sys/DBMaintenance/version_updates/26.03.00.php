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

		//galen

		//alexander

		//chloe

		//mark j

		//lucas


		//tomas

		// stephen

		'add_success_button_to_theme' => [
			'title' => 'Add Success button to theme options',
			'description' => 'Add configuration to the Theme page for the Bootstrap Success button',
			'continueOnError' => true,
			'sql' => [
				//"ALTER TABLE Theme ADD COLUMN successButtonBackgroundColor varchar(7) DEFAULT #",
			]
		],

		//other


	];
}
