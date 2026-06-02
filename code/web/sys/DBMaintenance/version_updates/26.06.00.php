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
		'series_columns' => [
			'title' => 'Add Columns in Series Table',
			'description' => 'Add columns in series table for permanent id and language',
			'continueOnError' => false,
			'sql' => [
				'ALTER TABLE series ADD COLUMN seriesPermanentId CHAR(40)',
				'ALTER TABLE series ADD COLUMN seriesLanguage VARCHAR(20)',
			]
		], //series_columns
		'series_setting_version' => [
			'title' => 'Add Column in Series Indexing Settings Table',
			'description' => 'Add column in series settings indexing table for version',
			'continueOnError' => false,
			'sql' => [
				'ALTER TABLE series_indexing_settings ADD COLUMN version tinyint(1) DEFAULT 0',
				'ALTER TABLE series_indexing_settings ADD COLUMN truncateForVersionSwitch TINYINT(1) NOT NULL DEFAULT 0',
			]
		], //series_setting_version

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
