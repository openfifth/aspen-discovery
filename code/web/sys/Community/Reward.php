<?php

class Reward extends DataObject {
    public $__table = 'ce_reward';
    public $id;
    public $name;
    public $description;
    public $rewardType;

    public static function getObjectStructure($context = ''):array {
        $rewardType = self::getRewardType();
        return [
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
				'description' => 'A name for the campaign',
				'required' => true,
			],
            'description' => [
				'property' => 'description',
				'type' => 'text',
				'label' => 'Description',
				'maxLength' => 255,
				'description' => 'A description of the campaign',
			],
            'rewardType' => [
                'property' => 'rewardType',
                'type' =>'enum',
                'label' => 'Reward Type',
                'description' => 'The type of reward',
                'values' => $rewardType,
            ],
        ];
    }

    public static function getRewardType () {
        return [
            0 => 'Physical',
            1 => 'Digiatal',
        ];
    }

    /**
     * @return array
     */
    public static function getRewardList(): array {
        $reward = new Reward();
        $rewardList = [];

        if ($reward->find()) {
            while ($reward->fetch()) {
                $rewardList[$reward->id] = $reward->name;
            }
        }
        return $rewardList;
    }
}