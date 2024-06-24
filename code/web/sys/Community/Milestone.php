<?php
require_once ROOT_DIR . '/sys/Community/Reward.php';
class Milestone extends DataObject {
    public $__table = 'ce_milestone';
    public $id;
    public $name;
    public $milestoneType;
    public $conditionalField;
    public $conditionalValue;
    public $campaignId;
    public $conditionalOperator;

  

    public static function getObjectStructure($context = ''): array {
     
        $structure = [
            'id' => [
                'property' => 'id',
                'type' => 'label',
				'label' => 'Id',
				'description' => 'The unique id',
            ],
            'name' => [
				'property' => 'name',
				'type' => 'text',
				'label' => 'Name',
				'maxLength' => 50,
				'description' => 'A name for the milestone',
				'required' => true,
			],
            'milestoneType' => [
                'property' => 'milestoneType',
                'type' => 'enum',
                'label' => 'When: ',
                'values' => [
                    'user_checkout' => 'Checkout',
                    'user_hold' => 'Hold',
                    'user_list' => 'List',
                    'user_work_review' => 'Rating',
                ],
            ],
            'conditionalField' => [
                'property' => 'conditionalField',
                'type' => 'enum',
                'label' => 'Conditional Field: ',
                'values' => [
                    'title' => 'Title',
                    'author' => 'Author',
                ],
            ],
            'conditionalOperator' => [
                'property' => 'conditionalOperator',
                'type' => 'enum',
                'label' => 'Conditional Operator',
                'values' => [
                    'equals' => 'Is',
                    'is_not' => 'Is Not',
                    'like' => 'Is Like',
                ],
            ],
            'conditionalValue' => [
                'property' => 'conditionalValue',
                'type' => 'text',
                'label' => 'Conditional Value: ',
                'maxLength' => 100,
                'description' => 'Optional value e.g. Fantasy',
                'required' => false,
            ],
        ];
        return $structure;
    } 

    /**
  * @return array
  */
  public static function getMilestoneList(): array {
    $milestone = new Milestone();
    $milestoneList = [];
     
    if ($milestone->find()) {
        while ($milestone->fetch()) {
            $milestoneList[$milestone->id] = $milestone->name;
        }
    }
    return $milestoneList;
  }
}