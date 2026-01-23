<?php
/** @noinspection SqlDialectInspection */

/** @noinspection PhpUnused */
function getUpdates26_01_01(): array {
	return [
		/*'name' => [
			 'title' => '',
			 'description' => '',
			 'continueOnError' => false,
			 'sql' => [
				 ''
			 ]
		 ], //name*/
		'add_circulation_cache_times_to_account_summary' => [
			'title' => 'Add Circulation Cache Times to Account Summary',
			'description' => 'Add Circulation Cache Times to Account Summary',
			'continueOnError' => false,
			'sql' => [
				'ALTER TABLE user_account_summary ADD COLUMN holdCacheTime INT(11) DEFAULT 0',
				'ALTER TABLE user_account_summary ADD COLUMN checkoutCacheTime INT(11) DEFAULT 0'
			]
		], //add_circulation_cache_times_to_account_summary

		//mark n
	];
}