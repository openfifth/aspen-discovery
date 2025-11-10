<?php

/** @noinspection PhpUnused */
function getUpdates25_12_00(): array {
	return [
		/*'name' => [
			 'title' => '',
			 'description' => '',
			 'continueOnError' => false,
			 'sql' => [
				 ''
			 ]
		 ], //name*/

		//mark - Grove

		//kirstien - Grove

		//kodi - Grove

		// Myranda - Grove

		//Yanjun Li - ByWater

		// Leo Stoyanov - BWS

		// Imani -BWS
		'externalRequestSettings' => [
			'title' => 'Add External Request Settings',
			'description' => 'Create table for External Request Settings',
			'sql' => [
				'CREATE TABLE IF NOT EXISTS `external_request_settings` (
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`requestType` varchar(50) DEFAULT NULL,
				`enabled` tinyint(1) DEFAULT 0,
				`expireDate` DATE DEFAULT NULL,
				PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;'
			]
		], //add external request settings table

		//alexander - Open Fifth

		//chloe - Open Fifth

		//James Staub - Nashville Public Library

		//Lucas Montoya - Theke Solutions

		//other
		
	];
}
