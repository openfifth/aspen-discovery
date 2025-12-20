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
		'polaris_cancelled_holds' => [
			'title' => 'Polaris Cancelled Holds',
			'description' => 'Add option to show users their cancelled holds for Polaris',
			'sql' => [
				'ALTER TABLE library ADD COLUMN showCancelledHolds TINYINT(1) DEFAULT 1',
				'ALTER TABLE user_hold ADD COLUMN cancelled TINYINT(1)'
			]
		], //polaris_cancelled_holds

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
