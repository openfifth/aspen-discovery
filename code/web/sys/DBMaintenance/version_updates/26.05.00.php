<?php
/** @noinspection SqlDialectInspection */

/** @noinspection PhpUnused */
function getUpdates26_05_00(): array {
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
		'web_resource_show_in_explore_more' => [
			'title' => 'Add Option to Web Resources to Show in Explore More',
			'description' => 'Add option in web builder resource settings to show in explore more',
			'sql' => [
				"ALTER TABLE web_builder_resource ADD COLUMN showInExploreMore TINYINT(1) DEFAULT 1"
			]
		], //web_resource_show_in_explore_more

		//lucas

		//tomas

		// stephen


		//pedro

		//other

	];
}
