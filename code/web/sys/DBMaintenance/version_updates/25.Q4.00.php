<?php

/** @noinspection PhpUnused */
function getUpdates25_Q4_00(): array {
	return [
		/*'name' => [
			 'title' => '',
			 'description' => '',
			 'continueOnError' => false,
			 'sql' => [
				 ''
			 ]
		 ], //name*/

		 //alexander - Open Fifth
		 'change_data_types_for_grapes_js_columns' => [
			'title' => 'Change Data Types For Grapes JS Columns',
			'description' => 'Update column types to allow for longer pages',
			'continueOnError' => false,
			'sql' => [
				"ALTER TABLE grapes_web_builder MODIFY templateContent LONGTEXT",
				"ALTER TABLE grapes_web_builder MODIFY htmlData LONGTEXT",
				"ALTER TABLE grapes_web_builder MODIFY cssData LONGTEXT",
			]
		], //change_data_types_for_grapes_js_columns
		'add_option_to_add_location_to_event_thumbail_image' => [
			'title' => 'Add Option to Add Location to Event Thumnail Image',
			'description' => 'Add ability to choose to add event location to event thumbnail image',
			'sql' => [
				"ALTER TABLE event ADD COLUMN displayEventBranchOnThumbnail TINYINT(1) DEFAULT 0",
				"ALTER TABLE user_events_entry ADD COLUMN displayEventBranchOnThumbnail TINYINT(1) DEFAULT 0"
			]
		], //add_option_to_add_location_to_event_thumbnail_image 
	];
}