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
		'scheduled_offline_mode' => [
			'title' => 'Scheduled Offline Mode',
			'description' => 'Add columns to system variables table for scheduling offline mode.',
			'sql' => [
				'ALTER TABLE system_variables ADD COLUMN scheduledOfflineStart int(11) DEFAULT NULL',
				'ALTER TABLE system_variables ADD COLUMN scheduledOfflineEnd int(11) NULL DEFAULT NULL',
				'ALTER TABLE system_variables ADD COLUMN scheduledEcontentAccess TINYINT(1) NOT NULL DEFAULT 0',
			]
		], //scheduled_offline_mode

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
