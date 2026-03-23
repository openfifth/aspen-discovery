<?php
/** @noinspection SqlDialectInspection */

/** @noinspection PhpUnused */
function getUpdates26_04_00(): array {
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
		'add_user_app_request_logging_option' => [
			'title' => 'Add LiDA Request Logging Option for Users',
			'description' => 'Add option to log a users LiDA API requests for debugging',
			'continueOnError' => false,
			'sql' => [
				'ALTER TABLE user ADD COLUMN allowAppRequestLogging TINYINT(1) NOT NULL DEFAULT 0',
			]
		],
		//add_user_app_request_logging_option
		'add_user_app_request_log' => [
			'title' => 'Add LiDA Request Log for Users',
			'description' => 'Add logging for users who have enabled allow API logging for LiDA requests',
			'continueOnError' => false,
			'sql' => [
				'CREATE TABLE IF NOT EXISTS user_app_request_log (
					id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, 
					userId INT NOT NULL, 
					action VARCHAR(25), 
					method VARCHAR(25), 
					queryString TEXT NOT NULL,
					version VARCHAR(255),
					time TIMESTAMP DEFAULT CURRENT_TIMESTAMP
				) ENGINE = InnoDB'
			]
		],
		//add_user_app_request_log


		//kodi

		//yanjun

		//imani

		//galen

		//chloe

		//mark j
		'add_pageViewsFromPlacard_to_web_builder_resource_usage' => [
			'title' => 'Track web resource page views from a placard',
			'description' => 'Add new column to keep track of page views from a placard in the Web Builder Resource Usage table.',
			'continueOnError' => false,
			'sql' => [
				'ALTER TABLE web_builder_resource_usage ADD COLUMN pageViewsFromPlacard INT NOT NULL DEFAULT 0',
			]
		], //add_pageViewsFromPlacard_to_web_builder_resource_usage

		//lucas

		//tomas

		// stephen

		//other


	];
}
