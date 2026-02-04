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
		'add_option_to_add_location_to_event_thumbail_image' => [
			'title' => 'Add Option to Add Location to Event Thumnail Image',
			'description' => 'Add ability to choose to add event location to event thumbnail image',
			'sql' => [
				"ALTER TABLE event ADD COLUMN displayEventBranchOnThumbnail TINYINT(1) DEFAULT 0",
				"ALTER TABLE user_events_entry ADD COLUMN displayEventBranchOnThumbnail TINYINT(1) DEFAULT 0"
			]
		], //add_option_to_add_location_to_event_thumbnail_image 

		//chloe

		//mark j

		//lucas


		//tomas

		//other


	];
}
