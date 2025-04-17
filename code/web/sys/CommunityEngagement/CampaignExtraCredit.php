<?php
require_once ROOT_DIR . '/sys/CommunityEngagement/Reward.php';
require_once ROOT_DIR . '/sys/CommunityEngagement/ExtraCredit.php';

class CampaignExtraCredit extends DataObject {
    public $__table = 'ce_campaign_extra_credit';
    public $id;
    public $campaignId;
    public $extraCreditId;
    public $goal;
    public $reward;
	public $weight;

    public function getNumericColumnNames(): array {
		return [
			'campaignId',
			'extraCreditId',
		];
	}

    static function getObjectStructure($context = '') {
		require_once ROOT_DIR . '/sys/CommunityEngagement/ExtraCredit.php';
		$extraCredit = new ExtraCredit();
		$availableExtraCreditActivities = [];
		$extraCredit->orderBy('name');
		$extraCredit->find();
		while ($extraCredit->fetch()) {
			$availableExtraCreditActivities[$extraCredit->id] = $extraCredit->name;
		}
		$goalRange = range(1, 100);
		$rewardList = Reward::getRewardList();

		return [
			'id' => [
				'property' => 'id',
				'type' => 'label',
				'label' => 'Id',
				'description' => 'The unique id',
			],
			'weight' => [
				'property' => 'weight',
				'type' => 'numeric',
				'label' => 'Weight',
				'weight' => 'Defines how items are sorted. Lower weights are displayed higher.',
				'required' => true,
			],
			'campaignId' => [
				'property' => 'campaignId',
				'type' => 'label',
				'label' => 'Id',
				'description' => 'The unique id of the campaign',
			],
			'extraCreditId' => [
				'property' => 'extraCreditId',
				'type' => 'enum',
				'label' => 'Extra Credit Criteria',
				'values' => $availableExtraCreditActivities,
				'description' => 'The extra credit activity to be added to the campaign',
			],
			'goal' => [
				'property' => 'goal',
				'type' => 'enum',
				'label' => 'Goal',
				'description' => 'The numerical goal for this activity',
				'values' => array_combine($goalRange, $goalRange),
				'required' => true,
			],
			'reward' => [
				'property' => 'reward',
				'type' => 'enum',
				'label' => 'Reward',
				'description' => 'The reward given for completing the activity',
				'values' => $rewardList,
			],
		];
	}
}
