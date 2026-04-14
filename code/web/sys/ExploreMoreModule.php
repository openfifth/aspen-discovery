<?php

class ExploreMoreModule extends DataObject {
	public $__table = 'explore_more';
	public $id;
	public $moduleId;
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
			'moduleId' => [
				'property' => 'moduleId',
				'type' => 'label',
				'label' => 'Module',
				'description' => 'The module this entry refers to',
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
				'description' => 'Whether this module displays content in Explore More',
				'default' => '1',
			],
		];
	}
}
