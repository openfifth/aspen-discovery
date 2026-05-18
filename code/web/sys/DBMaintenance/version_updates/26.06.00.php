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

		//yanjun

		//imani

		//galen

		//chloe

		//pedro

		//mark j

		//lucas

		//tomas

		// stephen

		'user_payments_receipt_url_rename' => [
		'title' => 'Rename Receipt URL Column',
		'description' => 'Rename column from stripeReceiptUrl to receiptUrl.',
		'continueOnError' => false,
		'sql' => [
			'ALTER TABLE user_payments CHANGE stripeReceiptUrl receiptUrl VARCHAR(255) DEFAULT NULL'
		]
	], //user_payments_receipt_url_rename

		//other

	];
}
