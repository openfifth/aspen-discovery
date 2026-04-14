<?php
require_once ROOT_DIR . '/sys/Translation/Language.php';

class ExploreMoreSource extends DataObject {
	public $__table = 'explore_more_source';
	public $id;
	public $source;
	public $weight;
	public $showInExploreMore;

	static function getObjectStructure($context = ''): array {
		return [
			'id' => [
				'property' => 'id',
				'type' => 'label',
				'label' => 'ID',
				'description' => 'The unique id',
				'hideInLists' => true,
			],
			'source' => [
				'property' => 'source',
				'type' => 'enum',
				'label' => 'Source',
				'values' => [
					'Catalog' => 'Catalog',
					'EBSCO EDS' => 'EBSCO EDS',
					'EBSCOhost' => 'EBSCOhost',
					'Summon' => 'Summon',
					'Gale' => 'Gale',
					'CloudSource' => 'CloudSource',
					'Events' => 'Events',
					'Web Indexer' => 'Web Indexer',
					'Lists' => 'Lists',
					'Open Archives' => 'Open Archives',
					'Series' => 'Series',
					'Genealogy' => 'Genealogy',
				],
				'required' => true,
			],
			'weight' => [
				'property' => 'weight',
				'type' => 'integer',
				'label' => 'Order',
				'description' => 'Order for display',
				'default' => 0,
			],
			'showInExploreMore' => [
				'property' => 'showInExploreMore',
				'type' => 'checkbox',
				'label' => 'Show in Explore More',
				'description' => 'Whether this source displays content in Explore More',
				'default' => '1',
			],
			'libraries' => [
				'property' => 'libraries',
				'type' => 'multiSelect',
				'label' => 'Libraries',
				'description' => 'Libraries where this source is visible',
				'listStyle' => 'checkbox',
				'values' => Library::getLibraryList(false),
			],
			'locations' => [
				'property' => 'locations',
				'type' => 'multiSelect',
				'label' => 'Locations',
				'description' => 'Locations where this source is visible',
				'listStyle' => 'checkbox',
				'values' => Location::getLocationList(false),
			],
		];
	}
}
