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
		'migrate_sendgrid_url_to_settings' => [
			'title' => 'Migrate SendGrid URL to Settings',
			'description' => 'The URL for sendGrid should be customisable as it is region specific',
			'continueOnError' => false,
			'sql' => [
				"ALTER TABLE sendgrid_settings ADD COLUMN baseUrl VARCHAR(255) DEFAULT null",
			],
		], //migrate_sendgrid_url_to_settings
	];
}