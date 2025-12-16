<?php

/** @noinspection PhpUnused */
function getUpdates26_01_00(): array {
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
		'toggle_timestamps_for_events' => [
			'title' => 'Toggle Event Timestamps',
			'description' => 'Toggle Event Timestamps',
			'sql' => [
				'ALTER TABLE event ADD COLUMN hideTimestamps TINYINT(1) NOT NULL DEFAULT 0'
			]
		], //toggle_timestamps_for_events

		//leo

		//yanjun

		//imani

		//galen

		//alexander

		//chloe

		//mark j

		//lucas

		//tomas

		//other

	'user_payments_stripe_receipt_url' => [
		'title' => 'Add Stripe Receipt URL to User Payments',
		'description' => 'Add column to store Stripe receipt URL for payment receipts.',
		'continueOnError' => false,
		'sql' => [
			'ALTER TABLE user_payments ADD COLUMN IF NOT EXISTS stripeReceiptUrl VARCHAR(255) DEFAULT NULL'
		]
	], //user_payments_stripe_receipt_url

	];
}
