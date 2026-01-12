<?php
/** @noinspection SqlDialectInspection */

/** @noinspection PhpUnused */
function getUpdates26_02_00(): array {
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
		'add_configuration_for_index_process' => [
			'title' => 'Add configuration for solr soft commits',
			'description' => 'Add additional configuration for how records are processed into solr',
			'continueOnError' => true,
			'sql' => [
				"ALTER TABLE system_variables ADD COLUMN indexCommitInterval INT DEFAULT 10000",
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
