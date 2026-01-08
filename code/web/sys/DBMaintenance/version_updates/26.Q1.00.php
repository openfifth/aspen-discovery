<?php

/** @noinspection PhpUnused */
function getUpdates26_Q1_00(): array {
	return [
		//yanjun
		'overdrive_suppress_kindle_format' => [
			'title' => 'OverDrive Suppress Kindle Format',
			'description' => 'Allow OverDrive scopes to suppress Kindle format in the grouped work',
			'continueOnError' => false,
			'sql' => [
				"ALTER TABLE overdrive_scopes ADD COLUMN suppressKindleFormat TINYINT(1) DEFAULT 0",
			]
		],
		// tomas
		'async_facet_loading' => [
			'title' => 'Async Facet Loading Configuration',
			'description' => 'Add enableAsyncFacetLoading setting to library table for configurable async facet loading',
			'continueOnError' => false,
			'sql' => [
				'ALTER TABLE library ADD COLUMN IF NOT EXISTS enableAsyncFacetLoading TINYINT(1) DEFAULT 1 COMMENT "Enable async loading of collapsed facets to improve initial search performance" AFTER groupedWorkDisplaySettingId'
			]
		],
	];
}
