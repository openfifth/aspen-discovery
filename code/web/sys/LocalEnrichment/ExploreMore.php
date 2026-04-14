<?php

class ExploreMore extends DataObject {
	public $__table = 'explore_more';
	public $id;
	public $title;
	public $description;
	public $weight;
	public $dateCreated;
	public $dateUpdated;

	static function getObjectStructure($context = ''): array {
		return [
			'id' => [
				'property' => 'id',
				'type' => 'label',
				'label' => 'ID',
				'description' => 'The unique id of the Explore More entry',
				'hideInLists' => true,
			],
			'title' => [
				'property' => 'title',
				'type' => 'text',
				'label' => 'Title',
				'required' => true,
			],
			'description' => [
				'property' => 'description',
				'type' => 'textarea',
				'label' => 'Description',
			],
			'weight' => [
				'property' => 'weight',
				'type' => 'integer',
				'label' => 'Weight',
				'description' => 'Order for display',
				'default' => 0,
			],
			'dateCreated' => [
				'property' => 'dateCreated',
				'type' => 'timestamp',
				'label' => 'Date Created',
				'hideInLists' => true,
			],
			'dateUpdated' => [
				'property' => 'dateUpdated',
				'type' => 'timestamp',
				'label' => 'Date Updated',
				'hideInLists' => true,
			],
		];
	}
}
