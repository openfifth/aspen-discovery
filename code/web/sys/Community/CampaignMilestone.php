<?php
require_once ROOT_DIR . '/sys/Community/Reward.php';
require_once ROOT_DIR . '/sys/Community/Milestone.php';
require_once ROOT_DIR . '/sys/Community/MilestoneUsersProgress.php';

class CampaignMilestone extends DataObject {
    public $__table = 'ce_campaign_milestones';
    public $id;
    public $campaignId;
    public $milestoneId;
    public $goal;

    public function getNumericColumnNames(): array {
		return [
			'campaignId',
			'milestoneId',
		];
	}

    static function getObjectStructure($context = '') {
        require_once ROOT_DIR . '/sys/Community/Milestone.php';
        $milestone = new Milestone();
        $availableMilestones = [];
        $milestone->orderBy('name');
        $milestone->find();
        while ($milestone->fetch()) {
            $availableMilestones[$milestone->id] = $milestone->name;
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
            'campaignId' => [
                'property' => 'campaignId',
                'type' => 'label',
				'label' => 'Id',
				'description' => 'The unique id of the campaign',
            ],
            'milestoneId' => [
                'property' => 'milestoneId',
                'type' => 'enum',
				'label' => 'Milestone',
                'values' => $availableMilestones,
				'description' => 'The milestone to be added to the campaign',
            ],
            'goal' => [
                'property' => 'goal',
                'type' => 'enum',
                'label' => 'Goal',
                'description' => 'The numerical goal for this milestone',
                'values' => array_combine($goalRange, $goalRange),
                'required' => true,
            ],
            'reward' => [
                'property' => 'reward',
                'type' => 'enum',
                'label' => 'Reward',
                'description' => 'The reward given for achieving the milestone',
                'values' => $rewardList,
            ],
        ];
    }

    public function canActiveUserEdit() {
		return true;
	}

    public static function getMilestoneByCampaign($campaignId) {
      $milestones = [];
      $campaignMilestone = new CampaignMilestone();
      $campaignMilestone->whereAdd('campaignId = ' . $campaignId);
      $campaignMilestone->find();

      $milestoneIds = [];
      while ($campaignMilestone->fetch()) {
        $milestoneIds[] = $campaignMilestone->milestoneId;
      }

      if (!empty($milestoneIds)) {
        $milestone = new Milestone();
        $milestone->whereAddIn('id', $milestoneIds, true);
        $milestone->find();

        while ($milestone->fetch()) {
            $milestones[] = clone $milestone;
        }
      }
      return $milestones;
    }

    public static function getMilestoneGoalCountByCampaign($campaignId, $milestoneId) {

        $campaignMilestone = new CampaignMilestone();
        $campaignMilestone->whereAdd('campaignId = ' . $campaignId);
        $campaignMilestone->whereAdd('milestoneId = ' . $milestoneId);
        $campaignMilestone->find(true);

        return $campaignMilestone->goal;
    }

   public static function getMilestoneProgress($campaignId, $userId, $milestoneId) {
        $milestoneUsersProgress = new MilestoneUsersProgress();
        $campaignMilestone = new CampaignMilestone();

        //Get goal total
        $goal = $campaignMilestone->getMilestoneGoalCountByCampaign($campaignId, $milestoneId);

        //Number of completed goals for this milestone
        $userCompletedGoalCount = $milestoneUsersProgress->getProgressByMilestoneId($milestoneId, $userId);

        if ($goal > 0) {
            $progress = ($userCompletedGoalCount / $goal ) * 100;
        } else {
            $progress = 0;
        }
        return [
            'progress' => round($progress, 2),
            'completed' => $userCompletedGoalCount
        ];
   }

}