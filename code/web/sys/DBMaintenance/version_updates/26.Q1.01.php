<?php

/** @noinspection PhpUnused */
function getUpdates26_Q1_01(): array {
	return [
		/*'name' => [
			 'title' => '',
			 'description' => '',
			 'continueOnError' => false,
			 'sql' => [
				 ''
			 ]
		 ], //name*/
		'sierra_phone_fields' => [
			'title' => 'Sierra Phone Fields',
			'description' => 'Add configurable phone fields for Sierra Phone and Work Phone',
			'sql' => [
				"ALTER TABLE library ADD COLUMN phoneField CHAR(1) DEFAULT 't'",
				"ALTER TABLE library ADD COLUMN workPhoneField CHAR(1) DEFAULT 'p'"
			]
		], //sierra_phone_fields
	];
}
