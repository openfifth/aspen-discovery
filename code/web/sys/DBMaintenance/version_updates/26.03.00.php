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
			'title' => 'Theme - Add Success button customization',
			'description' => 'In Themes, libraries can now customize the Bootstrap Success button.',
			'continueOnError' => true,
			'sql' => [
				"ALTER TABLE themes ADD COLUMN successButtonBackgroundColor char(7) DEFAULT '#5cb85c'",
				"ALTER TABLE themes ADD COLUMN successButtonBackgroundColorDefault tinyint(1) DEFAULT 1",
				"ALTER TABLE themes ADD COLUMN successButtonForegroundColor char(7) DEFAULT '#000000'",
				"ALTER TABLE themes ADD COLUMN successButtonForegroundColorDefault tinyint(1) DEFAULT 1",
				"ALTER TABLE themes ADD COLUMN successButtonBorderColor char(7) DEFAULT '#4cae4c'",
				"ALTER TABLE themes ADD COLUMN successButtonBorderColorDefault tinyint(1) DEFAULT 1",
				"ALTER TABLE themes ADD COLUMN successButtonHoverBackgroundColor char(7) DEFAULT '#449d44'",
				"ALTER TABLE themes ADD COLUMN successButtonHoverBackgroundColorDefault tinyint(1) DEFAULT 1",
				"ALTER TABLE themes ADD COLUMN successButtonHoverForegroundColor char(7) DEFAULT '#000000'",
				"ALTER TABLE themes ADD COLUMN successButtonHoverForegroundColorDefault tinyint(1) DEFAULT 1",
				"ALTER TABLE themes ADD COLUMN successButtonHoverBorderColor char(7) DEFAULT '#398439'",
				"ALTER TABLE themes ADD COLUMN successButtonHoverBorderColorDefault tinyint(1) DEFAULT 1",
			]
		],

		//other


	];
}
