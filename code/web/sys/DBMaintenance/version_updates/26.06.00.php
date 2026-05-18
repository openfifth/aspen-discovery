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
		'scoped_more_like_this' => [
			'title' => 'Scoped More Like This',
			'description' => 'Add setting for scoping options for More Like This feature.',
			'sql' => [
				'ALTER TABLE library ADD COLUMN moreLikeThisSettings tinyint(1) DEFAULT 1',
			]
		], //scoped_more_like_this

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
